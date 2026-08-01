<?php

$databaseDir = dirname(__DIR__);
$source = $databaseDir . DIRECTORY_SEPARATOR . 'Mer' . DIRECTORY_SEPARATOR . 'bstu_international_complete_merged.sql';
$targetDir = $databaseDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'full_database_snapshot';

if (! is_file($source)) {
    fwrite(STDERR, "Source dump not found: {$source}\n");
    exit(1);
}

if (! is_dir($targetDir) && ! mkdir($targetDir, 0777, true) && ! is_dir($targetDir)) {
    fwrite(STDERR, "Could not create target directory: {$targetDir}\n");
    exit(1);
}

$sql = file_get_contents($source);
if ($sql === false) {
    fwrite(STDERR, "Could not read source dump.\n");
    exit(1);
}

$statements = splitSqlStatements($sql);
$schema = [];
$data = [];
$tables = [];
$seededTables = [];
$skippedTables = [];
$autoIncrementStatements = [];

foreach ($statements as $statement) {
    $trimmed = trim($statement);
    if ($trimmed === '') {
        continue;
    }

    $classified = stripLeadingSqlComments($trimmed);

    if (preg_match('/^CREATE\s+TABLE\s+`([^`]+)`/i', $classified, $match)) {
        $table = $match[1];
        $tables[$table] = true;

        if ($table === 'migrations') {
            $skippedTables[$table] = 'Laravel owns the migration repository table during artisan migrate.';
            continue;
        }

        $schema[] = $trimmed . ';';
        continue;
    }

    if (preg_match('/^ALTER\s+TABLE\s+`([^`]+)`/i', $classified, $match)) {
        $table = $match[1];

        if ($table === 'migrations') {
            $skippedTables[$table] = 'Laravel owns the migration repository table during artisan migrate.';
            continue;
        }

        if (stripos($trimmed, 'AUTO_INCREMENT=') !== false) {
            $autoIncrementStatements[] = $trimmed . ';';
        }

        $schema[] = $trimmed . ';';
        continue;
    }

    if (preg_match('/^INSERT\s+INTO\s+`([^`]+)`/i', $classified, $match)) {
        $table = $match[1];

        if ($table === 'migrations') {
            $skippedTables[$table] = 'Laravel owns the migration repository table during artisan migrate.';
            continue;
        }

        $seededTables[$table] = true;
        foreach (splitInsertStatement($classified) as $insertStatement) {
            $data[] = $insertStatement;
        }
        continue;
    }
}

foreach ($autoIncrementStatements as $statement) {
    $data[] = $statement;
}

ksort($tables);
ksort($seededTables);
ksort($skippedTables);

$metadata = [
    'source_dump' => str_replace('\\', '/', realpath($source) ?: $source),
    'source_sha256' => hash_file('sha256', $source),
    'generated_at' => gmdate('c'),
    'table_count_in_dump' => count($tables),
    'tables_in_dump' => array_keys($tables),
    'seeded_tables' => array_keys($seededTables),
    'skipped_tables' => $skippedTables,
    'schema_statement_count' => count($schema),
    'data_statement_count' => count($data),
];

file_put_contents($targetDir . DIRECTORY_SEPARATOR . 'schema.sql', implode("\n\n", $schema) . "\n");
file_put_contents($targetDir . DIRECTORY_SEPARATOR . 'data.sql', implode("\n\n", $data) . "\n");
file_put_contents(
    $targetDir . DIRECTORY_SEPARATOR . 'metadata.json',
    json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);

echo "Generated full database snapshot.\n";
echo "Tables in dump: " . count($tables) . "\n";
echo "Schema statements: " . count($schema) . "\n";
echo "Data statements: " . count($data) . "\n";
echo "Skipped tables: " . implode(', ', array_keys($skippedTables)) . "\n";

function splitSqlStatements(string $sql): array
{
    $statements = [];
    $buffer = '';
    $length = strlen($sql);
    $quote = null;
    $escaped = false;
    $lineComment = false;
    $blockComment = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';

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
            $statements[] = $buffer;
            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    if (trim($buffer) !== '') {
        $statements[] = $buffer;
    }

    return $statements;
}

function stripLeadingSqlComments(string $statement): string
{
    while (true) {
        $statement = ltrim($statement);

        if (str_starts_with($statement, '--')) {
            $position = strpos($statement, "\n");
            if ($position === false) {
                return '';
            }
            $statement = substr($statement, $position + 1);
            continue;
        }

        if (str_starts_with($statement, '#')) {
            $position = strpos($statement, "\n");
            if ($position === false) {
                return '';
            }
            $statement = substr($statement, $position + 1);
            continue;
        }

        if (str_starts_with($statement, '/*')) {
            $position = strpos($statement, '*/');
            if ($position === false) {
                return '';
            }
            $statement = substr($statement, $position + 2);
            continue;
        }

        return $statement;
    }
}

function splitInsertStatement(string $statement, int $maxBytes = 512000): array
{
    $valuesPosition = stripos($statement, ' VALUES');
    if ($valuesPosition === false) {
        return [rtrim($statement, ';') . ';'];
    }

    $prefix = substr($statement, 0, $valuesPosition) . " VALUES\n";
    $values = ltrim(substr($statement, $valuesPosition + 7));
    $values = rtrim(rtrim($values), ';');
    $rows = [];
    $buffer = '';
    $quote = null;
    $escaped = false;
    $depth = 0;
    $length = strlen($values);

    for ($i = 0; $i < $length; $i++) {
        $char = $values[$i];
        $buffer .= $char;

        if ($quote !== null) {
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

        if ($char === '\'' || $char === '"') {
            $quote = $char;
            continue;
        }

        if ($char === '(') {
            $depth++;
            continue;
        }

        if ($char === ')') {
            $depth--;
            if ($depth === 0) {
                $rows[] = trim($buffer);
                $buffer = '';

                while ($i + 1 < $length && (ctype_space($values[$i + 1]) || $values[$i + 1] === ',')) {
                    $i++;
                }
            }
        }
    }

    if ($rows === []) {
        return [rtrim($statement, ';') . ';'];
    }

    $statements = [];
    $chunk = [];
    $chunkBytes = strlen($prefix);

    foreach ($rows as $row) {
        $rowBytes = strlen($row) + 3;
        if ($chunk !== [] && $chunkBytes + $rowBytes > $maxBytes) {
            $statements[] = $prefix . implode(",\n", $chunk) . ';';
            $chunk = [];
            $chunkBytes = strlen($prefix);
        }

        $chunk[] = $row;
        $chunkBytes += $rowBytes;
    }

    if ($chunk !== []) {
        $statements[] = $prefix . implode(",\n", $chunk) . ';';
    }

    return $statements;
}
