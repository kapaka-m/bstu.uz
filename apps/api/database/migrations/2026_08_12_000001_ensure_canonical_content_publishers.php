<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('blog_departments')
            || ! Schema::hasTable('blog_department_translations')
        ) {
            return;
        }

        $sort = (int) DB::table('blog_departments')->max('sort_order') + 1;
        $descriptions = $this->descriptions();

        foreach ($this->publishers() as $slug => $translations) {
            $id = DB::table('blog_departments')->where('slug', $slug)->value('id');

            if ($id) {
                DB::table('blog_departments')->where('id', $id)->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            } else {
                $id = DB::table('blog_departments')->insertGetId([
                    'slug' => $slug,
                    'image' => null,
                    'sort_order' => $sort++,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($translations as $locale => $name) {
                $description = $descriptions[$slug][$locale] ?? $descriptions[$slug]['en'] ?? null;

                DB::table('blog_department_translations')->updateOrInsert(
                    ['blog_department_id' => $id, 'locale' => $locale],
                    [
                        'name' => $name,
                        'description' => $description,
                        'meta_title' => $name,
                        'meta_description' => $description,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // The publishers may be referenced by public content, so rollback keeps data intact.
    }

    private function publishers(): array
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

    private function descriptions(): array
    {
        $base = [
            'en' => 'Official publisher for university content, public updates, announcements, and related academic information.',
            'uz' => 'Universitet kontenti, ommaviy yangiliklar, e’lonlar va tegishli akademik ma’lumotlar uchun rasmiy manba.',
            'ru' => 'Официальный источник университетского контента, публичных обновлений, объявлений и связанной академической информации.',
            'ar' => 'ناشر رسمي لمحتوى الجامعة والتحديثات العامة والإعلانات والمعلومات الأكاديمية ذات الصلة.',
        ];

        return [
            'security-department' => [
                'en' => 'Official publisher for campus safety notices, access control updates, and security-related information.',
                'uz' => 'Kampus xavfsizligi xabarlari, kirish nazorati yangiliklari va xavfsizlikka oid ma’lumotlar uchun rasmiy manba.',
                'ru' => 'Официальный источник уведомлений о безопасности кампуса, пропускном режиме и другой информации по безопасности.',
                'ar' => 'الناشر الرسمي لتنبيهات أمن الحرم الجامعي وتحديثات الدخول والمعلومات المتعلقة بالسلامة.',
            ],
        ] + array_fill_keys(array_keys($this->publishers()), $base);
    }
};
