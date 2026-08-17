<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'architecture')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->deletePlaceholderStaff($departmentId);
            $this->normalizeDepartmentSections($departmentId);
            $this->normalizeStaff($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function deletePlaceholderStaff(int $departmentId): void
    {
        $slugs = [
            'architecture-0-prof-mubin-m-vakhitov',
            'architecture-1-dr-hoshim-r-roziyev',
            'architecture-2-dr-bohodir-n-nigmatullayev',
            'architecture-3-gayrat-b-rabiyev',
            'architecture-4-mehriddin-m-khudayberdiyev',
            'architecture-5-maqsuda-n-sulaymonova',
            'architecture-6-dr-murodjon-u-jorayev',
        ];

        $ids = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereIn('slug', $slugs)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
        DB::table('staff_profiles')->whereIn('id', $ids)->delete();
    }

    private function normalizeDepartmentSections(int $departmentId): void
    {
        foreach ($this->departmentTranslations() as $locale => $payload) {
            DB::table('department_translations')->updateOrInsert(
                ['department_id' => $departmentId, 'locale' => $locale],
                [
                    'name' => $payload['name'],
                    'short_name' => $payload['short_name'],
                    'description' => $payload['description'],
                    'content_sections' => json_encode($payload['sections'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'meta_title' => $payload['name'],
                    'meta_description' => $payload['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function normalizeStaff(int $departmentId): void
    {
        $sort = 1;

        foreach ($this->staff() as $oldSlug => $item) {
            $slug = $item['slug'] ?? $oldSlug;

            DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->whereIn('slug', array_values(array_unique([$oldSlug, $slug])))
                ->update([
                    'slug' => $slug,
                    'sort_order' => $sort++,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            $profileId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $slug)
                ->value('id');

            if (! $profileId) {
                continue;
            }

            foreach ($item['translations'] as $locale => $translation) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $translation['name'],
                        'position' => $translation['position'],
                        'bio' => $translation['bio'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }
    }

    private function departmentTranslations(): array
    {
        return [
            'en' => [
                'name' => 'Architecture',
                'short_name' => 'Architecture',
                'description' => 'The Department of Architecture prepares specialists in architectural design, urban planning, restoration, building design, and sustainable spatial development.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'About the Department', 'items' => ['The department combines architectural theory, studio design, drawing, digital modeling, field practice, restoration studies, and applied design projects.']],
                    ['key' => 'subjects', 'title' => 'Taught Subjects', 'items' => [
                        'Architectural design fundamentals',
                        'Architectural drawing and composition',
                        'History of architecture and urban planning',
                        'Building structures and construction materials',
                        'Residential and public building design',
                        'Urban construction and planning',
                        'Restoration of architectural monuments',
                        'Computer-aided architectural design',
                        'Landscape architecture and environmental design',
                        'Professional practice and graduation project',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Programs and Specializations', 'items' => ['60730100 - Architecture', '60730800 - Reconstruction and Restoration of Architectural Monuments', '60730900 - Urban Construction and Planning']],
                    ['key' => 'research', 'title' => 'Research Work', 'items' => [
                        'Research on architectural heritage, restoration, urban environment, and sustainable building design.',
                        'Development of design approaches for modern public, residential, and industrial buildings.',
                        'Application of digital modeling and visualization tools in architectural education and practice.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'International Cooperation', 'items' => [
                        'The department cooperates with design organizations, construction enterprises, restoration specialists, research institutions, and partner universities.',
                    ]],
                    ['key' => 'department_structure', 'title' => 'Department Structure', 'items' => ['Head of Department: Mirzayev Shamsiddin Rajabovich', 'Office hours: Monday-Friday 14:00-16:00', 'Phone: +998 91 014 02 59', 'Email: mirzaev.shamsiddin@mail.ru']],
                ],
            ],
            'uz' => [
                'name' => 'Arxitektura kafedrasi',
                'short_name' => 'Arxitektura kafedrasi',
                'description' => 'Arxitektura kafedrasi arxitekturaviy loyihalash, shaharsozlik, restavratsiya, bino loyihalash va barqaror fazoviy rivojlanish bo‘yicha mutaxassislar tayyorlaydi.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'Kafedra haqida', 'items' => ['Kafedra arxitektura nazariyasi, loyiha studiyalari, chizmachilik, raqamli modellashtirish, dala amaliyoti, restavratsiya va amaliy dizayn loyihalarini uyg‘unlashtiradi.']],
                    ['key' => 'subjects', 'title' => 'O‘qitiladigan fanlar', 'items' => [
                        'Arxitekturaviy loyihalash asoslari',
                        'Arxitektura chizmachiligi va kompozitsiya',
                        'Arxitektura va shaharsozlik tarixi',
                        'Bino konstruksiyalari va qurilish materiallari',
                        'Turar joy va jamoat binolarini loyihalash',
                        'Shahar qurilishi va rejalashtirish',
                        'Me’moriy yodgorliklarni restavratsiya qilish',
                        'Kompyuter yordamida arxitekturaviy loyihalash',
                        'Landshaft arxitekturasi va ekologik dizayn',
                        'Kasbiy amaliyot va bitiruv loyihasi',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Dasturlar va mutaxassisliklar', 'items' => ['60730100 - Arxitektura', '60730800 - Me’moriy yodgorliklarni rekonstruksiya va restavratsiya qilish', '60730900 - Shahar qurilishi va rejalashtirish']],
                    ['key' => 'research', 'title' => 'Ilmiy ishlar', 'items' => [
                        'Arxitektura merosi, restavratsiya, shahar muhiti va barqaror bino loyihalash bo‘yicha tadqiqotlar.',
                        'Zamonaviy jamoat, turar joy va sanoat binolari uchun loyiha yondashuvlarini rivojlantirish.',
                        'Arxitektura ta’limi va amaliyotida raqamli modellashtirish hamda vizualizatsiya vositalarini qo‘llash.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Xalqaro hamkorlik', 'items' => [
                        'Kafedra loyiha tashkilotlari, qurilish korxonalari, restavratsiya mutaxassislari, ilmiy muassasalar va hamkor universitetlar bilan hamkorlik qiladi.',
                    ]],
                    ['key' => 'department_structure', 'title' => 'Kafedra tuzilmasi', 'items' => ['Kafedra mudiri: Mirzayev Shamsiddin Rajabovich', 'Qabul vaqti: Dushanba-juma 14:00-16:00', 'Telefon: +998 91 014 02 59', 'Elektron pochta: mirzaev.shamsiddin@mail.ru']],
                ],
            ],
            'ru' => [
                'name' => 'Кафедра архитектуры',
                'short_name' => 'Кафедра архитектуры',
                'description' => 'Кафедра архитектуры готовит специалистов в области архитектурного проектирования, градостроительства, реставрации, проектирования зданий и устойчивого пространственного развития.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'О кафедре', 'items' => ['Кафедра объединяет архитектурную теорию, проектные студии, черчение, цифровое моделирование, полевую практику, реставрационные исследования и прикладные проекты.']],
                    ['key' => 'subjects', 'title' => 'Учебные дисциплины', 'items' => [
                        'Основы архитектурного проектирования',
                        'Архитектурное черчение и композиция',
                        'История архитектуры и градостроительства',
                        'Строительные конструкции и материалы',
                        'Проектирование жилых и общественных зданий',
                        'Городское строительство и планирование',
                        'Реставрация архитектурных памятников',
                        'Компьютерное архитектурное проектирование',
                        'Ландшафтная архитектура и экологический дизайн',
                        'Профессиональная практика и выпускной проект',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Программы и специализации', 'items' => ['60730100 - Архитектура', '60730800 - Реконструкция и реставрация архитектурных памятников', '60730900 - Городское строительство и планирование']],
                    ['key' => 'research', 'title' => 'Научная работа', 'items' => [
                        'Исследования архитектурного наследия, реставрации, городской среды и устойчивого проектирования зданий.',
                        'Развитие проектных подходов для современных общественных, жилых и промышленных зданий.',
                        'Применение цифрового моделирования и визуализации в архитектурном образовании и практике.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Международное сотрудничество', 'items' => [
                        'Кафедра сотрудничает с проектными организациями, строительными предприятиями, специалистами по реставрации, научными учреждениями и партнерскими университетами.',
                    ]],
                    ['key' => 'department_structure', 'title' => 'Структура кафедры', 'items' => ['Заведующий кафедрой: Мирзаев Шамсиддин Ражабович', 'Часы приема: понедельник-пятница 14:00-16:00', 'Телефон: +998 91 014 02 59', 'Электронная почта: mirzaev.shamsiddin@mail.ru']],
                ],
            ],
            'ar' => [
                'name' => 'قسم العمارة',
                'short_name' => 'قسم العمارة',
                'description' => 'يعد قسم العمارة متخصصين في التصميم المعماري والتخطيط العمراني والترميم وتصميم المباني والتنمية المكانية المستدامة.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'عن القسم', 'items' => ['يجمع القسم بين نظرية العمارة واستوديوهات التصميم والرسم والنمذجة الرقمية والتدريب الميداني ودراسات الترميم والمشروعات التطبيقية.']],
                    ['key' => 'subjects', 'title' => 'المواد التي تدرس في القسم', 'items' => [
                        'أساسيات التصميم المعماري',
                        'الرسم المعماري والتكوين',
                        'تاريخ العمارة والتخطيط العمراني',
                        'هياكل المباني ومواد البناء',
                        'تصميم المباني السكنية والعامة',
                        'البناء الحضري والتخطيط',
                        'ترميم المعالم المعمارية',
                        'التصميم المعماري بالحاسوب',
                        'عمارة المناظر الطبيعية والتصميم البيئي',
                        'التدريب المهني ومشروع التخرج',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'البرامج والتخصصات', 'items' => ['60730100 - العمارة', '60730800 - إعادة بناء وترميم المعالم المعمارية', '60730900 - البناء والتخطيط الحضري']],
                    ['key' => 'research', 'title' => 'الأعمال البحثية', 'items' => [
                        'أبحاث في التراث المعماري والترميم والبيئة الحضرية والتصميم المستدام للمباني.',
                        'تطوير أساليب التصميم للمباني العامة والسكنية والصناعية الحديثة.',
                        'تطبيق أدوات النمذجة الرقمية والعرض المرئي في التعليم والممارسة المعمارية.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'التعاون الدولي', 'items' => [
                        'يتعاون القسم مع مؤسسات التصميم وشركات البناء ومتخصصي الترميم والمؤسسات البحثية والجامعات الشريكة.',
                    ]],
                    ['key' => 'department_structure', 'title' => 'هيكل القسم', 'items' => ['رئيس القسم: ميرزاييف شمس الدين رجبوفيتش', 'ساعات الاستقبال: الاثنين-الجمعة 14:00-16:00', 'الهاتف: +998 91 014 02 59', 'البريد الإلكتروني: mirzaev.shamsiddin@mail.ru']],
                ],
            ],
        ];
    }

    private function staff(): array
    {
        return [
            'architecture-mirzaev-shamsiddin-rajabovich' => $this->staffItem('Mirzayev Shamsiddin Rajabovich', 'Мирзаев Шамсиддин Ражабович', 'ميرزاييف شمس الدين رجبوفيتش', 'Head of Department', 'Kafedra mudiri', 'Заведующий кафедрой', 'رئيس القسم'),
            'architecture-mubin-muminovich' => $this->staffItem('Vakhitov Mubin Muminovich', 'Вахитов Мубин Муминович', 'فاخيتوف موبين مومينوفيتش', 'Doctor of Technical Sciences, Professor', 'Texnika fanlari doktori, professor', 'Доктор технических наук, профессор', 'دكتور في العلوم التقنية، أستاذ', 'architecture-vakhitov-mubin-muminovich'),
            'architecture-of-the-technical-sciences-roziyev-hoshim-roziyevich' => $this->staffItem("Roziyev Hoshim Ro'ziyevich", 'Розиев Хошим Розиевич', 'روزييف هوشيم روزييفيتش', 'Candidate of Technical Sciences, Associate Professor', 'Texnika fanlari nomzodi, dotsent', 'Кандидат технических наук, доцент', 'مرشح في العلوم التقنية، أستاذ مشارك', 'architecture-roziyev-hoshim-roziyevich'),
            'architecture-nigmatullayev-bohodir-nurullayevich' => $this->staffItem('Nigmatullayev Bohodir Nurullayevich', 'Нигматуллаев Бохудир Нуруллаевич', 'نيغماتوللاييف بوهودير نوروللاييفيتش', 'Candidate of Technical Sciences, Associate Professor', 'Texnika fanlari nomzodi, dotsent', 'Кандидат технических наук, доцент', 'مرشح في العلوم التقنية، أستاذ مشارك'),
            'architecture-suleymanova-maksuda-nadirovna' => $this->staffItem('Suleymanova Maksuda Nadirovna', 'Сулейманова Максуда Надировна', 'سليمانوفا ماكسودا ناديروفنا', 'Senior Lecturer', 'Katta o‘qituvchi', 'Старший преподаватель', 'محاضر أول'),
            'architecture-of-philosophy-in-architectural-sciences-jurayev-murodjon-ulugbekovich' => $this->staffItem('Jurayev Murodjon Ulugbekovich', 'Жураев Муроджон Улугбекович', 'جوراييف مرادجون أولوغبيكوفيتش', 'PhD in Architecture, Senior Lecturer', 'Arxitektura fanlari bo‘yicha PhD, katta o‘qituvchi', 'PhD по архитектуре, старший преподаватель', 'دكتوراه في العمارة، محاضر أول', 'architecture-jurayev-murodjon-ulugbekovich'),
            'architecture-shokirov-yunus-mamatovich' => $this->staffItem('Shokirov Yunus Mamatovich', 'Шокиров Юнус Маматович', 'شوكيروف يونس ماماتوفيتش', 'Senior Lecturer', 'Katta o‘qituvchi', 'Старший преподаватель', 'محاضر أول'),
            'architecture-muxammadov-said-karimovich' => $this->staffItem('Muxammadov Said Karimovich', 'Мухаммадов Саид Каримович', 'محمدوف سعيد كريمويتش', 'Assistant', 'Assistent', 'Ассистент', 'مساعد'),
            'architecture-jorabek-toyirovich' => $this->staffItem('Toshev Jorabek Toyirovich', 'Тошев Жорабек Тойирович', 'توشيف جورابيك تويروفيتش', 'Trainee Lecturer', 'Stajyor-o‘qituvchi', 'Преподаватель-стажер', 'محاضر متدرب', 'architecture-toshev-jorabek-toyirovich'),
            'architecture-boboeva-madina-shamsiddinovna' => $this->staffItem('Boboeva Madina Shamsiddinovna', 'Бобоева Мадина Шамсиддиновна', 'بوبوييفا مادينا شمس الدينوفنا', 'Trainee Lecturer', 'Stajyor-o‘qituvchi', 'Преподаватель-стажер', 'محاضر متدرب'),
            'architecture-rakhmonov-gayrat-fazliddinovich' => $this->staffItem("Rakhmonov G'ayrat Fazliddinovich", 'Рахмонов Гайрат Фазлиддинович', 'رحمونوف غيرات فضل الدينوفيتش', 'Trainee Lecturer', 'Stajyor-o‘qituvchi', 'Преподаватель-стажер', 'محاضر متدرب'),
            'architecture-bobomurotov-hamza-halimovich' => $this->staffItem('Bobomurotov Hamza Halimovich', 'Бобомуротов Хамза Халимович', 'بوبوموروتوف حمزة حليموفيتش', 'Trainee Lecturer', 'Stajyor-o‘qituvchi', 'Преподаватель-стажер', 'محاضر متدرب'),
            'architecture-khaitov-sunnat-istamovich' => $this->staffItem('Khaitov Sunnat Istamovich', 'Хаитов Суннат Истамович', 'خايتوف سونات إستاموفيتش', 'Instructor', 'O‘qituvchi', 'Преподаватель', 'مدرس'),
        ];
    }

    private function staffItem(string $enName, string $ruName, string $arName, string $enPosition, string $uzPosition, string $ruPosition, string $arPosition, ?string $slug = null): array
    {
        return [
            'slug' => $slug,
            'translations' => [
                'en' => ['name' => $enName, 'position' => $enPosition, 'bio' => "$enName serves as $enPosition in Architecture, contributing to academic, methodological, design, and research development."],
                'uz' => ['name' => $enName, 'position' => $uzPosition, 'bio' => "$enName Arxitektura kafedrasida $uzPosition sifatida ta’lim, metodik, loyiha va ilmiy faoliyatga hissa qo‘shadi."],
                'ru' => ['name' => $ruName, 'position' => $ruPosition, 'bio' => "$ruName работает на кафедре архитектуры в должности: $ruPosition."],
                'ar' => ['name' => $arName, 'position' => $arPosition, 'bio' => "$arName يعمل في قسم العمارة بصفة $arPosition ويساهم في التطوير الأكاديمي والمنهجي والتصميمي والبحثي."],
            ],
        ];
    }
};
