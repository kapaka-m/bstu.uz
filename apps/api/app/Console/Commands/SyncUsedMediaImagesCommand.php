<?php

namespace App\Console\Commands;

use App\Services\UsedMediaImageService;
use Illuminate\Console\Command;

class SyncUsedMediaImagesCommand extends Command
{
    protected $signature = 'media:sync-used-images {--dry-run : Only report used image counts without creating media rows}';

    protected $description = 'Register public images currently used by project content in the media library.';

    public function handle(UsedMediaImageService $usedMedia): int
    {
        if ($this->option('dry-run')) {
            $paths = $usedMedia->collectPublicImagePaths();
            $this->info('Used public images found: '.count($paths));
            foreach (array_slice($paths, 0, 20) as $path) {
                $this->line(' - '.$path);
            }
            if (count($paths) > 20) {
                $this->line('...and '.(count($paths) - 20).' more.');
            }

            return self::SUCCESS;
        }

        $result = $usedMedia->syncToMedia();

        $this->info("Used public images found: {$result['used']}");
        $this->info("Media rows created: {$result['created']}");
        $this->info("Already registered: {$result['existing']}");

        return self::SUCCESS;
    }
}
