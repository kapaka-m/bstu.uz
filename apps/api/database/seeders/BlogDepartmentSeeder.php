<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('blog_departments')) {
            return;
        }

        $locales = DB::table('locales')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all() ?: ['en', 'uz', 'ru', 'ar'];

        $blogs = DB::table('blogs')
            ->leftJoin('blog_translations', function ($join) {
                $join->on('blogs.id', '=', 'blog_translations.blog_id')
                    ->where('blog_translations.locale', '=', 'en');
            })
            ->select('blogs.id', 'blogs.author', 'blogs.author_image', 'blog_translations.author as translated_author')
            ->get();

        foreach ($blogs as $blog) {
            $name = trim((string) ($blog->translated_author ?: $blog->author));
            $slug = Str::slug($name);
            if ($name === '' || $slug === '') {
                continue;
            }

            $departmentId = DB::table('blog_departments')->where('slug', $slug)->value('id');
            if (! $departmentId) {
                $departmentId = DB::table('blog_departments')->insertGetId([
                    'slug' => $slug,
                    'image' => $blog->author_image,
                    'sort_order' => (int) $blog->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($locales as $locale) {
                DB::table('blog_department_translations')->updateOrInsert(
                    ['blog_department_id' => $departmentId, 'locale' => $locale],
                    [
                        'name' => $name,
                        'meta_title' => $name,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            DB::table('blogs')->where('id', $blog->id)->update([
                'blog_department_id' => $departmentId,
                'updated_at' => now(),
            ]);
        }

        $this->assignAnnouncementPublishers();
        $this->assignGreenCampusPublishers();
        $this->assignVideoPublishers();
        $this->assignNewsPublishers();
        $this->applyPublisherTranslations();
        $this->ensurePublisherUiTranslations();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    private function assignAnnouncementPublishers(): void
    {
        if (! DB::getSchemaBuilder()->hasColumn('announcements', 'publisher_id')) {
            return;
        }

        $name = DB::table('announcement_setting_translations')
            ->whereNotNull('publisher_name')
            ->where('publisher_name', '!=', '')
            ->orderByRaw("locale = 'en' desc")
            ->value('publisher_name') ?: 'BSTU Administration';

        DB::table('announcements')->whereNull('publisher_id')->update([
            'publisher_id' => $this->publisherId($name),
            'updated_at' => now(),
        ]);
    }

    private function assignGreenCampusPublishers(): void
    {
        if (! DB::getSchemaBuilder()->hasColumn('green_campus_articles', 'publisher_id')) {
            return;
        }

        $articles = DB::table('green_campus_articles')
            ->leftJoin('green_campus_article_translations', function ($join) {
                $join->on('green_campus_articles.id', '=', 'green_campus_article_translations.green_campus_article_id')
                    ->where('green_campus_article_translations.locale', '=', 'en');
            })
            ->select('green_campus_articles.id', 'green_campus_article_translations.author')
            ->get();

        foreach ($articles as $article) {
            $name = trim((string) $article->author) ?: 'BSTU Communications Office';
            DB::table('green_campus_articles')->where('id', $article->id)->update([
                'publisher_id' => $this->publisherId($name),
                'updated_at' => now(),
            ]);
        }
    }

    private function assignVideoPublishers(): void
    {
        if (! DB::getSchemaBuilder()->hasColumn('videos', 'publisher_id')) {
            return;
        }

        $name = DB::table('video_gallery_setting_translations')
            ->whereNotNull('channel_name')
            ->where('channel_name', '!=', '')
            ->orderByRaw("locale = 'en' desc")
            ->value('channel_name') ?: 'Bukhara State Technical University';

        DB::table('videos')->whereNull('publisher_id')->update([
            'publisher_id' => $this->publisherId($name),
            'updated_at' => now(),
        ]);
    }

    private function assignNewsPublishers(): void
    {
        if (! DB::getSchemaBuilder()->hasColumn('news', 'publisher_id')) {
            return;
        }

        DB::table('news')->whereNull('publisher_id')->update([
            'publisher_id' => $this->publisherId('BSTU Communications Office'),
            'updated_at' => now(),
        ]);
    }

    private function publisherId(string $name): int
    {
        $slug = Str::slug($name);
        $id = DB::table('blog_departments')->where('slug', $slug)->value('id');

        if (! $id) {
            $id = DB::table('blog_departments')->insertGetId([
                'slug' => $slug,
                'image' => 'cms/blog/blog-author.jpg',
                'sort_order' => (int) DB::table('blog_departments')->max('sort_order') + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->locales() as $locale) {
            DB::table('blog_department_translations')->updateOrInsert(
                ['blog_department_id' => $id, 'locale' => $locale],
                [
                    'name' => $name,
                    'meta_title' => $name,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return (int) $id;
    }

    private function applyPublisherTranslations(): void
    {
        foreach ($this->publisherTranslations() as $slug => $translations) {
            $id = DB::table('blog_departments')->where('slug', $slug)->value('id');
            if (! $id) {
                continue;
            }

            foreach ($translations as $locale => $name) {
                DB::table('blog_department_translations')->updateOrInsert(
                    ['blog_department_id' => $id, 'locale' => $locale],
                    [
                        'name' => $name,
                        'meta_title' => $name,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    private function publisherTranslations(): array
    {
        return [
            'security-department' => [
                'en' => 'Security Department',
                'uz' => 'Xavfsizlik bo‘limi',
                'ru' => 'Отдел безопасности',
                'ar' => 'إدارة الأمن',
            ],
            'student-union' => [
                'en' => 'Student Union',
                'uz' => 'Talabalar ittifoqi',
                'ru' => 'Студенческий союз',
                'ar' => 'اتحاد الطلاب',
            ],
            'research-department' => [
                'en' => 'Research Department',
                'uz' => 'Ilmiy tadqiqot bo‘limi',
                'ru' => 'Научно-исследовательский отдел',
                'ar' => 'قسم البحث العلمي',
            ],
            'academic-affairs' => [
                'en' => 'Academic Affairs',
                'uz' => 'O‘quv ishlari',
                'ru' => 'Учебный отдел',
                'ar' => 'الشؤون الأكاديمية',
            ],
            'innovation-center' => [
                'en' => 'Innovation Center',
                'uz' => 'Innovatsiyalar markazi',
                'ru' => 'Инновационный центр',
                'ar' => 'مركز الابتكار',
            ],
            'international-relations' => [
                'en' => 'International Relations',
                'uz' => 'Xalqaro aloqalar',
                'ru' => 'Международные связи',
                'ar' => 'العلاقات الدولية',
            ],
            'bstu-administration' => [
                'en' => 'BSTU Administration',
                'uz' => 'BDTU ma’muriyati',
                'ru' => 'Администрация БГТУ',
                'ar' => 'إدارة جامعة بخارى التقنية الحكومية',
            ],
            'bstu-communications-office' => [
                'en' => 'BSTU Communications Office',
                'uz' => 'BDTU axborot xizmati',
                'ru' => 'Пресс-служба БГТУ',
                'ar' => 'مكتب اتصالات جامعة بخارى التقنية الحكومية',
            ],
            'ministry-of-higher-education-innovation' => [
                'en' => 'Ministry of Higher Education & Innovation',
                'uz' => 'Oliy ta’lim va innovatsiyalar vazirligi',
                'ru' => 'Министерство высшего образования и инноваций',
                'ar' => 'وزارة التعليم العالي والابتكار',
            ],
            'international-relations-office' => [
                'en' => 'International Relations Office',
                'uz' => 'Xalqaro aloqalar bo‘limi',
                'ru' => 'Отдел международных связей',
                'ar' => 'مكتب العلاقات الدولية',
            ],
            'engineering-faculty-eco-committee' => [
                'en' => 'Engineering Faculty Eco-Committee',
                'uz' => 'Muhandislik fakulteti ekologiya qo‘mitasi',
                'ru' => 'Эко-комитет инженерного факультета',
                'ar' => 'اللجنة البيئية لكلية الهندسة',
            ],
            'bstu-youth-union-eco-committee' => [
                'en' => 'BSTU Youth Union Eco-Committee',
                'uz' => 'BDTU Yoshlar ittifoqi ekologiya qo‘mitasi',
                'ru' => 'Эко-комитет Союза молодежи БГТУ',
                'ar' => 'اللجنة البيئية لاتحاد شباب جامعة بخارى التقنية الحكومية',
            ],
            'community-engagement-committee' => [
                'en' => 'Community Engagement Committee',
                'uz' => 'Jamoatchilik bilan ishlash qo‘mitasi',
                'ru' => 'Комитет по взаимодействию с общественностью',
                'ar' => 'لجنة المشاركة المجتمعية',
            ],
            'environmental-sciences-dept' => [
                'en' => 'Environmental Sciences Dept',
                'uz' => 'Atrof-muhit fanlari bo‘limi',
                'ru' => 'Отдел экологических наук',
                'ar' => 'قسم العلوم البيئية',
            ],
            'bstu-student-volunteer-corps' => [
                'en' => 'BSTU Student Volunteer Corps',
                'uz' => 'BDTU talabalar ko‘ngillilar korpusi',
                'ru' => 'Студенческий волонтерский корпус БГТУ',
                'ar' => 'فريق المتطوعين الطلابي بجامعة بخارى التقنية الحكومية',
            ],
            'youth-union-eco-committee' => [
                'en' => 'Youth Union Eco-Committee',
                'uz' => 'Yoshlar ittifoqi ekologiya qo‘mitasi',
                'ru' => 'Эко-комитет Союза молодежи',
                'ar' => 'اللجنة البيئية لاتحاد الشباب',
            ],
            'bukhara-state-technical-university' => [
                'en' => 'Bukhara State Technical University',
                'uz' => 'Buxoro davlat texnika universiteti',
                'ru' => 'Бухарский государственный технический университет',
                'ar' => 'جامعة بخارى التقنية الحكومية',
            ],
        ];
    }

    private function ensurePublisherUiTranslations(): void
    {
        if (
            ! DB::getSchemaBuilder()->hasTable('translation_keys')
            || ! DB::getSchemaBuilder()->hasTable('translation_values')
        ) {
            return;
        }

        $keyId = DB::table('translation_keys')
            ->where('group', 'common')
            ->where('key', 'contentPublisher')
            ->value('id');

        $payload = [
            'group' => 'common',
            'key' => 'contentPublisher',
            'description' => 'Public UI label: common.contentPublisher',
            'is_system' => true,
            'updated_at' => now(),
        ];

        if ($keyId) {
            DB::table('translation_keys')->where('id', $keyId)->update($payload);
        } else {
            $keyId = DB::table('translation_keys')->insertGetId($payload + [
                'created_at' => now(),
            ]);
        }

        foreach ($this->publisherUiTranslations() as $locale => $value) {
            DB::table('translation_values')->updateOrInsert(
                ['translation_key_id' => $keyId, 'locale' => $locale],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function publisherUiTranslations(): array
    {
        return [
            'en' => 'Content Publisher',
            'uz' => 'Kontent manbasi',
            'ru' => 'Источник контента',
            'ar' => 'ناشر المحتوى',
        ];
    }

    private function locales(): array
    {
        return DB::table('locales')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all() ?: ['en', 'uz', 'ru', 'ar'];
    }
}
