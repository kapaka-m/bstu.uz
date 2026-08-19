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

        $programId = DB::table('programs')->where('slug', 'agricultural-mechanization-60810100')->value('id');

        if (! $programId) {
            return;
        }

        foreach ($this->courses() as $course) {
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

    public function down(): void
    {
        // Keep academic content corrections non-destructive.
    }

    protected function descriptionFor(string $name, string $locale): string
    {
        return match ($locale) {
            'uz' => $name.' fani qishloq xo‘jaligi texnikasi, mexanizatsiya, texnik xizmat va amaliy muhandislik ko‘nikmalarini rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональные навыки в области сельскохозяйственной техники, механизации, технического обслуживания и инженерной практики.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المهارات المهنية في الآلات الزراعية والميكنة والخدمة الفنية والتطبيق الهندسي.',
            default => $name.' develops professional skills in agricultural machinery, mechanization, technical service, and engineering practice.',
        };
    }

    private function courses(): array
    {
        return [
            $this->course('AGME-100', 1, 1, [
                'en' => 'Introduction to Agricultural Mechanization',
                'uz' => 'Qishloq xo‘jaligini mexanizatsiyalashga kirish',
                'ru' => 'Введение в механизацию сельского хозяйства',
                'ar' => 'مدخل إلى الميكنة الزراعية',
            ]),
            $this->course('AGME-101', 1, 2, [
                'en' => 'Fundamentals of Agricultural Engineering',
                'uz' => 'Qishloq xo‘jaligi muhandisligi asoslari',
                'ru' => 'Основы сельскохозяйственной инженерии',
                'ar' => 'أساسيات الهندسة الزراعية',
            ]),
            $this->course('AGME-102', 2, 1, [
                'en' => 'Tractors and Transport Vehicles',
                'uz' => 'Traktorlar va transport vositalari',
                'ru' => 'Тракторы и транспортные средства',
                'ar' => 'الجرارات ووسائل النقل',
            ]),
            $this->course('AGME-103', 2, 1, [
                'en' => 'Agricultural Machinery',
                'uz' => 'Qishloq xo‘jaligi mashinalari',
                'ru' => 'Сельскохозяйственные машины',
                'ar' => 'الآلات الزراعية',
            ]),
            $this->course('AGME-104', 2, 2, [
                'en' => 'Mechanization of Crop Production',
                'uz' => 'O‘simlikchilik ishlab chiqarishini mexanizatsiyalash',
                'ru' => 'Механизация растениеводства',
                'ar' => 'ميكنة إنتاج المحاصيل',
            ]),
            $this->course('AGME-105', 2, 2, [
                'en' => 'Livestock Equipment and Technologies',
                'uz' => 'Chorvachilik jihozlari va texnologiyalari',
                'ru' => 'Оборудование и технологии животноводства',
                'ar' => 'معدات وتقنيات الإنتاج الحيواني',
            ]),
            $this->course('AGME-106', 3, 1, [
                'en' => 'Reclamation and Construction Machinery',
                'uz' => 'Melioratsiya va qurilish mashinalari',
                'ru' => 'Мелиоративные и строительные машины',
                'ar' => 'آلات الاستصلاح والبناء',
            ]),
            $this->course('AGME-107', 3, 1, [
                'en' => 'Irrigation Equipment and Water Management Machinery',
                'uz' => 'Sug‘orish jihozlari va suv xo‘jaligi mashinalari',
                'ru' => 'Оросительное оборудование и машины водного хозяйства',
                'ar' => 'معدات الري وآلات إدارة المياه',
            ]),
            $this->course('AGME-108', 3, 2, [
                'en' => 'Machine and Tractor Fleet Operation',
                'uz' => 'Mashina-traktor parkidan foydalanish',
                'ru' => 'Эксплуатация машинно-тракторного парка',
                'ar' => 'تشغيل أسطول الآلات والجرارات',
            ]),
            $this->course('AGME-109', 3, 2, [
                'en' => 'Technical Service and Repair of Agricultural Machinery',
                'uz' => 'Qishloq xo‘jaligi texnikasiga texnik xizmat ko‘rsatish va ta’mirlash',
                'ru' => 'Техническое обслуживание и ремонт сельскохозяйственной техники',
                'ar' => 'الخدمة الفنية وإصلاح الآلات الزراعية',
            ]),
            $this->course('AGME-110', 4, 1, [
                'en' => 'Precision Agriculture Technologies',
                'uz' => 'Aniq dehqonchilik texnologiyalari',
                'ru' => 'Технологии точного земледелия',
                'ar' => 'تقنيات الزراعة الدقيقة',
            ]),
            $this->course('AGME-111', 4, 1, [
                'en' => 'Smart Agriculture Equipment and Digital Systems',
                'uz' => 'Aqlli qishloq xo‘jaligi jihozlari va raqamli tizimlar',
                'ru' => 'Оборудование умного сельского хозяйства и цифровые системы',
                'ar' => 'معدات الزراعة الذكية والأنظمة الرقمية',
            ]),
            $this->course('AGME-112', 4, 2, [
                'en' => 'Industrial Practice in Agricultural Mechanization',
                'uz' => 'Qishloq xo‘jaligini mexanizatsiyalash bo‘yicha ishlab chiqarish amaliyoti',
                'ru' => 'Производственная практика по механизации сельского хозяйства',
                'ar' => 'التدريب الصناعي في الميكنة الزراعية',
            ]),
            $this->course('AGME-113', 4, 2, [
                'en' => 'Graduation Project in Agricultural Mechanization',
                'uz' => 'Qishloq xo‘jaligini mexanizatsiyalash bo‘yicha bitiruv loyihasi',
                'ru' => 'Выпускной проект по механизации сельского хозяйства',
                'ar' => 'مشروع التخرج في الميكنة الزراعية',
            ]),
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
