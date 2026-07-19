<?php

namespace Database\Seeders;

use App\Models\Video;
use App\Models\VideoGallerySetting;
use App\Models\VideoTranslation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    protected array $locales = ['en', 'uz', 'ru', 'ar'];

    public function run(): void
    {
        $translationsPath = database_path('data/translations.json');
        $videosPath = database_path('data/videos.json');

        if (! file_exists($translationsPath) || ! file_exists($videosPath)) {
            return;
        }

        $translations = json_decode(file_get_contents($translationsPath), true);
        $videosMetadata = json_decode(file_get_contents($videosPath), true);

        $sortOrder = 1;
        foreach ($videosMetadata as $meta) {
            $id = $meta['id'];
            $isLocal = (bool) ($meta['isLocal'] ?? false);
            $youtubeId = $meta['youtubeId'] ?? null;
            $url = $isLocal
                ? ($meta['videoUrl'] ?? '')
                : 'https://www.youtube.com/watch?v='.$youtubeId;
            $thumbnail = $meta['poster'] ?? ($youtubeId ? 'https://img.youtube.com/vi/'.$youtubeId.'/maxresdefault.jpg' : null);

            $videoModel = Video::updateOrCreate(
                ['slug' => $id],
                [
                    'url' => $url,
                    'thumbnail' => $thumbnail,
                    'video_type' => $isLocal ? 'local' : 'youtube',
                    'youtube_id' => $youtubeId,
                    'duration' => $meta['duration'] ?? null,
                    'views_count' => $this->parseViews($meta['views'] ?? 0),
                    'likes_count' => 0,
                    'published_at' => $this->publishedAtFromRelative($meta['date'] ?? null),
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );

            // Seed translations for en, uz, ru, ar
            foreach ($this->locales as $locale) {
                // Video translations reside under translations[locale]['videos'][id]
                $videoTrans = $translations[$locale]['videos'][$id] ?? [];

                $title = $videoTrans['title'] ?? ($meta['title'] ?? '');
                $description = $videoTrans['description'] ?? ($meta['description'] ?? '');
                $category = $videoTrans['category'] ?? $this->translateCategory($meta['category'] ?? '', $locale);

                if (empty($title)) {
                    $title = $translations['en']['videos'][$id]['title'] ?? ($meta['title'] ?? '');
                }
                if (empty($description)) {
                    $description = $translations['en']['videos'][$id]['description'] ?? ($meta['description'] ?? '');
                }

                VideoTranslation::updateOrCreate(
                    [
                        'video_id' => $videoModel->id,
                        'locale' => $locale,
                    ],
                    [
                        'title' => $title,
                        'category' => $category,
                        'description' => $description,
                    ]
                );
            }
        }

        $this->seedSettings();
    }

    protected function seedSettings(): void
    {
        $setting = VideoGallerySetting::updateOrCreate(
            ['key' => 'main'],
            [
                'home_limit' => 4,
                'subscriber_count' => 12480,
                'youtube_channel_url' => 'https://www.youtube.com/@BSTU_UZ/videos',
                'is_active' => true,
            ]
        );

        $translations = [
            'en' => [
                'home_tag' => 'Video Gallery',
                'home_title' => 'Life at BSTU in Videos',
                'home_subtitle' => 'Watch special reports and highlights of our university life, achievements, and events.',
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
                'views_label' => 'Views',
                'channel_name' => 'Bukhara State Technical University',
                'link_copied_label' => 'Link copied to clipboard!',
                'no_videos_label' => 'No videos in this category.',
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
                'category_labels' => ['All' => 'All', 'Campus Life' => 'Campus Life', 'Research' => 'Research', 'Admissions' => 'Admissions', 'Academics' => 'Academics'],
            ],
            'uz' => [
                'home_tag' => 'Video galereya',
                'home_title' => 'BuxDTU hayoti videolarda',
                'home_subtitle' => 'Universitet hayoti, yutuqlari va tadbirlaridan maxsus lavhalar va hisobotlarni tomosha qiling.',
                'view_all_label' => 'Video galereya',
                'recommended_label' => 'Tavsiya etilgan videolar',
                'videos_label' => 'Videolar',
                'description_title' => 'Video tavsifi va asosiy lavhalar',
                'show_more_label' => "Ko'proq ko'rsatish",
                'show_less_label' => "Kamroq ko'rsatish",
                'like_label' => 'Yoqdi',
                'liked_label' => 'Yoqtirildi',
                'share_label' => 'Ulashish',
                'subscribe_label' => "Obuna bo'lish",
                'subscribed_label' => "Obuna bo'lindi",
                'subscribers_label' => 'obunachi',
                'views_label' => "Ko'rishlar",
                'channel_name' => 'Buxoro davlat texnika universiteti',
                'link_copied_label' => 'Havola nusxalandi!',
                'no_videos_label' => "Bu toifada video yo'q.",
                'comments_label' => 'Izohlar',
                'reply_label' => 'Javob berish',
                'form_title' => 'Izoh qo‘shish',
                'form_comment_label' => 'Izohingizni yozing',
                'form_submit_label' => 'Izoh yuborish',
                'sign_in_title' => 'Izoh yozish uchun tizimga kiring',
                'sign_in_text' => "Izohlar hamma uchun ko'rinadi. Izoh yoki javob qo'shish uchun avval tizimga kiring.",
                'sign_in_action' => 'Tizimga kirish',
                'signed_in_as_label' => 'Tizimga kirgan foydalanuvchi',
                'category_label' => 'Kategoriya',
                'duration_label' => 'Davomiyligi',
                'platform_label' => 'Platforma',
                'local_label' => 'Mahalliy',
                'youtube_label' => 'YouTube',
                'playing_label' => 'Ijro etilmoqda',
                'verified_channel_label' => 'Tasdiqlangan kanal',
                'category_labels' => ['All' => 'Barchasi', 'Kampus hayoti' => 'Kampus hayoti', 'Tadqiqot' => 'Tadqiqot', 'Qabul' => 'Qabul', 'Akademik' => 'Akademik'],
            ],
            'ru' => [
                'home_tag' => 'Видео галерея',
                'home_title' => 'Жизнь БГТУ в видео',
                'home_subtitle' => 'Смотрите специальные репортажи и яркие моменты университетской жизни, достижений и мероприятий.',
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
                'views_label' => 'Просмотров',
                'channel_name' => 'Бухарский государственный технический университет',
                'link_copied_label' => 'Ссылка скопирована!',
                'no_videos_label' => 'В этой категории нет видео.',
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
                'category_labels' => ['All' => 'Все', 'Студенческая жизнь' => 'Студенческая жизнь', 'Научные исследования' => 'Научные исследования', 'Прием' => 'Прием', 'Академическая деятельность' => 'Академическая деятельность'],
            ],
            'ar' => [
                'home_tag' => 'معرض الفيديو',
                'home_title' => 'حياة BSTU في مقاطع الفيديو',
                'home_subtitle' => 'شاهد التقارير الخاصة وأبرز لحظات الحياة الجامعية والإنجازات والفعاليات.',
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
                'link_copied_label' => 'تم نسخ الرابط!',
                'no_videos_label' => 'لا توجد مقاطع فيديو في هذا التصنيف.',
                'comments_label' => 'تعليقات',
                'reply_label' => 'رد',
                'form_title' => 'إضافة تعليق',
                'form_comment_label' => 'اكتب تعليقك',
                'form_submit_label' => 'نشر التعليق',
                'sign_in_title' => 'سجّل الدخول لإضافة تعليق',
                'sign_in_text' => 'التعليقات ظاهرة لجميع الزوار. لإضافة تعليق أو رد يجب تسجيل الدخول أولًا.',
                'sign_in_action' => 'تسجيل الدخول',
                'signed_in_as_label' => 'تم تسجيل الدخول باسم',
                'category_label' => 'الفئة',
                'duration_label' => 'المدة',
                'platform_label' => 'المنصة',
                'local_label' => 'محلي',
                'youtube_label' => 'يوتيوب',
                'playing_label' => 'قيد التشغيل',
                'verified_channel_label' => 'قناة موثقة',
                'category_labels' => ['All' => 'الكل', 'حياة الجامعة' => 'حياة الجامعة', 'الأبحاث العلمية' => 'الأبحاث العلمية', 'القبول' => 'القبول', 'الشؤون الأكاديمية' => 'الشؤون الأكاديمية'],
            ],
        ];

        foreach ($translations as $locale => $fields) {
            $setting->translations()->updateOrCreate(['locale' => $locale], $fields);
        }
    }

    protected function parseViews(string|int $value): int
    {
        $text = strtolower((string) $value);
        $number = (float) preg_replace('/[^0-9.]/', '', $text);

        return (int) round(str_contains($text, 'k') ? $number * 1000 : $number);
    }

    protected function publishedAtFromRelative(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        if (preg_match('/^(\\d+)\\s+(day|days|week|weeks|month|months|year|years)\\s+ago$/i', trim($value), $match)) {
            $count = (int) $match[1];
            $unit = strtolower($match[2]);

            return match (true) {
                str_starts_with($unit, 'day') => now()->subDays($count),
                str_starts_with($unit, 'week') => now()->subWeeks($count),
                str_starts_with($unit, 'month') => now()->subMonths($count),
                str_starts_with($unit, 'year') => now()->subYears($count),
                default => null,
            };
        }

        return null;
    }

    protected function translateCategory(string $category, string $locale): string
    {
        $labels = [
            'en' => ['Campus Life' => 'Campus Life', 'Research' => 'Research', 'Admissions' => 'Admissions', 'Academics' => 'Academics'],
            'uz' => ['Campus Life' => 'Kampus hayoti', 'Research' => 'Tadqiqot', 'Admissions' => 'Qabul', 'Academics' => 'Akademik'],
            'ru' => ['Campus Life' => 'Жизнь кампуса', 'Research' => 'Исследования', 'Admissions' => 'Прием', 'Academics' => 'Академическая жизнь'],
            'ar' => ['Campus Life' => 'حياة الحرم', 'Research' => 'البحث', 'Admissions' => 'القبول', 'Academics' => 'الأكاديميات'],
        ];

        return $labels[$locale][$category] ?? $category;
    }
}
