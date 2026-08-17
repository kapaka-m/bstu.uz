<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settingId = DB::table('news_event_settings')->where('key', 'main')->value('id');

        $payload = [
            'key' => 'main',
            'home_limit' => 4,
            'recent_limit' => 5,
            'home_icon' => 'newspaper',
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($settingId) {
            DB::table('news_event_settings')->where('id', $settingId)->update($payload);
        } else {
            $settingId = DB::table('news_event_settings')->insertGetId($payload + ['created_at' => now()]);
        }

        foreach ($this->translations() as $locale => $translation) {
            DB::table('news_event_setting_translations')->updateOrInsert(
                ['news_event_setting_id' => $settingId, 'locale' => $locale],
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
                'home_tag' => 'News & Events',
                'home_title' => 'Latest News',
                'home_subtitle' => 'Stay informed with the latest happenings, academic events, and achievements at BSTU.',
                'view_all_label' => 'View All News',
                'read_details_label' => 'Read Details',
                'search_title' => 'Search News',
                'search_placeholder' => 'Search news...',
                'categories_title' => 'News Categories',
                'recent_title' => 'Recent News',
                'all_news_label' => 'All News',
                'news_label' => 'News',
                'events_label' => 'Events',
                'views_label' => 'Views',
                'loading_label' => 'Loading news...',
                'no_results_label' => 'No news found.',
                'clear_filters_label' => 'Clear filters',
            ],
            'uz' => [
                'home_tag' => 'Yangiliklar va tadbirlar',
                'home_title' => 'So‘nggi yangiliklar',
                'home_subtitle' => 'BDTUdagi so‘nggi voqealar, akademik tadbirlar va yutuqlar haqida xabardor bo‘ling.',
                'view_all_label' => 'Barcha yangiliklar',
                'read_details_label' => 'Batafsil o‘qish',
                'search_title' => 'Yangiliklarni qidirish',
                'search_placeholder' => 'Yangiliklarni qidirish...',
                'categories_title' => 'Yangiliklar toifalari',
                'recent_title' => 'So‘nggi yangiliklar',
                'all_news_label' => 'Barcha yangiliklar',
                'news_label' => 'Yangiliklar',
                'events_label' => 'Tadbirlar',
                'views_label' => 'Ko‘rishlar',
                'loading_label' => 'Yangiliklar yuklanmoqda...',
                'no_results_label' => 'Yangiliklar topilmadi.',
                'clear_filters_label' => 'Filtrlarni tozalash',
            ],
            'ru' => [
                'home_tag' => 'Новости и события',
                'home_title' => 'Последние новости',
                'home_subtitle' => 'Будьте в курсе последних событий, академических мероприятий и достижений БГТУ.',
                'view_all_label' => 'Все новости',
                'read_details_label' => 'Подробнее',
                'search_title' => 'Поиск новостей',
                'search_placeholder' => 'Искать новости...',
                'categories_title' => 'Категории новостей',
                'recent_title' => 'Недавние новости',
                'all_news_label' => 'Все новости',
                'news_label' => 'Новости',
                'events_label' => 'События',
                'views_label' => 'Просмотры',
                'loading_label' => 'Загрузка новостей...',
                'no_results_label' => 'Новости не найдены.',
                'clear_filters_label' => 'Сбросить фильтры',
            ],
            'ar' => [
                'home_tag' => 'الأخبار والفعاليات',
                'home_title' => 'آخر الأخبار',
                'home_subtitle' => 'ابق على اطلاع بآخر المستجدات والفعاليات الأكاديمية والإنجازات في جامعة بخارى التقنية الحكومية.',
                'view_all_label' => 'عرض كل الأخبار',
                'read_details_label' => 'قراءة التفاصيل',
                'search_title' => 'البحث في الأخبار',
                'search_placeholder' => 'ابحث في الأخبار...',
                'categories_title' => 'تصنيفات الأخبار',
                'recent_title' => 'أحدث الأخبار',
                'all_news_label' => 'كل الأخبار',
                'news_label' => 'أخبار',
                'events_label' => 'فعاليات',
                'views_label' => 'المشاهدات',
                'loading_label' => 'جاري تحميل الأخبار...',
                'no_results_label' => 'لم يتم العثور على أخبار.',
                'clear_filters_label' => 'مسح الفلاتر',
            ],
        ];
    }
};
