<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;
use RuntimeException;

trait RunsSqlSeedFile
{
    protected function runSqlSeedFile(string $relativePath): void
    {
        $path = database_path($relativePath);

        if (! is_file($path)) {
            throw new RuntimeException("SQL seed file not found: {$path}");
        }

        $this->forEachSqlStatement($path, static function (string $statement): void {
            DB::unprepared($statement);
        });
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
}
