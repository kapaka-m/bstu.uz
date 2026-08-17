<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('green_campus_settings')->updateOrInsert(
            ['key' => 'main'],
            [
                'home_limit' => 3,
                'recent_limit' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $settingId = DB::table('green_campus_settings')->where('key', 'main')->value('id');

        if (! $settingId) {
            return;
        }

        foreach ($this->translations() as $locale => $translation) {
            DB::table('green_campus_setting_translations')->updateOrInsert(
                ['green_campus_setting_id' => $settingId, 'locale' => $locale],
                $translation + [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
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
                'home_tag' => 'Sustainability',
                'home_title' => 'Green Campus Leadership in Sustainability',
                'view_all_label' => 'Explore All Green Campus Initiatives',
                'read_more_label' => 'Read More',
                'search_title' => 'Search',
                'search_placeholder' => 'Type to search...',
                'categories_title' => 'Categories',
                'recent_title' => 'Recent Initiatives',
                'all_label' => 'All',
                'no_results_label' => 'No green campus articles found.',
                'callout_title' => 'Green University Project',
                'callout_description' => 'Join our environmental sustainability programs and help build a greener campus.',
                'callout_cta_label' => 'Contact Eco-Committee',
                'callout_email' => 'green@bstu.uz',
                'views_label' => 'views',
                'gallery_label' => 'Photo Gallery',
                'related_label' => 'Related Initiatives',
                'close_viewer_label' => 'Close image viewer',
                'previous_image_label' => 'Previous image',
                'next_image_label' => 'Next image',
                'category_labels' => $this->json([
                    'achievement' => 'Achievement',
                    'education' => 'Education',
                    'community' => 'Community',
                    'nature' => 'Nature',
                ]),
            ],
            'uz' => [
                'home_tag' => 'Barqarorlik',
                'home_title' => 'Yashil kampus: barqarorlik yetakchiligi',
                'view_all_label' => 'Barcha yashil kampus tashabbuslari',
                'read_more_label' => 'Batafsil o‘qish',
                'search_title' => 'Qidirish',
                'search_placeholder' => 'Tashabbuslarni qidiring',
                'categories_title' => 'Kategoriyalar',
                'recent_title' => 'So‘nggi tashabbuslar',
                'all_label' => 'Barchasi',
                'no_results_label' => 'Yashil kampus maqolalari topilmadi.',
                'callout_title' => 'Yashil universitet loyihasi',
                'callout_description' => 'Atrof-muhit barqarorligi dasturlarimizga qo‘shiling va yashil kampusni rivojlantirishga hissa qo‘shing.',
                'callout_cta_label' => 'Eko-qo‘mita bilan bog‘lanish',
                'callout_email' => 'green@bstu.uz',
                'views_label' => 'ko‘rish',
                'gallery_label' => 'Fotogalereya',
                'related_label' => 'Tegishli tashabbuslar',
                'close_viewer_label' => 'Ko‘rishni yopish',
                'previous_image_label' => 'Oldingi rasm',
                'next_image_label' => 'Keyingi rasm',
                'category_labels' => $this->json([
                    'achievement' => 'Yutuq',
                    'education' => 'Ta’lim',
                    'community' => 'Jamiyat',
                    'nature' => 'Tabiat',
                ]),
            ],
            'ru' => [
                'home_tag' => 'Устойчивое развитие',
                'home_title' => 'Лидерство зеленого кампуса в устойчивости',
                'view_all_label' => 'Все инициативы зеленого кампуса',
                'read_more_label' => 'Читать далее',
                'search_title' => 'Поиск',
                'search_placeholder' => 'Введите для поиска...',
                'categories_title' => 'Категории',
                'recent_title' => 'Последние инициативы',
                'all_label' => 'Все',
                'no_results_label' => 'Статьи о зеленом кампусе не найдены.',
                'callout_title' => 'Проект «Зеленый университет»',
                'callout_description' => 'Присоединяйтесь к программам экологической устойчивости и помогите создать зеленый кампус.',
                'callout_cta_label' => 'Связаться с Эко-комитетом',
                'callout_email' => 'green@bstu.uz',
                'views_label' => 'просмотров',
                'gallery_label' => 'Фотогалерея',
                'related_label' => 'Похожие инициативы',
                'close_viewer_label' => 'Закрыть просмотр изображений',
                'previous_image_label' => 'Предыдущее изображение',
                'next_image_label' => 'Следующее изображение',
                'category_labels' => $this->json([
                    'achievement' => 'Достижение',
                    'education' => 'Образование',
                    'community' => 'Сообщество',
                    'nature' => 'Природа',
                ]),
            ],
            'ar' => [
                'home_tag' => 'الاستدامة',
                'home_title' => 'ريادة الحرم الأخضر في الاستدامة',
                'view_all_label' => 'استكشاف جميع مبادرات الحرم الجامعي الأخضر',
                'read_more_label' => 'اقرأ المزيد',
                'search_title' => 'البحث',
                'search_placeholder' => 'اكتب للبحث...',
                'categories_title' => 'الفئات',
                'recent_title' => 'أحدث المبادرات',
                'all_label' => 'الكل',
                'no_results_label' => 'لم يتم العثور على مقالات حول الحرم الجامعي الأخضر.',
                'callout_title' => 'مشروع الجامعة الخضراء',
                'callout_description' => 'انضم إلى برامجنا للاستدامة البيئية وساعد في بناء حرم جامعي أكثر خضرة.',
                'callout_cta_label' => 'اتصل باللجنة البيئية',
                'callout_email' => 'green@bstu.uz',
                'views_label' => 'مشاهدة',
                'gallery_label' => 'معرض الصور',
                'related_label' => 'مبادرات ذات صلة',
                'close_viewer_label' => 'إغلاق عارض الصور',
                'previous_image_label' => 'الصورة السابقة',
                'next_image_label' => 'الصورة التالية',
                'category_labels' => $this->json([
                    'achievement' => 'إنجاز',
                    'education' => 'التعليم',
                    'community' => 'المجتمع',
                    'nature' => 'الطبيعة',
                ]),
            ],
        ];
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
};
