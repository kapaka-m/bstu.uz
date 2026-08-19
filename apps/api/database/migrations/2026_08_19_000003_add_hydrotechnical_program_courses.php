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
            'uz' => $name.' fani gidroenergetika, gidrotexnika inshootlari, nasos stansiyalari va muhandislik amaliyoti bo‘yicha kasbiy ko‘nikmalarni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональные навыки в области гидроэнергетики, гидротехнических сооружений, насосных станций и инженерной практики.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المهارات المهنية في الطاقة الكهرومائية والمنشآت الهيدروليكية ومحطات الضخ والتطبيق الهندسي.',
            default => $name.' develops professional skills in hydropower, hydraulic structures, pumping stations, and engineering practice.',
        };
    }

    private function programCourses(): array
    {
        return [
            'hydropower-engineering-60710600' => [
                $this->course('HYPO-100', 1, 1, [
                    'en' => 'Introduction to Hydropower Engineering',
                    'uz' => 'Gidroenergetika muhandisligiga kirish',
                    'ru' => 'Введение в гидроэнергетику',
                    'ar' => 'مدخل إلى هندسة الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-101', 1, 2, [
                    'en' => 'Engineering Hydraulics',
                    'uz' => 'Muhandislik gidravlikasi',
                    'ru' => 'Инженерная гидравлика',
                    'ar' => 'الهيدروليكا الهندسية',
                ]),
                $this->course('HYPO-102', 2, 1, [
                    'en' => 'Hydrology and Water Resources',
                    'uz' => 'Gidrologiya va suv resurslari',
                    'ru' => 'Гидрология и водные ресурсы',
                    'ar' => 'الهيدرولوجيا والموارد المائية',
                ]),
                $this->course('HYPO-103', 2, 1, [
                    'en' => 'Hydraulic Turbines',
                    'uz' => 'Gidravlik turbinalar',
                    'ru' => 'Гидравлические турбины',
                    'ar' => 'التوربينات الهيدروليكية',
                ]),
                $this->course('HYPO-104', 2, 2, [
                    'en' => 'Hydropower Plants',
                    'uz' => 'Gidroelektr stansiyalar',
                    'ru' => 'Гидроэлектростанции',
                    'ar' => 'محطات الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-105', 2, 2, [
                    'en' => 'Electrical Equipment of Hydropower Plants',
                    'uz' => 'Gidroelektr stansiyalarining elektr jihozlari',
                    'ru' => 'Электрооборудование гидроэлектростанций',
                    'ar' => 'المعدات الكهربائية لمحطات الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-106', 3, 1, [
                    'en' => 'Design of Hydropower Facilities',
                    'uz' => 'Gidroenergetika obyektlarini loyihalash',
                    'ru' => 'Проектирование гидроэнергетических объектов',
                    'ar' => 'تصميم منشآت الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-107', 3, 1, [
                    'en' => 'Automation of Hydropower Systems',
                    'uz' => 'Gidroenergetika tizimlarini avtomatlashtirish',
                    'ru' => 'Автоматизация гидроэнергетических систем',
                    'ar' => 'أتمتة أنظمة الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-108', 3, 2, [
                    'en' => 'Operation of Hydropower Equipment',
                    'uz' => 'Gidroenergetika jihozlaridan foydalanish',
                    'ru' => 'Эксплуатация гидроэнергетического оборудования',
                    'ar' => 'تشغيل معدات الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-109', 3, 2, [
                    'en' => 'Small Hydropower Systems',
                    'uz' => 'Kichik gidroenergetika tizimlari',
                    'ru' => 'Малые гидроэнергетические системы',
                    'ar' => 'أنظمة الطاقة الكهرومائية الصغيرة',
                ]),
                $this->course('HYPO-110', 4, 1, [
                    'en' => 'Safety of Hydropower Facilities',
                    'uz' => 'Gidroenergetika inshootlari xavfsizligi',
                    'ru' => 'Безопасность гидроэнергетических сооружений',
                    'ar' => 'سلامة منشآت الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-111', 4, 1, [
                    'en' => 'Environmental Management in Hydropower',
                    'uz' => 'Gidroenergetikada ekologik boshqaruv',
                    'ru' => 'Экологическое управление в гидроэнергетике',
                    'ar' => 'الإدارة البيئية في الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-112', 4, 2, [
                    'en' => 'Industrial Practice in Hydropower Engineering',
                    'uz' => 'Gidroenergetika muhandisligi bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по гидроэнергетике',
                    'ar' => 'التدريب الصناعي في هندسة الطاقة الكهرومائية',
                ]),
                $this->course('HYPO-113', 4, 2, [
                    'en' => 'Graduation Project in Hydropower Engineering',
                    'uz' => 'Gidroenergetika muhandisligi bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по гидроэнергетике',
                    'ar' => 'مشروع التخرج في هندسة الطاقة الكهرومائية',
                ]),
            ],
            'hydraulic-and-geotechnical-engineering-60730600' => [
                $this->course('HYGE-100', 1, 1, [
                    'en' => 'Introduction to Hydraulic and Geotechnical Engineering',
                    'uz' => 'Gidrotexnika va geotexnika muhandisligiga kirish',
                    'ru' => 'Введение в гидротехническую и геотехническую инженерию',
                    'ar' => 'مدخل إلى الهندسة الهيدروليكية والجيوتقنية',
                ]),
                $this->course('HYGE-101', 1, 2, [
                    'en' => 'Engineering Geology',
                    'uz' => 'Muhandislik geologiyasi',
                    'ru' => 'Инженерная геология',
                    'ar' => 'الجيولوجيا الهندسية',
                ]),
                $this->course('HYGE-102', 2, 1, [
                    'en' => 'Soil Mechanics',
                    'uz' => 'Gruntlar mexanikasi',
                    'ru' => 'Механика грунтов',
                    'ar' => 'ميكانيكا التربة',
                ]),
                $this->course('HYGE-103', 2, 1, [
                    'en' => 'Hydraulic Structures',
                    'uz' => 'Gidrotexnika inshootlari',
                    'ru' => 'Гидротехнические сооружения',
                    'ar' => 'المنشآت الهيدروليكية',
                ]),
                $this->course('HYGE-104', 2, 2, [
                    'en' => 'Foundation Engineering',
                    'uz' => 'Poydevorlar muhandisligi',
                    'ru' => 'Инженерия фундаментов',
                    'ar' => 'هندسة الأساسات',
                ]),
                $this->course('HYGE-105', 2, 2, [
                    'en' => 'Geotechnical Investigation Methods',
                    'uz' => 'Geotexnik tadqiqot usullari',
                    'ru' => 'Методы геотехнических изысканий',
                    'ar' => 'طرق التحقيق الجيوتقني',
                ]),
                $this->course('HYGE-106', 3, 1, [
                    'en' => 'Design of Dams and Water Retaining Structures',
                    'uz' => 'To‘g‘onlar va suv ushlab turuvchi inshootlarni loyihalash',
                    'ru' => 'Проектирование плотин и водоудерживающих сооружений',
                    'ar' => 'تصميم السدود والمنشآت الحاجزة للمياه',
                ]),
                $this->course('HYGE-107', 3, 1, [
                    'en' => 'River Engineering and Channel Regulation',
                    'uz' => 'Daryo muhandisligi va o‘zanlarni tartibga solish',
                    'ru' => 'Речная инженерия и регулирование русел',
                    'ar' => 'هندسة الأنهار وتنظيم المجاري',
                ]),
                $this->course('HYGE-108', 3, 2, [
                    'en' => 'Slope Stability and Retaining Walls',
                    'uz' => 'Qiyalik barqarorligi va tirgak devorlar',
                    'ru' => 'Устойчивость склонов и подпорные стены',
                    'ar' => 'استقرار المنحدرات والجدران الساندة',
                ]),
                $this->course('HYGE-109', 3, 2, [
                    'en' => 'Seepage and Filtration in Soils',
                    'uz' => 'Gruntlarda sizilish va filtratsiya',
                    'ru' => 'Просачивание и фильтрация в грунтах',
                    'ar' => 'التسرب والترشيح في التربة',
                ]),
                $this->course('HYGE-110', 4, 1, [
                    'en' => 'Monitoring of Hydraulic and Geotechnical Structures',
                    'uz' => 'Gidrotexnika va geotexnika inshootlari monitoringi',
                    'ru' => 'Мониторинг гидротехнических и геотехнических сооружений',
                    'ar' => 'مراقبة المنشآت الهيدروليكية والجيوتقنية',
                ]),
                $this->course('HYGE-111', 4, 1, [
                    'en' => 'Construction Safety and Risk Assessment',
                    'uz' => 'Qurilish xavfsizligi va xavflarni baholash',
                    'ru' => 'Безопасность строительства и оценка рисков',
                    'ar' => 'سلامة البناء وتقييم المخاطر',
                ]),
                $this->course('HYGE-112', 4, 2, [
                    'en' => 'Industrial Practice in Hydraulic and Geotechnical Engineering',
                    'uz' => 'Gidrotexnika va geotexnika muhandisligi bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по гидротехнической и геотехнической инженерии',
                    'ar' => 'التدريب الصناعي في الهندسة الهيدروليكية والجيوتقنية',
                ]),
                $this->course('HYGE-113', 4, 2, [
                    'en' => 'Graduation Project in Hydraulic and Geotechnical Engineering',
                    'uz' => 'Gidrotexnika va geotexnika muhandisligi bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по гидротехнической и геотехнической инженерии',
                    'ar' => 'مشروع التخرج في الهندسة الهيدروليكية والجيوتقنية',
                ]),
            ],
            'operation-of-hydraulic-structures-and-pumping-stations-60811300' => [
                $this->course('OHPS-100', 1, 1, [
                    'en' => 'Introduction to Hydraulic Structures and Pumping Stations Operation',
                    'uz' => 'Gidrotexnika inshootlari va nasos stansiyalaridan foydalanishga kirish',
                    'ru' => 'Введение в эксплуатацию гидротехнических сооружений и насосных станций',
                    'ar' => 'مدخل إلى تشغيل المنشآت الهيدروليكية ومحطات الضخ',
                ]),
                $this->course('OHPS-101', 1, 2, [
                    'en' => 'Hydraulics and Fluid Mechanics',
                    'uz' => 'Gidravlika va suyuqliklar mexanikasi',
                    'ru' => 'Гидравлика и механика жидкости',
                    'ar' => 'الهيدروليكا وميكانيكا الموائع',
                ]),
                $this->course('OHPS-102', 2, 1, [
                    'en' => 'Pumping Stations',
                    'uz' => 'Nasos stansiyalari',
                    'ru' => 'Насосные станции',
                    'ar' => 'محطات الضخ',
                ]),
                $this->course('OHPS-103', 2, 1, [
                    'en' => 'Pumps and Pumping Equipment',
                    'uz' => 'Nasoslar va nasos jihozlari',
                    'ru' => 'Насосы и насосное оборудование',
                    'ar' => 'المضخات ومعدات الضخ',
                ]),
                $this->course('OHPS-104', 2, 2, [
                    'en' => 'Operation of Hydraulic Structures',
                    'uz' => 'Gidrotexnika inshootlaridan foydalanish',
                    'ru' => 'Эксплуатация гидротехнических сооружений',
                    'ar' => 'تشغيل المنشآت الهيدروليكية',
                ]),
                $this->course('OHPS-105', 2, 2, [
                    'en' => 'Maintenance and Repair of Pumping Equipment',
                    'uz' => 'Nasos jihozlariga texnik xizmat ko‘rsatish va ta’mirlash',
                    'ru' => 'Техническое обслуживание и ремонт насосного оборудования',
                    'ar' => 'صيانة وإصلاح معدات الضخ',
                ]),
                $this->course('OHPS-106', 3, 1, [
                    'en' => 'Automation of Pumping Stations',
                    'uz' => 'Nasos stansiyalarini avtomatlashtirish',
                    'ru' => 'Автоматизация насосных станций',
                    'ar' => 'أتمتة محطات الضخ',
                ]),
                $this->course('OHPS-107', 3, 1, [
                    'en' => 'Energy Efficiency of Pumping Systems',
                    'uz' => 'Nasos tizimlarining energiya samaradorligi',
                    'ru' => 'Энергоэффективность насосных систем',
                    'ar' => 'كفاءة الطاقة في أنظمة الضخ',
                ]),
                $this->course('OHPS-108', 3, 2, [
                    'en' => 'Diagnostics of Hydraulic Equipment',
                    'uz' => 'Gidravlik jihozlar diagnostikasi',
                    'ru' => 'Диагностика гидравлического оборудования',
                    'ar' => 'تشخيص المعدات الهيدروليكية',
                ]),
                $this->course('OHPS-109', 3, 2, [
                    'en' => 'Safety of Hydraulic Structures and Pumping Stations',
                    'uz' => 'Gidrotexnika inshootlari va nasos stansiyalari xavfsizligi',
                    'ru' => 'Безопасность гидротехнических сооружений и насосных станций',
                    'ar' => 'سلامة المنشآت الهيدروليكية ومحطات الضخ',
                ]),
                $this->course('OHPS-110', 4, 1, [
                    'en' => 'Water Intake and Conveyance Structures',
                    'uz' => 'Suv olish va suv uzatish inshootlari',
                    'ru' => 'Водозаборные и водопроводящие сооружения',
                    'ar' => 'منشآت سحب ونقل المياه',
                ]),
                $this->course('OHPS-111', 4, 1, [
                    'en' => 'Operational Planning and Dispatching',
                    'uz' => 'Ekspluatatsion rejalashtirish va dispetcherlik',
                    'ru' => 'Эксплуатационное планирование и диспетчеризация',
                    'ar' => 'التخطيط التشغيلي والإدارة المركزية',
                ]),
                $this->course('OHPS-112', 4, 2, [
                    'en' => 'Industrial Practice in Pumping Stations',
                    'uz' => 'Nasos stansiyalari bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика на насосных станциях',
                    'ar' => 'التدريب الصناعي في محطات الضخ',
                ]),
                $this->course('OHPS-113', 4, 2, [
                    'en' => 'Graduation Project in Hydraulic Structures Operation',
                    'uz' => 'Gidrotexnika inshootlaridan foydalanish bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по эксплуатации гидротехнических сооружений',
                    'ar' => 'مشروع التخرج في تشغيل المنشآت الهيدروليكية',
                ]),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
