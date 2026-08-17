<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $programs = [
            [
                'current_slug' => 'design-apparel-and-textile-design-60210400',
                'slug' => 'design-clothing-and-textile-design-60210400',
                'official_code' => '60210400',
                'faculty_slug' => 'faculty-of-engineering',
                'department_slug' => 'light-industry-engineering-and-design',
                'names' => [
                    'en' => 'Design: Clothing and Textile Design',
                    'uz' => 'Dizayn: kiyim va to‘qimachilik dizayni',
                    'ru' => 'Дизайн: дизайн одежды и текстиля',
                    'ar' => 'التصميم: تصميم الملابس والمنسوجات',
                ],
            ],
            [
                'current_slug' => 'perfumery-and-cosmetics-technology-60720200',
                'slug' => 'perfumery-and-cosmetic-products-technology-60720200',
                'official_code' => '60720200',
                'faculty_slug' => 'faculty-of-technology',
                'department_slug' => 'agricultural-products-storage-oil-fat-technology',
                'names' => [
                    'en' => 'Perfumery and Cosmetic Products Technology',
                    'uz' => 'Parfyumeriya va kosmetika mahsulotlari texnologiyasi',
                    'ru' => 'Технология парфюмерных и косметических продуктов',
                    'ar' => 'تكنولوجيا منتجات العطور ومستحضرات التجميل',
                ],
            ],
            [
                'current_slug' => 'deep-gas-processing-technology-60720500',
                'slug' => 'light-industry-production-technology-60720500',
                'official_code' => '60720500',
                'faculty_slug' => 'faculty-of-engineering',
                'department_slug' => 'light-industry-engineering-and-design',
                'names' => [
                    'en' => 'Light Industry Production Technology',
                    'uz' => 'Yengil sanoat ishlab chiqarish texnologiyasi',
                    'ru' => 'Технология производства легкой промышленности',
                    'ar' => 'تكنولوجيا إنتاج الصناعات الخفيفة',
                ],
            ],
            [
                'current_slug' => 'geology-mineral-prospecting-and-exploration-60720900',
                'slug' => 'geology-prospecting-and-exploration-of-mineral-resources-60720900',
                'official_code' => '60720900',
                'faculty_slug' => 'faculty-of-technology',
                'department_slug' => 'oil-gas-engineering-upstream-downstream',
                'names' => [
                    'en' => 'Geology, Prospecting and Exploration of Mineral Resources',
                    'uz' => 'Geologiya, foydali qazilmalarni qidirish va razvedka qilish',
                    'ru' => 'Геология, поиски и разведка полезных ископаемых',
                    'ar' => 'الجيولوجيا والتنقيب واستكشاف الموارد المعدنية',
                ],
            ],
            [
                'current_slug' => 'geodesy-and-geoinformatics-60721500',
                'slug' => 'geodesy-and-geomatics-60721500',
                'official_code' => '60721500',
                'faculty_slug' => 'faculty-of-natural-resources-management',
                'department_slug' => 'land-resources-management-state-land-cadastres',
                'names' => [
                    'en' => 'Geodesy and Geomatics',
                    'uz' => 'Geodeziya va geomatika',
                    'ru' => 'Геодезия и геоматика',
                    'ar' => 'الجيوديسيا والجيوماتكس',
                ],
            ],
            [
                'current_slug' => 'industrial-engineering-60721800',
                'slug' => 'manufacturing-engineering-60721800',
                'official_code' => '60721800',
                'faculty_slug' => 'faculty-of-engineering',
                'department_slug' => 'technological-machines-equipment',
                'names' => [
                    'en' => 'Manufacturing Engineering',
                    'uz' => 'Ishlab chiqarish muhandisligi',
                    'ru' => 'Производственная инженерия',
                    'ar' => 'هندسة التصنيع',
                ],
            ],
            [
                'current_slug' => 'highway-engineering-60730500',
                'slug' => 'road-engineering-60730500',
                'official_code' => '60730500',
                'faculty_slug' => 'faculty-of-engineering',
                'department_slug' => 'civil-engineering',
                'names' => [
                    'en' => 'Road Engineering',
                    'uz' => 'Yo‘l muhandisligi',
                    'ru' => 'Дорожная инженерия',
                    'ar' => 'هندسة الطرق',
                ],
            ],
            [
                'current_slug' => 'urban-planning-and-design-60730900',
                'slug' => 'urban-construction-and-planning-60730900',
                'official_code' => '60730900',
                'faculty_slug' => 'faculty-of-engineering',
                'department_slug' => 'architecture',
                'names' => [
                    'en' => 'Urban Construction and Planning',
                    'uz' => 'Shahar qurilishi va rejalashtirish',
                    'ru' => 'Городское строительство и планирование',
                    'ar' => 'البناء والتخطيط الحضري',
                ],
            ],
            [
                'current_slug' => 'mechanization-of-agriculture-60810100',
                'slug' => 'agricultural-mechanization-60810100',
                'official_code' => '60810100',
                'faculty_slug' => 'faculty-of-natural-resources-management',
                'department_slug' => 'agricultural-water-resources-engineering-technologies',
                'names' => [
                    'en' => 'Agricultural Mechanization',
                    'uz' => 'Qishloq xo‘jaligini mexanizatsiyalash',
                    'ru' => 'Механизация сельского хозяйства',
                    'ar' => 'ميكنة الزراعة',
                ],
            ],
            [
                'current_slug' => 'water-management-and-melioration-60811200',
                'slug' => 'water-management-and-land-reclamation-60811200',
                'official_code' => '60811200',
                'faculty_slug' => 'faculty-of-natural-resources-management',
                'department_slug' => 'irrigation-melioration',
                'names' => [
                    'en' => 'Water Management and Land Reclamation',
                    'uz' => 'Suv xo‘jaligi va melioratsiya',
                    'ru' => 'Водное хозяйство и мелиорация земель',
                    'ar' => 'إدارة المياه واستصلاح الأراضي',
                ],
            ],
            [
                'current_slug' => 'operation-of-hydrotechnical-installations-and-pumping-stations-60811300',
                'slug' => 'operation-of-hydraulic-structures-and-pumping-stations-60811300',
                'official_code' => '60811300',
                'faculty_slug' => 'faculty-of-natural-resources-management',
                'department_slug' => 'hydrotechnical-structures-pump-stations',
                'names' => [
                    'en' => 'Operation of Hydraulic Structures and Pumping Stations',
                    'uz' => 'Gidrotexnika inshootlari va nasos stansiyalaridan foydalanish',
                    'ru' => 'Эксплуатация гидротехнических сооружений и насосных станций',
                    'ar' => 'تشغيل المنشآت الهيدروليكية ومحطات الضخ',
                ],
            ],
            [
                'current_slug' => 'meliorative-hydrogeology-60811400',
                'slug' => 'reclamation-hydrogeology-60811400',
                'official_code' => '60811400',
                'faculty_slug' => 'faculty-of-natural-resources-management',
                'department_slug' => 'industrial-ecology-hydrogeology',
                'names' => [
                    'en' => 'Reclamation Hydrogeology',
                    'uz' => 'Meliorativ gidrogeologiya',
                    'ru' => 'Мелиоративная гидрогеология',
                    'ar' => 'الهيدروجيولوجيا الاستصلاحية',
                ],
            ],
        ];

        foreach ($programs as $program) {
            $programId = DB::table('programs')->where('slug', $program['current_slug'])->value('id')
                ?: DB::table('programs')->where('slug', $program['slug'])->value('id');

            if (! $programId) {
                continue;
            }

            $facultyId = DB::table('faculties')->where('slug', $program['faculty_slug'])->value('id');
            $departmentId = DB::table('departments')->where('slug', $program['department_slug'])->value('id');

            DB::table('programs')
                ->where('id', $programId)
                ->update([
                    'faculty_id' => $facultyId,
                    'department_id' => $departmentId,
                    'slug' => $program['slug'],
                    'official_code' => $program['official_code'],
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            foreach ($program['names'] as $locale => $name) {
                DB::table('program_translations')->updateOrInsert(
                    ['program_id' => $programId, 'locale' => $locale],
                    [
                        'name' => $name,
                        'meta_title' => $name,
                        'meta_description' => Str::limit($name.' bachelor program at Bukhara State Technical University.', 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Official 2026/2027 daytime PDF names are the canonical source for these programs.
    }
};
