<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('home_sections')
            || ! Schema::hasTable('home_section_items')
            || ! Schema::hasTable('home_section_item_translations')
        ) {
            return;
        }

        DB::transaction(function () {
            $sectionId = $this->programsSectionId();

            foreach ($this->items() as $index => $item) {
                $itemId = DB::table('home_section_items')
                    ->where('home_section_id', $sectionId)
                    ->where('item_key', $item['item_key'])
                    ->value('id');

                $payload = [
                    'home_section_id' => $sectionId,
                    'item_key' => $item['item_key'],
                    'icon' => null,
                    'value' => null,
                    'suffix' => null,
                    'url' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'settings' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ];

                if ($itemId) {
                    DB::table('home_section_items')->where('id', $itemId)->update($payload);
                } else {
                    $itemId = DB::table('home_section_items')->insertGetId($payload + ['created_at' => now()]);
                }

                foreach ($item['labels'] as $locale => $label) {
                    DB::table('home_section_item_translations')->updateOrInsert(
                        ['home_section_item_id' => $itemId, 'locale' => $locale],
                        [
                            'title' => null,
                            'description' => null,
                            'label' => $label,
                            'action_label' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function programsSectionId(): int
    {
        $sectionId = DB::table('home_sections')->where('section_key', 'programs')->value('id');
        if ($sectionId) {
            return (int) $sectionId;
        }

        return (int) DB::table('home_sections')->insertGetId([
            'section_key' => 'programs',
            'section_type' => 'dynamic_programs',
            'sort_order' => 80,
            'is_active' => true,
            'settings' => json_encode(['cta_url' => '/programs'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function items(): array
    {
        return [
            [
                'item_key' => 'explore_label',
                'labels' => [
                    'en' => 'Explore',
                    'uz' => 'Ko‘rish',
                    'ru' => 'Подробнее',
                    'ar' => 'استكشف',
                ],
            ],
            [
                'item_key' => 'degree_bachelor',
                'labels' => [
                    'en' => 'Bachelor',
                    'uz' => 'Bakalavr',
                    'ru' => 'Бакалавриат',
                    'ar' => 'بكالوريوس',
                ],
            ],
            [
                'item_key' => 'degree_master',
                'labels' => [
                    'en' => 'Master',
                    'uz' => 'Magistratura',
                    'ru' => 'Магистратура',
                    'ar' => 'ماجستير',
                ],
            ],
            [
                'item_key' => 'degree_phd',
                'labels' => [
                    'en' => 'PhD',
                    'uz' => 'PhD',
                    'ru' => 'PhD',
                    'ar' => 'دكتوراه',
                ],
            ],
            [
                'item_key' => 'duration_years2',
                'labels' => [
                    'en' => '2 years',
                    'uz' => '2 yil',
                    'ru' => '2 года',
                    'ar' => 'سنتان',
                ],
            ],
            [
                'item_key' => 'duration_years4',
                'labels' => [
                    'en' => '4 years',
                    'uz' => '4 yil',
                    'ru' => '4 года',
                    'ar' => '4 سنوات',
                ],
            ],
            [
                'item_key' => 'duration_years5',
                'labels' => [
                    'en' => '5 years',
                    'uz' => '5 yil',
                    'ru' => '5 лет',
                    'ar' => '5 سنوات',
                ],
            ],
        ];
    }
};
