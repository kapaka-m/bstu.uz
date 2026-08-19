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
            'uz' => $name.' fani nazariy bilim, amaliy mashg‘ulotlar va kasbiy kompetensiyalarni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает теоретические знания, практические навыки и профессиональные компетенции.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المعرفة النظرية والمهارات العملية والكفاءات المهنية.',
            default => $name.' develops theoretical knowledge, practical skills, and professional competencies.',
        };
    }

    private function programCourses(): array
    {
        return [
            'automation-of-technological-processes-and-production-60710900' => [
                $this->course('AUTO-200', 1, 1, ['en' => 'Introduction to Automation of Technological Processes', 'uz' => 'Texnologik jarayonlarni avtomatlashtirishga kirish', 'ru' => 'Введение в автоматизацию технологических процессов', 'ar' => 'مدخل إلى أتمتة العمليات التكنولوجية']),
                $this->course('AUTO-201', 1, 2, ['en' => 'Control Theory', 'uz' => 'Boshqaruv nazariyasi', 'ru' => 'Теория управления', 'ar' => 'نظرية التحكم']),
                $this->course('AUTO-202', 2, 1, ['en' => 'Technological Measurements and Instruments', 'uz' => 'Texnologik o‘lchashlar va asboblar', 'ru' => 'Технологические измерения и приборы', 'ar' => 'القياسات والأجهزة التكنولوجية']),
                $this->course('AUTO-203', 2, 1, ['en' => 'Elements and Devices of Control Systems', 'uz' => 'Boshqaruv tizimlari elementlari va qurilmalari', 'ru' => 'Элементы и устройства систем управления', 'ar' => 'عناصر وأجهزة أنظمة التحكم']),
                $this->course('AUTO-204', 2, 2, ['en' => 'Technical Means of Automation', 'uz' => 'Avtomatlashtirishning texnik vositalari', 'ru' => 'Технические средства автоматизации', 'ar' => 'الوسائل التقنية للأتمتة']),
                $this->course('AUTO-205', 2, 2, ['en' => 'Electrical Automation', 'uz' => 'Elektr avtomatika', 'ru' => 'Электрическая автоматика', 'ar' => 'الأتمتة الكهربائية']),
                $this->course('AUTO-206', 3, 1, ['en' => 'Automation of Technological Processes', 'uz' => 'Texnologik jarayonlarni avtomatlashtirish', 'ru' => 'Автоматизация технологических процессов', 'ar' => 'أتمتة العمليات التكنولوجية']),
                $this->course('AUTO-207', 3, 1, ['en' => 'Computer Control Technologies', 'uz' => 'Kompyuterli boshqaruv texnologiyalari', 'ru' => 'Компьютерные технологии управления', 'ar' => 'تقنيات التحكم بالحاسوب']),
                $this->course('AUTO-208', 3, 2, ['en' => 'Automated Control Systems for Technological Processes', 'uz' => 'Texnologik jarayonlarni avtomatlashtirilgan boshqarish tizimlari', 'ru' => 'Автоматизированные системы управления технологическими процессами', 'ar' => 'أنظمة التحكم المؤتمتة للعمليات التكنولوجية']),
                $this->course('AUTO-209', 3, 2, ['en' => 'Modeling and Optimization of Technological Processes', 'uz' => 'Texnologik jarayonlarni modellashtirish va optimallashtirish', 'ru' => 'Моделирование и оптимизация технологических процессов', 'ar' => 'نمذجة وتحسين العمليات التكنولوجية']),
                $this->course('AUTO-210', 4, 1, ['en' => 'Digital Automation and Control Systems', 'uz' => 'Raqamli avtomatika va boshqaruv tizimlari', 'ru' => 'Цифровая автоматика и системы управления', 'ar' => 'الأتمتة الرقمية وأنظمة التحكم']),
                $this->course('AUTO-211', 4, 1, ['en' => 'Design and Adjustment of Automation Systems', 'uz' => 'Avtomatlashtirish tizimlarini loyihalash va sozlash', 'ru' => 'Проектирование и наладка систем автоматизации', 'ar' => 'تصميم وضبط أنظمة الأتمتة']),
                $this->course('AUTO-212', 4, 2, ['en' => 'Industrial Practice in Automation', 'uz' => 'Avtomatlashtirish bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по автоматизации', 'ar' => 'التدريب الصناعي في الأتمتة']),
                $this->course('AUTO-213', 4, 2, ['en' => 'Graduation Project in Automation', 'uz' => 'Avtomatlashtirish bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по автоматизации', 'ar' => 'مشروع التخرج في الأتمتة']),
            ],
            'mechatronics-and-robotics-60711000' => [
                $this->course('MECH-200', 1, 1, ['en' => 'Introduction to Mechatronics and Robotics', 'uz' => 'Mexatronika va robototexnikaga kirish', 'ru' => 'Введение в мехатронику и робототехнику', 'ar' => 'مدخل إلى الميكاترونكس والروبوتات']),
                $this->course('MECH-201', 1, 2, ['en' => 'Engineering Mechanics for Mechatronics', 'uz' => 'Mexatronika uchun muhandislik mexanikasi', 'ru' => 'Инженерная механика для мехатроники', 'ar' => 'الميكانيكا الهندسية للميكاترونكس']),
                $this->course('MECH-202', 2, 1, ['en' => 'Sensors and Actuators', 'uz' => 'Sensorlar va ijro mexanizmlari', 'ru' => 'Датчики и исполнительные механизмы', 'ar' => 'المستشعرات والمشغلات']),
                $this->course('MECH-203', 2, 1, ['en' => 'Microcontrollers and Embedded Systems', 'uz' => 'Mikrokontrollerlar va o‘rnatilgan tizimlar', 'ru' => 'Микроконтроллеры и встраиваемые системы', 'ar' => 'المتحكمات الدقيقة والأنظمة المدمجة']),
                $this->course('MECH-204', 2, 2, ['en' => 'Electric Drives in Mechatronics', 'uz' => 'Mexatronikada elektr yuritmalar', 'ru' => 'Электроприводы в мехатронике', 'ar' => 'المحركات الكهربائية في الميكاترونكس']),
                $this->course('MECH-205', 2, 2, ['en' => 'Robot Kinematics and Dynamics', 'uz' => 'Robotlar kinematikasi va dinamikasi', 'ru' => 'Кинематика и динамика роботов', 'ar' => 'حركيات وديناميكيات الروبوتات']),
                $this->course('MECH-206', 3, 1, ['en' => 'Industrial Robots', 'uz' => 'Sanoat robotlari', 'ru' => 'Промышленные роботы', 'ar' => 'الروبوتات الصناعية']),
                $this->course('MECH-207', 3, 1, ['en' => 'Mechatronic System Design', 'uz' => 'Mexatronik tizimlarni loyihalash', 'ru' => 'Проектирование мехатронных систем', 'ar' => 'تصميم الأنظمة الميكاترونية']),
                $this->course('MECH-208', 3, 2, ['en' => 'Machine Vision and Robotics', 'uz' => 'Mashina ko‘rishi va robototexnika', 'ru' => 'Машинное зрение и робототехника', 'ar' => 'الرؤية الآلية والروبوتات']),
                $this->course('MECH-209', 3, 2, ['en' => 'Automation and PLC Systems', 'uz' => 'Avtomatlashtirish va PLC tizimlari', 'ru' => 'Автоматизация и системы PLC', 'ar' => 'الأتمتة وأنظمة PLC']),
                $this->course('MECH-210', 4, 1, ['en' => 'Mobile Robotics', 'uz' => 'Mobil robototexnika', 'ru' => 'Мобильная робототехника', 'ar' => 'الروبوتات المتنقلة']),
                $this->course('MECH-211', 4, 1, ['en' => 'Intelligent Mechatronic Systems', 'uz' => 'Intellektual mexatronik tizimlar', 'ru' => 'Интеллектуальные мехатронные системы', 'ar' => 'الأنظمة الميكاترونية الذكية']),
                $this->course('MECH-212', 4, 2, ['en' => 'Industrial Practice in Mechatronics and Robotics', 'uz' => 'Mexatronika va robototexnika bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по мехатронике и робототехнике', 'ar' => 'التدريب الصناعي في الميكاترونكس والروبوتات']),
                $this->course('MECH-213', 4, 2, ['en' => 'Graduation Project in Mechatronics and Robotics', 'uz' => 'Mexatronika va robototexnika bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по мехатронике и робототехнике', 'ar' => 'مشروع التخرج في الميكاترونكس والروبوتات']),
            ],
            'biomedical-engineering-60711100' => [
                $this->course('BIOM-100', 1, 1, ['en' => 'Introduction to Biomedical Engineering', 'uz' => 'Biotibbiyot muhandisligiga kirish', 'ru' => 'Введение в биомедицинскую инженерию', 'ar' => 'مدخل إلى الهندسة الطبية الحيوية']),
                $this->course('BIOM-101', 1, 2, ['en' => 'Human Anatomy and Physiology for Engineers', 'uz' => 'Muhandislar uchun anatomiya va fiziologiya', 'ru' => 'Анатомия и физиология человека для инженеров', 'ar' => 'تشريح وفسيولوجيا الإنسان للمهندسين']),
                $this->course('BIOM-102', 2, 1, ['en' => 'Biomedical Sensors and Signals', 'uz' => 'Biotibbiy sensorlar va signallar', 'ru' => 'Биомедицинские датчики и сигналы', 'ar' => 'المستشعرات والإشارات الطبية الحيوية']),
                $this->course('BIOM-103', 2, 1, ['en' => 'Medical Electronics', 'uz' => 'Tibbiy elektronika', 'ru' => 'Медицинская электроника', 'ar' => 'الإلكترونيات الطبية']),
                $this->course('BIOM-104', 2, 2, ['en' => 'Biomedical Instrumentation', 'uz' => 'Biotibbiy asbobsozlik', 'ru' => 'Биомедицинское приборостроение', 'ar' => 'الأجهزة الطبية الحيوية']),
                $this->course('BIOM-105', 2, 2, ['en' => 'Medical Imaging Systems', 'uz' => 'Tibbiy tasvirlash tizimlari', 'ru' => 'Системы медицинской визуализации', 'ar' => 'أنظمة التصوير الطبي']),
                $this->course('BIOM-106', 3, 1, ['en' => 'Biomaterials', 'uz' => 'Biomateriallar', 'ru' => 'Биоматериалы', 'ar' => 'المواد الحيوية']),
                $this->course('BIOM-107', 3, 1, ['en' => 'Clinical Engineering', 'uz' => 'Klinik muhandislik', 'ru' => 'Клиническая инженерия', 'ar' => 'الهندسة السريرية']),
                $this->course('BIOM-108', 3, 2, ['en' => 'Medical Device Design', 'uz' => 'Tibbiy qurilmalarni loyihalash', 'ru' => 'Проектирование медицинских устройств', 'ar' => 'تصميم الأجهزة الطبية']),
                $this->course('BIOM-109', 3, 2, ['en' => 'Biomedical Data Processing', 'uz' => 'Biotibbiy ma’lumotlarni qayta ishlash', 'ru' => 'Обработка биомедицинских данных', 'ar' => 'معالجة البيانات الطبية الحيوية']),
                $this->course('BIOM-110', 4, 1, ['en' => 'Safety and Standards for Medical Devices', 'uz' => 'Tibbiy qurilmalar xavfsizligi va standartlari', 'ru' => 'Безопасность и стандарты медицинских устройств', 'ar' => 'سلامة ومعايير الأجهزة الطبية']),
                $this->course('BIOM-111', 4, 1, ['en' => 'Hospital Equipment Maintenance', 'uz' => 'Shifoxona jihozlariga texnik xizmat ko‘rsatish', 'ru' => 'Техническое обслуживание больничного оборудования', 'ar' => 'صيانة معدات المستشفيات']),
                $this->course('BIOM-112', 4, 2, ['en' => 'Industrial Practice in Biomedical Engineering', 'uz' => 'Biotibbiyot muhandisligi bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по биомедицинской инженерии', 'ar' => 'التدريب الصناعي في الهندسة الطبية الحيوية']),
                $this->course('BIOM-113', 4, 2, ['en' => 'Graduation Project in Biomedical Engineering', 'uz' => 'Biotibbiyot muhandisligi bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по биомедицинской инженерии', 'ar' => 'مشروع التخرج في الهندسة الطبية الحيوية']),
            ],
            'computer-engineering-60610300' => [
                $this->course('COMP-200', 1, 1, ['en' => 'Introduction to Computer Engineering', 'uz' => 'Kompyuter muhandisligiga kirish', 'ru' => 'Введение в компьютерную инженерию', 'ar' => 'مدخل إلى هندسة الحاسوب']),
                $this->course('COMP-201', 1, 2, ['en' => 'Digital Logic Design', 'uz' => 'Raqamli mantiqiy loyihalash', 'ru' => 'Проектирование цифровой логики', 'ar' => 'تصميم المنطق الرقمي']),
                $this->course('COMP-202', 2, 1, ['en' => 'Computer Architecture', 'uz' => 'Kompyuter arxitekturasi', 'ru' => 'Архитектура компьютеров', 'ar' => 'معمارية الحاسوب']),
                $this->course('COMP-203', 2, 1, ['en' => 'Programming for Computer Engineers', 'uz' => 'Kompyuter muhandislari uchun dasturlash', 'ru' => 'Программирование для компьютерных инженеров', 'ar' => 'البرمجة لمهندسي الحاسوب']),
                $this->course('COMP-204', 2, 2, ['en' => 'Operating Systems', 'uz' => 'Operatsion tizimlar', 'ru' => 'Операционные системы', 'ar' => 'أنظمة التشغيل']),
                $this->course('COMP-205', 2, 2, ['en' => 'Computer Networks', 'uz' => 'Kompyuter tarmoqlari', 'ru' => 'Компьютерные сети', 'ar' => 'شبكات الحاسوب']),
                $this->course('COMP-206', 3, 1, ['en' => 'Embedded Systems', 'uz' => 'O‘rnatilgan tizimlar', 'ru' => 'Встраиваемые системы', 'ar' => 'الأنظمة المدمجة']),
                $this->course('COMP-207', 3, 1, ['en' => 'Microprocessor Systems', 'uz' => 'Mikroprotsessor tizimlari', 'ru' => 'Микропроцессорные системы', 'ar' => 'أنظمة المعالجات الدقيقة']),
                $this->course('COMP-208', 3, 2, ['en' => 'Cybersecurity Fundamentals', 'uz' => 'Kiberxavfsizlik asoslari', 'ru' => 'Основы кибербезопасности', 'ar' => 'أساسيات الأمن السيبراني']),
                $this->course('COMP-209', 3, 2, ['en' => 'Database Systems', 'uz' => 'Ma’lumotlar bazasi tizimlari', 'ru' => 'Системы баз данных', 'ar' => 'أنظمة قواعد البيانات']),
                $this->course('COMP-210', 4, 1, ['en' => 'Internet of Things Systems', 'uz' => 'Buyumlar interneti tizimlari', 'ru' => 'Системы интернета вещей', 'ar' => 'أنظمة إنترنت الأشياء']),
                $this->course('COMP-211', 4, 1, ['en' => 'Computer System Design', 'uz' => 'Kompyuter tizimlarini loyihalash', 'ru' => 'Проектирование компьютерных систем', 'ar' => 'تصميم أنظمة الحاسوب']),
                $this->course('COMP-212', 4, 2, ['en' => 'Industrial Practice in Computer Engineering', 'uz' => 'Kompyuter muhandisligi bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по компьютерной инженерии', 'ar' => 'التدريب الصناعي في هندسة الحاسوب']),
                $this->course('COMP-213', 4, 2, ['en' => 'Graduation Project in Computer Engineering', 'uz' => 'Kompyuter muhandisligi bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по компьютерной инженерии', 'ar' => 'مشروع التخرج في هندسة الحاسوب']),
            ],
            'software-engineering-60610400' => [
                $this->course('SOFT-200', 1, 1, ['en' => 'Introduction to Software Engineering', 'uz' => 'Dasturiy injiniringga kirish', 'ru' => 'Введение в программную инженерию', 'ar' => 'مدخل إلى هندسة البرمجيات']),
                $this->course('SOFT-201', 1, 2, ['en' => 'Programming Fundamentals', 'uz' => 'Dasturlash asoslari', 'ru' => 'Основы программирования', 'ar' => 'أساسيات البرمجة']),
                $this->course('SOFT-202', 2, 1, ['en' => 'Object-Oriented Programming', 'uz' => 'Obyektga yo‘naltirilgan dasturlash', 'ru' => 'Объектно-ориентированное программирование', 'ar' => 'البرمجة كائنية التوجه']),
                $this->course('SOFT-203', 2, 1, ['en' => 'Data Structures and Algorithms', 'uz' => 'Ma’lumotlar tuzilmalari va algoritmlar', 'ru' => 'Структуры данных и алгоритмы', 'ar' => 'هياكل البيانات والخوارزميات']),
                $this->course('SOFT-204', 2, 2, ['en' => 'Software Requirements Engineering', 'uz' => 'Dasturiy talablar muhandisligi', 'ru' => 'Инженерия требований к программному обеспечению', 'ar' => 'هندسة متطلبات البرمجيات']),
                $this->course('SOFT-205', 2, 2, ['en' => 'Database Application Development', 'uz' => 'Ma’lumotlar bazasi ilovalarini ishlab chiqish', 'ru' => 'Разработка приложений баз данных', 'ar' => 'تطوير تطبيقات قواعد البيانات']),
                $this->course('SOFT-206', 3, 1, ['en' => 'Web Application Development', 'uz' => 'Veb ilovalarni ishlab chiqish', 'ru' => 'Разработка веб-приложений', 'ar' => 'تطوير تطبيقات الويب']),
                $this->course('SOFT-207', 3, 1, ['en' => 'Software Architecture', 'uz' => 'Dasturiy arxitektura', 'ru' => 'Архитектура программного обеспечения', 'ar' => 'معمارية البرمجيات']),
                $this->course('SOFT-208', 3, 2, ['en' => 'Software Testing and Quality Assurance', 'uz' => 'Dasturiy ta’minotni testlash va sifat kafolati', 'ru' => 'Тестирование ПО и обеспечение качества', 'ar' => 'اختبار البرمجيات وضمان الجودة']),
                $this->course('SOFT-209', 3, 2, ['en' => 'DevOps and Cloud Technologies', 'uz' => 'DevOps va bulut texnologiyalari', 'ru' => 'DevOps и облачные технологии', 'ar' => 'DevOps وتقنيات السحابة']),
                $this->course('SOFT-210', 4, 1, ['en' => 'Mobile Application Development', 'uz' => 'Mobil ilovalarni ishlab chiqish', 'ru' => 'Разработка мобильных приложений', 'ar' => 'تطوير تطبيقات الهاتف المحمول']),
                $this->course('SOFT-211', 4, 1, ['en' => 'Software Project Management', 'uz' => 'Dasturiy loyihalarni boshqarish', 'ru' => 'Управление программными проектами', 'ar' => 'إدارة مشاريع البرمجيات']),
                $this->course('SOFT-212', 4, 2, ['en' => 'Industrial Practice in Software Engineering', 'uz' => 'Dasturiy injiniring bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по программной инженерии', 'ar' => 'التدريب الصناعي في هندسة البرمجيات']),
                $this->course('SOFT-213', 4, 2, ['en' => 'Graduation Project in Software Engineering', 'uz' => 'Dasturiy injiniring bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по программной инженерии', 'ar' => 'مشروع التخرج في هندسة البرمجيات']),
            ],
            'artificial-intelligence-60610500' => [
                $this->course('AINT-200', 1, 1, ['en' => 'Introduction to Artificial Intelligence', 'uz' => 'Sun’iy intellektga kirish', 'ru' => 'Введение в искусственный интеллект', 'ar' => 'مدخل إلى الذكاء الاصطناعي']),
                $this->course('AINT-201', 1, 2, ['en' => 'Python Programming for AI', 'uz' => 'AI uchun Python dasturlash', 'ru' => 'Программирование на Python для ИИ', 'ar' => 'برمجة Python للذكاء الاصطناعي']),
                $this->course('AINT-202', 2, 1, ['en' => 'Linear Algebra and Probability for AI', 'uz' => 'AI uchun chiziqli algebra va ehtimollik', 'ru' => 'Линейная алгебра и вероятность для ИИ', 'ar' => 'الجبر الخطي والاحتمالات للذكاء الاصطناعي']),
                $this->course('AINT-203', 2, 1, ['en' => 'Data Structures for AI Systems', 'uz' => 'AI tizimlari uchun ma’lumotlar tuzilmalari', 'ru' => 'Структуры данных для систем ИИ', 'ar' => 'هياكل البيانات لأنظمة الذكاء الاصطناعي']),
                $this->course('AINT-204', 2, 2, ['en' => 'Machine Learning', 'uz' => 'Mashinali o‘qitish', 'ru' => 'Машинное обучение', 'ar' => 'تعلم الآلة']),
                $this->course('AINT-205', 2, 2, ['en' => 'Data Mining and Analytics', 'uz' => 'Ma’lumotlarni qazib olish va tahlil qilish', 'ru' => 'Интеллектуальный анализ данных и аналитика', 'ar' => 'تنقيب البيانات والتحليلات']),
                $this->course('AINT-206', 3, 1, ['en' => 'Deep Learning', 'uz' => 'Chuqur o‘qitish', 'ru' => 'Глубокое обучение', 'ar' => 'التعلم العميق']),
                $this->course('AINT-207', 3, 1, ['en' => 'Computer Vision', 'uz' => 'Kompyuter ko‘rishi', 'ru' => 'Компьютерное зрение', 'ar' => 'الرؤية الحاسوبية']),
                $this->course('AINT-208', 3, 2, ['en' => 'Natural Language Processing', 'uz' => 'Tabiiy tilni qayta ishlash', 'ru' => 'Обработка естественного языка', 'ar' => 'معالجة اللغة الطبيعية']),
                $this->course('AINT-209', 3, 2, ['en' => 'Intelligent Decision Support Systems', 'uz' => 'Intellektual qarorlarni qo‘llab-quvvatlash tizimlari', 'ru' => 'Интеллектуальные системы поддержки принятия решений', 'ar' => 'أنظمة دعم القرار الذكية']),
                $this->course('AINT-210', 4, 1, ['en' => 'AI Ethics and Safety', 'uz' => 'AI etikasi va xavfsizligi', 'ru' => 'Этика и безопасность ИИ', 'ar' => 'أخلاقيات وسلامة الذكاء الاصطناعي']),
                $this->course('AINT-211', 4, 1, ['en' => 'AI System Deployment', 'uz' => 'AI tizimlarini joriy etish', 'ru' => 'Развертывание систем ИИ', 'ar' => 'نشر أنظمة الذكاء الاصطناعي']),
                $this->course('AINT-212', 4, 2, ['en' => 'Industrial Practice in Artificial Intelligence', 'uz' => 'Sun’iy intellekt bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по искусственному интеллекту', 'ar' => 'التدريب الصناعي في الذكاء الاصطناعي']),
                $this->course('AINT-213', 4, 2, ['en' => 'Graduation Project in Artificial Intelligence', 'uz' => 'Sun’iy intellekt bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по искусственному интеллекту', 'ar' => 'مشروع التخرج في الذكاء الاصطناعي']),
            ],
            'logistics-61010400' => [
                $this->course('LOGI-100', 1, 1, ['en' => 'Introduction to Logistics', 'uz' => 'Logistikaga kirish', 'ru' => 'Введение в логистику', 'ar' => 'مدخل إلى اللوجستيات']),
                $this->course('LOGI-101', 1, 2, ['en' => 'Supply Chain Management', 'uz' => 'Ta’minot zanjirini boshqarish', 'ru' => 'Управление цепями поставок', 'ar' => 'إدارة سلسلة الإمداد']),
                $this->course('LOGI-102', 2, 1, ['en' => 'Transport Logistics', 'uz' => 'Transport logistikasi', 'ru' => 'Транспортная логистика', 'ar' => 'لوجستيات النقل']),
                $this->course('LOGI-103', 2, 1, ['en' => 'Warehouse Management', 'uz' => 'Omborlarni boshqarish', 'ru' => 'Управление складами', 'ar' => 'إدارة المستودعات']),
                $this->course('LOGI-104', 2, 2, ['en' => 'Inventory Management', 'uz' => 'Zaxiralarni boshqarish', 'ru' => 'Управление запасами', 'ar' => 'إدارة المخزون']),
                $this->course('LOGI-105', 2, 2, ['en' => 'International Logistics', 'uz' => 'Xalqaro logistika', 'ru' => 'Международная логистика', 'ar' => 'اللوجستيات الدولية']),
                $this->course('LOGI-106', 3, 1, ['en' => 'Customs and Trade Documentation', 'uz' => 'Bojxona va savdo hujjatlari', 'ru' => 'Таможня и торговая документация', 'ar' => 'الجمارك ووثائق التجارة']),
                $this->course('LOGI-107', 3, 1, ['en' => 'Logistics Information Systems', 'uz' => 'Logistika axborot tizimlari', 'ru' => 'Логистические информационные системы', 'ar' => 'أنظمة المعلومات اللوجستية']),
                $this->course('LOGI-108', 3, 2, ['en' => 'Distribution and Last-Mile Logistics', 'uz' => 'Taqsimot va so‘nggi mil logistikasi', 'ru' => 'Распределение и логистика последней мили', 'ar' => 'التوزيع ولوجستيات الميل الأخير']),
                $this->course('LOGI-109', 3, 2, ['en' => 'Logistics Cost Analysis', 'uz' => 'Logistika xarajatlarini tahlil qilish', 'ru' => 'Анализ логистических затрат', 'ar' => 'تحليل تكاليف اللوجستيات']),
                $this->course('LOGI-110', 4, 1, ['en' => 'Digital Logistics and Analytics', 'uz' => 'Raqamli logistika va analitika', 'ru' => 'Цифровая логистика и аналитика', 'ar' => 'اللوجستيات الرقمية والتحليلات']),
                $this->course('LOGI-111', 4, 1, ['en' => 'Green Logistics', 'uz' => 'Yashil logistika', 'ru' => 'Зеленая логистика', 'ar' => 'اللوجستيات الخضراء']),
                $this->course('LOGI-112', 4, 2, ['en' => 'Industrial Practice in Logistics', 'uz' => 'Logistika bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по логистике', 'ar' => 'التدريب الصناعي في اللوجستيات']),
                $this->course('LOGI-113', 4, 2, ['en' => 'Graduation Project in Logistics', 'uz' => 'Logistika bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по логистике', 'ar' => 'مشروع التخرج في اللوجستيات']),
            ],
            'finance-and-financial-technologies-60410500' => [
                $this->course('FINT-100', 1, 1, ['en' => 'Introduction to Finance and Financial Technologies', 'uz' => 'Moliya va moliyaviy texnologiyalarga kirish', 'ru' => 'Введение в финансы и финансовые технологии', 'ar' => 'مدخل إلى المالية والتقنيات المالية']),
                $this->course('FINT-101', 1, 2, ['en' => 'Financial Accounting Fundamentals', 'uz' => 'Moliyaviy hisob asoslari', 'ru' => 'Основы финансового учета', 'ar' => 'أساسيات المحاسبة المالية']),
                $this->course('FINT-102', 2, 1, ['en' => 'Corporate Finance', 'uz' => 'Korporativ moliya', 'ru' => 'Корпоративные финансы', 'ar' => 'التمويل المؤسسي']),
                $this->course('FINT-103', 2, 1, ['en' => 'Banking and Payment Systems', 'uz' => 'Bank ishi va to‘lov tizimlari', 'ru' => 'Банковское дело и платежные системы', 'ar' => 'الأعمال المصرفية وأنظمة الدفع']),
                $this->course('FINT-104', 2, 2, ['en' => 'Financial Markets and Institutions', 'uz' => 'Moliya bozorlari va institutlari', 'ru' => 'Финансовые рынки и институты', 'ar' => 'الأسواق والمؤسسات المالية']),
                $this->course('FINT-105', 2, 2, ['en' => 'FinTech Products and Services', 'uz' => 'FinTech mahsulotlari va xizmatlari', 'ru' => 'Финтех-продукты и услуги', 'ar' => 'منتجات وخدمات التقنية المالية']),
                $this->course('FINT-106', 3, 1, ['en' => 'Investment Analysis', 'uz' => 'Investitsiya tahlili', 'ru' => 'Инвестиционный анализ', 'ar' => 'تحليل الاستثمار']),
                $this->course('FINT-107', 3, 1, ['en' => 'Risk Management in Finance', 'uz' => 'Moliyada risklarni boshqarish', 'ru' => 'Управление рисками в финансах', 'ar' => 'إدارة المخاطر المالية']),
                $this->course('FINT-108', 3, 2, ['en' => 'Digital Banking', 'uz' => 'Raqamli bank ishi', 'ru' => 'Цифровой банкинг', 'ar' => 'المصرفية الرقمية']),
                $this->course('FINT-109', 3, 2, ['en' => 'Financial Data Analytics', 'uz' => 'Moliyaviy ma’lumotlar analitikasi', 'ru' => 'Аналитика финансовых данных', 'ar' => 'تحليلات البيانات المالية']),
                $this->course('FINT-110', 4, 1, ['en' => 'Blockchain and Digital Assets', 'uz' => 'Blokcheyn va raqamli aktivlar', 'ru' => 'Блокчейн и цифровые активы', 'ar' => 'البلوك تشين والأصول الرقمية']),
                $this->course('FINT-111', 4, 1, ['en' => 'Financial Regulation and Compliance', 'uz' => 'Moliyaviy tartibga solish va muvofiqlik', 'ru' => 'Финансовое регулирование и комплаенс', 'ar' => 'التنظيم المالي والامتثال']),
                $this->course('FINT-112', 4, 2, ['en' => 'Industrial Practice in Finance and FinTech', 'uz' => 'Moliya va FinTech bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по финансам и финтеху', 'ar' => 'التدريب الصناعي في المالية والتقنية المالية']),
                $this->course('FINT-113', 4, 2, ['en' => 'Graduation Project in Finance and Financial Technologies', 'uz' => 'Moliya va moliyaviy texnologiyalar bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по финансам и финансовым технологиям', 'ar' => 'مشروع التخرج في المالية والتقنيات المالية']),
            ],
            'accounting-60410200' => [
                $this->course('ACCT-100', 1, 1, ['en' => 'Introduction to Accounting', 'uz' => 'Buxgalteriya hisobiga kirish', 'ru' => 'Введение в бухгалтерский учет', 'ar' => 'مدخل إلى المحاسبة']),
                $this->course('ACCT-101', 1, 2, ['en' => 'Financial Accounting', 'uz' => 'Moliyaviy hisob', 'ru' => 'Финансовый учет', 'ar' => 'المحاسبة المالية']),
                $this->course('ACCT-102', 2, 1, ['en' => 'Managerial Accounting', 'uz' => 'Boshqaruv hisobi', 'ru' => 'Управленческий учет', 'ar' => 'المحاسبة الإدارية']),
                $this->course('ACCT-103', 2, 1, ['en' => 'Taxation', 'uz' => 'Soliqqa tortish', 'ru' => 'Налогообложение', 'ar' => 'الضرائب']),
                $this->course('ACCT-104', 2, 2, ['en' => 'Audit Fundamentals', 'uz' => 'Audit asoslari', 'ru' => 'Основы аудита', 'ar' => 'أساسيات التدقيق']),
                $this->course('ACCT-105', 2, 2, ['en' => 'Accounting Information Systems', 'uz' => 'Buxgalteriya axborot tizimlari', 'ru' => 'Бухгалтерские информационные системы', 'ar' => 'أنظمة المعلومات المحاسبية']),
                $this->course('ACCT-106', 3, 1, ['en' => 'Cost Accounting', 'uz' => 'Xarajatlar hisobi', 'ru' => 'Учет затрат', 'ar' => 'محاسبة التكاليف']),
                $this->course('ACCT-107', 3, 1, ['en' => 'International Financial Reporting Standards', 'uz' => 'Xalqaro moliyaviy hisobot standartlari', 'ru' => 'Международные стандарты финансовой отчетности', 'ar' => 'المعايير الدولية لإعداد التقارير المالية']),
                $this->course('ACCT-108', 3, 2, ['en' => 'Financial Statement Analysis', 'uz' => 'Moliyaviy hisobotlarni tahlil qilish', 'ru' => 'Анализ финансовой отчетности', 'ar' => 'تحليل القوائم المالية']),
                $this->course('ACCT-109', 3, 2, ['en' => 'Public Sector Accounting', 'uz' => 'Davlat sektori hisobi', 'ru' => 'Учет в государственном секторе', 'ar' => 'محاسبة القطاع العام']),
                $this->course('ACCT-110', 4, 1, ['en' => 'Internal Control and Compliance', 'uz' => 'Ichki nazorat va muvofiqlik', 'ru' => 'Внутренний контроль и комплаенс', 'ar' => 'الرقابة الداخلية والامتثال']),
                $this->course('ACCT-111', 4, 1, ['en' => 'Digital Accounting Technologies', 'uz' => 'Raqamli buxgalteriya texnologiyalari', 'ru' => 'Цифровые бухгалтерские технологии', 'ar' => 'تقنيات المحاسبة الرقمية']),
                $this->course('ACCT-112', 4, 2, ['en' => 'Industrial Practice in Accounting', 'uz' => 'Buxgalteriya hisobi bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по бухгалтерскому учету', 'ar' => 'التدريب الصناعي في المحاسبة']),
                $this->course('ACCT-113', 4, 2, ['en' => 'Graduation Project in Accounting', 'uz' => 'Buxgalteriya hisobi bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по бухгалтерскому учету', 'ar' => 'مشروع التخرج في المحاسبة']),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
