<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('is_published');
            }
        });

        Schema::table('announcement_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('announcement_translations', 'category_label')) {
                $table->string('category_label')->nullable()->after('locale');
            }
        });

        if (! Schema::hasTable('announcement_settings')) {
            Schema::create('announcement_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->default('main');
            $table->unsignedTinyInteger('home_limit')->default(4);
                $table->unsignedTinyInteger('recent_limit')->default(5);
                $table->unsignedTinyInteger('important_limit')->default(3);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('announcement_setting_translations')) {
            Schema::create('announcement_setting_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_setting_id');
                $table->string('locale', 5);
                $table->string('home_tag')->nullable();
                $table->string('home_title')->nullable();
                $table->string('view_all_label')->nullable();
                $table->string('read_details_label')->nullable();
                $table->string('search_title')->nullable();
                $table->string('search_placeholder')->nullable();
                $table->string('categories_title')->nullable();
                $table->string('recent_title')->nullable();
                $table->string('all_label')->nullable();
                $table->string('views_label')->nullable();
                $table->string('important_label')->nullable();
                $table->string('loading_label')->nullable();
                $table->string('no_results_label')->nullable();
                $table->string('clear_filters_label')->nullable();
                $table->string('share_label')->nullable();
                $table->string('copy_link_label')->nullable();
                $table->string('copied_label')->nullable();
                $table->string('published_by_label')->nullable();
                $table->string('publisher_name')->nullable();
                $table->timestamps();
                $table->unique(['announcement_setting_id', 'locale'], 'ann_setting_locale_unique');
                $table->foreign('announcement_setting_id', 'ann_setting_trans_setting_fk')
                    ->references('id')
                    ->on('announcement_settings')
                    ->cascadeOnDelete();
            });
        }

        DB::table('announcement_settings')->updateOrInsert(
            ['key' => 'main'],
            [
                'home_limit' => 4,
                'recent_limit' => 5,
                'important_limit' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $settingId = DB::table('announcement_settings')->where('key', 'main')->value('id');

        $translations = [
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
                'publisher_name' => 'إدارة BSTU',
            ],
        ];

        foreach ($translations as $locale => $fields) {
            DB::table('announcement_setting_translations')->updateOrInsert(
                ['announcement_setting_id' => $settingId, 'locale' => $locale],
                array_merge($fields, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $metadata = database_path('data/announcements.json');
        if (file_exists($metadata)) {
            $dictionaryPath = database_path('data/translations.json');
            $dictionary = file_exists($dictionaryPath)
                ? (json_decode(file_get_contents($dictionaryPath), true) ?: [])
                : [];

            foreach (json_decode(file_get_contents($metadata), true) ?: [] as $item) {
                $slug = $item['slug'] ?? $item['id'] ?? null;
                $category = strtolower($item['category'] ?? 'announcements');

                DB::table('announcements')
                    ->where('slug', $slug)
                    ->update([
                        'views_count' => (int) ($item['views'] ?? 0),
                        'image' => isset($item['image'])
                            ? preg_replace('#^https?://old\.bstu\.uz/#', '', $item['image'])
                            : null,
                    ]);

                foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                    $label = $dictionary[$locale]['announcements']['categories'][$category]
                        ?? ucfirst($category);

                    DB::table('announcement_translations')
                        ->where('locale', $locale)
                        ->whereIn('announcement_id', function ($query) use ($slug) {
                            $query->select('id')->from('announcements')->where('slug', $slug);
                        })
                        ->update(['category_label' => $label]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_setting_translations');
        Schema::dropIfExists('announcement_settings');

        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'views_count')) {
                $table->dropColumn('views_count');
            }
        });

        Schema::table('announcement_translations', function (Blueprint $table) {
            if (Schema::hasColumn('announcement_translations', 'category_label')) {
                $table->dropColumn('category_label');
            }
        });
    }
};
