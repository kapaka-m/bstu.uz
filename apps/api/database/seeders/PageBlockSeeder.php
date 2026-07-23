<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageBlockTranslation;
use Illuminate\Database\Seeder;

class PageBlockSeeder extends Seeder
{
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
            $heroBlock = PageBlock::updateOrCreate(
                [
                    'page_id' => $homePage->id,
                    'block_key' => 'home_hero',
                ],
                [
                    'type' => 'hero',
                    'sort_order' => 1,
                    'settings_json' => ['bg_image' => 'assets/img/hero-bg.jpg'],
                    'is_active' => true,
                ]
            );

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $t = $translations[$locale] ?? [];

                $title = $t['home']['hero']['title'] ?? 'BSTU International';
                $subtitle = $t['home']['hero']['subtitle'] ?? 'Building global technical leaders';
                $content = $t['home']['hero']['badge'] ?? 'Admissions open for Fall 2026';
                $buttonText = $t['common']['applyNow'] ?? 'Apply Now';

                PageBlockTranslation::updateOrCreate(
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
