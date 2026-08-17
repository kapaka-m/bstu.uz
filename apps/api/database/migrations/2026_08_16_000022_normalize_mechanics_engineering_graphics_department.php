<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'mechanics-engineering-graphics')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->deletePlaceholderStaff($departmentId);
            $this->normalizeDepartmentSections($departmentId);
            $this->normalizeStaff($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function deletePlaceholderStaff(int $departmentId): void
    {
        $ids = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereIn('slug', [
                'mechanics-engineering-graphics-0-dr-ravshan-b-xalilov',
                'mechanics-engineering-graphics-1-feruza-a-tursunova',
                'mechanics-engineering-graphics-2-jasur-o-sharipov',
            ])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
        DB::table('staff_profiles')->whereIn('id', $ids)->delete();
    }

    private function normalizeDepartmentSections(int $departmentId): void
    {
        foreach ($this->departmentTranslations() as $locale => $payload) {
            DB::table('department_translations')->updateOrInsert(
                ['department_id' => $departmentId, 'locale' => $locale],
                [
                    'name' => $payload['name'],
                    'short_name' => $payload['short_name'],
                    'description' => $payload['description'],
                    'content_sections' => json_encode($payload['sections'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'meta_title' => $payload['name'],
                    'meta_description' => $payload['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function normalizeStaff(int $departmentId): void
    {
        $profileId = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->where('slug', 'mechanics-engineering-graphics-fakhriddin-yusupovich-khabibov')
            ->value('id');

        if (! $profileId) {
            return;
        }

        DB::table('staff_profiles')->where('id', $profileId)->update([
            'sort_order' => 1,
            'is_active' => true,
            'updated_at' => now(),
        ]);

        foreach ($this->headTranslations() as $locale => $translation) {
            DB::table('staff_profile_translations')->updateOrInsert(
                ['staff_profile_id' => $profileId, 'locale' => $locale],
                [
                    'full_name' => $translation['name'],
                    'position' => $translation['position'],
                    'bio' => $translation['bio'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function departmentTranslations(): array
    {
        return [
            'en' => [
                'name' => 'Mechanics and Engineering Graphics',
                'short_name' => 'Mechanics and Engineering Graphics',
                'description' => 'The Department of Mechanics and Engineering Graphics provides foundational engineering training in mechanics, technical drawing, computer-aided design, machine elements, and visual engineering communication.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'About the Department', 'items' => ['The department supports engineering programs through theoretical mechanics, applied mechanics, engineering graphics, CAD skills, laboratory work, and project-based technical visualization.']],
                    ['key' => 'subjects', 'title' => 'Taught Subjects', 'items' => [
                        'Theoretical mechanics',
                        'Applied mechanics',
                        'Engineering graphics',
                        'Descriptive geometry',
                        'Technical drawing',
                        'Computer-aided design',
                        'Machine elements',
                        'Strength of materials',
                        'Mechanisms and machine theory',
                        'Laboratory practice and course projects',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Programs and Specializations', 'items' => ['60712300 - Mechanical Engineering']],
                    ['key' => 'research', 'title' => 'Research Work', 'items' => [
                        'Improving teaching methods for mechanics, engineering graphics, and computer-aided design.',
                        'Development of applied mechanics solutions for engineering structures, machine elements, and technical systems.',
                        'Use of digital modeling and visualization tools in engineering education and design practice.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'International Cooperation', 'items' => ['The department cooperates with engineering departments, production enterprises, design organizations, technical schools, and partner institutions to strengthen practical skills and project work.']],
                    ['key' => 'department_structure', 'title' => 'Department Structure', 'items' => ['Head of Department: Xabibov Faxriddin Yusupovich', 'Office hours: Monday-Friday 14:00-16:00', 'Phone: +998 93 379 65 00', 'Email: faxrilo@mail.ru']],
                ],
            ],
            'uz' => [
                'name' => 'Mexanika va muhandislik grafikasi kafedrasi',
                'short_name' => 'Mexanika va muhandislik grafikasi kafedrasi',
                'description' => 'Mexanika va muhandislik grafikasi kafedrasi mexanika, texnik chizmachilik, kompyuter yordamida loyihalash, mashina detallari va muhandislik vizual kommunikatsiyasi bo‘yicha tayanch muhandislik tayyorgarligini ta’minlaydi.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'Kafedra haqida', 'items' => ['Kafedra muhandislik dasturlarini nazariy mexanika, amaliy mexanika, muhandislik grafikasi, CAD ko‘nikmalari, laboratoriya ishlari va loyiha asosidagi texnik vizualizatsiya orqali qo‘llab-quvvatlaydi.']],
                    ['key' => 'subjects', 'title' => 'O‘qitiladigan fanlar', 'items' => [
                        'Nazariy mexanika',
                        'Amaliy mexanika',
                        'Muhandislik grafikasi',
                        'Chizma geometriya',
                        'Texnik chizmachilik',
                        'Kompyuter yordamida loyihalash',
                        'Mashina detallari',
                        'Materiallar qarshiligi',
                        'Mexanizmlar va mashinalar nazariyasi',
                        'Laboratoriya amaliyoti va kurs loyihalari',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Dasturlar va mutaxassisliklar', 'items' => ['60712300 - Mexanika muhandisligi']],
                    ['key' => 'research', 'title' => 'Ilmiy ishlar', 'items' => [
                        'Mexanika, muhandislik grafikasi va kompyuter yordamida loyihalashni o‘qitish metodlarini takomillashtirish.',
                        'Muhandislik konstruksiyalari, mashina detallari va texnik tizimlar uchun amaliy mexanika yechimlarini ishlab chiqish.',
                        'Muhandislik ta’limi va loyiha amaliyotida raqamli modellashtirish hamda vizualizatsiya vositalarini qo‘llash.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Xalqaro hamkorlik', 'items' => ['Kafedra amaliy ko‘nikmalar va loyiha ishlarini kuchaytirish uchun muhandislik kafedralari, ishlab chiqarish korxonalari, loyiha tashkilotlari, texnikumlar va hamkor muassasalar bilan hamkorlik qiladi.']],
                    ['key' => 'department_structure', 'title' => 'Kafedra tuzilmasi', 'items' => ['Kafedra mudiri: Xabibov Faxriddin Yusupovich', 'Qabul vaqti: Dushanba-juma 14:00-16:00', 'Telefon: +998 93 379 65 00', 'Elektron pochta: faxrilo@mail.ru']],
                ],
            ],
            'ru' => [
                'name' => 'Кафедра механики и инженерной графики',
                'short_name' => 'Кафедра механики и инженерной графики',
                'description' => 'Кафедра механики и инженерной графики обеспечивает базовую инженерную подготовку по механике, техническому черчению, компьютерному проектированию, деталям машин и визуальной инженерной коммуникации.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'О кафедре', 'items' => ['Кафедра поддерживает инженерные программы через теоретическую механику, прикладную механику, инженерную графику, навыки CAD, лабораторные работы и проектную техническую визуализацию.']],
                    ['key' => 'subjects', 'title' => 'Учебные дисциплины', 'items' => [
                        'Теоретическая механика',
                        'Прикладная механика',
                        'Инженерная графика',
                        'Начертательная геометрия',
                        'Техническое черчение',
                        'Компьютерное проектирование',
                        'Детали машин',
                        'Сопротивление материалов',
                        'Теория механизмов и машин',
                        'Лабораторная практика и курсовые проекты',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Программы и специализации', 'items' => ['60712300 - Машиностроение']],
                    ['key' => 'research', 'title' => 'Научная работа', 'items' => [
                        'Совершенствование методов преподавания механики, инженерной графики и компьютерного проектирования.',
                        'Разработка прикладных механических решений для инженерных конструкций, деталей машин и технических систем.',
                        'Использование цифрового моделирования и визуализации в инженерном образовании и проектной практике.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Международное сотрудничество', 'items' => ['Кафедра сотрудничает с инженерными кафедрами, производственными предприятиями, проектными организациями, техникумами и партнерскими учреждениями для усиления практических навыков и проектной работы.']],
                    ['key' => 'department_structure', 'title' => 'Структура кафедры', 'items' => ['Заведующий кафедрой: Хабибов Фахриддин Юсупович', 'Часы приема: понедельник-пятница 14:00-16:00', 'Телефон: +998 93 379 65 00', 'Электронная почта: faxrilo@mail.ru']],
                ],
            ],
            'ar' => [
                'name' => 'قسم الميكانيكا والرسم الهندسي',
                'short_name' => 'قسم الميكانيكا والرسم الهندسي',
                'description' => 'يوفر قسم الميكانيكا والرسم الهندسي إعدادًا هندسيًا أساسيًا في الميكانيكا والرسم الفني والتصميم بالحاسوب وعناصر الآلات والاتصال الهندسي البصري.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'عن القسم', 'items' => ['يدعم القسم البرامج الهندسية من خلال الميكانيكا النظرية والميكانيكا التطبيقية والرسم الهندسي ومهارات CAD والعمل المخبري والتصور التقني القائم على المشاريع.']],
                    ['key' => 'subjects', 'title' => 'المواد التي تدرس في القسم', 'items' => [
                        'الميكانيكا النظرية',
                        'الميكانيكا التطبيقية',
                        'الرسم الهندسي',
                        'الهندسة الوصفية',
                        'الرسم الفني',
                        'التصميم بالحاسوب',
                        'عناصر الآلات',
                        'مقاومة المواد',
                        'نظرية الآليات والآلات',
                        'التطبيقات المخبرية ومشروعات المقرر',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'البرامج والتخصصات', 'items' => ['60712300 - الهندسة الميكانيكية']],
                    ['key' => 'research', 'title' => 'الأعمال البحثية', 'items' => [
                        'تحسين طرق تدريس الميكانيكا والرسم الهندسي والتصميم بالحاسوب.',
                        'تطوير حلول ميكانيكية تطبيقية للمنشآت الهندسية وعناصر الآلات والأنظمة التقنية.',
                        'استخدام أدوات النمذجة الرقمية والعرض المرئي في التعليم الهندسي وممارسة التصميم.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'التعاون الدولي', 'items' => ['يتعاون القسم مع الأقسام الهندسية والمؤسسات الإنتاجية ومؤسسات التصميم والمدارس التقنية والمؤسسات الشريكة لتعزيز المهارات العملية والعمل بالمشروعات.']],
                    ['key' => 'department_structure', 'title' => 'هيكل القسم', 'items' => ['رئيس القسم: خبيبوف فخر الدين يوسفوفيتش', 'ساعات الاستقبال: الاثنين-الجمعة 14:00-16:00', 'الهاتف: +998 93 379 65 00', 'البريد الإلكتروني: faxrilo@mail.ru']],
                ],
            ],
        ];
    }

    private function headTranslations(): array
    {
        return [
            'en' => ['name' => 'Xabibov Faxriddin Yusupovich', 'position' => 'Head of Department', 'bio' => 'Xabibov Faxriddin Yusupovich serves as Head of Department in Mechanics and Engineering Graphics, contributing to academic, methodological, and engineering development.'],
            'uz' => ['name' => 'Xabibov Faxriddin Yusupovich', 'position' => 'Kafedra mudiri', 'bio' => 'Xabibov Faxriddin Yusupovich Mexanika va muhandislik grafikasi kafedrasida kafedra mudiri sifatida ta’lim, metodik va muhandislik faoliyatiga hissa qo‘shadi.'],
            'ru' => ['name' => 'Хабибов Фахриддин Юсупович', 'position' => 'Заведующий кафедрой', 'bio' => 'Хабибов Фахриддин Юсупович работает заведующим кафедрой механики и инженерной графики.'],
            'ar' => ['name' => 'خبيبوف فخر الدين يوسفوفيتش', 'position' => 'رئيس القسم', 'bio' => 'خبيبوف فخر الدين يوسفوفيتش يعمل رئيسًا لقسم الميكانيكا والرسم الهندسي ويساهم في التطوير الأكاديمي والمنهجي والهندسي.'],
        ];
    }
};
