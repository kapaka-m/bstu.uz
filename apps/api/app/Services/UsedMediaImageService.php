<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UsedMediaImageService
{
    private const IMAGE_EXTENSION_PATTERN = '/\.(?:avif|gif|jpe?g|png|svg|webp)(?:[?#].*)?$/i';

    /**
     * Return public-storage image paths that are referenced by active project data.
     *
     * This deliberately scans content tables, not the media table itself, so stale media
     * records do not count as "used" merely because they exist in the library.
     *
     * @return array<int, string>
     */
    public function collectPublicImagePaths(): array
    {
        $paths = [];

        foreach (Schema::getTableListing() as $table) {
            if ($this->shouldSkipTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            if ($columns === []) {
                continue;
            }

            $query = DB::table($table)->select($columns);
            $handler = function ($rows) use (&$paths, $columns): void {
                foreach ($rows as $row) {
                    foreach ($columns as $column) {
                        $this->collectFromValue($row->{$column} ?? null, $paths);
                    }
                }
            };

            if (in_array('id', $columns, true)) {
                $query->orderBy('id')->chunkById(200, $handler);
            } else {
                $query->orderBy($columns[0])->chunk(200, $handler);
            }
        }

        $paths = array_keys($paths);
        sort($paths, SORT_NATURAL | SORT_FLAG_CASE);

        return $paths;
    }

    /**
     * Ensure every used public image has a media-library row.
     *
     * @return array{used:int, created:int, existing:int}
     */
    public function syncToMedia(): array
    {
        $paths = $this->collectPublicImagePaths();
        $created = 0;
        $existing = 0;

        foreach ($paths as $path) {
            if (Media::where('path', $path)->exists()) {
                $existing++;

                continue;
            }

            $absolutePath = Storage::disk('public')->path($path);
            $filename = basename($path);
            $title = Str::of(pathinfo($filename, PATHINFO_FILENAME))
                ->replace(['_', '-'], ' ')
                ->headline()
                ->toString();

            Media::create([
                'disk' => 'public',
                'path' => $path,
                'filename' => $filename,
                'title' => $title,
                'alt_text' => $title,
                'type' => 'image',
                'mime_type' => is_file($absolutePath) ? (mime_content_type($absolutePath) ?: null) : null,
                'size' => is_file($absolutePath) ? filesize($absolutePath) : null,
                'is_public' => true,
                'alt_key' => 'used-media.'.sha1($path),
            ]);

            $created++;
        }

        return [
            'used' => count($paths),
            'created' => $created,
            'existing' => $existing,
        ];
    }

    private function shouldSkipTable(string $table): bool
    {
        $tableName = Str::afterLast($table, '.');

        return in_array($tableName, [
            'audit_logs',
            'cache',
            'cache_locks',
            'failed_jobs',
            'jobs',
            'job_batches',
            'media',
            'migrations',
            'password_reset_tokens',
            'personal_access_tokens',
            'sessions',
        ], true);
    }

    /**
     * @param array<string, true> $paths
     */
    private function collectFromValue(mixed $value, array &$paths): void
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->collectFromValue($item, $paths);
            }

            return;
        }

        if (! is_string($value) || $value === '') {
            return;
        }

        $directPath = $this->normalizePublicImagePath($value);
        if ($directPath !== null) {
            $paths[$directPath] = true;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $this->collectFromValue($decoded, $paths);
        }

        foreach ($this->extractImageCandidates($value) as $candidate) {
            $path = $this->normalizePublicImagePath($candidate);
            if ($path !== null) {
                $paths[$path] = true;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function extractImageCandidates(string $value): array
    {
        if (! preg_match_all(
            '!(?:https?://[^\s"\'<>]+|/storage/[^\s"\'<>]+|storage/[^\s"\'<>]+|public/[^\s"\'<>]+|[A-Za-z0-9._~%+\-/]+\.(?:avif|gif|jpe?g|png|svg|webp)(?:[?#][^\s"\'<>]*)?)!i',
            $value,
            $matches
        )) {
            return [];
        }

        return $matches[0];
    }

    private function normalizePublicImagePath(string $candidate): ?string
    {
        $candidate = trim($candidate, " \t\n\r\0\x0B\"'()[]{}<>,;");
        if ($candidate === '' || str_starts_with($candidate, 'data:')) {
            return null;
        }

        if (preg_match('#^https?://#i', $candidate)) {
            $path = parse_url($candidate, PHP_URL_PATH);
            if (! is_string($path) || $path === '') {
                return null;
            }
            $candidate = $path;
        }

        $candidate = rawurldecode($candidate);
        $candidate = ltrim(str_replace('\\', '/', $candidate), '/');

        if (strlen($candidate) > 512 || preg_match('/[\r\n\t\0]/', $candidate)) {
            return null;
        }

        foreach (['storage/', 'public/'] as $prefix) {
            if (str_starts_with($candidate, $prefix)) {
                $candidate = substr($candidate, strlen($prefix));
            }
        }

        $candidate = preg_replace('/[?#].*$/', '', $candidate) ?? $candidate;
        $candidate = ltrim($candidate, '/');

        if (! preg_match(self::IMAGE_EXTENSION_PATTERN, $candidate)) {
            return null;
        }

        if (str_contains($candidate, '..') || str_starts_with($candidate, 'private/')) {
            return null;
        }

        return Storage::disk('public')->exists($candidate) ? $candidate : null;
    }
}
