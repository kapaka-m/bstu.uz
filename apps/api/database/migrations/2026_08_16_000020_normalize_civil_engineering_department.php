<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'civil-engineering')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->deleteObsoleteStaff($departmentId);
            $this->normalizeDepartmentSections($departmentId);
            $this->normalizeStaff($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function deleteObsoleteStaff(int $departmentId): void
    {
        $slugs = [
            'civil-engineering-0-prof-zoir-s-toshov',
            'civil-engineering-1-dr-elmurod-a-nazarov',
            'civil-engineering-2-zuhra-k-saidova',
            'civil-engineering-3-rustam-b-karimov',
            'civil-engineering-lecturer-trainee',
            'civil-engineering-doctoral-student',
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
                'name' => 'Civil Engineering',
                'short_name' => 'Civil Engineering',
                'description' => 'The Department of Civil Engineering prepares specialists in construction design, engineering communications, road engineering, building materials, and safe operation of infrastructure.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'About the Department', 'items' => ['The department combines engineering theory, structural analysis, laboratory testing, design practice, field work, and cooperation with construction and infrastructure organizations.']],
                    ['key' => 'subjects', 'title' => 'Taught Subjects', 'items' => [
                        'Engineering mechanics and structural analysis',
                        'Building materials and construction products',
                        'Reinforced concrete and steel structures',
                        'Foundations and soil mechanics',
                        'Construction technology and organization',
                        'Engineering communications and utility networks',
                        'Road engineering and transport infrastructure',
                        'Building information modeling and CAD',
                        'Construction safety and quality control',
                        'Industrial practice and graduation project',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Programs and Specializations', 'items' => ['60730300 - Civil Engineering', '60730400 - Construction and Operation of Engineering Communications', '60730500 - Road Engineering', '60731100 - Production of Construction Materials, Products and Structures']],
                    ['key' => 'research', 'title' => 'Research Work', 'items' => [
                        'Improving durability, safety, and energy efficiency of buildings and structures.',
                        'Research on modern construction materials, products, and engineering communications.',
                        'Development of practical solutions for road infrastructure, design, construction quality, and maintenance.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'International Cooperation', 'items' => ['The department cooperates with construction enterprises, design organizations, infrastructure companies, research institutions, and partner universities.']],
                    ['key' => 'department_structure', 'title' => 'Department Structure', 'items' => ["Head of Department: Tojiyev In'omjon Ilhomovich", 'Office hours: Monday-Friday 14:00-16:00', 'Phone: +998 91 444 87 03', 'Email: arminom@mail.ru']],
                ],
            ],
            'uz' => [
                'name' => 'Qurilish muhandisligi kafedrasi',
                'short_name' => 'Qurilish muhandisligi kafedrasi',
                'description' => 'Qurilish muhandisligi kafedrasi qurilish loyihalash, muhandislik kommunikatsiyalari, yo‘l muhandisligi, qurilish materiallari va infratuzilmani xavfsiz ishlatish bo‘yicha mutaxassislar tayyorlaydi.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'Kafedra haqida', 'items' => ['Kafedra muhandislik nazariyasi, konstruksiyalar tahlili, laboratoriya sinovlari, loyiha amaliyoti, dala ishlari hamda qurilish va infratuzilma tashkilotlari bilan hamkorlikni uyg‘unlashtiradi.']],
                    ['key' => 'subjects', 'title' => 'O‘qitiladigan fanlar', 'items' => [
                        'Muhandislik mexanikasi va konstruksiyalar tahlili',
                        'Qurilish materiallari va buyumlari',
                        'Temir-beton va po‘lat konstruksiyalar',
                        'Poydevorlar va gruntlar mexanikasi',
                        'Qurilish texnologiyasi va tashkil etish',
                        'Muhandislik kommunikatsiyalari va kommunal tarmoqlar',
                        'Yo‘l muhandisligi va transport infratuzilmasi',
                        'BIM va CAD texnologiyalari',
                        'Qurilish xavfsizligi va sifat nazorati',
                        'Ishlab chiqarish amaliyoti va bitiruv loyihasi',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Dasturlar va mutaxassisliklar', 'items' => ['60730300 - Qurilish muhandisligi', '60730400 - Muhandislik kommunikatsiyalarini qurish va ekspluatatsiya qilish', '60730500 - Yo‘l muhandisligi', '60731100 - Qurilish materiallari, buyumlari va konstruksiyalarini ishlab chiqarish']],
                    ['key' => 'research', 'title' => 'Ilmiy ishlar', 'items' => [
                        'Bino va inshootlarning chidamliligi, xavfsizligi va energiya samaradorligini oshirish.',
                        'Zamonaviy qurilish materiallari, buyumlari va muhandislik kommunikatsiyalari bo‘yicha tadqiqotlar.',
                        'Yo‘l infratuzilmasi, loyihalash, qurilish sifati va texnik xizmat ko‘rsatish bo‘yicha amaliy yechimlarni rivojlantirish.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Xalqaro hamkorlik', 'items' => ['Kafedra qurilish korxonalari, loyiha tashkilotlari, infratuzilma kompaniyalari, ilmiy muassasalar va hamkor universitetlar bilan hamkorlik qiladi.']],
                    ['key' => 'department_structure', 'title' => 'Kafedra tuzilmasi', 'items' => ["Kafedra mudiri: Tojiyev In'omjon Ilhomovich", 'Qabul vaqti: Dushanba-juma 14:00-16:00', 'Telefon: +998 91 444 87 03', 'Elektron pochta: arminom@mail.ru']],
                ],
            ],
            'ru' => [
                'name' => 'Кафедра гражданского строительства',
                'short_name' => 'Кафедра гражданского строительства',
                'description' => 'Кафедра гражданского строительства готовит специалистов в области строительного проектирования, инженерных коммуникаций, дорожной инженерии, строительных материалов и безопасной эксплуатации инфраструктуры.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'О кафедре', 'items' => ['Кафедра объединяет инженерную теорию, расчет конструкций, лабораторные испытания, проектную практику, полевые работы и сотрудничество со строительными и инфраструктурными организациями.']],
                    ['key' => 'subjects', 'title' => 'Учебные дисциплины', 'items' => [
                        'Инженерная механика и расчет конструкций',
                        'Строительные материалы и изделия',
                        'Железобетонные и стальные конструкции',
                        'Фундаменты и механика грунтов',
                        'Технология и организация строительства',
                        'Инженерные коммуникации и коммунальные сети',
                        'Дорожная инженерия и транспортная инфраструктура',
                        'BIM и CAD технологии',
                        'Безопасность строительства и контроль качества',
                        'Производственная практика и выпускной проект',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Программы и специализации', 'items' => ['60730300 - Гражданское строительство', '60730400 - Строительство и эксплуатация инженерных коммуникаций', '60730500 - Дорожная инженерия', '60731100 - Производство строительных материалов, изделий и конструкций']],
                    ['key' => 'research', 'title' => 'Научная работа', 'items' => [
                        'Повышение долговечности, безопасности и энергоэффективности зданий и сооружений.',
                        'Исследования современных строительных материалов, изделий и инженерных коммуникаций.',
                        'Развитие практических решений для дорожной инфраструктуры, проектирования, качества строительства и обслуживания.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Международное сотрудничество', 'items' => ['Кафедра сотрудничает со строительными предприятиями, проектными организациями, инфраструктурными компаниями, научными учреждениями и партнерскими университетами.']],
                    ['key' => 'department_structure', 'title' => 'Структура кафедры', 'items' => ['Заведующий кафедрой: Тожиев Иномжон Илхомович', 'Часы приема: понедельник-пятница 14:00-16:00', 'Телефон: +998 91 444 87 03', 'Электронная почта: arminom@mail.ru']],
                ],
            ],
            'ar' => [
                'name' => 'قسم الهندسة المدنية',
                'short_name' => 'قسم الهندسة المدنية',
                'description' => 'يعد قسم الهندسة المدنية متخصصين في التصميم الإنشائي والاتصالات الهندسية وهندسة الطرق ومواد البناء والتشغيل الآمن للبنية التحتية.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'عن القسم', 'items' => ['يجمع القسم بين النظرية الهندسية والتحليل الإنشائي والاختبارات المخبرية والتطبيق التصميمي والعمل الميداني والتعاون مع مؤسسات البناء والبنية التحتية.']],
                    ['key' => 'subjects', 'title' => 'المواد التي تدرس في القسم', 'items' => [
                        'الميكانيكا الهندسية والتحليل الإنشائي',
                        'مواد ومنتجات البناء',
                        'الهياكل الخرسانية المسلحة والفولاذية',
                        'الأساسات وميكانيكا التربة',
                        'تقنية وتنظيم أعمال البناء',
                        'الاتصالات الهندسية وشبكات المرافق',
                        'هندسة الطرق والبنية التحتية للنقل',
                        'تقنيات BIM و CAD',
                        'سلامة البناء ومراقبة الجودة',
                        'التدريب الصناعي ومشروع التخرج',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'البرامج والتخصصات', 'items' => ['60730300 - الهندسة المدنية', '60730400 - إنشاء وتشغيل الاتصالات الهندسية', '60730500 - هندسة الطرق', '60731100 - إنتاج مواد ومنتجات وهياكل البناء']],
                    ['key' => 'research', 'title' => 'الأعمال البحثية', 'items' => [
                        'تحسين متانة وسلامة وكفاءة الطاقة في المباني والمنشآت.',
                        'أبحاث في مواد ومنتجات البناء الحديثة والاتصالات الهندسية.',
                        'تطوير حلول عملية للبنية التحتية للطرق والتصميم وجودة البناء والصيانة.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'التعاون الدولي', 'items' => ['يتعاون القسم مع شركات البناء ومؤسسات التصميم وشركات البنية التحتية والمؤسسات البحثية والجامعات الشريكة.']],
                    ['key' => 'department_structure', 'title' => 'هيكل القسم', 'items' => ['رئيس القسم: توجييف إنعام جون إلهوموفيتش', 'ساعات الاستقبال: الاثنين-الجمعة 14:00-16:00', 'الهاتف: +998 91 444 87 03', 'البريد الإلكتروني: arminom@mail.ru']],
                ],
            ],
        ];
    }

    private function staff(): array
    {
        return [
            'civil-engineering-inomjon-ilhomovich-tojiyev' => $this->staffItem("Tojiyev In'omjon Ilhomovich", 'Тожиев Иномжон Илхомович', 'توجييف إنعام جون إلهوموفيتش', 'Head of Department', 'Kafedra mudiri', 'Заведующий кафедрой', 'رئيس القسم'),
            'civil-engineering-vokhitov-mubin-muminovich' => $this->staffItem('Vokhitov Mubin Muminovich', 'Вохитов Мубин Муминович', 'فوخيتوف موبين مومينوفيتش', 'Doctor of Technical Sciences, Professor', 'Texnika fanlari doktori, professor', 'Доктор технических наук, профессор', 'دكتور في العلوم التقنية، أستاذ'),
            'civil-engineering-usmanov-farkhad-bafoyevich' => $this->staffItem('Usmanov Farkhad Bafoyevich', 'Усманов Фархад Бафоевич', 'عثمانوف فرخاد بافوييفيتش', 'Candidate of Technical Sciences, Professor', 'Texnika fanlari nomzodi, professor', 'Кандидат технических наук, профессор', 'مرشح في العلوم التقنية، أستاذ'),
            'civil-engineering-boronov-raxmiddin-yozilovich' => $this->staffItem('Bo‘ronov Raxmiddin Yozilovich', 'Буронов Рахмиддин Ёзилович', 'بورونوف رحم الدين يوزيلوفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'civil-engineering-polatov-akram-panoyevich' => $this->staffItem('Po‘latov Akram Panoyevich', 'Пулатов Акрам Паноевич', 'بولاتوف أكرم بانوييفيتش', 'Candidate of Technical Sciences, Associate Professor', 'Texnika fanlari nomzodi, dotsent', 'Кандидат технических наук, доцент', 'مرشح في العلوم التقنية، أستاذ مشارك'),
            'civil-engineering-aslonov-baxtiyor-boboqulovich' => $this->staffItem('Aslonov Baxtiyor Boboqulovich', 'Аслонов Бахтиёр Бобокулович', 'أصلونوف بختيار بوبوقولوفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'civil-engineering-texnika-fanlari-boyicha-falsafa-doktori-phd' => $this->staffItem('Sodiqov Qambarali Shukurovich', 'Содиков Камбарали Шукурович', 'صديقوف قمبرعلي شكوروفيتش', 'PhD in Technical Sciences', 'Texnika fanlari bo‘yicha PhD', 'PhD по техническим наукам', 'دكتوراه في العلوم التقنية', 'civil-engineering-sodiqov-qambarali-shukurovich'),
            'civil-engineering-safarov-uchqun-isroilovich' => $this->staffItem('Safarov Uchqun Isroilovich', 'Сафаров Учкун Исроилович', 'سافاروف أوچقون إسرائلوفيتش', 'PhD in Technical Sciences', 'Texnika fanlari bo‘yicha PhD', 'PhD по техническим наукам', 'دكتوراه في العلوم التقنية'),
            'civil-engineering-senior-lecturer-doctoral-student' => $this->staffItem('Kiyimov Shavkat Fazliddinovich', 'Кийимов Шавкат Фазлиддинович', 'كييموف شوكت فضل الدينوفيتش', 'Senior Lecturer, Doctoral Student', 'Katta o‘qituvchi, doktorant', 'Старший преподаватель, докторант', 'محاضر أول، طالب دكتوراه', 'civil-engineering-kiyimov-shavkat-fazliddinovich'),
            'civil-engineering-yadgarova-gulnora-gulomovna' => $this->staffItem('Yadgarova Gulnora Gulomovna', 'Ядгарова Гулнора Гуломовна', 'يادغاروفا غولنورا غولوموفنا', 'Senior Lecturer, Doctoral Student', 'Katta o‘qituvchi, doktorant', 'Старший преподаватель, докторант', 'محاضرة أولى، طالبة دكتوراه'),
            'civil-engineering-qaxxorov-hamid-ahrorovich' => $this->staffItem('Qaxxorov Hamid Ahrorovich', 'Каххоров Хамид Ахрорович', 'قخخوروف حميد أحروروفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'civil-engineering-gadoeva-olima-pulatovna' => $this->staffItem('Gadoeva Olima Pulatovna', 'Гадоева Олима Пулатовна', 'غادوييفا أوليما بولاتوفنا', 'Senior Lecturer', 'Katta o‘qituvchi', 'Старший преподаватель', 'محاضرة أولى'),
            'civil-engineering-rakhmatov-yakitev-jacob-gafforovich' => $this->staffItem('Rakhmatov Yakitev Jacob Gafforovich', 'Рахматов Якитев Якоб Гаффорович', 'رحمتوف ياكيتيف ياكوب غفوروفيتش', 'Senior Lecturer', 'Katta o‘qituvchi', 'Старший преподаватель', 'محاضر أول'),
            'civil-engineering-ochilova-nurzoda-tursunovna' => $this->staffItem('Ochilova Nurzoda Tursunovna', 'Очилова Нурзода Турсуновна', 'أوتشيلوفا نورزودا تورسونوفنا', 'Senior Lecturer', 'Katta o‘qituvchi', 'Старший преподаватель', 'محاضرة أولى'),
            'civil-engineering-fayzullaeva-zarina-nayimovna' => $this->staffItem('Fayzullaeva Zarina Nayimovna', 'Файзуллаева Зарина Наимовна', 'فايزوللاييفا زارينا نعيموفنا', 'Senior Lecturer', 'Katta o‘qituvchi', 'Старший преподаватель', 'محاضرة أولى'),
            'civil-engineering-imamov-sukhrob-solekhovich' => $this->staffItem('Imamov Sukhrob Solekhovich', 'Имамов Сухроб Солехович', 'إماموف سوخروب صاليحوفيتش', 'Trainee Lecturer', 'Stajyor-o‘qituvchi', 'Преподаватель-стажер', 'محاضر متدرب'),
            'civil-engineering-niyozov-kamol-ergashevich' => $this->staffItem('Niyozov Kamol Ergashevich', 'Ниёзов Камол Эргашевич', 'نيازوف كامول إرغاشيفيتش', 'Assistant', 'Assistent', 'Ассистент', 'مساعد'),
            'civil-engineering-tosheva-dilfuza-farkhodovna' => $this->staffItem('Tosheva Dilfuza Farkhodovna', 'Тошева Дилфуза Фарходовна', 'توشيفا ديلفوزا فرخودوفنا', 'Doctoral Student', 'Doktorant', 'Докторант', 'طالبة دكتوراه'),
            'civil-engineering-sodiqov-mizrob-ayubovich' => $this->staffItem('Sodiqov Mizrob Ayubovich', 'Содиков Мизроб Аюбович', 'صديقوف ميزروب أيوبوفيتش', 'Assistant', 'Assistent', 'Ассистент', 'مساعد'),
            'civil-engineering-tosheva-dilbar-farkhodovna' => $this->staffItem('Tosheva Dilbar Farkhodovna', 'Тошева Дилбар Фарходовна', 'توشيفا ديلبار فرخودوفنا', 'Doctoral Student', 'Doktorant', 'Докторант', 'طالبة دكتوراه'),
        ];
    }

    private function staffItem(string $enName, string $ruName, string $arName, string $enPosition, string $uzPosition, string $ruPosition, string $arPosition, ?string $slug = null): array
    {
        return [
            'slug' => $slug,
            'translations' => [
                'en' => ['name' => $enName, 'position' => $enPosition, 'bio' => "$enName serves as $enPosition in Civil Engineering, contributing to academic, methodological, engineering, and research development."],
                'uz' => ['name' => $enName, 'position' => $uzPosition, 'bio' => "$enName Qurilish muhandisligi kafedrasida $uzPosition sifatida ta’lim, metodik, muhandislik va ilmiy faoliyatga hissa qo‘shadi."],
                'ru' => ['name' => $ruName, 'position' => $ruPosition, 'bio' => "$ruName работает на кафедре гражданского строительства в должности: $ruPosition."],
                'ar' => ['name' => $arName, 'position' => $arPosition, 'bio' => "$arName يعمل في قسم الهندسة المدنية بصفة $arPosition ويساهم في التطوير الأكاديمي والمنهجي والهندسي والبحثي."],
            ],
        ];
    }
};
