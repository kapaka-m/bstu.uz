<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->syncHomeSection(
            'hero',
            'hero',
            10,
            [
                'cta_url' => '/apply',
                'video_url' => 'cms/videos/files/graduation-2026.mp4',
                'image' => 'cms/home/hero/hero-university.jpg',
                'background_image' => 'cms/home/hero/hero-bg.png',
            ],
            [
                'en' => [
                    'title' => 'Bukhara State Technical University',
                    'subtitle' => 'Empowering international students with accredited engineering programs, applied research opportunities, and dedicated academic support.',
                    'cta_label' => 'Apply Now',
                    'secondary_title' => 'Watch video',
                    'image_alt' => 'University campus',
                ],
                'uz' => [
                    'title' => 'Buxoro davlat texnika universiteti',
                    'subtitle' => 'Xalqaro talabalar uchun akkreditatsiyadan o‘tgan muhandislik dasturlari, amaliy tadqiqot imkoniyatlari va maxsus akademik yordam.',
                    'cta_label' => 'Ariza topshirish',
                    'secondary_title' => 'Videoni ko‘rish',
                    'image_alt' => 'Universitet kampusi',
                ],
                'ru' => [
                    'title' => 'Бухарский государственный технический университет',
                    'subtitle' => 'Аккредитованные инженерные программы, прикладные исследования и академическая поддержка для иностранных студентов.',
                    'cta_label' => 'Подать заявку',
                    'secondary_title' => 'Смотреть видео',
                    'image_alt' => 'Кампус университета',
                ],
                'ar' => [
                    'title' => 'جامعة بخارى الحكومية التقنية',
                    'subtitle' => 'برامج هندسية معتمدة وفرص بحث تطبيقي ودعم أكاديمي مخصص للطلاب الدوليين.',
                    'cta_label' => 'قدّم الآن',
                    'secondary_title' => 'شاهد الفيديو',
                    'image_alt' => 'حرم الجامعة',
                ],
            ],
            [
                'student_count' => [
                    'value' => '15,000',
                    'suffix' => '+',
                    'sort_order' => 1,
                    'translations' => [
                        'en' => ['label' => 'Active students'],
                        'uz' => ['label' => 'Faol talabalar'],
                        'ru' => ['label' => 'Активные студенты'],
                        'ar' => ['label' => 'طلاب نشطون'],
                    ],
                ],
                'accreditation' => [
                    'sort_order' => 2,
                    'translations' => [
                        'en' => ['title' => 'Accredited', 'label' => 'State programs'],
                        'uz' => ['title' => 'Akkreditatsiyadan o‘tgan', 'label' => 'Davlat dasturlari'],
                        'ru' => ['title' => 'Аккредитовано', 'label' => 'Государственные программы'],
                        'ar' => ['title' => 'معتمدة', 'label' => 'برامج حكومية'],
                    ],
                ],
            ],
        );

        $this->syncHomeSection(
            'programs',
            'dynamic_programs',
            80,
            ['cta_url' => '/programs'],
            [
                'en' => ['eyebrow' => 'Academic programs', 'title' => 'Choose your study program', 'cta_label' => 'View all programs'],
                'uz' => ['eyebrow' => 'Akademik dasturlar', 'title' => 'O‘quv dasturingizni tanlang', 'cta_label' => 'Barcha dasturlar'],
                'ru' => ['eyebrow' => 'Академические программы', 'title' => 'Выберите программу обучения', 'cta_label' => 'Все программы'],
                'ar' => ['eyebrow' => 'البرامج الأكاديمية', 'title' => 'اختر برنامجك الدراسي', 'cta_label' => 'عرض كل البرامج'],
            ],
            [
                'explore_label' => [
                    'sort_order' => 1,
                    'translations' => [
                        'en' => ['label' => 'Explore'],
                        'uz' => ['label' => 'Ko‘rish'],
                        'ru' => ['label' => 'Подробнее'],
                        'ar' => ['label' => 'استكشف'],
                    ],
                ],
            ],
        );

        DB::table('settings')
            ->whereIn('key', [
                'home_hero_background_image',
                'home_hero_main_image',
                'home_hero_student_count',
            ])
            ->update(['is_public' => false, 'updated_at' => now()]);

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('key', [
                'home_hero_background_image',
                'home_hero_main_image',
                'home_hero_student_count',
            ])
            ->update(['is_public' => true, 'updated_at' => now()]);

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    private function syncHomeSection(
        string $sectionKey,
        string $sectionType,
        int $sortOrder,
        array $settings,
        array $translations,
        array $items = [],
    ): void {
        $existing = DB::table('home_sections')->where('section_key', $sectionKey)->first();

        if ($existing) {
            DB::table('home_sections')->where('id', $existing->id)->update([
                'section_type' => $sectionType,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'settings' => json_encode(array_merge((array) json_decode($existing->settings ?: '{}', true), $settings), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
            $sectionId = (int) $existing->id;
        } else {
            $sectionId = (int) DB::table('home_sections')->insertGetId([
                'section_key' => $sectionKey,
                'section_type' => $sectionType,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'settings' => json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($translations as $locale => $fields) {
            DB::table('home_section_translations')->updateOrInsert(
                ['home_section_id' => $sectionId, 'locale' => $locale],
                array_merge([
                    'eyebrow' => null,
                    'title' => null,
                    'subtitle' => null,
                    'description' => null,
                    'secondary_title' => null,
                    'secondary_description' => null,
                    'cta_label' => null,
                    'cta_url' => null,
                    'image_alt' => null,
                    'created_at' => now(),
                ], $fields, ['updated_at' => now()])
            );
        }

        foreach ($items as $itemKey => $item) {
            $existingItem = DB::table('home_section_items')
                ->where('home_section_id', $sectionId)
                ->where('item_key', $itemKey)
                ->first();

            $itemPayload = [
                'home_section_id' => $sectionId,
                'item_key' => $itemKey,
                'icon' => $item['icon'] ?? null,
                'value' => $item['value'] ?? null,
                'suffix' => $item['suffix'] ?? null,
                'url' => $item['url'] ?? null,
                'sort_order' => $item['sort_order'] ?? 0,
                'is_active' => true,
                'settings' => json_encode($item['settings'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ];

            if ($existingItem) {
                DB::table('home_section_items')->where('id', $existingItem->id)->update($itemPayload);
                $itemId = (int) $existingItem->id;
            } else {
                $itemId = (int) DB::table('home_section_items')->insertGetId($itemPayload + ['created_at' => now()]);
            }

            foreach (($item['translations'] ?? []) as $locale => $fields) {
                DB::table('home_section_item_translations')->updateOrInsert(
                    ['home_section_item_id' => $itemId, 'locale' => $locale],
                    array_merge([
                        'title' => null,
                        'description' => null,
                        'label' => null,
                        'action_label' => null,
                        'created_at' => now(),
                    ], $fields, ['updated_at' => now()])
                );
            }
        }
    }
};
