<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $schemaFile = 'data/schema_groups/005_public_cms.sql';

    public function up(): void
    {
        $this->runSqlFile(database_path($this->schemaFile));
    }

    public function down(): void
    {
        $metadataPath = database_path('data/database_layout.json');
        if (! is_file($metadataPath)) {
            return;
        }

        $metadata = json_decode((string) file_get_contents($metadataPath), true, 512, JSON_THROW_ON_ERROR);
        $tables = array_reverse($metadata['migration_groups'][$this->schemaFile] ?? []);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            DB::statement('DROP TABLE IF EXISTS `'.str_replace('`', '``', $table).'`');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function runSqlFile(string $path): void
    {
        if (! is_file($path)) {
            throw new RuntimeException("SQL schema file not found: {$path}");
        }

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
                    if ($char === "\n") {
                        $lineComment = false;
                    }

                    continue;
                }

                if ($blockComment) {
                    if ($char === '*' && $next === '/') {
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

                    continue;
                }

                if ($char === '/' && $next === '*') {
                    $blockComment = true;
                    $i++;

                    continue;
                }

                if ($char === "'" || $char === '"' || $char === '`') {
                    $quote = $char;
                    $buffer .= $char;

                    continue;
                }

                if ($char === ';') {
                    $statement = trim($buffer);
                    if ($statement !== '') {
                        DB::unprepared($statement);
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
            DB::unprepared($statement);
        }
    }
};
