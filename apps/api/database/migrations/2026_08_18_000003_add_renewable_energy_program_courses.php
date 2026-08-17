<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('programs') || ! Schema::hasTable('courses') || ! Schema::hasTable('course_translations') || ! Schema::hasTable('program_courses')) {
            return;
        }

        $programId = DB::table('programs')->where('slug', 'renewable-energy-sources-60712000')->value('id');

        if (! $programId) {
            return;
        }

        foreach ($this->courses() as $index => $course) {
            $courseId = DB::table('courses')->where('code', $course['code'])->value('id');

            if (! $courseId) {
                $courseId = DB::table('courses')->insertGetId([
                    'code' => $course['code'],
                    'credits' => $course['credits'] ?? 4,
                    'semester' => $course['semester'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('courses')->where('id', $courseId)->update([
                    'credits' => $course['credits'] ?? 4,
                    'semester' => $course['semester'],
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            }

            foreach ($course['translations'] as $locale => $name) {
                DB::table('course_translations')->updateOrInsert(
                    [
                        'course_id' => $courseId,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $name,
                        'description' => $this->descriptionFor($name, $locale),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            DB::table('program_courses')->updateOrInsert(
                [
                    'program_id' => $programId,
                    'course_id' => $courseId,
                ],
                [
                    'year' => $course['year'],
                    'semester' => $course['semester'],
                    'is_required' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $linkedIds = DB::table('program_courses')
            ->where('program_id', $programId)
            ->pluck('course_id');

        DB::table('courses')
            ->whereIn('id', $linkedIds)
            ->update(['is_active' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Keep content changes non-destructive.
    }

    protected function descriptionFor(string $name, string $locale): string
    {
        return match ($locale) {
            'uz' => $name.' fani qayta tiklanuvchi energiya manbalari bo‘yicha kasbiy tayyorgarlikni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональную подготовку в области возобновляемых источников энергии.',
            'ar' => 'يطور مقرر '.$name.' الإعداد المهني في مجال مصادر الطاقة المتجددة.',
            default => $name.' develops professional preparation in renewable energy sources.',
        };
    }

    protected function courses(): array
    {
        return [
            [
                'code' => 'RENE-100',
                'year' => 1,
                'semester' => 1,
                'translations' => [
                    'en' => 'Introduction to Renewable Energy Sources',
                    'uz' => 'Qayta tiklanuvchi energiya manbalariga kirish',
                    'ru' => 'Введение в возобновляемые источники энергии',
                    'ar' => 'مدخل إلى مصادر الطاقة المتجددة',
                ],
            ],
            [
                'code' => 'SOLA-101',
                'year' => 1,
                'semester' => 2,
                'translations' => [
                    'en' => 'Solar Energy Fundamentals',
                    'uz' => 'Quyosh energetikasi asoslari',
                    'ru' => 'Основы солнечной энергетики',
                    'ar' => 'أساسيات الطاقة الشمسية',
                ],
            ],
            [
                'code' => 'WIND-102',
                'year' => 1,
                'semester' => 2,
                'translations' => [
                    'en' => 'Wind Energy Fundamentals',
                    'uz' => 'Shamol energetikasi asoslari',
                    'ru' => 'Основы ветроэнергетики',
                    'ar' => 'أساسيات طاقة الرياح',
                ],
            ],
            [
                'code' => 'ELEC-120',
                'year' => 2,
                'semester' => 1,
                'translations' => [
                    'en' => 'Electrical Circuits for Renewable Energy Systems',
                    'uz' => 'Qayta tiklanuvchi energiya tizimlari uchun elektr zanjirlari',
                    'ru' => 'Электрические цепи для систем возобновляемой энергетики',
                    'ar' => 'الدوائر الكهربائية لأنظمة الطاقة المتجددة',
                ],
            ],
            [
                'code' => 'POWE-121',
                'year' => 2,
                'semester' => 1,
                'translations' => [
                    'en' => 'Power Electronics for Renewable Energy',
                    'uz' => 'Qayta tiklanuvchi energiya uchun kuch elektronikasi',
                    'ru' => 'Силовая электроника для возобновляемой энергетики',
                    'ar' => 'إلكترونيات القدرة للطاقة المتجددة',
                ],
            ],
            [
                'code' => 'STOR-122',
                'year' => 2,
                'semester' => 2,
                'translations' => [
                    'en' => 'Energy Storage Systems',
                    'uz' => 'Energiyani saqlash tizimlari',
                    'ru' => 'Системы накопления энергии',
                    'ar' => 'أنظمة تخزين الطاقة',
                ],
            ],
            [
                'code' => 'GRID-123',
                'year' => 2,
                'semester' => 2,
                'translations' => [
                    'en' => 'Smart Grids and Distributed Generation',
                    'uz' => 'Aqlli tarmoqlar va taqsimlangan generatsiya',
                    'ru' => 'Интеллектуальные сети и распределенная генерация',
                    'ar' => 'الشبكات الذكية والتوليد الموزع',
                ],
            ],
            [
                'code' => 'HYDR-124',
                'year' => 2,
                'semester' => 2,
                'translations' => [
                    'en' => 'Small Hydropower Systems',
                    'uz' => 'Kichik gidroenergetika tizimlari',
                    'ru' => 'Малые гидроэнергетические системы',
                    'ar' => 'أنظمة الطاقة الكهرومائية الصغيرة',
                ],
            ],
            [
                'code' => 'BIOE-125',
                'year' => 3,
                'semester' => 1,
                'translations' => [
                    'en' => 'Bioenergy and Biomass Technologies',
                    'uz' => 'Bioenergiya va biomassa texnologiyalari',
                    'ru' => 'Биоэнергетика и технологии биомассы',
                    'ar' => 'الطاقة الحيوية وتقنيات الكتلة الحيوية',
                ],
            ],
            [
                'code' => 'SOLA-126',
                'year' => 3,
                'semester' => 1,
                'translations' => [
                    'en' => 'Photovoltaic System Design',
                    'uz' => 'Fotoelektr tizimlarni loyihalash',
                    'ru' => 'Проектирование фотоэлектрических систем',
                    'ar' => 'تصميم الأنظمة الكهروضوئية',
                ],
            ],
            [
                'code' => 'WIND-127',
                'year' => 3,
                'semester' => 1,
                'translations' => [
                    'en' => 'Wind Turbine Design and Operation',
                    'uz' => 'Shamol turbinalarini loyihalash va ekspluatatsiya qilish',
                    'ru' => 'Проектирование и эксплуатация ветроустановок',
                    'ar' => 'تصميم وتشغيل توربينات الرياح',
                ],
            ],
            [
                'code' => 'ENER-128',
                'year' => 3,
                'semester' => 2,
                'translations' => [
                    'en' => 'Energy Efficiency and Energy Audit',
                    'uz' => 'Energiya samaradorligi va energiya auditi',
                    'ru' => 'Энергоэффективность и энергоаудит',
                    'ar' => 'كفاءة الطاقة وتدقيق الطاقة',
                ],
            ],
            [
                'code' => 'RENE-129',
                'year' => 3,
                'semester' => 2,
                'translations' => [
                    'en' => 'Renewable Energy Resource Assessment',
                    'uz' => 'Qayta tiklanuvchi energiya resurslarini baholash',
                    'ru' => 'Оценка ресурсов возобновляемой энергии',
                    'ar' => 'تقييم موارد الطاقة المتجددة',
                ],
            ],
            [
                'code' => 'RENE-130',
                'year' => 3,
                'semester' => 2,
                'translations' => [
                    'en' => 'Hybrid Renewable Energy Systems',
                    'uz' => 'Gibrid qayta tiklanuvchi energiya tizimlari',
                    'ru' => 'Гибридные системы возобновляемой энергетики',
                    'ar' => 'أنظمة الطاقة المتجددة الهجينة',
                ],
            ],
            [
                'code' => 'ENVI-131',
                'year' => 4,
                'semester' => 1,
                'translations' => [
                    'en' => 'Environmental Safety in Renewable Energy',
                    'uz' => 'Qayta tiklanuvchi energetikada ekologik xavfsizlik',
                    'ru' => 'Экологическая безопасность в возобновляемой энергетике',
                    'ar' => 'السلامة البيئية في الطاقة المتجددة',
                ],
            ],
            [
                'code' => 'PROJ-132',
                'year' => 4,
                'semester' => 1,
                'translations' => [
                    'en' => 'Renewable Energy Project Management',
                    'uz' => 'Qayta tiklanuvchi energiya loyihalarini boshqarish',
                    'ru' => 'Управление проектами возобновляемой энергетики',
                    'ar' => 'إدارة مشاريع الطاقة المتجددة',
                ],
            ],
            [
                'code' => 'MONI-133',
                'year' => 4,
                'semester' => 1,
                'translations' => [
                    'en' => 'Monitoring and Diagnostics of Energy Systems',
                    'uz' => 'Energetika tizimlarini monitoring va diagnostika qilish',
                    'ru' => 'Мониторинг и диагностика энергетических систем',
                    'ar' => 'مراقبة وتشخيص أنظمة الطاقة',
                ],
            ],
            [
                'code' => 'ECON-134',
                'year' => 4,
                'semester' => 2,
                'translations' => [
                    'en' => 'Economics of Renewable Energy',
                    'uz' => 'Qayta tiklanuvchi energetika iqtisodiyoti',
                    'ru' => 'Экономика возобновляемой энергетики',
                    'ar' => 'اقتصاديات الطاقة المتجددة',
                ],
            ],
            [
                'code' => 'PRAC-135',
                'year' => 4,
                'semester' => 2,
                'translations' => [
                    'en' => 'Field Practice in Renewable Energy Facilities',
                    'uz' => 'Qayta tiklanuvchi energiya obyektlarida dala amaliyoti',
                    'ru' => 'Полевая практика на объектах возобновляемой энергетики',
                    'ar' => 'التدريب الميداني في منشآت الطاقة المتجددة',
                ],
            ],
            [
                'code' => 'GRAD-136',
                'year' => 4,
                'semester' => 2,
                'translations' => [
                    'en' => 'Graduation Project in Renewable Energy',
                    'uz' => 'Qayta tiklanuvchi energiya bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по возобновляемой энергетике',
                    'ar' => 'مشروع التخرج في الطاقة المتجددة',
                ],
            ],
        ];
    }
};
