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
            'uz' => $name.' fani geodeziya, kartografiya, kadastr, yer resurslari va GIS bo‘yicha kasbiy ko‘nikmalarni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональные навыки в области геодезии, картографии, кадастра, земельных ресурсов и ГИС.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المهارات المهنية في الجيوديسيا والخرائط والسجل العقاري وموارد الأراضي ونظم المعلومات الجغرافية.',
            default => $name.' develops professional skills in geodesy, cartography, cadastre, land resources, and GIS.',
        };
    }

    private function programCourses(): array
    {
        return [
            'geodesy-and-geomatics-60721500' => [
                $this->course('GEOM-100', 1, 1, ['en' => 'Introduction to Geodesy and Geomatics', 'uz' => 'Geodeziya va geomatikaga kirish', 'ru' => 'Введение в геодезию и геоматику', 'ar' => 'مدخل إلى الجيوديسيا والجيوماتكس']),
                $this->course('GEOM-101', 1, 2, ['en' => 'Geodesy', 'uz' => 'Geodeziya', 'ru' => 'Геодезия', 'ar' => 'الجيوديسيا']),
                $this->course('GEOM-102', 2, 1, ['en' => 'Higher Geodesy', 'uz' => 'Oliy geodeziya', 'ru' => 'Высшая геодезия', 'ar' => 'الجيوديسيا العليا']),
                $this->course('GEOM-103', 2, 1, ['en' => 'Modern Geodetic Instruments', 'uz' => 'Zamonaviy geodezik asboblar', 'ru' => 'Современные геодезические приборы', 'ar' => 'الأجهزة الجيوديسية الحديثة']),
                $this->course('GEOM-104', 2, 2, ['en' => 'Engineering Geodesy', 'uz' => 'Muhandislik geodeziyasi', 'ru' => 'Инженерная геодезия', 'ar' => 'الجيوديسيا الهندسية']),
                $this->course('GEOM-105', 2, 2, ['en' => 'Satellite Geodesy and GNSS', 'uz' => 'Sun’iy yo‘ldosh geodeziyasi va GNSS', 'ru' => 'Спутниковая геодезия и GNSS', 'ar' => 'الجيوديسيا بالأقمار الصناعية و GNSS']),
                $this->course('GEOM-106', 3, 1, ['en' => 'Digital Photogrammetry', 'uz' => 'Raqamli fotogrammetriya', 'ru' => 'Цифровая фотограмметрия', 'ar' => 'الفوتوغرامترية الرقمية']),
                $this->course('GEOM-107', 3, 1, ['en' => 'Geographic Information Systems', 'uz' => 'Geografik axborot tizimlari', 'ru' => 'Географические информационные системы', 'ar' => 'نظم المعلومات الجغرافية']),
                $this->course('GEOM-108', 3, 2, ['en' => 'Geodetic Works in Land Management', 'uz' => 'Yer tuzishda geodezik ishlar', 'ru' => 'Геодезические работы в землеустройстве', 'ar' => 'الأعمال الجيوديسية في إدارة الأراضي']),
                $this->course('GEOM-109', 3, 2, ['en' => 'Three-Dimensional Modeling in GIS', 'uz' => 'GISda uch o‘lchamli modellashtirish', 'ru' => 'Трехмерное моделирование в ГИС', 'ar' => 'النمذجة ثلاثية الأبعاد في GIS']),
                $this->course('GEOM-110', 4, 1, ['en' => 'Geodatabase and Spatial Data Architecture', 'uz' => 'Geoma’lumotlar bazasi va fazoviy ma’lumotlar arxitekturasi', 'ru' => 'Геобаза данных и архитектура пространственных данных', 'ar' => 'قاعدة البيانات الجغرافية وبنية البيانات المكانية']),
                $this->course('GEOM-111', 4, 1, ['en' => 'Data Acquisition and Spatial Analysis', 'uz' => 'Ma’lumot yig‘ish va fazoviy tahlil', 'ru' => 'Сбор данных и пространственный анализ', 'ar' => 'جمع البيانات والتحليل المكاني']),
                $this->course('GEOM-112', 4, 2, ['en' => 'Industrial Practice in Geodesy and Geomatics', 'uz' => 'Geodeziya va geomatika bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по геодезии и геоматике', 'ar' => 'التدريب الصناعي في الجيوديسيا والجيوماتكس']),
                $this->course('GEOM-113', 4, 2, ['en' => 'Graduation Project in Geodesy and Geomatics', 'uz' => 'Geodeziya va geomatika bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по геодезии и геоматике', 'ar' => 'مشروع التخرج في الجيوديسيا والجيوماتكس']),
            ],
            'cartography-and-remote-sensing-60721600' => [
                $this->course('CARS-100', 1, 1, ['en' => 'Introduction to Cartography and Remote Sensing', 'uz' => 'Kartografiya va masofadan zondlashga kirish', 'ru' => 'Введение в картографию и дистанционное зондирование', 'ar' => 'مدخل إلى علم الخرائط والاستشعار عن بعد']),
                $this->course('CARS-101', 1, 2, ['en' => 'Cartography', 'uz' => 'Kartografiya', 'ru' => 'Картография', 'ar' => 'علم الخرائط']),
                $this->course('CARS-102', 2, 1, ['en' => 'Map Science', 'uz' => 'Xaritashunoslik', 'ru' => 'Картоведение', 'ar' => 'دراسة الخرائط']),
                $this->course('CARS-103', 2, 1, ['en' => 'Cartographic Design', 'uz' => 'Kartografik dizayn', 'ru' => 'Картографическое проектирование', 'ar' => 'تصميم الخرائط']),
                $this->course('CARS-104', 2, 2, ['en' => 'Digital Cartography', 'uz' => 'Raqamli kartografiya', 'ru' => 'Цифровая картография', 'ar' => 'الخرائط الرقمية']),
                $this->course('CARS-105', 2, 2, ['en' => 'Remote Sensing Fundamentals', 'uz' => 'Masofadan zondlash asoslari', 'ru' => 'Основы дистанционного зондирования', 'ar' => 'أساسيات الاستشعار عن بعد']),
                $this->course('CARS-106', 3, 1, ['en' => 'Satellite Image Processing', 'uz' => 'Sun’iy yo‘ldosh tasvirlarini qayta ishlash', 'ru' => 'Обработка спутниковых изображений', 'ar' => 'معالجة صور الأقمار الصناعية']),
                $this->course('CARS-107', 3, 1, ['en' => 'GIS Technologies', 'uz' => 'GIS texnologiyalari', 'ru' => 'ГИС-технологии', 'ar' => 'تقنيات نظم المعلومات الجغرافية']),
                $this->course('CARS-108', 3, 2, ['en' => 'Thematic Mapping', 'uz' => 'Mavzuli xaritalash', 'ru' => 'Тематическое картографирование', 'ar' => 'رسم الخرائط الموضوعية']),
                $this->course('CARS-109', 3, 2, ['en' => 'Aerial Survey and Photogrammetry', 'uz' => 'Aerotasvirga olish va fotogrammetriya', 'ru' => 'Аэрофотосъемка и фотограмметрия', 'ar' => 'المسح الجوي والفوتوغرامترية']),
                $this->course('CARS-110', 4, 1, ['en' => 'Spatial Data Integration', 'uz' => 'Fazoviy ma’lumotlarni integratsiyalash', 'ru' => 'Интеграция пространственных данных', 'ar' => 'تكامل البيانات المكانية']),
                $this->course('CARS-111', 4, 1, ['en' => 'Web Mapping and Geoportals', 'uz' => 'Veb xaritalash va geoportallar', 'ru' => 'Веб-картография и геопорталы', 'ar' => 'خرائط الويب والبوابات الجغرافية']),
                $this->course('CARS-112', 4, 2, ['en' => 'Industrial Practice in Cartography and Remote Sensing', 'uz' => 'Kartografiya va masofadan zondlash bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по картографии и дистанционному зондированию', 'ar' => 'التدريب الصناعي في الخرائط والاستشعار عن بعد']),
                $this->course('CARS-113', 4, 2, ['en' => 'Graduation Project in Cartography and Remote Sensing', 'uz' => 'Kartografiya va masofadan zondlash bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по картографии и дистанционному зондированию', 'ar' => 'مشروع التخرج في الخرائط والاستشعار عن بعد']),
            ],
            'cadastre-60721700' => [
                $this->course('CADS-100', 1, 1, ['en' => 'Introduction to Cadastre', 'uz' => 'Kadastrga kirish', 'ru' => 'Введение в кадастр', 'ar' => 'مدخل إلى السجل العقاري']),
                $this->course('CADS-101', 1, 2, ['en' => 'Fundamentals of State Cadastre', 'uz' => 'Davlat kadastri asoslari', 'ru' => 'Основы государственного кадастра', 'ar' => 'أساسيات السجل الحكومي']),
                $this->course('CADS-102', 2, 1, ['en' => 'Territory Cadastre', 'uz' => 'Hududlar kadastri', 'ru' => 'Кадастр территорий', 'ar' => 'سجل المناطق']),
                $this->course('CADS-103', 2, 1, ['en' => 'Digital Land Cadastre', 'uz' => 'Raqamli yer kadastri', 'ru' => 'Цифровой земельный кадастр', 'ar' => 'السجل الرقمي للأراضي']),
                $this->course('CADS-104', 2, 2, ['en' => 'Real Estate Cadastre', 'uz' => 'Ko‘chmas mulk kadastri', 'ru' => 'Кадастр недвижимости', 'ar' => 'السجل العقاري للعقارات']),
                $this->course('CADS-105', 2, 2, ['en' => 'Geodesy for Cadastre', 'uz' => 'Kadastr uchun geodeziya', 'ru' => 'Геодезия для кадастра', 'ar' => 'الجيوديسيا للسجل العقاري']),
                $this->course('CADS-106', 3, 1, ['en' => 'GIS in Cadastre', 'uz' => 'Kadastrda GIS', 'ru' => 'ГИС в кадастре', 'ar' => 'نظم المعلومات الجغرافية في السجل العقاري']),
                $this->course('CADS-107', 3, 1, ['en' => 'Accounting and Valuation of Land Plots', 'uz' => 'Yer uchastkalarini hisobga olish va baholash', 'ru' => 'Учет и оценка земельных участков', 'ar' => 'تسجيل وتقييم قطع الأراضي']),
                $this->course('CADS-108', 3, 2, ['en' => 'Regulation of Land Relations', 'uz' => 'Yer munosabatlarini tartibga solish', 'ru' => 'Регулирование земельных отношений', 'ar' => 'تنظيم علاقات الأراضي']),
                $this->course('CADS-109', 3, 2, ['en' => 'Automated Cadastre Systems', 'uz' => 'Avtomatlashtirilgan kadastr tizimlari', 'ru' => 'Автоматизированные кадастровые системы', 'ar' => 'أنظمة السجل العقاري المؤتمتة']),
                $this->course('CADS-110', 4, 1, ['en' => 'Cadastral Mapping and Documentation', 'uz' => 'Kadastr xaritalari va hujjatlari', 'ru' => 'Кадастровое картирование и документация', 'ar' => 'خرائط ووثائق السجل العقاري']),
                $this->course('CADS-111', 4, 1, ['en' => 'Cadastral Data Quality Control', 'uz' => 'Kadastr ma’lumotlari sifat nazorati', 'ru' => 'Контроль качества кадастровых данных', 'ar' => 'مراقبة جودة بيانات السجل العقاري']),
                $this->course('CADS-112', 4, 2, ['en' => 'Industrial Practice in Cadastre', 'uz' => 'Kadastr bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по кадастру', 'ar' => 'التدريب الصناعي في السجل العقاري']),
                $this->course('CADS-113', 4, 2, ['en' => 'Graduation Project in Cadastre', 'uz' => 'Kadastr bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по кадастру', 'ar' => 'مشروع التخرج في السجل العقاري']),
            ],
            'land-cadastre-and-land-management-60811600' => [
                $this->course('LCLM-100', 1, 1, ['en' => 'Introduction to Land Cadastre and Land Management', 'uz' => 'Yer kadastri va yer tuzishga kirish', 'ru' => 'Введение в земельный кадастр и землеустройство', 'ar' => 'مدخل إلى سجل الأراضي وإدارة الأراضي']),
                $this->course('LCLM-101', 1, 2, ['en' => 'Land Resource Management', 'uz' => 'Yer resurslarini boshqarish', 'ru' => 'Управление земельными ресурсами', 'ar' => 'إدارة موارد الأراضي']),
                $this->course('LCLM-102', 2, 1, ['en' => 'Land Management Design', 'uz' => 'Yer tuzishni loyihalash', 'ru' => 'Проектирование землеустройства', 'ar' => 'تصميم إدارة الأراضي']),
                $this->course('LCLM-103', 2, 1, ['en' => 'Theoretical Foundations of Land Management', 'uz' => 'Yer tuzishning nazariy asoslari', 'ru' => 'Теоретические основы землеустройства', 'ar' => 'الأسس النظرية لإدارة الأراضي']),
                $this->course('LCLM-104', 2, 2, ['en' => 'Organization and Planning of Land Management Works', 'uz' => 'Yer tuzish ishlarini tashkil etish va rejalashtirish', 'ru' => 'Организация и планирование землеустроительных работ', 'ar' => 'تنظيم وتخطيط أعمال إدارة الأراضي']),
                $this->course('LCLM-105', 2, 2, ['en' => 'Soil Science and Land Quality Assessment', 'uz' => 'Tuproqshunoslik va yer sifatini baholash', 'ru' => 'Почвоведение и оценка качества земель', 'ar' => 'علم التربة وتقييم جودة الأراضي']),
                $this->course('LCLM-106', 3, 1, ['en' => 'Land Cadastre', 'uz' => 'Yer kadastri', 'ru' => 'Земельный кадастр', 'ar' => 'سجل الأراضي']),
                $this->course('LCLM-107', 3, 1, ['en' => 'Automated Systems in Land Management Design', 'uz' => 'Yer tuzishni loyihalashda avtomatlashtirilgan tizimlar', 'ru' => 'Автоматизированные системы в проектировании землеустройства', 'ar' => 'الأنظمة المؤتمتة في تصميم إدارة الأراضي']),
                $this->course('LCLM-108', 3, 2, ['en' => 'Formation of Land Plots', 'uz' => 'Yer uchastkalarini shakllantirish', 'ru' => 'Формирование земельных участков', 'ar' => 'تشكيل قطع الأراضي']),
                $this->course('LCLM-109', 3, 2, ['en' => 'Legal Foundations of Land Resource Management', 'uz' => 'Yer resurslarini boshqarishning huquqiy asoslari', 'ru' => 'Правовые основы управления земельными ресурсами', 'ar' => 'الأسس القانونية لإدارة موارد الأراضي']),
                $this->course('LCLM-110', 4, 1, ['en' => 'Integrated Land Use Management', 'uz' => 'Yerdan foydalanishni integratsiyalashgan boshqarish', 'ru' => 'Интегрированное управление землепользованием', 'ar' => 'الإدارة المتكاملة لاستخدام الأراضي']),
                $this->course('LCLM-111', 4, 1, ['en' => 'Territorial Development and Land Monitoring', 'uz' => 'Hududiy rivojlanish va yer monitoringi', 'ru' => 'Территориальное развитие и мониторинг земель', 'ar' => 'التنمية الإقليمية ومراقبة الأراضي']),
                $this->course('LCLM-112', 4, 2, ['en' => 'Industrial Practice in Land Management', 'uz' => 'Yer tuzish bo‘yicha ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по землеустройству', 'ar' => 'التدريب الصناعي في إدارة الأراضي']),
                $this->course('LCLM-113', 4, 2, ['en' => 'Graduation Project in Land Cadastre and Land Management', 'uz' => 'Yer kadastri va yer tuzish bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по земельному кадастру и землеустройству', 'ar' => 'مشروع التخرج في سجل الأراضي وإدارة الأراضي']),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
