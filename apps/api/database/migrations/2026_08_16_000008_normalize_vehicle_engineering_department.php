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

        $departmentId = DB::table('departments')->where('slug', 'vehicle-engineering-automotive-transport-systems')->value('id');
        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeDepartment((int) $departmentId);
            $this->normalizeSections((int) $departmentId);
            $this->normalizeStaff((int) $departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function normalizeDepartment(int $departmentId): void
    {
        DB::table('departments')->where('id', $departmentId)->update([
            'head_name' => 'Gaffarov Hasan Ravshanovich',
            'phone' => '+998 97 306 07 37',
            'reception_time' => 'Monday-Friday 14:00-16:00',
            'updated_at' => now(),
        ]);

        foreach ($this->departmentDescriptions() as $locale => $description) {
            DB::table('department_translations')
                ->where('department_id', $departmentId)
                ->where('locale', $locale)
                ->update([
                    'description' => $description,
                    'meta_description' => $description,
                    'updated_at' => now(),
                ]);
        }
    }

    private function normalizeSections(int $departmentId): void
    {
        foreach ($this->locales() as $locale) {
            $translation = DB::table('department_translations')->where('department_id', $departmentId)->where('locale', $locale)->first();
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
                'publications' => ['title' => $this->label($locale, 'publications'), 'items' => $this->publicationItems($locale)],
                'research' => ['title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)],
                'cooperation' => ['title' => $this->label($locale, 'cooperation'), 'items' => $this->cooperationItems($locale)],
                'plans' => ['title' => $this->label($locale, 'plans'), 'items' => $this->plansItems($locale)],
            ] as $key => $payload) {
                $sections = $this->replaceSection($sections, ['key' => $key, 'title' => $payload['title'], 'items' => $payload['items']]);
            }

            DB::table('department_translations')->where('id', $translation->id)->update([
                'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteSlugs = [
            'vehicle-engineering-automotive-transport-systems-kafedra-mudiri-associate-professor',
            'vehicle-engineering-automotive-transport-systems-0-dr-olim-b-xalilov',
            'vehicle-engineering-automotive-transport-systems-1-ravshan-m-fayziyev',
            'vehicle-engineering-automotive-transport-systems-2-guli-sh-saidova',
        ];

        $ids = DB::table('staff_profiles')->whereIn('slug', $deleteSlugs)->pluck('id')->all();
        if ($ids !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
            DB::table('staff_profiles')->whereIn('id', $ids)->delete();
        }

        foreach ($this->staffProfiles() as $index => $profile) {
            $profileId = DB::table('staff_profiles')->where('slug', $profile['slug'])->value('id');
            if (! $profileId) {
                $profileId = DB::table('staff_profiles')->insertGetId([
                    'slug' => $profile['slug'],
                    'department_id' => $departmentId,
                    'faculty_id' => DB::table('departments')->where('id', $departmentId)->value('faculty_id'),
                    'photo' => null,
                    'email' => null,
                    'phone' => null,
                    'sort_order' => $index + 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
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
        return $this->numberedSection('Bachelor subjects', $this->bachelorSubjects($locale))."\n".$this->numberedSection('Master subjects', $this->masterSubjects($locale));
    }

    private function numberedSection(string $heading, array $items): string
    {
        return $heading.":\n".collect($items)->map(fn (string $item, int $index) => ($index + 1).'. '.$item)->implode("\n");
    }

    private function bachelorSubjects(string $locale): array
    {
        $en = ['Introduction to the Specialty', 'Foundry Alloys', 'CNC Technological Equipment and Systems', 'Turning Practice', 'Hydraulics', 'Cutting Theory and Cutting Tools', 'Materials Science and Engineering Fundamentals', 'Fundamentals of Materials Science', 'Heat Treatment Technology', 'Non-Metallic Materials Technology', 'Hydraulics and Hydraulic Systems', 'Materials Science and Structural Materials Technology', 'Soil Science and Farming', 'Technology of Livestock Product Production', 'Modern Professions and Renewable Energy Sources', 'Materials Science', 'Hydraulics and Hydropneumatic Drives', 'Fundamentals of Mechanical Engineering Technology', 'Foundry Technologies', 'Design of Mechanical Engineering Production', 'Design and Production of Blanks', 'New Materials Technology', 'Corrosion and Protection Methods', 'Metal Forming', 'Material Structure and Quality Control of Parts', 'Powder Composite Materials', 'Tractors and Vehicles', 'Agricultural Machinery', 'Professional Skills and Software Modeling of Engineering Works', 'Precision Agriculture Systems', 'Internal Combustion Engines and Green Economy', 'Mechanization of Agricultural Production and Fuel-Lubricant Materials', 'Automated Production Technology', 'Technological Equipment of Automated Production', 'Special Course in Mechanical Engineering', 'Innovative Equipment and Technologies in Mechanical Engineering', 'Fundamentals of Automation of Production Processes in Mechanical Engineering', 'Milling Practice', 'Technical Systems Management', 'Heating Devices', 'Powder Composite Materials', 'Foundry Technologies', 'Material Structure and Quality Control of Parts', 'Tool Materials', 'Corrosion and Protection Methods', 'Mechanization of Agricultural Production', 'Traffic Rules, Traffic Safety, Horticulture and Vegetable Machinery', 'Use and Technical Service of Agricultural Machinery and Technologies', 'Innovative Equipment and Technology Transfer', 'Mechanical Engineering Technology: Special Course', 'Design and Production of Technological Fixtures', 'Design of Automated Sections and Workshops'];
        $uz = ['Yo‘nalishga kirish', 'Quymakorlik qotishmalari', 'Raqamli dasturda boshqariladigan texnologik jihozlar va tizimlar', 'Tokarlik ishi', 'Gidravlika', 'Kesish nazariyasi va kesuvchi asboblar', 'Materialshunoslik va muhandislik asoslari', 'Materialshunoslikning fundamental asoslari', 'Termik ishlov berish texnologiyasi', 'Nometall materiallar texnologiyasi', 'Gidravlika va gidravlik tizimlar', 'Materialshunoslik va konstruksion materiallar texnologiyasi', 'Tuproqshunoslik va dehqonchilik', 'Chorvachilik mahsulotlarini yetishtirish texnologiyasi', 'Zamonaviy kasblar va tiklanuvchan energiya manbalari', 'Materialshunoslik', 'Gidravlika va gidropnevmatik yuritmalar', 'Mashinasozlik texnologiyasi asoslari', 'Quymakorlik texnologiyalari', 'Mashinasozlik ishlab chiqarishini loyihalash', 'Zagotovkalarni loyihalash va ishlab chiqarish', 'Yangi materiallar texnologiyasi', 'Korroziya va undan himoyalanish usullari', 'Metallarga bosim ostida ishlov berish', 'Materiallar tuzilishi va detallar sifatini nazorat qilish', 'Kukun kompozitsion materiallar', 'Traktorlar va transport vositalari', 'Qishloq xo‘jaligi mashinalari', 'Kasb mahorati va muhandislik ishlarini dasturiy modellashtirish', 'Aniq qishloq xo‘jaligi tizimlari', 'Ichki yonuv dvigatellari va yashil iqtisod', 'Qishloq xo‘jaligi ishlab chiqarishini mexanizatsiyalashtirish va yoqilg‘i-moylash materiallari', 'Avtomatlashtirilgan ishlab chiqarish texnologiyasi', 'Avtomatlashtirilgan ishlab chiqarishning texnologik jihozlari', 'Mashinasozlik maxsus kursi', 'Mashinasozlikda innovatsion texnika va texnologiyalar', 'Mashinasozlikda ishlab chiqarish jarayonlarini avtomatlashtirish asoslari', 'Frezalash ishi', 'Texnik tizimlarni boshqarish', 'Qizdirish qurilmalari', 'Kukunli kompozitsion materiallar', 'Quymakorlik texnologiyalari', 'Materiallar tuzilishi va detallar sifatini nazorat qilish', 'Asbobsozlik materiallari', 'Korroziya va undan himoyalanish usullari', 'Qishloq xo‘jaligi ishlab chiqarishini mexanizatsiyalash', 'Yo‘l harakati qoidalari, harakat xavfsizligi, bog‘dorchilik va sabzavotchilik mashinalari', 'Qishloq xo‘jalik texnika va texnologiyalaridan foydalanish va texnik servis', 'Innovatsion texnika va ularning transferi', 'Mashinasozlik texnologiyasi: maxsus kurs', 'Texnologik moslamalarni loyihalash va ishlab chiqarish', 'Avtomatlashtirilgan uchastka va sexlarni loyihalash'];
        $ru = ['Введение в специальность', 'Литейные сплавы', 'Технологическое оборудование и системы с ЧПУ', 'Токарное дело', 'Гидравлика', 'Теория резания и режущие инструменты', 'Материаловедение и основы инженерии', 'Фундаментальные основы материаловедения', 'Технология термической обработки', 'Технология неметаллических материалов', 'Гидравлика и гидравлические системы', 'Материаловедение и технология конструкционных материалов', 'Почвоведение и земледелие', 'Технология производства продукции животноводства', 'Современные профессии и возобновляемые источники энергии', 'Материаловедение', 'Гидравлика и гидропневмоприводы', 'Основы технологии машиностроения', 'Литейные технологии', 'Проектирование машиностроительного производства', 'Проектирование и производство заготовок', 'Технология новых материалов', 'Коррозия и методы защиты', 'Обработка металлов давлением', 'Структура материалов и контроль качества деталей', 'Порошковые композиционные материалы', 'Тракторы и транспортные средства', 'Сельскохозяйственные машины', 'Профессиональное мастерство и программное моделирование инженерных работ', 'Системы точного земледелия', 'Двигатели внутреннего сгорания и зеленая экономика', 'Механизация сельскохозяйственного производства и горюче-смазочные материалы', 'Технология автоматизированного производства', 'Технологическое оборудование автоматизированного производства', 'Специальный курс машиностроения', 'Инновационная техника и технологии в машиностроении', 'Основы автоматизации производственных процессов в машиностроении', 'Фрезерное дело', 'Управление техническими системами', 'Нагревательные устройства', 'Порошковые композиционные материалы', 'Литейные технологии', 'Структура материалов и контроль качества деталей', 'Инструментальные материалы', 'Коррозия и методы защиты', 'Механизация сельскохозяйственного производства', 'Правила дорожного движения, безопасность движения, машины садоводства и овощеводства', 'Использование и технический сервис сельскохозяйственной техники и технологий', 'Инновационная техника и трансфер технологий', 'Технология машиностроения: специальный курс', 'Проектирование и производство технологической оснастки', 'Проектирование автоматизированных участков и цехов'];
        $ar = ['مدخل إلى التخصص', 'سبائك السباكة', 'المعدات والأنظمة التقنية ذات التحكم الرقمي CNC', 'أعمال الخراطة', 'الهيدروليكا', 'نظرية القطع وأدوات القطع', 'علم المواد وأساسيات الهندسة', 'الأسس الأساسية لعلم المواد', 'تقنية المعالجة الحرارية', 'تقنية المواد غير المعدنية', 'الهيدروليكا والأنظمة الهيدروليكية', 'علم المواد وتقنية المواد الإنشائية', 'علم التربة والزراعة', 'تقنية إنتاج المنتجات الحيوانية', 'المهن الحديثة ومصادر الطاقة المتجددة', 'علم المواد', 'الهيدروليكا ومحركات الهيدروبنيوماتيك', 'أساسيات تقنية الهندسة الميكانيكية', 'تقنيات السباكة', 'تصميم إنتاج الهندسة الميكانيكية', 'تصميم وإنتاج المشغولات الأولية', 'تقنية المواد الجديدة', 'التآكل وطرق الحماية منه', 'تشكيل المعادن بالضغط', 'بنية المواد ومراقبة جودة القطع', 'المواد المركبة المسحوقية', 'الجرارات والمركبات', 'الآلات الزراعية', 'المهارات المهنية والنمذجة البرمجية للأعمال الهندسية', 'أنظمة الزراعة الدقيقة', 'محركات الاحتراق الداخلي والاقتصاد الأخضر', 'ميكنة الإنتاج الزراعي ومواد الوقود والتشحيم', 'تقنية الإنتاج المؤتمت', 'المعدات التقنية للإنتاج المؤتمت', 'مقرر خاص في الهندسة الميكانيكية', 'المعدات والتقنيات المبتكرة في الهندسة الميكانيكية', 'أساسيات أتمتة عمليات الإنتاج في الهندسة الميكانيكية', 'أعمال التفريز', 'إدارة الأنظمة التقنية', 'أجهزة التسخين', 'المواد المركبة المسحوقية', 'تقنيات السباكة', 'بنية المواد ومراقبة جودة القطع', 'مواد تصنيع الأدوات', 'التآكل وطرق الحماية منه', 'ميكنة الإنتاج الزراعي', 'قواعد المرور وسلامة الحركة وآلات البستنة والخضروات', 'استخدام وخدمة التقنيات والآلات الزراعية', 'المعدات المبتكرة ونقل التقنيات', 'تقنية الهندسة الميكانيكية: مقرر خاص', 'تصميم وإنتاج التجهيزات التقنية', 'تصميم الأقسام والورش المؤتمتة'];

        return match ($locale) {
            'uz' => $uz,
            'ru' => $ru,
            'ar' => $ar,
            default => $en,
        };
    }

    private function masterSubjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Ilmiy tadqiqot metodologiyasi', 'Mashinasozlikda kesuvchi asboblarni loyihalash va ishlab chiqarish', 'Dastgohlar va dastgohli tizimlarni hisoblash va konstruksiyalash', 'Mashinalar va jarayonlar puxtaligi', 'Ilmiy-tadqiqot ishi va magistrlik dissertatsiyasini tayyorlash', 'Ilmiy-pedagogik ish', 'Mashinasozlikda resurstejamkor texnologiyalar', 'Mashinasozlikda aniqlik va uning texnologik ta’minoti', 'Raqamli dastur bilan boshqariladigan dastgohlar va dastgohli komplekslar', 'Maxsus fanlarni o‘qitish metodikasi', 'Transport vositalari va tizimlarining ishonchliligini loyihalash', 'Avtomobil transport vositalarining istiqbolli rivojlanishi va texnik ekspluatatsiyasi', 'Texnologik jarayonlarni boshqarish va tajribani rejalashtirish', 'Avtomobillar texnik ekspluatatsiyasi me’yorlarini aniqlash usullari', 'Avtomobil transportida logistika va resurslarni tejash', 'Avtotransport vositalarining ekspluatatsion qobiliyatini ta’minlash', 'Qiyosiy muhandislik', 'Qishloq xo‘jaligi mashinalari uchun yangi texnik yechimlar', 'Qishloq xo‘jaligi mashinalarining nazariy hisoblari', 'Tadqiqot usullari va ekspluatatsiya'],
            'ru' => ['Методология научных исследований', 'Проектирование и производство режущих инструментов в машиностроении', 'Расчет и конструирование станков и станочных систем', 'Надежность машин и процессов', 'Научно-исследовательская работа и подготовка магистерской диссертации', 'Научно-педагогическая работа', 'Ресурсосберегающие технологии в машиностроении', 'Точность в машиностроении и ее технологическое обеспечение', 'Станки и станочные комплексы с числовым программным управлением', 'Методика преподавания специальных дисциплин', 'Проектирование надежности транспортных средств и систем', 'Перспективное развитие автомобильных транспортных средств и их техническая эксплуатация', 'Управление технологическими процессами и планирование экспериментов', 'Методы определения норм технической эксплуатации автомобилей', 'Логистика и ресурсосбережение на автомобильном транспорте', 'Обеспечение эксплуатационной способности автотранспортных средств', 'Сравнительная инженерия', 'Новые технические решения для сельскохозяйственных машин', 'Теоретические расчеты сельскохозяйственных машин', 'Методы исследования и эксплуатация'],
            'ar' => ['منهجية البحث العلمي', 'تصميم وإنتاج أدوات القطع في الهندسة الميكانيكية', 'حساب وتصميم الماكينات وأنظمة الماكينات', 'اعتمادية الآلات والعمليات', 'العمل البحثي وإعداد رسالة الماجستير', 'العمل العلمي والتربوي', 'تقنيات توفير الموارد في الهندسة الميكانيكية', 'الدقة في الهندسة الميكانيكية ودعمها التقني', 'الماكينات ومجمعات الماكينات ذات التحكم الرقمي', 'طرائق تدريس التخصصات الخاصة', 'تصميم اعتمادية المركبات والأنظمة', 'التطوير المستقبلي للمركبات والنقل البري وتشغيلها التقني', 'إدارة العمليات التقنية وتخطيط التجارب', 'طرق تحديد معايير التشغيل التقني للسيارات', 'اللوجستيات وتوفير الموارد في النقل البري', 'ضمان الكفاءة التشغيلية للمركبات', 'الهندسة المقارنة', 'حلول تقنية جديدة للآلات الزراعية', 'الحسابات النظرية للآلات الزراعية', 'طرق البحث والتشغيل'],
            default => ['Research Methodology', 'Design and Production of Cutting Tools in Mechanical Engineering', 'Calculation and Design of Machine Tools and Machine Tool Systems', 'Reliability of Machines and Processes', 'Research Work and Preparation of Master’s Thesis', 'Scientific and Pedagogical Work', 'Resource-Saving Technologies in Mechanical Engineering', 'Precision in Mechanical Engineering and Its Technological Support', 'CNC Machine Tools and Machine Tool Complexes', 'Methodology of Teaching Special Disciplines', 'Reliability Design of Transport Vehicles and Systems', 'Prospective Development and Technical Operation of Motor Vehicles', 'Management of Technological Processes and Experimental Planning', 'Methods for Determining Standards of Technical Exploitation of Automobiles', 'Logistics and Resource Conservation in Road Transport', 'Ensuring the Operational Capability of Motor Vehicles', 'Comparative Engineering', 'New Technical Solutions for Agricultural Machinery', 'Theoretical Calculations of Agricultural Machinery', 'Research Methods and Operation'],
        };
    }

    private function publicationItems(string $locale): array
    {
        $base = [
            ['authors' => 'O‘rinov U.A., Amonov M.I., Yo‘ldoshev M.N.', 'title' => '“Mashinasozlik texnologiyasi asoslari”', 'type' => null, 'details' => ['en' => 'Bukhara: Sharq, 2025.', 'uz' => 'Buxoro: Sharq, 2025.', 'ru' => 'Бухара: издательство «Sharq», 2025.', 'ar' => 'بخارى: دار Sharq، 2025.']],
            ['authors' => 'Kamolov M.Q., Gaffarov H.R.', 'title' => '“Avtotransport tarmog‘i korxonalarini loyihalash va jihozlash”', 'type' => 'textbook', 'details' => ['en' => 'Bukhara: Umid, 2023, 444 p.', 'uz' => 'Buxoro: Umid, 2023, 444 b.', 'ru' => 'Бухара: издательство «Umid», 2023, 444 с.', 'ar' => 'بخارى: دار Umid، 2023، 444 صفحة.']],
            ['authors' => 'Kamolov M.Q., Gaffarov H.R.', 'title' => '“Avtomobillarni texnik diagnostikalash”', 'type' => 'textbook', 'details' => ['en' => 'Bukhara: Umid, 2023, 216 p.', 'uz' => 'Buxoro: Umid, 2023, 216 b.', 'ru' => 'Бухара: издательство «Umid», 2023, 216 с.', 'ar' => 'بخارى: دار Umid، 2023، 216 صفحة.']],
            ['authors' => 'Kamolov M.Q., Gaffarov H.R.', 'title' => '“Transport vositalarining elektr jihozlari va elektron tizimlari”', 'type' => 'textbook', 'details' => ['en' => 'Bukhara: Umid, 2023, 496 p.', 'uz' => 'Buxoro: Umid, 2023, 496 b.', 'ru' => 'Бухара: издательство «Umid», 2023, 496 с.', 'ar' => 'بخارى: دار Umid، 2023، 496 صفحة.']],
            ['authors' => 'Kamolov M.Q., Gaffarov X.L., Samandarov A.X., Dushanov N.Q.', 'title' => '“Avtomobil dvigatellariga servis xizmati ko‘rsatish va ta’mirlash”', 'type' => 'textbook', 'details' => ['en' => 'Bukhara: Kamolot, 2024, 424 p.', 'uz' => 'Buxoro: Kamolot, 2024, 424 b.', 'ru' => 'Бухара: издательство «Kamolot», 2024, 424 с.', 'ar' => 'بخارى: دار Kamolot، 2024، 424 صفحة.']],
            ['authors' => 'Kamolov M.Q., Gaffarov L.X., Samandarov A.X., Samandarov V.A.', 'title' => '“Ichki yonuv dvigatellari”', 'type' => 'textbook', 'details' => ['en' => 'Bukhara: Kamolot, 2024, 202 p.', 'uz' => 'Buxoro: Kamolot, 2024, 202 b.', 'ru' => 'Бухара: издательство «Kamolot», 2024, 202 с.', 'ar' => 'بخارى: دار Kamolot، 2024، 202 صفحة.']],
            ['authors' => 'Kamolov M.Q., Gaffarov L.X.', 'title' => '“Transport vositalari detallarini ta’mirlash”', 'type' => 'laboratory', 'details' => ['en' => 'Bukhara: Umid, 2023, 220 p.', 'uz' => 'Buxoro: Umid, 2023, 220 b.', 'ru' => 'Бухара: издательство «Umid», 2023, 220 с.', 'ar' => 'بخارى: دار Umid، 2023، 220 صفحة.']],
            ['authors' => 'Kamolov M.Q., Gaffarov L.X., Samandarov A.X., Djumaev Z.F.', 'title' => '“Avtomobillarning texnik ekspluatatsiyasi va servis”', 'type' => 'guide', 'details' => ['en' => 'Bukhara: Umid, 2023, 220 p.', 'uz' => 'Buxoro: Umid, 2023, 220 b.', 'ru' => 'Бухара: издательство «Umid», 2023, 220 с.', 'ar' => 'بخارى: دار Umid، 2023، 220 صفحة.']],
            ['authors' => 'Kamolov M.Q., Gaffarov H.R., Jumaev U.R., Berdiev Z.R., Ilxomova M.I.', 'title' => '“Transport vositalari konstruktsiyasi”', 'type' => 'textbook', 'details' => ['en' => 'Bukhara: Durdona, 2024, 275 p.', 'uz' => 'Buxoro: Durdona, 2024, 275 b.', 'ru' => 'Бухара: издательство «Durdona», 2024, 275 с.', 'ar' => 'بخارى: دار Durdona، 2024، 275 صفحة.']],
            ['authors' => 'Kamolov M.Q., Kamolov Z.M.', 'title' => 'Automotive and vehicle engineering electronic test collection', 'type' => 'certificate', 'details' => ['en' => 'Intellectual Property Agency certificate No. DGU 12839, 11.08.2021.', 'uz' => 'Intellektual mulk agentligi guvohnomasi № DGU 12839, 11.08.2021.', 'ru' => 'Свидетельство Агентства интеллектуальной собственности № DGU 12839, 11.08.2021.', 'ar' => 'شهادة وكالة الملكية الفكرية رقم DGU 12839، 11.08.2021.']],
            ['authors' => 'Kamolov M.Q., Gaffarov L.X.', 'title' => 'Computer software official registration', 'type' => 'certificate', 'details' => ['en' => 'Ministry of Justice certificate No. DGU 21555, 12.01.2023.', 'uz' => 'Adliya vazirligi guvohnomasi № DGU 21555, 12.01.2023.', 'ru' => 'Свидетельство Министерства юстиции № DGU 21555, 12.01.2023.', 'ar' => 'شهادة وزارة العدل رقم DGU 21555، 12.01.2023.']],
            ['authors' => 'Kamolov M.Q.', 'title' => '“Yonilg‘i ta’minlash tizimini servis xizmat ko‘rsatish va ta’mirlash”', 'type' => 'guide_technical', 'details' => ['en' => 'Bukhara: Kamolot, 2025, 142 p.', 'uz' => 'Buxoro: Kamolot, 2025, 142 b.', 'ru' => 'Бухара: издательство «Kamolot», 2025, 142 с.', 'ar' => 'بخارى: دار Kamolot، 2025، 142 صفحة.']],
            ['authors' => 'Kamolov M.Q.', 'title' => '“Ishonchlilik nazariyasi va diagnostika asoslari”', 'type' => 'guide', 'details' => ['en' => 'Bukhara: Kamolot, 2025, 99 p.', 'uz' => 'Buxoro: Kamolot, 2025, 99 b.', 'ru' => 'Бухара: издательство «Kamolot», 2025, 99 с.', 'ar' => 'بخارى: دار Kamolot، 2025، 99 صفحة.']],
            ['authors' => 'Norov S.N.', 'title' => '“Avtomobillarning elektr va elektron jihozlari”', 'type' => 'textbook', 'details' => ['en' => 'Registration No. 391462, 2023.', 'uz' => 'Ro‘yxat raqami №391462, 2023.', 'ru' => 'Регистрационный номер №391462, 2023.', 'ar' => 'رقم التسجيل 391462، 2023.']],
            ['authors' => 'Yuliyev O.O.', 'title' => '“Issiqlik texnikasi va ichki yonuv dvigatellari”', 'type' => null, 'details' => ['en' => 'ISBN 978-9943-6979-9-7, 2025.', 'uz' => 'ISBN 978-9943-6979-9-7, 2025.', 'ru' => 'ISBN 978-9943-6979-9-7, 2025.', 'ar' => 'ISBN 978-9943-6979-9-7، 2025.']],
            ['authors' => 'Bafoyev D.X.', 'title' => '“Texnologik jihozlarni ta’mirlash va foydalanish”', 'type' => null, 'details' => ['en' => 'Durdona, ISBN 978-9910-634-72-2, 2025.', 'uz' => 'Durdona, ISBN 978-9910-634-72-2, 2025.', 'ru' => 'Издательство «Durdona», ISBN 978-9910-634-72-2, 2025.', 'ar' => 'دار Durdona، ISBN 978-9910-634-72-2، 2025.']],
            ['authors' => 'Bafoyev D.X., Xalilov J.M.', 'title' => '“Texnologik jihozlarni ta’mirlash va foydalanish”', 'type' => null, 'details' => ['en' => 'GlobeEdit, ISBN 978-620-9-18250-1, 2026.', 'uz' => 'GlobeEdit, ISBN 978-620-9-18250-1, 2026.', 'ru' => 'Издательство GlobeEdit, ISBN 978-620-9-18250-1, 2026.', 'ar' => 'دار GlobeEdit، ISBN 978-620-9-18250-1، 2026.']],
            ['authors' => 'Fazliyev J.Sh.', 'title' => '“Bog‘larni tomchilatib sug‘orishning ilmiy asoslangan texnologiyasini ishlab chiqish”', 'type' => null, 'details' => ['en' => 'Bukhara regional printing house, ISBN 978-9910-8421-2-2, 2026.', 'uz' => 'Buxoro viloyat bosmaxonasi, ISBN 978-9910-8421-2-2, 2026.', 'ru' => 'Бухарская областная типография, ISBN 978-9910-8421-2-2, 2026.', 'ar' => 'مطبعة ولاية بخارى، ISBN 978-9910-8421-2-2، 2026.']],
            ['authors' => 'Hakimova Z.Z., Fazliyev J.Sh., Xalilov J.M.', 'title' => '“Maishiy-oqova suvlaridan samarali foydalanish texnologiyasi”', 'type' => null, 'details' => ['en' => 'GlobeEdit, ISBN 978-620-9-43359-7, 2026.', 'uz' => 'GlobeEdit, ISBN 978-620-9-43359-7, 2026.', 'ru' => 'Издательство GlobeEdit, ISBN 978-620-9-43359-7, 2026.', 'ar' => 'دار GlobeEdit، ISBN 978-620-9-43359-7، 2026.']],
            ['authors' => 'Fazliyev J.Sh., Hakimova Z.Z., Xalilov J.M.', 'title' => '“Gidrometriya”', 'type' => null, 'details' => ['en' => 'GlobeEdit, ISBN 978-620-9-30286-2, 2025.', 'uz' => 'GlobeEdit, ISBN 978-620-9-30286-2, 2025.', 'ru' => 'Издательство GlobeEdit, ISBN 978-620-9-30286-2, 2025.', 'ar' => 'دار GlobeEdit، ISBN 978-620-9-30286-2، 2025.']],
            ['authors' => 'O‘rinov N.F.', 'title' => '“Gidravlika”', 'type' => null, 'details' => ['en' => 'V.V.S. Publishing, ISBN 978-9910-8961-7-0, 2025.', 'uz' => 'V.V.S. nashriyoti, ISBN 978-9910-8961-7-0, 2025.', 'ru' => 'Издательство «V.V.S.», ISBN 978-9910-8961-7-0, 2025.', 'ar' => 'دار V.V.S.، ISBN 978-9910-8961-7-0، 2025.']],
            ['authors' => 'O‘rinov N.F.', 'title' => '“Detali mashin”', 'type' => null, 'details' => ['en' => 'Ipak yo‘li Publishing, ISBN 978-9910-9238-9-0, 2024.', 'uz' => 'Ipak yo‘li nashriyoti, ISBN 978-9910-9238-9-0, 2024.', 'ru' => 'Издательство «Ipak yo‘li», ISBN 978-9910-9238-9-0, 2024.', 'ar' => 'دار Ipak yo‘li، ISBN 978-9910-9238-9-0، 2024.']],
            ['authors' => 'Adizova S.Y., Abdullayeva D.X.', 'title' => '“Quymakorlik texnologiyalari”', 'type' => null, 'details' => ['en' => 'Durdona, ISBN 978-9910-879-2, 2026.', 'uz' => 'Durdona, ISBN 978-9910-879-2, 2026.', 'ru' => 'Издательство «Durdona», ISBN 978-9910-879-2, 2026.', 'ar' => 'دار Durdona، ISBN 978-9910-879-2، 2026.']],
            ['authors' => 'N.R. Barakayev, N.F. O‘rinov', 'title' => '“Tokarlik ishi”', 'type' => 'guide', 'details' => ['en' => 'Bukhara: Umid, 2022, 295 p.', 'uz' => 'Buxoro: Umid, 2022, 295 b.', 'ru' => 'Бухара: издательство «Umid», 2022, 295 с.', 'ar' => 'بخارى: دار Umid، 2022، 295 صفحة.']],
            ['authors' => 'N.F. O‘rinov, M.X. Saidova', 'title' => '“Mashinasozlik texnologiyasi fanidan kurs loyihasi”', 'type' => 'guide', 'details' => ['en' => 'Bukhara: Umid, 2022, 195 p.', 'uz' => 'Buxoro: Umid, 2022, 195 b.', 'ru' => 'Бухара: издательство «Umid», 2022, 195 с.', 'ar' => 'بخارى: دار Umid، 2022، 195 صفحة.']],
            ['authors' => 'Bafoyev D.X.', 'title' => '“Gidravlika va gidropnevmo yuritmalar”', 'type' => 'certificate', 'details' => ['en' => 'Certificate No. DGU 45598, 14.12.2024.', 'uz' => 'Guvohnoma № DGU 45598, 14.12.2024.', 'ru' => 'Свидетельство № DGU 45598, 14.12.2024.', 'ar' => 'شهادة رقم DGU 45598، 14.12.2024.']],
            ['authors' => 'O‘rinov N.F.', 'title' => '“Mashina detallari”', 'type' => 'certificate', 'details' => ['en' => 'Certificate No. DGU 45023, 04.12.2024.', 'uz' => 'Guvohnoma № DGU 45023, 04.12.2024.', 'ru' => 'Свидетельство № DGU 45023, 04.12.2024.', 'ar' => 'شهادة رقم DGU 45023، 04.12.2024.']],
            ['authors' => 'Saidova M.X.', 'title' => '“Kesish nazariyasi va kesuvchi asboblar”', 'type' => 'certificate', 'details' => ['en' => 'Certificate No. DGU 45022, 04.12.2024.', 'uz' => 'Guvohnoma № DGU 45022, 04.12.2024.', 'ru' => 'Свидетельство № DGU 45022, 04.12.2024.', 'ar' => 'شهادة رقم DGU 45022، 04.12.2024.']],
            ['authors' => 'Abdullayeva D.X.', 'title' => '“Avtomatlashtirilgan ishlab chiqarishning texnologik jihozlari”', 'type' => 'certificate', 'details' => ['en' => 'Certificate No. DGU 44164, 19.11.2024.', 'uz' => 'Guvohnoma № DGU 44164, 19.11.2024.', 'ru' => 'Свидетельство № DGU 44164, 19.11.2024.', 'ar' => 'شهادة رقم DGU 44164، 19.11.2024.']],
            ['authors' => 'Saidov M.N.', 'title' => '“Transport vositalarida ishlatiladigan ekspluatatsion materiallar”', 'type' => 'certificate', 'details' => ['en' => 'Certificate DGU 202402611, 10.03.2024.', 'uz' => 'Guvohnoma DGU 202402611, 10.03.2024.', 'ru' => 'Свидетельство DGU 202402611, 10.03.2024.', 'ar' => 'شهادة DGU 202402611، 10.03.2024.']],
            ['authors' => 'Saidov M.N.', 'title' => '“Transport vositalarining elektr va elektron jihozlari”', 'type' => 'electronic_textbook', 'details' => ['en' => 'Electronic textbook certificate DGU 202405365, 02.05.2024.', 'uz' => 'Elektron darslik guvohnomasi DGU 202405365, 02.05.2024.', 'ru' => 'Свидетельство электронного учебника DGU 202405365, 02.05.2024.', 'ar' => 'شهادة كتاب إلكتروني DGU 202405365، 02.05.2024.']],
        ];

        $typeLabels = [
            'textbook' => ['en' => 'Textbook.', 'uz' => 'Darslik.', 'ru' => 'Учебник.', 'ar' => 'كتاب دراسي.'],
            'laboratory' => ['en' => 'Laboratory manual.', 'uz' => 'Laboratoriya qo‘llanmasi.', 'ru' => 'Лабораторное пособие.', 'ar' => 'دليل مختبر.'],
            'guide' => ['en' => 'Study guide.', 'uz' => 'O‘quv qo‘llanma.', 'ru' => 'Учебное пособие.', 'ar' => 'دليل تعليمي.'],
            'guide_technical' => ['en' => 'Study guide for technical colleges.', 'uz' => 'Texnikumlar uchun o‘quv qo‘llanma.', 'ru' => 'Учебное пособие для техникумов.', 'ar' => 'دليل تعليمي للكليات التقنية.'],
            'certificate' => ['en' => '', 'uz' => '', 'ru' => '', 'ar' => ''],
            'electronic_textbook' => ['en' => '', 'uz' => '', 'ru' => '', 'ar' => ''],
        ];

        $localizedTitles = $this->publicationTitles($locale);

        return collect($base)->map(function (array $item, int $index) use ($locale, $typeLabels, $localizedTitles) {
            $type = $item['type'] ? ($typeLabels[$item['type']][$locale] ?? $typeLabels[$item['type']]['en']) : '';
            $details = $item['details'][$locale] ?? $item['details']['en'];
            $title = $localizedTitles[$index] ?? $item['title'];

            return trim(preg_replace('/\s+/', ' ', $item['authors'].' '.$title.'. '.$type.' '.$details));
        })->all();
    }

    private function publicationTitles(string $locale): array
    {
        return match ($locale) {
            'ru' => ['«Основы технологии машиностроения»', '«Проектирование и оснащение предприятий автотранспортной отрасли»', '«Техническая диагностика автомобилей»', '«Электрооборудование и электронные системы транспортных средств»', '«Сервисное обслуживание и ремонт автомобильных двигателей»', '«Двигатели внутреннего сгорания»', '«Ремонт деталей транспортных средств»', '«Техническая эксплуатация и сервис автомобилей»', '«Конструкция транспортных средств»', 'Электронный сборник тестов по дисциплинам автомобильной и транспортной инженерии', 'Официальная регистрация программного обеспечения для компьютеров', '«Сервисное обслуживание и ремонт системы топливоподачи»', '«Теория надежности и основы диагностики»', '«Электрическое и электронное оборудование автомобилей»', '«Теплотехника и двигатели внутреннего сгорания»', '«Ремонт и эксплуатация технологического оборудования»', '«Ремонт и эксплуатация технологического оборудования»', '«Разработка научно обоснованной технологии капельного орошения садов»', '«Технология эффективного использования бытовых сточных вод»', '«Гидрометрия»', '«Гидравлика»', '«Детали машин»', '«Литейные технологии»', '«Токарное дело»', '«Курсовой проект по технологии машиностроения»', '«Гидравлика и гидропневматические приводы»', '«Детали машин»', '«Теория резания и режущие инструменты»', '«Технологическое оборудование автоматизированного производства»', '«Эксплуатационные материалы, используемые в транспортных средствах»', '«Электрическое и электронное оборудование транспортных средств»'],
            'ar' => ['«أساسيات تقنية الهندسة الميكانيكية»', '«تصميم وتجهيز مؤسسات قطاع النقل البري»', '«التشخيص الفني للسيارات»', '«المعدات الكهربائية والأنظمة الإلكترونية للمركبات»', '«خدمة وصيانة محركات السيارات»', '«محركات الاحتراق الداخلي»', '«إصلاح أجزاء المركبات»', '«التشغيل الفني وخدمة السيارات»', '«تصميم المركبات»', 'مجموعة اختبارات إلكترونية لمواد هندسة السيارات والمركبات', 'التسجيل الرسمي لبرنامج حاسوبي', '«خدمة وصيانة نظام تزويد الوقود»', '«نظرية الاعتمادية وأساسيات التشخيص»', '«المعدات الكهربائية والإلكترونية للسيارات»', '«الهندسة الحرارية ومحركات الاحتراق الداخلي»', '«إصلاح وتشغيل المعدات التقنية»', '«إصلاح وتشغيل المعدات التقنية»', '«تطوير تقنية علمية للري بالتنقيط في البساتين»', '«تقنية الاستخدام الفعال لمياه الصرف المنزلية»', '«الهيدرومترية»', '«الهيدروليكا»', '«أجزاء الآلات»', '«تقنيات السباكة»', '«أعمال الخراطة»', '«مشروع مقرر في تقنية الهندسة الميكانيكية»', '«الهيدروليكا ومحركات الهيدروبنيوماتيك»', '«أجزاء الآلات»', '«نظرية القطع وأدوات القطع»', '«المعدات التقنية للإنتاج المؤتمت»', '«مواد التشغيل المستخدمة في المركبات»', '«المعدات الكهربائية والإلكترونية للمركبات»'],
            'uz' => ['“Mashinasozlik texnologiyasi asoslari”', '“Avtotransport tarmog‘i korxonalarini loyihalash va jihozlash”', '“Avtomobillarni texnik diagnostikalash”', '“Transport vositalarining elektr jihozlari va elektron tizimlari”', '“Avtomobil dvigatellariga servis xizmati ko‘rsatish va ta’mirlash”', '“Ichki yonuv dvigatellari”', '“Transport vositalari detallarini ta’mirlash”', '“Avtomobillarning texnik ekspluatatsiyasi va servis”', '“Transport vositalari konstruktsiyasi”', 'Avtomobil va transport muhandisligi fanlari bo‘yicha elektron testlar to‘plami', 'Elektron hisoblash mashinalari uchun dasturiy ta’minotni rasmiy ro‘yxatdan o‘tkazish', '“Yonilg‘i ta’minlash tizimini servis xizmat ko‘rsatish va ta’mirlash”', '“Ishonchlilik nazariyasi va diagnostika asoslari”', '“Avtomobillarning elektr va elektron jihozlari”', '“Issiqlik texnikasi va ichki yonuv dvigatellari”', '“Texnologik jihozlarni ta’mirlash va foydalanish”', '“Texnologik jihozlarni ta’mirlash va foydalanish”', '“Bog‘larni tomchilatib sug‘orishning ilmiy asoslangan texnologiyasini ishlab chiqish”', '“Maishiy-oqova suvlaridan samarali foydalanish texnologiyasi”', '“Gidrometriya”', '“Gidravlika”', '“Detali mashin”', '“Quymakorlik texnologiyalari”', '“Tokarlik ishi”', '“Mashinasozlik texnologiyasi fanidan kurs loyihasi”', '“Gidravlika va gidropnevmo yuritmalar”', '“Mashina detallari”', '“Kesish nazariyasi va kesuvchi asboblar”', '“Avtomatlashtirilgan ishlab chiqarishning texnologik jihozlari”', '“Transport vositalarida ishlatiladigan ekspluatatsion materiallar”', '“Transport vositalarining elektr va elektron jihozlari”'],
            default => ['“Fundamentals of Mechanical Engineering Technology”', '“Design and Equipment of Road Transport Enterprises”', '“Technical Diagnostics of Automobiles”', '“Electrical Equipment and Electronic Systems of Vehicles”', '“Service and Repair of Automobile Engines”', '“Internal Combustion Engines”', '“Repair of Vehicle Parts”', '“Technical Operation and Service of Automobiles”', '“Vehicle Construction”', 'Electronic test collection for automotive and vehicle engineering subjects', 'Official registration of software for computers', '“Service and Repair of the Fuel Supply System”', '“Reliability Theory and Fundamentals of Diagnostics”', '“Electrical and Electronic Equipment of Automobiles”', '“Heat Engineering and Internal Combustion Engines”', '“Repair and Operation of Technological Equipment”', '“Repair and Operation of Technological Equipment”', '“Development of Scientifically Grounded Drip Irrigation Technology for Orchards”', '“Technology for Efficient Use of Domestic Wastewater”', '“Hydrometry”', '“Hydraulics”', '“Machine Parts”', '“Foundry Technologies”', '“Turning Practice”', '“Course Project in Mechanical Engineering Technology”', '“Hydraulics and Hydropneumatic Drives”', '“Machine Parts”', '“Cutting Theory and Cutting Tools”', '“Technological Equipment of Automated Production”', '“Operating Materials Used in Vehicles”', '“Electrical and Electronic Equipment of Vehicles”'],
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Gaffarov Hasan Ravshanovich: paxta yetishtirish zonasida yer osti tuproq qatlamini yumshatish asbobining texnologik jarayonini takomillashtirish va parametrlarini asoslash.', 'Norov Sobir Negmurodovich: yer tekislash mashinalari shnekli ish organlarining parametrlarini asoslash.', 'Xalilov Jahongir Mansur o‘g‘li: intensiv bog‘ qator oralariga ishlov beradigan takomillashgan mashina ish organlari parametrlarini asoslash.', 'Rajabov Bobir Bozorovich: keng qamrovli chizel-kultivator uchun dala yuzasini tekislaydigan va mayin tuproq qatlamini hosil qiladigan moslama ishlab chiqish.', 'O‘rinov Nasillo Fayzulloyevich: oziq-ovqat mahsulotlarini kesuvchi mashinalarning ish samaradorligini oshirish.', 'Ismoyilov Ibrohim Barotovich: junni titish mashinasi konstruksiyasini takomillashtirish va ishchi parametrlarini asoslash.', 'Duskarayev Nortoyloq: ko‘p shpindelli tokarlik stanoklari uchun sozlab turgich ishlab chiqish.', 'O‘rinov Uyg‘un Abdullayevich: texnika oliy ta’lim muassasalari va ishlab chiqarish korxonalari hamkorligida talabalarning amaliy ko‘nikmalarini rivojlantirish mexanizmini takomillashtirish.', 'Adizova Sevara Yusupovna: innovatsion yondashuv asosida talabalarni yuqori texnologik korxonalardagi kasbiy faoliyatga tayyorlash metodikasini takomillashtirish.', 'Amonov Mahmud Idrisovich: tayyor unli mahsulotlarni kesish texnologik jarayonlarini o‘rganish va qurilmalarni takomillashtirish.', 'Sohibov Ibodillo Adizmurodovich: yumshoq unli yarimtayyor mahsulotlarni kesish bosqichida qo‘llaniladigan texnologik uskunani takomillashtirish.', 'Fazliyev Jamoliddin Sharofiddinovich: Buxoro viloyati misolida bog‘larni tomchilatib sug‘orishning ilmiy asoslangan texnologiyasini ishlab chiqish.', 'Hakimova Zarina Ziyodillayevna: qishloq xo‘jaligi ekinlarini sug‘orishda maishiy oqova suvlaridan foydalanish samaradorligi.', 'Jo‘rayev Toyir Omonovich: elastik yarim tekislikda joylashgan inshootlarda turg‘un bo‘lmagan to‘lqinlar ta’siri xususiyatlari.'],
            'ru' => ['Гаффаров Хасан Равшанович: совершенствование технологического процесса и обоснование параметров орудия для рыхления подпочвенного слоя в зоне хлопководства.', 'Норов Собир Негмуродович: обоснование параметров шнековых рабочих органов машин для планировки земель.', 'Халилов Жахонгир Мансур угли: обоснование параметров рабочих органов усовершенствованной машины для обработки междурядий интенсивных садов.', 'Ражабов Бобир Бозорович: разработка и обоснование параметров устройства для широкозахватного чизель-культиватора, выравнивающего поверхность поля и создающего мелкокомковатый слой почвы.', 'Уринов Насилло Файзуллоевич: повышение эффективности машин для резки пищевых продуктов.', 'Исмоилов Иброхим Баротович: совершенствование конструкции машины для разрыхления шерсти и обоснование рабочих параметров.', 'Дускараев Нортойлок: разработка наладочного устройства для многошпиндельных токарных станков.', 'Уринов Уйгун Абдуллаевич: совершенствование механизма развития практических навыков студентов в сотрудничестве технических вузов и производственных предприятий.', 'Адизова Севара Юсуповна: совершенствование методики подготовки студентов к профессиональной деятельности на высокотехнологичных предприятиях на основе инновационного подхода.', 'Амонов Махмуд Идрисович: изучение технологических процессов резки готовых мучных изделий и совершенствование оборудования.', 'Сохибов Ибодилло Адизмуродович: совершенствование технологического оборудования, применяемого на этапе резки мягких мучных полуфабрикатов.', 'Фазлиев Жамолиддин Шарофиддинович: разработка научно обоснованной технологии капельного орошения садов на примере Бухарской области.', 'Хакимова Зарина Зиёдиллаевна: эффективность использования бытовых сточных вод при орошении сельскохозяйственных культур.', 'Жураев Тойир Омонович: особенности воздействия нестационарных волн на сооружения, расположенные в упругой полуплоскости.'],
            'ar' => ['غفاروف حسن رافشانوفيتش: تحسين العملية التقنية وتبرير معايير أداة تفكيك طبقة التربة التحتية في مناطق زراعة القطن.', 'نوروف صابر نيغمورودوفيتش: تبرير معايير الأعضاء اللولبية العاملة في آلات تسوية الأراضي.', 'خليلوف جهانغير منصور أوغلي: تبرير معايير الأعضاء العاملة لآلة مطورة لمعالجة المسافات بين صفوف البساتين المكثفة.', 'رجبوف بوبير بوزوروفيتش: تطوير جهاز للمحراث العريض يقوم بتسوية سطح الحقل وتكوين طبقة تربة ناعمة.', 'أورينوف ناسيلو فيض الله ييفيتش: رفع كفاءة آلات تقطيع المنتجات الغذائية.', 'إسماعيلوف إبراهيم باراتوفيتش: تحسين تصميم آلة تفكيك الصوف وتبرير معايير عملها.', 'دوسكاراييف نورتويلوق: تطوير جهاز ضبط للمخارط متعددة المغازل.', 'أورينوف أويغون عبد اللاييفيتش: تحسين آلية تطوير المهارات العملية للطلاب بالتعاون بين مؤسسات التعليم التقني ومؤسسات الإنتاج.', 'أديزوفا سيفارا يوسفوفنا: تحسين منهجية إعداد الطلاب للنشاط المهني في المؤسسات عالية التقنية على أساس نهج ابتكاري.', 'أمونوف محمود إدريسوفيتش: دراسة عمليات تقطيع منتجات الدقيق الجاهزة وتطوير المعدات.', 'صاحيبوف إيبوديلو أديزمورودوفيتش: تحسين المعدات التقنية المستخدمة في مرحلة تقطيع أنصاف المنتجات اللينة من الدقيق.', 'فازلييف جمال الدين شرف الدينوفيتش: تطوير تقنية علمية للري بالتنقيط في البساتين على مثال منطقة بخارى.', 'حكيموفا زارينا زيودلاييفنا: كفاءة استخدام مياه الصرف المنزلية في ري المحاصيل الزراعية.', 'جوراييف توير أومونوفيتش: خصائص تأثير الموجات غير المستقرة على المنشآت الواقعة في نصف مستوى مرن.'],
            default => ['Gaffarov Hasan Ravshanovich: improving the technological process and substantiating the parameters of a tool for loosening the subsoil layer in cotton-growing areas.', 'Norov Sobir Negmurodovich: substantiating the parameters of screw working bodies of land-leveling machines.', 'Xalilov Jahongir Mansur o‘g‘li: substantiating parameters of improved machine working bodies for inter-row cultivation in intensive orchards.', 'Rajabov Bobir Bozorovich: developing and substantiating parameters of a device for a wide-coverage chisel cultivator that levels the field surface and forms a fine soil layer.', 'Urinov Nasillo Fayzilloyevich: improving the efficiency of machines for cutting food products.', 'Ismoyilov Ibrohim Barotovich: improving the design of a wool-opening machine and substantiating its operating parameters.', 'Duskarayev Nortaylak: developing an adjustment device for multi-spindle lathes.', 'O‘rinov Uyg‘un Abdullayevich: improving mechanisms for developing students’ practical skills through cooperation between technical universities and production enterprises.', 'Adizova Sevara Yusupovna: improving methods for preparing students for professional activity in high-tech enterprises based on an innovative approach.', 'Amonov Mahmud Idrisovich: studying cutting processes for finished flour products and improving the equipment.', 'Sohibov Ibodillo Adizmurodovich: improving technological equipment used in cutting soft flour semi-finished products.', 'Fazliyev Jamoliddin Sharofiddinovich: developing scientifically grounded drip irrigation technology for orchards using Bukhara region as an example.', 'Hakimova Zarina Ziyodillayevna: efficiency of using domestic wastewater for irrigating agricultural crops.', 'Jurayev Toyir Omonovich: characteristics of non-stationary wave effects on structures located in an elastic half-plane.'],
        };
    }

    private function cooperationItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['“Avto Service Inter Millennium” MChJ', '“Vobkent Yulduzi Texservis” MChJ', '“Buxoro Avtotexxizmat” MChJ', '“Vatanparvar” tashkilotining Buxoro viloyat kengashi', '“Neftgazavtotransxizmat” UK', '“Maxsus avtotrans xizmat” MChJ', '“Buxsozta’mirservis” ishlab chiqarish kooperativi', '“Nasos ta’mirlash” MChJ', '“Trubodetal” UK', '“Alixan trans servis” MChJ', '“Usmon sher shamshod” MChJ', '“Losha qurilish sanoat komplekt” MChJ', '“Emirate Steel” MChJ', '“XXI Metall Works Buxara” MChJ', '“Ko‘hna Buxoro ta’mir” MChJ', '“Emirate Steel” xorijiy korxonasi', '“ENTER MASHINERIES SERVICE” xorijiy korxonasi', 'China Nuclear Industry 22ND Construction CO., LTD xorijiy korxonasi', '“MERMER GURLUSHYK” xorijiy korxonasi'],
            'ru' => ['ООО «Avto Service Inter Millennium»', 'ООО «Vobkent Yulduzi Texservis»', 'ООО «Buxoro Avtotexxizmat»', 'Бухарский областной совет организации «Vatanparvar»', 'Унитарное предприятие «Neftgazavtotransxizmat»', 'ООО «Maxsus avtotrans xizmat»', 'Производственный кооператив «Buxsozta’mirservis»', 'ООО «Nasos ta’mirlash»', 'Унитарное предприятие «Trubodetal»', 'ООО «Alixan trans servis»', 'ООО «Usmon sher shamshod»', 'ООО «Losha qurilish sanoat komplekt»', 'ООО «Emirate Steel»', 'ООО «XXI Metall Works Buxara»', 'ООО «Ko‘hna Buxoro ta’mir»', 'Иностранное предприятие «Emirate Steel»', 'Иностранное предприятие «ENTER MASHINERIES SERVICE»', 'Иностранное предприятие China Nuclear Industry 22ND Construction CO., LTD', 'Иностранное предприятие «MERMER GURLUSHYK»'],
            'ar' => ['شركة Avto Service Inter Millennium ذات المسؤولية المحدودة', 'شركة Vobkent Yulduzi Texservis ذات المسؤولية المحدودة', 'شركة Buxoro Avtotexxizmat ذات المسؤولية المحدودة', 'المجلس الإقليمي في بخارى لمنظمة Vatanparvar', 'المؤسسة الوحدوية Neftgazavtotransxizmat', 'شركة Maxsus avtotrans xizmat ذات المسؤولية المحدودة', 'تعاونية الإنتاج Buxsozta’mirservis', 'شركة Nasos ta’mirlash ذات المسؤولية المحدودة', 'المؤسسة الوحدوية Trubodetal', 'شركة Alixan trans servis ذات المسؤولية المحدودة', 'شركة Usmon sher shamshod ذات المسؤولية المحدودة', 'شركة Losha qurilish sanoat komplekt ذات المسؤولية المحدودة', 'شركة Emirate Steel ذات المسؤولية المحدودة', 'شركة XXI Metall Works Buxara ذات المسؤولية المحدودة', 'شركة Ko‘hna Buxoro ta’mir ذات المسؤولية المحدودة', 'المؤسسة الأجنبية Emirate Steel', 'المؤسسة الأجنبية ENTER MASHINERIES SERVICE', 'المؤسسة الأجنبية China Nuclear Industry 22ND Construction CO., LTD', 'المؤسسة الأجنبية MERMER GURLUSHYK'],
            default => ['LLC “Avto Service Inter Millennium”', 'LLC “Vobkent Yulduzi Texservis”', 'LLC “Bukhoro Avtotexxizmat”', 'Bukhara Regional Council of the “Vatanparvar” Organization', 'UE “Neftgazavtotransxizmat”', 'LLC “Maxsus avtotrans xizmat”', 'Production Cooperative “Buxsozta’mirservis”', 'LLC “Nasos ta’mirlash”', 'UE “Trubodetal”', 'LLC “Alixan trans servis”', 'LLC “Usmon sher shamshod”', 'LLC “Losha qurilish sanoat komplekt”', 'LLC “Emirate Steel”', 'LLC “XXI Metall Works Buxara”', 'LLC “Ko‘hna Buxoro ta’mir”', 'Emirate Steel foreign enterprise', 'ENTER MASHINERIES SERVICE foreign enterprise', 'China Nuclear Industry 22ND Construction CO., LTD foreign enterprise', 'MERMER GURLUSHYK foreign enterprise'],
        };
    }

    private function plansItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['2026-2030-yillarda transport vositalari muhandisligi ta’lim yo‘nalishini xalqaro standartlar asosida modernizatsiya qilish.', 'Elektr va gibrid avtomobillar, avtomobil elektronikasi, intellektual transport tizimlari va CAD/CAE modellashtirish fanlarini kuchaytirish.', 'Avtomobil diagnostikasi, elektromobillar, dvigatellar sinovi, avtomobil elektronikasi va virtual laboratoriyalarni rivojlantirish.', 'Innovatsion transport texnologiyalari ilmiy markazini tashkil etish, startaplar, grantlar va patentlarni qo‘llab-quvvatlash.', 'Transport korxonalari bilan dual ta’lim, ishlab chiqarish amaliyoti va mutaxassislarni ta’lim jarayoniga jalb qilishni kengaytirish.', 'Xorijiy universitetlar bilan qo‘shma dasturlar, akademik almashinuv va xalqaro ilmiy loyihalarni rivojlantirish.', 'Ta’lim dasturini xalqaro akkreditatsiyadan o‘tkazish va bitiruvchilar bandligini monitoring qilish.', 'Ilmiy grantlar, sanoat hamkorligi, konsalting xizmatlari va innovatsion ishlanmalarni tijoratlashtirish orqali moliyaviy barqarorlikni ta’minlash.'],
            'ru' => ['Модернизация направления «Инженерия транспортных средств» на 2026-2030 годы на основе международных стандартов.', 'Усиление дисциплин по электрическим и гибридным автомобилям, автомобильной электронике, интеллектуальным транспортным системам и CAD/CAE-моделированию.', 'Развитие лабораторий автомобильной диагностики, электромобилей, испытания двигателей, автомобильной электроники и виртуальных лабораторий.', 'Создание научного центра инновационных транспортных технологий, поддержка стартапов, грантов и патентов.', 'Расширение дуального образования, производственных практик и привлечения специалистов предприятий к образовательному процессу.', 'Развитие совместных программ, академического обмена и международных научных проектов с зарубежными университетами.', 'Прохождение международной аккредитации образовательной программы и мониторинг трудоустройства выпускников.', 'Обеспечение финансовой устойчивости через научные гранты, промышленное сотрудничество, консалтинг и коммерциализацию инноваций.'],
            'ar' => ['تحديث تخصص هندسة المركبات خلال 2026-2030 وفق المعايير الدولية.', 'تعزيز مقررات المركبات الكهربائية والهجينة وإلكترونيات السيارات وأنظمة النقل الذكية ونمذجة CAD/CAE.', 'تطوير مختبرات تشخيص السيارات والمركبات الكهربائية واختبار المحركات وإلكترونيات السيارات والمختبرات الافتراضية.', 'إنشاء مركز علمي لتقنيات النقل المبتكرة ودعم الشركات الناشئة والمنح وبراءات الاختراع.', 'توسيع التعليم المزدوج والتدريب الإنتاجي وإشراك خبراء المؤسسات في العملية التعليمية.', 'تطوير البرامج المشتركة والتبادل الأكاديمي والمشاريع العلمية الدولية مع الجامعات الأجنبية.', 'الحصول على اعتماد دولي للبرنامج التعليمي ومراقبة توظيف الخريجين.', 'ضمان الاستدامة المالية عبر المنح العلمية والتعاون الصناعي والاستشارات وتسويق الابتكارات.'],
            default => ['Modernize the Vehicle Engineering program for 2026-2030 in line with international standards.', 'Strengthen courses in electric and hybrid vehicles, automotive electronics, intelligent transport systems, and CAD/CAE modeling.', 'Develop laboratories for vehicle diagnostics, electric vehicles, engine testing, automotive electronics, and virtual simulation.', 'Establish a scientific center for innovative transport technologies and support startups, grants, and patents.', 'Expand dual education, production internships, and involvement of enterprise specialists in education.', 'Develop joint programs, academic exchange, and international scientific projects with foreign universities.', 'Pursue international accreditation of the educational program and monitor graduate employment.', 'Ensure financial stability through research grants, industry cooperation, consulting services, and commercialization of innovations.'],
        };
    }

    private function staffProfiles(): array
    {
        return [
            ['slug' => 'vehicle-engineering-automotive-transport-systems-gafforov-hasan-ravshanovich', 'names' => ['en' => 'Gaffarov Hasan Ravshanovich', 'uz' => 'Gaffarov Hasan Ravshanovich', 'ru' => 'Гаффаров Хасан Равшанович', 'ar' => 'غفاروف حسن رافشانوفيتش'], 'position' => 'Head of Department, Candidate of Technical Sciences, Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-urinov-nasillo-fayzilloyevich', 'names' => ['en' => 'Urinov Nasillo Fayzilloyevich', 'uz' => 'O‘rinov Nasillo Fayzilloyevich', 'ru' => 'Уринов Насилло Файзуллоевич', 'ar' => 'أورينوف ناسيلو فيض الله ييفيتش'], 'position' => 'Candidate of Technical Sciences, Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-norov-sobir-negmurodovich', 'names' => ['en' => 'Norov Sobir Negmurodovich', 'uz' => 'Norov Sobir Negmurodovich', 'ru' => 'Норов Собир Негмуродович', 'ar' => 'نوروف صابر نيغمورودوفيتش'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-saidova-mukhabbat-khamroyevna', 'names' => ['en' => 'Saidova Mukhabbat Khamroyevna', 'uz' => 'Saidova Muxabbat Xamroyevna', 'ru' => 'Саидова Мухаббат Хамроевна', 'ar' => 'سعيدوفا محبت خمرويفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-amonov-mahmud-idris-oglu', 'names' => ['en' => 'Amonov Mahmud Idris oglu', 'uz' => 'Amonov Mahmud Idris o‘g‘li', 'ru' => 'Амонов Махмуд Идрис угли', 'ar' => 'أمونوف محمود إدريس أوغلي'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-duskarayev-nartaylak', 'names' => ['en' => 'Duskarayev Nortaylak', 'uz' => 'Duskarayev Nortoyloq', 'ru' => 'Дускараев Нортойлок', 'ar' => 'دوسكاراييف نورتويلوق'], 'position' => 'Candidate of Technical Sciences, Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-jurayev-toyir-omonovich', 'names' => ['en' => 'Jurayev Toyir Omonovich', 'uz' => 'Jo‘rayev Toyir Omonovich', 'ru' => 'Жураев Тойир Омонович', 'ar' => 'جوراييف توير أومونوفيتش'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-adizova-sevara-yusupovna', 'names' => ['en' => 'Adizova Sevara Yusupovna', 'uz' => 'Adizova Sevara Yusupovna', 'ru' => 'Адизова Севара Юсуповна', 'ar' => 'أديزوفا سيفارا يوسفوفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-qishloq-xojaligi-fanlari-falsafa-doktori-phd-dotsent', 'names' => ['en' => 'Fazliyev Jamoliddin Sharofiddinovich', 'uz' => 'Fazliyev Jamoliddin Sharofiddinovich', 'ru' => 'Фазлиев Жамолиддин Шарофиддинович', 'ar' => 'فازلييف جمال الدين شرف الدينوفيتش'], 'position' => 'Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-hakimova-zarina-ziyodilloyevna', 'names' => ['en' => 'Hakimova Zarina Ziyodilloyevna', 'uz' => 'Hakimova Zarina Ziyodilloyevna', 'ru' => 'Хакимова Зарина Зиёдиллаевна', 'ar' => 'حكيموفا زارينا زيودلاييفنا'], 'position' => 'PhD in Agricultural Sciences, Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-xalilov-jahongir-mansur-ogli', 'names' => ['en' => 'Xalilov Jahongir Mansur o‘g‘li', 'uz' => 'Xalilov Jahongir Mansur o‘g‘li', 'ru' => 'Халилов Жахонгир Мансур угли', 'ar' => 'خليلوف جهانغير منصور أوغلي'], 'position' => 'PhD in Technical Sciences, Associate Professor'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-katta-oqituvchit', 'names' => ['en' => 'Rajabov Bobir Bozorovich', 'uz' => 'Rajabov Bobir Bozorovich', 'ru' => 'Ражабов Бобир Бозорович', 'ar' => 'رجبوف بوبير بوزوروفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-kamolov-muhiddin-qurbonovich', 'names' => ['en' => 'Kamolov Muhiddin Qurbonovich', 'uz' => 'Kamolov Muhiddin Qurbonovich', 'ru' => 'Камолов Мухиддин Курбонович', 'ar' => 'كامولوف محي الدين قربونوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-bafoyev-dustmurod-xolmurodovich', 'names' => ['en' => 'Bafoyev Dustmurod Xolmurodovich', 'uz' => 'Bafoyev Dustmurod Xolmurodovich', 'ru' => 'Бафоев Дустмурод Холмуродович', 'ar' => 'بافوييف دوستمراد خولمرودوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-yuliyev-ozod-olimovich', 'names' => ['en' => 'Yuliyev Ozod Olimovich', 'uz' => 'Yuliyev Ozod Olimovich', 'ru' => 'Юлиев Озод Олимович', 'ar' => 'يولييف أوزود أوليموفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-samandarov-ali-xayrullayevich', 'names' => ['en' => 'Samandarov Ali Xayrullayevich', 'uz' => 'Samandarov Ali Xayrullayevich', 'ru' => 'Самандаров Али Хайруллаевич', 'ar' => 'سمندروف علي خير الله ييفيتش'], 'position' => 'Assistant'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-jumayev-ulugbek-rustamovich', 'names' => ['en' => 'Jumayev Ulug‘bek Rustamovich', 'uz' => 'Jumayev Ulug‘bek Rustamovich', 'ru' => 'Жумаев Улугбек Рустамович', 'ar' => 'جمعييف أولوغبيك رستاموفيتش'], 'position' => 'Assistant'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-haydarova-nargiza-dilmurod-qizi', 'names' => ['en' => 'Haydarova Nargiza Dilmurod qizi', 'uz' => 'Haydarova Nargiza Dilmurod qizi', 'ru' => 'Хайдарова Наргиза Дилмурод кизи', 'ar' => 'حيدروفا نرجيزا ديلمورود قيزي'], 'position' => 'Assistant'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-abdullayeva-dilnavoz-xusniddinovna', 'names' => ['en' => 'Abdullayeva Dilnavoz Xusniddinovna', 'uz' => 'Abdullayeva Dilnavoz Xusniddinovna', 'ru' => 'Абдуллаева Дилнавоз Хусниддиновна', 'ar' => 'عبداللاييفا ديلنواز خوسن الدينوفنا'], 'position' => 'Assistant'],
            ['slug' => 'vehicle-engineering-automotive-transport-systems-hojiyev-oybek-odinayevich', 'names' => ['en' => 'Hojiyev Oybek Odinayevich', 'uz' => 'Hojiyev Oybek Odinayevich', 'ru' => 'Хожиев Ойбек Одинаевич', 'ar' => 'حاجييف أويبيك أوديناييفيتش'], 'position' => 'Assistant'],
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
            'Head of Department, Candidate of Technical Sciences, Professor' => ['en' => 'Head of Department, Candidate of Technical Sciences, Professor', 'uz' => 'Kafedra mudiri, texnika fanlari nomzodi, professor', 'ru' => 'Заведующий кафедрой, кандидат технических наук, профессор', 'ar' => 'رئيس القسم، مرشح في العلوم التقنية، أستاذ'],
            'Candidate of Technical Sciences, Associate Professor' => ['en' => 'Candidate of Technical Sciences, Associate Professor', 'uz' => 'Texnika fanlari nomzodi, dotsent', 'ru' => 'Кандидат технических наук, доцент', 'ar' => 'مرشح في العلوم التقنية، أستاذ مشارك'],
            'PhD, Associate Professor' => ['en' => 'PhD, Associate Professor', 'uz' => 'PhD, dotsent', 'ru' => 'PhD, доцент', 'ar' => 'دكتوراه، أستاذ مشارك'],
            'Associate Professor' => ['en' => 'Associate Professor', 'uz' => 'Dotsent', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'PhD in Agricultural Sciences, Associate Professor' => ['en' => 'PhD in Agricultural Sciences, Associate Professor', 'uz' => 'Qishloq xo‘jaligi fanlari bo‘yicha PhD, dotsent', 'ru' => 'PhD по сельскохозяйственным наукам, доцент', 'ar' => 'دكتوراه في العلوم الزراعية، أستاذ مشارك'],
            'PhD in Technical Sciences, Associate Professor' => ['en' => 'PhD in Technical Sciences, Associate Professor', 'uz' => 'Texnika fanlari bo‘yicha PhD, dotsent', 'ru' => 'PhD по техническим наукам, доцент', 'ar' => 'دكتوراه في العلوم التقنية، أستاذ مشارك'],
            'Senior Lecturer' => ['en' => 'Senior Lecturer', 'uz' => 'Katta o‘qituvchi', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'Assistant' => ['en' => 'Assistant', 'uz' => 'Assistent', 'ru' => 'Ассистент', 'ar' => 'مساعد'],
        ];

        return $map[$position][$locale] ?? $position;
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Transport vositalari muhandisligi va avtomobil transport tizimlari kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре инженерии транспортных средств и автомобильных транспортных систем в должности «{$position}».",
            'ar' => "{$name} يعمل/تعمل في قسم هندسة المركبات وأنظمة النقل البري بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Vehicle Engineering and Automotive Transport Systems.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'Kafedrada o‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد التي تدرس في القسم'],
            'publications' => ['en' => 'Textbooks, Manuals and Monographs', 'uz' => 'Darsliklar, o‘quv qo‘llanmalar va monografiyalar', 'ru' => 'Учебники, пособия и монографии', 'ar' => 'الكتب الدراسية والأدلة والمونوغرافيات'],
            'research' => ['en' => 'Ongoing Research', 'uz' => 'Joriy ilmiy tadqiqotlar', 'ru' => 'Текущие исследования', 'ar' => 'الأبحاث الجارية'],
            'cooperation' => ['en' => 'Cooperation / International Relations', 'uz' => 'Hamkorlik / xalqaro aloqalar', 'ru' => 'Сотрудничество / международные связи', 'ar' => 'التعاون / العلاقات الدولية'],
            'plans' => ['en' => 'News / Activities / Prospective Plans', 'uz' => 'Yangiliklar / faoliyat / istiqbolli rejalar', 'ru' => 'Новости / деятельность / перспективные планы', 'ar' => 'الأخبار / الأنشطة / الخطط المستقبلية'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function departmentDescriptions(): array
    {
        return [
            'en' => 'The Department of Vehicle Engineering and Automotive Transport Systems connects engineering education with applied research, industrial practice, and modern professional training.',
            'uz' => 'Transport vositalari muhandisligi va avtomobil transport tizimlari kafedrasi muhandislik ta’limini amaliy tadqiqotlar, ishlab chiqarish tajribasi va zamonaviy kasbiy tayyorgarlik bilan bog‘laydi.',
            'ru' => 'Кафедра инженерии транспортных средств и автомобильных транспортных систем объединяет инженерное образование с прикладными исследованиями, производственной практикой и современной профессиональной подготовкой.',
            'ar' => 'يربط قسم هندسة المركبات وأنظمة النقل البري التعليم الهندسي بالبحث التطبيقي والخبرة الصناعية والإعداد المهني الحديث.',
        ];
    }

    private function replaceSection(array $sections, array $replacement): array
    {
        $found = false;
        foreach ($sections as $index => $section) {
            if (($section['key'] ?? null) === $replacement['key']) {
                $sections[$index] = $replacement;
                $found = true;
            }
        }

        if (! $found) {
            $sections[] = $replacement;
        }

        return $sections;
    }

    private function locales(): array
    {
        return ['en', 'uz', 'ru', 'ar'];
    }
};
