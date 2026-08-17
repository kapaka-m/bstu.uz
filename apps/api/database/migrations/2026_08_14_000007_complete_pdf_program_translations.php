<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->programNames() as $code => $variants) {
            foreach ($variants as $englishName => $names) {
                $program = DB::table('programs as p')
                    ->join('program_translations as pt', 'pt.program_id', '=', 'p.id')
                    ->where('p.official_code', $code)
                    ->where('pt.locale', 'en')
                    ->where('p.is_active', true)
                    ->get(['p.id', 'p.degree', 'p.duration_years', 'pt.name'])
                    ->first(fn ($row) => $this->normalize($row->name) === $this->normalize($englishName));

                if (! $program) {
                    continue;
                }

                $faculty = DB::table('programs as p')
                    ->join('faculties as f', 'f.id', '=', 'p.faculty_id')
                    ->where('p.id', $program->id)
                    ->value('f.slug');

                $department = DB::table('programs as p')
                    ->join('departments as d', 'd.id', '=', 'p.department_id')
                    ->where('p.id', $program->id)
                    ->value('d.slug');

                foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                    $name = $locale === 'en' ? $englishName : $names[$locale];
                    $degree = $this->degree((string) ($program->degree ?: 'bachelor'), $locale);
                    $duration = (int) ($program->duration_years ?: 4);
                    $facultyName = $this->facultyName($faculty, $locale);
                    $departmentName = $this->departmentName($department, $locale);
                    $description = $this->description($name, $facultyName, $departmentName, $degree, $duration, $locale);

                    DB::table('program_translations')->updateOrInsert(
                        ['program_id' => $program->id, 'locale' => $locale],
                        [
                            'name' => $name,
                            'description' => $description,
                            'requirements' => $this->requirements($locale),
                            'documents' => $this->documents($locale),
                            'curriculum_summary' => $this->curriculum($duration, $locale),
                            'career_opportunities' => $this->careers($departmentName, $locale),
                            'meta_title' => $code.' - '.$name,
                            'meta_description' => Str::limit($description, 240, ''),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Program translations are canonicalized from the official 2026/2027 PDF.
    }

    private function normalize(string $value): string
    {
        $value = strtolower($value);
        $value = str_replace(['&', '–', '—', '-', '(', ')', ',', ':'], ' ', $value);
        $value = preg_replace('/\b(and|of|the|in|by|their|for)\b/', ' ', $value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function description(string $name, string $faculty, string $department, string $degree, int $duration, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} — {$faculty} tarkibidagi {$duration} yillik {$degree} dasturi. Dastur {$department} bilan bog‘langan bo‘lib, nazariy ta’lim, laboratoriya ishlari, amaliy mashg‘ulotlar va sohaga yo‘naltirilgan tayyorgarlikni birlashtiradi.",
            'ru' => "{$name} — {$duration}-летняя программа уровня «{$degree}» в составе {$faculty}. Программа связана с кафедрой «{$department}» и объединяет теоретическое обучение, лабораторные работы, практическую подготовку и отраслевую направленность.",
            'ar' => "{$name} هو برنامج {$degree} مدته {$duration} سنوات ضمن {$faculty}. يرتبط البرنامج بـ {$department} ويجمع بين الدراسة النظرية والعمل المخبري والتدريب العملي والتعليم الموجه لاحتياجات سوق العمل.",
            default => "{$name} is a {$duration}-year {$degree} program in {$faculty}. It is connected with {$department} and combines theoretical study, laboratory work, practical training, and industry-oriented learning.",
        };
    }

    private function requirements(string $locale): string
    {
        return match ($locale) {
            'uz' => "O‘rta ta’lim yoki unga tenglashtirilgan hujjat\nUniversitet qabul talablariga muvofiq ariza\nTanlangan yo‘nalish bo‘yicha belgilangan kirish talablari",
            'ru' => "Документ о среднем образовании или его эквивалент\nЗаявление согласно правилам приема университета\nВступительные требования по выбранному направлению",
            'ar' => "شهادة التعليم الثانوي أو ما يعادلها\nطلب تقديم وفق قواعد القبول في الجامعة\nاستيفاء متطلبات القبول الخاصة بالتخصص المختار",
            default => "Secondary education certificate or equivalent\nApplication according to university admission rules\nEntrance requirements for the selected program",
        };
    }

    private function documents(string $locale): string
    {
        return match ($locale) {
            'uz' => "Pasport yoki shaxsni tasdiqlovchi hujjat\nTa’lim to‘g‘risidagi hujjat va baholar ilovasi\nFotosurat va talab etilgan ariza hujjatlari",
            'ru' => "Паспорт или удостоверение личности\nДокумент об образовании и приложение с оценками\nФотография и необходимые документы для подачи заявления",
            'ar' => "جواز السفر أو وثيقة الهوية\nوثيقة التعليم وكشف الدرجات\nصورة شخصية والوثائق المطلوبة للتقديم",
            default => "Passport or identity document\nEducation certificate and transcript\nPhoto and required application documents",
        };
    }

    private function curriculum(int $duration, string $locale): string
    {
        return match ($locale) {
            'uz' => "O‘qish muddati: {$duration} yil\nAsosiy fanlar va mutaxassislik modullari\nLaboratoriya va amaliy mashg‘ulotlar\nIshlab chiqarish amaliyoti va bitiruv loyihasi",
            'ru' => "Срок обучения: {$duration} года\nБазовые дисциплины и профильные модули\nЛабораторные и практические занятия\nПроизводственная практика и выпускной проект",
            'ar' => "مدة الدراسة: {$duration} سنوات\nمواد أساسية ووحدات تخصصية\nتدريب مخبري وعملي\nتدريب صناعي ومشروع تخرج",
            default => "Duration of study: {$duration} years\nCore subjects and specialization modules\nLaboratory and practical classes\nIndustrial internship and graduation project",
        };
    }

    private function careers(string $department, string $locale): string
    {
        return match ($locale) {
            'uz' => "Mutaxassis muhandis yoki texnolog\n{$department} yo‘nalishidagi korxonalar mutaxassisi\nLoyiha va ishlab chiqarish jarayonlari koordinatori\nIlmiy-tadqiqot va innovatsion loyihalar ishtirokchisi",
            'ru' => "Инженер-специалист или технолог\nСпециалист предприятий по направлению «{$department}»\nКоординатор проектных и производственных процессов\nУчастник научно-исследовательских и инновационных проектов",
            'ar' => "مهندس أو تقني متخصص\nمتخصص في المؤسسات المرتبطة بمجال {$department}\nمنسق للعمليات الإنتاجية والمشروعات\nمشارك في مشروعات البحث والابتكار",
            default => "Specialist engineer or technologist\nIndustry specialist in {$department}\nProject and production process coordinator\nResearch and innovation project participant",
        };
    }

    private function degree(string $degree, string $locale): string
    {
        return match (strtolower($degree)) {
            'master' => ['en' => 'master', 'uz' => 'magistratura', 'ru' => 'магистратура', 'ar' => 'ماجستير'][$locale],
            'phd', 'doctoral' => ['en' => 'doctoral', 'uz' => 'doktorantura', 'ru' => 'докторантура', 'ar' => 'دكتوراه'][$locale],
            default => ['en' => 'bachelor', 'uz' => 'bakalavriat', 'ru' => 'бакалавриат', 'ar' => 'بكالوريوس'][$locale],
        };
    }

    private function facultyName(?string $slug, string $locale): string
    {
        $names = [
            'faculty-of-engineering' => ['en' => 'Faculty of Engineering', 'uz' => 'Muhandislik fakulteti', 'ru' => 'Инженерный факультет', 'ar' => 'كلية الهندسة'],
            'faculty-of-technology' => ['en' => 'Faculty of Technology', 'uz' => 'Texnologiya fakulteti', 'ru' => 'Факультет технологий', 'ar' => 'كلية التكنولوجيا'],
            'faculty-of-natural-resources-management' => ['en' => 'Faculty of Natural Resources Management', 'uz' => 'Tabiiy resurslarni boshqarish fakulteti', 'ru' => 'Факультет управления природными ресурсами', 'ar' => 'كلية إدارة الموارد الطبيعية'],
            'faculty-of-service-and-digitalization' => ['en' => 'Faculty of Service and Digitalization', 'uz' => 'Servis va raqamlashtirish fakulteti', 'ru' => 'Факультет сервиса и цифровизации', 'ar' => 'كلية الخدمات والرقمنة'],
        ];

        return $names[$slug][$locale] ?? ($names[$slug]['en'] ?? 'Bukhara State Technical University');
    }

    private function departmentName(?string $slug, string $locale): string
    {
        $names = [
            'light-industry-engineering-and-design' => ['en' => 'Light Industry Engineering and Design', 'uz' => 'Yengil sanoat muhandisligi va dizayn kafedrasi', 'ru' => 'Кафедра инженерии и дизайна легкой промышленности', 'ar' => 'قسم هندسة وتصميم الصناعات الخفيفة'],
            'economics-and-management' => ['en' => 'Economics and Management', 'uz' => 'Iqtisodiyot va menejment kafedrasi', 'ru' => 'Кафедра экономики и менеджмента', 'ar' => 'قسم الاقتصاد والإدارة'],
            'chemical-technology' => ['en' => 'Chemical Technology', 'uz' => 'Kimyo texnologiyasi kafedrasi', 'ru' => 'Кафедра химической технологии', 'ar' => 'قسم التكنولوجيا الكيميائية'],
            'industrial-ecology-hydrogeology' => ['en' => 'Industrial Ecology and Hydrogeology', 'uz' => 'Sanoat ekologiyasi va gidrogeologiya kafedrasi', 'ru' => 'Кафедра промышленной экологии и гидрогеологии', 'ar' => 'قسم البيئة الصناعية والهيدروجيولوجيا'],
            'information-and-communication-technologies' => ['en' => 'Information and Communication Technologies', 'uz' => 'Axborot-kommunikatsiya texnologiyalari kafedrasi', 'ru' => 'Кафедра информационно-коммуникационных технологий', 'ar' => 'قسم تكنولوجيا المعلومات والاتصالات'],
            'electrical-power-engineering' => ['en' => 'Electrical and Power Engineering', 'uz' => 'Elektr energetikasi kafedrasi', 'ru' => 'Кафедра электроэнергетики', 'ar' => 'قسم الهندسة الكهربائية والطاقة'],
            'hydrotechnical-structures-pump-stations' => ['en' => 'Hydraulic Structures and Pumping Stations', 'uz' => 'Gidrotexnika inshootlari va nasos stansiyalari kafedrasi', 'ru' => 'Кафедра гидротехнических сооружений и насосных станций', 'ar' => 'قسم المنشآت الهيدروليكية ومحطات الضخ'],
            'technological-processes-production-automation' => ['en' => 'Automation of Technological Processes and Production', 'uz' => 'Texnologik jarayonlar va ishlab chiqarishni avtomatlashtirish kafedrasi', 'ru' => 'Кафедра автоматизации технологических процессов и производств', 'ar' => 'قسم أتمتة العمليات التكنولوجية والإنتاج'],
            'vehicle-engineering-automotive-transport-systems' => ['en' => 'Vehicle Engineering', 'uz' => 'Transport vositalari muhandisligi kafedrasi', 'ru' => 'Кафедра транспортного машиностроения', 'ar' => 'قسم هندسة المركبات'],
            'agricultural-products-storage-oil-fat-technology' => ['en' => 'Agricultural Products Storage and Oil-Fat Technology', 'uz' => 'Qishloq xo‘jaligi mahsulotlarini saqlash va moy-yog‘ texnologiyasi kafedrasi', 'ru' => 'Кафедра хранения сельхозпродукции и масложировой технологии', 'ar' => 'قسم تخزين المنتجات الزراعية وتكنولوجيا الزيوت والدهون'],
            'oil-gas-refining-technology' => ['en' => 'Oil and Gas Refining Technology', 'uz' => 'Neft va gazni qayta ishlash texnologiyasi kafedrasi', 'ru' => 'Кафедра технологии переработки нефти и газа', 'ar' => 'قسم تكنولوجيا تكرير النفط والغاز'],
            'oil-gas-engineering-upstream-downstream' => ['en' => 'Oil and Gas Engineering', 'uz' => 'Neft va gaz ishi kafedrasi', 'ru' => 'Кафедра нефтегазового дела', 'ar' => 'قسم هندسة النفط والغاز'],
            'technological-machines-equipment' => ['en' => 'Technological Machines and Equipment', 'uz' => 'Texnologik mashinalar va jihozlar kafedrasi', 'ru' => 'Кафедра технологических машин и оборудования', 'ar' => 'قسم الآلات والمعدات التكنولوجية'],
            'architecture' => ['en' => 'Architecture', 'uz' => 'Arxitektura kafedrasi', 'ru' => 'Кафедра архитектуры', 'ar' => 'قسم العمارة'],
            'civil-engineering' => ['en' => 'Civil Engineering', 'uz' => 'Qurilish muhandisligi kafedrasi', 'ru' => 'Кафедра гражданского строительства', 'ar' => 'قسم الهندسة المدنية'],
            'agricultural-water-resources-engineering-technologies' => ['en' => 'Agricultural and Water Management Engineering Technologies', 'uz' => 'Qishloq va suv xo‘jaligi muhandislik texnologiyalari kafedrasi', 'ru' => 'Кафедра инженерных технологий сельского и водного хозяйства', 'ar' => 'قسم تقنيات هندسة الزراعة وإدارة المياه'],
            'land-resources-management-state-land-cadastres' => ['en' => 'Land Resource Management and State Cadastres', 'uz' => 'Yer resurslarini boshqarish va davlat kadastrlari kafedrasi', 'ru' => 'Кафедра управления земельными ресурсами и государственных кадастров', 'ar' => 'قسم إدارة موارد الأراضي والسجلات العقارية الحكومية'],
            'irrigation-melioration' => ['en' => 'Irrigation and Land Reclamation', 'uz' => 'Irrigatsiya va melioratsiya kafedrasi', 'ru' => 'Кафедра ирригации и мелиорации', 'ar' => 'قسم الري واستصلاح الأراضي'],
        ];

        return $names[$slug][$locale] ?? ($names[$slug]['en'] ?? 'academic department');
    }

    private function programNames(): array
    {
        return [
            '60210400' => [
                'Design: Footwear and Accessories Design' => ['uz' => 'Dizayn: poyabzal va aksessuarlar dizayni', 'ru' => 'Дизайн: дизайн обуви и аксессуаров', 'ar' => 'التصميم: تصميم الأحذية والإكسسوارات'],
                'Design: Clothing and Textile Design' => ['uz' => 'Dizayn: kiyim va to‘qimachilik dizayni', 'ru' => 'Дизайн: дизайн одежды и текстиля', 'ar' => 'التصميم: تصميم الملابس والمنسوجات'],
                'Design: Textile and Light Industry Design' => ['uz' => 'Dizayn: to‘qimachilik va yengil sanoat dizayni', 'ru' => 'Дизайн: дизайн текстиля и легкой промышленности', 'ar' => 'التصميم: تصميم المنسوجات والصناعات الخفيفة'],
            ],
            '60410100' => ['Economics' => ['uz' => 'Iqtisodiyot', 'ru' => 'Экономика', 'ar' => 'الاقتصاد']],
            '60410200' => ['Accounting' => ['uz' => 'Buxgalteriya hisobi', 'ru' => 'Бухгалтерский учет', 'ar' => 'المحاسبة']],
            '60410500' => ['Finance and Financial Technologies' => ['uz' => 'Moliya va moliyaviy texnologiyalar', 'ru' => 'Финансы и финансовые технологии', 'ar' => 'المالية والتقنيات المالية']],
            '60410800' => ['Management' => ['uz' => 'Menejment', 'ru' => 'Менеджмент', 'ar' => 'الإدارة']],
            '60411200' => ['Marketing' => ['uz' => 'Marketing va bozor tadqiqotlari', 'ru' => 'Маркетинг', 'ar' => 'التسويق']],
            '60520200' => ['Ecology and Environmental Protection' => ['uz' => 'Ekologiya va atrof-muhit muhofazasi', 'ru' => 'Экология и охрана окружающей среды', 'ar' => 'البيئة وحماية البيئة']],
            '60530400' => ['Hydrology' => ['uz' => 'Gidrologiya', 'ru' => 'Гидрология', 'ar' => 'الهيدرولوجيا']],
            '60610100' => ['Information Systems and Technologies' => ['uz' => 'Axborot tizimlari va texnologiyalari', 'ru' => 'Информационные системы и технологии', 'ar' => 'نظم وتكنولوجيا المعلومات']],
            '60610300' => ['Computer Engineering' => ['uz' => 'Kompyuter muhandisligi', 'ru' => 'Компьютерная инженерия', 'ar' => 'هندسة الحاسوب']],
            '60610400' => ['Software Engineering' => ['uz' => 'Dasturiy injiniring', 'ru' => 'Программная инженерия', 'ar' => 'هندسة البرمجيات']],
            '60610500' => ['Artificial Intelligence' => ['uz' => 'Sun’iy intellekt', 'ru' => 'Искусственный интеллект', 'ar' => 'الذكاء الاصطناعي']],
            '60710100' => ['Chemical Engineering' => ['uz' => 'Kimyoviy muhandislik', 'ru' => 'Химическая инженерия', 'ar' => 'الهندسة الكيميائية']],
            '60710200' => ['Biotechnology' => ['uz' => 'Biotexnologiya', 'ru' => 'Биотехнология', 'ar' => 'التكنولوجيا الحيوية']],
            '60710400' => ['Energy Engineering' => ['uz' => 'Energetika muhandisligi', 'ru' => 'Энергетическая инженерия', 'ar' => 'هندسة الطاقة']],
            '60710500' => ['Electrical Engineering' => ['uz' => 'Elektr muhandisligi', 'ru' => 'Электротехника', 'ar' => 'الهندسة الكهربائية']],
            '60710600' => ['Hydropower Engineering' => ['uz' => 'Gidroenergetika muhandisligi', 'ru' => 'Гидроэнергетическая инженерия', 'ar' => 'هندسة الطاقة الكهرومائية']],
            '60710800' => ['Metrology and Standardization' => ['uz' => 'Metrologiya va standartlashtirish', 'ru' => 'Метрология и стандартизация', 'ar' => 'المترولوجيا والتقييس']],
            '60710900' => ['Automation of Technological Processes and Production' => ['uz' => 'Texnologik jarayonlar va ishlab chiqarishni avtomatlashtirish', 'ru' => 'Автоматизация технологических процессов и производств', 'ar' => 'أتمتة العمليات التكنولوجية والإنتاج']],
            '60711000' => ['Mechatronics and Robotics' => ['uz' => 'Mexatronika va robototexnika', 'ru' => 'Мехатроника и робототехника', 'ar' => 'الميكاترونكس والروبوتات']],
            '60711100' => ['Biomedical Engineering' => ['uz' => 'Biotibbiyot muhandisligi', 'ru' => 'Биомедицинская инженерия', 'ar' => 'الهندسة الطبية الحيوية']],
            '60711400' => ['Vehicle Engineering' => ['uz' => 'Transport vositalari muhandisligi', 'ru' => 'Транспортное машиностроение', 'ar' => 'هندسة المركبات']],
            '60712000' => ['Renewable Energy Sources' => ['uz' => 'Qayta tiklanuvchi energiya manbalari', 'ru' => 'Возобновляемые источники энергии', 'ar' => 'مصادر الطاقة المتجددة']],
            '60720100' => ['Food Technology' => ['uz' => 'Oziq-ovqat texnologiyasi', 'ru' => 'Пищевая технология', 'ar' => 'تكنولوجيا الأغذية']],
            '60720200' => ['Perfumery and Cosmetic Products Technology' => ['uz' => 'Parfyumeriya va kosmetika mahsulotlari texnologiyasi', 'ru' => 'Технология парфюмерных и косметических продуктов', 'ar' => 'تكنولوجيا منتجات العطور ومستحضرات التجميل']],
            '60720400' => ['Technological Machines and Equipment' => ['uz' => 'Texnologik mashinalar va jihozlar', 'ru' => 'Технологические машины и оборудование', 'ar' => 'الآلات والمعدات التكنولوجية']],
            '60720500' => ['Light Industry Production Technology' => ['uz' => 'Yengil sanoat ishlab chiqarish texnologiyasi', 'ru' => 'Технология производства легкой промышленности', 'ar' => 'تكنولوجيا إنتاج الصناعات الخفيفة']],
            '60720600' => ['Oil and Oil-Gas Processing Technology' => ['uz' => 'Neft va neft-gazni qayta ishlash texnologiyasi', 'ru' => 'Технология переработки нефти и нефтегаза', 'ar' => 'تكنولوجيا معالجة النفط والنفط والغاز']],
            '60720700' => ['Light Industry Engineering' => ['uz' => 'Yengil sanoat muhandisligi', 'ru' => 'Инженерия легкой промышленности', 'ar' => 'هندسة الصناعات الخفيفة']],
            '60720900' => ['Geology, Prospecting and Exploration of Mineral Resources' => ['uz' => 'Geologiya, foydali qazilmalarni qidirish va razvedka qilish', 'ru' => 'Геология, поиски и разведка полезных ископаемых', 'ar' => 'الجيولوجيا والتنقيب واستكشاف الموارد المعدنية']],
            '60721100' => ['Oil and Gas Engineering' => ['uz' => 'Neft va gaz muhandisligi', 'ru' => 'Нефтегазовая инженерия', 'ar' => 'هندسة النفط والغاز']],
            '60721500' => ['Geodesy and Geomatics' => ['uz' => 'Geodeziya va geomatika', 'ru' => 'Геодезия и геоматика', 'ar' => 'الجيوديسيا والجيوماتكس']],
            '60721600' => ['Cartography and Remote Sensing' => ['uz' => 'Kartografiya va masofadan zondlash', 'ru' => 'Картография и дистанционное зондирование', 'ar' => 'رسم الخرائط والاستشعار عن بعد']],
            '60721700' => ['Cadastre' => ['uz' => 'Kadastr', 'ru' => 'Кадастр', 'ar' => 'السجل العقاري']],
            '60721800' => ['Manufacturing Engineering' => ['uz' => 'Ishlab chiqarish muhandisligi', 'ru' => 'Производственная инженерия', 'ar' => 'هندسة التصنيع']],
            '60730100' => ['Architecture' => ['uz' => 'Arxitektura', 'ru' => 'Архитектура', 'ar' => 'العمارة']],
            '60730300' => ['Civil Engineering' => ['uz' => 'Qurilish muhandisligi', 'ru' => 'Гражданское строительство', 'ar' => 'الهندسة المدنية']],
            '60730500' => ['Road Engineering' => ['uz' => 'Yo‘l muhandisligi', 'ru' => 'Дорожная инженерия', 'ar' => 'هندسة الطرق']],
            '60730600' => ['Hydraulic and Geotechnical Engineering' => ['uz' => 'Gidravlika va geotexnika muhandisligi', 'ru' => 'Гидравлическая и геотехническая инженерия', 'ar' => 'الهندسة الهيدروليكية والجيوتقنية']],
            '60730800' => ['Reconstruction and Restoration of Architectural Monuments' => ['uz' => 'Me’moriy yodgorliklarni rekonstruksiya va restavratsiya qilish', 'ru' => 'Реконструкция и реставрация архитектурных памятников', 'ar' => 'إعادة بناء وترميم المعالم المعمارية']],
            '60730900' => ['Urban Construction and Planning' => ['uz' => 'Shahar qurilishi va rejalashtirish', 'ru' => 'Городское строительство и планирование', 'ar' => 'البناء والتخطيط الحضري']],
            '60731100' => ['Production of Construction Materials, Products and Structures' => ['uz' => 'Qurilish materiallari, buyumlari va konstruksiyalarini ishlab chiqarish', 'ru' => 'Производство строительных материалов, изделий и конструкций', 'ar' => 'إنتاج مواد ومنتجات وهياكل البناء']],
            '60810100' => ['Agricultural Mechanization' => ['uz' => 'Qishloq xo‘jaligini mexanizatsiyalash', 'ru' => 'Механизация сельского хозяйства', 'ar' => 'ميكنة الزراعة']],
            '60810700' => ['Technology of Storage and Processing of Agricultural Products' => ['uz' => 'Qishloq xo‘jaligi mahsulotlarini saqlash va qayta ishlash texnologiyasi', 'ru' => 'Технология хранения и переработки сельскохозяйственной продукции', 'ar' => 'تكنولوجيا تخزين ومعالجة المنتجات الزراعية']],
            '60811000' => ['Fruit and Vegetable Growing and Viticulture' => ['uz' => 'Meva-sabzavotchilik va uzumchilik', 'ru' => 'Плодоовощеводство и виноградарство', 'ar' => 'زراعة الفواكه والخضروات والكروم']],
            '60811200' => ['Water Management and Land Reclamation' => ['uz' => 'Suv xo‘jaligi va melioratsiya', 'ru' => 'Водное хозяйство и мелиорация земель', 'ar' => 'إدارة المياه واستصلاح الأراضي']],
            '60811300' => ['Operation of Hydraulic Structures and Pumping Stations' => ['uz' => 'Gidrotexnika inshootlari va nasos stansiyalaridan foydalanish', 'ru' => 'Эксплуатация гидротехнических сооружений и насосных станций', 'ar' => 'تشغيل المنشآت الهيدروليكية ومحطات الضخ']],
            '60811400' => ['Reclamation Hydrogeology' => ['uz' => 'Meliorativ gidrogeologiya', 'ru' => 'Мелиоративная гидрогеология', 'ar' => 'الهيدروجيولوجيا الاستصلاحية']],
            '60811500' => ['Water Supply Engineering Systems' => ['uz' => 'Suv ta’minoti muhandislik tizimlari', 'ru' => 'Инженерные системы водоснабжения', 'ar' => 'أنظمة هندسة إمدادات المياه']],
            '60811600' => ['Land Cadastre and Land Management' => ['uz' => 'Yer kadastri va yer tuzish', 'ru' => 'Земельный кадастр и землеустройство', 'ar' => 'السجل العقاري وإدارة الأراضي']],
            '61010100' => ['Tourism and Hospitality' => ['uz' => 'Turizm va mehmondo‘stlik', 'ru' => 'Туризм и гостиничное дело', 'ar' => 'السياحة والضيافة']],
            '61010400' => ['Logistics' => ['uz' => 'Logistika', 'ru' => 'Логистика', 'ar' => 'اللوجستيات']],
            '61020000' => ['Occupational Safety and Technical Safety' => ['uz' => 'Mehnat muhofazasi va texnika xavfsizligi', 'ru' => 'Охрана труда и техническая безопасность', 'ar' => 'السلامة المهنية والسلامة التقنية']],
        ];
    }
};
