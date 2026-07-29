<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageBlockTranslation;
use Database\Seeders\Concerns\ResolvesSeedLocales;
use Illuminate\Database\Seeder;

class PageBlockSeeder extends Seeder
{
    use ResolvesSeedLocales;
    public function run(): void
    {
        $filePath = database_path('data/translations.json');
        if (! file_exists($filePath)) {
            return;
        }

        $translations = json_decode(file_get_contents($filePath), true);
        $homePage = Page::where('slug', 'home')->first();

        if ($homePage) {
            // 1. Hero Block
            $heroBlock = PageBlock::firstOrCreate(
                [
                    'page_id' => $homePage->id,
                    'block_key' => 'home_hero',
                ],
                [
                    'type' => 'hero',
                    'sort_order' => 1,
                    'settings_json' => ['bg_image' => 'cms/home/hero/hero-bg.png'],
                    'is_active' => true,
                ]
            );

            foreach ($this->activeSeedLocales() as $locale) {
                $t = $translations[$locale] ?? [];

                $title = $t['home']['hero']['title'] ?? null;
                if ($title === null || $title === '') {
                    continue;
                }

                $subtitle = $t['home']['hero']['subtitle'] ?? null;
                $content = $t['home']['hero']['badge'] ?? null;
                $buttonText = $t['common']['applyNow'] ?? null;

                PageBlockTranslation::firstOrCreate(
                    [
                        'page_block_id' => $heroBlock->id,
                        'locale' => $locale,
                    ],
                    [
                        'title' => $title,
                        'subtitle' => $subtitle,
                        'content' => $content,
                        'button_text' => $buttonText,
                    ]
                );
            }
        }
    }
}
