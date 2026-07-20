<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AnnouncementTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $translationsPath = database_path('data/translations.json');
        $announcementsPath = database_path('data/announcements.json');

        if (! file_exists($translationsPath) || ! file_exists($announcementsPath)) {
            return;
        }

        $translations = json_decode(file_get_contents($translationsPath), true);
        $annMetadata = json_decode(file_get_contents($announcementsPath), true);

        usort($annMetadata, fn ($a, $b) => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));

        foreach ($annMetadata as $index => $meta) {
            $slug = $meta['id'];
            $image = $meta['image'] ?? null;
            if ($image) {
                $image = str_replace('https://old.bstu.uz/', '', $image);
            }

            $dateStr = $meta['date'] ?? null;
            $startsAt = null;
            if ($dateStr) {
                try {
                    $startsAt = Carbon::parse($dateStr);
                } catch (\Exception $e) {
                    $startsAt = now();
                }
            }

            $annModel = Announcement::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => strtolower($meta['category'] ?? 'general'),
                    'priority' => $index < 3 ? 'high' : 'normal',
                    'image' => $image,
                    'starts_at' => $startsAt ?: now(),
                    'ends_at' => $startsAt ? $startsAt->copy()->addDays(60) : now()->addDays(60),
                    'is_published' => true,
                    'views_count' => (int) ($meta['views'] ?? 0),
                ]
            );

            // Seed translations for en, uz, ru, ar
            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $localeAnnouncements = $locale === 'uz'
                    ? ($translations['announcements'] ?? [])
                    : ($translations[$locale]['announcements'] ?? []);
                $annTrans = $localeAnnouncements['items'][$slug] ?? [];

                $title = $annTrans['title'] ?? '';
                $summary = $annTrans['excerpt'] ?? '';

                $paras = $annTrans['paragraphs'] ?? [];
                $content = is_array($paras) ? implode("\n\n", $paras) : $paras;

                // Fallbacks
                if (empty($title)) {
                    $title = $translations['en']['announcements']['items'][$slug]['title'] ?? str_replace('-', ' ', $slug);
                }
                if (empty($summary)) {
                    $summary = $translations['en']['announcements']['items'][$slug]['excerpt'] ?? '';
                }
                if (empty($content)) {
                    $enParas = $translations['en']['announcements']['items'][$slug]['paragraphs'] ?? [];
                    $content = is_array($enParas) ? implode("\n\n", $enParas) : $enParas;
                }

                AnnouncementTranslation::updateOrCreate(
                    [
                        'announcement_id' => $annModel->id,
                        'locale' => $locale,
                    ],
                    [
                        'category_label' => $translations[$locale]['announcements']['categories'][strtolower($meta['category'] ?? 'announcements')]
                            ?? $localeAnnouncements['categories'][strtolower($meta['category'] ?? 'announcements')]
                            ?? ucfirst(strtolower($meta['category'] ?? 'announcements')),
                        'title' => $title,
                        'summary' => $summary,
                        'content' => $content,
                    ]
                );
            }
        }
    }
}
