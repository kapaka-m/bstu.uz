<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentTranslation;
use App\Models\Faculty;
use App\Models\FacultyTranslation;
use App\Models\Program;
use App\Models\ProgramTranslation;
use App\Models\StaffProfile;
use App\Models\StaffProfileTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CanonicalAcademicStructureSeeder extends Seeder
{
    private array $locales = ['en', 'uz', 'ru', 'ar'];

    public function run(): void
    {
        $faculties = $this->faculties();
        $departments = $this->departments();
        $programs = $this->programs();

        Faculty::query()->update(['is_active' => false]);
        Department::query()->update(['is_active' => false]);
        Program::query()->update(['is_active' => false]);

        $facultyModels = [];
        foreach ($faculties as $index => $facultyData) {
            $faculty = Faculty::updateOrCreate(
                ['slug' => $facultyData['slug']],
                [
                    'code' => $facultyData['code'],
                    'image' => $facultyData['image'],
                    'icon' => $facultyData['icon'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            foreach ($this->locales as $locale) {
                FacultyTranslation::updateOrCreate(
                    ['faculty_id' => $faculty->id, 'locale' => $locale],
                    [
                        'name' => $facultyData['name'][$locale],
                        'short_name' => $facultyData['short'][$locale],
                        'description' => $facultyData['description'][$locale],
                        'content_sections' => [],
                        'meta_title' => $facultyData['name'][$locale],
                        'meta_description' => $facultyData['description'][$locale],
                    ]
                );
            }

            $facultyModels[$facultyData['slug']] = $faculty;
            $this->seedFacultyLeadership($faculty, $facultyData['leadership']);
        }

        $departmentModels = [];
        foreach ($departments as $index => $departmentData) {
            $faculty = $facultyModels[$departmentData['faculty']] ?? null;
            if (! $faculty) {
                continue;
            }

            $department = Department::updateOrCreate(
                ['slug' => $departmentData['slug']],
                [
                    'faculty_id' => $faculty->id,
                    'code' => 'CAN-D'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'image' => 'departments/'.$departmentData['slug'].'.jpg',
                    'icon' => $departmentData['icon'] ?? 'building-2',
                    'head_name' => $departmentData['head']['name'] ?? null,
                    'email' => $departmentData['head']['email'] ?? null,
                    'phone' => $departmentData['head']['phone'] ?? null,
                    'reception_time' => $departmentData['head']['office'] ?? null,
                    'source_url' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            foreach ($this->locales as $locale) {
                DepartmentTranslation::updateOrCreate(
                    ['department_id' => $department->id, 'locale' => $locale],
                    [
                        'name' => $departmentData['name'][$locale] ?? $departmentData['name']['en'],
                        'short_name' => $departmentData['short'][$locale] ?? $departmentData['short']['en'],
                        'description' => $this->departmentDescription($departmentData, $faculty, $locale),
                        'content_sections' => $this->departmentSections($departmentData, $locale),
                        'meta_title' => $departmentData['name'][$locale] ?? $departmentData['name']['en'],
                        'meta_description' => $this->departmentDescription($departmentData, $faculty, $locale),
                    ]
                );
            }

            $departmentModels[$departmentData['slug']] = $department;
            $this->seedDepartmentHead($department, $departmentData);
        }

        foreach ($programs as $index => $programData) {
            $faculty = $facultyModels[$programData['faculty']] ?? null;
            $department = $departmentModels[$programData['department']] ?? null;
            if (! $faculty || ! $department) {
                continue;
            }

            $program = Program::updateOrCreate(
                ['slug' => $programData['slug']],
                [
                    'faculty_id' => $faculty->id,
                    'department_id' => $department->id,
                    'code' => $programData['slug'],
                    'official_code' => $programData['code'],
                    'track' => $programData['track'] ?? null,
                    'degree' => $programData['degree'],
                    'duration_years' => $programData['degree'] === 'master' ? 2 : 4,
                    'study_mode' => 'full_time',
                    'language_of_study' => 'uzbek',
                    'tuition_fee' => 0,
                    'currency' => 'UZS',
                    'image' => 'programs/'.$programData['slug'].'.jpg',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            foreach ($this->locales as $locale) {
                $name = $this->programName($programData, $locale);
                ProgramTranslation::updateOrCreate(
                    ['program_id' => $program->id, 'locale' => $locale],
                    [
                        'name' => $name,
                        'description' => $this->programDescription($name, $programData, $locale),
                        'requirements' => $this->programRequirements($locale),
                        'documents' => $this->programDocuments($locale),
                        'curriculum_summary' => $this->programCurriculum($programData, $locale),
                        'career_opportunities' => $this->programCareers($programData, $locale),
                        'meta_title' => $programData['code'].' - '.$name,
                        'meta_description' => $this->programDescription($name, $programData, $locale),
                    ]
                );
            }
        }
    }

    private function seedFacultyLeadership(Faculty $faculty, array $leaders): void
    {
        StaffProfile::where('faculty_id', $faculty->id)->whereNull('department_id')->update(['is_active' => false]);

        foreach ($leaders as $index => $leader) {
            $staff = StaffProfile::updateOrCreate(
                ['slug' => Str::slug($faculty->slug.'-'.$leader['name'])],
                [
                    'faculty_id' => $faculty->id,
                    'department_id' => null,
                    'photo' => 'staff/'.Str::slug($leader['name']).'.jpg',
                    'email' => $leader['email'],
                    'phone' => $leader['phone'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            foreach ($this->locales as $locale) {
                StaffProfileTranslation::updateOrCreate(
                    ['staff_profile_id' => $staff->id, 'locale' => $locale],
                    [
                        'full_name' => $leader['name'],
                        'position' => $this->position($leader['role'], $locale),
                        'bio' => $this->position($leader['role'], $locale),
                        'office' => $this->office($leader['office'], $locale),
                    ]
                );
            }
        }
    }

    private function seedDepartmentHead(Department $department, array $departmentData): void
    {
        if (empty($departmentData['head']['name'])) {
            return;
        }

        $head = $departmentData['head'];
        $staff = StaffProfile::updateOrCreate(
            ['slug' => Str::slug($department->slug.'-'.$head['name'])],
            [
                'faculty_id' => $department->faculty_id,
                'department_id' => $department->id,
                'photo' => null,
                'email' => $head['email'] ?? null,
                'phone' => $head['phone'] ?? null,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        foreach ($this->locales as $locale) {
            StaffProfileTranslation::updateOrCreate(
                ['staff_profile_id' => $staff->id, 'locale' => $locale],
                [
                    'full_name' => $head['name'],
                    'position' => $this->position('Head of Department', $locale),
                    'bio' => $this->position('Head of Department', $locale),
                    'office' => $this->office($head['office'] ?? '', $locale),
                ]
            );
        }
    }

    private function tr(string $en, string $uz, string $ru, string $ar): array
    {
        return compact('en', 'uz', 'ru', 'ar');
    }

    private function faculties(): array
    {
        return [
            [
                'slug' => 'faculty-of-engineering',
                'code' => 'ENG',
                'image' => 'faculties/engineering.jpg',
                'icon' => 'wrench',
                'name' => $this->tr('Faculty of Engineering', 'Muhandislik fakulteti', 'Инженерный факультет', 'كلية الهندسة'),
                'short' => $this->tr('Engineering', 'Muhandislik', 'Инженерия', 'الهندسة'),
                'description' => $this->tr(
                    'The Faculty of Engineering prepares specialists in power engineering, architecture, civil engineering, design, mechanics and technological equipment through applied education and laboratory practice.',
                    'Muhandislik fakulteti energetika, arxitektura, qurilish, dizayn, mexanika va texnologik uskunalar yo‘nalishlarida amaliy taʼlim va laboratoriya mashg‘ulotlari orqali mutaxassislar tayyorlaydi.',
                    'Инженерный факультет готовит специалистов в энергетике, архитектуре, строительстве, дизайне, механике и технологическом оборудовании через прикладное обучение и лабораторную практику.',
                    'تعد كلية الهندسة متخصصين في الطاقة والعمارة والهندسة المدنية والتصميم والميكانيكا والمعدات التقنية من خلال التعليم التطبيقي والتدريب المخبري.'
                ),
                'leadership' => [
                    ['name' => 'Xojiyev Aziz Xolmurodovich', 'role' => 'Dean of the Faculty of Engineering', 'office' => 'Every day 14:00-16:00', 'phone' => '+998 (90) 744 01 79', 'email' => 'azizhojiyev1979y@mail.ru'],
                    ['name' => 'Rustamov Bobir Ismatovich', 'role' => 'Deputy Dean for Academic Affairs', 'office' => 'Every day 14:00-16:00', 'phone' => '+998 (99) 704 79 72', 'email' => 'bobir_rustamov@bk.ru'],
                    ['name' => 'Ashurov Asrorjon Komilovich', 'role' => 'Deputy Dean for Student Affairs', 'office' => 'Every day 14:00-16:00', 'phone' => '+998 (97) 488 28 22', 'email' => 'a.asrorjon83@mail.ru'],
                ],
            ],
            [
                'slug' => 'faculty-of-technology',
                'code' => 'TECH',
                'image' => 'faculties/technology.jpg',
                'icon' => 'flask-conical',
                'name' => $this->tr('Faculty of Technology', 'Texnologiya fakulteti', 'Технологический факультет', 'كلية التكنولوجيا'),
                'short' => $this->tr('Technology', 'Texnologiya', 'Технология', 'التكنولوجيا'),
                'description' => $this->tr(
                    'The Faculty of Technology trains specialists for chemical technology, food production, oil and gas processing, metrology and agricultural product processing.',
                    'Texnologiya fakulteti kimyoviy texnologiya, oziq-ovqat ishlab chiqarish, neft va gazni qayta ishlash, metrologiya hamda qishloq xo‘jaligi mahsulotlarini qayta ishlash bo‘yicha mutaxassislar tayyorlaydi.',
                    'Технологический факультет готовит специалистов по химической технологии, пищевому производству, переработке нефти и газа, метрологии и переработке сельхозпродукции.',
                    'تعد كلية التكنولوجيا متخصصين في التكنولوجيا الكيميائية وإنتاج الأغذية ومعالجة النفط والغاز والمترولوجيا ومعالجة المنتجات الزراعية.'
                ),
                'leadership' => [
                    ['name' => 'Adizov Rashid Tokhtayevich', 'role' => 'Dean of the Faculty of Technology', 'office' => 'Every day 14:00-16:00 except Monday and Saturday', 'phone' => '+998 93 479 77 65', 'email' => 'adizov.rashid@mail.ru'],
                    ['name' => 'Safarov Jasur Alijon o‘g‘li', 'role' => 'Deputy Dean for Academic Affairs', 'office' => 'Every day 14:00-16:00', 'phone' => '+998 93 688 56 88', 'email' => 'jasur.safarov1993@mail.ru'],
                    ['name' => 'Bozorov Dilmurod Xolmurodovich', 'role' => 'Deputy Dean for Youth Affairs', 'office' => 'Every day 14:00-16:00', 'phone' => '+998 90 744 47 97', 'email' => 'd.bozorov_78@mail.ru'],
                ],
            ],
            [
                'slug' => 'faculty-of-natural-resources-management',
                'code' => 'NRM',
                'image' => 'faculties/natural.jpg',
                'icon' => 'leaf',
                'name' => $this->tr('Faculty of Natural Resources Management', 'Tabiiy resurslarni boshqarish fakulteti', 'Факультет управления природными ресурсами', 'كلية إدارة الموارد الطبيعية'),
                'short' => $this->tr('Natural Resources', 'Tabiiy resurslar', 'Природные ресурсы', 'الموارد الطبيعية'),
                'description' => $this->tr(
                    'The faculty prepares engineers for water management, land cadastre, ecology, hydrogeology, vehicle engineering, geodesy and environmental protection.',
                    'Fakultet suv xo‘jaligi, yer kadastri, ekologiya, gidrogeologiya, transport muhandisligi, geodeziya va atrof-muhit muhofazasi bo‘yicha muhandislar tayyorlaydi.',
                    'Факультет готовит инженеров по водному хозяйству, земельному кадастру, экологии, гидрогеологии, транспортной инженерии, геодезии и охране окружающей среды.',
                    'تعد الكلية مهندسين في إدارة المياه والسجل العقاري والبيئة والهيدروجيولوجيا وهندسة المركبات والجيوديسيا وحماية البيئة.'
                ),
                'leadership' => [
                    ['name' => 'Qobulova Barno Baxriddin qizi', 'role' => 'Dean of the Faculty of Natural Resources Management', 'office' => 'Every day 14:00-16:00 except Monday and Saturday', 'phone' => '+998 93 721 04 85', 'email' => 'kobulovabarno@gmail.com'],
                    ['name' => 'To be announced', 'role' => 'Deputy Dean for Academic Affairs', 'office' => 'Every day 14:00-16:00', 'phone' => '-', 'email' => '-'],
                    ['name' => 'Gadoyeva Abera Hasanovna', 'role' => 'Deputy Dean for Youth Affairs', 'office' => 'Every day 14:00-16:00', 'phone' => '+998 (93) 620 20 98', 'email' => 'Aberaxasanovnaa2208@gmail.com'],
                ],
            ],
            [
                'slug' => 'faculty-of-service-and-digitalization',
                'code' => 'SD',
                'image' => 'faculties/service.jpg',
                'icon' => 'laptop',
                'name' => $this->tr('Faculty of Service and Digitalization', 'Servis va raqamlashtirish fakulteti', 'Факультет сервиса и цифровизации', 'كلية الخدمات والرقمنة'),
                'short' => $this->tr('Service & Digitalization', 'Servis va raqamlashtirish', 'Сервис и цифровизация', 'الخدمات والرقمنة'),
                'description' => $this->tr(
                    'The faculty brings together economics, management, tourism, ICT, automation, exact sciences, social sciences and language education for a digital university environment.',
                    'Fakultet raqamli universitet muhitida iqtisodiyot, menejment, turizm, AKT, avtomatlashtirish, aniq fanlar, ijtimoiy fanlar va tillarni birlashtiradi.',
                    'Факультет объединяет экономику, менеджмент, туризм, ИКТ, автоматизацию, точные науки, социальные науки и языковое образование в цифровой университетской среде.',
                    'تجمع الكلية بين الاقتصاد والإدارة والسياحة وتقنيات المعلومات والأتمتة والعلوم الدقيقة والاجتماعية وتعليم اللغات ضمن بيئة جامعية رقمية.'
                ),
                'leadership' => [
                    ['name' => 'Khayitov Sherbek Nayimovich', 'role' => 'Dean of the Faculty of Service and Digitalization', 'office' => 'Every day 14:00-16:00 except Monday and Saturday', 'phone' => '+998 (93) 686 95 55', 'email' => 'Sherbek-market@mail.ru'],
                    ['name' => 'Farhod Bakhtiyorovich Boboqulov', 'role' => 'Deputy Dean for Academic Affairs', 'office' => 'Every day 14:00-16:00', 'phone' => '+998 (93) 471 16 61', 'email' => 'boboqulovfarxod1993@gmail.ru'],
                    ['name' => 'Fayzullayev Askar Rajabboevich', 'role' => 'Deputy Dean for Youth Affairs', 'office' => 'Every day 9:00-16:00', 'phone' => '+998 (93) 194 75 20', 'email' => 'fayzullayev_asqar_2023@mail.ru'],
                ],
            ],
        ];
    }

    private function departments(): array
    {
        $d = fn ($faculty, $slug, $en, $uz, $ru, $ar, $head = []) => [
            'faculty' => $faculty,
            'slug' => $slug,
            'name' => $this->tr($en, $uz, $ru, $ar),
            'short' => $this->tr(str_replace('Department of ', '', $en), str_replace(' kafedrasi', '', $uz), str_replace('Кафедра ', '', $ru), str_replace('قسم ', '', $ar)),
            'head' => $head,
        ];

        return [
            $d('faculty-of-engineering', 'electrical-power-engineering', 'Department of Electrical and Power Engineering', 'Elektr va energetika muhandisligi kafedrasi', 'Кафедра электрической и энергетической инженерии', 'قسم الهندسة الكهربائية وهندسة الطاقة', ['name' => 'Latipov Saidmurod Tuyg‘unovich', 'office' => 'Tuesday-Thursday 10:00-13:00', 'phone' => '+998 91 979 88 22', 'email' => 'stlatipov@gmail.com']),
            $d('faculty-of-engineering', 'architecture', 'Department of Architecture', 'Arxitektura kafedrasi', 'Кафедра архитектуры', 'قسم العمارة'),
            $d('faculty-of-engineering', 'civil-engineering', 'Department of Civil Engineering', 'Qurilish muhandisligi kafedrasi', 'Кафедра гражданского строительства', 'قسم الهندسة المدنية'),
            $d('faculty-of-engineering', 'light-industry-engineering-and-design', 'Department of Light Industry Engineering and Design', 'Yengil sanoat muhandisligi va dizayn kafedrasi', 'Кафедра инженерии и дизайна легкой промышленности', 'قسم هندسة وتصميم الصناعات الخفيفة'),
            $d('faculty-of-engineering', 'mechanics-engineering-graphics', 'Department of Mechanics and Engineering Graphics', 'Mexanika va muhandislik grafikasi kafedrasi', 'Кафедра механики и инженерной графики', 'قسم الميكانيكا والرسم الهندسي'),
            $d('faculty-of-engineering', 'technological-machines-equipment', 'Department of Technological Machines and Equipment', 'Texnologik mashinalar va jihozlar kafedrasi', 'Кафедра технологических машин и оборудования', 'قسم الآلات والمعدات التكنولوجية'),
            $d('faculty-of-technology', 'oil-gas-refining-technology', 'Department of Oil and Gas Processing Technology', 'Neft va gazni qayta ishlash texnologiyasi kafedrasi', 'Кафедра технологии переработки нефти и газа', 'قسم تكنولوجيا معالجة النفط والغاز'),
            $d('faculty-of-technology', 'food-technology-service', 'Department of Food Technology and Service', 'Oziq-ovqat texnologiyasi va servis kafedrasi', 'Кафедра пищевой технологии и сервиса', 'قسم تكنولوجيا الأغذية والخدمات'),
            $d('faculty-of-technology', 'chemical-technology', 'Department of Chemical Engineering', 'Kimyoviy muhandislik kafedrasi', 'Кафедра химической инженерии', 'قسم الهندسة الكيميائية'),
            $d('faculty-of-technology', 'agricultural-products-storage-oil-fat-technology', 'Department of Storage, Processing, and Oil-Fat Technology of Agricultural Products', 'Qishloq xo‘jaligi mahsulotlarini saqlash, qayta ishlash va yog‘-moy texnologiyasi kafedrasi', 'Кафедра хранения, переработки и масложировой технологии сельхозпродукции', 'قسم تخزين ومعالجة المنتجات الزراعية وتكنولوجيا الزيوت والدهون'),
            $d('faculty-of-technology', 'oil-gas-engineering-upstream-downstream', 'Department of Oil and Gas Engineering', 'Neft va gaz muhandisligi kafedrasi', 'Кафедра нефтегазовой инженерии', 'قسم هندسة النفط والغاز'),
            $d('faculty-of-technology', 'metrology-standardization-quality-control', 'Department of Metrology and Standardization', 'Metrologiya va standartlashtirish kafedrasi', 'Кафедра метрологии и стандартизации', 'قسم المترولوجيا والتقييس'),
            $d('faculty-of-natural-resources-management', 'irrigation-melioration', 'Department of Irrigation and Land Reclamation', 'Irrigatsiya va melioratsiya kafedrasi', 'Кафедра ирригации и мелиорации', 'قسم الري واستصلاح الأراضي'),
            $d('faculty-of-natural-resources-management', 'hydrotechnical-structures-pump-stations', 'Department of Hydraulic Structures and Pumping Stations', 'Gidrotexnika inshootlari va nasos stansiyalari kafedrasi', 'Кафедра гидротехнических сооружений и насосных станций', 'قسم المنشآت الهيدروليكية ومحطات الضخ'),
            $d('faculty-of-natural-resources-management', 'agricultural-water-resources-engineering-technologies', 'Department of Agricultural and Water Management Engineering Technologies', 'Qishloq va suv xo‘jaligi muhandislik texnologiyalari kafedrasi', 'Кафедра инженерных технологий сельского и водного хозяйства', 'قسم تقنيات هندسة الزراعة وإدارة المياه'),
            $d('faculty-of-natural-resources-management', 'land-resources-management-state-land-cadastres', 'Department of Land Use and State Cadastre', 'Yer resurslarini boshqarish va davlat kadastri kafedrasi', 'Кафедра землепользования и государственного кадастра', 'قسم استخدام الأراضي والسجل العقاري الحكومي'),
            $d('faculty-of-natural-resources-management', 'industrial-ecology-hydrogeology', 'Department of Industrial Ecology and Hydrogeology', 'Sanoat ekologiyasi va gidrogeologiya kafedrasi', 'Кафедра промышленной экологии и гидрогеологии', 'قسم البيئة الصناعية والهيدروجيولوجيا'),
            $d('faculty-of-natural-resources-management', 'vehicle-engineering-automotive-transport-systems', 'Department of Vehicle Engineering', 'Transport vositalari muhandisligi kafedrasi', 'Кафедра транспортной инженерии', 'قسم هندسة المركبات'),
            $d('faculty-of-service-and-digitalization', 'technological-processes-production-automation', 'Department of Technological Processes and Production Automation', 'Texnologik jarayonlar va ishlab chiqarishni avtomatlashtirish kafedrasi', 'Кафедра автоматизации технологических процессов и производства', 'قسم العمليات التكنولوجية وأتمتة الإنتاج'),
            $d('faculty-of-service-and-digitalization', 'information-and-communication-technologies', 'Department of Information and Communication Technologies', 'Axborot-kommunikatsiya texnologiyalari kafedrasi', 'Кафедра информационно-коммуникационных технологий', 'قسم تكنولوجيا المعلومات والاتصالات'),
            $d('faculty-of-service-and-digitalization', 'economics-and-management', 'Department of Economics and Management', 'Iqtisodiyot va menejment kafedrasi', 'Кафедра экономики и менеджмента', 'قسم الاقتصاد والإدارة'),
            $d('faculty-of-service-and-digitalization', 'social-sciences-physical-culture', 'Department of Social Sciences and Physical Education', 'Ijtimoiy fanlar va jismoniy tarbiya kafedrasi', 'Кафедра социальных наук и физического воспитания', 'قسم العلوم الاجتماعية والتربية البدنية'),
            $d('faculty-of-service-and-digitalization', 'exact-sciences', 'Department of Exact Sciences', 'Aniq fanlar kafedrasi', 'Кафедра точных наук', 'قسم العلوم الدقيقة', ['name' => 'Kasimova Guzal Karimovna', 'office' => 'Monday-Friday 14:00-16:00', 'phone' => '+998 94 490 22 90', 'email' => null]),
            $d('faculty-of-service-and-digitalization', 'uzbek-foreign-languages', 'Department of Uzbek and Foreign Languages', 'O‘zbek va xorijiy tillar kafedrasi', 'Кафедра узбекского и иностранных языков', 'قسم اللغات الأوزبكية والأجنبية', ['name' => 'Yusupova Shoxida Batirovna', 'office' => 'Monday-Friday 14:00-16:00', 'phone' => '+998 (93) 623 74 72', 'email' => null]),
        ];
    }

    private function programs(): array
    {
        $p = fn ($faculty, $department, $degree, $code, $name, $track = null) => [
            'faculty' => $faculty,
            'department' => $department,
            'degree' => $degree,
            'code' => $code,
            'name' => $name,
            'track' => $track,
            'slug' => Str::slug($name.($track ? '-'.$track : '').'-'.$code),
        ];

        return [
            $p('faculty-of-engineering', 'light-industry-engineering-and-design', 'bachelor', '60210400', 'Design', 'Footwear and Accessories Design'),
            $p('faculty-of-engineering', 'light-industry-engineering-and-design', 'bachelor', '60210400', 'Design', 'Apparel and Textile Design'),
            $p('faculty-of-engineering', 'light-industry-engineering-and-design', 'bachelor', '60210400', 'Design', 'Textile and Light Industry Design'),
            $p('faculty-of-engineering', 'electrical-power-engineering', 'bachelor', '60710400', 'Energy Engineering'),
            $p('faculty-of-engineering', 'electrical-power-engineering', 'bachelor', '60710500', 'Electrical Engineering'),
            $p('faculty-of-engineering', 'electrical-power-engineering', 'bachelor', '60711800', 'Environmental Engineering'),
            $p('faculty-of-engineering', 'electrical-power-engineering', 'bachelor', '60712100', 'Renewable Energy Sources'),
            $p('faculty-of-engineering', 'mechanics-engineering-graphics', 'bachelor', '60712300', 'Mechanical Engineering'),
            $p('faculty-of-engineering', 'technological-machines-equipment', 'bachelor', '60720400', 'Technological Machines and Equipment'),
            $p('faculty-of-engineering', 'light-industry-engineering-and-design', 'bachelor', '60720700', 'Light Industry Engineering'),
            $p('faculty-of-engineering', 'technological-machines-equipment', 'bachelor', '60721800', 'Industrial Engineering'),
            $p('faculty-of-engineering', 'architecture', 'bachelor', '60730100', 'Architecture'),
            $p('faculty-of-engineering', 'civil-engineering', 'bachelor', '60730300', 'Civil Engineering'),
            $p('faculty-of-engineering', 'civil-engineering', 'bachelor', '60730400', 'Construction and Operation of Engineering Communications'),
            $p('faculty-of-engineering', 'civil-engineering', 'bachelor', '60730500', 'Highway Engineering'),
            $p('faculty-of-engineering', 'architecture', 'bachelor', '60730800', 'Reconstruction and Restoration of Architectural Monuments'),
            $p('faculty-of-engineering', 'architecture', 'bachelor', '60730900', 'Urban Planning and Design'),
            $p('faculty-of-engineering', 'civil-engineering', 'bachelor', '60731100', 'Production of Construction Materials, Products and Structures'),
            $p('faculty-of-technology', 'chemical-technology', 'bachelor', '60710100', 'Chemical Engineering'),
            $p('faculty-of-technology', 'chemical-technology', 'bachelor', '60710200', 'Biotechnology'),
            $p('faculty-of-technology', 'chemical-technology', 'bachelor', '60710300', 'Printing and Packaging Engineering'),
            $p('faculty-of-technology', 'metrology-standardization-quality-control', 'bachelor', '60710800', 'Metrology and Standardization'),
            $p('faculty-of-technology', 'agricultural-products-storage-oil-fat-technology', 'bachelor', '60810700', 'Storage and Processing Technology of Agricultural Products'),
            $p('faculty-of-technology', 'agricultural-products-storage-oil-fat-technology', 'bachelor', '60811000', 'Horticulture and Viticulture'),
            $p('faculty-of-technology', 'food-technology-service', 'bachelor', '60720100', 'Food Technology'),
            $p('faculty-of-technology', 'oil-gas-refining-technology', 'bachelor', '60720500', 'Gas Processing Technology'),
            $p('faculty-of-technology', 'oil-gas-refining-technology', 'bachelor', '60720600', 'Oil and Oil-Gas Processing Technology'),
            $p('faculty-of-technology', 'oil-gas-engineering-upstream-downstream', 'bachelor', '60720900', 'Geology, Exploration and Prospecting of Mineral Deposits'),
            $p('faculty-of-technology', 'oil-gas-engineering-upstream-downstream', 'bachelor', '60721100', 'Oil and Gas Engineering'),
            $p('faculty-of-technology', 'chemical-technology', 'master', '70710103', 'Chemical Technology of High-Molecular Compounds', 'Polymer Production'),
            $p('faculty-of-technology', 'food-technology-service', 'master', '70720101', 'Technology of Food Production and Processing', 'Grain Storage and Processing Technology'),
            $p('faculty-of-technology', 'agricultural-products-storage-oil-fat-technology', 'master', '70720101', 'Technology of Food Production and Processing', 'Oil Processing Technology'),
            $p('faculty-of-technology', 'chemical-technology', 'master', '70710101', 'Chemical Technology', 'Inorganic Substances Chemical Technology'),
            $p('faculty-of-technology', 'chemical-technology', 'master', '70710101', 'Chemical Technology', 'Silicate and Refractory Nonmetallic Materials Technology'),
            $p('faculty-of-technology', 'chemical-technology', 'master', '70710101', 'Chemical Technology', 'Organic Substances Chemical Technology'),
            $p('faculty-of-natural-resources-management', 'vehicle-engineering-automotive-transport-systems', 'bachelor', '60711300', 'Metallurgy Technologies'),
            $p('faculty-of-natural-resources-management', 'vehicle-engineering-automotive-transport-systems', 'bachelor', '60711400', 'Vehicle Engineering'),
            $p('faculty-of-natural-resources-management', 'vehicle-engineering-automotive-transport-systems', 'bachelor', '60720300', 'Materials Science'),
            $p('faculty-of-natural-resources-management', 'agricultural-water-resources-engineering-technologies', 'bachelor', '60810100', 'Mechanization of Agriculture'),
            $p('faculty-of-natural-resources-management', 'irrigation-melioration', 'bachelor', '60812300', 'Water Management and Land Reclamation'),
            $p('faculty-of-natural-resources-management', 'irrigation-melioration', 'bachelor', '60813000', 'Innovative Technologies in Water Management and Their Application'),
            $p('faculty-of-natural-resources-management', 'hydrotechnical-structures-pump-stations', 'bachelor', '60812900', 'Water Supply Engineering Systems'),
            $p('faculty-of-natural-resources-management', 'hydrotechnical-structures-pump-stations', 'bachelor', '60812500', 'Use of Hydraulic Structures and Pumping Stations'),
            $p('faculty-of-natural-resources-management', 'hydrotechnical-structures-pump-stations', 'bachelor', '60730600', 'Hydraulic and Geotechnical Engineering'),
            $p('faculty-of-natural-resources-management', 'hydrotechnical-structures-pump-stations', 'bachelor', '60710600', 'Hydropower Engineering'),
            $p('faculty-of-natural-resources-management', 'irrigation-melioration', 'bachelor', '60812400', 'Mechanization of Water Management and Land Reclamation Works'),
            $p('faculty-of-natural-resources-management', 'land-resources-management-state-land-cadastres', 'bachelor', '60810600', 'Land Cadastre and Land Management'),
            $p('faculty-of-natural-resources-management', 'land-resources-management-state-land-cadastres', 'bachelor', '60722600', 'Geodesy and Geoinformatics'),
            $p('faculty-of-natural-resources-management', 'land-resources-management-state-land-cadastres', 'bachelor', '60721600', 'Cartography and Remote Sensing'),
            $p('faculty-of-natural-resources-management', 'land-resources-management-state-land-cadastres', 'bachelor', '60721700', 'Cadastre'),
            $p('faculty-of-natural-resources-management', 'industrial-ecology-hydrogeology', 'bachelor', '60810800', 'Soil Evaluation and Land Degradation'),
            $p('faculty-of-natural-resources-management', 'industrial-ecology-hydrogeology', 'bachelor', '60812600', 'Reclamation Hydrogeology'),
            $p('faculty-of-natural-resources-management', 'industrial-ecology-hydrogeology', 'bachelor', '60530800', 'Hydrology', 'River and Reservoir Hydrology'),
            $p('faculty-of-natural-resources-management', 'industrial-ecology-hydrogeology', 'bachelor', '60710400', 'Ecology and Environmental Protection', 'in Water Management'),
            $p('faculty-of-natural-resources-management', 'industrial-ecology-hydrogeology', 'bachelor', '61020200', 'Occupational Safety and Technical Safety'),
            $p('faculty-of-service-and-digitalization', 'economics-and-management', 'bachelor', '60410100', 'Economics'),
            $p('faculty-of-service-and-digitalization', 'economics-and-management', 'bachelor', '60410800', 'Management'),
            $p('faculty-of-service-and-digitalization', 'economics-and-management', 'bachelor', '60411200', 'Marketing'),
            $p('faculty-of-service-and-digitalization', 'information-and-communication-technologies', 'bachelor', '60610100', 'Information Systems and Technologies'),
            $p('faculty-of-service-and-digitalization', 'technological-processes-production-automation', 'bachelor', '60710900', 'Technological Processes and Production Automation'),
            $p('faculty-of-service-and-digitalization', 'economics-and-management', 'bachelor', '61010100', 'Tourism and Hospitality'),
        ];
    }

    private function departmentDescription(array $department, Faculty $faculty, string $locale): string
    {
        $name = $department['name'][$locale] ?? $department['name']['en'];
        $facultyName = $faculty->translate('name', $locale) ?: $faculty->translate('name', 'en');

        return match ($locale) {
            'uz' => "{$name} {$facultyName} tarkibida zamonaviy taʼlim, amaliy laboratoriya ishlari va ishlab chiqarish bilan hamkorlik asosida mutaxassislar tayyorlaydi.",
            'ru' => "{$name} в составе {$facultyName} готовит специалистов через современное обучение, лабораторную практику и сотрудничество с производством.",
            'ar' => "{$name} ضمن {$facultyName} يعد متخصصين عبر التعليم الحديث والتدريب المخبري والتعاون المباشر مع قطاعات الصناعة.",
            default => "{$name} operates within {$facultyName}, preparing specialists through modern coursework, laboratory practice, applied research and industry cooperation.",
        };
    }

    private function departmentSections(array $department, string $locale): array
    {
        return [
            ['key' => 'prepared_specialists', 'title' => $this->sectionTitle('prepared_specialists', $locale), 'items' => []],
            ['key' => 'subjects', 'title' => $this->sectionTitle('subjects', $locale), 'items' => []],
            ['key' => 'research', 'title' => $this->sectionTitle('research', $locale), 'items' => [$this->researchLine($locale)]],
            ['key' => 'cooperation', 'title' => $this->sectionTitle('cooperation', $locale), 'items' => [$this->cooperationLine($locale)]],
            ['key' => 'publications', 'title' => $this->sectionTitle('publications', $locale), 'items' => []],
            ['key' => 'plans', 'title' => $this->sectionTitle('plans', $locale), 'items' => [$this->plansLine($locale)]],
        ];
    }

    private function sectionTitle(string $key, string $locale): string
    {
        $labels = [
            'prepared_specialists' => $this->tr('Prepared Specialists', 'Tayyorlanadigan mutaxassislar', 'Подготавливаемые специалисты', 'التخصصات التي يتم إعدادها'),
            'subjects' => $this->tr('Taught Subjects', 'O‘qitiladigan fanlar', 'Преподаваемые дисциплины', 'المقررات الدراسية'),
            'research' => $this->tr('Research Work', 'Ilmiy-tadqiqot ishlari', 'Научно-исследовательская работа', 'الأعمال البحثية'),
            'cooperation' => $this->tr('Cooperation', 'Hamkorlik', 'Сотрудничество', 'التعاون'),
            'publications' => $this->tr('Publications', 'Nashrlar', 'Публикации', 'المنشورات'),
            'plans' => $this->tr('Prospective Plans', 'Istiqboldagi rejalar', 'Перспективные планы', 'الخطط المستقبلية'),
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function programName(array $program, string $locale): string
    {
        $value = $program['track'] ? "{$program['name']}: ".Str::lower($program['track']) : $program['name'];

        return match ($locale) {
            'ar' => $value,
            'ru' => $value,
            'uz' => $value,
            default => $value,
        };
    }

    private function programDescription(string $name, array $program, string $locale): string
    {
        $degree = $this->degree($program['degree'], $locale);

        return match ($locale) {
            'uz' => "{$name} {$degree} dasturi nazariy taʼlim, laboratoriya ishlari, amaliyot va soha korxonalari bilan hamkorlikni birlashtiradi.",
            'ru' => "Программа {$degree} «{$name}» объединяет теоретическое обучение, лабораторные работы, практику и сотрудничество с профильными предприятиями.",
            'ar' => "برنامج {$name} لدرجة {$degree} يجمع بين الدراسة النظرية والعمل المخبري والتطبيق العملي والتعاون مع المؤسسات المتخصصة.",
            default => "{$name} is a {$degree} program combining theoretical study, laboratory training, practical projects and industry cooperation.",
        };
    }

    private function programRequirements(string $locale): string
    {
        return match ($locale) {
            'uz' => "O‘rta taʼlim hujjati\nPasport yoki shaxsni tasdiqlovchi hujjat\nQabul talablariga muvofiq ariza",
            'ru' => "Документ о среднем образовании\nПаспорт или удостоверение личности\nЗаявление согласно правилам приема",
            'ar' => "شهادة التعليم الثانوي\nجواز سفر أو وثيقة هوية\nطلب وفق متطلبات القبول",
            default => "Secondary education certificate\nPassport or identity document\nApplication according to admission requirements",
        };
    }

    private function programDocuments(string $locale): string
    {
        return match ($locale) {
            'uz' => "Pasport nusxasi\nTaʼlim hujjati\nFotosurat",
            'ru' => "Копия паспорта\nДокумент об образовании\nФотография",
            'ar' => "نسخة جواز السفر\nوثيقة التعليم\nصورة شخصية",
            default => "Passport copy\nEducation document\nPhoto",
        };
    }

    private function programCurriculum(array $program, string $locale): string
    {
        return match ($locale) {
            'uz' => "Mutaxassislik fanlari\nLaboratoriya mashg‘ulotlari\nIshlab chiqarish amaliyoti\nBitiruv loyihasi",
            'ru' => "Профильные дисциплины\nЛабораторные занятия\nПроизводственная практика\nВыпускной проект",
            'ar' => "مقررات التخصص\nتدريب مخبري\nتدريب صناعي\nمشروع التخرج",
            default => "Specialized subjects\nLaboratory practice\nIndustrial internship\nGraduation project",
        };
    }

    private function programCareers(array $program, string $locale): string
    {
        return match ($locale) {
            'uz' => "Muhandis-mutaxassis\nIshlab chiqarish texnologi\nLoyiha va tadqiqot mutaxassisi\nSifat nazorati mutaxassisi",
            'ru' => "Инженер-специалист\nТехнолог производства\nСпециалист по проектам и исследованиям\nСпециалист по контролю качества",
            'ar' => "مهندس متخصص\nتقني إنتاج\nمتخصص مشاريع وبحوث\nمتخصص مراقبة الجودة",
            default => "Specialist engineer\nProduction technologist\nProject and research specialist\nQuality control specialist",
        };
    }

    private function position(string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => str_replace(['Dean of the Faculty', 'Deputy Dean for Academic Affairs', 'Deputy Dean for Youth Affairs', 'Deputy Dean for Student Affairs', 'Head of Department'], ['Fakultet dekani', 'O‘quv ishlari bo‘yicha dekan o‘rinbosari', 'Yoshlar masalalari bo‘yicha dekan o‘rinbosari', 'Talabalar ishlari bo‘yicha dekan o‘rinbosari', 'Kafedra mudiri'], $position),
            'ru' => str_replace(['Dean of the Faculty', 'Deputy Dean for Academic Affairs', 'Deputy Dean for Youth Affairs', 'Deputy Dean for Student Affairs', 'Head of Department'], ['Декан факультета', 'Заместитель декана по учебной работе', 'Заместитель декана по молодежным вопросам', 'Заместитель декана по работе со студентами', 'Заведующий кафедрой'], $position),
            'ar' => str_replace(['Dean of the Faculty', 'Deputy Dean for Academic Affairs', 'Deputy Dean for Youth Affairs', 'Deputy Dean for Student Affairs', 'Head of Department'], ['عميد الكلية', 'نائب العميد للشؤون الأكاديمية', 'نائب العميد لشؤون الشباب', 'نائب العميد لشؤون الطلاب', 'رئيس القسم'], $position),
            default => $position,
        };
    }

    private function office(string $office, string $locale): string
    {
        if ($office === '-') {
            return '-';
        }

        return match ($locale) {
            'uz' => str_replace(['Every day', 'except Monday and Saturday', 'Monday-Friday', 'Tuesday-Thursday'], ['Har kuni', 'dushanba va shanbadan tashqari', 'Dushanba-Juma', 'Seshanba-Payshanba'], $office),
            'ru' => str_replace(['Every day', 'except Monday and Saturday', 'Monday-Friday', 'Tuesday-Thursday'], ['Каждый день', 'кроме понедельника и субботы', 'Понедельник-Пятница', 'Вторник-Четверг'], $office),
            'ar' => str_replace(['Every day', 'except Monday and Saturday', 'Monday-Friday', 'Tuesday-Thursday'], ['كل يوم', 'ما عدا الاثنين والسبت', 'الاثنين-الجمعة', 'الثلاثاء-الخميس'], $office),
            default => $office,
        };
    }

    private function degree(string $degree, string $locale): string
    {
        return match ($locale) {
            'uz' => ['bachelor' => 'bakalavriat', 'master' => 'magistratura', 'phd' => 'doktorantura'][$degree] ?? $degree,
            'ru' => ['bachelor' => 'бакалавриата', 'master' => 'магистратуры', 'phd' => 'докторантуры'][$degree] ?? $degree,
            'ar' => ['bachelor' => 'البكالوريوس', 'master' => 'الماجستير', 'phd' => 'الدكتوراه'][$degree] ?? $degree,
            default => $degree,
        };
    }

    private function researchLine(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra amaliy tadqiqotlar, innovatsion loyihalar va talabalar ilmiy ishlarini rivojlantiradi.',
            'ru' => 'Кафедра развивает прикладные исследования, инновационные проекты и студенческую научную работу.',
            'ar' => 'يطور القسم البحوث التطبيقية والمشاريع الابتكارية والعمل العلمي للطلاب.',
            default => 'The department develops applied research, innovation projects and student scientific work.',
        };
    }

    private function cooperationLine(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Taʼlim jarayoni sanoat korxonalari, ilmiy tashkilotlar va hamkor universitetlar bilan integratsiyada olib boriladi.',
            'ru' => 'Учебный процесс интегрирован с промышленными предприятиями, научными организациями и партнерскими университетами.',
            'ar' => 'تتكامل العملية التعليمية مع المؤسسات الصناعية والمنظمات العلمية والجامعات الشريكة.',
            default => 'The educational process is integrated with industry enterprises, research organizations and partner universities.',
        };
    }

    private function plansLine(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra xalqaro hamkorlik, dual taʼlim va zamonaviy laboratoriyalarni kengaytirishni rejalashtiradi.',
            'ru' => 'Кафедра планирует развивать международное сотрудничество, дуальное образование и современные лаборатории.',
            'ar' => 'يخطط القسم لتطوير التعاون الدولي والتعليم المزدوج والمختبرات الحديثة.',
            default => 'The department plans to expand international cooperation, dual education and modern laboratories.',
        };
    }
}
