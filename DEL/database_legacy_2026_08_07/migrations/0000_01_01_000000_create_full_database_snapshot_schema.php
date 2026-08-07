<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $schemaPath = database_path('data/full_database_snapshot/schema.sql');

        if (! is_file($schemaPath)) {
            throw new RuntimeException("Full database snapshot schema file not found: {$schemaPath}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->forEachSqlStatement($schemaPath, static function (string $statement): void {
            DB::unprepared($statement);
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        $metadataPath = database_path('data/full_database_snapshot/metadata.json');
        if (! is_file($metadataPath)) {
            return;
        }

        $metadata = json_decode((string) file_get_contents($metadataPath), true, 512, JSON_THROW_ON_ERROR);
        $tables = array_reverse($metadata['tables_in_dump'] ?? []);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            if ($table === 'migrations') {
                continue;
            }

            DB::statement('DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function forEachSqlStatement(string $path, callable $callback): void
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
};
