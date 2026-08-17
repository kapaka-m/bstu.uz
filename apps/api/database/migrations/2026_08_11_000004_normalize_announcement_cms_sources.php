<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settingId = DB::table('announcement_settings')->where('key', 'main')->value('id');

        $payload = [
            'key' => 'main',
            'home_limit' => 4,
            'recent_limit' => 5,
            'important_limit' => 3,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($settingId) {
            DB::table('announcement_settings')->where('id', $settingId)->update($payload);
        } else {
            $settingId = DB::table('announcement_settings')->insertGetId($payload + ['created_at' => now()]);
        }

        foreach ($this->translations() as $locale => $translation) {
            DB::table('announcement_setting_translations')->updateOrInsert(
                ['announcement_setting_id' => $settingId, 'locale' => $locale],
                $translation + ['created_at' => now(), 'updated_at' => now()]
            );
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function translations(): array
    {
        return [
            'en' => [
                'home_tag' => 'Announcements',
                'home_title' => 'Latest Announcements',
                'view_all_label' => 'All Announcements',
                'read_details_label' => 'Read Details',
                'search_title' => 'Search Announcements',
                'search_placeholder' => 'Search announcements...',
                'categories_title' => 'Categories',
                'recent_title' => 'Recent Announcements',
                'all_label' => 'All',
                'views_label' => 'views',
                'important_label' => 'Important',
                'loading_label' => 'Loading announcements...',
                'no_results_label' => 'No announcements found',
                'clear_filters_label' => 'Clear search filters',
                'share_label' => 'Share',
                'copy_link_label' => 'Copy Link',
                'copied_label' => 'Copied!',
                'published_by_label' => 'Published By',
                'publisher_name' => 'BSTU Administration',
            ],
            'uz' => [
                'home_tag' => 'Eʼlonlar',
                'home_title' => 'So‘nggi eʼlonlar',
                'view_all_label' => 'Barcha eʼlonlar',
                'read_details_label' => 'Batafsil o‘qish',
                'search_title' => 'Eʼlonlarni qidirish',
                'search_placeholder' => 'Eʼlonlarni qidirish...',
                'categories_title' => 'Toifalar',
                'recent_title' => 'So‘nggi eʼlonlar',
                'all_label' => 'Barchasi',
                'views_label' => 'ko‘rish',
                'important_label' => 'Muhim',
                'loading_label' => 'Eʼlonlar yuklanmoqda...',
                'no_results_label' => 'Eʼlonlar topilmadi',
                'clear_filters_label' => 'Qidiruv filtrlarini tozalash',
                'share_label' => 'Ulashish',
                'copy_link_label' => 'Havolani nusxalash',
                'copied_label' => 'Nusxalandi!',
                'published_by_label' => 'Nashr etdi',
                'publisher_name' => 'BuxDTU maʼmuriyati',
            ],
            'ru' => [
                'home_tag' => 'Объявления',
                'home_title' => 'Последние объявления',
                'view_all_label' => 'Все объявления',
                'read_details_label' => 'Подробнее',
                'search_title' => 'Поиск объявлений',
                'search_placeholder' => 'Искать объявления...',
                'categories_title' => 'Категории',
                'recent_title' => 'Свежие объявления',
                'all_label' => 'Все',
                'views_label' => 'просмотров',
                'important_label' => 'Важно',
                'loading_label' => 'Загрузка объявлений...',
                'no_results_label' => 'Объявления не найдены',
                'clear_filters_label' => 'Очистить фильтры',
                'share_label' => 'Поделиться',
                'copy_link_label' => 'Скопировать ссылку',
                'copied_label' => 'Скопировано!',
                'published_by_label' => 'Опубликовано',
                'publisher_name' => 'Администрация БухГТУ',
            ],
            'ar' => [
                'home_tag' => 'الإعلانات',
                'home_title' => 'أحدث الإعلانات',
                'view_all_label' => 'كل الإعلانات',
                'read_details_label' => 'قراءة التفاصيل',
                'search_title' => 'بحث الإعلانات',
                'search_placeholder' => 'ابحث في الإعلانات...',
                'categories_title' => 'التصنيفات',
                'recent_title' => 'أحدث الإعلانات',
                'all_label' => 'الكل',
                'views_label' => 'مشاهدة',
                'important_label' => 'مهم',
                'loading_label' => 'جاري تحميل الإعلانات...',
                'no_results_label' => 'لا توجد إعلانات',
                'clear_filters_label' => 'مسح عوامل البحث',
                'share_label' => 'مشاركة',
                'copy_link_label' => 'نسخ الرابط',
                'copied_label' => 'تم النسخ!',
                'published_by_label' => 'نشر بواسطة',
                'publisher_name' => 'إدارة الجامعة',
            ],
        ];
    }
};
