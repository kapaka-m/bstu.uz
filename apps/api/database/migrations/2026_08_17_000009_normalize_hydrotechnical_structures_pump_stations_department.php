<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'hydrotechnical-structures-pump-stations')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeSections($departmentId);
            $this->normalizeStaff($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function normalizeSections(int $departmentId): void
    {
        foreach ($this->sectionContent() as $locale => $content) {
            $translation = DB::table('department_translations')
                ->where('department_id', $departmentId)
                ->where('locale', $locale)
                ->first();

            if (! $translation) {
                continue;
            }

            $sections = [
                [
                    'key' => 'overview',
                    'title' => $content['titles']['overview'],
                    'items' => [$content['overview']],
                ],
                [
                    'key' => 'prepared_specialists',
                    'title' => $content['titles']['prepared'],
                    'items' => $this->programItems($departmentId, $locale),
                ],
                [
                    'key' => 'subjects',
                    'title' => $content['titles']['subjects'],
                    'items' => [],
                    'bachelor' => $content['subjects'],
                    'master' => [],
                ],
                [
                    'key' => 'research',
                    'title' => $content['titles']['research'],
                    'items' => $content['research'],
                ],
                [
                    'key' => 'cooperation',
                    'title' => $content['titles']['cooperation'],
                    'items' => $content['cooperation'],
                ],
            ];

            DB::table('department_translations')
                ->where('id', $translation->id)
                ->update([
                    'description' => $content['overview'],
                    'content_sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
        }
    }

    private function programItems(int $departmentId, string $locale): array
    {
        return DB::table('programs as p')
            ->leftJoin('program_translations as t', function ($join) use ($locale) {
                $join->on('t.program_id', '=', 'p.id')->where('t.locale', '=', $locale);
            })
            ->where('p.department_id', $departmentId)
            ->where('p.is_active', true)
            ->orderBy('p.official_code')
            ->select('p.official_code', 't.name')
            ->get()
            ->map(fn ($program) => trim($program->official_code.' - '.$program->name))
            ->values()
            ->all();
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteIds = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereIn('slug', [
                'hydrotechnical-structures-pump-stations-0-dr-nemat-k-saidov',
                'hydrotechnical-structures-pump-stations-1-mavjuda-r-sharipova',
                'hydrotechnical-structures-pump-stations-2-rustam-b-fayziyev',
            ])
            ->pluck('id');

        if ($deleteIds->isNotEmpty()) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $deleteIds)->delete();
            DB::table('staff_profiles')->whereIn('id', $deleteIds)->delete();
        }

        $profileId = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->where('slug', 'hydrotechnical-structures-pump-stations-hydraulic-structures-and-pumping-stations')
            ->value('id');

        if (! $profileId) {
            $profileId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', 'hydrotechnical-structures-pump-stations-axmedov-sharifboy-roziyevich')
                ->value('id');
        }

        if (! $profileId) {
            return;
        }

        DB::table('staff_profiles')->where('id', $profileId)->update([
            'slug' => 'hydrotechnical-structures-pump-stations-axmedov-sharifboy-roziyevich',
            'sort_order' => 10,
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
                    'office' => 'Monday-Friday 14:00-16:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function headTranslations(): array
    {
        return [
            'en' => [
                'name' => 'Axmedov Sharifboy Ro‘ziyevich',
                'position' => 'Head of Department',
                'bio' => 'Axmedov Sharifboy Ro‘ziyevich leads the Department of Hydraulic Structures and Pumping Stations and supports academic, laboratory, and practical training.',
            ],
            'uz' => [
                'name' => 'Axmedov Sharifboy Ro‘ziyevich',
                'position' => 'Kafedra mudiri',
                'bio' => 'Axmedov Sharifboy Ro‘ziyevich Gidrotexnika inshootlari va nasos stansiyalari kafedrasiga rahbarlik qiladi hamda o‘quv, laboratoriya va amaliy tayyorgarlikni rivojlantirishga hissa qo‘shadi.',
            ],
            'ru' => [
                'name' => 'Ахмедов Шарифбой Рузиевич',
                'position' => 'Заведующий кафедрой',
                'bio' => 'Ахмедов Шарифбой Рузиевич руководит кафедрой гидротехнических сооружений и насосных станций и участвует в развитии учебной, лабораторной и практической подготовки.',
            ],
            'ar' => [
                'name' => 'أحمدوف شريفبوي روزييفيتش',
                'position' => 'رئيس القسم',
                'bio' => 'يقود أحمدوف شريفبوي روزييفيتش قسم المنشآت الهيدروليكية ومحطات الضخ ويساهم في تطوير التعليم والتدريب المخبري والعملي.',
            ],
        ];
    }

    private function sectionContent(): array
    {
        return [
            'en' => [
                'titles' => ['overview' => 'Department History', 'prepared' => 'Prepared Specialists', 'subjects' => 'Taught Subjects', 'research' => 'Ongoing Research', 'cooperation' => 'Cooperation / International Relations'],
                'overview' => 'Hydraulic Structures and Pumping Stations is an important engineering field that ensures the management, distribution, and efficient use of water resources.',
                'subjects' => ['Hydraulic structures', 'Pumping stations', 'Hydropower engineering', 'Hydraulics and fluid mechanics', 'Engineering hydrology', 'Operation of hydraulic structures', 'Geotechnical engineering fundamentals', 'Water resource systems', 'Design of pumping equipment', 'Industrial practice and graduation project'],
                'research' => ['Research on hydraulic structures, pumping stations, and reliable water distribution systems.', 'Applied studies on hydropower, geotechnical safety, and efficient operation of water infrastructure.', 'Development of practical engineering solutions for water management facilities and pumping equipment.'],
                'cooperation' => ['Cooperation with water management organizations and operating enterprises.', 'Practical training and field practice at hydraulic structures, pumping stations, and water infrastructure facilities.', 'Academic and methodological cooperation with partner universities and research institutions in water engineering.'],
            ],
            'uz' => [
                'titles' => ['overview' => 'Kafedra tarixi', 'prepared' => 'Tayyorlanadigan mutaxassislar', 'subjects' => 'O‘qitiladigan fanlar', 'research' => 'Ilmiy tadqiqotlar', 'cooperation' => 'Hamkorlik / xalqaro aloqalar'],
                'overview' => 'Gidrotexnika inshootlari va nasos stansiyalari suv resurslarini boshqarish, taqsimlash va ulardan samarali foydalanishni ta’minlaydigan muhim muhandislik yo‘nalishidir.',
                'subjects' => ['Gidrotexnika inshootlari', 'Nasos stansiyalari', 'Gidroenergetika muhandisligi', 'Gidravlika va suyuqliklar mexanikasi', 'Muhandislik gidrologiyasi', 'Gidrotexnika inshootlaridan foydalanish', 'Geotexnika muhandisligi asoslari', 'Suv resurslari tizimlari', 'Nasos uskunalarini loyihalash', 'Ishlab chiqarish amaliyoti va bitiruv loyihasi'],
                'research' => ['Gidrotexnika inshootlari, nasos stansiyalari va ishonchli suv taqsimlash tizimlari bo‘yicha tadqiqotlar.', 'Gidroenergetika, geotexnik xavfsizlik va suv infratuzilmasidan samarali foydalanish bo‘yicha amaliy izlanishlar.', 'Suv xo‘jaligi obyektlari va nasos uskunalari uchun amaliy muhandislik yechimlarini ishlab chiqish.'],
                'cooperation' => ['Suv xo‘jaligi tashkilotlari va ekspluatatsiya korxonalari bilan hamkorlik.', 'Gidrotexnika inshootlari, nasos stansiyalari va suv infratuzilmasi obyektlarida amaliy mashg‘ulotlar va ishlab chiqarish amaliyoti.', 'Suv muhandisligi yo‘nalishida hamkor universitetlar va ilmiy tashkilotlar bilan o‘quv-uslubiy hamkorlik.'],
            ],
            'ru' => [
                'titles' => ['overview' => 'История кафедры', 'prepared' => 'Подготовка специалистов', 'subjects' => 'Преподаваемые дисциплины', 'research' => 'Текущие исследования', 'cooperation' => 'Сотрудничество / международные связи'],
                'overview' => 'Гидротехнические сооружения и насосные станции являются важным инженерным направлением, обеспечивающим управление, распределение и эффективное использование водных ресурсов.',
                'subjects' => ['Гидротехнические сооружения', 'Насосные станции', 'Гидроэнергетика', 'Гидравлика и механика жидкости', 'Инженерная гидрология', 'Эксплуатация гидротехнических сооружений', 'Основы геотехнической инженерии', 'Системы водных ресурсов', 'Проектирование насосного оборудования', 'Производственная практика и выпускной проект'],
                'research' => ['Исследования гидротехнических сооружений, насосных станций и надежных систем распределения воды.', 'Прикладные исследования в области гидроэнергетики, геотехнической безопасности и эффективной эксплуатации водной инфраструктуры.', 'Разработка практических инженерных решений для объектов водного хозяйства и насосного оборудования.'],
                'cooperation' => ['Сотрудничество с организациями водного хозяйства и эксплуатационными предприятиями.', 'Практическая подготовка и производственная практика на гидротехнических сооружениях, насосных станциях и объектах водной инфраструктуры.', 'Учебно-методическое сотрудничество с партнерскими университетами и научными организациями в области водной инженерии.'],
            ],
            'ar' => [
                'titles' => ['overview' => 'تاريخ القسم', 'prepared' => 'التخصصات التي يتم إعدادها', 'subjects' => 'المواد الدراسية', 'research' => 'الأبحاث الجارية', 'cooperation' => 'التعاون / العلاقات الدولية'],
                'overview' => 'تعد المنشآت الهيدروليكية ومحطات الضخ مجالا هندسيا مهما يضمن إدارة الموارد المائية وتوزيعها واستخدامها بكفاءة.',
                'subjects' => ['المنشآت الهيدروليكية', 'محطات الضخ', 'هندسة الطاقة الكهرومائية', 'الهيدروليكا وميكانيكا الموائع', 'الهيدرولوجيا الهندسية', 'تشغيل المنشآت الهيدروليكية', 'أساسيات الهندسة الجيوتقنية', 'أنظمة الموارد المائية', 'تصميم معدات الضخ', 'التدريب الصناعي ومشروع التخرج'],
                'research' => ['أبحاث حول المنشآت الهيدروليكية ومحطات الضخ وأنظمة توزيع المياه الموثوقة.', 'دراسات تطبيقية في الطاقة الكهرومائية والسلامة الجيوتقنية والتشغيل الفعال للبنية التحتية المائية.', 'تطوير حلول هندسية عملية لمنشآت إدارة المياه ومعدات الضخ.'],
                'cooperation' => ['التعاون مع مؤسسات إدارة المياه وشركات التشغيل.', 'التدريب العملي والميداني في المنشآت الهيدروليكية ومحطات الضخ ومرافق البنية التحتية المائية.', 'التعاون الأكاديمي والمنهجي مع الجامعات والمؤسسات البحثية الشريكة في هندسة المياه.'],
            ],
        ];
    }
};
