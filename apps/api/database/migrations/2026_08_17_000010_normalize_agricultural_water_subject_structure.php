<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'agricultural-water-resources-engineering-technologies')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeSections($departmentId);
            $this->renameStaffSlugs($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function normalizeSections(int $departmentId): void
    {
        foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
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

            foreach ($sections as $index => $section) {
                if (($section['key'] ?? null) === 'subjects') {
                    $sections[$index] = [
                        'key' => 'subjects',
                        'title' => $section['title'] ?? 'Taught Subjects',
                        'items' => [],
                        'bachelor' => $this->uniqueItems($this->bachelorSubjects($locale)),
                        'master' => $this->uniqueItems($this->masterSubjects($locale)),
                    ];
                }

                if (($section['key'] ?? null) === 'prepared_specialists') {
                    $sections[$index]['items'] = $this->programItems($departmentId, $locale);
                }
            }

            DB::table('department_translations')
                ->where('id', $translation->id)
                ->update([
                    'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
        }
    }

    private function renameStaffSlugs(int $departmentId): void
    {
        foreach ([
            'agricultural-water-resources-engineering-technologies-of-philosophy-in-technical-sciences-orziyev-sardor-samandarovich' => 'agricultural-water-resources-engineering-technologies-orziyev-sardor-samandarovich',
            'agricultural-water-resources-engineering-technologies-of-philosophy-in-technical-sciences-jurayev-asliddin-nasriddin-oglu' => 'agricultural-water-resources-engineering-technologies-jurayev-asliddin-nasriddin-oglu',
            'agricultural-water-resources-engineering-technologies-of-philosophy-in-technical-sciences-rozikulov-jasur-uktam-oglu' => 'agricultural-water-resources-engineering-technologies-rozikulov-jasur-uktam-oglu',
            'agricultural-water-resources-engineering-technologies-rozikulov-step-istamovich' => 'agricultural-water-resources-engineering-technologies-rozikulov-sobit-istamovich',
        ] as $oldSlug => $newSlug) {
            $oldId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $oldSlug)
                ->value('id');

            if (! $oldId) {
                continue;
            }

            $existingId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $newSlug)
                ->value('id');

            if ($existingId && $existingId !== $oldId) {
                DB::table('staff_profile_translations')->where('staff_profile_id', $oldId)->delete();
                DB::table('staff_profiles')->where('id', $oldId)->delete();
                continue;
            }

            DB::table('staff_profiles')->where('id', $oldId)->update([
                'slug' => $newSlug,
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

    private function uniqueItems(array $items): array
    {
        return collect($items)->unique()->values()->all();
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
};
