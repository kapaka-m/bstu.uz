<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            foreach ($this->departments() as $slug => $department) {
                $departmentRecord = DB::table('departments')->where('slug', $slug)->first(['id', 'head_name', 'phone', 'email', 'reception_time']);

                if (! $departmentRecord) {
                    continue;
                }

                foreach ($department['translations'] as $locale => $translation) {
                    DB::table('department_translations')->updateOrInsert(
                        ['department_id' => $departmentRecord->id, 'locale' => $locale],
                        [
                            'name' => $translation['name'],
                            'short_name' => $translation['short_name'],
                            'description' => $translation['description'],
                            'content_sections' => json_encode(
                                $this->sections($translation, $departmentRecord),
                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                            ),
                            'meta_title' => $translation['name'],
                            'meta_description' => $translation['description'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                }
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function sections(array $translation, object $department): array
    {
        return [
            ['key' => 'overview', 'title' => $translation['overview_title'], 'items' => [$translation['description']]],
            ['key' => 'subjects', 'title' => $translation['subjects_title'], 'items' => $translation['subjects']],
            ['key' => 'prepared_specialists', 'title' => $translation['programs_title'], 'items' => $translation['programs']],
            ['key' => 'research', 'title' => $translation['research_title'], 'items' => $translation['research']],
            ['key' => 'cooperation', 'title' => $translation['cooperation_title'], 'items' => $translation['cooperation']],
            [
                'key' => 'department_structure',
                'title' => $translation['structure_title'],
                'items' => [
                    $translation['head_label'].': '.$department->head_name,
                    $translation['office_label'].': '.($department->reception_time ?: '-'),
                    $translation['phone_label'].': '.($department->phone ?: '-'),
                    $translation['email_label'].': '.($department->email ?: '-'),
                ],
            ],
        ];
    }

    private function departments(): array
    {
        return [
            'oil-gas-refining-technology' => $this->department(
                ['Oil and Gas Refining Technology', 'Neft va gazni qayta ishlash texnologiyasi', 'Технология переработки нефти и газа', 'تكنولوجيا تكرير النفط والغاز'],
                ['60720500 - Light Industry Production Technology', '60720600 - Oil and Oil-Gas Processing Technology'],
                [
                    ['Oil and gas processing fundamentals', 'Petroleum chemistry', 'Refining processes and equipment', 'Gas processing technology', 'Petrochemical products', 'Catalysis and corrosion protection', 'Industrial safety', 'Laboratory practice and industrial training'],
                    ['Neft va gazni qayta ishlash asoslari', 'Neft kimyosi', 'Qayta ishlash jarayonlari va jihozlari', 'Gazni qayta ishlash texnologiyasi', 'Neft-kimyo mahsulotlari', 'Kataliz va korroziyadan himoya', 'Sanoat xavfsizligi', 'Laboratoriya va ishlab chiqarish amaliyoti'],
                    ['Основы переработки нефти и газа', 'Нефтяная химия', 'Процессы и оборудование переработки', 'Технология переработки газа', 'Нефтехимические продукты', 'Катализ и защита от коррозии', 'Промышленная безопасность', 'Лабораторная и производственная практика'],
                    ['أساسيات معالجة النفط والغاز', 'كيمياء النفط', 'عمليات ومعدات التكرير', 'تكنولوجيا معالجة الغاز', 'المنتجات البتروكيميائية', 'الحفز والحماية من التآكل', 'السلامة الصناعية', 'التدريب المخبري والصناعي'],
                ],
                [
                    ['Improvement of oil and gas refining processes', 'Energy-saving technologies in petrochemical production', 'Catalysis, corrosion protection, and product quality research'],
                    ['Neft va gazni qayta ishlash jarayonlarini takomillashtirish', 'Neft-kimyo ishlab chiqarishida energiya tejamkor texnologiyalar', 'Kataliz, korroziyadan himoya va mahsulot sifati bo‘yicha tadqiqotlar'],
                    ['Совершенствование процессов переработки нефти и газа', 'Энергосберегающие технологии в нефтехимическом производстве', 'Исследования катализа, защиты от коррозии и качества продукции'],
                    ['تطوير عمليات تكرير النفط والغاز', 'تقنيات ترشيد الطاقة في الإنتاج البتروكيميائي', 'أبحاث الحفز والحماية من التآكل وجودة المنتجات'],
                ],
                [
                    ['Baku Higher Oil School', 'SOCAR cooperation', 'Atyrau Oil and Gas University', 'Ufa State Petroleum Technological University', 'M. Auezov South Kazakhstan University'],
                    ['Baku Higher Oil School', 'SOCAR hamkorligi', 'Atyrau Oil and Gas University', 'Ufa State Petroleum Technological University', 'M. Auezov South Kazakhstan University'],
                    ['Бакинская высшая школа нефти', 'Сотрудничество с SOCAR', 'Атырауский университет нефти и газа', 'Уфимский государственный нефтяной технический университет', 'Южно-Казахстанский университет им. М. Ауэзова'],
                    ['مدرسة باكو العليا للنفط', 'التعاون مع SOCAR', 'جامعة أتيراو للنفط والغاز', 'جامعة أوفا الحكومية التقنية للبترول', 'جامعة جنوب كازاخستان باسم م. أويزوف'],
                ],
            ),
            'food-technology-service' => $this->department(
                ['Food Technology and Service', 'Oziq-ovqat texnologiyasi va servis', 'Пищевая технология и сервис', 'تكنولوجيا الأغذية والخدمات'],
                ['60720100 - Food Technology'],
                [
                    ['Food production technology', 'Food chemistry and microbiology', 'Bakery and confectionery technology', 'Food safety and quality control', 'Packaging and storage', 'Service organization', 'Laboratory analysis', 'Industrial practice'],
                    ['Oziq-ovqat ishlab chiqarish texnologiyasi', 'Oziq-ovqat kimyosi va mikrobiologiyasi', 'Non va qandolat mahsulotlari texnologiyasi', 'Oziq-ovqat xavfsizligi va sifat nazorati', 'Qadoqlash va saqlash', 'Servisni tashkil etish', 'Laboratoriya tahlili', 'Ishlab chiqarish amaliyoti'],
                    ['Технология пищевого производства', 'Пищевая химия и микробиология', 'Технология хлебобулочных и кондитерских изделий', 'Безопасность пищевых продуктов и контроль качества', 'Упаковка и хранение', 'Организация сервиса', 'Лабораторный анализ', 'Производственная практика'],
                    ['تكنولوجيا إنتاج الأغذية', 'كيمياء وميكروبيولوجيا الأغذية', 'تكنولوجيا المخبوزات والحلويات', 'سلامة الأغذية ومراقبة الجودة', 'التعبئة والتخزين', 'تنظيم الخدمات', 'التحليل المخبري', 'التدريب الصناعي'],
                ],
                [
                    ['Functional food product development', 'Food quality improvement and safety research', 'Laboratory modernization through international projects'],
                    ['Funksional oziq-ovqat mahsulotlarini ishlab chiqish', 'Oziq-ovqat sifati va xavfsizligini yaxshilash bo‘yicha tadqiqotlar', 'Xalqaro loyihalar orqali laboratoriyalarni modernizatsiya qilish'],
                    ['Разработка функциональных пищевых продуктов', 'Исследования качества и безопасности пищевых продуктов', 'Модернизация лабораторий через международные проекты'],
                    ['تطوير المنتجات الغذائية الوظيفية', 'أبحاث تحسين جودة وسلامة الأغذية', 'تحديث المختبرات عبر المشروعات الدولية'],
                ],
                [
                    ['Dresden University of Technology', 'Moscow State University of Food Production', 'Mogilev State University of Food Technologies', 'Latvia University of Life Sciences and Technologies'],
                    ['Dresden University of Technology', 'Moscow State University of Food Production', 'Mogilev State University of Food Technologies', 'Latvia University of Life Sciences and Technologies'],
                    ['Дрезденский технический университет', 'Московский государственный университет пищевых производств', 'Могилевский государственный университет продовольствия', 'Латвийский университет бионаук и технологий'],
                    ['جامعة دريسدن التقنية', 'جامعة موسكو الحكومية لإنتاج الأغذية', 'جامعة موغيليف الحكومية لتكنولوجيا الأغذية', 'جامعة لاتفيا لعلوم الحياة والتقنيات'],
                ],
            ),
            'chemical-technology' => $this->department(
                ['Chemical Technology', 'Kimyoviy texnologiya', 'Химическая технология', 'التكنولوجيا الكيميائية'],
                ['60520200 - Ecology and Environmental Protection', '60710100 - Chemical Engineering', '60710200 - Biotechnology'],
                [
                    ['General and inorganic chemistry', 'Organic chemistry', 'Chemical process technology', 'Colloid and membrane chemistry', 'Polymer materials', 'Biotechnology fundamentals', 'Environmental protection', 'Laboratory and industrial practice'],
                    ['Umumiy va noorganik kimyo', 'Organik kimyo', 'Kimyoviy jarayonlar texnologiyasi', 'Kolloid va membrana kimyosi', 'Polimer materiallar', 'Biotexnologiya asoslari', 'Atrof-muhit muhofazasi', 'Laboratoriya va ishlab chiqarish amaliyoti'],
                    ['Общая и неорганическая химия', 'Органическая химия', 'Технология химических процессов', 'Коллоидная и мембранная химия', 'Полимерные материалы', 'Основы биотехнологии', 'Охрана окружающей среды', 'Лабораторная и производственная практика'],
                    ['الكيمياء العامة وغير العضوية', 'الكيمياء العضوية', 'تكنولوجيا العمليات الكيميائية', 'الكيمياء الغروية والغشائية', 'المواد البوليمرية', 'أساسيات التكنولوجيا الحيوية', 'حماية البيئة', 'التدريب المخبري والصناعي'],
                ],
                [
                    ['Research in colloid, membrane, inorganic, organic, and polymer chemistry', 'Development of innovative chemical technologies', 'Applied research for industrial and environmental needs'],
                    ['Kolloid, membrana, noorganik, organik va polimer kimyosi bo‘yicha tadqiqotlar', 'Innovatsion kimyoviy texnologiyalarni ishlab chiqish', 'Sanoat va ekologik ehtiyojlar uchun amaliy tadqiqotlar'],
                    ['Исследования в области коллоидной, мембранной, неорганической, органической и полимерной химии', 'Разработка инновационных химических технологий', 'Прикладные исследования для промышленности и экологии'],
                    ['أبحاث في الكيمياء الغروية والغشائية وغير العضوية والعضوية والبوليمرية', 'تطوير تقنيات كيميائية مبتكرة', 'أبحاث تطبيقية لخدمة الصناعة والبيئة'],
                ],
                [
                    ['Beijing University of Chemical Technology', 'Cyprus International University', 'Universiti Putra Malaysia', 'Perm National Research Polytechnic University', 'M. Auezov South Kazakhstan University'],
                    ['Beijing University of Chemical Technology', 'Cyprus International University', 'Universiti Putra Malaysia', 'Perm National Research Polytechnic University', 'M. Auezov South Kazakhstan University'],
                    ['Пекинский университет химической технологии', 'Кипрский международный университет', 'Universiti Putra Malaysia', 'Пермский национальный исследовательский политехнический университет', 'Южно-Казахстанский университет им. М. Ауэзова'],
                    ['جامعة بكين للتكنولوجيا الكيميائية', 'جامعة قبرص الدولية', 'جامعة بوترا ماليزيا', 'جامعة بيرم الوطنية البحثية متعددة التقنيات', 'جامعة جنوب كازاخستان باسم م. أويزوف'],
                ],
            ),
            'agricultural-products-storage-oil-fat-technology' => $this->department(
                ['Agricultural Products Storage and Oil-Fat Technology', 'Qishloq xo‘jaligi mahsulotlarini saqlash va moy-yog‘ texnologiyasi', 'Хранение сельхозпродукции и масложировая технология', 'تخزين المنتجات الزراعية وتكنولوجيا الزيوت والدهون'],
                ['60720200 - Perfumery and Cosmetic Products Technology', '60810700 - Technology of Storage and Processing of Agricultural Products', '60810800 - Animal Husbandry Engineering', '60811000 - Fruit-Vegetable Growing and Viticulture', '61010500 - Cosmetology'],
                [
                    ['Storage of agricultural products', 'Processing of agricultural products', 'Oil and fat technology', 'Vegetable oils and catalysts', 'Perfumery and cosmetic products', 'Fruit and vegetable processing', 'Quality control and certification', 'Laboratory and production practice'],
                    ['Qishloq xo‘jaligi mahsulotlarini saqlash', 'Qishloq xo‘jaligi mahsulotlarini qayta ishlash', 'Moy-yog‘ texnologiyasi', 'O‘simlik moylari va katalizatorlar', 'Parfyumeriya va kosmetika mahsulotlari', 'Meva-sabzavotlarni qayta ishlash', 'Sifat nazorati va sertifikatlash', 'Laboratoriya va ishlab chiqarish amaliyoti'],
                    ['Хранение сельскохозяйственной продукции', 'Переработка сельскохозяйственной продукции', 'Масложировая технология', 'Растительные масла и катализаторы', 'Парфюмерно-косметическая продукция', 'Переработка фруктов и овощей', 'Контроль качества и сертификация', 'Лабораторная и производственная практика'],
                    ['تخزين المنتجات الزراعية', 'معالجة المنتجات الزراعية', 'تكنولوجيا الزيوت والدهون', 'الزيوت النباتية والعوامل الحفازة', 'منتجات العطور ومستحضرات التجميل', 'تصنيع الفواكه والخضروات', 'مراقبة الجودة والاعتماد', 'التدريب المخبري والإنتاجي'],
                ],
                [
                    ['Research on oil-fat technology, catalysts, vegetable oils, and functional products', 'Quality improvement for agricultural products and processing chains', 'Scientific publication activity in international indexed journals'],
                    ['Moy-yog‘ texnologiyasi, katalizatorlar, o‘simlik moylari va funksional mahsulotlar bo‘yicha tadqiqotlar', 'Qishloq xo‘jaligi mahsulotlari va qayta ishlash zanjirlarida sifatni yaxshilash', 'Xalqaro indekslangan jurnallarda ilmiy nashrlar'],
                    ['Исследования масложировой технологии, катализаторов, растительных масел и функциональных продуктов', 'Повышение качества сельхозпродукции и технологических цепочек переработки', 'Научные публикации в международных индексируемых журналах'],
                    ['أبحاث تكنولوجيا الزيوت والدهون والعوامل الحفازة والزيوت النباتية والمنتجات الوظيفية', 'تحسين جودة المنتجات الزراعية وسلاسل المعالجة', 'النشر العلمي في المجلات الدولية المفهرسة'],
                ],
                [
                    ['Al-Farabi Kazakh National University', 'Karaganda Technical University', 'M. Auezov South Kazakhstan University', 'University of Copenhagen', 'University of Extremadura'],
                    ['Al-Farabi Kazakh National University', 'Karaganda Technical University', 'M. Auezov South Kazakhstan University', 'University of Copenhagen', 'University of Extremadura'],
                    ['Казахский национальный университет им. Аль-Фараби', 'Карагандинский технический университет', 'Южно-Казахстанский университет им. М. Ауэзова', 'Копенгагенский университет', 'Университет Эстремадуры'],
                    ['جامعة الفارابي الوطنية الكازاخية', 'جامعة كاراغاندا التقنية', 'جامعة جنوب كازاخستان باسم م. أويزوف', 'جامعة كوبنهاغن', 'جامعة إكستريمادورا'],
                ],
            ),
            'oil-gas-engineering-upstream-downstream' => $this->department(
                ['Oil and Gas Engineering', 'Neft va gaz ishi', 'Нефтегазовое дело', 'هندسة النفط والغاز'],
                ['60720900 - Geology, Prospecting and Exploration of Mineral Resources', '60721100 - Oil and Gas Engineering'],
                [
                    ['Oil and gas field development', 'Drilling and production technology', 'Geology and exploration', 'Reservoir engineering', 'Oil and gas equipment operation', 'Pipeline systems', 'Industrial safety', 'Field practice and graduation project'],
                    ['Neft va gaz konlarini ishlatish', 'Burg‘ilash va qazib olish texnologiyasi', 'Geologiya va qidiruv ishlari', 'Qatlam muhandisligi', 'Neft-gaz jihozlarini ekspluatatsiya qilish', 'Quvur tizimlari', 'Sanoat xavfsizligi', 'Dala amaliyoti va bitiruv loyihasi'],
                    ['Разработка нефтяных и газовых месторождений', 'Технология бурения и добычи', 'Геология и разведка', 'Пластовая инженерия', 'Эксплуатация нефтегазового оборудования', 'Трубопроводные системы', 'Промышленная безопасность', 'Полевая практика и выпускной проект'],
                    ['تطوير حقول النفط والغاز', 'تقنيات الحفر والإنتاج', 'الجيولوجيا والاستكشاف', 'هندسة المكامن', 'تشغيل معدات النفط والغاز', 'أنظمة خطوط الأنابيب', 'السلامة الصناعية', 'التدريب الميداني ومشروع التخرج'],
                ],
                [
                    ['Research on oil emulsions, local clays, gas odorants, and field equipment', 'Applied studies for extraction, operation, and production efficiency', 'Publications in national, international, Scopus, and Web of Science indexed sources'],
                    ['Neft emulsiyalari, mahalliy gillari, gaz odorantlari va kon jihozlari bo‘yicha tadqiqotlar', 'Qazib olish, ekspluatatsiya va ishlab chiqarish samaradorligi uchun amaliy tadqiqotlar', 'Milliy, xalqaro, Scopus va Web of Science manbalaridagi nashrlar'],
                    ['Исследования нефтяных эмульсий, местных глин, газовых одорантов и промыслового оборудования', 'Прикладные исследования для повышения эффективности добычи и эксплуатации', 'Публикации в национальных, международных, Scopus и Web of Science источниках'],
                    ['أبحاث مستحلبات النفط والطين المحلي ومواد تعطير الغاز ومعدات الحقول', 'دراسات تطبيقية لتحسين الاستخراج والتشغيل وكفاءة الإنتاج', 'منشورات في مصادر وطنية ودولية ومفهرسة في Scopus وWeb of Science'],
                ],
                [
                    ['Baku Higher Oil School', 'Gubkin Russian State University of Oil and Gas', 'M. Auezov South Kazakhstan University', 'UAE University', 'Ukhta State Technical University'],
                    ['Baku Higher Oil School', 'Gubkin Russian State University of Oil and Gas', 'M. Auezov South Kazakhstan University', 'UAE University', 'Ukhta State Technical University'],
                    ['Бакинская высшая школа нефти', 'Российский государственный университет нефти и газа имени И.М. Губкина', 'Южно-Казахстанский университет им. М. Ауэзова', 'Университет ОАЭ', 'Ухтинский государственный технический университет'],
                    ['مدرسة باكو العليا للنفط', 'جامعة غوبكين الروسية الحكومية للنفط والغاز', 'جامعة جنوب كازاخستان باسم م. أويزوف', 'جامعة الإمارات', 'جامعة أوختا الحكومية التقنية'],
                ],
            ),
            'metrology-standardization-quality-control' => $this->department(
                ['Metrology and Standardization', 'Metrologiya va standartlashtirish', 'Метрология и стандартизация', 'المترولوجيا والتقييس'],
                ['60710800 - Metrology and Standardization', '61020200 - Occupational Health and Safety'],
                [
                    ['Metrology fundamentals', 'Standardization and certification', 'Measurement systems', 'Quality management', 'Product quality control', 'Technical regulation', 'Occupational safety', 'Laboratory practice and calibration'],
                    ['Metrologiya asoslari', 'Standartlashtirish va sertifikatlash', 'O‘lchash tizimlari', 'Sifat menejmenti', 'Mahsulot sifatini nazorat qilish', 'Texnik tartibga solish', 'Mehnat muhofazasi', 'Laboratoriya amaliyoti va kalibrlash'],
                    ['Основы метрологии', 'Стандартизация и сертификация', 'Измерительные системы', 'Менеджмент качества', 'Контроль качества продукции', 'Техническое регулирование', 'Охрана труда', 'Лабораторная практика и калибровка'],
                    ['أساسيات المترولوجيا', 'التقييس والاعتماد', 'أنظمة القياس', 'إدارة الجودة', 'مراقبة جودة المنتجات', 'التنظيم الفني', 'السلامة المهنية', 'التدريب المخبري والمعايرة'],
                ],
                [
                    ['Research on quality process control, drying processes, confectionery quality, and measurement reliability', 'Development of metrology, standardization, certification, and modern measurement systems', 'Applied studies supporting product safety and industrial quality'],
                    ['Sifat jarayonlarini nazorat qilish, quritish jarayonlari, qandolat sifati va o‘lchash ishonchliligi bo‘yicha tadqiqotlar', 'Metrologiya, standartlashtirish, sertifikatlash va zamonaviy o‘lchash tizimlarini rivojlantirish', 'Mahsulot xavfsizligi va sanoat sifatini qo‘llab-quvvatlovchi amaliy tadqiqotlar'],
                    ['Исследования контроля процессов качества, процессов сушки, качества кондитерских изделий и надежности измерений', 'Развитие метрологии, стандартизации, сертификации и современных измерительных систем', 'Прикладные исследования для безопасности продукции и промышленного качества'],
                    ['أبحاث مراقبة عمليات الجودة وعمليات التجفيف وجودة الحلويات وموثوقية القياس', 'تطوير المترولوجيا والتقييس والاعتماد وأنظمة القياس الحديثة', 'دراسات تطبيقية تدعم سلامة المنتجات وجودة الصناعة'],
                ],
                [
                    ['M. Auezov South Kazakhstan University', 'Al-Farabi Kazakh National University'],
                    ['M. Auezov South Kazakhstan University', 'Al-Farabi Kazakh National University'],
                    ['Южно-Казахстанский университет им. М. Ауэзова', 'Казахский национальный университет им. Аль-Фараби'],
                    ['جامعة جنوب كازاخستان باسم م. أويزوف', 'جامعة الفارابي الوطنية الكازاخية'],
                ],
            ),
        ];
    }

    private function department(array $names, array $programs, array $subjects, array $research, array $cooperation): array
    {
        $locales = ['en', 'uz', 'ru', 'ar'];
        $translations = [];

        foreach ($locales as $index => $locale) {
            $translations[$locale] = [
                'name' => $names[$index],
                'short_name' => $names[$index],
                'description' => $this->description($names[$index], $locale),
                'overview_title' => ['en' => 'About the Department', 'uz' => 'Kafedra haqida', 'ru' => 'О кафедре', 'ar' => 'عن القسم'][$locale],
                'subjects_title' => ['en' => 'Taught Subjects', 'uz' => 'O‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد الدراسية'][$locale],
                'programs_title' => ['en' => 'Programs and Specializations', 'uz' => 'Dasturlar va mutaxassisliklar', 'ru' => 'Программы и специализации', 'ar' => 'البرامج والتخصصات'][$locale],
                'research_title' => ['en' => 'Research Work', 'uz' => 'Ilmiy ishlar', 'ru' => 'Научная работа', 'ar' => 'الأعمال البحثية'][$locale],
                'cooperation_title' => ['en' => 'International Cooperation', 'uz' => 'Xalqaro hamkorlik', 'ru' => 'Международное сотрудничество', 'ar' => 'التعاون الدولي'][$locale],
                'structure_title' => ['en' => 'Department Structure', 'uz' => 'Kafedra tuzilmasi', 'ru' => 'Структура кафедры', 'ar' => 'هيكل القسم'][$locale],
                'head_label' => ['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'][$locale],
                'office_label' => ['en' => 'Office hours', 'uz' => 'Qabul vaqti', 'ru' => 'Время приема', 'ar' => 'ساعات الاستقبال'][$locale],
                'phone_label' => ['en' => 'Phone', 'uz' => 'Telefon', 'ru' => 'Телефон', 'ar' => 'الهاتف'][$locale],
                'email_label' => ['en' => 'Email', 'uz' => 'Email', 'ru' => 'Email', 'ar' => 'البريد الإلكتروني'][$locale],
                'programs' => $programs,
                'subjects' => $subjects[$index],
                'research' => $research[$index],
                'cooperation' => $cooperation[$index],
            ];
        }

        return ['translations' => $translations];
    }

    private function description(string $name, string $locale): string
    {
        return match ($locale) {
            'uz' => $name.' kafedrasi nazariy taʼlim, laboratoriya tayyorgarligi, amaliy loyihalar, ishlab chiqarish amaliyoti va soha hamkorligini birlashtiradi.',
            'ru' => 'Кафедра '.$name.' объединяет теоретическое обучение, лабораторную подготовку, практические проекты, производственную практику и отраслевое сотрудничество.',
            'ar' => 'يربط قسم '.$name.' التعليم النظري والتدريب المخبري والمشروعات التطبيقية والتدريب الصناعي والتعاون مع القطاع.',
            default => $name.' combines theoretical education, laboratory training, practical projects, industrial practice, and cooperation with industry partners.',
        };
    }
};
