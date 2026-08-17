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
        }
    }

    public function down(): void
    {
        // Content corrections are intentionally non-destructive.
    }

    protected function descriptionFor(string $name, string $locale): string
    {
        return match ($locale) {
            'uz' => $name.' fani yengil sanoat, dizayn va ishlab chiqarish bo‘yicha kasbiy tayyorgarlikni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональную подготовку в сфере легкой промышленности, дизайна и производства.',
            'ar' => 'يطور مقرر '.$name.' الإعداد المهني في مجال الصناعة الخفيفة والتصميم والإنتاج.',
            default => $name.' develops professional preparation in light industry, design, and production.',
        };
    }

    protected function c(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }

    protected function programCourses(): array
    {
        return [
            'design-footwear-and-accessories-design-60210400' => [
                $this->c('FDES-100', 1, 1, ['en' => 'Introduction to Footwear and Accessories Design', 'uz' => 'Poyabzal va aksessuarlar dizayniga kirish', 'ru' => 'Введение в дизайн обуви и аксессуаров', 'ar' => 'مدخل إلى تصميم الأحذية والإكسسوارات']),
                $this->c('FDES-101', 1, 1, ['en' => 'Drawing and Composition for Product Design', 'uz' => 'Mahsulot dizayni uchun chizmachilik va kompozitsiya', 'ru' => 'Рисунок и композиция для предметного дизайна', 'ar' => 'الرسم والتكوين لتصميم المنتجات']),
                $this->c('FDES-102', 1, 2, ['en' => 'Materials for Footwear and Leather Goods', 'uz' => 'Poyabzal va charm buyumlar materiallari', 'ru' => 'Материалы для обуви и кожгалантереи', 'ar' => 'مواد الأحذية والمنتجات الجلدية']),
                $this->c('FDES-103', 1, 2, ['en' => 'Fashion History and Design Trends', 'uz' => 'Moda tarixi va dizayn tendensiyalari', 'ru' => 'История моды и тенденции дизайна', 'ar' => 'تاريخ الموضة واتجاهات التصميم']),
                $this->c('FDES-104', 2, 1, ['en' => 'Footwear Construction and Pattern Making', 'uz' => 'Poyabzal konstruksiyasi va andaza tayyorlash', 'ru' => 'Конструирование обуви и разработка лекал', 'ar' => 'بناء الأحذية وإعداد القوالب']),
                $this->c('FDES-105', 2, 1, ['en' => 'Accessory Design Studio', 'uz' => 'Aksessuarlar dizayni studiyasi', 'ru' => 'Студия дизайна аксессуаров', 'ar' => 'استوديو تصميم الإكسسوارات']),
                $this->c('FDES-106', 2, 2, ['en' => 'Computer-Aided Design for Footwear', 'uz' => 'Poyabzal uchun avtomatlashtirilgan loyihalash', 'ru' => 'Автоматизированное проектирование обуви', 'ar' => 'التصميم بمساعدة الحاسوب للأحذية']),
                $this->c('FDES-107', 2, 2, ['en' => 'Ergonomics and Comfort in Footwear', 'uz' => 'Poyabzal ergonomikasi va qulayligi', 'ru' => 'Эргономика и комфорт обуви', 'ar' => 'بيئة العمل والراحة في الأحذية']),
                $this->c('FDES-108', 3, 1, ['en' => 'Footwear Manufacturing Technology', 'uz' => 'Poyabzal ishlab chiqarish texnologiyasi', 'ru' => 'Технология производства обуви', 'ar' => 'تكنولوجيا تصنيع الأحذية']),
                $this->c('FDES-109', 3, 2, ['en' => 'Quality Control of Footwear Products', 'uz' => 'Poyabzal mahsulotlari sifatini nazorat qilish', 'ru' => 'Контроль качества обувной продукции', 'ar' => 'مراقبة جودة منتجات الأحذية']),
                $this->c('FDES-110', 4, 1, ['en' => 'Branding and Portfolio Development', 'uz' => 'Brending va portfolio tayyorlash', 'ru' => 'Брендинг и разработка портфолио', 'ar' => 'بناء العلامة التجارية وتطوير ملف الأعمال']),
                $this->c('FDES-111', 4, 2, ['en' => 'Graduation Project in Footwear and Accessories Design', 'uz' => 'Poyabzal va aksessuarlar dizayni bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по дизайну обуви и аксессуаров', 'ar' => 'مشروع التخرج في تصميم الأحذية والإكسسوارات']),
            ],
            'design-clothing-and-textile-design-60210400' => [
                $this->c('CDES-100', 1, 1, ['en' => 'Introduction to Clothing and Textile Design', 'uz' => 'Kiyim va to‘qimachilik dizayniga kirish', 'ru' => 'Введение в дизайн одежды и текстиля', 'ar' => 'مدخل إلى تصميم الملابس والمنسوجات']),
                $this->c('CDES-101', 1, 1, ['en' => 'Color Theory and Composition', 'uz' => 'Rang nazariyasi va kompozitsiya', 'ru' => 'Теория цвета и композиция', 'ar' => 'نظرية اللون والتكوين']),
                $this->c('CDES-102', 1, 2, ['en' => 'Textile Materials Science', 'uz' => 'To‘qimachilik materialshunosligi', 'ru' => 'Материаловедение текстиля', 'ar' => 'علم مواد المنسوجات']),
                $this->c('CDES-103', 1, 2, ['en' => 'Fashion Illustration', 'uz' => 'Moda illyustratsiyasi', 'ru' => 'Модная иллюстрация', 'ar' => 'الرسم التوضيحي للأزياء']),
                $this->c('CDES-104', 2, 1, ['en' => 'Garment Pattern Construction', 'uz' => 'Kiyim andazalarini konstruksiyalash', 'ru' => 'Конструирование лекал одежды', 'ar' => 'بناء قوالب الملابس']),
                $this->c('CDES-105', 2, 1, ['en' => 'Sewing Technology and Equipment', 'uz' => 'Tikuv texnologiyasi va jihozlari', 'ru' => 'Технология швейного производства и оборудование', 'ar' => 'تكنولوجيا الخياطة ومعداتها']),
                $this->c('CDES-106', 2, 2, ['en' => 'Computer-Aided Fashion Design', 'uz' => 'Moda dizaynida avtomatlashtirilgan loyihalash', 'ru' => 'Автоматизированный дизайн моды', 'ar' => 'تصميم الأزياء بمساعدة الحاسوب']),
                $this->c('CDES-107', 2, 2, ['en' => 'Textile Ornament and Print Design', 'uz' => 'To‘qimachilik naqshi va bosma dizayni', 'ru' => 'Текстильный орнамент и дизайн принта', 'ar' => 'تصميم الزخارف والطباعة النسيجية']),
                $this->c('CDES-108', 3, 1, ['en' => 'Collection Design Studio', 'uz' => 'Kolleksiya dizayni studiyasi', 'ru' => 'Студия проектирования коллекции', 'ar' => 'استوديو تصميم المجموعات']),
                $this->c('CDES-109', 3, 2, ['en' => 'Apparel Quality Control', 'uz' => 'Tikuv mahsulotlari sifatini nazorat qilish', 'ru' => 'Контроль качества швейных изделий', 'ar' => 'مراقبة جودة الملابس']),
                $this->c('CDES-110', 4, 1, ['en' => 'Fashion Marketing and Portfolio', 'uz' => 'Moda marketingi va portfolio', 'ru' => 'Маркетинг моды и портфолио', 'ar' => 'تسويق الأزياء وملف الأعمال']),
                $this->c('CDES-111', 4, 2, ['en' => 'Graduation Project in Clothing and Textile Design', 'uz' => 'Kiyim va to‘qimachilik dizayni bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по дизайну одежды и текстиля', 'ar' => 'مشروع التخرج في تصميم الملابس والمنسوجات']),
            ],
            'design-textile-and-light-industry-design-60210400' => [
                $this->c('TDES-100', 1, 1, ['en' => 'Introduction to Textile and Light Industry Design', 'uz' => 'To‘qimachilik va yengil sanoat dizayniga kirish', 'ru' => 'Введение в дизайн текстиля и легкой промышленности', 'ar' => 'مدخل إلى تصميم المنسوجات والصناعة الخفيفة']),
                $this->c('TDES-101', 1, 1, ['en' => 'Industrial Design Drawing', 'uz' => 'Sanoat dizayni chizmachiligi', 'ru' => 'Рисунок промышленного дизайна', 'ar' => 'الرسم في التصميم الصناعي']),
                $this->c('TDES-102', 1, 2, ['en' => 'Fibers, Yarns and Textile Materials', 'uz' => 'Tolalar, iplar va to‘qimachilik materiallari', 'ru' => 'Волокна, пряжа и текстильные материалы', 'ar' => 'الألياف والخيوط ومواد النسيج']),
                $this->c('TDES-103', 1, 2, ['en' => 'Design Thinking in Light Industry', 'uz' => 'Yengil sanoatda dizayn fikrlash', 'ru' => 'Дизайн-мышление в легкой промышленности', 'ar' => 'التفكير التصميمي في الصناعة الخفيفة']),
                $this->c('TDES-104', 2, 1, ['en' => 'Textile Product Design', 'uz' => 'To‘qimachilik mahsulotlari dizayni', 'ru' => 'Дизайн текстильных изделий', 'ar' => 'تصميم المنتجات النسيجية']),
                $this->c('TDES-105', 2, 1, ['en' => 'Light Industry Materials and Finishing', 'uz' => 'Yengil sanoat materiallari va pardozlash', 'ru' => 'Материалы и отделка легкой промышленности', 'ar' => 'مواد وتشطيبات الصناعة الخفيفة']),
                $this->c('TDES-106', 2, 2, ['en' => 'CAD for Textile and Product Design', 'uz' => 'To‘qimachilik va mahsulot dizayni uchun CAD', 'ru' => 'CAD для текстильного и предметного дизайна', 'ar' => 'CAD لتصميم المنسوجات والمنتجات']),
                $this->c('TDES-107', 2, 2, ['en' => 'Surface Pattern and Digital Print Design', 'uz' => 'Sirt naqshi va raqamli bosma dizayni', 'ru' => 'Дизайн поверхностного узора и цифровой печати', 'ar' => 'تصميم نقوش الأسطح والطباعة الرقمية']),
                $this->c('TDES-108', 3, 1, ['en' => 'Product Prototyping in Light Industry', 'uz' => 'Yengil sanoatda mahsulot prototiplash', 'ru' => 'Прототипирование изделий легкой промышленности', 'ar' => 'النمذجة الأولية للمنتجات في الصناعة الخفيفة']),
                $this->c('TDES-109', 3, 2, ['en' => 'Sustainable Textile Design', 'uz' => 'Barqaror to‘qimachilik dizayni', 'ru' => 'Устойчивый текстильный дизайн', 'ar' => 'تصميم المنسوجات المستدام']),
                $this->c('TDES-110', 4, 1, ['en' => 'Design Project Management', 'uz' => 'Dizayn loyihalarini boshqarish', 'ru' => 'Управление дизайн-проектами', 'ar' => 'إدارة مشاريع التصميم']),
                $this->c('TDES-111', 4, 2, ['en' => 'Graduation Project in Textile and Light Industry Design', 'uz' => 'To‘qimachilik va yengil sanoat dizayni bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по дизайну текстиля и легкой промышленности', 'ar' => 'مشروع التخرج في تصميم المنسوجات والصناعة الخفيفة']),
            ],
            'light-industry-production-technology-60720500' => [
                $this->c('LIPT-100', 1, 1, ['en' => 'Introduction to Light Industry Production Technology', 'uz' => 'Yengil sanoat ishlab chiqarish texnologiyasiga kirish', 'ru' => 'Введение в технологию производства легкой промышленности', 'ar' => 'مدخل إلى تكنولوجيا إنتاج الصناعة الخفيفة']),
                $this->c('LIPT-101', 1, 1, ['en' => 'Materials Science for Light Industry', 'uz' => 'Yengil sanoat materialshunosligi', 'ru' => 'Материаловедение легкой промышленности', 'ar' => 'علم المواد للصناعة الخفيفة']),
                $this->c('LIPT-102', 1, 2, ['en' => 'Textile Fibers and Yarn Technology', 'uz' => 'To‘qimachilik tolalari va ip texnologiyasi', 'ru' => 'Технология текстильных волокон и пряжи', 'ar' => 'تكنولوجيا الألياف والخيوط النسيجية']),
                $this->c('LIPT-103', 1, 2, ['en' => 'Sewing Production Technology', 'uz' => 'Tikuv ishlab chiqarish texnologiyasi', 'ru' => 'Технология швейного производства', 'ar' => 'تكنولوجيا إنتاج الملابس']),
                $this->c('LIPT-104', 2, 1, ['en' => 'Leather and Fur Processing Technology', 'uz' => 'Charm va mo‘yna ishlov berish texnologiyasi', 'ru' => 'Технология обработки кожи и меха', 'ar' => 'تكنولوجيا معالجة الجلد والفراء']),
                $this->c('LIPT-105', 2, 1, ['en' => 'Knitting and Weaving Technology', 'uz' => 'Trikotaj va to‘quv texnologiyasi', 'ru' => 'Технология вязания и ткачества', 'ar' => 'تكنولوجيا الحياكة والنسيج']),
                $this->c('LIPT-106', 2, 2, ['en' => 'Equipment of Light Industry Enterprises', 'uz' => 'Yengil sanoat korxonalari jihozlari', 'ru' => 'Оборудование предприятий легкой промышленности', 'ar' => 'معدات مؤسسات الصناعة الخفيفة']),
                $this->c('LIPT-107', 2, 2, ['en' => 'Garment and Textile Quality Control', 'uz' => 'Tikuv va to‘qimachilik sifatini nazorat qilish', 'ru' => 'Контроль качества швейных и текстильных изделий', 'ar' => 'مراقبة جودة الملابس والمنسوجات']),
                $this->c('LIPT-108', 3, 1, ['en' => 'Automation in Light Industry Production', 'uz' => 'Yengil sanoat ishlab chiqarishida avtomatlashtirish', 'ru' => 'Автоматизация производства в легкой промышленности', 'ar' => 'الأتمتة في إنتاج الصناعة الخفيفة']),
                $this->c('LIPT-109', 3, 2, ['en' => 'Production Planning and Process Control', 'uz' => 'Ishlab chiqarishni rejalashtirish va jarayon nazorati', 'ru' => 'Планирование производства и управление процессами', 'ar' => 'تخطيط الإنتاج والتحكم في العمليات']),
                $this->c('LIPT-110', 4, 1, ['en' => 'Industrial Practice in Light Industry', 'uz' => 'Yengil sanoatda ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика в легкой промышленности', 'ar' => 'التدريب الصناعي في الصناعة الخفيفة']),
                $this->c('LIPT-111', 4, 2, ['en' => 'Graduation Project in Light Industry Production Technology', 'uz' => 'Yengil sanoat ishlab chiqarish texnologiyasi bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по технологии производства легкой промышленности', 'ar' => 'مشروع التخرج في تكنولوجيا إنتاج الصناعة الخفيفة']),
            ],
            'light-industry-engineering-60720700' => [
                $this->c('LIEG-100', 1, 1, ['en' => 'Introduction to Light Industry Engineering', 'uz' => 'Yengil sanoat muhandisligiga kirish', 'ru' => 'Введение в инженерию легкой промышленности', 'ar' => 'مدخل إلى هندسة الصناعة الخفيفة']),
                $this->c('LIEG-101', 1, 1, ['en' => 'Engineering Graphics for Light Industry', 'uz' => 'Yengil sanoat uchun muhandislik grafikasi', 'ru' => 'Инженерная графика для легкой промышленности', 'ar' => 'الرسم الهندسي للصناعة الخفيفة']),
                $this->c('LIEG-102', 1, 2, ['en' => 'Mechanics of Light Industry Machines', 'uz' => 'Yengil sanoat mashinalari mexanikasi', 'ru' => 'Механика машин легкой промышленности', 'ar' => 'ميكانيكا آلات الصناعة الخفيفة']),
                $this->c('LIEG-103', 1, 2, ['en' => 'Materials and Structural Elements', 'uz' => 'Materiallar va konstruktiv elementlar', 'ru' => 'Материалы и конструктивные элементы', 'ar' => 'المواد والعناصر الإنشائية']),
                $this->c('LIEG-104', 2, 1, ['en' => 'Design of Light Industry Equipment', 'uz' => 'Yengil sanoat jihozlarini loyihalash', 'ru' => 'Проектирование оборудования легкой промышленности', 'ar' => 'تصميم معدات الصناعة الخفيفة']),
                $this->c('LIEG-105', 2, 1, ['en' => 'Machine Drives and Control Systems', 'uz' => 'Mashina yuritmalari va boshqaruv tizimlari', 'ru' => 'Машинные приводы и системы управления', 'ar' => 'مشغلات الآلات وأنظمة التحكم']),
                $this->c('LIEG-106', 2, 2, ['en' => 'Maintenance and Repair of Industrial Equipment', 'uz' => 'Sanoat jihozlariga texnik xizmat ko‘rsatish va ta’mirlash', 'ru' => 'Техническое обслуживание и ремонт промышленного оборудования', 'ar' => 'صيانة وإصلاح المعدات الصناعية']),
                $this->c('LIEG-107', 2, 2, ['en' => 'Technological Processes in Light Industry', 'uz' => 'Yengil sanoatdagi texnologik jarayonlar', 'ru' => 'Технологические процессы в легкой промышленности', 'ar' => 'العمليات التكنولوجية في الصناعة الخفيفة']),
                $this->c('LIEG-108', 3, 1, ['en' => 'Automation and Digital Control of Equipment', 'uz' => 'Jihozlarni avtomatlashtirish va raqamli boshqarish', 'ru' => 'Автоматизация и цифровое управление оборудованием', 'ar' => 'أتمتة المعدات والتحكم الرقمي']),
                $this->c('LIEG-109', 3, 2, ['en' => 'Energy Efficiency in Light Industry Enterprises', 'uz' => 'Yengil sanoat korxonalarida energiya samaradorligi', 'ru' => 'Энергоэффективность предприятий легкой промышленности', 'ar' => 'كفاءة الطاقة في مؤسسات الصناعة الخفيفة']),
                $this->c('LIEG-110', 4, 1, ['en' => 'Engineering Practice in Light Industry', 'uz' => 'Yengil sanoatda muhandislik amaliyoti', 'ru' => 'Инженерная практика в легкой промышленности', 'ar' => 'التدريب الهندسي في الصناعة الخفيفة']),
                $this->c('LIEG-111', 4, 2, ['en' => 'Graduation Project in Light Industry Engineering', 'uz' => 'Yengil sanoat muhandisligi bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по инженерии легкой промышленности', 'ar' => 'مشروع التخرج في هندسة الصناعة الخفيفة']),
            ],
        ];
    }
};
