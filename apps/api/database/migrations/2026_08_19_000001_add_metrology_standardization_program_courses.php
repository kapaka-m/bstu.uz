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

        $programId = DB::table('programs')->where('slug', 'metrology-and-standardization-60710800')->value('id');

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
            'uz' => $name.' fani o‘lchash, standartlashtirish, sertifikatlash va sifat nazorati bo‘yicha kasbiy ko‘nikmalarni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональные навыки в области измерений, стандартизации, сертификации и контроля качества.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المهارات المهنية في القياس والتقييس والاعتماد ومراقبة الجودة.',
            default => $name.' develops professional skills in measurement, standardization, certification, and quality control.',
        };
    }

    private function courses(): array
    {
        return [
            $this->course('MSTD-100', 1, 1, [
                'en' => 'Introduction to Metrology and Standardization',
                'uz' => 'Metrologiya va standartlashtirishga kirish',
                'ru' => 'Введение в метрологию и стандартизацию',
                'ar' => 'مدخل إلى المترولوجيا والتقييس',
            ]),
            $this->course('MSTD-101', 1, 2, [
                'en' => 'Fundamentals of Measurement Theory',
                'uz' => 'O‘lchash nazariyasi asoslari',
                'ru' => 'Основы теории измерений',
                'ar' => 'أساسيات نظرية القياس',
            ]),
            $this->course('MSTD-102', 2, 1, [
                'en' => 'Measurement Instruments and Systems',
                'uz' => 'O‘lchash asboblari va tizimlari',
                'ru' => 'Измерительные приборы и системы',
                'ar' => 'أجهزة وأنظمة القياس',
            ]),
            $this->course('MSTD-103', 2, 1, [
                'en' => 'Standardization Principles',
                'uz' => 'Standartlashtirish tamoyillari',
                'ru' => 'Принципы стандартизации',
                'ar' => 'مبادئ التقييس',
            ]),
            $this->course('MSTD-104', 2, 2, [
                'en' => 'Certification and Conformity Assessment',
                'uz' => 'Sertifikatlash va muvofiqlikni baholash',
                'ru' => 'Сертификация и оценка соответствия',
                'ar' => 'الاعتماد وتقييم المطابقة',
            ]),
            $this->course('MSTD-105', 2, 2, [
                'en' => 'Technical Regulation',
                'uz' => 'Texnik jihatdan tartibga solish',
                'ru' => 'Техническое регулирование',
                'ar' => 'التنظيم الفني',
            ]),
            $this->course('MSTD-106', 3, 1, [
                'en' => 'Quality Management Systems',
                'uz' => 'Sifat menejmenti tizimlari',
                'ru' => 'Системы менеджмента качества',
                'ar' => 'أنظمة إدارة الجودة',
            ]),
            $this->course('MSTD-107', 3, 1, [
                'en' => 'Product Quality Control',
                'uz' => 'Mahsulot sifatini nazorat qilish',
                'ru' => 'Контроль качества продукции',
                'ar' => 'مراقبة جودة المنتجات',
            ]),
            $this->course('MSTD-108', 3, 2, [
                'en' => 'Calibration and Verification of Instruments',
                'uz' => 'Asboblarni kalibrlash va tekshirish',
                'ru' => 'Калибровка и поверка средств измерений',
                'ar' => 'معايرة وفحص أجهزة القياس',
            ]),
            $this->course('MSTD-109', 3, 2, [
                'en' => 'Measurement Uncertainty and Data Processing',
                'uz' => 'O‘lchash noaniqligi va ma’lumotlarni qayta ishlash',
                'ru' => 'Неопределенность измерений и обработка данных',
                'ar' => 'لايقين القياس ومعالجة البيانات',
            ]),
            $this->course('MSTD-110', 4, 1, [
                'en' => 'Metrological Support of Production',
                'uz' => 'Ishlab chiqarishni metrologik ta’minlash',
                'ru' => 'Метрологическое обеспечение производства',
                'ar' => 'الدعم المترولوجي للإنتاج',
            ]),
            $this->course('MSTD-111', 4, 1, [
                'en' => 'Laboratory Accreditation and Audit',
                'uz' => 'Laboratoriyalarni akkreditatsiya qilish va audit',
                'ru' => 'Аккредитация лабораторий и аудит',
                'ar' => 'اعتماد المختبرات والتدقيق',
            ]),
            $this->course('MSTD-112', 4, 2, [
                'en' => 'Industrial Practice in Metrology and Standardization',
                'uz' => 'Metrologiya va standartlashtirish bo‘yicha ishlab chiqarish amaliyoti',
                'ru' => 'Производственная практика по метрологии и стандартизации',
                'ar' => 'التدريب الصناعي في المترولوجيا والتقييس',
            ]),
            $this->course('MSTD-113', 4, 2, [
                'en' => 'Graduation Project in Metrology and Standardization',
                'uz' => 'Metrologiya va standartlashtirish bo‘yicha bitiruv loyihasi',
                'ru' => 'Выпускной проект по метрологии и стандартизации',
                'ar' => 'مشروع التخرج في المترولوجيا والتقييس',
            ]),
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
