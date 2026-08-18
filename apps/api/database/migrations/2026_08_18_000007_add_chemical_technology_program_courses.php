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
            'uz' => $name.' fani nazariy bilim, laboratoriya ishlari va amaliy kasbiy ko‘nikmalarni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает теоретические знания, лабораторные навыки и практические профессиональные компетенции.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المعرفة النظرية والمهارات المخبرية والكفاءات المهنية العملية.',
            default => $name.' develops theoretical knowledge, laboratory skills, and practical professional competencies.',
        };
    }

    private function programCourses(): array
    {
        return [
            'ecology-and-environmental-protection-60520200' => [
                $this->course('ECOL-100', 1, 1, [
                    'en' => 'Introduction to Ecology and Environmental Protection',
                    'uz' => 'Ekologiya va atrof-muhit muhofazasiga kirish',
                    'ru' => 'Введение в экологию и охрану окружающей среды',
                    'ar' => 'مدخل إلى علم البيئة وحماية البيئة',
                ]),
                $this->course('ECOL-101', 1, 2, [
                    'en' => 'General Ecology',
                    'uz' => 'Umumiy ekologiya',
                    'ru' => 'Общая экология',
                    'ar' => 'علم البيئة العام',
                ]),
                $this->course('ECOL-102', 2, 1, [
                    'en' => 'Environmental Chemistry',
                    'uz' => 'Atrof-muhit kimyosi',
                    'ru' => 'Химия окружающей среды',
                    'ar' => 'كيمياء البيئة',
                ]),
                $this->course('ECOL-103', 2, 1, [
                    'en' => 'Environmental Monitoring',
                    'uz' => 'Ekologik monitoring',
                    'ru' => 'Экологический мониторинг',
                    'ar' => 'الرصد البيئي',
                ]),
                $this->course('ECOL-104', 2, 2, [
                    'en' => 'Environmental Impact Assessment',
                    'uz' => 'Atrof-muhitga ta’sirni baholash',
                    'ru' => 'Оценка воздействия на окружающую среду',
                    'ar' => 'تقييم الأثر البيئي',
                ]),
                $this->course('ECOL-105', 2, 2, [
                    'en' => 'Waste Management',
                    'uz' => 'Chiqindilarni boshqarish',
                    'ru' => 'Управление отходами',
                    'ar' => 'إدارة النفايات',
                ]),
                $this->course('ECOL-106', 3, 1, [
                    'en' => 'Water and Soil Protection',
                    'uz' => 'Suv va tuproqni muhofaza qilish',
                    'ru' => 'Охрана воды и почвы',
                    'ar' => 'حماية المياه والتربة',
                ]),
                $this->course('ECOL-107', 3, 1, [
                    'en' => 'Air Pollution Control',
                    'uz' => 'Atmosfera havosi ifloslanishini nazorat qilish',
                    'ru' => 'Контроль загрязнения атмосферного воздуха',
                    'ar' => 'مكافحة تلوث الهواء',
                ]),
                $this->course('ECOL-108', 3, 2, [
                    'en' => 'Environmental Law and Standards',
                    'uz' => 'Ekologik huquq va standartlar',
                    'ru' => 'Экологическое право и стандарты',
                    'ar' => 'القانون والمعايير البيئية',
                ]),
                $this->course('ECOL-109', 3, 2, [
                    'en' => 'GIS in Environmental Studies',
                    'uz' => 'Ekologik tadqiqotlarda GIS',
                    'ru' => 'ГИС в экологических исследованиях',
                    'ar' => 'نظم المعلومات الجغرافية في الدراسات البيئية',
                ]),
                $this->course('ECOL-110', 4, 1, [
                    'en' => 'Sustainable Development and Green Technologies',
                    'uz' => 'Barqaror rivojlanish va yashil texnologiyalar',
                    'ru' => 'Устойчивое развитие и зеленые технологии',
                    'ar' => 'التنمية المستدامة والتقنيات الخضراء',
                ]),
                $this->course('ECOL-111', 4, 1, [
                    'en' => 'Environmental Audit and Risk Assessment',
                    'uz' => 'Ekologik audit va xavflarni baholash',
                    'ru' => 'Экологический аудит и оценка рисков',
                    'ar' => 'التدقيق البيئي وتقييم المخاطر',
                ]),
                $this->course('ECOL-112', 4, 2, [
                    'en' => 'Industrial Practice in Environmental Protection',
                    'uz' => 'Atrof-muhit muhofazasi bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по охране окружающей среды',
                    'ar' => 'التدريب الصناعي في حماية البيئة',
                ]),
                $this->course('ECOL-113', 4, 2, [
                    'en' => 'Graduation Project in Ecology',
                    'uz' => 'Ekologiya bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по экологии',
                    'ar' => 'مشروع التخرج في علم البيئة',
                ]),
            ],
            'chemical-engineering-60710100' => [
                $this->course('CHEM-100', 1, 1, [
                    'en' => 'Introduction to Chemical Engineering',
                    'uz' => 'Kimyo muhandisligiga kirish',
                    'ru' => 'Введение в химическую инженерию',
                    'ar' => 'مدخل إلى الهندسة الكيميائية',
                ]),
                $this->course('CHEM-101', 1, 2, [
                    'en' => 'General and Inorganic Chemistry',
                    'uz' => 'Umumiy va noorganik kimyo',
                    'ru' => 'Общая и неорганическая химия',
                    'ar' => 'الكيمياء العامة وغير العضوية',
                ]),
                $this->course('CHEM-102', 2, 1, [
                    'en' => 'Organic Chemistry',
                    'uz' => 'Organik kimyo',
                    'ru' => 'Органическая химия',
                    'ar' => 'الكيمياء العضوية',
                ]),
                $this->course('CHEM-103', 2, 1, [
                    'en' => 'Physical and Colloid Chemistry',
                    'uz' => 'Fizik va kolloid kimyo',
                    'ru' => 'Физическая и коллоидная химия',
                    'ar' => 'الكيمياء الفيزيائية والغروية',
                ]),
                $this->course('CHEM-104', 2, 2, [
                    'en' => 'Chemical Process Principles',
                    'uz' => 'Kimyoviy jarayonlar asoslari',
                    'ru' => 'Основы химических процессов',
                    'ar' => 'أساسيات العمليات الكيميائية',
                ]),
                $this->course('CHEM-105', 2, 2, [
                    'en' => 'Heat and Mass Transfer',
                    'uz' => 'Issiqlik va massa almashinuvi',
                    'ru' => 'Тепло- и массообмен',
                    'ar' => 'انتقال الحرارة والكتلة',
                ]),
                $this->course('CHEM-106', 3, 1, [
                    'en' => 'Chemical Reactors',
                    'uz' => 'Kimyoviy reaktorlar',
                    'ru' => 'Химические реакторы',
                    'ar' => 'المفاعلات الكيميائية',
                ]),
                $this->course('CHEM-107', 3, 1, [
                    'en' => 'Process Equipment and Apparatus',
                    'uz' => 'Jarayon jihozlari va apparatlari',
                    'ru' => 'Процессное оборудование и аппараты',
                    'ar' => 'معدات وأجهزة العمليات',
                ]),
                $this->course('CHEM-108', 3, 2, [
                    'en' => 'Polymer and Composite Materials',
                    'uz' => 'Polimer va kompozit materiallar',
                    'ru' => 'Полимерные и композиционные материалы',
                    'ar' => 'المواد البوليمرية والمركبة',
                ]),
                $this->course('CHEM-109', 3, 2, [
                    'en' => 'Process Control and Automation',
                    'uz' => 'Jarayonlarni boshqarish va avtomatlashtirish',
                    'ru' => 'Управление и автоматизация процессов',
                    'ar' => 'التحكم في العمليات وأتمتتها',
                ]),
                $this->course('CHEM-110', 4, 1, [
                    'en' => 'Industrial Safety in Chemical Production',
                    'uz' => 'Kimyoviy ishlab chiqarishda sanoat xavfsizligi',
                    'ru' => 'Промышленная безопасность в химическом производстве',
                    'ar' => 'السلامة الصناعية في الإنتاج الكيميائي',
                ]),
                $this->course('CHEM-111', 4, 1, [
                    'en' => 'Chemical Technology Design',
                    'uz' => 'Kimyoviy texnologiyani loyihalash',
                    'ru' => 'Проектирование химической технологии',
                    'ar' => 'تصميم التقنية الكيميائية',
                ]),
                $this->course('CHEM-112', 4, 2, [
                    'en' => 'Industrial Practice in Chemical Engineering',
                    'uz' => 'Kimyo muhandisligi bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по химической инженерии',
                    'ar' => 'التدريب الصناعي في الهندسة الكيميائية',
                ]),
                $this->course('CHEM-113', 4, 2, [
                    'en' => 'Graduation Project in Chemical Engineering',
                    'uz' => 'Kimyo muhandisligi bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по химической инженерии',
                    'ar' => 'مشروع التخرج في الهندسة الكيميائية',
                ]),
            ],
            'biotechnology-60710200' => [
                $this->course('BIOT-100', 1, 1, [
                    'en' => 'Introduction to Biotechnology',
                    'uz' => 'Biotexnologiyaga kirish',
                    'ru' => 'Введение в биотехнологию',
                    'ar' => 'مدخل إلى التكنولوجيا الحيوية',
                ]),
                $this->course('BIOT-101', 1, 2, [
                    'en' => 'General Biology and Microbiology',
                    'uz' => 'Umumiy biologiya va mikrobiologiya',
                    'ru' => 'Общая биология и микробиология',
                    'ar' => 'الأحياء العامة وعلم الأحياء الدقيقة',
                ]),
                $this->course('BIOT-102', 2, 1, [
                    'en' => 'Biochemistry',
                    'uz' => 'Biokimyo',
                    'ru' => 'Биохимия',
                    'ar' => 'الكيمياء الحيوية',
                ]),
                $this->course('BIOT-103', 2, 1, [
                    'en' => 'Cell Biology and Genetics',
                    'uz' => 'Hujayra biologiyasi va genetika',
                    'ru' => 'Клеточная биология и генетика',
                    'ar' => 'بيولوجيا الخلية والوراثة',
                ]),
                $this->course('BIOT-104', 2, 2, [
                    'en' => 'Biotechnological Processes',
                    'uz' => 'Biotexnologik jarayonlar',
                    'ru' => 'Биотехнологические процессы',
                    'ar' => 'العمليات الحيوية التقنية',
                ]),
                $this->course('BIOT-105', 2, 2, [
                    'en' => 'Fermentation Technology',
                    'uz' => 'Fermentatsiya texnologiyasi',
                    'ru' => 'Технология ферментации',
                    'ar' => 'تقنية التخمر',
                ]),
                $this->course('BIOT-106', 3, 1, [
                    'en' => 'Bioreactors and Process Equipment',
                    'uz' => 'Bioreaktorlar va jarayon jihozlari',
                    'ru' => 'Биореакторы и процессное оборудование',
                    'ar' => 'المفاعلات الحيوية ومعدات العمليات',
                ]),
                $this->course('BIOT-107', 3, 1, [
                    'en' => 'Industrial Microbiology',
                    'uz' => 'Sanoat mikrobiologiyasi',
                    'ru' => 'Промышленная микробиология',
                    'ar' => 'علم الأحياء الدقيقة الصناعي',
                ]),
                $this->course('BIOT-108', 3, 2, [
                    'en' => 'Food and Agricultural Biotechnology',
                    'uz' => 'Oziq-ovqat va qishloq xo‘jaligi biotexnologiyasi',
                    'ru' => 'Пищевая и сельскохозяйственная биотехнология',
                    'ar' => 'التكنولوجيا الحيوية الغذائية والزراعية',
                ]),
                $this->course('BIOT-109', 3, 2, [
                    'en' => 'Environmental Biotechnology',
                    'uz' => 'Ekologik biotexnologiya',
                    'ru' => 'Экологическая биотехнология',
                    'ar' => 'التكنولوجيا الحيوية البيئية',
                ]),
                $this->course('BIOT-110', 4, 1, [
                    'en' => 'Quality Control in Biotechnology',
                    'uz' => 'Biotexnologiyada sifat nazorati',
                    'ru' => 'Контроль качества в биотехнологии',
                    'ar' => 'مراقبة الجودة في التكنولوجيا الحيوية',
                ]),
                $this->course('BIOT-111', 4, 1, [
                    'en' => 'Biosafety and Bioethics',
                    'uz' => 'Bioxavfsizlik va bioetika',
                    'ru' => 'Биобезопасность и биоэтика',
                    'ar' => 'السلامة الحيوية وأخلاقيات الأحياء',
                ]),
                $this->course('BIOT-112', 4, 2, [
                    'en' => 'Industrial Practice in Biotechnology',
                    'uz' => 'Biotexnologiya bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по биотехнологии',
                    'ar' => 'التدريب الصناعي في التكنولوجيا الحيوية',
                ]),
                $this->course('BIOT-113', 4, 2, [
                    'en' => 'Graduation Project in Biotechnology',
                    'uz' => 'Biotexnologiya bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по биотехнологии',
                    'ar' => 'مشروع التخرج في التكنولوجيا الحيوية',
                ]),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
