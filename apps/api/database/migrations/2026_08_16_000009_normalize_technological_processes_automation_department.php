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

        $departmentId = DB::table('departments')->where('slug', 'technological-processes-production-automation')->value('id');
        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeDepartment((int) $departmentId);
            $this->normalizeStaff((int) $departmentId);
            $this->normalizeSections((int) $departmentId);
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
            'head_name' => 'Qobilov Hasan Xalilovich',
            'phone' => '+998 91 409 66 18',
            'email' => 'h.qobilov@mail.ru',
            'reception_time' => 'Daily 14:00-16:00',
            'updated_at' => now(),
        ]);
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteSlugs = [
            'technological-processes-production-automation-kabilov-hasan-khalilovich',
            'technological-processes-production-automation-oqituvchi-stajorstajor-tadqiqotchi',
            'technological-processes-production-automation-11-raxmatov-sanat-sultonovich',
        ];

        $ids = DB::table('staff_profiles')->whereIn('slug', $deleteSlugs)->pluck('id')->all();
        if ($ids !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
            DB::table('staff_profiles')->whereIn('id', $ids)->delete();
        }

        DB::table('staff_profiles')
            ->where('slug', 'technological-processes-production-automation-raxmatov-sanat-sultonovich-8')
            ->update(['slug' => 'technological-processes-production-automation-raxmatov-sanat-sultonovich', 'updated_at' => now()]);

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
                'research' => ['title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)],
                'cooperation' => ['title' => $this->label($locale, 'cooperation'), 'items' => $this->cooperationItems($locale)],
            ] as $key => $payload) {
                $sections = $this->replaceSection($sections, ['key' => $key, 'title' => $payload['title'], 'items' => $payload['items']]);
            }

            DB::table('department_translations')->where('id', $translation->id)->update([
                'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
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
        return match ($locale) {
            'uz' => ['Texnologik jarayonlarni boshqarish dasturiy ta’minoti', 'Dasturlash tillari asosidagi texnik ilovalar', 'Axborot-kommunikatsiya tizimlarini standartlashtirish', 'Soha texnologik o‘lchovlari va asboblari', 'Kompyuter tizimlari va tarmoqlari', 'Boshqaruv tizimlari elementlari va qurilmalari', 'Axborot jarayonlari va tizimlari', 'Mexatronikada kompyuterli boshqaruv texnologiyalari', 'Boshqaruv nazariyasi', 'Texnologik jarayonlarni modellashtirish va optimallashtirish asoslari', 'Avtomatlashtirishning texnik vositalari', 'Elektr avtomatika', 'Avtomatlashtirish tizimlarini loyihalash, montaj qilish va sozlash', 'Axborot tizimlarining instrumental vositalari', 'Axborot-kommunikatsiya tizimlarini loyihalash', 'Raqamli avtomatlashtirish va boshqaruv tizimlari', 'Ma’lumotlar bazasini boshqarish tizimlari', 'Texnologik jarayonlarni avtomatlashtirish', 'Axborot tizimlari interfeysi dizayni', 'Axborot xavfsizligi', 'Texnologik jarayonlarning avtomatlashtirilgan boshqaruv tizimlari', 'Avtomatik boshqaruv asoslari', 'Nazorat-o‘lchov asboblari', 'Avtomatika va muhandislikda raqamli usullar'],
            'ru' => ['Программное обеспечение управления технологическими процессами', 'Технические приложения на основе языков программирования', 'Стандартизация информационно-коммуникационных систем', 'Технологические измерения и приборы отрасли', 'Компьютерные системы и сети', 'Элементы и устройства систем управления', 'Информационные процессы и системы', 'Технологии компьютерного управления в мехатронике', 'Теория управления', 'Основы моделирования и оптимизации технологических процессов', 'Технические средства автоматизации', 'Электрическая автоматика', 'Проектирование, монтаж и наладка систем автоматизации', 'Инструментальные средства информационных систем', 'Проектирование информационно-коммуникационных систем', 'Цифровые системы автоматизации и управления', 'Системы управления базами данных', 'Автоматизация технологических процессов', 'Дизайн интерфейсов информационных систем', 'Информационная безопасность', 'Автоматизированные системы управления технологическими процессами', 'Основы автоматического управления', 'Контрольно-измерительные приборы', 'Цифровые методы в автоматике и инженерии'],
            'ar' => ['برمجيات التحكم في العمليات التقنية', 'تطبيقات تقنية قائمة على لغات البرمجة', 'توحيد نظم المعلومات والاتصالات', 'القياسات والأجهزة التقنية في المجال', 'أنظمة وشبكات الحاسوب', 'عناصر وأجهزة أنظمة التحكم', 'العمليات والأنظمة المعلوماتية', 'تقنيات التحكم الحاسوبي في الميكاترونكس', 'نظرية التحكم', 'أساسيات نمذجة وتحسين العمليات التقنية', 'الوسائل التقنية للأتمتة', 'الأتمتة الكهربائية', 'تصميم وتركيب وضبط أنظمة الأتمتة', 'الأدوات التطبيقية لنظم المعلومات', 'تصميم نظم المعلومات والاتصالات', 'أنظمة الأتمتة والتحكم الرقمية', 'أنظمة إدارة قواعد البيانات', 'أتمتة العمليات التقنية', 'تصميم واجهات نظم المعلومات', 'أمن المعلومات', 'أنظمة التحكم المؤتمتة للعمليات التقنية', 'أساسيات التحكم الآلي', 'أجهزة القياس والتحكم', 'الطرق الرقمية في الأتمتة والهندسة'],
            default => ['Software for Technological Process Control', 'Technical Applications Based on Programming Languages', 'Standardization of Information and Communication Systems', 'Technological Measurements and Instruments of the Field', 'Computer Systems and Networks', 'Elements and Devices of Control Systems', 'Information Processes and Systems', 'Computer Control Technologies in Mechatronics', 'Control Theory', 'Fundamentals of Modeling and Optimization of Technological Processes', 'Technical Means of Automation', 'Electrical Automation', 'Design, Installation and Adjustment of Automation Systems', 'Instrumental Tools of Information Systems', 'Design of Information and Communication Systems', 'Digital Automation and Control Systems', 'Database Management Systems', 'Automation of Technological Processes', 'Interface Design of Information Systems', 'Information Security', 'Automated Control Systems for Technological Processes', 'Fundamentals of Automatic Control', 'Control and Measuring Instruments', 'Digital Methods in Automation and Engineering'],
        };
    }

    private function masterSubjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Texnologik jarayonlarni boshqarish uchun axborot-kommunikatsiya tizimlarini ishlab chiqish texnologiyasi', 'Axborot-kommunikatsiya tizimlarini optimallashtirish va diagnostika qilish', 'Axborot-kommunikatsiya tizimlarini tadqiq qilish usullari va vositalari', 'Intellektual boshqaruv tizimlari va qaror qabul qilish', 'Axborot-kommunikatsiya tizimlari arxitekturasi va nanotexnologiyasi', 'Texnologik jarayon ma’lumotlarini qayta ishlashda o‘z-o‘zini o‘rganuvchi texnologiyalar', 'Kompyuter yordamida muhandislik loyihalash tizimlari CAD/CAE/CAM', 'Ilmiy-tadqiqot ishi va magistrlik dissertatsiyasini tayyorlash', 'Ilmiy-pedagogik ish'],
            'ru' => ['Технология разработки информационно-коммуникационных систем для управления технологическими процессами', 'Оптимизация и диагностика информационно-коммуникационных систем', 'Методы и средства исследования информационно-коммуникационных систем', 'Интеллектуальные системы управления и принятия решений', 'Архитектура и нанотехнологии информационно-коммуникационных систем', 'Самообучающиеся технологии обработки данных технологических процессов', 'Системы автоматизированного инженерного проектирования CAD/CAE/CAM', 'Научно-исследовательская работа и подготовка магистерской диссертации', 'Научно-педагогическая работа'],
            'ar' => ['تقنية تطوير نظم المعلومات والاتصالات للتحكم في العمليات التقنية', 'تحسين وتشخيص نظم المعلومات والاتصالات', 'طرق وأدوات بحث نظم المعلومات والاتصالات', 'أنظمة التحكم الذكية واتخاذ القرار', 'معمارية ونانو تكنولوجيا نظم المعلومات والاتصالات', 'تقنيات التعلم الذاتي لمعالجة بيانات العمليات التقنية', 'أنظمة التصميم الهندسي بمساعدة الحاسوب CAD/CAE/CAM', 'العمل البحثي وإعداد رسالة الماجستير', 'العمل العلمي والتربوي'],
            default => ['Technology of Developing Information and Communication Systems for Technological Process Control', 'Optimization and Diagnostics of Information and Communication Systems', 'Methods and Tools for Researching Information and Communication Systems', 'Intelligent Control Systems and Decision-Making', 'Architecture and Nanotechnology of Information and Communication Systems', 'Self-Learning Technologies for Processing Technological Process Data', 'Computer-Aided Engineering Design Systems CAD/CAE/CAM', 'Research Work and Preparation of Master’s Thesis', 'Scientific and Pedagogical Work'],
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Qishloq xo‘jaligi mahsulotlarini qayta ishlash uchun energiya tejovchi texnologiya va uskunalarni ishlab chiqish.', 'Ishlab chiqarish va iqtisodiyot tarmoqlari samaradorligini oshirishga qaratilgan axborot-kommunikatsiya tizimlarini yaratish.', 'Texnik ta’limning maxsus fanlarida raqamli texnologiyalar asosida ta’lim sifatini ta’minlash metodikasini takomillashtirish.', 'Ko‘mir briketlarini yuqori energiya samaradorligi va ekologik ko‘rsatkichlar bilan ishlab chiqarish texnologiyasi.', 'Suyultirilgan karbonat angidrid yordamida o‘simlik xomashyosidan ingredientlar olishning resurs tejamkor ekologik texnologiyasi.', 'Yuqori bosimli ekstraktlar ishlab chiqarish jarayonini boshqaruvchi axborot-kommunikatsiya tizimini ishlab chiqish.', 'ELBA Erasmus+ loyihasi doirasida Markaziy Osiyoda intellektual katta ma’lumotlar tahlili bo‘yicha o‘quv va tadqiqot markazlarini tashkil etish.'],
            'ru' => ['Разработка энергосберегающих технологий и оборудования для переработки сельскохозяйственной продукции.', 'Создание информационно-коммуникационных систем для повышения эффективности производственных и экономических отраслей.', 'Совершенствование методики обеспечения качества образования на основе цифровых технологий в специальных дисциплинах технического образования.', 'Технология производства угольных брикетов с высокой энергоэффективностью и экологическими показателями.', 'Ресурсосберегающая экологическая технология получения ингредиентов из растительного сырья с применением сжиженного углекислого газа.', 'Разработка информационно-коммуникационной системы управления процессом производства экстрактов при высоком давлении.', 'Создание учебно-исследовательских центров по интеллектуальному анализу больших данных в Центральной Азии в рамках проекта ELBA Erasmus+.'],
            'ar' => ['تطوير تقنيات ومعدات موفرة للطاقة لمعالجة المنتجات الزراعية.', 'إنشاء نظم معلومات واتصالات لرفع كفاءة قطاعات الإنتاج والاقتصاد.', 'تطوير منهجية ضمان جودة التعليم على أساس التقنيات الرقمية في التخصصات التقنية.', 'تقنية إنتاج قوالب الفحم ذات الكفاءة العالية والمؤشرات البيئية الجيدة.', 'تقنية بيئية موفرة للموارد لاستخراج المكونات من المواد النباتية باستخدام ثاني أكسيد الكربون المسال.', 'تطوير نظام معلومات واتصالات للتحكم في عملية إنتاج المستخلصات تحت الضغط العالي.', 'إنشاء مراكز تدريب وبحث لتحليل البيانات الضخمة الذكي في آسيا الوسطى ضمن مشروع ELBA Erasmus+.'],
            default => ['Development of energy-saving technologies and equipment for processing agricultural products.', 'Creation of information and communication systems aimed at improving production and economic efficiency.', 'Methodology for ensuring education quality based on digital technologies in specialized technical disciplines.', 'Technology for producing coal briquettes with high energy efficiency and environmental performance.', 'Resource-saving environmentally friendly technology for producing ingredients from plant raw materials using liquefied carbon dioxide.', 'Development of an information and communication system for controlling high-pressure extract production processes.', 'Establishment of training and research centers for intelligent big data analysis in Central Asia within the ELBA Erasmus+ project.'],
        };
    }

    private function cooperationItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['KTH Qirollik texnologiya instituti, Shvetsiya', 'Turin politexnika universiteti, Italiya', 'Lids universiteti, Angliya', 'Leven universiteti, Belgiya', 'Santyago-de-Kompostela universiteti, Ispaniya', 'Primorska universiteti, Sloveniya', 'Lintsdagi Yoxannes Kepler universiteti, Avstriya', '“Stankin” Moskva davlat texnologiya universiteti, Rossiya', 'Mogilyov davlat oziq-ovqat texnologiyalari universiteti, Belarus', 'Kostanay davlat universiteti, Qozog‘iston', 'Olmaota xalqaro axborot texnologiyalari universiteti, Qozog‘iston', 'Tempus-Mach loyihasi doirasida mechatronika bo‘yicha magistratura, doktorantura va tanlov fanlari ishlab chiqildi.', 'Erasmus+ ELBA loyihasi doirasida intellektual katta ma’lumotlar tahlili bo‘yicha kurslar va markazlar rivojlantirilmoqda.'],
            'ru' => ['Королевский технологический институт KTH, Швеция', 'Туринский политехнический университет, Италия', 'Университет Лидса, Англия', 'Лёвенский университет, Бельгия', 'Университет Сантьяго-де-Компостела, Испания', 'Университет Приморска, Словения', 'Университет Иоганна Кеплера в Линце, Австрия', 'Московский государственный технологический университет «Станкин», Россия', 'Могилевский государственный университет пищевых технологий, Беларусь', 'Костанайский государственный университет, Казахстан', 'Алматинский университет международных информационных технологий, Казахстан', 'В рамках проекта Tempus-Mach разработаны магистерские, докторские и элективные курсы по мехатронике.', 'В рамках проекта Erasmus+ ELBA развиваются курсы и центры по интеллектуальному анализу больших данных.'],
            'ar' => ['المعهد الملكي للتكنولوجيا KTH، السويد', 'جامعة تورينو التقنية، إيطاليا', 'جامعة ليدز، إنجلترا', 'جامعة لوفان، بلجيكا', 'جامعة سانتياغو دي كومبوستيلا، إسبانيا', 'جامعة بريمورسكا، سلوفينيا', 'جامعة يوهانس كيبلر في لينتس، النمسا', 'جامعة موسكو الحكومية للتكنولوجيا “ستانكين”، روسيا', 'جامعة موغيليف الحكومية لتقنيات الأغذية، بيلاروس', 'جامعة كوستاناي الحكومية، كازاخستان', 'جامعة ألماتي لتقنيات المعلومات الدولية، كازاخستان', 'ضمن مشروع Tempus-Mach تم تطوير برامج ماجستير ودكتوراه ومقررات اختيارية في الميكاترونكس.', 'ضمن مشروع Erasmus+ ELBA يتم تطوير مقررات ومراكز لتحليل البيانات الضخمة الذكي.'],
            default => ['KTH Royal Institute of Technology, Sweden', 'Turin Polytechnic University, Italy', 'University of Leeds, England', 'University of Leuven, Belgium', 'University of Santiago de Compostela, Spain', 'University of Primorska, Slovenia', 'Johannes Kepler University Linz, Austria', 'Moscow State University of Technology “Stankin”, Russia', 'Mogilev State University of Food Technologies, Belarus', 'Kostanay State University, Kazakhstan', 'Almaty University of International Information Technologies, Kazakhstan', 'The Tempus-Mach project developed master’s, doctoral, and elective courses in mechatronics.', 'The Erasmus+ ELBA project develops courses and centers for intelligent big data analysis.'],
        };
    }

    private function staffProfiles(): array
    {
        return [
            ['slug' => 'technological-processes-production-automation-qobilov-hasan-xalilovich', 'names' => ['en' => 'Qobilov Hasan Xalilovich', 'uz' => 'Qobilov Hasan Xalilovich', 'ru' => 'Кобилов Хасан Халилович', 'ar' => 'قبيلوف حسن خليلوفيتش'], 'position' => 'Head of Department'],
            ['slug' => 'technological-processes-production-automation-djuraev-xayrullo-fayzievich', 'names' => ['en' => 'Djuraev Xayrullo Fayzievich', 'uz' => 'Jo‘rayev Xayrullo Fayziyevich', 'ru' => 'Джураев Хайрулло Файзиевич', 'ar' => 'جوراييف خير الله فايزييفيتش'], 'position' => 'Professor'],
            ['slug' => 'technological-processes-production-automation-abduraxmonov-olim-rustamovich', 'names' => ['en' => 'Abduraxmonov Olim Rustamovich', 'uz' => 'Abduraxmonov Olim Rustamovich', 'ru' => 'Абдурахмонов Олим Рустамович', 'ar' => 'عبد الرحمنوف أوليم رستاموفيتش'], 'position' => 'Professor'],
            ['slug' => 'technological-processes-production-automation-usmonov-axtam-usmonovich', 'names' => ['en' => 'Usmonov Axtam Usmonovich', 'uz' => 'Usmonov Axtam Usmonovich', 'ru' => 'Усмонов Ахтам Усмонович', 'ar' => 'عثمانوف أختم عثمانوفيتش'], 'position' => 'Associate Professor'],
            ['slug' => 'technological-processes-production-automation-abidov-kamildjan-zarifovich', 'names' => ['en' => 'Abidov Kamildjan Zarifovich', 'uz' => 'Abidov Kamildjan Zarifovich', 'ru' => 'Абидов Камилджан Зарифович', 'ar' => 'عبيدوف كاميلجان زاريفوفيتش'], 'position' => 'Acting Associate Professor'],
            ['slug' => 'technological-processes-production-automation-salieva-olima-kamalovna', 'names' => ['en' => 'Salieva Olima Kamalovna', 'uz' => 'Saliyeva Olima Kamalovna', 'ru' => 'Салиева Олима Камаловна', 'ar' => 'سالييفا أوليما كامالوفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'technological-processes-production-automation-ibragimov-ulugbek-murodilloevich', 'names' => ['en' => 'Ibragimov Ulug‘bek Murodilloevich', 'uz' => 'Ibragimov Ulug‘bek Murodilloyevich', 'ru' => 'Ибрагимов Улугбек Муродиллоевич', 'ar' => 'إبراهيموف أولوغبيك مراديللاييفيتش'], 'position' => 'Acting Associate Professor'],
            ['slug' => 'technological-processes-production-automation-abduraxmonova-muqadas-irismatovna', 'names' => ['en' => 'Abduraxmonova Muqadas Irismatovna', 'uz' => 'Abduraxmonova Muqadas Irismatovna', 'ru' => 'Абдурахмонова Мукаддас Ирисматовна', 'ar' => 'عبد الرحمنوفا مقدس إيريسماتوفنا'], 'position' => 'Senior Lecturer'],
            ['slug' => 'technological-processes-production-automation-ergashev-baxtiyor-tilavovich', 'names' => ['en' => 'Ergashev Baxtiyor Tilavovich', 'uz' => 'Ergashev Baxtiyor Tilavovich', 'ru' => 'Эргашев Бахтиёр Тилавович', 'ar' => 'إرغاشيف بختيار تيلافوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'technological-processes-production-automation-rasulov-shuhrat-xojaqulovich', 'names' => ['en' => 'Rasulov Shuhrat Xo‘jaqulovich', 'uz' => 'Rasulov Shuhrat Xo‘jaqulovich', 'ru' => 'Расулов Шухрат Хужакулович', 'ar' => 'رسولوف شوخرات خوجاقولوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'technological-processes-production-automation-ismoyilov-hayot-barotovich', 'names' => ['en' => 'Ismoyilov Hayot Barotovich', 'uz' => 'Ismoyilov Hayot Barotovich', 'ru' => 'Исмоилов Хаёт Баротович', 'ar' => 'إسماعيلوف حيات باراتوفيتش'], 'position' => 'Senior Lecturer, Doctoral Student'],
            ['slug' => 'technological-processes-production-automation-xalilov-fayoz-vaxobjonovich', 'names' => ['en' => 'Xalilov Fayoz Vaxobjonovich', 'uz' => 'Xalilov Fayoz Vaxobjonovich', 'ru' => 'Халилов Фаёз Вахобжонович', 'ar' => 'خليلوف فياض وهابجونوفيتش'], 'position' => 'Senior Lecturer, Doctoral Student'],
            ['slug' => 'technological-processes-production-automation-raxmatov-sanat-sultonovich', 'names' => ['en' => 'Raxmatov San’at Sultonovich', 'uz' => 'Raxmatov San’at Sultonovich', 'ru' => 'Рахматов Санъат Султонович', 'ar' => 'رحمتوف صنعت سلطانوفتيتش'], 'position' => 'Assistant'],
            ['slug' => 'technological-processes-production-automation-ismatova-nafisa', 'names' => ['en' => 'Ismatova Nafisa', 'uz' => 'Ismatova Nafisa', 'ru' => 'Исматова Нафиса', 'ar' => 'إسماتوفا نفيسة'], 'position' => 'Trainee Teacher'],
            ['slug' => 'technological-processes-production-automation-xojanazarov-zayniddin-rashidovich', 'names' => ['en' => 'Xo‘janazarov Zayniddin Rashidovich', 'uz' => 'Xo‘janazarov Zayniddin Rashidovich', 'ru' => 'Хужаназаров Зайниддин Рашидович', 'ar' => 'خوجانازاروف زين الدين رشيدوفيتش'], 'position' => 'Doctoral Student'],
            ['slug' => 'technological-processes-production-automation-xojiev-azizjon-kaimovich', 'names' => ['en' => 'Xojiev Azizjon Kaimovich', 'uz' => 'Xojiyev Azizjon Kaimovich', 'ru' => 'Хожиев Азизжон Каимович', 'ar' => 'خوجييف عزيزجون قايموفيتش'], 'position' => 'Doctoral Student'],
            ['slug' => 'technological-processes-production-automation-uvayzov-saidjon-komilovich', 'names' => ['en' => 'Uvayzov Saidjon Komilovich', 'uz' => 'Uvayzov Saidjon Komilovich', 'ru' => 'Увайзов Саиджон Комилович', 'ar' => 'أوفايزوف سعيدجون كوميلوفيتش'], 'position' => 'Doctoral Student'],
            ['slug' => 'technological-processes-production-automation-ibragimov-shohruh-ramazonovich', 'names' => ['en' => 'Ibragimov Shohruh Ramazonovich', 'uz' => 'Ibragimov Shohruh Ramazonovich', 'ru' => 'Ибрагимов Шохрух Рамазонович', 'ar' => 'إبراهيموف شاهروخ رمضانوفитش'], 'position' => 'Doctoral Student'],
            ['slug' => 'technological-processes-production-automation-adizova-madina-rozievna', 'names' => ['en' => 'Adizova Madina Ro‘zievna', 'uz' => 'Adizova Madina Ro‘ziyevna', 'ru' => 'Адизова Мадина Рузиевна', 'ar' => 'أديزوفا مادينا روزييفنا'], 'position' => 'Trainee Teacher'],
            ['slug' => 'technological-processes-production-automation-mizomov-muxammad-saydullo-ogli', 'names' => ['en' => 'Mizomov Muxammad Saydullo o‘g‘li', 'uz' => 'Mizomov Muxammad Saydullo o‘g‘li', 'ru' => 'Мизомов Мухаммад Сайдулло угли', 'ar' => 'ميزوموف محمد سعيد الله أوغلي'], 'position' => 'Doctoral Student'],
            ['slug' => 'technological-processes-production-automation-qazoqov-jorabek-roziqovich', 'names' => ['en' => 'Qazoqov Jo‘rabek Roziqovich', 'uz' => 'Qazoqov Jo‘rabek Roziqovich', 'ru' => 'Казоков Журабек Розикович', 'ar' => 'قازاقوف جورابيك روزيقوفيتش'], 'position' => 'Assistant'],
            ['slug' => 'technological-processes-production-automation-jalolov-tursunbek-sadriddinovich', 'names' => ['en' => 'Jalolov Tursunbek Sadriddinovich', 'uz' => 'Jalolov Tursunbek Sadriddinovich', 'ru' => 'Жалолов Турсунбек Садриддинович', 'ar' => 'جلالوف تورسونبيك صدر الدينوفيتش'], 'position' => 'Trainee Teacher'],
            ['slug' => 'technological-processes-production-automation-oktamova-shohsanam-hakimovna', 'names' => ['en' => 'O‘ktamova Shohsanam Hakimovna', 'uz' => 'O‘ktamova Shohsanam Hakimovna', 'ru' => 'Уктамова Шохсанам Хакимовна', 'ar' => 'أوكتاموفا شوحسانام حكيموفنا'], 'position' => 'Trainee Research Teacher'],
            ['slug' => 'technological-processes-production-automation-safarova-dilshoda-nodirovna', 'names' => ['en' => 'Safarova Dilshoda Nodirovna', 'uz' => 'Safarova Dilshoda Nodirovna', 'ru' => 'Сафарова Дилшода Нодировна', 'ar' => 'سفاروفا ديلشودا نوديروفنا'], 'position' => 'Trainee Teacher'],
            ['slug' => 'technological-processes-production-automation-saidov-said-rustamovich', 'names' => ['en' => 'Saidov Said Rustamovich', 'uz' => 'Saidov Said Rustamovich', 'ru' => 'Саидов Саид Рустамович', 'ar' => 'سعيدوف سعيد رستاموفيتش'], 'position' => 'Trainee Teacher'],
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
            'Professor' => ['en' => 'Professor', 'uz' => 'Professor', 'ru' => 'Профессор', 'ar' => 'أستاذ'],
            'Associate Professor' => ['en' => 'Associate Professor', 'uz' => 'Dotsent', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'Acting Associate Professor' => ['en' => 'Acting Associate Professor', 'uz' => 'Dotsent vazifasini bajaruvchi', 'ru' => 'Исполняющий обязанности доцента', 'ar' => 'قائم بأعمال أستاذ مشارك'],
            'Senior Lecturer' => ['en' => 'Senior Lecturer', 'uz' => 'Katta o‘qituvchi', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'Senior Lecturer, Doctoral Student' => ['en' => 'Senior Lecturer, Doctoral Student', 'uz' => 'Katta o‘qituvchi, doktorant', 'ru' => 'Старший преподаватель, докторант', 'ar' => 'محاضر أول، طالب دكتوراه'],
            'Assistant' => ['en' => 'Assistant', 'uz' => 'Assistent', 'ru' => 'Ассистент', 'ar' => 'مساعد'],
            'Trainee Teacher' => ['en' => 'Trainee Teacher', 'uz' => 'O‘qituvchi-stajor', 'ru' => 'Преподаватель-стажер', 'ar' => 'مدرس متدرب'],
            'Trainee Research Teacher' => ['en' => 'Trainee Research Teacher', 'uz' => 'O‘qituvchi-stajor, stajor-tadqiqotchi', 'ru' => 'Преподаватель-стажер, стажер-исследователь', 'ar' => 'مدرس متدرب وباحث متدرب'],
            'Doctoral Student' => ['en' => 'Doctoral Student', 'uz' => 'Doktorant', 'ru' => 'Докторант', 'ar' => 'طالب دكتوراه'],
        ];

        return $map[$position][$locale] ?? $position;
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Texnologik jarayonlar va ishlab chiqarishni avtomatlashtirish kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре автоматизации технологических процессов и производства в должности «{$position}».",
            'ar' => "{$name} يعمل/تعمل في قسم أتمتة العمليات التقنية والإنتاج بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Technological Processes and Production Automation.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'Kafedrada o‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد التي تدرس في القسم'],
            'research' => ['en' => 'Ongoing Research', 'uz' => 'Joriy ilmiy tadqiqotlar', 'ru' => 'Текущие исследования', 'ar' => 'الأبحاث الجارية'],
            'cooperation' => ['en' => 'Cooperation / International Relations', 'uz' => 'Hamkorlik / xalqaro aloqalar', 'ru' => 'Сотрудничество / международные связи', 'ar' => 'التعاون / العلاقات الدولية'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
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
