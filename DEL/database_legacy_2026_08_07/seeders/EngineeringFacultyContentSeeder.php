<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentTranslation;
use App\Models\Faculty;
use App\Models\FacultyTranslation;
use App\Models\Program;
use App\Models\StaffProfile;
use App\Models\StaffProfileTranslation;
use Database\Seeders\Concerns\ResolvesSeedLocales;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EngineeringFacultyContentSeeder extends Seeder
{
    use ResolvesSeedLocales;
    private array $locales = [];

    public function run(): void
    {
        $this->locales = $this->activeSeedLocales();
        $faculty = Faculty::where('slug', 'faculty-of-engineering')->first();
        if (! $faculty) {
            return;
        }

        foreach ($this->locales as $locale) {
            FacultyTranslation::firstOrCreate(
                ['faculty_id' => $faculty->id, 'locale' => $locale],
                [
                    'name' => $this->facultyName($locale),
                    'short_name' => $this->facultyShortName($locale),
                    'description' => $this->facultyDescription($locale),
                    'content_sections' => $this->facultySections($locale),
                    'meta_title' => $this->facultyName($locale),
                    'meta_description' => Str::limit($this->facultyDescription($locale), 250, ''),
                ]
            );
        }

        $this->seedLeadership($faculty);
        $this->syncCanonicalDepartmentsAndPrograms($faculty);
        $this->seedDepartmentSummaries($faculty);
    }

    private function syncCanonicalDepartmentsAndPrograms(Faculty $faculty): void
    {
        return;
    }

    private function seedLeadership(Faculty $faculty): void
    {
        $leaders = [
            [
                'name' => 'Xojiyev Aziz Xolmurodovich',
                'role' => [
                    'en' => 'Dean of the Faculty of Engineering',
                    'uz' => 'Muhandislik fakulteti dekani',
                    'ru' => 'Декан инженерного факультета',
                    'ar' => 'عميد كلية الهندسة',
                ],
                'office' => [
                    'en' => 'Every day 14:00-16:00',
                    'uz' => 'Har kuni 14:00-16:00',
                    'ru' => 'Каждый день 14:00-16:00',
                    'ar' => 'كل يوم 14:00-16:00',
                ],
                'phone' => '+998 (90) 744 01 79',
                'email' => 'azizhojiyev1979y@mail.ru',
                'photo' => 'cms/staff/khojiyev-aziz-kholmurodovich.jpg',
            ],
            [
                'name' => 'Rustamov Bobir Ismatovich',
                'role' => [
                    'en' => 'Deputy Dean for Academic Affairs',
                    'uz' => 'O‘quv ishlari bo‘yicha dekan o‘rinbosari',
                    'ru' => 'Заместитель декана по учебной работе',
                    'ar' => 'نائب العميد للشؤون الأكاديمية',
                ],
                'office' => [
                    'en' => 'Every day 14:00-16:00',
                    'uz' => 'Har kuni 14:00-16:00',
                    'ru' => 'Каждый день 14:00-16:00',
                    'ar' => 'كل يوم 14:00-16:00',
                ],
                'phone' => '+998 (99) 704 79 72',
                'email' => 'bobir_rustamov@bk.ru',
                'photo' => 'cms/staff/rustamov-bobir-ismatovich.jpg',
            ],
            [
                'name' => 'Ashurov Asrorjon Komilovich',
                'role' => [
                    'en' => 'Deputy Dean for Youth Affairs',
                    'uz' => 'Yoshlar masalalari bo‘yicha dekan o‘rinbosari',
                    'ru' => 'Заместитель декана по молодежным вопросам',
                    'ar' => 'نائب العميد لشؤون الشباب',
                ],
                'office' => [
                    'en' => 'Every day 14:00-16:00',
                    'uz' => 'Har kuni 14:00-16:00',
                    'ru' => 'Каждый день 14:00-16:00',
                    'ar' => 'كل يوم 14:00-16:00',
                ],
                'phone' => '+998 (97) 488 28 22',
                'email' => 'a.asrorjon83@mail.ru',
                'photo' => 'cms/staff/ashurov-asrorjon-komilovich.jpg',
            ],
        ];

        foreach ($leaders as $index => $leader) {
            $staff = StaffProfile::firstOrCreate(
                ['slug' => Str::slug('faculty-of-engineering-'.$leader['name'])],
                [
                    'faculty_id' => $faculty->id,
                    'department_id' => null,
                    'photo' => $leader['photo'],
                    'email' => $leader['email'],
                    'phone' => $leader['phone'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            foreach ($this->locales as $locale) {
                StaffProfileTranslation::firstOrCreate(
                    ['staff_profile_id' => $staff->id, 'locale' => $locale],
                    [
                        'full_name' => $leader['name'],
                        'position' => $leader['role'][$locale],
                        'bio' => $leader['role'][$locale].'. '.$this->leadershipBio($locale),
                        'office' => $leader['office'][$locale],
                    ]
                );
            }
        }
    }

    private function seedDepartmentSummaries(Faculty $faculty): void
    {
        foreach ($this->departmentSummaries() as $slug => $translations) {
            $department = Department::where('faculty_id', $faculty->id)->where('slug', $slug)->first();
            if (! $department) {
                continue;
            }

            foreach ($this->locales as $locale) {
                $translation = DepartmentTranslation::where('department_id', $department->id)
                    ->where('locale', $locale)
                    ->first();

                DepartmentTranslation::firstOrCreate(
                    ['department_id' => $department->id, 'locale' => $locale],
                    [
                        'name' => $translation?->name ?: $this->departmentNames()[$slug][$locale],
                        'short_name' => $translation?->short_name ?: $this->departmentShortNames()[$slug][$locale],
                        'description' => $translations[$locale],
                        'content_sections' => $translation?->content_sections ?: [],
                        'meta_title' => $translation?->meta_title ?: $this->departmentNames()[$slug][$locale],
                        'meta_description' => Str::limit($translations[$locale], 250, ''),
                    ]
                );
            }
        }
    }

    private function facultyName(string $locale): string
    {
        return [
            'en' => 'Faculty of Engineering',
            'uz' => 'Muhandislik fakulteti',
            'ru' => 'Инженерный факультет',
            'ar' => 'كلية الهندسة',
        ][$locale];
    }

    private function facultyShortName(string $locale): string
    {
        return [
            'en' => 'Engineering',
            'uz' => 'Muhandislik',
            'ru' => 'Инженерия',
            'ar' => 'الهندسة',
        ][$locale];
    }

    private function facultyDescription(string $locale): string
    {
        return [
            'en' => "The Faculty of Engineering is an academic division that prepares highly qualified specialists in power engineering, architecture, civil engineering, design, mechanics, and technological equipment. Students gain theoretical knowledge and practical skills while studying modern technologies, design, manufacturing processes, and innovative engineering solutions.\n\nCurrently, the faculty educates 1,161 students across 60 academic groups. Of these, 916 study on a contract basis and 245 under the State Grant. The student body includes 958 male students and 203 female students.\n\nThe faculty has 6 departments, including 4 specialized departments. A total of 111 academic staff members work at the faculty: 8 professors, 26 associate professors, 30 senior lecturers, 15 assistants, and 32 trainee lecturers. In addition, 4 foreign professors contribute to the faculty.\n\nThe faculty has established more than 20 scientific and technical clubs, a creative-artistic group called Satire and Humor, and a Scientific Innovation Center that supports research, economic contracts, and student employment.",
            'uz' => "Muhandislik fakulteti energetika, arxitektura, qurilish muhandisligi, dizayn, mexanika va texnologik jihozlar yo‘nalishlarida yuqori malakali mutaxassislar tayyorlaydigan akademik bo‘linmadir. Talabalar zamonaviy texnologiyalar, loyihalash, ishlab chiqarish jarayonlari va innovatsion muhandislik yechimlarini o‘rganib, nazariy bilim va amaliy ko‘nikmalarga ega bo‘ladilar.\n\nHozir fakultetda 60 ta akademik guruhda 1 161 nafar talaba tahsil oladi. Ulardan 916 nafari to‘lov-shartnoma asosida, 245 nafari davlat granti asosida o‘qiydi. Talabalarning 958 nafari o‘g‘il, 203 nafari qiz talabalardir.\n\nFakultetda 6 ta kafedra mavjud, ulardan 4 tasi ixtisoslashgan kafedralardir. Fakultetda 111 nafar professor-o‘qituvchi faoliyat yuritadi: 8 professor, 26 dotsent, 30 katta o‘qituvchi, 15 assistent va 32 stajyor-o‘qituvchi. Shuningdek, 4 nafar xorijiy professor ham fakultet faoliyatida ishtirok etadi.\n\nFakultetda 20 dan ortiq ilmiy-texnik to‘garaklar, Satira va yumor ijodiy-badiiy guruhi hamda ilmiy tadqiqotlar, xo‘jalik shartnomalari va talabalar bandligini qo‘llab-quvvatlovchi Ilmiy innovatsion markaz tashkil etilgan.",
            'ru' => "Инженерный факультет является академическим подразделением, которое готовит высококвалифицированных специалистов в области энергетики, архитектуры, гражданского строительства, дизайна, механики и технологического оборудования. Студенты получают теоретические знания и практические навыки, изучая современные технологии, проектирование, производственные процессы и инновационные инженерные решения.\n\nВ настоящее время на факультете обучается 1 161 студент в 60 академических группах. Из них 916 обучаются на контрактной основе, а 245 - по государственному гранту. Среди студентов 958 юношей и 203 девушки.\n\nНа факультете действует 6 кафедр, включая 4 специализированные кафедры. На факультете работают 111 преподавателей: 8 профессоров, 26 доцентов, 30 старших преподавателей, 15 ассистентов и 32 стажера-преподавателя. Кроме того, в работе факультета участвуют 4 зарубежных профессора.\n\nНа факультете создано более 20 научно-технических кружков, творческая группа Satire and Humor и Научно-инновационный центр, который поддерживает исследования, хозяйственные договоры и трудоустройство студентов.",
            'ar' => "كلية الهندسة هي وحدة أكاديمية تعد متخصصين مؤهلين تأهيلًا عاليًا في هندسة الطاقة والعمارة والهندسة المدنية والتصميم والميكانيكا والمعدات التكنولوجية. يكتسب الطلاب المعرفة النظرية والمهارات العملية من خلال دراسة التقنيات الحديثة والتصميم وعمليات التصنيع والحلول الهندسية المبتكرة.\n\nتضم الكلية حاليًا 1,161 طالبًا ضمن 60 مجموعة أكاديمية. يدرس 916 طالبًا على أساس التعاقد، بينما يدرس 245 طالبًا بمنحة حكومية. ويبلغ عدد الطلاب الذكور 958 طالبًا والطالبات 203 طالبة.\n\nتضم الكلية 6 أقسام، منها 4 أقسام تخصصية. يعمل في الكلية 111 عضوًا من الهيئة الأكاديمية: 8 أساتذة، و26 أستاذًا مشاركًا، و30 محاضرًا أول، و15 مساعدًا، و32 محاضرًا متدربًا. كما يشارك 4 أساتذة أجانب في نشاط الكلية.\n\nأنشأت الكلية أكثر من 20 ناديًا علميًا وتقنيًا، ومجموعة إبداعية فنية باسم Satire and Humor، ومركزًا علميًا للابتكار يدعم البحث العلمي والعقود الاقتصادية وتوظيف الطلاب.",
        ][$locale];
    }

    private function facultySections(string $locale): array
    {
        $labels = [
            'about' => ['en' => 'About the Faculty', 'uz' => 'Fakultet haqida', 'ru' => 'О факультете', 'ar' => 'عن الكلية'],
            'students' => ['en' => 'Student Body', 'uz' => 'Talabalar tarkibi', 'ru' => 'Контингент студентов', 'ar' => 'الطلاب'],
            'staff' => ['en' => 'Academic Staff', 'uz' => 'Professor-o‘qituvchilar', 'ru' => 'Профессорско-преподавательский состав', 'ar' => 'الهيئة الأكاديمية'],
            'activities' => ['en' => 'Clubs, Scholarships and Activities', 'uz' => 'To‘garaklar, stipendiyalar va faoliyat', 'ru' => 'Кружки, стипендии и деятельность', 'ar' => 'الأندية والمنح والأنشطة'],
            'innovation' => ['en' => 'Scientific Innovation Center', 'uz' => 'Ilmiy innovatsion markaz', 'ru' => 'Научно-инновационный центр', 'ar' => 'مركز الابتكار العلمي'],
        ];

        return [
            ['key' => 'about', 'title' => $labels['about'][$locale], 'items' => [$this->facultyDescription($locale)]],
            ['key' => 'students', 'title' => $labels['students'][$locale], 'items' => [$this->studentBody($locale)]],
            ['key' => 'staff', 'title' => $labels['staff'][$locale], 'items' => [$this->staffBody($locale)]],
            ['key' => 'activities', 'title' => $labels['activities'][$locale], 'items' => [$this->activitiesBody($locale)]],
            ['key' => 'innovation', 'title' => $labels['innovation'][$locale], 'items' => [$this->innovationBody($locale)]],
        ];
    }

    private function studentBody(string $locale): string
    {
        return [
            'en' => '1,161 students study in 60 academic groups: 916 contract students, 245 State Grant students, 958 male students, and 203 female students.',
            'uz' => '60 ta akademik guruhda 1 161 nafar talaba tahsil oladi: 916 nafari to‘lov-shartnoma, 245 nafari davlat granti, 958 nafari o‘g‘il va 203 nafari qiz talabalardir.',
            'ru' => 'В 60 академических группах обучается 1 161 студент: 916 контрактников, 245 студентов по государственному гранту, 958 юношей и 203 девушки.',
            'ar' => 'يدرس في الكلية 1,161 طالبًا ضمن 60 مجموعة أكاديمية: 916 طالبًا بنظام التعاقد، و245 بمنحة حكومية، و958 طالبًا و203 طالبة.',
        ][$locale];
    }

    private function staffBody(string $locale): string
    {
        return [
            'en' => 'The faculty has 111 academic staff members: 8 professors, 26 associate professors, 30 senior lecturers, 15 assistants, 32 trainee lecturers, and 4 foreign professors.',
            'uz' => 'Fakultetda 111 nafar professor-o‘qituvchi faoliyat yuritadi: 8 professor, 26 dotsent, 30 katta o‘qituvchi, 15 assistent, 32 stajyor-o‘qituvchi va 4 xorijiy professor.',
            'ru' => 'На факультете работают 111 преподавателей: 8 профессоров, 26 доцентов, 30 старших преподавателей, 15 ассистентов, 32 стажера-преподавателя и 4 зарубежных профессора.',
            'ar' => 'يعمل في الكلية 111 عضوًا أكاديميًا: 8 أساتذة، و26 أستاذًا مشاركًا، و30 محاضرًا أول، و15 مساعدًا، و32 محاضرًا متدربًا، إضافة إلى 4 أساتذة أجانب.',
        ][$locale];
    }

    private function activitiesBody(string $locale): string
    {
        return [
            'en' => 'The faculty runs more than 20 scientific and technical clubs, the Satire and Humor creative group, Zakovat intellectual teams, and sports sections. Students include 3 Beruni State Scholarship holders, 1 I. Karimov State Scholarship holder, 10 international competition winners, and 12 national competition and science olympiad winners.',
            'uz' => 'Fakultetda 20 dan ortiq ilmiy-texnik to‘garaklar, Satira va yumor ijodiy guruhi, Zakovat jamoalari va sport seksiyalari faoliyat yuritadi. Talabalar orasida 3 nafar Beruniy davlat stipendiyasi, 1 nafar I. Karimov davlat stipendiyasi sohibi, 10 nafar xalqaro tanlov g‘olibi va 12 nafar respublika tanlovlari hamda fan olimpiadalari g‘olibi bor.',
            'ru' => 'На факультете действуют более 20 научно-технических кружков, творческая группа Satire and Humor, интеллектуальные команды Zakovat и спортивные секции. Среди студентов 3 стипендиата государственной стипендии Беруни, 1 стипендиат государственной стипендии И. Каримова, 10 победителей международных конкурсов и 12 победителей республиканских конкурсов и олимпиад.',
            'ar' => 'تدير الكلية أكثر من 20 ناديًا علميًا وتقنيًا، ومجموعة Satire and Humor الإبداعية، وفرق Zakovat الفكرية، وأقسامًا رياضية. ومن بين الطلاب 3 حاصلين على منحة بيروني الحكومية، وطالب واحد حاصل على منحة إسلام كريموف الحكومية، و10 فائزين في مسابقات دولية، و12 فائزًا في مسابقات وأولمبيادات علمية وطنية.',
        ][$locale];
    }

    private function innovationBody(string $locale): string
    {
        return [
            'en' => 'The Scientific Innovation Center conducts research, supports economic contracts, and helps with student employment. At present, 10 students and 8 research staff work there full-time.',
            'uz' => 'Ilmiy innovatsion markaz ilmiy tadqiqotlar olib boradi, xo‘jalik shartnomalarini qo‘llab-quvvatlaydi va talabalar bandligiga ko‘maklashadi. Hozir markazda 10 nafar talaba va 8 nafar ilmiy xodim to‘liq stavkada ishlaydi.',
            'ru' => 'Научно-инновационный центр проводит исследования, поддерживает хозяйственные договоры и содействует трудоустройству студентов. В настоящее время там на полной ставке работают 10 студентов и 8 научных сотрудников.',
            'ar' => 'ينفذ مركز الابتكار العلمي الأبحاث، ويدعم العقود الاقتصادية، ويساعد في توظيف الطلاب. يعمل في المركز حاليًا 10 طلاب و8 باحثين بدوام كامل.',
        ][$locale];
    }

    private function leadershipBio(string $locale): string
    {
        return [
            'en' => 'Responsible for faculty academic coordination, student affairs, and administrative support.',
            'uz' => 'Fakultetdagi o‘quv jarayoni, talabalar bilan ishlash va maʼmuriy qo‘llab-quvvatlash yo‘nalishlari uchun masʼul.',
            'ru' => 'Отвечает за координацию учебного процесса, работу со студентами и административную поддержку факультета.',
            'ar' => 'مسؤول عن تنسيق الشؤون الأكاديمية وشؤون الطلاب والدعم الإداري في الكلية.',
        ][$locale];
    }

    private function departmentNames(): array
    {
        return [
            'electrical-power-engineering' => ['en' => 'Department of Electrical and Power Engineering', 'uz' => 'Elektr va energetika muhandisligi kafedrasi', 'ru' => 'Кафедра электрической и энергетической инженерии', 'ar' => 'قسم الهندسة الكهربائية وهندسة الطاقة'],
            'architecture' => ['en' => 'Department of Architecture', 'uz' => 'Arxitektura kafedrasi', 'ru' => 'Кафедра архитектуры', 'ar' => 'قسم العمارة'],
            'civil-engineering' => ['en' => 'Department of Civil Engineering', 'uz' => 'Qurilish muhandisligi kafedrasi', 'ru' => 'Кафедра гражданского строительства', 'ar' => 'قسم الهندسة المدنية'],
            'light-industry-engineering-and-design' => ['en' => 'Department of Light Industry Engineering and Design', 'uz' => 'Yengil sanoat muhandisligi va dizayn kafedrasi', 'ru' => 'Кафедра инженерии и дизайна легкой промышленности', 'ar' => 'قسم هندسة وتصميم الصناعات الخفيفة'],
            'mechanics-engineering-graphics' => ['en' => 'Department of Mechanics and Engineering Graphics', 'uz' => 'Mexanika va muhandislik grafikasi kafedrasi', 'ru' => 'Кафедра механики и инженерной графики', 'ar' => 'قسم الميكانيكا والرسم الهندسي'],
            'technological-machines-equipment' => ['en' => 'Department of Technological Machines and Equipment', 'uz' => 'Texnologik mashinalar va jihozlar kafedrasi', 'ru' => 'Кафедра технологических машин и оборудования', 'ar' => 'قسم الآلات والمعدات التكنولوجية'],
        ];
    }

    private function departmentShortNames(): array
    {
        return [
            'electrical-power-engineering' => ['en' => 'Electrical and Power Engineering', 'uz' => 'Elektr va energetika muhandisligi', 'ru' => 'Электрическая и энергетическая инженерия', 'ar' => 'الهندسة الكهربائية وهندسة الطاقة'],
            'architecture' => ['en' => 'Architecture', 'uz' => 'Arxitektura', 'ru' => 'Архитектура', 'ar' => 'العمارة'],
            'civil-engineering' => ['en' => 'Civil Engineering', 'uz' => 'Qurilish muhandisligi', 'ru' => 'Гражданское строительство', 'ar' => 'الهندسة المدنية'],
            'light-industry-engineering-and-design' => ['en' => 'Light Industry Engineering and Design', 'uz' => 'Yengil sanoat muhandisligi va dizayn', 'ru' => 'Инженерия и дизайн легкой промышленности', 'ar' => 'هندسة وتصميم الصناعات الخفيفة'],
            'mechanics-engineering-graphics' => ['en' => 'Mechanics and Engineering Graphics', 'uz' => 'Mexanika va muhandislik grafikasi', 'ru' => 'Механика и инженерная графика', 'ar' => 'الميكانيكا والرسم الهندسي'],
            'technological-machines-equipment' => ['en' => 'Technological Machines and Equipment', 'uz' => 'Texnologik mashinalar va jihozlar', 'ru' => 'Технологические машины и оборудование', 'ar' => 'الآلات والمعدات التكنولوجية'],
        ];
    }

    private function departmentSummaries(): array
    {
        return [
            'electrical-power-engineering' => [
                'en' => 'The department prepares engineers in power generation, electrical systems, energy auditing, and renewable energy. It combines laboratory training, industrial cooperation, and applied research for the modern energy sector.',
                'uz' => 'Kafedra elektr energiyasini ishlab chiqarish, elektr tizimlari, energetik audit va qayta tiklanuvchi energiya bo‘yicha muhandislar tayyorlaydi. Taʼlim laboratoriya mashg‘ulotlari, ishlab chiqarish bilan hamkorlik va amaliy tadqiqotlar bilan uyg‘unlashgan.',
                'ru' => 'Кафедра готовит инженеров в области производства электроэнергии, электрических систем, энергоаудита и возобновляемой энергетики. Обучение сочетает лабораторную практику, сотрудничество с производством и прикладные исследования.',
                'ar' => 'يعد القسم مهندسين في توليد الطاقة والأنظمة الكهربائية وتدقيق الطاقة والطاقة المتجددة، مع الجمع بين التدريب المخبري والتعاون الصناعي والبحث التطبيقي.',
            ],
            'architecture' => [
                'en' => 'The department trains architects who can preserve Bukhara historical heritage while designing modern, functional, and climate-adapted buildings. Its work covers architectural design, urban planning, restoration, and digital modeling.',
                'uz' => 'Kafedra Buxoroning tarixiy merosini asrab-avaylagan holda zamonaviy, funksional va iqlimga mos binolarni loyihalay oladigan arxitektorlarni tayyorlaydi. Yo‘nalishlar arxitektura loyihasi, shaharsozlik, restavratsiya va raqamli modellashtirishni qamrab oladi.',
                'ru' => 'Кафедра готовит архитекторов, способных сохранять историческое наследие Бухары и проектировать современные, функциональные здания, адаптированные к климату. Работа охватывает архитектурное проектирование, градостроительство, реставрацию и цифровое моделирование.',
                'ar' => 'يعد القسم معماريين قادرين على الحفاظ على التراث التاريخي لبخارى وتصميم مبان حديثة وعملية ومتوافقة مع المناخ، ويغطي التصميم المعماري والتخطيط الحضري والترميم والنمذجة الرقمية.',
            ],
            'civil-engineering' => [
                'en' => 'The department provides training in structural mechanics, construction technology, geotechnics, concrete materials, and project organization. It prepares specialists for buildings, infrastructure, restoration, and modern construction management.',
                'uz' => 'Kafedra konstruktiv mexanika, qurilish texnologiyasi, geotexnika, beton materiallari va loyihalarni tashkil etish bo‘yicha taʼlim beradi. U binolar, infratuzilma, restavratsiya va zamonaviy qurilish boshqaruvi uchun mutaxassislar tayyorlaydi.',
                'ru' => 'Кафедра обучает строительной механике, технологиям строительства, геотехнике, бетонным материалам и организации проектов. Она готовит специалистов для зданий, инфраструктуры, реставрации и современного управления строительством.',
                'ar' => 'يوفر القسم تعليمًا في ميكانيكا المنشآت وتقنيات البناء والجيوتقنية ومواد الخرسانة وتنظيم المشاريع، ويعد متخصصين للمباني والبنية التحتية والترميم وإدارة البناء الحديثة.',
            ],
            'light-industry-engineering-and-design' => [
                'en' => 'The department focuses on textile processing, garment technology, apparel manufacturing, footwear and accessories design, and product modeling. It links engineering practice with creative design for light industry enterprises.',
                'uz' => 'Kafedra to‘qimachilikni qayta ishlash, tikuvchilik texnologiyasi, kiyim-kechak ishlab chiqarish, poyabzal va aksessuarlar dizayni hamda mahsulot modellashtirishga ixtisoslashgan. U muhandislik amaliyotini yengil sanoat korxonalari uchun ijodiy dizayn bilan bog‘laydi.',
                'ru' => 'Кафедра специализируется на переработке текстиля, технологии швейного производства, изготовлении одежды, дизайне обуви и аксессуаров, а также моделировании продукции. Она связывает инженерную практику с творческим дизайном для предприятий легкой промышленности.',
                'ar' => 'يركز القسم على معالجة المنسوجات وتقنيات الملابس وتصنيع الأزياء وتصميم الأحذية والإكسسوارات ونمذجة المنتجات، ويربط الممارسة الهندسية بالتصميم الإبداعي لمؤسسات الصناعات الخفيفة.',
            ],
            'mechanics-engineering-graphics' => [
                'en' => 'The department teaches foundational engineering disciplines such as mechanics, machine elements, engineering graphics, CAD, and technical drawing. It supports engineering programs with the core skills needed for design and production.',
                'uz' => 'Kafedra mexanika, mashina detallari, muhandislik grafikasi, CAD va texnik chizmachilik kabi tayanch muhandislik fanlarini o‘qitadi. U loyihalash va ishlab chiqarish uchun zarur asosiy ko‘nikmalar bilan muhandislik dasturlarini qo‘llab-quvvatlaydi.',
                'ru' => 'Кафедра преподает базовые инженерные дисциплины: механику, детали машин, инженерную графику, CAD и техническое черчение. Она поддерживает инженерные программы ключевыми навыками для проектирования и производства.',
                'ar' => 'يدرّس القسم التخصصات الهندسية الأساسية مثل الميكانيكا وعناصر الآلات والرسم الهندسي وCAD والرسم التقني، ويدعم البرامج الهندسية بالمهارات الأساسية اللازمة للتصميم والإنتاج.',
            ],
            'technological-machines-equipment' => [
                'en' => 'The department trains specialists in the design, operation, maintenance, and modernization of technological machines used in light industry, cotton processing, food production, and chemical enterprises.',
                'uz' => 'Kafedra yengil sanoat, paxtani qayta ishlash, oziq-ovqat ishlab chiqarish va kimyo korxonalarida qo‘llaniladigan texnologik mashinalarni loyihalash, ishlatish, taʼmirlash va modernizatsiya qilish bo‘yicha mutaxassislar tayyorlaydi.',
                'ru' => 'Кафедра готовит специалистов по проектированию, эксплуатации, обслуживанию и модернизации технологических машин, используемых в легкой промышленности, переработке хлопка, пищевом производстве и химических предприятиях.',
                'ar' => 'يعد القسم متخصصين في تصميم وتشغيل وصيانة وتحديث الآلات التكنولوجية المستخدمة في الصناعات الخفيفة ومعالجة القطن وإنتاج الأغذية والمؤسسات الكيميائية.',
            ],
        ];
    }

    private function engineeringProgramSlugs(): array
    {
        $programs = [
            ['Design', 'Footwear and Accessories Design', '60210400'],
            ['Design', 'Apparel and Textile Design', '60210400'],
            ['Design', 'Textile and Light Industry Design', '60210400'],
            ['Energy Engineering', null, '60710400'],
            ['Electrical Engineering', null, '60710500'],
            ['Environmental Engineering', null, '60711800'],
            ['Renewable Energy Sources', null, '60712100'],
            ['Mechanical Engineering', null, '60712300'],
            ['Technological Machines and Equipment', null, '60720400'],
            ['Light Industry Engineering', null, '60720700'],
            ['Industrial Engineering', null, '60721800'],
            ['Architecture', null, '60730100'],
            ['Civil Engineering', null, '60730300'],
            ['Construction and Operation of Engineering Communications', null, '60730400'],
            ['Highway Engineering', null, '60730500'],
            ['Reconstruction and Restoration of Architectural Monuments', null, '60730800'],
            ['Urban Planning and Design', null, '60730900'],
            ['Production of Construction Materials, Products and Structures', null, '60731100'],
        ];

        return collect($programs)
            ->map(fn ($program) => Str::slug($program[0].($program[1] ? '-'.$program[1] : '').'-'.$program[2]))
            ->all();
    }
}
