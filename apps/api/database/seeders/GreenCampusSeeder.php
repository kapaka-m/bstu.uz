<?php

namespace Database\Seeders;

use App\Models\GreenCampusArticle;
use App\Models\GreenCampusArticleTranslation;
use App\Models\GreenCampusStat;
use App\Models\GreenCampusStatTranslation;
use Illuminate\Database\Seeder;

class GreenCampusSeeder extends Seeder
{
    public function run(): void
    {
        $translationsPath = database_path('data/translations.json');
        $greenCampusPath = database_path('data/green_campus.json');

        if (! file_exists($translationsPath) || ! file_exists($greenCampusPath)) {
            return;
        }

        $translations = json_decode(file_get_contents($translationsPath), true);
        $gcMetadata = json_decode(file_get_contents($greenCampusPath), true);

        // 1. Seed Green Campus Stats
        $stats = $gcMetadata['stats'] ?? [];
        foreach ($stats as $index => $meta) {
            $statModel = GreenCampusStat::updateOrCreate(['sort_order' => $index + 1], [
                'icon' => $meta['icon'] ?? 'award',
                'sort_order' => $index + 1,
            ]);

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                // In translations.json, stats are under translations[locale]['greenCampus']['stats'][index]
                $statTrans = $translations[$locale]['greenCampus']['stats'][$index] ?? [];

                $value = $statTrans['value'] ?? ($meta['value'] ?? '');
                $label = $statTrans['label'] ?? ($meta['label'] ?? '');

                if (empty($value)) {
                    $value = $translations['en']['greenCampus']['stats'][$index]['value'] ?? ($meta['value'] ?? '');
                }
                if (empty($label)) {
                    $label = $translations['en']['greenCampus']['stats'][$index]['label'] ?? ($meta['label'] ?? '');
                }

                GreenCampusStatTranslation::updateOrCreate([
                    'green_campus_stat_id' => $statModel->id,
                    'locale' => $locale,
                ], [
                    'green_campus_stat_id' => $statModel->id,
                    'locale' => $locale,
                    'value' => $value,
                    'label' => $label,
                ]);
            }
        }

        // 2. Seed Green Campus Articles
        $articles = $gcMetadata['articles'] ?? [];
        foreach ($articles as $meta) {
            $slug = $meta['id'];
            $image = $meta['image'] ?? null;
            if ($image) {
                $image = str_replace('/assets/img/', 'assets/img/', $image);
            }

            $gallery = [];
            if (! empty($meta['gallery'])) {
                foreach ($meta['gallery'] as $img) {
                    $gallery[] = str_replace('/assets/img/', 'assets/img/', $img);
                }
            }

            $artModel = GreenCampusArticle::updateOrCreate(['slug' => $slug], [
                'slug' => $slug,
                'image' => $image,
                'gallery' => $gallery,
                'views' => intval($meta['views'] ?? 0),
            ]);

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                // In translations.json, articles are under translations[locale]['greenCampus'][slug] or translations[locale]['greenCampus']['articles'][slug]
                $artTrans = $translations[$locale]['greenCampus'][$slug] ?? ($translations[$locale]['greenCampus']['articles'][$slug] ?? []);

                $title = $artTrans['title'] ?? ($meta['title'] ?? '');
                $category = $artTrans['category'] ?? ($meta['category'] ?? '');
                $excerpt = $artTrans['excerpt'] ?? ($meta['excerpt'] ?? '');

                $paras = $artTrans['paragraphs'] ?? ($meta['paragraphs'] ?? []);
                $content = is_array($paras) ? implode("\n\n", $paras) : $paras;
                $author = $artTrans['author'] ?? ($meta['author'] ?? 'BSTU Admin');

                // Fallbacks
                if (empty($title)) {
                    $enTrans = $translations['en']['greenCampus'][$slug] ?? ($translations['en']['greenCampus']['articles'][$slug] ?? []);
                    $title = $enTrans['title'] ?? ($meta['title'] ?? '');
                }
                if (empty($category)) {
                    $enTrans = $translations['en']['greenCampus'][$slug] ?? ($translations['en']['greenCampus']['articles'][$slug] ?? []);
                    $category = $enTrans['category'] ?? ($meta['category'] ?? '');
                }
                if (empty($excerpt)) {
                    $enTrans = $translations['en']['greenCampus'][$slug] ?? ($translations['en']['greenCampus']['articles'][$slug] ?? []);
                    $excerpt = $enTrans['excerpt'] ?? ($meta['excerpt'] ?? '');
                }
                if (empty($content)) {
                    $enTrans = $translations['en']['greenCampus'][$slug] ?? ($translations['en']['greenCampus']['articles'][$slug] ?? []);
                    $enParas = $enTrans['paragraphs'] ?? ($meta['paragraphs'] ?? []);
                    $content = is_array($enParas) ? implode("\n\n", $enParas) : $enParas;
                }

                GreenCampusArticleTranslation::updateOrCreate([
                    'green_campus_article_id' => $artModel->id,
                    'locale' => $locale,
                ], [
                    'green_campus_article_id' => $artModel->id,
                    'locale' => $locale,
                    'title' => $title,
                    'category' => $category,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'author' => $author,
                ]);
            }
        }
    }
}
