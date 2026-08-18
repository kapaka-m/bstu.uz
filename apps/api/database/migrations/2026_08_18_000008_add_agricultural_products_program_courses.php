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
            'uz' => $name.' fani nazariy bilim, laboratoriya ishlari va ishlab chiqarish amaliyotini rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает теоретические знания, лабораторные навыки и производственную практику.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المعرفة النظرية والمهارات المخبرية والتطبيق الصناعي.',
            default => $name.' develops theoretical knowledge, laboratory skills, and industrial practice.',
        };
    }

    private function programCourses(): array
    {
        return [
            'perfumery-and-cosmetic-products-technology-60720200' => [
                $this->course('PCPT-100', 1, 1, [
                    'en' => 'Introduction to Perfumery and Cosmetic Products Technology',
                    'uz' => 'Parfyumeriya va kosmetika mahsulotlari texnologiyasiga kirish',
                    'ru' => 'Введение в технологию парфюмерно-косметической продукции',
                    'ar' => 'مدخل إلى تقنية منتجات العطور ومستحضرات التجميل',
                ]),
                $this->course('PCPT-101', 1, 2, [
                    'en' => 'General and Organic Chemistry for Cosmetics',
                    'uz' => 'Kosmetika uchun umumiy va organik kimyo',
                    'ru' => 'Общая и органическая химия для косметики',
                    'ar' => 'الكيمياء العامة والعضوية لمستحضرات التجميل',
                ]),
                $this->course('PCPT-102', 2, 1, [
                    'en' => 'Raw Materials for Perfumery and Cosmetics',
                    'uz' => 'Parfyumeriya va kosmetika xomashyolari',
                    'ru' => 'Сырье для парфюмерии и косметики',
                    'ar' => 'المواد الخام للعطور ومستحضرات التجميل',
                ]),
                $this->course('PCPT-103', 2, 1, [
                    'en' => 'Essential Oils and Fragrance Composition',
                    'uz' => 'Efir moylari va xushbo‘y kompozitsiyalar',
                    'ru' => 'Эфирные масла и ароматические композиции',
                    'ar' => 'الزيوت العطرية وتركيب الروائح',
                ]),
                $this->course('PCPT-104', 2, 2, [
                    'en' => 'Cosmetic Emulsions and Formulations',
                    'uz' => 'Kosmetik emulsiyalar va formulalar',
                    'ru' => 'Косметические эмульсии и рецептуры',
                    'ar' => 'المستحلبات والتركيبات التجميلية',
                ]),
                $this->course('PCPT-105', 2, 2, [
                    'en' => 'Technology of Skin and Hair Care Products',
                    'uz' => 'Teri va soch parvarishi mahsulotlari texnologiyasi',
                    'ru' => 'Технология средств ухода за кожей и волосами',
                    'ar' => 'تقنية منتجات العناية بالبشرة والشعر',
                ]),
                $this->course('PCPT-106', 3, 1, [
                    'en' => 'Perfumery Product Manufacturing',
                    'uz' => 'Parfyumeriya mahsulotlarini ishlab chiqarish',
                    'ru' => 'Производство парфюмерной продукции',
                    'ar' => 'تصنيع منتجات العطور',
                ]),
                $this->course('PCPT-107', 3, 1, [
                    'en' => 'Cosmetic Product Equipment',
                    'uz' => 'Kosmetika mahsulotlari jihozlari',
                    'ru' => 'Оборудование косметического производства',
                    'ar' => 'معدات إنتاج مستحضرات التجميل',
                ]),
                $this->course('PCPT-108', 3, 2, [
                    'en' => 'Quality Control of Perfumery and Cosmetics',
                    'uz' => 'Parfyumeriya va kosmetika sifat nazorati',
                    'ru' => 'Контроль качества парфюмерии и косметики',
                    'ar' => 'مراقبة جودة العطور ومستحضرات التجميل',
                ]),
                $this->course('PCPT-109', 3, 2, [
                    'en' => 'Packaging and Storage of Cosmetic Products',
                    'uz' => 'Kosmetik mahsulotlarni qadoqlash va saqlash',
                    'ru' => 'Упаковка и хранение косметической продукции',
                    'ar' => 'تعبئة وتخزين المنتجات التجميلية',
                ]),
                $this->course('PCPT-110', 4, 1, [
                    'en' => 'Safety and Certification of Cosmetic Products',
                    'uz' => 'Kosmetik mahsulotlar xavfsizligi va sertifikatlash',
                    'ru' => 'Безопасность и сертификация косметической продукции',
                    'ar' => 'سلامة واعتماد المنتجات التجميلية',
                ]),
                $this->course('PCPT-111', 4, 1, [
                    'en' => 'Innovation in Perfumery and Cosmetic Technology',
                    'uz' => 'Parfyumeriya va kosmetika texnologiyasida innovatsiyalar',
                    'ru' => 'Инновации в парфюмерно-косметической технологии',
                    'ar' => 'الابتكار في تقنية العطور ومستحضرات التجميل',
                ]),
                $this->course('PCPT-112', 4, 2, [
                    'en' => 'Industrial Practice in Cosmetic Production',
                    'uz' => 'Kosmetika ishlab chiqarish bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по косметическому производству',
                    'ar' => 'التدريب الصناعي في إنتاج مستحضرات التجميل',
                ]),
                $this->course('PCPT-113', 4, 2, [
                    'en' => 'Graduation Project in Perfumery and Cosmetics',
                    'uz' => 'Parfyumeriya va kosmetika bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по парфюмерии и косметике',
                    'ar' => 'مشروع التخرج في العطور ومستحضرات التجميل',
                ]),
            ],
            'technology-of-storage-and-processing-of-agricultural-products-60810700' => [
                $this->course('AGSP-100', 1, 1, [
                    'en' => 'Introduction to Agricultural Product Storage and Processing',
                    'uz' => 'Qishloq xo‘jaligi mahsulotlarini saqlash va qayta ishlashga kirish',
                    'ru' => 'Введение в хранение и переработку сельскохозяйственной продукции',
                    'ar' => 'مدخل إلى تخزين ومعالجة المنتجات الزراعية',
                ]),
                $this->course('AGSP-101', 1, 2, [
                    'en' => 'Agricultural Raw Materials Science',
                    'uz' => 'Qishloq xo‘jaligi xomashyolari fanlari',
                    'ru' => 'Наука о сельскохозяйственном сырье',
                    'ar' => 'علم المواد الخام الزراعية',
                ]),
                $this->course('AGSP-102', 2, 1, [
                    'en' => 'Post-Harvest Physiology',
                    'uz' => 'Hosildan keyingi fiziologiya',
                    'ru' => 'Послеуборочная физиология',
                    'ar' => 'فسيولوجيا ما بعد الحصاد',
                ]),
                $this->course('AGSP-103', 2, 1, [
                    'en' => 'Storage Facilities and Controlled Atmosphere',
                    'uz' => 'Saqlash omborlari va boshqariladigan atmosfera',
                    'ru' => 'Хранилища и регулируемая атмосфера',
                    'ar' => 'مرافق التخزين والجو المتحكم فيه',
                ]),
                $this->course('AGSP-104', 2, 2, [
                    'en' => 'Processing of Fruits and Vegetables',
                    'uz' => 'Meva va sabzavotlarni qayta ishlash',
                    'ru' => 'Переработка фруктов и овощей',
                    'ar' => 'معالجة الفواكه والخضروات',
                ]),
                $this->course('AGSP-105', 2, 2, [
                    'en' => 'Drying and Freezing Technologies',
                    'uz' => 'Quritish va muzlatish texnologiyalari',
                    'ru' => 'Технологии сушки и замораживания',
                    'ar' => 'تقنيات التجفيف والتجميد',
                ]),
                $this->course('AGSP-106', 3, 1, [
                    'en' => 'Canning and Preservation Technology',
                    'uz' => 'Konservalash va saqlash texnologiyasi',
                    'ru' => 'Технология консервирования и сохранения',
                    'ar' => 'تقنية التعليب والحفظ',
                ]),
                $this->course('AGSP-107', 3, 1, [
                    'en' => 'Grain and Seed Storage Technology',
                    'uz' => 'Don va urug‘larni saqlash texnologiyasi',
                    'ru' => 'Технология хранения зерна и семян',
                    'ar' => 'تقنية تخزين الحبوب والبذور',
                ]),
                $this->course('AGSP-108', 3, 2, [
                    'en' => 'Packaging of Agricultural Products',
                    'uz' => 'Qishloq xo‘jaligi mahsulotlarini qadoqlash',
                    'ru' => 'Упаковка сельскохозяйственной продукции',
                    'ar' => 'تعبئة المنتجات الزراعية',
                ]),
                $this->course('AGSP-109', 3, 2, [
                    'en' => 'Quality Control and Food Safety',
                    'uz' => 'Sifat nazorati va oziq-ovqat xavfsizligi',
                    'ru' => 'Контроль качества и безопасность пищевых продуктов',
                    'ar' => 'مراقبة الجودة وسلامة الغذاء',
                ]),
                $this->course('AGSP-110', 4, 1, [
                    'en' => 'Processing Equipment and Production Lines',
                    'uz' => 'Qayta ishlash jihozlari va ishlab chiqarish liniyalari',
                    'ru' => 'Оборудование переработки и производственные линии',
                    'ar' => 'معدات المعالجة وخطوط الإنتاج',
                ]),
                $this->course('AGSP-111', 4, 1, [
                    'en' => 'Resource-Saving Technologies in Processing',
                    'uz' => 'Qayta ishlashda resurs tejamkor texnologiyalar',
                    'ru' => 'Ресурсосберегающие технологии в переработке',
                    'ar' => 'تقنيات توفير الموارد في المعالجة',
                ]),
                $this->course('AGSP-112', 4, 2, [
                    'en' => 'Industrial Practice in Agricultural Product Processing',
                    'uz' => 'Qishloq xo‘jaligi mahsulotlarini qayta ishlash bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по переработке сельскохозяйственной продукции',
                    'ar' => 'التدريب الصناعي في معالجة المنتجات الزراعية',
                ]),
                $this->course('AGSP-113', 4, 2, [
                    'en' => 'Graduation Project in Storage and Processing',
                    'uz' => 'Saqlash va qayta ishlash bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по хранению и переработке',
                    'ar' => 'مشروع التخرج في التخزين والمعالجة',
                ]),
            ],
            'fruit-vegetable-growing-and-viticulture-60811000' => [
                $this->course('FVV-100', 1, 1, [
                    'en' => 'Introduction to Fruit, Vegetable Growing and Viticulture',
                    'uz' => 'Meva-sabzavotchilik va uzumchilikka kirish',
                    'ru' => 'Введение в плодоовощеводство и виноградарство',
                    'ar' => 'مدخل إلى زراعة الفواكه والخضروات والكروم',
                ]),
                $this->course('FVV-101', 1, 2, [
                    'en' => 'Botany and Plant Physiology',
                    'uz' => 'Botanika va o‘simliklar fiziologiyasi',
                    'ru' => 'Ботаника и физиология растений',
                    'ar' => 'علم النبات وفسيولوجيا النبات',
                ]),
                $this->course('FVV-102', 2, 1, [
                    'en' => 'Soil Science and Plant Nutrition',
                    'uz' => 'Tuproqshunoslik va o‘simliklarni oziqlantirish',
                    'ru' => 'Почвоведение и питание растений',
                    'ar' => 'علم التربة وتغذية النبات',
                ]),
                $this->course('FVV-103', 2, 1, [
                    'en' => 'Fruit Growing Technology',
                    'uz' => 'Mevachilik texnologiyasi',
                    'ru' => 'Технология плодоводства',
                    'ar' => 'تقنية زراعة الفواكه',
                ]),
                $this->course('FVV-104', 2, 2, [
                    'en' => 'Vegetable Growing Technology',
                    'uz' => 'Sabzavotchilik texnologiyasi',
                    'ru' => 'Технология овощеводства',
                    'ar' => 'تقنية زراعة الخضروات',
                ]),
                $this->course('FVV-105', 2, 2, [
                    'en' => 'Viticulture',
                    'uz' => 'Uzumchilik',
                    'ru' => 'Виноградарство',
                    'ar' => 'زراعة الكروم',
                ]),
                $this->course('FVV-106', 3, 1, [
                    'en' => 'Protected Ground Vegetable Production',
                    'uz' => 'Himoyalangan yerda sabzavot yetishtirish',
                    'ru' => 'Овощеводство защищенного грунта',
                    'ar' => 'إنتاج الخضروات في البيوت المحمية',
                ]),
                $this->course('FVV-107', 3, 1, [
                    'en' => 'Irrigation and Fertilization in Horticulture',
                    'uz' => 'Bog‘dorchilikda sug‘orish va o‘g‘itlash',
                    'ru' => 'Орошение и удобрение в садоводстве',
                    'ar' => 'الري والتسميد في البستنة',
                ]),
                $this->course('FVV-108', 3, 2, [
                    'en' => 'Plant Protection in Orchards and Vineyards',
                    'uz' => 'Bog‘ va uzumzorlarda o‘simliklarni himoya qilish',
                    'ru' => 'Защита растений в садах и виноградниках',
                    'ar' => 'حماية النبات في البساتين وكروم العنب',
                ]),
                $this->course('FVV-109', 3, 2, [
                    'en' => 'Nursery Management and Seedling Production',
                    'uz' => 'Ko‘chatchilik va ko‘chat yetishtirishni boshqarish',
                    'ru' => 'Питомниководство и производство саженцев',
                    'ar' => 'إدارة المشاتل وإنتاج الشتلات',
                ]),
                $this->course('FVV-110', 4, 1, [
                    'en' => 'Harvesting and Post-Harvest Handling',
                    'uz' => 'Hosil yig‘ish va hosildan keyingi ishlov berish',
                    'ru' => 'Уборка урожая и послеуборочная обработка',
                    'ar' => 'الحصاد ومعاملات ما بعد الحصاد',
                ]),
                $this->course('FVV-111', 4, 1, [
                    'en' => 'Precision Horticulture Technologies',
                    'uz' => 'Aniq bog‘dorchilik texnologiyalari',
                    'ru' => 'Технологии точного садоводства',
                    'ar' => 'تقنيات البستنة الدقيقة',
                ]),
                $this->course('FVV-112', 4, 2, [
                    'en' => 'Industrial Practice in Horticulture and Viticulture',
                    'uz' => 'Bog‘dorchilik va uzumchilik bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по садоводству и виноградарству',
                    'ar' => 'التدريب الصناعي في البستنة وزراعة الكروم',
                ]),
                $this->course('FVV-113', 4, 2, [
                    'en' => 'Graduation Project in Fruit and Vegetable Growing',
                    'uz' => 'Meva-sabzavotchilik bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по плодоовощеводству',
                    'ar' => 'مشروع التخرج في زراعة الفواكه والخضروات',
                ]),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
