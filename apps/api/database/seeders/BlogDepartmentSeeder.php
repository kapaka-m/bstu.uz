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
        $this->ensureCanonicalPublishers();
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
        $descriptions = $this->publisherDescriptions();

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
                        'description' => $descriptions[$slug][$locale] ?? $descriptions[$slug]['en'] ?? null,
                        'meta_title' => $name,
                        'meta_description' => $descriptions[$slug][$locale] ?? $descriptions[$slug]['en'] ?? null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    private function ensureCanonicalPublishers(): void
    {
        $sort = (int) DB::table('blog_departments')->max('sort_order') + 1;

        foreach (array_keys($this->publisherTranslations()) as $slug) {
            $exists = DB::table('blog_departments')->where('slug', $slug)->exists();
            if ($exists) {
                DB::table('blog_departments')->where('slug', $slug)->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
                continue;
            }

            DB::table('blog_departments')->insert([
                'slug' => $slug,
                'image' => null,
                'sort_order' => $sort++,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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

    private function publisherDescriptions(): array
    {
        return [
            'security-department' => [
                'en' => 'Official publisher for campus safety notices, access control updates, and security-related information.',
                'uz' => 'Kampus xavfsizligi xabarlari, kirish nazorati yangiliklari va xavfsizlikka oid ma’lumotlar uchun rasmiy manba.',
                'ru' => 'Официальный источник уведомлений о безопасности кампуса, пропускном режиме и другой информации по безопасности.',
                'ar' => 'الناشر الرسمي لتنبيهات أمن الحرم الجامعي وتحديثات الدخول والمعلومات المتعلقة بالسلامة.',
            ],
            'student-union' => [
                'en' => 'Official publisher for student initiatives, youth activities, and campus community updates.',
                'uz' => 'Talabalar tashabbuslari, yoshlar faoliyati va kampus hamjamiyati yangiliklari uchun rasmiy manba.',
                'ru' => 'Официальный источник студенческих инициатив, молодежных мероприятий и новостей университетского сообщества.',
                'ar' => 'الناشر الرسمي لمبادرات الطلاب وأنشطة الشباب وتحديثات مجتمع الحرم الجامعي.',
            ],
            'research-department' => [
                'en' => 'Official publisher for research activity, innovation projects, scientific events, and academic achievements.',
                'uz' => 'Ilmiy faoliyat, innovatsion loyihalar, ilmiy tadbirlar va akademik yutuqlar uchun rasmiy manba.',
                'ru' => 'Официальный источник информации о научной деятельности, инновационных проектах, научных мероприятиях и академических достижениях.',
                'ar' => 'الناشر الرسمي للأنشطة البحثية ومشروعات الابتكار والفعاليات العلمية والإنجازات الأكاديمية.',
            ],
            'academic-affairs' => [
                'en' => 'Official publisher for academic process updates, study schedules, student records, and educational notices.',
                'uz' => 'O‘quv jarayoni yangiliklari, dars jadvallari, talabalar hujjatlari va ta’lim e’lonlari uchun rasmiy manba.',
                'ru' => 'Официальный источник обновлений учебного процесса, расписаний, студенческих данных и образовательных объявлений.',
                'ar' => 'الناشر الرسمي لتحديثات العملية الأكاديمية والجداول والسجلات الطلابية والإعلانات التعليمية.',
            ],
            'innovation-center' => [
                'en' => 'Official publisher for innovation programs, startup activity, technology projects, and applied research news.',
                'uz' => 'Innovatsion dasturlar, startap faoliyati, texnologik loyihalar va amaliy tadqiqot yangiliklari uchun rasmiy manba.',
                'ru' => 'Официальный источник информации об инновационных программах, стартапах, технологических проектах и прикладных исследованиях.',
                'ar' => 'الناشر الرسمي لبرامج الابتكار ونشاط الشركات الناشئة والمشروعات التقنية وأخبار البحث التطبيقي.',
            ],
            'international-relations' => [
                'en' => 'Official publisher for international partnerships, mobility programs, foreign delegations, and global cooperation news.',
                'uz' => 'Xalqaro hamkorlik, akademik mobillik, xorijiy delegatsiyalar va global aloqalar yangiliklari uchun rasmiy manba.',
                'ru' => 'Официальный источник новостей о международных партнерствах, мобильности, иностранных делегациях и глобальном сотрудничестве.',
                'ar' => 'الناشر الرسمي للشراكات الدولية وبرامج التنقل والوفود الأجنبية وأخبار التعاون العالمي.',
            ],
            'bstu-administration' => [
                'en' => 'Official publisher for university-wide announcements, administrative updates, and institutional information.',
                'uz' => 'Universitet miqyosidagi e’lonlar, ma’muriy yangiliklar va institutsional ma’lumotlar uchun rasmiy manba.',
                'ru' => 'Официальный источник общеуниверситетских объявлений, административных обновлений и институциональной информации.',
                'ar' => 'الناشر الرسمي للإعلانات العامة للجامعة والتحديثات الإدارية والمعلومات المؤسسية.',
            ],
            'bstu-communications-office' => [
                'en' => 'Official publisher for university news, media updates, public communications, and press materials.',
                'uz' => 'Universitet yangiliklari, media xabarlari, jamoatchilik bilan aloqalar va matbuot materiallari uchun rasmiy manba.',
                'ru' => 'Официальный источник университетских новостей, медиаобновлений, публичных коммуникаций и пресс-материалов.',
                'ar' => 'الناشر الرسمي لأخبار الجامعة والتحديثات الإعلامية والاتصالات العامة والمواد الصحفية.',
            ],
            'ministry-of-higher-education-innovation' => [
                'en' => 'Official publisher for higher education policy updates, ministry notices, and innovation-sector announcements.',
                'uz' => 'Oliy ta’lim siyosati yangiliklari, vazirlik xabarlari va innovatsiya sohasi e’lonlari uchun rasmiy manba.',
                'ru' => 'Официальный источник обновлений политики высшего образования, уведомлений министерства и объявлений инновационной сферы.',
                'ar' => 'الناشر الرسمي لتحديثات سياسات التعليم العالي وإشعارات الوزارة وإعلانات قطاع الابتكار.',
            ],
            'international-relations-office' => [
                'en' => 'Official publisher for international office notices, partner communication, exchange programs, and cooperation updates.',
                'uz' => 'Xalqaro bo‘lim xabarlari, hamkorlar bilan aloqa, almashinuv dasturlari va hamkorlik yangiliklari uchun rasmiy manba.',
                'ru' => 'Официальный источник уведомлений международного отдела, коммуникации с партнерами, программ обмена и обновлений сотрудничества.',
                'ar' => 'الناشر الرسمي لإشعارات مكتب العلاقات الدولية والتواصل مع الشركاء وبرامج التبادل وتحديثات التعاون.',
            ],
            'engineering-faculty-eco-committee' => [
                'en' => 'Official publisher for engineering faculty sustainability initiatives and environmental activity updates.',
                'uz' => 'Muhandislik fakulteti barqarorlik tashabbuslari va ekologik faoliyat yangiliklari uchun rasmiy manba.',
                'ru' => 'Официальный источник инициатив устойчивого развития инженерного факультета и обновлений экологической деятельности.',
                'ar' => 'الناشر الرسمي لمبادرات الاستدامة بكلية الهندسة وتحديثات الأنشطة البيئية.',
            ],
            'bstu-youth-union-eco-committee' => [
                'en' => 'Official publisher for youth eco-initiatives, volunteer activities, and green campus engagement.',
                'uz' => 'Yoshlar ekologik tashabbuslari, ko‘ngillilar faoliyati va yashil kampus ishtiroki uchun rasmiy manba.',
                'ru' => 'Официальный источник молодежных экологических инициатив, волонтерской деятельности и участия в зеленом кампусе.',
                'ar' => 'الناشر الرسمي للمبادرات البيئية الشبابية والأنشطة التطوعية والمشاركة في الحرم الأخضر.',
            ],
            'community-engagement-committee' => [
                'en' => 'Official publisher for community engagement, outreach activities, and public partnership updates.',
                'uz' => 'Jamoatchilik bilan ishlash, targ‘ibot tadbirlari va hamkorlik yangiliklari uchun rasmiy manba.',
                'ru' => 'Официальный источник информации о взаимодействии с общественностью, внешних мероприятиях и общественных партнерствах.',
                'ar' => 'الناشر الرسمي للمشاركة المجتمعية وأنشطة التواصل وتحديثات الشراكات العامة.',
            ],
            'environmental-sciences-dept' => [
                'en' => 'Official publisher for environmental science updates, sustainability research, and ecology-focused activities.',
                'uz' => 'Atrof-muhit fanlari yangiliklari, barqarorlik tadqiqotlari va ekologiyaga oid tadbirlar uchun rasmiy manba.',
                'ru' => 'Официальный источник обновлений экологических наук, исследований устойчивого развития и экологических мероприятий.',
                'ar' => 'الناشر الرسمي لتحديثات العلوم البيئية وأبحاث الاستدامة والأنشطة المتعلقة بالبيئة.',
            ],
            'bstu-student-volunteer-corps' => [
                'en' => 'Official publisher for student volunteering, campus service activities, and community support initiatives.',
                'uz' => 'Talabalar ko‘ngilliligi, kampus xizmat tadbirlari va jamoatchilikni qo‘llab-quvvatlash tashabbuslari uchun rasmiy manba.',
                'ru' => 'Официальный источник студенческого волонтерства, кампусных сервисных мероприятий и инициатив поддержки сообщества.',
                'ar' => 'الناشر الرسمي للتطوع الطلابي وأنشطة خدمة الحرم الجامعي ومبادرات دعم المجتمع.',
            ],
            'youth-union-eco-committee' => [
                'en' => 'Official publisher for youth union environmental campaigns, awareness events, and sustainability projects.',
                'uz' => 'Yoshlar ittifoqining ekologik kampaniyalari, targ‘ibot tadbirlari va barqarorlik loyihalari uchun rasmiy manba.',
                'ru' => 'Официальный источник экологических кампаний Союза молодежи, просветительских мероприятий и проектов устойчивого развития.',
                'ar' => 'الناشر الرسمي لحملات اتحاد الشباب البيئية وفعاليات التوعية ومشروعات الاستدامة.',
            ],
            'bukhara-state-technical-university' => [
                'en' => 'Official publisher for institutional updates, university news, academic activity, and public communications.',
                'uz' => 'Institutsional yangiliklar, universitet xabarlari, akademik faoliyat va jamoatchilik bilan aloqalar uchun rasmiy manba.',
                'ru' => 'Официальный источник институциональных обновлений, университетских новостей, академической деятельности и публичных коммуникаций.',
                'ar' => 'الناشر الرسمي للتحديثات المؤسسية وأخبار الجامعة والأنشطة الأكاديمية والاتصالات العامة.',
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
