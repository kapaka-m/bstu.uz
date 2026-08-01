<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OrganizeStorageCommand extends Command
{
    protected $signature = 'bstu:organize-storage';

    protected $description = 'Organize BSTU storage files and update database file paths.';

    public function handle(): int
    {
        $publicMappings = [
            'uploads/images/' => 'cms/uploads/images/',
            'about-page/' => 'cms/about-page/',
            'centers/' => 'cms/university-centers/',
            'news-events/' => 'cms/news-events/',
            'media/administration/' => 'cms/administration/',
            'media/blog/' => 'cms/blog/',
            'media/green-campus/' => 'cms/green-campus/',
            'media/graduation-2026-thumbnail.jpg' => 'cms/videos/thumbnails/graduation-2026-thumbnail.jpg',
            'media/graduation-2026.mp4' => 'cms/videos/files/graduation-2026.mp4',
            'media/' => 'cms/media-library/',
        ];

        $privateMappings = [
            'private/application-documents/' => 'applications/',
            'private/application-fees/' => 'applications/',
            'private/contract-payments/' => 'applications/',
            'private/service-fees/' => 'applications/',
            'admissions/' => 'generated/admissions/',
            'contracts/' => 'generated/contracts/',
            'enrollments/' => 'generated/enrollments/',
            'prikazes/' => 'generated/prikazes/',
        ];

        DB::transaction(function () use ($publicMappings): void {
            $this->organizePublicFiles($publicMappings);
            $this->organizePrivateFiles();
            $this->updatePublicDatabasePaths($publicMappings);
            $this->updateGreenCampusGalleryPaths($publicMappings);
            $this->updatePrivateDatabasePaths();
            $this->removeEmptyDirectories(storage_path('app/public'));
            $this->removeEmptyDirectories(storage_path('app/private'));
        });

        $this->info('BSTU storage structure organized successfully.');

        return self::SUCCESS;
    }

    private function organizePublicFiles(array $mappings): void
    {
        foreach ($this->files(storage_path('app/public')) as $absolutePath) {
            $relativePath = $this->relativePath(storage_path('app/public'), $absolutePath);
            if ($relativePath === '.gitignore' || str_starts_with($relativePath, 'cms/')) {
                continue;
            }

            $newRelativePath = $this->mapPublicPath($relativePath, $mappings);
            if ($newRelativePath !== $relativePath) {
                $this->moveFile(storage_path('app/public/'.$relativePath), storage_path('app/public/'.$newRelativePath));
            }
        }
    }

    private function organizePrivateFiles(): void
    {
        foreach ($this->files(storage_path('app/private')) as $absolutePath) {
            $relativePath = $this->relativePath(storage_path('app/private'), $absolutePath);
            if ($relativePath === '.gitignore' || str_starts_with($relativePath, 'applications/') || str_starts_with($relativePath, 'generated/')) {
                continue;
            }

            $newRelativePath = $this->mapPrivatePath($relativePath);
            if ($newRelativePath !== $relativePath) {
                $this->moveFile(storage_path('app/private/'.$relativePath), storage_path('app/private/'.$newRelativePath));
            }
        }
    }

    private function updatePublicDatabasePaths(array $mappings): void
    {
        $columns = [
            ['about_pages', 'identity_image'],
            ['administration_profiles', 'photo'],
            ['announcements', 'image'],
            ['blogs', 'image'],
            ['blogs', 'author_image'],
            ['departments', 'image'],
            ['faculties', 'image'],
            ['green_campus_articles', 'image'],
            ['media', 'path'],
            ['news', 'image'],
            ['programs', 'image'],
            ['services', 'image'],
            ['staff_profiles', 'photo'],
            ['university_centers', 'image'],
            ['videos', 'thumbnail'],
        ];

        foreach ($columns as [$table, $column]) {
            if (! $this->tableHasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->whereNotNull($column)
                ->orderBy('id')
                ->select('id', $column)
                ->chunkById(200, function ($rows) use ($table, $column, $mappings): void {
                    foreach ($rows as $row) {
                        $value = $row->{$column};
                        if (! is_string($value) || $value === '' || str_starts_with($value, 'http') || str_starts_with($value, '/')) {
                            continue;
                        }

                        $mapped = $this->mapPublicPath($value, $mappings);
                        if ($mapped !== $value) {
                            DB::table($table)->where('id', $row->id)->update([$column => $mapped]);
                        }
                    }
                });
        }
    }

    private function updatePrivateDatabasePaths(): void
    {
        $columns = [
            ['application_documents', 'file_path'],
            ['application_fee_payments', 'receipt_path'],
            ['payments', 'receipt_path'],
            ['service_fee_payments', 'receipt_path'],
            ['admissions', 'document_path'],
            ['contracts', 'document_path'],
            ['enrollments', 'document_path'],
            ['prikazes', 'document_path'],
        ];

        foreach ($columns as [$table, $column]) {
            if (! $this->tableHasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->whereNotNull($column)
                ->orderBy('id')
                ->select('id', $column)
                ->chunkById(200, function ($rows) use ($table, $column): void {
                    foreach ($rows as $row) {
                        $value = $row->{$column};
                        if (! is_string($value) || $value === '') {
                            continue;
                        }

                        $mapped = $this->mapPrivatePath($value);
                        if ($mapped !== $value) {
                            DB::table($table)->where('id', $row->id)->update([$column => $mapped]);
                        }
                    }
                });
        }
    }

    private function updateGreenCampusGalleryPaths(array $mappings): void
    {
        if (! $this->tableHasColumn('green_campus_articles', 'gallery')) {
            return;
        }

        DB::table('green_campus_articles')
            ->whereNotNull('gallery')
            ->orderBy('id')
            ->select('id', 'gallery')
            ->chunkById(200, function ($rows) use ($mappings): void {
                foreach ($rows as $row) {
                    $gallery = is_string($row->gallery) ? json_decode($row->gallery, true) : null;

                    if (! is_array($gallery)) {
                        continue;
                    }

                    $mapped = array_map(function ($value) use ($mappings) {
                        if (! is_string($value) || $value === '' || str_starts_with($value, 'http') || str_starts_with($value, '/')) {
                            return $value;
                        }

                        return $this->mapPublicPath($value, $mappings);
                    }, $gallery);

                    if ($mapped !== $gallery) {
                        DB::table('green_campus_articles')
                            ->where('id', $row->id)
                            ->update(['gallery' => json_encode($mapped, JSON_UNESCAPED_SLASHES)]);
                    }
                }
            });
    }

    private function mapPublicPath(string $path, array $mappings): string
    {
        $path = $this->normalize($path);

        if (preg_match('#^uploads/images/(\d{4})-(\d{2})-(\d{2})/(.+)$#', $path, $match)) {
            return "cms/uploads/images/{$match[1]}/{$match[2]}/{$match[3]}/{$match[4]}";
        }

        foreach ($mappings as $from => $to) {
            if ($path === rtrim($from, '/') || str_starts_with($path, $from)) {
                return $to.substr($path, strlen($from));
            }
        }

        return $path;
    }

    private function mapPrivatePath(string $path): string
    {
        $path = $this->normalize($path);

        if (preg_match('#^private/application-documents/([^/]+)/(.+)$#', $path, $match)) {
            return "applications/{$match[1]}/documents/{$match[2]}";
        }

        if (preg_match('#^private/application-fees/([^/]+)/(.+)$#', $path, $match)) {
            return "applications/{$match[1]}/receipts/application-fees/{$match[2]}";
        }

        if (preg_match('#^private/contract-payments/([^/]+)/(.+)$#', $path, $match)) {
            return "applications/{$match[1]}/receipts/contract-payments/{$match[2]}";
        }

        if (preg_match('#^private/service-fees/([^/]+)/(.+)$#', $path, $match)) {
            return "applications/{$match[1]}/receipts/service-fees/{$match[2]}";
        }

        foreach (['admissions', 'contracts', 'enrollments', 'prikazes'] as $type) {
            if (str_starts_with($path, "{$type}/")) {
                return "generated/{$type}/".substr($path, strlen($type) + 1);
            }
        }

        return $path;
    }

    private function moveFile(string $from, string $to): void
    {
        if (! is_file($from)) {
            return;
        }

        File::ensureDirectoryExists(dirname($to));

        if (! file_exists($to)) {
            File::move($from, $to);

            return;
        }

        if (filesize($from) === filesize($to)) {
            File::delete($from);
        }
    }

    private function removeEmptyDirectories(string $root): void
    {
        do {
            $removed = false;
            $directories = collect(File::allDirectories($root))
                ->sortByDesc(fn (string $directory) => substr_count($directory, DIRECTORY_SEPARATOR));

            foreach ($directories as $directory) {
                if (is_dir($directory) && count(scandir($directory) ?: []) === 2) {
                    @rmdir($directory);
                    $removed = true;
                }
            }
        } while ($removed);
    }

    private function files(string $root): array
    {
        if (! is_dir($root)) {
            return [];
        }

        return array_map(
            fn ($file) => $file->getPathname(),
            iterator_to_array(File::allFiles($root, true))
        );
    }

    private function relativePath(string $root, string $path): string
    {
        return $this->normalize(substr($path, strlen(rtrim($root, DIRECTORY_SEPARATOR)) + 1));
    }

    private function normalize(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return DB::getSchemaBuilder()->hasColumn($table, $column);
    }
}
