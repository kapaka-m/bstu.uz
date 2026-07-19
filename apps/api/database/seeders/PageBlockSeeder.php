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

            // 2. Rector Address Block
            $rectorBlock = PageBlock::updateOrCreate(
                [
                    'page_id' => $homePage->id,
                    'block_key' => 'home_rector',
                ],
                [
                    'type' => 'rector',
                    'sort_order' => 2,
                    'settings_json' => ['photo' => 'assets/img/administration/rector.jpg'],
                    'is_active' => true,
                ]
            );

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $t = $translations[$locale] ?? [];

                $title = $t['about']['rector']['title'] ?? 'Dr. Saddidin M. Turabdjanov';
                $subtitle = $t['about']['rector']['badge'] ?? "Rector's Address";
                $content = $t['about']['rector']['quote'] ?? 'Welcome message from the Rector.';
                $buttonText = $t['about']['rector']['btnText'] ?? 'View Structure';

                PageBlockTranslation::updateOrCreate(
                    [
                        'page_block_id' => $rectorBlock->id,
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

            // 3. Green Campus Block
            $gcBlock = PageBlock::updateOrCreate(
                [
                    'page_id' => $homePage->id,
                    'block_key' => 'home_green_campus',
                ],
                [
                    'type' => 'green_campus',
                    'sort_order' => 3,
                    'settings_json' => ['bg_image' => 'assets/img/green-campus/green_img_34.jpg'],
                    'is_active' => true,
                ]
            );

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $t = $translations[$locale] ?? [];

                $title = $t['greenCampus']['title'] ?? 'Green Campus Leadership in Sustainability';
                $subtitle = $t['greenCampus']['tag'] ?? 'Sustainability';
                $content = $t['greenCampus']['explore'] ?? 'Explore All Green Campus Initiatives';

                PageBlockTranslation::updateOrCreate(
                    [
                        'page_block_id' => $gcBlock->id,
                        'locale' => $locale,
                    ],
                    [
                        'title' => $title,
                        'subtitle' => $subtitle,
                        'content' => $content,
                        'button_text' => 'Read More',
                    ]
                );
            }
        }
    }
}
