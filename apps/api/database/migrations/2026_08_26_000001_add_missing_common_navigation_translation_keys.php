<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $entries = [
            'common.logoAlt' => [
                'description' => 'Accessible alt text for the public site logo.',
                'values' => [
                    'en' => 'BSTU logo',
                    'uz' => 'BDTU logotipi',
                    'ru' => 'Логотип БГТУ',
                    'ar' => 'شعار جامعة بخارى التقنية الحكومية',
                ],
            ],
            'common.toggleMobileMenu' => [
                'description' => 'Accessible label for opening or closing the mobile navigation menu.',
                'values' => [
                    'en' => 'Toggle mobile menu',
                    'uz' => 'Mobil menyuni ochish yoki yopish',
                    'ru' => 'Открыть или закрыть мобильное меню',
                    'ar' => 'فتح أو إغلاق قائمة الهاتف',
                ],
            ],
            'common.allFaculties' => [
                'description' => 'Programs filter label for all faculties.',
                'values' => [
                    'en' => 'All Faculties',
                    'uz' => 'Barcha fakultetlar',
                    'ru' => 'Все факультеты',
                    'ar' => 'جميع الكليات',
                ],
            ],
        ];

        foreach ($entries as $key => $entry) {
            $translationKeyId = DB::table('translation_keys')->where('key', $key)->value('id');

            if (! $translationKeyId) {
                $translationKeyId = DB::table('translation_keys')->insertGetId([
                    'group' => 'common',
                    'key' => $key,
                    'description' => $entry['description'],
                    'is_system' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('translation_keys')->where('id', $translationKeyId)->update([
                    'group' => 'common',
                    'description' => $entry['description'],
                    'is_system' => true,
                    'updated_at' => $now,
                ]);
            }

            foreach ($entry['values'] as $locale => $value) {
                DB::table('translation_values')->updateOrInsert(
                    [
                        'translation_key_id' => $translationKeyId,
                        'locale' => $locale,
                    ],
                    [
                        'value' => $value,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }
    }

    public function down(): void
    {
        $keys = [
            'common.logoAlt',
            'common.toggleMobileMenu',
            'common.allFaculties',
        ];

        $ids = DB::table('translation_keys')->whereIn('key', $keys)->pluck('id');

        DB::table('translation_values')->whereIn('translation_key_id', $ids)->delete();
        DB::table('translation_keys')->whereIn('id', $ids)->delete();
    }
};
