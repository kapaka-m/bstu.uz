<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments') || ! Schema::hasTable('department_translations')) {
            return;
        }

        $departmentId = DB::table('departments')
            ->where('slug', 'agricultural-water-resources-engineering-technologies')
            ->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeSections((int) $departmentId);
            $this->normalizeStaff((int) $departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function normalizeSections(int $departmentId): void
    {
        foreach ($this->locales() as $locale) {
            $translation = DB::table('department_translations')
                ->where('department_id', $departmentId)
                ->where('locale', $locale)
                ->first();

            if (! $translation) {
                continue;
            }

            $sections = json_decode((string) $translation->content_sections, true);
            if (! is_array($sections)) {
                $sections = [];
            }

            foreach ([
                'staff' => ['title' => $this->label($locale, 'staff'), 'items' => $this->staffSectionItems($locale)],
                'subjects' => ['title' => $this->label($locale, 'subjects'), 'items' => [$this->subjectsText($locale)]],
                'research' => ['title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)],
                'cooperation' => ['title' => $this->label($locale, 'cooperation'), 'items' => $this->partners()],
                'plans' => ['title' => $this->label($locale, 'plans'), 'items' => $this->plans($locale)],
            ] as $key => $payload) {
                $sections = $this->replaceSection($sections, [
                    'key' => $key,
                    'title' => $payload['title'],
                    'items' => $payload['items'],
                ]);
            }

            DB::table('department_translations')
                ->where('id', $translation->id)
                ->update([
                    'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
        }
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteSlugs = [
            'agricultural-water-resources-engineering-technologies-radjabov-yarash-jabborovich',
            'agricultural-water-resources-engineering-technologies-doctor-of-philosophy-in-technical-sciences-docent',
            'agricultural-water-resources-engineering-technologies-0-dr-alim-s-xamrayev',
            'agricultural-water-resources-engineering-technologies-1-sabohat-a-karimova',
            'agricultural-water-resources-engineering-technologies-2-javohir-k-safarov',
        ];

        $deleteIds = DB::table('staff_profiles')->whereIn('slug', $deleteSlugs)->pluck('id')->all();
        if ($deleteIds !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $deleteIds)->delete();
            DB::table('staff_profiles')->whereIn('id', $deleteIds)->delete();
        }

        foreach ($this->staffProfiles() as $index => $profile) {
            $profileId = DB::table('staff_profiles')->where('slug', $profile['slug'])->value('id');
            if (! $profileId) {
                continue;
            }

            DB::table('staff_profiles')->where('id', $profileId)->update([
                'department_id' => $departmentId,
                'sort_order' => $index + 10,
                'is_active' => true,
                'updated_at' => now(),
            ]);

            foreach ($this->locales() as $locale) {
                $name = $profile['names'][$locale] ?? $profile['names']['en'];
                $position = $this->position($profile['position'], $locale);

                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $name,
                        'position' => $position,
                        'bio' => $this->bio($name, $position, $locale),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function subjectsText(string $locale): string
    {
        return $this->numberedSection($this->degreeLabel($locale, 'bachelor'), $this->bachelorSubjects($locale))
            ."\n".$this->numberedSection($this->degreeLabel($locale, 'master'), $this->masterSubjects($locale));
    }

    private function numberedSection(string $heading, array $items): string
    {
        return $heading.":\n".collect($items)->map(fn (string $item, int $index) => ($index + 1).'. '.$item)->implode("\n");
    }

    private function bachelorSubjects(string $locale): array
    {
        $en = [
            'Fundamentals of Agricultural Engineering',
            'Technology of Livestock Product Production',
            'Reclamation and Construction Machinery',
            'Materials Science and Technology of Structural Materials',
            'Interchangeability, Standardization and Technical Measurement',
            'Mechanization of Agricultural Production',
            'Agricultural Machinery',
            'Construction Machinery',
            'Irrigation Equipment',
            'Tractors and Transport Vehicles',
            'Precision Agriculture',
            'Reclamation Equipment',
            'Fuels, Lubricants and Technical Fluids',
            'Livestock Equipment and Technologies',
            'Comprehensive Mechanization of Irrigation and Reclamation Works',
            'Fundamentals of Machine Reliability and Repair',
            'Technology of Reclamation and Water Management Works',
            'Operation of Reclamation and Water Management Equipment',
            'Use of Machine and Tractor Fleet in Agriculture',
            'Agricultural Technologies and Technical Service',
            'Horticulture and Vegetable Farming Machinery',
            'Innovative Equipment and Technologies and Their Transfer',
            'Modern Equipment and Technologies Used in Water Management',
            'Fundamentals of Animal Husbandry',
            'Fundamentals of Agricultural Engineering',
            'Equipment and Devices for Material Processing',
            'Design of Technical Service and Repair Enterprises',
            'Traffic Rules and Road Safety',
        ];

        return match ($locale) {
            'uz' => [
                'Qishloq xo‘jaligi muhandisligi asoslari',
                'Chorvachilik mahsulotlarini yetishtirish texnologiyasi',
                'Melioratsiya va qurilish mashinalari',
                'Materialshunoslik va konstruksion materiallar texnologiyasi',
                'O‘zaro almashuvchanlik, standartlashtirish va texnik o‘lchash',
                'Qishloq xo‘jaligi ishlab chiqarishini mexanizatsiyalash',
                'Qishloq xo‘jaligi mashinalari',
                'Qurilish mashinalari',
                'Sug‘orish texnikasi',
                'Traktorlar va transport vositalari',
                'Aniq dehqonchilik',
                'Melioratsiya texnikasi',
                'Yoqilg‘i, moylash materiallari va texnik suyuqliklar',
                'Chorvachilik uskunalari va texnologiyalari',
                'Sug‘orish va melioratsiya ishlarini kompleks mexanizatsiyalash',
                'Mashinalar ishonchliligi va ta’mirlash asoslari',
                'Melioratsiya va suv xo‘jaligi ishlari texnologiyasi',
                'Melioratsiya va suv xo‘jaligi texnikalaridan foydalanish',
                'Qishloq xo‘jaligida mashina-traktor parkidan foydalanish',
                'Qishloq xo‘jaligi texnologiyalari va texnik servis',
                'Bog‘dorchilik va sabzavotchilik mashinalari',
                'Innovatsion texnika va texnologiyalar hamda ularni transfer qilish',
                'Suv xo‘jaligida qo‘llaniladigan zamonaviy texnika va texnologiyalar',
                'Chorvachilik asoslari',
                'Qishloq xo‘jaligi muhandisligi asoslari',
                'Materiallarga ishlov berish uskunalari va qurilmalari',
                'Texnik xizmat ko‘rsatish va ta’mirlash korxonalarini loyihalash',
                'Yo‘l harakati qoidalari va harakat xavfsizligi',
            ],
            'ru' => [
                'Основы сельскохозяйственной инженерии',
                'Технология производства продукции животноводства',
                'Мелиоративные и строительные машины',
                'Материаловедение и технология конструкционных материалов',
                'Взаимозаменяемость, стандартизация и технические измерения',
                'Механизация сельскохозяйственного производства',
                'Сельскохозяйственные машины',
                'Строительные машины',
                'Оросительная техника',
                'Тракторы и транспортные средства',
                'Точное земледелие',
                'Мелиоративная техника',
                'Топливо, смазочные материалы и технические жидкости',
                'Оборудование и технологии животноводства',
                'Комплексная механизация ирригационных и мелиоративных работ',
                'Основы надежности и ремонта машин',
                'Технология мелиоративных и водохозяйственных работ',
                'Эксплуатация мелиоративного и водохозяйственного оборудования',
                'Использование машинно-тракторного парка в сельском хозяйстве',
                'Сельскохозяйственные технологии и технический сервис',
                'Машины для садоводства и овощеводства',
                'Инновационная техника и технологии и их трансфер',
                'Современная техника и технологии, применяемые в водном хозяйстве',
                'Основы животноводства',
                'Основы сельскохозяйственной инженерии',
                'Оборудование и устройства для обработки материалов',
                'Проектирование предприятий технического обслуживания и ремонта',
                'Правила дорожного движения и безопасность движения',
            ],
            'ar' => [
                'أساسيات الهندسة الزراعية',
                'تكنولوجيا إنتاج منتجات الثروة الحيوانية',
                'آلات الاستصلاح والبناء',
                'علم المواد وتكنولوجيا المواد الإنشائية',
                'قابلية التبادل والتقييس والقياس الفني',
                'ميكنة الإنتاج الزراعي',
                'الآلات الزراعية',
                'آلات البناء',
                'معدات الري',
                'الجرارات ومركبات النقل',
                'الزراعة الدقيقة',
                'معدات الاستصلاح',
                'الوقود ومواد التشحيم والسوائل الفنية',
                'معدات وتقنيات الثروة الحيوانية',
                'الميكنة الشاملة لأعمال الري والاستصلاح',
                'أساسيات موثوقية الآلات وإصلاحها',
                'تكنولوجيا أعمال الاستصلاح وإدارة المياه',
                'تشغيل معدات الاستصلاح وإدارة المياه',
                'استخدام أسطول الآلات والجرارات في الزراعة',
                'التقنيات الزراعية والخدمة الفنية',
                'آلات البستنة وزراعة الخضروات',
                'المعدات والتقنيات المبتكرة ونقلها',
                'المعدات والتقنيات الحديثة المستخدمة في إدارة المياه',
                'أساسيات تربية الحيوانات',
                'أساسيات الهندسة الزراعية',
                'معدات وأجهزة معالجة المواد',
                'تصميم مؤسسات الخدمة الفنية والإصلاح',
                'قواعد المرور والسلامة المرورية',
            ],
            default => $en,
        };
    }

    private function masterSubjects(string $locale): array
    {
        $en = [
            'Research Work and State Attestation Commission',
            'Reclamation Dredging Vessels and Equipment',
            'Scientific Foundations of Reclamation Construction Organization',
            'Engineering Logistics and Modeling',
            'Research Works',
            'Theoretical Foundations of Reclamation Machine Design',
            'Scientific and Pedagogical Work and Scientific Seminar',
            'Fundamentals of Technical Servicing of Reclamation Construction Machines',
            'Fundamentals of Technological Machine Design',
            'Theoretical Foundations of Irrigation Machine Design',
            'Scientific Foundations of Technical Standardization',
            'Fundamentals of Developing Resource Estimate Norms (ShNQ.IKN)',
            'Hydromechanization Equipment',
            'Technology of Drainage System Construction',
            'Volumetric Hydropneumatic Drives',
            'Theoretical Foundations of Operating Construction and Reclamation Machines',
            'Technologies for Construction and Operation of Closed Horizontal Drainage',
            'System of Technologies and Machines for Reclamation Construction',
            'Fundamentals of Digital Engineering and Digital Systems',
            'Smart Agriculture: Technologies and Equipment',
        ];

        return match ($locale) {
            'uz' => [
                'Ilmiy-tadqiqot ishi va davlat attestatsiya komissiyasi',
                'Meliorativ yer qazish kemalari va uskunalari',
                'Meliorativ qurilishni tashkil etishning ilmiy asoslari',
                'Muhandislik logistikasi va modellashtirish',
                'Ilmiy-tadqiqot ishlari',
                'Melioratsiya mashinalarini loyihalashning nazariy asoslari',
                'Ilmiy-pedagogik ish va ilmiy seminar',
                'Meliorativ qurilish mashinalariga texnik xizmat ko‘rsatish asoslari',
                'Texnologik mashinalarni loyihalash asoslari',
                'Sug‘orish mashinalarini loyihalashning nazariy asoslari',
                'Texnik me’yorlashtirishning ilmiy asoslari',
                'Resurs smeta normalarini ishlab chiqish asoslari (ShNQ.IKN)',
                'Gidromexanizatsiya uskunalari',
                'Drenaj tizimini qurish texnologiyasi',
                'Hajmiy gidropnevmatik yuritmalar',
                'Qurilish va melioratsiya mashinalaridan foydalanishning nazariy asoslari',
                'Yopiq gorizontal drenajlarni qurish va ulardan foydalanish texnologiyalari',
                'Meliorativ qurilish texnologiyalari va mashinalari tizimi',
                'Raqamli muhandislik va raqamli tizimlar asoslari',
                'Aqlli qishloq xo‘jaligi: texnologiyalar va uskunalar',
            ],
            'ru' => [
                'Научно-исследовательская работа и государственная аттестационная комиссия',
                'Мелиоративные земснаряды и оборудование',
                'Научные основы организации мелиоративного строительства',
                'Инженерная логистика и моделирование',
                'Научно-исследовательские работы',
                'Теоретические основы проектирования мелиоративных машин',
                'Научно-педагогическая работа и научный семинар',
                'Основы технического обслуживания машин мелиоративного строительства',
                'Основы проектирования технологических машин',
                'Теоретические основы проектирования оросительных машин',
                'Научные основы технического нормирования',
                'Основы разработки ресурсных сметных норм (ШНК.ИКН)',
                'Оборудование гидромеханизации',
                'Технология строительства дренажных систем',
                'Объемные гидропневматические приводы',
                'Теоретические основы эксплуатации строительных и мелиоративных машин',
                'Технологии строительства и эксплуатации закрытого горизонтального дренажа',
                'Система технологий и машин мелиоративного строительства',
                'Основы цифровой инженерии и цифровых систем',
                'Умное сельское хозяйство: технологии и оборудование',
            ],
            'ar' => [
                'الأعمال البحثية ولجنة الاعتماد الحكومية',
                'سفن ومعدات التجريف الاستصلاحية',
                'الأسس العلمية لتنظيم البناء الاستصلاحي',
                'اللوجستيات الهندسية والنمذجة',
                'الأعمال البحثية',
                'الأسس النظرية لتصميم آلات الاستصلاح',
                'العمل العلمي والتربوي والندوة العلمية',
                'أساسيات الصيانة الفنية لآلات البناء الاستصلاحي',
                'أساسيات تصميم الآلات التكنولوجية',
                'الأسس النظرية لتصميم آلات الري',
                'الأسس العلمية للتنظيم الفني',
                'أساسيات تطوير معايير تقدير الموارد (ShNQ.IKN)',
                'معدات الهيدروميكانيك',
                'تكنولوجيا إنشاء أنظمة الصرف',
                'المشغلات الهيدروبنيوماتيكية الحجمية',
                'الأسس النظرية لتشغيل آلات البناء والاستصلاح',
                'تقنيات إنشاء وتشغيل المصارف الأفقية المغلقة',
                'نظام التقنيات والآلات للبناء الاستصلاحي',
                'أساسيات الهندسة الرقمية والأنظمة الرقمية',
                'الزراعة الذكية: التقنيات والمعدات',
            ],
            default => $en,
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => [
                'Qishloq xo‘jaligi va suv xo‘jaligidagi mexanizatsiyalashgan jarayonlarni takomillashtirish, energiya sarfini kamaytirish, xizmat muddatini uzaytirish va ish samaradorligini oshirish bo‘yicha ilmiy tadqiqotlar olib boriladi.',
                'Suv resurslarini tejashda yer tekislash mashinalari samaradorligini oshirishning ilmiy asoslari o‘rganiladi.',
                'G‘o‘za qator oralig‘ida bo‘ylama va ko‘ndalang pushta olish jarayonlarini mexanizatsiyalashning texnik va texnologik asoslari tadqiq etiladi.',
                'Buxoro vohasi tuproq-iqlim sharoitida energiya tejamkor dastlabki ishlov berish mashinalari tizimining texnik va texnologik asoslari ishlab chiqiladi.',
                'G‘o‘zani suyuq bioo‘g‘itlar bilan tuproq ostidan sug‘orish uskunasi parametrlarini asoslash bo‘yicha tadqiqotlar olib boriladi.',
                'Biohumusdan ko‘chat tuvakchalari tayyorlash qurilmasi parametrlarini asoslash ishlari bajariladi.',
            ],
            'ru' => [
                'Кафедра проводит исследования по совершенствованию механизированных процессов в сельском и водном хозяйстве, снижению энергозатрат, увеличению срока службы машин и повышению эффективности работы.',
                'Изучаются научные основы повышения эффективности планировочных машин при сбережении водных ресурсов.',
                'Исследуются технические и технологические основы механизации продольного и поперечного окучивания междурядий хлопчатника.',
                'Разрабатываются технические и технологические основы системы энергосберегающих машин основной обработки почвы в почвенно-климатических условиях Бухарского оазиса.',
                'Проводятся исследования по обоснованию параметров оборудования для подпочвенного орошения хлопчатника жидкими биоудобрениями.',
                'Выполняются работы по обоснованию параметров устройства для изготовления рассадных горшочков из биогумуса.',
            ],
            'ar' => [
                'يجري القسم بحوثا لتحسين العمليات الميكانيكية في الزراعة وإدارة المياه، وتقليل استهلاك الطاقة، وزيادة عمر المعدات ورفع كفاءة العمل.',
                'تتم دراسة الأسس العلمية لرفع كفاءة آلات تسوية الأراضي في ترشيد استخدام الموارد المائية.',
                'تتم دراسة الأسس الفنية والتكنولوجية لميكنة عمليات التكويم الطولي والعرضي بين صفوف القطن.',
                'يتم تطوير الأسس الفنية والتكنولوجية لمنظومة آلات المعالجة الأولية الموفرة للطاقة في ظروف التربة والمناخ بواحة بخارى.',
                'تجرى بحوث لتبرير معايير معدات الري تحت السطحي للقطن باستخدام الأسمدة الحيوية السائلة.',
                'تجرى أعمال لتبرير معايير جهاز تصنيع أوعية الشتلات من البيوهوموس.',
            ],
            default => [
                'The department conducts research to improve mechanized processes in agriculture and water management, reduce energy consumption, extend equipment service life, and increase operational efficiency.',
                'Scientific foundations for improving the efficiency of land-leveling machines in water resource conservation are studied.',
                'Technical and technological foundations for mechanizing longitudinal and transverse hilling processes between cotton rows are investigated.',
                'Technical and technological foundations of an energy-saving primary tillage machine system are developed for the soil and climate conditions of the Bukhara oasis.',
                'Research is carried out to justify parameters for subsurface irrigation equipment for cotton using liquid bio-fertilizers.',
                'Parameters of a device for manufacturing seedling pots from biohumus are substantiated.',
            ],
        };
    }

    private function partners(): array
    {
        return [
            'Kursk State Agrarian University',
            'North Dakota State University (USA)',
            'Belarusian State Agrarian Technical University',
            'Humboldt University of Berlin (Germany)',
            'Obuda University (Hungary)',
            'Southwestern State University (Russia)',
            'Iowa State University (USA)',
            'INTI International University (Malaysia)',
        ];
    }

    private function plans(string $locale): array
    {
        return match ($locale) {
            'uz' => [
                'Ta’lim sifatini oshirish: xorijiy tajribani o‘rganish, dual ta’limni kengaytirish, ishlab chiqarish korxonalari bilan amaliyot va qo‘shma ishlanmalarni rivojlantirish.',
                'Korxonalarning zamonaviy laboratoriyalaridan o‘quv jarayonida foydalanish: Buxoro shahri va tumanlaridagi korxonalar bilan shartnomalar tuzish va amaliy mashg‘ulotlarni kengaytirish.',
                'Kafedraning ilmiy salohiyatini oshirish: yosh kadrlar uchun sharoit yaratish, iqtidorli bitiruvchilar va magistrlarni ilmiy ishlarga jalb qilish, startap va innovatsion loyihalarni qo‘llab-quvvatlash.',
                'Xalqaro aloqalarni rivojlantirish: TOP-1000 xorijiy oliy ta’lim muassasalari bilan hamkorlik, grantlar, SCOPUS maqolalari, akademik almashinuv va stajirovkalarni tashkil etish.',
            ],
            'ru' => [
                'Повышение качества образования: изучение зарубежного опыта, расширение дуального обучения, развитие практики и совместных разработок с производственными предприятиями.',
                'Использование современных лабораторий предприятий в учебном процессе: заключение договоров с предприятиями города Бухары и районов области и расширение практических занятий.',
                'Повышение научного потенциала кафедры: создание условий для молодых кадров, привлечение талантливых выпускников и магистрантов к научной работе, поддержка стартапов и инновационных проектов.',
                'Развитие международных связей: сотрудничество с зарубежными вузами TOP-1000, гранты, публикации в SCOPUS, академический обмен и стажировки.',
            ],
            'ar' => [
                'تحسين جودة التعليم: دراسة الخبرات الأجنبية، توسيع التعليم المزدوج، وتطوير التدريب العملي والمشروعات المشتركة مع مؤسسات الإنتاج.',
                'استخدام المختبرات الحديثة للمؤسسات في العملية التعليمية: توقيع عقود مع مؤسسات مدينة بخارى ومناطقها وتوسيع التدريب العملي.',
                'رفع القدرة العلمية للقسم: تهيئة الظروف للكوادر الشابة، وجذب الخريجين وطلبة الماجستير المتميزين إلى البحث العلمي، ودعم الأفكار الناشئة والمشروعات الابتكارية.',
                'تطوير العلاقات الدولية: التعاون مع جامعات أجنبية ضمن تصنيف TOP-1000، وتنفيذ المنح، ونشر مقالات في SCOPUS، وتنظيم التبادل الأكاديمي والتدريب.',
            ],
            default => [
                'Improve education quality through international experience, expanded dual education, production-based practice, and joint scientific and technical developments with enterprises.',
                'Use modern enterprise laboratories in the educational process by signing agreements with enterprises in Bukhara city and regional districts and expanding practical training.',
                'Increase the scientific potential of the department by supporting young researchers, selecting talented graduates and master’s students for research, and encouraging startup and innovation projects.',
                'Develop international relations through cooperation with TOP-1000 foreign universities, grants, SCOPUS publications, academic exchange, and staff internships.',
            ],
        };
    }

    private function staffProfiles(): array
    {
        return [
            ['slug' => 'agricultural-water-resources-engineering-technologies-rajabov-yarash-jabborovich', 'names' => ['en' => 'Rajabov Yarash Jabborovich', 'uz' => 'Rajabov Yarash Jabborovich', 'ru' => 'Ражабов Яраш Жабборович', 'ar' => 'رجبوف ياراش جباروفيتش'], 'position' => 'Head of Department'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-hasanov-ibrahim-subhonovich', 'names' => ['en' => 'Hasanov Ibrahim Subhonovich', 'uz' => 'Hasanov Ibrohim Subhonovich', 'ru' => 'Хасанов Ибрагим Субхонович', 'ar' => 'حسنوف إبراهيم سبحونوفيتش'], 'position' => 'Candidate of Technical Sciences, Associate Professor'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-juraev-fazliddin-orovich', 'names' => ['en' => 'Juraev Fazliddin Orovich', 'uz' => 'Jo‘rayev Fazliddin Orovich', 'ru' => 'Жураев Фазлиддин Орович', 'ar' => 'جوراييف فضل الدين أوروفيتش'], 'position' => 'Doctor of Technical Sciences, Professor'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-nuriddinov-khurram', 'names' => ['en' => 'Nuriddinov Khurram', 'uz' => 'Nuriddinov Xurram', 'ru' => 'Нуриддинов Хуррам', 'ar' => 'نوريدينوف خورام'], 'position' => 'Doctor of Technical Sciences, Professor'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-olimov-hamid-haydarovich', 'names' => ['en' => 'Olimov Hamid Haydarovich', 'uz' => 'Olimov Hamid Haydarovich', 'ru' => 'Олимов Хамид Хайдарович', 'ar' => 'أوليموف حميد حيدروفيتش'], 'position' => 'Doctor of Technical Sciences, Professor'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-hasanov-ulug-ibragimovich', 'names' => ['en' => 'Hasanov Ulug Ibragimovich', 'uz' => 'Hasanov Ulug‘ Ibragimovich', 'ru' => 'Хасанов Улуг Ибрагимович', 'ar' => 'حسنوف أولوغ إبراهيموفيتش'], 'position' => 'PhD in Technical Sciences, Associate Professor'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-jurayev-akram-azamat-oglu', 'names' => ['en' => 'Jurayev Akram Azamat oglu', 'uz' => 'Jo‘rayev Akram Azamat o‘g‘li', 'ru' => 'Жураев Акрам Азамат угли', 'ar' => 'جوراييف أكرم عزمت أوغلي'], 'position' => 'PhD in Technical Sciences, Associate Professor'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-kochkorov-jurat-jalilovich', 'names' => ['en' => 'Kochkorov Jurat Jalilovich', 'uz' => 'Qo‘chqorov Jo‘rat Jalilovich', 'ru' => 'Кочкоров Журат Жалилович', 'ar' => 'كوتشكوروف جورات جليلوفيتش'], 'position' => 'PhD in Technical Sciences, Associate Professor'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-ostonov-shukhrat-saidovich', 'names' => ['en' => 'Ostonov Shukhrat Saidovich', 'uz' => 'Ostonov Shukhrat Saidovich', 'ru' => 'Остонов Шухрат Саидович', 'ar' => 'أوستانوف شوخرات سعيدوفيتش'], 'position' => 'PhD in Technical Sciences, Associate Professor'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-ergashev-zuhriddin', 'names' => ['en' => 'Ergashev Zuhriddin', 'uz' => 'Ergashev Zuhriddin', 'ru' => 'Эргашев Зухриддин', 'ar' => 'إرغاشيف زخر الدين'], 'position' => 'Senior Lecturer'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-of-philosophy-in-technical-sciences-orziyev-sardor-samandarovich', 'names' => ['en' => 'Orziyev Sardor Samandarovich', 'uz' => 'Orziyev Sardor Samandarovich', 'ru' => 'Орзиев Сардор Самандарович', 'ar' => 'أورزييف سردور سماندروفيتش'], 'position' => 'Doctor of Philosophy in Technical Sciences'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-of-philosophy-in-technical-sciences-jurayev-asliddin-nasriddin-oglu', 'names' => ['en' => 'Jurayev Asliddin Nasriddin oglu', 'uz' => 'Jo‘rayev Asliddin Nasriddin o‘g‘li', 'ru' => 'Жураев Аслиддин Насриддин угли', 'ar' => 'جوراييف أصل الدين نصر الدين أوغلي'], 'position' => 'Doctor of Philosophy in Technical Sciences'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-of-philosophy-in-technical-sciences-rozikulov-jasur-uktam-oglu', 'names' => ['en' => 'Rozikulov Jasur Uktam oglu', 'uz' => 'Ro‘ziqulov Jasur O‘ktam o‘g‘li', 'ru' => 'Розикулов Жасур Уктам угли', 'ar' => 'روزيقولوف جسور أوكتام أوغلي'], 'position' => 'Doctor of Philosophy in Technical Sciences'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-rozikulov-step-istamovich', 'names' => ['en' => 'Rozikulov Sobit Istamovich', 'uz' => 'Ro‘ziqulov Sobit Istamovich', 'ru' => 'Розикулов Собит Истамович', 'ar' => 'روزيقولوف سوبيت إستاموفيتش'], 'position' => 'Assistant Teacher'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-isakov-zafarjon-shukhrat-oglu', 'names' => ['en' => 'Isakov Zafarjon Shukhrat oglu', 'uz' => 'Isakov Zafarjon Shukhrat o‘g‘li', 'ru' => 'Исаков Зафаржон Шухрат угли', 'ar' => 'إيساكوف زفرجون شوخرات أوغلي'], 'position' => 'Assistant Teacher'],
            ['slug' => 'agricultural-water-resources-engineering-technologies-halimov-tilavjon-azamat-oglu', 'names' => ['en' => 'Halimov Tilavjon Azamat oglu', 'uz' => 'Halimov Tilavjon Azamat o‘g‘li', 'ru' => 'Халимов Тилавжон Азамат угли', 'ar' => 'حاليموف تيلافجون عزمت أوغلي'], 'position' => 'Assistant Teacher'],
        ];
    }

    private function staffSectionItems(string $locale): array
    {
        return collect($this->staffProfiles())->map(function (array $profile) use ($locale) {
            $name = $profile['names'][$locale] ?? $profile['names']['en'];
            return $name."\n".$this->position($profile['position'], $locale);
        })->all();
    }

    private function position(string $position, string $locale): string
    {
        $map = [
            'Head of Department' => ['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'Candidate of Technical Sciences, Associate Professor' => ['en' => 'Candidate of Technical Sciences, Associate Professor', 'uz' => 'Texnika fanlari nomzodi, dotsent', 'ru' => 'Кандидат технических наук, доцент', 'ar' => 'مرشح العلوم التقنية، أستاذ مشارك'],
            'Doctor of Technical Sciences, Professor' => ['en' => 'Doctor of Technical Sciences, Professor', 'uz' => 'Texnika fanlari doktori, professor', 'ru' => 'Доктор технических наук, профессор', 'ar' => 'دكتور في العلوم التقنية، أستاذ'],
            'PhD in Technical Sciences, Associate Professor' => ['en' => 'PhD in Technical Sciences, Associate Professor', 'uz' => 'Texnika fanlari bo‘yicha PhD, dotsent', 'ru' => 'PhD по техническим наукам, доцент', 'ar' => 'دكتوراه في العلوم التقنية، أستاذ مشارك'],
            'Senior Lecturer' => ['en' => 'Senior Lecturer', 'uz' => 'Katta o‘qituvchi', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'Doctor of Philosophy in Technical Sciences' => ['en' => 'Doctor of Philosophy (PhD) in Technical Sciences', 'uz' => 'Texnika fanlari bo‘yicha falsafa doktori (PhD)', 'ru' => 'Доктор философии (PhD) по техническим наукам', 'ar' => 'دكتوراه في العلوم التقنية'],
            'Assistant Teacher' => ['en' => 'Assistant Teacher', 'uz' => 'Assistent-o‘qituvchi', 'ru' => 'Ассистент-преподаватель', 'ar' => 'مدرس مساعد'],
        ];

        return $map[$position][$locale] ?? $position;
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Qishloq va suv xo‘jaligi muhandisligi texnologiyalari kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре технологий сельскохозяйственной и водохозяйственной инженерии в должности «{$position}».",
            'ar' => "{$name} يعمل/تعمل في قسم تقنيات الهندسة الزراعية وإدارة المياه بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Agricultural and Water Management Engineering Technologies.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'Kafedrada o‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد التي تدرس في القسم'],
            'research' => ['en' => 'Research Work', 'uz' => 'Ilmiy-tadqiqot ishlari', 'ru' => 'Научно-исследовательские работы', 'ar' => 'الأعمال البحثية'],
            'cooperation' => ['en' => 'International Cooperation', 'uz' => 'Xalqaro hamkorlik', 'ru' => 'Международное сотрудничество', 'ar' => 'التعاون الدولي'],
            'plans' => ['en' => 'Prospective Plans', 'uz' => 'Istiqboldagi rejalar', 'ru' => 'Перспективные планы', 'ar' => 'الخطط المستقبلية'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function degreeLabel(string $locale, string $degree): string
    {
        return match ("{$locale}:{$degree}") {
            'uz:bachelor' => 'Bakalavriat fanlari',
            'uz:master' => 'Magistratura fanlari',
            'ru:bachelor' => 'Дисциплины бакалавриата',
            'ru:master' => 'Дисциплины магистратуры',
            'ar:bachelor' => 'مواد البكالوريوس',
            'ar:master' => 'مواد الماجستير',
            'en:master' => 'Master subjects',
            default => 'Bachelor subjects',
        };
    }

    private function replaceSection(array $sections, array $replacement): array
    {
        foreach ($sections as $index => $section) {
            if (($section['key'] ?? null) === $replacement['key']) {
                $sections[$index] = $replacement;
                return $sections;
            }
        }

        $sections[] = $replacement;
        return $sections;
    }

    private function locales(): array
    {
        if (! Schema::hasTable('locales')) {
            return ['en', 'uz', 'ru', 'ar'];
        }

        return DB::table('locales')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all() ?: ['en', 'uz', 'ru', 'ar'];
    }
};
