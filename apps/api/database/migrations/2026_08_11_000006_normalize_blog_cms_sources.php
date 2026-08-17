<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settingId = DB::table('blog_settings')->where('key', 'main')->value('id');

        $payload = [
            'key' => 'main',
            'home_limit' => 3,
            'recent_limit' => 5,
            'home_icon' => 'book-open',
            'tags' => json_encode([], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($settingId) {
            DB::table('blog_settings')->where('id', $settingId)->update($payload);
        } else {
            $settingId = DB::table('blog_settings')->insertGetId($payload + ['created_at' => now()]);
        }

        foreach ($this->translations() as $locale => $translation) {
            DB::table('blog_setting_translations')->updateOrInsert(
                ['blog_setting_id' => $settingId, 'locale' => $locale],
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
                'home_tag' => 'Blog',
                'home_title' => 'Insights, Stories & Academic Perspectives',
                'view_all_label' => 'View All Blog',
                'read_more_label' => 'Read More',
                'search_title' => 'Search Blog',
                'search_placeholder' => 'Search articles...',
                'categories_title' => 'Categories',
                'recent_title' => 'Recent Posts',
                'tags_title' => 'Tags',
                'all_blog_label' => 'All',
                'loading_label' => 'Loading blog posts...',
                'no_results_label' => 'No articles found',
                'clear_filters_label' => 'Clear filters',
                'back_to_blog_label' => 'Back to Blog',
                'comments_label' => 'Comments',
                'reply_label' => 'Reply',
                'form_title' => 'Leave a Reply',
                'form_name_label' => 'Name',
                'form_email_label' => 'Email',
                'form_comment_label' => 'Comment',
                'form_submit_label' => 'Post Comment',
                'signed_in_as_label' => 'Signed in as',
                'comment_login_action' => 'Sign In',
                'comment_login_text' => 'Comments are visible to everyone. Please sign in before adding a comment or reply.',
                'comment_login_title' => 'Sign in to comment',
            ],
            'uz' => [
                'home_tag' => 'Blog',
                'home_title' => 'Fikrlar, hikoyalar va akademik qarashlar',
                'view_all_label' => 'Barcha maqolalarni ko‘rish',
                'read_more_label' => 'Batafsil',
                'search_title' => 'Blogdan qidirish',
                'search_placeholder' => 'Maqolalarni qidirish...',
                'categories_title' => 'Toifalar',
                'recent_title' => 'So‘nggi postlar',
                'tags_title' => 'Teglar',
                'all_blog_label' => 'Barchasi',
                'loading_label' => 'Blog postlari yuklanmoqda...',
                'no_results_label' => 'Maqolalar topilmadi',
                'clear_filters_label' => 'Filtrlarni tozalash',
                'back_to_blog_label' => 'Blogga qaytish',
                'comments_label' => 'Izohlar',
                'reply_label' => 'Javob berish',
                'form_title' => 'Javob qoldiring',
                'form_name_label' => 'Ism',
                'form_email_label' => 'Email',
                'form_comment_label' => 'Izoh',
                'form_submit_label' => 'Izoh yuborish',
                'signed_in_as_label' => 'Tizimga kirgan foydalanuvchi',
                'comment_login_action' => 'Tizimga kirish',
                'comment_login_text' => 'Izohlar hamma uchun ko‘rinadi. Izoh yoki javob qo‘shish uchun avval tizimga kiring.',
                'comment_login_title' => 'Izoh yozish uchun tizimga kiring',
            ],
            'ru' => [
                'home_tag' => 'Блог',
                'home_title' => 'Идеи, истории и академические взгляды',
                'view_all_label' => 'Все статьи',
                'read_more_label' => 'Подробнее',
                'search_title' => 'Поиск в блоге',
                'search_placeholder' => 'Искать статьи...',
                'categories_title' => 'Категории',
                'recent_title' => 'Недавние публикации',
                'tags_title' => 'Теги',
                'all_blog_label' => 'Все',
                'loading_label' => 'Загрузка публикаций...',
                'no_results_label' => 'Статьи не найдены',
                'clear_filters_label' => 'Сбросить фильтры',
                'back_to_blog_label' => 'Назад к блогу',
                'comments_label' => 'Комментарии',
                'reply_label' => 'Ответить',
                'form_title' => 'Оставить ответ',
                'form_name_label' => 'Имя',
                'form_email_label' => 'Email',
                'form_comment_label' => 'Комментарий',
                'form_submit_label' => 'Отправить комментарий',
                'signed_in_as_label' => 'Вы вошли как',
                'comment_login_action' => 'Войти',
                'comment_login_text' => 'Комментарии видны всем. Пожалуйста, войдите перед добавлением комментария или ответа.',
                'comment_login_title' => 'Войдите, чтобы комментировать',
            ],
            'ar' => [
                'home_tag' => 'المدونة',
                'home_title' => 'رؤى وقصص وآراء أكاديمية',
                'view_all_label' => 'عرض كل المقالات',
                'read_more_label' => 'قراءة المزيد',
                'search_title' => 'بحث المدونة',
                'search_placeholder' => 'ابحث في المقالات...',
                'categories_title' => 'التصنيفات',
                'recent_title' => 'أحدث المقالات',
                'tags_title' => 'الوسوم',
                'all_blog_label' => 'الكل',
                'loading_label' => 'جاري تحميل المقالات...',
                'no_results_label' => 'لم يتم العثور على مقالات',
                'clear_filters_label' => 'مسح الفلاتر',
                'back_to_blog_label' => 'العودة إلى المدونة',
                'comments_label' => 'تعليقات',
                'reply_label' => 'رد',
                'form_title' => 'اترك ردا',
                'form_name_label' => 'الاسم',
                'form_email_label' => 'البريد الإلكتروني',
                'form_comment_label' => 'التعليق',
                'form_submit_label' => 'نشر التعليق',
                'signed_in_as_label' => 'تم تسجيل الدخول باسم',
                'comment_login_action' => 'تسجيل الدخول',
                'comment_login_text' => 'التعليقات ظاهرة للجميع. يرجى تسجيل الدخول قبل إضافة تعليق أو رد.',
                'comment_login_title' => 'سجل الدخول للتعليق',
            ],
        ];
    }
};
