<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('video_gallery_settings')->updateOrInsert(
            ['key' => 'main'],
            [
                'home_limit' => 4,
                'subscriber_count' => 2014,
                'youtube_channel_url' => 'https://www.youtube.com/@BSTU_UZ/videos',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $settingId = DB::table('video_gallery_settings')->where('key', 'main')->value('id');

        if (! $settingId) {
            return;
        }

        foreach ($this->translations() as $locale => $translation) {
            DB::table('video_gallery_setting_translations')->updateOrInsert(
                ['video_gallery_setting_id' => $settingId, 'locale' => $locale],
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
                'home_tag' => 'Video Gallery',
                'home_title' => 'Life at BSTU in Videos',
                'home_subtitle' => 'Watch university reports, campus highlights, achievements, and student life through curated videos.',
                'view_all_label' => 'Video Gallery',
                'recommended_label' => 'Recommended Videos',
                'videos_label' => 'Videos',
                'description_title' => 'Video Description & Highlights',
                'show_more_label' => 'Show more',
                'show_less_label' => 'Show less',
                'like_label' => 'Like',
                'liked_label' => 'Liked',
                'share_label' => 'Share',
                'subscribe_label' => 'Subscribe',
                'subscribed_label' => 'Subscribed',
                'subscribers_label' => 'subscribers',
                'views_label' => 'views',
                'channel_name' => 'Bukhara State Technical University',
                'link_copied_label' => 'Link copied to clipboard.',
                'no_videos_label' => 'No videos are currently available.',
                'comments_label' => 'Comments',
                'reply_label' => 'Reply',
                'form_title' => 'Add a comment',
                'form_comment_label' => 'Write your comment',
                'form_submit_label' => 'Post Comment',
                'sign_in_title' => 'Sign in to comment',
                'sign_in_text' => 'Comments are visible to everyone. Please sign in before adding a comment or reply.',
                'sign_in_action' => 'Sign In',
                'signed_in_as_label' => 'Signed in as',
                'category_label' => 'Category',
                'duration_label' => 'Duration',
                'platform_label' => 'Platform',
                'local_label' => 'Local',
                'youtube_label' => 'YouTube',
                'playing_label' => 'Playing',
                'verified_channel_label' => 'Verified channel',
                'category_labels' => $this->json([
                    'All' => 'All',
                    'Campus Life' => 'Campus Life',
                    'Research' => 'Research',
                    'Admissions' => 'Admissions',
                    'Academics' => 'Academics',
                ]),
            ],
            'uz' => [
                'home_tag' => 'Video galereya',
                'home_title' => 'BuxDTU hayoti videolarda',
                'home_subtitle' => 'Universitet hayoti, yutuqlar, tadbirlar va talabalar faoliyatidan saralangan videolarni tomosha qiling.',
                'view_all_label' => 'Video galereya',
                'recommended_label' => 'Tavsiya etilgan videolar',
                'videos_label' => 'Videolar',
                'description_title' => 'Video tavsifi va asosiy lavhalar',
                'show_more_label' => 'Ko‘proq ko‘rsatish',
                'show_less_label' => 'Kamroq ko‘rsatish',
                'like_label' => 'Yoqdi',
                'liked_label' => 'Yoqtirildi',
                'share_label' => 'Ulashish',
                'subscribe_label' => 'Obuna bo‘lish',
                'subscribed_label' => 'Obuna bo‘lindi',
                'subscribers_label' => 'obunachi',
                'views_label' => 'ko‘rish',
                'channel_name' => 'Buxoro davlat texnika universiteti',
                'link_copied_label' => 'Havola nusxalandi.',
                'no_videos_label' => 'Hozircha video mavjud emas.',
                'comments_label' => 'Izohlar',
                'reply_label' => 'Javob berish',
                'form_title' => 'Izoh qo‘shish',
                'form_comment_label' => 'Izohingizni yozing',
                'form_submit_label' => 'Izoh yuborish',
                'sign_in_title' => 'Izoh yozish uchun tizimga kiring',
                'sign_in_text' => 'Izohlar hamma uchun ko‘rinadi. Izoh yoki javob qo‘shish uchun avval tizimga kiring.',
                'sign_in_action' => 'Tizimga kirish',
                'signed_in_as_label' => 'Tizimga kirgan foydalanuvchi',
                'category_label' => 'Kategoriya',
                'duration_label' => 'Davomiyligi',
                'platform_label' => 'Platforma',
                'local_label' => 'Mahalliy',
                'youtube_label' => 'YouTube',
                'playing_label' => 'Ijro etilmoqda',
                'verified_channel_label' => 'Tasdiqlangan kanal',
                'category_labels' => $this->json([
                    'All' => 'Barchasi',
                    'Campus Life' => 'Kampus hayoti',
                    'Kampus hayoti' => 'Kampus hayoti',
                    'Research' => 'Tadqiqot',
                    'Tadqiqot' => 'Tadqiqot',
                    'Admissions' => 'Qabul',
                    'Academics' => 'Akademik',
                    'Akademik' => 'Akademik',
                ]),
            ],
            'ru' => [
                'home_tag' => 'Видео галерея',
                'home_title' => 'Жизнь БГТУ в видео',
                'home_subtitle' => 'Смотрите университетские репортажи, события, достижения и яркие моменты студенческой жизни.',
                'view_all_label' => 'Видео галерея',
                'recommended_label' => 'Рекомендуемые видео',
                'videos_label' => 'Видео',
                'description_title' => 'Описание и основные моменты видео',
                'show_more_label' => 'Показать больше',
                'show_less_label' => 'Показать меньше',
                'like_label' => 'Нравится',
                'liked_label' => 'Понравилось',
                'share_label' => 'Поделиться',
                'subscribe_label' => 'Подписаться',
                'subscribed_label' => 'Вы подписаны',
                'subscribers_label' => 'подписчиков',
                'views_label' => 'просмотров',
                'channel_name' => 'Бухарский государственный технический университет',
                'link_copied_label' => 'Ссылка скопирована.',
                'no_videos_label' => 'Видео пока недоступны.',
                'comments_label' => 'Комментарии',
                'reply_label' => 'Ответить',
                'form_title' => 'Добавить комментарий',
                'form_comment_label' => 'Напишите комментарий',
                'form_submit_label' => 'Отправить комментарий',
                'sign_in_title' => 'Войдите, чтобы оставить комментарий',
                'sign_in_text' => 'Комментарии видны всем. Чтобы добавить комментарий или ответ, сначала войдите в систему.',
                'sign_in_action' => 'Войти',
                'signed_in_as_label' => 'Вы вошли как',
                'category_label' => 'Категория',
                'duration_label' => 'Продолжительность',
                'platform_label' => 'Платформа',
                'local_label' => 'Локальное',
                'youtube_label' => 'YouTube',
                'playing_label' => 'Сейчас воспроизводится',
                'verified_channel_label' => 'Подтвержденный канал',
                'category_labels' => $this->json([
                    'All' => 'Все',
                    'Campus Life' => 'Жизнь кампуса',
                    'Студенческая жизнь' => 'Студенческая жизнь',
                    'Research' => 'Исследования',
                    'Научные исследования' => 'Научные исследования',
                    'Admissions' => 'Прием',
                    'Academics' => 'Академическая жизнь',
                    'Академическая деятельность' => 'Академическая деятельность',
                ]),
            ],
            'ar' => [
                'home_tag' => 'معرض الفيديو',
                'home_title' => 'حياة BSTU في مقاطع الفيديو',
                'home_subtitle' => 'شاهد تقارير الجامعة وأبرز الفعاليات والإنجازات والحياة الطلابية من خلال مقاطع مختارة.',
                'view_all_label' => 'معرض الفيديو',
                'recommended_label' => 'مقاطع فيديو مقترحة',
                'videos_label' => 'مقاطع الفيديو',
                'description_title' => 'وصف الفيديو وأبرز النقاط',
                'show_more_label' => 'عرض المزيد',
                'show_less_label' => 'عرض أقل',
                'like_label' => 'إعجاب',
                'liked_label' => 'تم الإعجاب',
                'share_label' => 'مشاركة',
                'subscribe_label' => 'اشتراك',
                'subscribed_label' => 'تم الاشتراك',
                'subscribers_label' => 'مشترك',
                'views_label' => 'مشاهدة',
                'channel_name' => 'جامعة بخارى التقنية الحكومية',
                'link_copied_label' => 'تم نسخ الرابط.',
                'no_videos_label' => 'لا توجد مقاطع فيديو متاحة حاليا.',
                'comments_label' => 'تعليقات',
                'reply_label' => 'رد',
                'form_title' => 'إضافة تعليق',
                'form_comment_label' => 'اكتب تعليقك',
                'form_submit_label' => 'نشر التعليق',
                'sign_in_title' => 'سجّل الدخول لإضافة تعليق',
                'sign_in_text' => 'التعليقات ظاهرة لجميع الزوار. لإضافة تعليق أو رد يجب تسجيل الدخول أولا.',
                'sign_in_action' => 'تسجيل الدخول',
                'signed_in_as_label' => 'تم تسجيل الدخول باسم',
                'category_label' => 'الفئة',
                'duration_label' => 'المدة',
                'platform_label' => 'المنصة',
                'local_label' => 'محلي',
                'youtube_label' => 'يوتيوب',
                'playing_label' => 'قيد التشغيل',
                'verified_channel_label' => 'قناة موثقة',
                'category_labels' => $this->json([
                    'All' => 'الكل',
                    'Campus Life' => 'حياة الجامعة',
                    'حياة الجامعة' => 'حياة الجامعة',
                    'Research' => 'الأبحاث العلمية',
                    'الأبحاث العلمية' => 'الأبحاث العلمية',
                    'Admissions' => 'القبول',
                    'Academics' => 'الشؤون الأكاديمية',
                    'الشؤون الأكاديمية' => 'الشؤون الأكاديمية',
                ]),
            ],
        ];
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
};
