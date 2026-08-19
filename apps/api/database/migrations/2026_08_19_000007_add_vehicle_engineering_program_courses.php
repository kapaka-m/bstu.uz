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

        $programId = DB::table('programs')->where('slug', 'vehicle-engineering-60711400')->value('id');

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
            'uz' => $name.' fani transport vositalari konstruktsiyasi, ekspluatatsiyasi, diagnostikasi va servis xizmati bo‘yicha kasbiy ko‘nikmalarni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональные навыки в области конструкции, эксплуатации, диагностики и сервиса транспортных средств.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المهارات المهنية في تصميم المركبات وتشغيلها وتشخيصها وخدمتها الفنية.',
            default => $name.' develops professional skills in vehicle design, operation, diagnostics, and technical service.',
        };
    }

    private function courses(): array
    {
        return [
            $this->course('VEHI-100', 1, 1, [
                'en' => 'Introduction to Vehicle Engineering',
                'uz' => 'Transport vositalari muhandisligiga kirish',
                'ru' => 'Введение в инженерию транспортных средств',
                'ar' => 'مدخل إلى هندسة المركبات',
            ]),
            $this->course('VEHI-101', 1, 2, [
                'en' => 'Vehicle Construction',
                'uz' => 'Transport vositalari konstruktsiyasi',
                'ru' => 'Конструкция транспортных средств',
                'ar' => 'تصميم المركبات',
            ]),
            $this->course('VEHI-102', 2, 1, [
                'en' => 'Internal Combustion Engines',
                'uz' => 'Ichki yonuv dvigatellari',
                'ru' => 'Двигатели внутреннего сгорания',
                'ar' => 'محركات الاحتراق الداخلي',
            ]),
            $this->course('VEHI-103', 2, 1, [
                'en' => 'Vehicle Electrical Equipment and Electronic Systems',
                'uz' => 'Transport vositalarining elektr jihozlari va elektron tizimlari',
                'ru' => 'Электрооборудование и электронные системы транспортных средств',
                'ar' => 'المعدات الكهربائية والأنظمة الإلكترونية للمركبات',
            ]),
            $this->course('VEHI-104', 2, 2, [
                'en' => 'Technical Diagnostics of Automobiles',
                'uz' => 'Avtomobillarni texnik diagnostikalash',
                'ru' => 'Техническая диагностика автомобилей',
                'ar' => 'التشخيص الفني للسيارات',
            ]),
            $this->course('VEHI-105', 2, 2, [
                'en' => 'Technical Operation and Service of Automobiles',
                'uz' => 'Avtomobillarning texnik ekspluatatsiyasi va servisi',
                'ru' => 'Техническая эксплуатация и сервис автомобилей',
                'ar' => 'التشغيل الفني وخدمة السيارات',
            ]),
            $this->course('VEHI-106', 3, 1, [
                'en' => 'Repair of Vehicle Parts',
                'uz' => 'Transport vositalari detallarini ta’mirlash',
                'ru' => 'Ремонт деталей транспортных средств',
                'ar' => 'إصلاح أجزاء المركبات',
            ]),
            $this->course('VEHI-107', 3, 1, [
                'en' => 'Automobile Engine Service and Repair',
                'uz' => 'Avtomobil dvigatellariga servis xizmati ko‘rsatish va ta’mirlash',
                'ru' => 'Сервис и ремонт автомобильных двигателей',
                'ar' => 'خدمة وإصلاح محركات السيارات',
            ]),
            $this->course('VEHI-108', 3, 2, [
                'en' => 'Vehicle Operational Materials',
                'uz' => 'Transport vositalarida ishlatiladigan ekspluatatsion materiallar',
                'ru' => 'Эксплуатационные материалы транспортных средств',
                'ar' => 'مواد التشغيل المستخدمة في المركبات',
            ]),
            $this->course('VEHI-109', 3, 2, [
                'en' => 'Road Transport Enterprise Design and Equipment',
                'uz' => 'Avtotransport korxonalarini loyihalash va jihozlash',
                'ru' => 'Проектирование и оснащение автотранспортных предприятий',
                'ar' => 'تصميم وتجهيز مؤسسات النقل البري',
            ]),
            $this->course('VEHI-110', 4, 1, [
                'en' => 'Traffic Rules and Road Safety',
                'uz' => 'Yo‘l harakati qoidalari va harakat xavfsizligi',
                'ru' => 'Правила дорожного движения и безопасность движения',
                'ar' => 'قواعد المرور وسلامة الحركة',
            ]),
            $this->course('VEHI-111', 4, 1, [
                'en' => 'Electric and Hybrid Vehicles',
                'uz' => 'Elektr va gibrid transport vositalari',
                'ru' => 'Электрические и гибридные транспортные средства',
                'ar' => 'المركبات الكهربائية والهجينة',
            ]),
            $this->course('VEHI-112', 4, 2, [
                'en' => 'Industrial Practice in Vehicle Engineering',
                'uz' => 'Transport vositalari muhandisligi bo‘yicha ishlab chiqarish amaliyoti',
                'ru' => 'Производственная практика по инженерии транспортных средств',
                'ar' => 'التدريب الصناعي في هندسة المركبات',
            ]),
            $this->course('VEHI-113', 4, 2, [
                'en' => 'Graduation Project in Vehicle Engineering',
                'uz' => 'Transport vositalari muhandisligi bo‘yicha bitiruv loyihasi',
                'ru' => 'Выпускной проект по инженерии транспортных средств',
                'ar' => 'مشروع التخرج في هندسة المركبات',
            ]),
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
