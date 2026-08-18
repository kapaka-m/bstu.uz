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

        foreach ($this->programCourses() as $programSlug => $courses) {
            $programId = DB::table('programs')->where('slug', $programSlug)->value('id');

            if (! $programId) {
                continue;
            }

            foreach ($courses as $course) {
                $courseId = DB::table('courses')->where('code', $course['code'])->value('id');

                if (! $courseId) {
                    $courseId = DB::table('courses')->insertGetId([
                        'code' => $course['code'],
                        'credits' => $course['credits'],
                        'semester' => $course['semester'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('courses')->where('id', $courseId)->update([
                        'credits' => $course['credits'],
                        'semester' => $course['semester'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                }

                foreach ($course['translations'] as $locale => $name) {
                    DB::table('course_translations')->updateOrInsert(
                        ['course_id' => $courseId, 'locale' => $locale],
                        [
                            'name' => $name,
                            'description' => $this->descriptionFor($name, $locale),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                DB::table('program_courses')->updateOrInsert(
                    ['program_id' => $programId, 'course_id' => $courseId],
                    [
                        'year' => $course['year'],
                        'semester' => $course['semester'],
                        'is_required' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Keep academic content corrections non-destructive.
    }

    protected function descriptionFor(string $name, string $locale): string
    {
        return match ($locale) {
            'uz' => $name.' fani nazariy bilim, dala-amaliy ishlar va kasbiy muhandislik ko‘nikmalarini rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает теоретические знания, полевые и практические навыки, а также инженерные компетенции.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المعرفة النظرية والمهارات الميدانية والعملية والكفاءات الهندسية.',
            default => $name.' develops theoretical knowledge, field practice skills, and engineering competencies.',
        };
    }

    private function programCourses(): array
    {
        return [
            'geology-prospecting-and-exploration-of-mineral-resources-60720900' => [
                $this->course('GEOX-100', 1, 1, [
                    'en' => 'Introduction to Geology and Mineral Exploration',
                    'uz' => 'Geologiya va foydali qazilmalarni qidirishga kirish',
                    'ru' => 'Введение в геологию и разведку полезных ископаемых',
                    'ar' => 'مدخل إلى الجيولوجيا واستكشاف الموارد المعدنية',
                ]),
                $this->course('GEOX-101', 1, 2, [
                    'en' => 'General Geology',
                    'uz' => 'Umumiy geologiya',
                    'ru' => 'Общая геология',
                    'ar' => 'الجيولوجيا العامة',
                ]),
                $this->course('GEOX-102', 2, 1, [
                    'en' => 'Mineralogy and Petrography',
                    'uz' => 'Mineralogiya va petrografiya',
                    'ru' => 'Минералогия и петрография',
                    'ar' => 'علم المعادن والصخور',
                ]),
                $this->course('GEOX-103', 2, 1, [
                    'en' => 'Structural Geology',
                    'uz' => 'Strukturaviy geologiya',
                    'ru' => 'Структурная геология',
                    'ar' => 'الجيولوجيا التركيبية',
                ]),
                $this->course('GEOX-104', 2, 2, [
                    'en' => 'Geochemistry',
                    'uz' => 'Geokimyo',
                    'ru' => 'Геохимия',
                    'ar' => 'الجيوكيمياء',
                ]),
                $this->course('GEOX-105', 2, 2, [
                    'en' => 'Geophysical Exploration Methods',
                    'uz' => 'Geofizik qidiruv usullari',
                    'ru' => 'Геофизические методы разведки',
                    'ar' => 'طرق الاستكشاف الجيوفيزيائي',
                ]),
                $this->course('GEOX-106', 3, 1, [
                    'en' => 'Geological Mapping and Surveying',
                    'uz' => 'Geologik xaritalash va syomka',
                    'ru' => 'Геологическое картирование и съемка',
                    'ar' => 'رسم الخرائط والمسح الجيولوجي',
                ]),
                $this->course('GEOX-107', 3, 1, [
                    'en' => 'Mineral Deposits',
                    'uz' => 'Foydali qazilma konlari',
                    'ru' => 'Месторождения полезных ископаемых',
                    'ar' => 'رواسب الموارد المعدنية',
                ]),
                $this->course('GEOX-108', 3, 2, [
                    'en' => 'Drilling and Sampling Techniques',
                    'uz' => 'Burg‘ilash va namuna olish texnikalari',
                    'ru' => 'Техника бурения и отбора проб',
                    'ar' => 'تقنيات الحفر وأخذ العينات',
                ]),
                $this->course('GEOX-109', 3, 2, [
                    'en' => 'Remote Sensing and GIS in Geology',
                    'uz' => 'Geologiyada masofadan zondlash va GIS',
                    'ru' => 'Дистанционное зондирование и ГИС в геологии',
                    'ar' => 'الاستشعار عن بعد ونظم المعلومات الجغرافية في الجيولوجيا',
                ]),
                $this->course('GEOX-110', 4, 1, [
                    'en' => 'Reserve Estimation and Resource Evaluation',
                    'uz' => 'Zaxiralarni hisoblash va resurslarni baholash',
                    'ru' => 'Подсчет запасов и оценка ресурсов',
                    'ar' => 'تقدير الاحتياطيات وتقييم الموارد',
                ]),
                $this->course('GEOX-111', 4, 1, [
                    'en' => 'Environmental Safety in Mineral Exploration',
                    'uz' => 'Foydali qazilmalarni qidirishda ekologik xavfsizlik',
                    'ru' => 'Экологическая безопасность при разведке полезных ископаемых',
                    'ar' => 'السلامة البيئية في استكشاف الموارد المعدنية',
                ]),
                $this->course('GEOX-112', 4, 2, [
                    'en' => 'Field Practice in Geological Exploration',
                    'uz' => 'Geologik qidiruv bo‘yicha dala amaliyoti',
                    'ru' => 'Полевая практика по геологической разведке',
                    'ar' => 'التدريب الميداني في الاستكشاف الجيولوجي',
                ]),
                $this->course('GEOX-113', 4, 2, [
                    'en' => 'Graduation Project in Mineral Exploration',
                    'uz' => 'Foydali qazilmalarni qidirish bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по разведке полезных ископаемых',
                    'ar' => 'مشروع التخرج في استكشاف الموارد المعدنية',
                ]),
            ],
            'oil-and-gas-engineering-60721100' => [
                $this->course('OGEN-100', 1, 1, [
                    'en' => 'Introduction to Oil and Gas Engineering',
                    'uz' => 'Neft va gaz muhandisligiga kirish',
                    'ru' => 'Введение в нефтегазовую инженерию',
                    'ar' => 'مدخل إلى هندسة النفط والغاز',
                ]),
                $this->course('OGEN-101', 1, 2, [
                    'en' => 'Petroleum Geology',
                    'uz' => 'Neft-gaz geologiyasi',
                    'ru' => 'Нефтегазовая геология',
                    'ar' => 'جيولوجيا النفط والغاز',
                ]),
                $this->course('OGEN-102', 2, 1, [
                    'en' => 'Reservoir Engineering',
                    'uz' => 'Qatlam muhandisligi',
                    'ru' => 'Разработка пластов',
                    'ar' => 'هندسة المكامن',
                ]),
                $this->course('OGEN-103', 2, 1, [
                    'en' => 'Drilling Engineering',
                    'uz' => 'Burg‘ilash muhandisligi',
                    'ru' => 'Буровая инженерия',
                    'ar' => 'هندسة الحفر',
                ]),
                $this->course('OGEN-104', 2, 2, [
                    'en' => 'Oil and Gas Production Technology',
                    'uz' => 'Neft va gaz qazib olish texnologiyasi',
                    'ru' => 'Технология добычи нефти и газа',
                    'ar' => 'تقنية إنتاج النفط والغاز',
                ]),
                $this->course('OGEN-105', 2, 2, [
                    'en' => 'Well Logging and Formation Evaluation',
                    'uz' => 'Quduq karotaji va qatlamni baholash',
                    'ru' => 'Геофизические исследования скважин и оценка пластов',
                    'ar' => 'تسجيل الآبار وتقييم الطبقات',
                ]),
                $this->course('OGEN-106', 3, 1, [
                    'en' => 'Oilfield Equipment and Operation',
                    'uz' => 'Neft koni jihozlari va ulardan foydalanish',
                    'ru' => 'Нефтепромысловое оборудование и эксплуатация',
                    'ar' => 'معدات حقول النفط وتشغيلها',
                ]),
                $this->course('OGEN-107', 3, 1, [
                    'en' => 'Pipeline Transportation Systems',
                    'uz' => 'Quvur transporti tizimlari',
                    'ru' => 'Системы трубопроводного транспорта',
                    'ar' => 'أنظمة النقل عبر خطوط الأنابيب',
                ]),
                $this->course('OGEN-108', 3, 2, [
                    'en' => 'Gas Processing and Preparation',
                    'uz' => 'Gazni qayta ishlash va tayyorlash',
                    'ru' => 'Переработка и подготовка газа',
                    'ar' => 'معالجة وتجهيز الغاز',
                ]),
                $this->course('OGEN-109', 3, 2, [
                    'en' => 'Enhanced Oil Recovery Methods',
                    'uz' => 'Neft beruvchanlikni oshirish usullari',
                    'ru' => 'Методы повышения нефтеотдачи',
                    'ar' => 'طرق تحسين استخلاص النفط',
                ]),
                $this->course('OGEN-110', 4, 1, [
                    'en' => 'Industrial Safety in Oil and Gas Operations',
                    'uz' => 'Neft va gaz ishlarida sanoat xavfsizligi',
                    'ru' => 'Промышленная безопасность в нефтегазовых работах',
                    'ar' => 'السلامة الصناعية في عمليات النفط والغاز',
                ]),
                $this->course('OGEN-111', 4, 1, [
                    'en' => 'Oil and Gas Field Development Design',
                    'uz' => 'Neft va gaz konlarini ishlashni loyihalash',
                    'ru' => 'Проектирование разработки нефтегазовых месторождений',
                    'ar' => 'تصميم تطوير حقول النفط والغاز',
                ]),
                $this->course('OGEN-112', 4, 2, [
                    'en' => 'Field Practice in Oil and Gas Facilities',
                    'uz' => 'Neft va gaz obyektlarida dala amaliyoti',
                    'ru' => 'Полевая практика на нефтегазовых объектах',
                    'ar' => 'التدريب الميداني في منشآت النفط والغاز',
                ]),
                $this->course('OGEN-113', 4, 2, [
                    'en' => 'Graduation Project in Oil and Gas Engineering',
                    'uz' => 'Neft va gaz muhandisligi bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по нефтегазовой инженерии',
                    'ar' => 'مشروع التخرج في هندسة النفط والغاز',
                ]),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
