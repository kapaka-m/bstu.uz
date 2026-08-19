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
            'uz' => $name.' fani gidrologiya, gidrogeologiya, ekologiya, mehnat muhofazasi va amaliy tadqiqot ko‘nikmalarini rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональные навыки в области гидрологии, гидрогеологии, экологии, охраны труда и прикладных исследований.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المهارات المهنية في الهيدرولوجيا والهيدروجيولوجيا والبيئة والسلامة المهنية والبحث التطبيقي.',
            default => $name.' develops professional skills in hydrology, hydrogeology, ecology, occupational safety, and applied research.',
        };
    }

    private function programCourses(): array
    {
        return [
            'hydrology-60530400' => [
                $this->course('HYDR-200', 1, 1, ['en' => 'Introduction to Hydrology', 'uz' => 'Gidrologiyaga kirish', 'ru' => 'Введение в гидрологию', 'ar' => 'مدخل إلى الهيدرولوجيا']),
                $this->course('HYDR-201', 1, 2, ['en' => 'General Hydrology', 'uz' => 'Umumiy gidrologiya', 'ru' => 'Общая гидрология', 'ar' => 'الهيدرولوجيا العامة']),
                $this->course('HYDR-202', 2, 1, ['en' => 'Hydrometry', 'uz' => 'Gidrometriya', 'ru' => 'Гидрометрия', 'ar' => 'قياس المياه']),
                $this->course('HYDR-203', 2, 1, ['en' => 'Operational Hydrometry', 'uz' => 'Ekspluatatsion gidrometriya', 'ru' => 'Эксплуатационная гидрометрия', 'ar' => 'قياس المياه التشغيلي']),
                $this->course('HYDR-204', 2, 2, ['en' => 'Hydrography', 'uz' => 'Gidrografiya', 'ru' => 'Гидрография', 'ar' => 'الهيدروغرافيا']),
                $this->course('HYDR-205', 2, 2, ['en' => 'Hydrochemistry', 'uz' => 'Gidrokimyo', 'ru' => 'Гидрохимия', 'ar' => 'الكيمياء المائية']),
                $this->course('HYDR-206', 3, 1, ['en' => 'Hydrological Forecasting', 'uz' => 'Gidrologik prognozlash', 'ru' => 'Гидрологическое прогнозирование', 'ar' => 'التنبؤ الهيدرولوجي']),
                $this->course('HYDR-207', 3, 1, ['en' => 'GIS and Hydrology', 'uz' => 'GIS va gidrologiya', 'ru' => 'ГИС и гидрология', 'ar' => 'نظم المعلومات الجغرافية والهيدرولوجيا']),
                $this->course('HYDR-208', 3, 2, ['en' => 'Hydrological Research Methods', 'uz' => 'Gidrologik tadqiqot usullari', 'ru' => 'Методы гидрологических исследований', 'ar' => 'طرق البحث الهيدرولوجي']),
                $this->course('HYDR-209', 3, 2, ['en' => 'Water Resources Assessment', 'uz' => 'Suv resurslarini baholash', 'ru' => 'Оценка водных ресурсов', 'ar' => 'تقييم الموارد المائية']),
                $this->course('HYDR-210', 4, 1, ['en' => 'Environmental Hydrology', 'uz' => 'Ekologik gidrologiya', 'ru' => 'Экологическая гидрология', 'ar' => 'الهيدرولوجيا البيئية']),
                $this->course('HYDR-211', 4, 1, ['en' => 'Hydrological Modeling', 'uz' => 'Gidrologik modellashtirish', 'ru' => 'Гидрологическое моделирование', 'ar' => 'النمذجة الهيدرولوجية']),
                $this->course('HYDR-212', 4, 2, ['en' => 'Field Practice in Hydrology', 'uz' => 'Gidrologiya bo‘yicha dala amaliyoti', 'ru' => 'Полевая практика по гидрологии', 'ar' => 'التدريب الميداني في الهيدرولوجيا']),
                $this->course('HYDR-213', 4, 2, ['en' => 'Graduation Project in Hydrology', 'uz' => 'Gidrologiya bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по гидрологии', 'ar' => 'مشروع التخرج في الهيدرولوجيا']),
            ],
            'reclamation-hydrogeology-60811400' => [
                $this->course('RHYD-100', 1, 1, ['en' => 'Introduction to Reclamation Hydrogeology', 'uz' => 'Meliorativ gidrogeologiyaga kirish', 'ru' => 'Введение в мелиоративную гидрогеологию', 'ar' => 'مدخل إلى الهيدروجيولوجيا الاستصلاحية']),
                $this->course('RHYD-101', 1, 2, ['en' => 'Geology and Hydrogeology', 'uz' => 'Geologiya va gidrogeologiya', 'ru' => 'Геология и гидрогеология', 'ar' => 'الجيولوجيا والهيدروجيولوجيا']),
                $this->course('RHYD-102', 2, 1, ['en' => 'Hydrogeology and Engineering Geology', 'uz' => 'Gidrogeologiya va muhandislik geologiyasi', 'ru' => 'Гидрогеология и инженерная геология', 'ar' => 'الهيدروجيولوجيا والجيولوجيا الهندسية']),
                $this->course('RHYD-103', 2, 1, ['en' => 'Geology and Geomorphology', 'uz' => 'Geologiya va geomorfologiya', 'ru' => 'Геология и геоморфология', 'ar' => 'الجيولوجيا والجيومورفولوجيا']),
                $this->course('RHYD-104', 2, 2, ['en' => 'Groundwater Dynamics', 'uz' => 'Yer osti suvlari dinamikasi', 'ru' => 'Динамика подземных вод', 'ar' => 'ديناميكية المياه الجوفية']),
                $this->course('RHYD-105', 2, 2, ['en' => 'Hydrogeological Reclamation Observations and Cadastre', 'uz' => 'Gidrogeologik-meliorativ kuzatuvlar va kadastr', 'ru' => 'Гидрогеолого-мелиоративные наблюдения и кадастр', 'ar' => 'الرصد والسجل الهيدروجيولوجي الاستصلاحي']),
                $this->course('RHYD-106', 3, 1, ['en' => 'Hydrological Reclamation Forecasting', 'uz' => 'Gidrologik-meliorativ prognozlash', 'ru' => 'Гидролого-мелиоративное прогнозирование', 'ar' => 'التنبؤ الهيدرولوجي الاستصلاحي']),
                $this->course('RHYD-107', 3, 1, ['en' => 'Soil Salinity and Drainage Water', 'uz' => 'Tuproq sho‘rlanishi va drenaj suvlari', 'ru' => 'Засоление почв и дренажные воды', 'ar' => 'ملوحة التربة ومياه الصرف']),
                $this->course('RHYD-108', 3, 2, ['en' => 'Reclamation Monitoring Methods', 'uz' => 'Meliorativ monitoring usullari', 'ru' => 'Методы мелиоративного мониторинга', 'ar' => 'طرق مراقبة الاستصلاح']),
                $this->course('RHYD-109', 3, 2, ['en' => 'Groundwater Quality Assessment', 'uz' => 'Yer osti suvlari sifatini baholash', 'ru' => 'Оценка качества подземных вод', 'ar' => 'تقييم جودة المياه الجوفية']),
                $this->course('RHYD-110', 4, 1, ['en' => 'GIS in Reclamation Hydrogeology', 'uz' => 'Meliorativ gidrogeologiyada GIS', 'ru' => 'ГИС в мелиоративной гидрогеологии', 'ar' => 'نظم المعلومات الجغرافية في الهيدروجيولوجيا الاستصلاحية']),
                $this->course('RHYD-111', 4, 1, ['en' => 'Environmental Protection in Hydrogeology', 'uz' => 'Gidrogeologiyada atrof-muhit muhofazasi', 'ru' => 'Охрана окружающей среды в гидрогеологии', 'ar' => 'حماية البيئة في الهيدروجيولوجيا']),
                $this->course('RHYD-112', 4, 2, ['en' => 'Field Practice in Reclamation Hydrogeology', 'uz' => 'Meliorativ gidrogeologiya bo‘yicha dala amaliyoti', 'ru' => 'Полевая практика по мелиоративной гидрогеологии', 'ar' => 'التدريب الميداني في الهيدروجيولوجيا الاستصلاحية']),
                $this->course('RHYD-113', 4, 2, ['en' => 'Graduation Project in Reclamation Hydrogeology', 'uz' => 'Meliorativ gidrogeologiya bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по мелиоративной гидрогеологии', 'ar' => 'مشروع التخرج في الهيدروجيولوجيا الاستصلاحية']),
            ],
            'occupational-safety-and-technical-safety-61020000' => [
                $this->course('OSTS-100', 1, 1, ['en' => 'Introduction to Occupational and Technical Safety', 'uz' => 'Mehnat muhofazasi va texnik xavfsizlikka kirish', 'ru' => 'Введение в охрану труда и техническую безопасность', 'ar' => 'مدخل إلى السلامة المهنية والفنية']),
                $this->course('OSTS-101', 1, 2, ['en' => 'Life Safety', 'uz' => 'Hayot faoliyati xavfsizligi', 'ru' => 'Безопасность жизнедеятельности', 'ar' => 'سلامة الحياة']),
                $this->course('OSTS-102', 2, 1, ['en' => 'Civil Protection', 'uz' => 'Fuqaro muhofazasi', 'ru' => 'Гражданская защита', 'ar' => 'الحماية المدنية']),
                $this->course('OSTS-103', 2, 1, ['en' => 'Safety in Emergency Situations', 'uz' => 'Favqulodda vaziyatlarda xavfsizlik', 'ru' => 'Безопасность в чрезвычайных ситуациях', 'ar' => 'السلامة في حالات الطوارئ']),
                $this->course('OSTS-104', 2, 2, ['en' => 'Electrical Safety and Operation Rules', 'uz' => 'Elektr xavfsizligi va foydalanish qoidalari', 'ru' => 'Электробезопасность и правила эксплуатации', 'ar' => 'السلامة الكهربائية وقواعد التشغيل']),
                $this->course('OSTS-105', 2, 2, ['en' => 'Fundamentals of Ergonomics', 'uz' => 'Ergonomika asoslari', 'ru' => 'Основы эргономики', 'ar' => 'أساسيات الهندسة البشرية']),
                $this->course('OSTS-106', 3, 1, ['en' => 'Industrial Safety', 'uz' => 'Sanoat xavfsizligi', 'ru' => 'Промышленная безопасность', 'ar' => 'السلامة الصناعية']),
                $this->course('OSTS-107', 3, 1, ['en' => 'Fire and Explosion Safety', 'uz' => 'Yong‘in va portlash xavfsizligi', 'ru' => 'Пожарная и взрывная безопасность', 'ar' => 'السلامة من الحرائق والانفجارات']),
                $this->course('OSTS-108', 3, 2, ['en' => 'Environmental and Life Safety', 'uz' => 'Ekologiya va hayot xavfsizligi', 'ru' => 'Экология и безопасность жизнедеятельности', 'ar' => 'البيئة وسلامة الحياة']),
                $this->course('OSTS-109', 3, 2, ['en' => 'Protective Equipment and Devices', 'uz' => 'Himoya vositalari va qurilmalari', 'ru' => 'Средства и устройства защиты', 'ar' => 'معدات وأجهزة الحماية']),
                $this->course('OSTS-110', 4, 1, ['en' => 'Occupational Risk Assessment', 'uz' => 'Kasbiy xavflarni baholash', 'ru' => 'Оценка профессиональных рисков', 'ar' => 'تقييم المخاطر المهنية']),
                $this->course('OSTS-111', 4, 1, ['en' => 'Safety Management Systems', 'uz' => 'Xavfsizlikni boshqarish tizimlari', 'ru' => 'Системы управления безопасностью', 'ar' => 'أنظمة إدارة السلامة']),
                $this->course('OSTS-112', 4, 2, ['en' => 'Industrial Practice in Occupational Safety', 'uz' => 'Mehnat muhofazasi bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по охране труда', 'ar' => 'التدريب الصناعي في السلامة المهنية']),
                $this->course('OSTS-113', 4, 2, ['en' => 'Graduation Project in Occupational and Technical Safety', 'uz' => 'Mehnat muhofazasi va texnik xavfsizlik bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по охране труда и технической безопасности', 'ar' => 'مشروع التخرج في السلامة المهنية والفنية']),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
