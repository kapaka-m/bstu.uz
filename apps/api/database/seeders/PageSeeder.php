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
            $content = $t['about']['body'] ?? 'Welcome to Bukhara State Technical University International website.';
            $metaTitle = $t['page']['home_title'] ?? ($t['home']['hero']['title'] ?? 'Bukhara State Technical University');
            $metaDesc = $t['about']['body'] ?? 'Providing premium education and global technical solutions.';

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

        // 2. About Page
        $about = Page::updateOrCreate(
            ['slug' => 'about'],
            [
                'template' => 'about',
                'is_published' => true,
                'sort_order' => 2,
            ]
        );

        foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
            $t = $translations[$locale] ?? [];
            $title = $t['nav']['aboutUs'] ?? 'About Us';
            $content = $t['about']['body'] ?? 'BSTU is a leading higher education institution in Uzbekistan.';
            $metaTitle = $t['page']['about_title'] ?? ($t['about']['title'] ?? 'About BSTU International');
            $metaDesc = $t['about']['body'] ?? 'Discover the history, mission, and achievements of BSTU.';

            PageTranslation::updateOrCreate(
                [
                    'page_id' => $about->id,
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
