<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'economics-and-management')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            foreach ($this->locales() as $locale) {
                $translation = DB::table('department_translations')
                    ->where('department_id', $departmentId)
                    ->where('locale', $locale)
                    ->first();

                if (! $translation) {
                    continue;
                }

                DB::table('department_translations')
                    ->where('id', $translation->id)
                    ->update([
                        'content_sections' => json_encode($this->sections($departmentId, $locale), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at' => now(),
                    ]);
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function sections(int $departmentId, string $locale): array
    {
        return [
            ['key' => 'history', 'title' => $this->label($locale, 'history'), 'items' => [$this->history($locale)]],
            ['key' => 'prepared_specialists', 'title' => $this->label($locale, 'prepared'), 'items' => $this->programItems($departmentId, $locale)],
            [
                'key' => 'subjects',
                'title' => $this->label($locale, 'subjects'),
                'items' => [],
                'bachelor' => $this->bachelorSubjects($locale),
                'master' => $this->masterSubjects($locale),
            ],
            ['key' => 'staff', 'title' => $this->label($locale, 'staff'), 'items' => $this->staffItems($departmentId, $locale)],
            ['key' => 'research', 'title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)],
            ['key' => 'cooperation', 'title' => $this->label($locale, 'cooperation'), 'items' => $this->cooperationItems($locale)],
        ];
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

    private function staffItems(int $departmentId, string $locale): array
    {
        return DB::table('staff_profiles as s')
            ->join('staff_profile_translations as t', function ($join) use ($locale) {
                $join->on('t.staff_profile_id', '=', 's.id')->where('t.locale', '=', $locale);
            })
            ->where('s.department_id', $departmentId)
            ->where('s.is_active', true)
            ->orderBy('s.sort_order')
            ->select('t.full_name', 't.position')
            ->get()
            ->map(fn ($staff) => trim($staff->full_name."\n".$staff->position))
            ->values()
            ->all();
    }

    private function history(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra iqtisodiy tahlil, strategik boshqaruv, tadbirkorlik, marketing, moliya, buxgalteriya hisobi va zamonaviy biznes boshqaruvi bo‘yicha ta’lim beradi.',
            'ru' => 'Кафедра обеспечивает подготовку в области экономического анализа, стратегического управления, предпринимательства, маркетинга, финансов, бухгалтерского учета и современного управления бизнесом.',
            'ar' => 'يوفر القسم تعليما في التحليل الاقتصادي، الإدارة الاستراتيجية، ريادة الأعمال، التسويق، المالية، المحاسبة، وإدارة الأعمال الحديثة.',
            default => 'The department provides education in economic analysis, strategic management, entrepreneurship, marketing, finance, accounting, and modern business management.',
        };
    }

    private function bachelorSubjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Iqtisodiyot nazariyasi', 'Mikroiqtisodiyot', 'Makroiqtisodiyot', 'Menejment', 'Marketing', 'Buxgalteriya hisobi', 'Moliya', 'Moliyaviy texnologiyalar', 'Soliq va soliqqa tortish', 'Statistika', 'Ekonometrika', 'Biznes rejalashtirish', 'Tadbirkorlik asoslari', 'Strategik menejment', 'Innovatsion menejment', 'Inson resurslarini boshqarish', 'Logistika va ta’minot zanjiri boshqaruvi', 'Turizm va mehmondo‘stlik menejmenti', 'Raqamli iqtisodiyot', 'Bank ishi', 'Audit', 'Investitsiyalar', 'Korporativ moliya', 'Xalqaro iqtisodiy munosabatlar', 'Biznes kommunikatsiya', 'Iqtisodiy tahlil', 'Loyiha boshqaruvi', 'Bitiruv amaliyoti va loyiha'],
            'ru' => ['Экономическая теория', 'Микроэкономика', 'Макроэкономика', 'Менеджмент', 'Маркетинг', 'Бухгалтерский учет', 'Финансы', 'Финансовые технологии', 'Налоги и налогообложение', 'Статистика', 'Эконометрика', 'Бизнес-планирование', 'Основы предпринимательства', 'Стратегический менеджмент', 'Инновационный менеджмент', 'Управление человеческими ресурсами', 'Логистика и управление цепями поставок', 'Менеджмент туризма и гостеприимства', 'Цифровая экономика', 'Банковское дело', 'Аудит', 'Инвестиции', 'Корпоративные финансы', 'Международные экономические отношения', 'Деловая коммуникация', 'Экономический анализ', 'Управление проектами', 'Выпускная практика и проект'],
            'ar' => ['نظرية الاقتصاد', 'الاقتصاد الجزئي', 'الاقتصاد الكلي', 'الإدارة', 'التسويق', 'المحاسبة', 'المالية', 'التقنيات المالية', 'الضرائب والنظام الضريبي', 'الإحصاء', 'الاقتصاد القياسي', 'تخطيط الأعمال', 'أساسيات ريادة الأعمال', 'الإدارة الاستراتيجية', 'إدارة الابتكار', 'إدارة الموارد البشرية', 'اللوجستيات وإدارة سلاسل الإمداد', 'إدارة السياحة والضيافة', 'الاقتصاد الرقمي', 'العمل المصرفي', 'التدقيق', 'الاستثمارات', 'المالية المؤسسية', 'العلاقات الاقتصادية الدولية', 'الاتصال التجاري', 'التحليل الاقتصادي', 'إدارة المشاريع', 'التدريب ومشروع التخرج'],
            default => ['Economic Theory', 'Microeconomics', 'Macroeconomics', 'Management', 'Marketing', 'Accounting', 'Finance', 'Financial Technologies', 'Taxes and Taxation', 'Statistics', 'Econometrics', 'Business Planning', 'Entrepreneurship Fundamentals', 'Strategic Management', 'Innovation Management', 'Human Resource Management', 'Logistics and Supply Chain Management', 'Tourism and Hospitality Management', 'Digital Economy', 'Banking', 'Audit', 'Investments', 'Corporate Finance', 'International Economic Relations', 'Business Communication', 'Economic Analysis', 'Project Management', 'Graduation Internship and Project'],
        };
    }

    private function masterSubjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Ilmiy tadqiqot metodologiyasi', 'Ilg‘or iqtisodiy tahlil', 'Strategik boshqaruv metodlari', 'Raqamli biznes modellari', 'Moliyaviy menejment', 'Marketing tadqiqotlari', 'Innovatsion tadbirkorlik', 'Ilmiy-pedagogik ish', 'Magistrlik dissertatsiyasini tayyorlash'],
            'ru' => ['Методология научных исследований', 'Продвинутый экономический анализ', 'Методы стратегического управления', 'Цифровые бизнес-модели', 'Финансовый менеджмент', 'Маркетинговые исследования', 'Инновационное предпринимательство', 'Научно-педагогическая работа', 'Подготовка магистерской диссертации'],
            'ar' => ['منهجية البحث العلمي', 'التحليل الاقتصادي المتقدم', 'طرائق الإدارة الاستراتيجية', 'نماذج الأعمال الرقمية', 'الإدارة المالية', 'بحوث التسويق', 'ريادة الأعمال الابتكارية', 'العمل العلمي والتربوي', 'إعداد رسالة الماجستير'],
            default => ['Research Methodology', 'Advanced Economic Analysis', 'Strategic Management Methods', 'Digital Business Models', 'Financial Management', 'Marketing Research', 'Innovative Entrepreneurship', 'Scientific and Pedagogical Work', 'Preparation of Master’s Thesis'],
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Innovatsion tadbirkorlik va raqamli iqtisodiyot bo‘yicha tadqiqotlar.', 'Marketing, menejment va moliyaviy texnologiyalar bo‘yicha amaliy tadqiqotlar.', 'Aholi prognozi, ijtimoiy-iqtisodiy tizimlar va hududiy rivojlanish masalalarini o‘rganish.', 'Biznes boshqaruvi va raqobatbardoshlikni oshirish bo‘yicha ilmiy-amaliy ishlanmalar.'],
            'ru' => ['Исследования в области инновационного предпринимательства и цифровой экономики.', 'Прикладные исследования по маркетингу, менеджменту и финансовым технологиям.', 'Изучение прогнозирования населения, социально-экономических систем и регионального развития.', 'Научно-практические разработки по управлению бизнесом и повышению конкурентоспособности.'],
            'ar' => ['أبحاث في ريادة الأعمال الابتكارية والاقتصاد الرقمي.', 'دراسات تطبيقية في التسويق والإدارة والتقنيات المالية.', 'دراسة التنبؤ السكاني والأنظمة الاجتماعية والاقتصادية والتنمية الإقليمية.', 'تطويرات علمية وتطبيقية في إدارة الأعمال ورفع القدرة التنافسية.'],
            default => ['Research in innovative entrepreneurship and the digital economy.', 'Applied studies in marketing, management, and financial technologies.', 'Studies of population forecasting, socio-economic systems, and regional development.', 'Scientific and practical developments in business management and competitiveness improvement.'],
        };
    }

    private function cooperationItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Belgorod State National Research University', 'Iqtisodiyot, menejment, marketing va innovatsiyalar bo‘yicha xalqaro ilmiy konferensiyalar'],
            'ru' => ['Белгородский государственный национальный исследовательский университет', 'Международные научные конференции по экономике, менеджменту, маркетингу и инновациям'],
            'ar' => ['جامعة بيلغورود الوطنية للبحوث', 'مؤتمرات علمية دولية في الاقتصاد والإدارة والتسويق والابتكار'],
            default => ['Belgorod State National Research University', 'International scientific conferences on economics, management, marketing, and innovation'],
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'history' => ['en' => 'Department History', 'uz' => 'Kafedra tarixi', 'ru' => 'История кафедры', 'ar' => 'تاريخ القسم'],
            'prepared' => ['en' => 'Prepared Specialists', 'uz' => 'Tayyorlanadigan mutaxassislar', 'ru' => 'Подготавливаемые специалисты', 'ar' => 'التخصصات التي يتم إعدادها'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'O‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد الدراسية'],
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'research' => ['en' => 'Ongoing Research', 'uz' => 'Joriy ilmiy tadqiqotlar', 'ru' => 'Текущие исследования', 'ar' => 'الأبحاث الجارية'],
            'cooperation' => ['en' => 'Cooperation / International Relations', 'uz' => 'Hamkorlik / xalqaro aloqalar', 'ru' => 'Сотрудничество / международные связи', 'ar' => 'التعاون / العلاقات الدولية'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function locales(): array
    {
        return ['en', 'uz', 'ru', 'ar'];
    }
};
