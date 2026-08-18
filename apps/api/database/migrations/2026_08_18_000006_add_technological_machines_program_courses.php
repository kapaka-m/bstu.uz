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
                        [
                            'course_id' => $courseId,
                            'locale' => $locale,
                        ],
                        [
                            'name' => $name,
                            'description' => $this->descriptionFor($name, $locale),
                            'created_at' => now(),
                            'updated_at' => now(),
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
            'uz' => $name.' fani nazariy bilim, amaliy mashg‘ulotlar va ishlab chiqarish ko‘nikmalarini rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает теоретические знания, практические навыки и производственные компетенции.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المعرفة النظرية والمهارات العملية والكفاءات المهنية.',
            default => $name.' develops theoretical knowledge, practical skills, and professional competencies.',
        };
    }

    private function programCourses(): array
    {
        return [
            'technological-machines-and-equipment-60720400' => [
                $this->course('TMEQ-100', 1, 1, [
                    'en' => 'Introduction to Technological Machines and Equipment',
                    'uz' => 'Texnologik mashinalar va jihozlarga kirish',
                    'ru' => 'Введение в технологические машины и оборудование',
                    'ar' => 'مدخل إلى الآلات والمعدات التكنولوجية',
                ]),
                $this->course('TMEQ-101', 1, 2, [
                    'en' => 'Engineering Graphics and Machine Drawing',
                    'uz' => 'Muhandislik grafikasi va mashina chizmachiligi',
                    'ru' => 'Инженерная графика и машинное черчение',
                    'ar' => 'الرسم الهندسي ورسم الآلات',
                ]),
                $this->course('TMEQ-102', 2, 1, [
                    'en' => 'Materials Science and Structural Materials',
                    'uz' => 'Materialshunoslik va konstruksion materiallar',
                    'ru' => 'Материаловедение и конструкционные материалы',
                    'ar' => 'علم المواد والمواد الإنشائية',
                ]),
                $this->course('TMEQ-103', 2, 1, [
                    'en' => 'Machine Elements and Mechanisms',
                    'uz' => 'Mashina detallari va mexanizmlar',
                    'ru' => 'Детали машин и механизмы',
                    'ar' => 'عناصر الآلات والآليات',
                ]),
                $this->course('TMEQ-104', 2, 2, [
                    'en' => 'Hydraulic and Pneumatic Drives',
                    'uz' => 'Gidravlik va pnevmatik yuritmalar',
                    'ru' => 'Гидравлические и пневматические приводы',
                    'ar' => 'المشغلات الهيدروليكية والهوائية',
                ]),
                $this->course('TMEQ-105', 2, 2, [
                    'en' => 'Technological Equipment Design',
                    'uz' => 'Texnologik jihozlarni loyihalash',
                    'ru' => 'Проектирование технологического оборудования',
                    'ar' => 'تصميم المعدات التكنولوجية',
                ]),
                $this->course('TMEQ-106', 3, 1, [
                    'en' => 'Operation of Technological Machines',
                    'uz' => 'Texnologik mashinalardan foydalanish',
                    'ru' => 'Эксплуатация технологических машин',
                    'ar' => 'تشغيل الآلات التكنولوجية',
                ]),
                $this->course('TMEQ-107', 3, 1, [
                    'en' => 'Maintenance and Repair of Equipment',
                    'uz' => 'Jihozlarga texnik xizmat ko‘rsatish va ta’mirlash',
                    'ru' => 'Техническое обслуживание и ремонт оборудования',
                    'ar' => 'صيانة وإصلاح المعدات',
                ]),
                $this->course('TMEQ-108', 3, 2, [
                    'en' => 'Automation of Technological Equipment',
                    'uz' => 'Texnologik jihozlarni avtomatlashtirish',
                    'ru' => 'Автоматизация технологического оборудования',
                    'ar' => 'أتمتة المعدات التكنولوجية',
                ]),
                $this->course('TMEQ-109', 3, 2, [
                    'en' => 'CAD/CAE in Equipment Engineering',
                    'uz' => 'Jihozlar muhandisligida CAD/CAE',
                    'ru' => 'CAD/CAE в инженерии оборудования',
                    'ar' => 'CAD/CAE في هندسة المعدات',
                ]),
                $this->course('TMEQ-110', 4, 1, [
                    'en' => 'Industrial Safety and Quality Control',
                    'uz' => 'Sanoat xavfsizligi va sifat nazorati',
                    'ru' => 'Промышленная безопасность и контроль качества',
                    'ar' => 'السلامة الصناعية ومراقبة الجودة',
                ]),
                $this->course('TMEQ-111', 4, 1, [
                    'en' => 'Energy Efficiency of Technological Equipment',
                    'uz' => 'Texnologik jihozlarning energiya samaradorligi',
                    'ru' => 'Энергоэффективность технологического оборудования',
                    'ar' => 'كفاءة الطاقة في المعدات التكنولوجية',
                ]),
                $this->course('TMEQ-112', 4, 2, [
                    'en' => 'Industrial Practice in Technological Equipment',
                    'uz' => 'Texnologik jihozlar bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по технологическому оборудованию',
                    'ar' => 'التدريب الصناعي في المعدات التكنولوجية',
                ]),
                $this->course('TMEQ-113', 4, 2, [
                    'en' => 'Graduation Project in Technological Machines',
                    'uz' => 'Texnologik mashinalar bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по технологическим машинам',
                    'ar' => 'مشروع التخرج في الآلات التكنولوجية',
                ]),
            ],
            'manufacturing-engineering-60721800' => [
                $this->course('MFG-100', 1, 1, [
                    'en' => 'Introduction to Manufacturing Engineering',
                    'uz' => 'Ishlab chiqarish muhandisligiga kirish',
                    'ru' => 'Введение в производственную инженерию',
                    'ar' => 'مدخل إلى هندسة التصنيع',
                ]),
                $this->course('MFG-101', 1, 2, [
                    'en' => 'Engineering Materials and Manufacturing Properties',
                    'uz' => 'Muhandislik materiallari va ishlab chiqarish xossalari',
                    'ru' => 'Инженерные материалы и производственные свойства',
                    'ar' => 'المواد الهندسية وخصائص التصنيع',
                ]),
                $this->course('MFG-102', 2, 1, [
                    'en' => 'Manufacturing Processes',
                    'uz' => 'Ishlab chiqarish jarayonlari',
                    'ru' => 'Производственные процессы',
                    'ar' => 'عمليات التصنيع',
                ]),
                $this->course('MFG-103', 2, 1, [
                    'en' => 'Machine Tools and Cutting Processes',
                    'uz' => 'Metall kesish stanoklari va kesish jarayonlari',
                    'ru' => 'Станки и процессы резания',
                    'ar' => 'آلات التشغيل وعمليات القطع',
                ]),
                $this->course('MFG-104', 2, 2, [
                    'en' => 'Computer-Aided Manufacturing',
                    'uz' => 'Kompyuter yordamida ishlab chiqarish',
                    'ru' => 'Компьютеризированное производство',
                    'ar' => 'التصنيع بمساعدة الحاسوب',
                ]),
                $this->course('MFG-105', 2, 2, [
                    'en' => 'CNC Programming and Operation',
                    'uz' => 'CNC dasturlash va ishlatish',
                    'ru' => 'Программирование и эксплуатация CNC',
                    'ar' => 'برمجة وتشغيل آلات CNC',
                ]),
                $this->course('MFG-106', 3, 1, [
                    'en' => 'Production Planning and Process Design',
                    'uz' => 'Ishlab chiqarishni rejalashtirish va jarayonlarni loyihalash',
                    'ru' => 'Планирование производства и проектирование процессов',
                    'ar' => 'تخطيط الإنتاج وتصميم العمليات',
                ]),
                $this->course('MFG-107', 3, 1, [
                    'en' => 'Automation and Robotics in Manufacturing',
                    'uz' => 'Ishlab chiqarishda avtomatlashtirish va robototexnika',
                    'ru' => 'Автоматизация и робототехника в производстве',
                    'ar' => 'الأتمتة والروبوتات في التصنيع',
                ]),
                $this->course('MFG-108', 3, 2, [
                    'en' => 'Quality Management in Manufacturing',
                    'uz' => 'Ishlab chiqarishda sifat menejmenti',
                    'ru' => 'Управление качеством в производстве',
                    'ar' => 'إدارة الجودة في التصنيع',
                ]),
                $this->course('MFG-109', 3, 2, [
                    'en' => 'Lean Manufacturing and Resource Efficiency',
                    'uz' => 'Lean ishlab chiqarish va resurs samaradorligi',
                    'ru' => 'Бережливое производство и эффективность ресурсов',
                    'ar' => 'التصنيع الرشيق وكفاءة الموارد',
                ]),
                $this->course('MFG-110', 4, 1, [
                    'en' => 'Industrial Logistics and Production Systems',
                    'uz' => 'Sanoat logistikasi va ishlab chiqarish tizimlari',
                    'ru' => 'Промышленная логистика и производственные системы',
                    'ar' => 'اللوجستيات الصناعية وأنظمة الإنتاج',
                ]),
                $this->course('MFG-111', 4, 1, [
                    'en' => 'Digital Manufacturing and Industry 4.0',
                    'uz' => 'Raqamli ishlab chiqarish va Sanoat 4.0',
                    'ru' => 'Цифровое производство и Индустрия 4.0',
                    'ar' => 'التصنيع الرقمي والصناعة 4.0',
                ]),
                $this->course('MFG-112', 4, 2, [
                    'en' => 'Industrial Practice in Manufacturing Engineering',
                    'uz' => 'Ishlab chiqarish muhandisligi bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по производственной инженерии',
                    'ar' => 'التدريب الصناعي في هندسة التصنيع',
                ]),
                $this->course('MFG-113', 4, 2, [
                    'en' => 'Graduation Project in Manufacturing Engineering',
                    'uz' => 'Ishlab chiqarish muhandisligi bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по производственной инженерии',
                    'ar' => 'مشروع التخرج في هندسة التصنيع',
                ]),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
