<?php

$databaseDir = dirname(__DIR__);
$snapshotDir = $databaseDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'full_database_snapshot';
$schemaPath = $snapshotDir . DIRECTORY_SEPARATOR . 'schema.sql';
$dataPath = $snapshotDir . DIRECTORY_SEPARATOR . 'data.sql';
$metadataPath = $snapshotDir . DIRECTORY_SEPARATOR . 'metadata.json';
$resultPath = $snapshotDir . DIRECTORY_SEPARATOR . 'verification_result.json';

$sourceDb = $argv[1] ?? 'bstu_rebuild_source_tmp';
$targetDb = $argv[2] ?? 'bstu_rebuild_target_tmp';

foreach ([$sourceDb, $targetDb] as $dbName) {
    if (! str_starts_with($dbName, 'bstu_rebuild_')) {
        fwrite(STDERR, "Refusing to verify against non-temporary database: {$dbName}\n");
        exit(1);
    }
}

foreach ([$schemaPath, $dataPath, $metadataPath] as $path) {
    if (! is_file($path)) {
        fwrite(STDERR, "Required snapshot file missing: {$path}\n");
        exit(1);
    }
}

$root = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$root->exec("DROP DATABASE IF EXISTS `{$sourceDb}`");
$root->exec("CREATE DATABASE `{$sourceDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$source = connectDb($sourceDb);
$target = connectDb($targetDb);

$source->exec('SET FOREIGN_KEY_CHECKS=0');
forEachSqlStatement($schemaPath, static function (string $statement) use ($source): void {
    $source->exec($statement);
});
forEachSqlStatement($dataPath, static function (string $statement) use ($source): void {
    $source->exec($statement);
});
$source->exec('SET FOREIGN_KEY_CHECKS=1');

$metadata = json_decode((string) file_get_contents($metadataPath), true, 512, JSON_THROW_ON_ERROR);
$tables = array_values(array_filter($metadata['tables_in_dump'] ?? [], static fn ($table) => $table !== 'migrations'));

$result = [
    'source_database' => $sourceDb,
    'target_database' => $targetDb,
    'compared_tables' => count($tables),
    'schema' => [
        'tables_match' => true,
        'columns_match' => true,
        'indexes_match' => true,
        'foreign_keys_match' => true,
        'differences' => [],
    ],
    'data' => [
        'row_counts_match' => true,
        'total_source_rows' => 0,
        'total_target_rows' => 0,
        'differences' => [],
    ],
    'integrity' => [
        'orphan_foreign_keys' => [],
        'duplicate_unique_keys' => [],
    ],
];

$sourceTables = listTables($source, $sourceDb);
$targetTables = listTables($target, $targetDb);
$missingTables = array_values(array_diff($sourceTables, $targetTables));
$extraTables = array_values(array_diff($targetTables, $sourceTables));
if ($missingTables !== [] || $extraTables !== []) {
    $result['schema']['tables_match'] = false;
    $result['schema']['differences']['tables'] = [
        'missing_in_target' => $missingTables,
        'extra_in_target' => $extraTables,
    ];
}

compareMap($result, 'columns', getColumns($source, $sourceDb, $tables), getColumns($target, $targetDb, $tables));
compareMap($result, 'indexes', getIndexes($source, $sourceDb, $tables), getIndexes($target, $targetDb, $tables));
compareMap($result, 'foreign_keys', getForeignKeys($source, $sourceDb, $tables), getForeignKeys($target, $targetDb, $tables));

foreach ($tables as $table) {
    $sourceCount = (int) $source->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '`')->fetchColumn();
    $targetCount = (int) $target->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '`')->fetchColumn();
    $result['data']['total_source_rows'] += $sourceCount;
    $result['data']['total_target_rows'] += $targetCount;

    if ($sourceCount !== $targetCount) {
        $result['data']['row_counts_match'] = false;
        $result['data']['differences'][$table] = [
            'source' => $sourceCount,
            'target' => $targetCount,
        ];
    }
}

$result['integrity']['orphan_foreign_keys'] = findOrphanForeignKeys($target, $targetDb, $tables);
$result['integrity']['duplicate_unique_keys'] = findDuplicateUniqueKeys($target, $targetDb, $tables);

file_put_contents($resultPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "Verification complete.\n";
echo "Compared tables: {$result['compared_tables']}\n";
echo "Source rows: {$result['data']['total_source_rows']}\n";
echo "Target rows: {$result['data']['total_target_rows']}\n";
echo 'Schema match: ' . ($result['schema']['tables_match'] && $result['schema']['columns_match'] && $result['schema']['indexes_match'] && $result['schema']['foreign_keys_match'] ? 'yes' : 'no') . "\n";
echo 'Row counts match: ' . ($result['data']['row_counts_match'] ? 'yes' : 'no') . "\n";
echo 'Orphan FKs: ' . count($result['integrity']['orphan_foreign_keys']) . "\n";
echo 'Duplicate unique keys: ' . count($result['integrity']['duplicate_unique_keys']) . "\n";

function connectDb(string $database): PDO
{
    return new PDO("mysql:host=127.0.0.1;port=3306;dbname={$database};charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

function listTables(PDO $pdo, string $database): array
{
    $stmt = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = "BASE TABLE" AND TABLE_NAME <> "migrations" ORDER BY TABLE_NAME');
    $stmt->execute([$database]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getColumns(PDO $pdo, string $database, array $tables): array
{
    $stmt = $pdo->prepare(
        'SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, CHARACTER_SET_NAME, COLLATION_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ?
         ORDER BY TABLE_NAME, ORDINAL_POSITION'
    );
    $stmt->execute([$database]);

    $allowed = array_flip($tables);
    $rows = [];
    foreach ($stmt as $row) {
        if (! isset($allowed[$row['TABLE_NAME']])) {
            continue;
        }

        $rows[$row['TABLE_NAME'] . '.' . $row['COLUMN_NAME']] = [
            'position' => (int) $row['ORDINAL_POSITION'],
            'type' => $row['COLUMN_TYPE'],
            'nullable' => $row['IS_NULLABLE'],
            'default' => $row['COLUMN_DEFAULT'],
            'extra' => $row['EXTRA'],
            'charset' => $row['CHARACTER_SET_NAME'],
            'collation' => $row['COLLATION_NAME'],
        ];
    }

    return $rows;
}

function getIndexes(PDO $pdo, string $database, array $tables): array
{
    $stmt = $pdo->prepare(
        'SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, SUB_PART, INDEX_TYPE
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = ?
         ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX'
    );
    $stmt->execute([$database]);

    $allowed = array_flip($tables);
    $indexes = [];
    foreach ($stmt as $row) {
        if (! isset($allowed[$row['TABLE_NAME']])) {
            continue;
        }

        $key = $row['TABLE_NAME'] . '.' . $row['INDEX_NAME'];
        $indexes[$key]['unique'] = ((int) $row['NON_UNIQUE']) === 0;
        $indexes[$key]['type'] = $row['INDEX_TYPE'];
        $indexes[$key]['columns'][] = [
            'name' => $row['COLUMN_NAME'],
            'sub_part' => $row['SUB_PART'] === null ? null : (int) $row['SUB_PART'],
        ];
    }

    return $indexes;
}

function getForeignKeys(PDO $pdo, string $database, array $tables): array
{
    $stmt = $pdo->prepare(
        'SELECT k.CONSTRAINT_NAME, k.TABLE_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME, r.UPDATE_RULE, r.DELETE_RULE
         FROM information_schema.KEY_COLUMN_USAGE k
         JOIN information_schema.REFERENTIAL_CONSTRAINTS r
           ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
          AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
          AND r.TABLE_NAME = k.TABLE_NAME
         WHERE k.TABLE_SCHEMA = ? AND k.REFERENCED_TABLE_NAME IS NOT NULL
         ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION'
    );
    $stmt->execute([$database]);

    $allowed = array_flip($tables);
    $fks = [];
    foreach ($stmt as $row) {
        if (! isset($allowed[$row['TABLE_NAME']])) {
            continue;
        }

        $key = $row['TABLE_NAME'] . '.' . $row['CONSTRAINT_NAME'];
        $fks[$key]['referenced_table'] = $row['REFERENCED_TABLE_NAME'];
        $fks[$key]['update_rule'] = $row['UPDATE_RULE'];
        $fks[$key]['delete_rule'] = $row['DELETE_RULE'];
        $fks[$key]['columns'][] = $row['COLUMN_NAME'] . '->' . $row['REFERENCED_COLUMN_NAME'];
    }

    return $fks;
}

function compareMap(array &$result, string $name, array $source, array $target): void
{
    if ($source === $target) {
        return;
    }

    $flag = match ($name) {
        'columns' => 'columns_match',
        'indexes' => 'indexes_match',
        'foreign_keys' => 'foreign_keys_match',
    };

    $result['schema'][$flag] = false;
    $result['schema']['differences'][$name] = [
        'missing_in_target' => array_slice(array_values(array_diff(array_keys($source), array_keys($target))), 0, 50),
        'extra_in_target' => array_slice(array_values(array_diff(array_keys($target), array_keys($source))), 0, 50),
        'different_definitions' => array_slice(array_values(array_filter(array_keys(array_intersect_key($source, $target)), static fn ($key) => $source[$key] !== $target[$key])), 0, 50),
    ];
}

function findOrphanForeignKeys(PDO $pdo, string $database, array $tables): array
{
    $issues = [];
    $fks = getForeignKeys($pdo, $database, $tables);

    foreach ($fks as $key => $fk) {
        [$table] = explode('.', $key, 2);
        if (count($fk['columns']) !== 1) {
            continue;
        }

        [$column, $referencedColumn] = explode('->', $fk['columns'][0], 2);
        $sql = 'SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '` child '
            . 'LEFT JOIN `' . str_replace('`', '``', $fk['referenced_table']) . '` parent '
            . 'ON child.`' . str_replace('`', '``', $column) . '` = parent.`' . str_replace('`', '``', $referencedColumn) . '` '
            . 'WHERE child.`' . str_replace('`', '``', $column) . '` IS NOT NULL '
            . 'AND parent.`' . str_replace('`', '``', $referencedColumn) . '` IS NULL';
        $count = (int) $pdo->query($sql)->fetchColumn();
        if ($count > 0) {
            $issues[$key] = $count;
        }
    }

    return $issues;
}

function findDuplicateUniqueKeys(PDO $pdo, string $database, array $tables): array
{
    $issues = [];
    $indexes = getIndexes($pdo, $database, $tables);

    foreach ($indexes as $key => $index) {
        if (! $index['unique'] || str_ends_with($key, '.PRIMARY')) {
            continue;
        }

        [$table] = explode('.', $key, 2);
        $columns = array_column($index['columns'], 'name');
        $columnSql = implode(', ', array_map(static fn ($column) => '`' . str_replace('`', '``', $column) . '`', $columns));
        $notNull = implode(' AND ', array_map(static fn ($column) => '`' . str_replace('`', '``', $column) . '` IS NOT NULL', $columns));
        $sql = 'SELECT COUNT(*) FROM (SELECT ' . $columnSql . ', COUNT(*) c FROM `' . str_replace('`', '``', $table) . '` WHERE ' . $notNull . ' GROUP BY ' . $columnSql . ' HAVING c > 1) duplicates';
        $count = (int) $pdo->query($sql)->fetchColumn();
        if ($count > 0) {
            $issues[$key] = $count;
        }
    }

    return $issues;
}

function forEachSqlStatement(string $path, callable $callback): void
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException("Unable to open SQL file: {$path}");
    }

    $buffer = '';
    $quote = null;
    $escaped = false;
    $lineComment = false;
    $blockComment = false;

    while (! feof($handle)) {
        $chunk = fread($handle, 1024 * 1024);
        if ($chunk === false) {
            fclose($handle);
            throw new RuntimeException("Unable to read SQL file: {$path}");
        }

        $length = strlen($chunk);
        for ($i = 0; $i < $length; $i++) {
            $char = $chunk[$i];
            $next = $i + 1 < $length ? $chunk[$i + 1] : '';

            if ($lineComment) {
                $buffer .= $char;
                if ($char === "\n") {
                    $lineComment = false;
                }
                continue;
            }

            if ($blockComment) {
                $buffer .= $char;
                if ($char === '*' && $next === '/') {
                    $buffer .= $next;
                    $i++;
                    $blockComment = false;
                }
                continue;
            }

            if ($quote !== null) {
                $buffer .= $char;
                if ($escaped) {
                    $escaped = false;
                    continue;
                }
                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }
                if ($char === $quote) {
                    $quote = null;
                }
                continue;
            }

            if (($char === '-' && $next === '-') || $char === '#') {
                $lineComment = true;
                $buffer .= $char;
                continue;
            }

            if ($char === '/' && $next === '*') {
                $blockComment = true;
                $buffer .= $char . $next;
                $i++;
                continue;
            }

            if ($char === '\'' || $char === '"' || $char === '`') {
                $quote = $char;
                $buffer .= $char;
                continue;
            }

            if ($char === ';') {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $callback($statement);
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }
    }

    fclose($handle);

    $statement = trim($buffer);
    if ($statement !== '') {
        $callback($statement);
    }
}
