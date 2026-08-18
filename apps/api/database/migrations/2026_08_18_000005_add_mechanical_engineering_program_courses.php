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

        $programId = DB::table('programs')->where('slug', 'mechanical-engineering-60712300')->value('id');

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

    public function down(): void
    {
        // Keep academic content corrections non-destructive.
    }

    protected function descriptionFor(string $name, string $locale): string
    {
        return match ($locale) {
            'uz' => $name.' fani mexanika, mashinasozlik va muhandislik grafikasi bo‘yicha kasbiy tayyorgarlikni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональную подготовку в области механики, машиностроения и инженерной графики.',
            'ar' => 'يطور مقرر '.$name.' الإعداد المهني في مجال الميكانيكا والهندسة الميكانيكية والرسم الهندسي.',
            default => $name.' develops professional preparation in mechanics, mechanical engineering, and engineering graphics.',
        };
    }

    protected function c(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }

    protected function courses(): array
    {
        return [
            $this->c('MENG-100', 1, 1, ['en' => 'Introduction to Mechanical Engineering', 'uz' => 'Mexanika muhandisligiga kirish', 'ru' => 'Введение в машиностроение', 'ar' => 'مدخل إلى الهندسة الميكانيكية']),
            $this->c('MENG-101', 1, 1, ['en' => 'Engineering Graphics and Technical Drawing', 'uz' => 'Muhandislik grafikasi va texnik chizmachilik', 'ru' => 'Инженерная графика и техническое черчение', 'ar' => 'الرسم الهندسي والرسم الفني']),
            $this->c('MENG-102', 1, 2, ['en' => 'Theoretical Mechanics', 'uz' => 'Nazariy mexanika', 'ru' => 'Теоретическая механика', 'ar' => 'الميكانيكا النظرية']),
            $this->c('MENG-103', 1, 2, ['en' => 'Materials Science for Mechanical Engineering', 'uz' => 'Mexanika muhandisligi uchun materialshunoslik', 'ru' => 'Материаловедение для машиностроения', 'ar' => 'علم المواد للهندسة الميكانيكية']),
            $this->c('MENG-104', 2, 1, ['en' => 'Strength of Materials', 'uz' => 'Materiallar qarshiligi', 'ru' => 'Сопротивление материалов', 'ar' => 'مقاومة المواد']),
            $this->c('MENG-105', 2, 1, ['en' => 'Machine Elements and Mechanisms', 'uz' => 'Mashina detallari va mexanizmlar', 'ru' => 'Детали машин и механизмы', 'ar' => 'عناصر الآلات والآليات']),
            $this->c('MENG-106', 2, 2, ['en' => 'Computer-Aided Design in Mechanical Engineering', 'uz' => 'Mexanika muhandisligida avtomatlashtirilgan loyihalash', 'ru' => 'Автоматизированное проектирование в машиностроении', 'ar' => 'التصميم بمساعدة الحاسوب في الهندسة الميكانيكية']),
            $this->c('MENG-107', 2, 2, ['en' => 'Manufacturing Processes and Machine Tools', 'uz' => 'Ishlab chiqarish jarayonlari va metall kesish stanoklari', 'ru' => 'Производственные процессы и станки', 'ar' => 'عمليات التصنيع وآلات التشغيل']),
            $this->c('MENG-108', 3, 1, ['en' => 'Hydraulics and Pneumatic Systems', 'uz' => 'Gidravlika va pnevmatik tizimlar', 'ru' => 'Гидравлика и пневматические системы', 'ar' => 'الهيدروليكا والأنظمة الهوائية']),
            $this->c('MENG-109', 3, 1, ['en' => 'Thermodynamics and Heat Engineering', 'uz' => 'Termodinamika va issiqlik texnikasi', 'ru' => 'Термодинамика и теплотехника', 'ar' => 'الديناميكا الحرارية وهندسة الحرارة']),
            $this->c('MENG-110', 3, 2, ['en' => 'Metrology, Standardization and Technical Measurements', 'uz' => 'Metrologiya, standartlashtirish va texnik o‘lchashlar', 'ru' => 'Метрология, стандартизация и технические измерения', 'ar' => 'المترولوجيا والتقييس والقياسات الفنية']),
            $this->c('MENG-111', 3, 2, ['en' => 'Automation of Mechanical Engineering Production', 'uz' => 'Mashinasozlik ishlab chiqarishini avtomatlashtirish', 'ru' => 'Автоматизация машиностроительного производства', 'ar' => 'أتمتة إنتاج الهندسة الميكانيكية']),
            $this->c('MENG-112', 4, 1, ['en' => 'Machine Design and Engineering Analysis', 'uz' => 'Mashinalarni loyihalash va muhandislik tahlili', 'ru' => 'Проектирование машин и инженерный анализ', 'ar' => 'تصميم الآلات والتحليل الهندسي']),
            $this->c('MENG-113', 4, 1, ['en' => 'Maintenance and Reliability of Machines', 'uz' => 'Mashinalarga texnik xizmat ko‘rsatish va ishonchlilik', 'ru' => 'Техническое обслуживание и надежность машин', 'ar' => 'صيانة الآلات واعتماديتها']),
            $this->c('MENG-114', 4, 2, ['en' => 'Industrial Practice in Mechanical Engineering', 'uz' => 'Mexanika muhandisligida ishlab chiqarish amaliyoti', 'ru' => 'Производственная практика по машиностроению', 'ar' => 'التدريب الصناعي في الهندسة الميكانيكية']),
            $this->c('MENG-115', 4, 2, ['en' => 'Graduation Project in Mechanical Engineering', 'uz' => 'Mexanika muhandisligi bo‘yicha bitiruv loyihasi', 'ru' => 'Выпускной проект по машиностроению', 'ar' => 'مشروع التخرج في الهندسة الميكانيكية']),
        ];
    }
};
