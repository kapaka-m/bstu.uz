<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/translations.json');
        if (! file_exists($filePath)) {
            return;
        }

        $translations = json_decode(file_get_contents($filePath), true);

        // 1. Home Page
        $home = Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'template' => 'home',
                'is_published' => true,
                'sort_order' => 1,
            ]
        );

        foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
            $t = $translations[$locale] ?? [];
            $title = $t['nav']['home'] ?? 'Home';
            $content = '';
            $metaTitle = $t['page']['home_title'] ?? ($t['home']['hero']['title'] ?? 'Bukhara State Technical University');
            $metaDesc = $t['home']['hero']['subtitle'] ?? '';

            PageTranslation::updateOrCreate(
                [
                    'page_id' => $home->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $title,
                    'content' => $content,
                    'meta_title' => $metaTitle,
                    'meta_description' => mb_substr($metaDesc, 0, 200, 'UTF-8'),
                ]
            );
        }
    }
}
