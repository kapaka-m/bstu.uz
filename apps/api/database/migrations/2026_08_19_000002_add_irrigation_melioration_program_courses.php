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
            'uz' => $name.' fani suv xo‘jaligi, melioratsiya, loyihalash va ishlab chiqarish amaliyoti bo‘yicha kasbiy ko‘nikmalarni rivojlantiradi.',
            'ru' => 'Дисциплина «'.$name.'» развивает профессиональные навыки в области водного хозяйства, мелиорации, проектирования и производственной практики.',
            'ar' => 'يركز مقرر '.$name.' على تطوير المهارات المهنية في إدارة المياه والاستصلاح والتصميم والتطبيق العملي.',
            default => $name.' develops professional skills in water management, land reclamation, design, and industrial practice.',
        };
    }

    private function programCourses(): array
    {
        return [
            'water-management-and-land-reclamation-60811200' => [
                $this->course('WMLR-100', 1, 1, [
                    'en' => 'Introduction to Water Management and Land Reclamation',
                    'uz' => 'Suv xo‘jaligi va melioratsiyaga kirish',
                    'ru' => 'Введение в водное хозяйство и мелиорацию',
                    'ar' => 'مدخل إلى إدارة المياه واستصلاح الأراضي',
                ]),
                $this->course('WMLR-101', 1, 2, [
                    'en' => 'Irrigation and Land Reclamation',
                    'uz' => 'Sug‘orish va melioratsiya',
                    'ru' => 'Орошение и мелиорация',
                    'ar' => 'الري واستصلاح الأراضي',
                ]),
                $this->course('WMLR-102', 2, 1, [
                    'en' => 'Hydraulics for Water Management',
                    'uz' => 'Suv xo‘jaligi uchun gidravlika',
                    'ru' => 'Гидравлика для водного хозяйства',
                    'ar' => 'الهيدروليكا لإدارة المياه',
                ]),
                $this->course('WMLR-103', 2, 1, [
                    'en' => 'Soil Science and Salinity Control',
                    'uz' => 'Tuproqshunoslik va sho‘rlanishni boshqarish',
                    'ru' => 'Почвоведение и управление засолением',
                    'ar' => 'علم التربة والتحكم في الملوحة',
                ]),
                $this->course('WMLR-104', 2, 2, [
                    'en' => 'Water-Saving Irrigation Technologies',
                    'uz' => 'Suv tejovchi sug‘orish texnologiyalari',
                    'ru' => 'Водосберегающие технологии орошения',
                    'ar' => 'تقنيات الري الموفرة للمياه',
                ]),
                $this->course('WMLR-105', 2, 2, [
                    'en' => 'Drainage Systems and Reclamation Networks',
                    'uz' => 'Drenaj tizimlari va meliorativ tarmoqlar',
                    'ru' => 'Дренажные системы и мелиоративные сети',
                    'ar' => 'أنظمة الصرف وشبكات الاستصلاح',
                ]),
                $this->course('WMLR-106', 3, 1, [
                    'en' => 'Rational Use of Water Resources',
                    'uz' => 'Suv resurslaridan oqilona foydalanish',
                    'ru' => 'Рациональное использование водных ресурсов',
                    'ar' => 'الاستخدام الرشيد للموارد المائية',
                ]),
                $this->course('WMLR-107', 3, 1, [
                    'en' => 'Design of Irrigation Networks',
                    'uz' => 'Sug‘orish tarmoqlarini loyihalash',
                    'ru' => 'Проектирование оросительных сетей',
                    'ar' => 'تصميم شبكات الري',
                ]),
                $this->course('WMLR-108', 3, 2, [
                    'en' => 'Operation and Automation of Irrigation Systems',
                    'uz' => 'Sug‘orish tizimlarini ishlatish va avtomatlashtirish',
                    'ru' => 'Эксплуатация и автоматизация оросительных систем',
                    'ar' => 'تشغيل وأتمتة أنظمة الري',
                ]),
                $this->course('WMLR-109', 3, 2, [
                    'en' => 'Water Cadastre and Integrated Water Resources Management',
                    'uz' => 'Suv kadastri va suv resurslarini integratsiyalashgan boshqarish',
                    'ru' => 'Водный кадастр и интегрированное управление водными ресурсами',
                    'ar' => 'سجل المياه والإدارة المتكاملة للموارد المائية',
                ]),
                $this->course('WMLR-110', 4, 1, [
                    'en' => 'Meliorative Soil Science and Farming',
                    'uz' => 'Meliorativ tuproqshunoslik va dehqonchilik',
                    'ru' => 'Мелиоративное почвоведение и земледелие',
                    'ar' => 'علم التربة الاستصلاحي والزراعة',
                ]),
                $this->course('WMLR-111', 4, 1, [
                    'en' => 'Field Research Methods in Reclamation',
                    'uz' => 'Melioratsiyada dala tadqiqot usullari',
                    'ru' => 'Методы полевых исследований в мелиорации',
                    'ar' => 'طرق البحث الميداني في الاستصلاح',
                ]),
                $this->course('WMLR-112', 4, 2, [
                    'en' => 'Industrial Practice in Water Management',
                    'uz' => 'Suv xo‘jaligi bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по водному хозяйству',
                    'ar' => 'التدريب الصناعي في إدارة المياه',
                ]),
                $this->course('WMLR-113', 4, 2, [
                    'en' => 'Graduation Project in Water Management and Land Reclamation',
                    'uz' => 'Suv xo‘jaligi va melioratsiya bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по водному хозяйству и мелиорации',
                    'ar' => 'مشروع التخرج في إدارة المياه واستصلاح الأراضي',
                ]),
            ],
            'water-supply-engineering-systems-60811500' => [
                $this->course('WSES-100', 1, 1, [
                    'en' => 'Introduction to Water Supply Engineering Systems',
                    'uz' => 'Suv ta’minoti muhandislik tizimlariga kirish',
                    'ru' => 'Введение в инженерные системы водоснабжения',
                    'ar' => 'مدخل إلى أنظمة هندسة إمدادات المياه',
                ]),
                $this->course('WSES-101', 1, 2, [
                    'en' => 'Hydraulics of Water Supply Networks',
                    'uz' => 'Suv ta’minoti tarmoqlari gidravlikasi',
                    'ru' => 'Гидравлика сетей водоснабжения',
                    'ar' => 'هيدروليكا شبكات إمدادات المياه',
                ]),
                $this->course('WSES-102', 2, 1, [
                    'en' => 'Water Supply',
                    'uz' => 'Suv ta’minoti',
                    'ru' => 'Водоснабжение',
                    'ar' => 'إمدادات المياه',
                ]),
                $this->course('WSES-103', 2, 1, [
                    'en' => 'Water Purification Technology',
                    'uz' => 'Suvni tozalash texnologiyasi',
                    'ru' => 'Технология очистки воды',
                    'ar' => 'تقنية تنقية المياه',
                ]),
                $this->course('WSES-104', 2, 2, [
                    'en' => 'Design of Water Distribution Networks',
                    'uz' => 'Suv taqsimlash tarmoqlarini loyihalash',
                    'ru' => 'Проектирование водораспределительных сетей',
                    'ar' => 'تصميم شبكات توزيع المياه',
                ]),
                $this->course('WSES-105', 2, 2, [
                    'en' => 'Wastewater Reuse Fundamentals',
                    'uz' => 'Oqova suvlarni qayta ishlatish asoslari',
                    'ru' => 'Основы повторного использования сточных вод',
                    'ar' => 'أساسيات إعادة استخدام مياه الصرف',
                ]),
                $this->course('WSES-106', 3, 1, [
                    'en' => 'Installation of Water Supply and Sewage Networks',
                    'uz' => 'Suv ta’minoti va kanalizatsiya tarmoqlarini montaj qilish',
                    'ru' => 'Монтаж сетей водоснабжения и канализации',
                    'ar' => 'تركيب شبكات المياه والصرف الصحي',
                ]),
                $this->course('WSES-107', 3, 1, [
                    'en' => 'Operation of Water Supply Systems',
                    'uz' => 'Suv ta’minoti tizimlaridan foydalanish',
                    'ru' => 'Эксплуатация систем водоснабжения',
                    'ar' => 'تشغيل أنظمة إمدادات المياه',
                ]),
                $this->course('WSES-108', 3, 2, [
                    'en' => 'Water Intake Structures and Wells',
                    'uz' => 'Suv olish inshootlari va quduqlar',
                    'ru' => 'Водозаборные сооружения и скважины',
                    'ar' => 'منشآت وآبار سحب المياه',
                ]),
                $this->course('WSES-109', 3, 2, [
                    'en' => 'Control and Measuring Instruments in Networks',
                    'uz' => 'Tarmoqlardagi nazorat-o‘lchash asboblari',
                    'ru' => 'Контрольно-измерительные приборы в сетях',
                    'ar' => 'أجهزة القياس والتحكم في الشبكات',
                ]),
                $this->course('WSES-110', 4, 1, [
                    'en' => 'Modeling of Water Supply Technologies',
                    'uz' => 'Suv ta’minoti texnologiyalarini modellashtirish',
                    'ru' => 'Моделирование технологий водоснабжения',
                    'ar' => 'نمذجة تقنيات إمدادات المياه',
                ]),
                $this->course('WSES-111', 4, 1, [
                    'en' => 'Communal Infrastructure Management',
                    'uz' => 'Kommunal infratuzilmani boshqarish',
                    'ru' => 'Управление коммунальной инфраструктурой',
                    'ar' => 'إدارة البنية التحتية للمرافق العامة',
                ]),
                $this->course('WSES-112', 4, 2, [
                    'en' => 'Industrial Practice in Water Supply Systems',
                    'uz' => 'Suv ta’minoti tizimlari bo‘yicha ishlab chiqarish amaliyoti',
                    'ru' => 'Производственная практика по системам водоснабжения',
                    'ar' => 'التدريب الصناعي في أنظمة إمدادات المياه',
                ]),
                $this->course('WSES-113', 4, 2, [
                    'en' => 'Graduation Project in Water Supply Engineering',
                    'uz' => 'Suv ta’minoti muhandisligi bo‘yicha bitiruv loyihasi',
                    'ru' => 'Выпускной проект по инженерии водоснабжения',
                    'ar' => 'مشروع التخرج في هندسة إمدادات المياه',
                ]),
            ],
        ];
    }

    private function course(string $code, int $year, int $semester, array $translations, int $credits = 4): array
    {
        return compact('code', 'year', 'semester', 'translations', 'credits');
    }
};
