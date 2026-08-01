<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FullDatabaseSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $snapshotDir = database_path('data/full_database_snapshot');
        $metadataPath = $snapshotDir . DIRECTORY_SEPARATOR . 'metadata.json';
        $dataPath = $snapshotDir . DIRECTORY_SEPARATOR . 'data.sql';

        if (! is_file($metadataPath)) {
            throw new RuntimeException("Full database snapshot metadata file not found: {$metadataPath}");
        }

        if (! is_file($dataPath)) {
            throw new RuntimeException("Full database snapshot data file not found: {$dataPath}");
        }

        $metadata = json_decode((string) file_get_contents($metadataPath), true, 512, JSON_THROW_ON_ERROR);
        $tables = $metadata['tables_in_dump'] ?? [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            if ($table === 'migrations') {
                continue;
            }

            DB::statement('TRUNCATE TABLE `' . str_replace('`', '``', $table) . '`');
        }

        $this->forEachSqlStatement($dataPath, static function (string $statement): void {
            DB::unprepared($statement);
        });

        $this->normalizeSnapshotData();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function normalizeSnapshotData(): void
    {
        DB::table('about_pages')
            ->where('identity_image', 'about-page/bstu-about-identity.jpg')
            ->update(['identity_image' => 'cms/about-page/bstu-about-identity.jpg']);

        AboutPage::with('contentEntries.translations')
            ->get()
            ->each
            ->syncTranslationsMirror();

        DB::table('cache')
            ->where('key', 'like', '%about-page%')
            ->delete();
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
}
