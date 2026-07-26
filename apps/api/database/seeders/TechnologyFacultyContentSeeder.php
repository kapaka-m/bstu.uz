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

class TechnologyFacultyContentSeeder extends Seeder
{
    private array $locales = ['en', 'uz', 'ru', 'ar'];

    public function run(): void
    {
        $faculty = Faculty::updateOrCreate(
            ['slug' => 'faculty-of-technology'],
            [
                'code' => 'TECH',
                'image' => 'faculties/technology.jpg',
                'icon' => 'flask-conical',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $this->seedFacultyTranslations($faculty);
        $departments = $this->seedDepartments($faculty);
        $this->seedPrograms($faculty, $departments);
        $this->seedLeadership($faculty);
        $this->seedDepartmentStaff($faculty, $departments);
    }

    private function seedFacultyTranslations(Faculty $faculty): void
    {
        $texts = [
            'en' => [
                'name' => 'Faculty of Technology',
                'short' => 'Technology',
                'description' => 'The Faculty of Technology trains engineers and technologists for chemical production, oil and gas processing, food production, agricultural product storage and processing, oil-fat technology, metrology, standardization and quality management. The faculty connects academic departments with industrial enterprises so students can reinforce theory through laboratories, production practice and graduation internships.',
                'sections' => [
                    ['title' => 'Industry Practice and Cooperation', 'body' => 'Specialists from partner enterprises participate in classes for graduating students, assess professional knowledge, sign internship agreements and help create employment pathways for students with strong knowledge and potential.'],
                    ['title' => 'Leadership', 'body' => 'The faculty is headed by Associate Professor Rashid Tokhtayevich Adizov, PhD in Technical Sciences. The dean office includes deputy deans for academic affairs and youth affairs.'],
                    ['title' => 'Academic Structure', 'items' => ['Oil and Gas Processing Technology', 'Food Technology and Service', 'Chemical Engineering', 'Storage, Processing, and Oil-Fat Technology of Agricultural Products', 'Oil and Gas Engineering', 'Metrology and Standardization']],
                ],
            ],
            'uz' => [
                'name' => 'Texnologiya fakulteti',
                'short' => 'Texnologiya',
                'description' => 'Texnologiya fakulteti kimyo ishlab chiqarishi, neft va gazni qayta ishlash, oziq-ovqat ishlab chiqarish, qishloq xo‘jaligi mahsulotlarini saqlash va qayta ishlash, yog‘-moy texnologiyasi, metrologiya, standartlashtirish va sifat menejmenti yo‘nalishlari uchun muhandis va texnolog kadrlarni tayyorlaydi.',
                'sections' => [
                    ['title' => 'Ishlab chiqarish amaliyoti va hamkorlik', 'body' => 'Hamkor korxonalar mutaxassislari bitiruvchi talabalar mashg‘ulotlarida ishtirok etadi, bilimini baholaydi, amaliyot shartnomalarini rasmiylashtiradi va salohiyatli talabalar bandligiga ko‘maklashadi.'],
                    ['title' => 'Rahbariyat', 'body' => 'Fakultetga texnika fanlari bo‘yicha PhD, dotsent Rashid To‘xtayevich Adizov rahbarlik qiladi. Dekanat tarkibida o‘quv ishlari va yoshlar masalalari bo‘yicha dekan o‘rinbosarlari faoliyat yuritadi.'],
                    ['title' => 'Akademik tuzilma', 'items' => ['Neft va gazni qayta ishlash texnologiyasi', 'Oziq-ovqat texnologiyasi va servis', 'Kimyoviy muhandislik', 'Qishloq xo‘jaligi mahsulotlarini saqlash, qayta ishlash va yog‘-moy texnologiyasi', 'Neft-gaz ishi', 'Metrologiya va standartlashtirish']],
                ],
            ],
            'ru' => [
                'name' => 'Факультет технологии',
                'short' => 'Технология',
                'description' => 'Факультет технологии готовит инженеров и технологов для химического производства, переработки нефти и газа, пищевой промышленности, хранения и переработки сельскохозяйственной продукции, масложировой технологии, метрологии, стандартизации и управления качеством.',
                'sections' => [
                    ['title' => 'Производственная практика и сотрудничество', 'body' => 'Специалисты предприятий-партнёров участвуют в занятиях выпускных курсов, оценивают профессиональные знания студентов, оформляют договоры практики и помогают создавать условия для трудоустройства сильных выпускников.'],
                    ['title' => 'Руководство', 'body' => 'Факультетом руководит доцент, PhD по техническим наукам Рашид Тохтаевич Адизов. В деканате работают заместители декана по учебной работе и по вопросам молодёжи.'],
                    ['title' => 'Академическая структура', 'items' => ['Технология переработки нефти и газа', 'Пищевая технология и сервис', 'Химическая инженерия', 'Хранение, переработка и масложировая технология сельскохозяйственной продукции', 'Нефтегазовое дело', 'Метрология и стандартизация']],
                ],
            ],
            'ar' => [
                'name' => 'كلية التكنولوجيا',
                'short' => 'التكنولوجيا',
                'description' => 'تعد كلية التكنولوجيا مهندسين وتقنيين في مجالات الإنتاج الكيميائي، ومعالجة النفط والغاز، والصناعات الغذائية، وتخزين ومعالجة المنتجات الزراعية، وتقنيات الزيوت والدهون، والمترولوجيا، والتقييس وإدارة الجودة.',
                'sections' => [
                    ['title' => 'التدريب العملي والتعاون الصناعي', 'body' => 'يشارك متخصصو المؤسسات الشريكة في تدريس طلبة السنوات النهائية وتقييم معارفهم المهنية وتوقيع اتفاقيات التدريب، كما يساهمون في تهيئة فرص توظيف للطلبة المتميزين.'],
                    ['title' => 'القيادة', 'body' => 'يرأس الكلية الأستاذ المشارك رشيد توختاييفيتش أديزوف، الحاصل على PhD في العلوم التقنية، ويعمل ضمن العمادة نائبان لشؤون التعليم وشؤون الشباب.'],
                    ['title' => 'الهيكل الأكاديمي', 'items' => ['تكنولوجيا معالجة النفط والغاز', 'تكنولوجيا الأغذية والخدمات', 'الهندسة الكيميائية', 'تكنولوجيا تخزين ومعالجة المنتجات الزراعية والزيوت والدهون', 'هندسة النفط والغاز', 'المترولوجيا والتقييس']],
                ],
            ],
        ];

        foreach ($this->locales as $locale) {
            FacultyTranslation::updateOrCreate(
                ['faculty_id' => $faculty->id, 'locale' => $locale],
                [
                    'name' => $texts[$locale]['name'],
                    'short_name' => $texts[$locale]['short'],
                    'description' => $texts[$locale]['description'],
                    'content_sections' => $texts[$locale]['sections'],
                    'meta_title' => $texts[$locale]['name'].' - BSTU',
                    'meta_description' => mb_substr($texts[$locale]['description'], 0, 200, 'UTF-8'),
                ]
            );
        }
    }

    private function seedDepartments(Faculty $faculty): array
    {
        $departments = [
            'oil-gas-refining-technology' => [
                'code' => 'OGPT',
                'head' => 'Ochilov Abduraxim Abdurasulovich',
                'email' => 'ochilov82@gmail.ru',
                'phone' => '+998 91 411 00 16',
                'reception' => 'Dushanba-Juma 14:00-16:00',
                'names' => ['en' => 'Department of Oil and Gas Processing Technology', 'uz' => 'Neft va gazni qayta ishlash texnologiyasi kafedrasi', 'ru' => 'Кафедра технологии переработки нефти и газа', 'ar' => 'قسم تكنولوجيا معالجة النفط والغاز'],
                'about' => ['en' => 'The department trains specialists for oil and gas processing enterprises, including petroleum refining, hydrocarbon gas processing, product quality control, petroleum chemistry, corrosion protection and modern physical and chemical analysis.', 'uz' => 'Kafedra neft va gazni qayta ishlash korxonalari uchun mutaxassislar tayyorlaydi hamda neftni qayta ishlash, uglevodorod gazlarini chuqur qayta ishlash, mahsulot sifatini nazorat qilish, neft kimyosi va korroziyadan himoyalash yo‘nalishlarini qamrab oladi.', 'ru' => 'Кафедра готовит специалистов для предприятий переработки нефти и газа, включая нефтепереработку, глубокую переработку углеводородных газов, контроль качества продукции, нефтехимию и защиту от коррозии.', 'ar' => 'يعد القسم متخصصين لمؤسسات معالجة النفط والغاز، ويغطي تكرير النفط، والمعالجة العميقة للغازات الهيدروكربونية، ومراقبة جودة المنتجات، وكيمياء النفط والحماية من التآكل.'],
                'sections' => [
                    'prepared_specialists' => ['60720500 - Gas Processing Technology', '60720600 - Oil and Oil-Gas Processing Technology', '70720600 - Technology of Oil and Gas Processing', '02.00.08 - Chemistry and Technology of Oil and Gas'],
                    'subjects' => ['Oil and Gas Processing Technology', 'Equipment for Oil and Gas Processing', 'Hydrocarbon Gas Processing Technology', 'Quality Control of Oil and Gas Products', 'Modern Methods of Physical and Chemical Analysis'],
                    'research' => ['Compositions for breaking oil and oil-water emulsions and improving rheological properties.', 'Technologies for odorant production based on mercaptans in natural gas.'],
                ],
            ],
            'food-technology-service' => [
                'code' => 'FTS',
                'head' => 'Qurbonov Murod Tashpulatovich',
                'email' => 'kurbanov.m@rambler.ru',
                'phone' => '+998 90 299 85 48',
                'reception' => 'Dushanba-Shanba 15:00-17:00',
                'names' => ['en' => 'Department of Food Technology and Service', 'uz' => 'Oziq-ovqat texnologiyasi va servis kafedrasi', 'ru' => 'Кафедра пищевой технологии и сервиса', 'ar' => 'قسم تكنولوجيا الأغذية والخدمات'],
                'about' => ['en' => 'The department trains specialists in food production, grain storage and processing, bakery and confectionery technologies, fermentation products, soft drinks, conservation technology, food safety, microbiology and catering service.', 'uz' => 'Kafedra oziq-ovqat ishlab chiqarish, donni saqlash va qayta ishlash, non, makaron va qandolat texnologiyalari, bijg‘ish mahsulotlari, alkogolsiz ichimliklar, konservatsiya, oziq-ovqat xavfsizligi va servis yo‘nalishlarida mutaxassislar tayyorlaydi.', 'ru' => 'Кафедра готовит специалистов по пищевому производству, хранению и переработке зерна, хлебобулочным и кондитерским технологиям, продуктам брожения, безалкогольным напиткам, консервированию, пищевой безопасности и сервису.', 'ar' => 'يعد القسم متخصصين في إنتاج الأغذية، وتخزين الحبوب ومعالجتها، وتقنيات المخبوزات والحلويات، ومنتجات التخمير، والمشروبات غير الكحولية، والحفظ، وسلامة الأغذية والخدمات.'],
                'sections' => [
                    'prepared_specialists' => ['60720100 - Food Technology', '60720300 - Wine, Fermentation Products and Soft Drinks Technology', '60720400 - Conservation Technology', '70720101 - Food Production and Processing Technology'],
                    'subjects' => ['Food Biochemistry', 'Food Microbiology', 'Food Commodity Science', 'Technology of Pasta Products', 'Technology of Grain Storage and Grain Products'],
                    'cooperation' => ['GIZ vocational education cooperation', 'University of Coimbra OUWOW wine technology project', 'Dresden University of Technology'],
                ],
            ],
            'chemical-technology' => [
                'code' => 'CHEM',
                'head' => 'Axmedov Voxid Nizomovich',
                'email' => 'vohid7@mail.ru',
                'phone' => '+998 90 511 59 58',
                'reception' => 'Dushanba-Juma 14:00-16:00',
                'names' => ['en' => 'Department of Chemical Engineering', 'uz' => 'Kimyoviy muhandislik kafedrasi', 'ru' => 'Кафедра химической инженерии', 'ar' => 'قسم الهندسة الكيميائية'],
                'about' => ['en' => 'The department covers inorganic and organic chemical technology, high-molecular compounds, silicate and refractory nonmetallic materials, mineral fertilizers, chemical process design and environmentally responsible production.', 'uz' => 'Kafedra noorganik va organik moddalar kimyoviy texnologiyasi, yuqori molekulyar birikmalar, silikat va qiyin eriydigan nometall materiallar, mineral o‘g‘itlar, kimyoviy jarayonlarni loyihalash va ekologik mas’uliyatli ishlab chiqarishni qamrab oladi.', 'ru' => 'Кафедра охватывает химическую технологию неорганических и органических веществ, высокомолекулярные соединения, силикатные и тугоплавкие неметаллические материалы, минеральные удобрения и проектирование химических процессов.', 'ar' => 'يغطي القسم تكنولوجيا المواد الكيميائية غير العضوية والعضوية، والمركبات عالية الجزيئية، والمواد السيليكاتية وغير المعدنية المقاومة للحرارة، والأسمدة المعدنية وتصميم العمليات الكيميائية.'],
                'sections' => [
                    'prepared_specialists' => ['60710100 - Chemical Engineering', '60710200 - Biotechnology', '60710300 - Printing and Packaging Engineering', '70710101 - Chemical Technology tracks', '70710103 - Polymer Production'],
                    'subjects' => ['General Chemical Technology', 'Inorganic Chemical Technology', 'Organic Substances Chemical Technology', 'Polymer Technology', 'Silicate Materials Technology'],
                    'research' => ['Chemical technology of inorganic substances and materials.', 'Colloid and membrane chemistry.', 'Chemical processes and devices of food industry.'],
                ],
            ],
            'agricultural-products-storage-oil-fat-technology' => [
                'code' => 'APOFT',
                'head' => 'Majidova Nargiza Kaxramonovna',
                'email' => 'nargiz-1234n@mail.ru',
                'phone' => '+998 97 305 95 59',
                'reception' => 'Dushanba-Juma 14:00-16:00',
                'names' => ['en' => 'Department of Storage, Processing, and Oil-Fat Technology of Agricultural Products', 'uz' => 'Qishloq xo‘jaligi mahsulotlarini saqlash, qayta ishlash va yog‘-moy texnologiyasi kafedrasi', 'ru' => 'Кафедра хранения, переработки и масложировой технологии сельскохозяйственной продукции', 'ar' => 'قسم تكنولوجيا تخزين ومعالجة المنتجات الزراعية والزيوت والدهون'],
                'about' => ['en' => 'The department began in August 2022 after separation from Food Technology. It trains specialists in storage and processing of agricultural products, horticulture and viticulture, food technology oil-product tracks and oil processing technology.', 'uz' => 'Kafedra 2022-yil avgust oyida Oziq-ovqat texnologiyasi kafedrasidan ajralib chiqqan holda faoliyat boshlagan. U qishloq xo‘jaligi mahsulotlarini saqlash va qayta ishlash, meva-sabzavotchilik va uzumchilik, yog‘-moy mahsulotlari hamda moyni qayta ishlash yo‘nalishlari bo‘yicha mutaxassislar tayyorlaydi.', 'ru' => 'Кафедра начала деятельность в августе 2022 года после выделения из кафедры пищевой технологии. Она готовит специалистов по хранению и переработке сельхозпродукции, плодоовощеводству и виноградарству, пищевым масложировым направлениям и технологии переработки масел.', 'ar' => 'بدأ القسم عمله في أغسطس 2022 بعد انفصاله عن قسم تكنولوجيا الأغذية، ويعد متخصصين في تخزين ومعالجة المنتجات الزراعية، والبستنة والكروم، ومسارات تكنولوجيا الأغذية المرتبطة بالزيوت ومنتجاتها.'],
                'sections' => [
                    'prepared_specialists' => ['60810700 - Storage and Processing Technology of Agricultural Products', '60811000 - Horticulture and Viticulture', '60720100 - Food Technology (Oil and Oil Products)', '70720101 - Oil Processing Technology'],
                    'subjects' => ['Technology of Storage of Grain and Grain Products', 'Oil and Fat Production Technology', 'Technology of Processing Agricultural Products', 'Food Safety and Quality Control'],
                    'publications' => ['Fundamentals of Food Technology textbook', 'Technology of storage of grain and grain products textbook'],
                ],
            ],
            'oil-gas-engineering-upstream-downstream' => [
                'code' => 'OGE',
                'head' => 'Sharipov Qaxramon Qandiyorovich',
                'email' => 'kahramon.sharipov@mail.ru',
                'phone' => '+998 93 453 69 69',
                'reception' => 'Every day 15:00-16:00',
                'names' => ['en' => 'Department of Oil and Gas Engineering', 'uz' => 'Neft-gaz ishi kafedrasi', 'ru' => 'Кафедра нефтегазового дела', 'ar' => 'قسم هندسة النفط والغاز'],
                'about' => ['en' => 'The department trains specialists for oil and gas field development, start-up and exploitation of oil and gas fields, drilling, field machinery, gas supply and energy-efficient operation of petroleum infrastructure.', 'uz' => 'Kafedra neft va gaz konlarini ishlatish, konlarni ishga tushirish va ekspluatatsiya qilish, burg‘ilash, kon mashina-uskunalari, gaz ta’minoti hamda neft-gaz infratuzilmasini energiya tejamkor boshqarish bo‘yicha mutaxassislar tayyorlaydi.', 'ru' => 'Кафедра готовит специалистов по разработке нефтяных и газовых месторождений, вводу и эксплуатации месторождений, бурению, оборудованию промыслов, газоснабжению и энергоэффективной эксплуатации нефтегазовой инфраструктуры.', 'ar' => 'يعد القسم متخصصين في تطوير حقول النفط والغاز، وتشغيل واستثمار الحقول، والحفر، ومعدات الحقول، وإمدادات الغاز والتشغيل الموفر للطاقة للبنية التحتية النفطية.'],
                'sections' => [
                    'prepared_specialists' => ['60721100 - Oil and Gas Engineering', '60720900 - Geology, Exploration and Prospecting of Mineral Deposits', '70721802 - Machinery and Equipment for Oil and Gas Fields'],
                    'research' => ['Reducing energy consumption in gas supply.', 'Technology for production of odorants based on mercaptans containing natural gas.'],
                    'publications' => ['Earth and Environmental Science articles on oil-water emulsions, gas supply energy factors and waste yellow oil research.'],
                ],
            ],
            'metrology-standardization-quality-control' => [
                'code' => 'MSQC',
                'head' => 'Tairov Bakhtiyor Bobokulovich',
                'email' => 'b.toirov@mail.ru',
                'phone' => '+998 93 471 00 65',
                'reception' => 'Dushanba-Juma 14:00-16:00',
                'names' => ['en' => 'Department of Metrology and Standardization', 'uz' => 'Metrologiya va standartlashtirish kafedrasi', 'ru' => 'Кафедра метрологии и стандартизации', 'ar' => 'قسم المترولوجيا والتقييس'],
                'about' => ['en' => 'The department began in August 2019 and trains specialists in metrology, standardization, certification and product quality management. Its scientific potential includes professors, associate professors, senior teachers, assistants, doctoral students and independent researchers.', 'uz' => 'Kafedra 2019-yil avgust oyida faoliyat boshlagan bo‘lib, metrologiya, standartlashtirish, sertifikatlashtirish va mahsulot sifati menejmenti bo‘yicha mutaxassislar tayyorlaydi. Ilmiy salohiyat professor-o‘qituvchilar, doktorantlar va mustaqil tadqiqotchilar faoliyati bilan mustahkamlanadi.', 'ru' => 'Кафедра начала деятельность в августе 2019 года и готовит специалистов по метрологии, стандартизации, сертификации и управлению качеством продукции. Научный потенциал поддерживается профессорами, доцентами, старшими преподавателями, ассистентами, докторантами и независимыми исследователями.', 'ar' => 'بدأ القسم نشاطه في أغسطس 2019 ويعد متخصصين في المترولوجيا والتقييس والشهادات وإدارة جودة المنتجات. وتدعمه هيئة علمية تضم أساتذة وأساتذة مشاركين ومدرسين ومساعدين وطلبة دكتوراه وباحثين مستقلين.'],
                'sections' => [
                    'prepared_specialists' => ['60710800 - Metrology and Standardization', '60711300 - Metrology, Standardization and Product Quality Management', '70710802 - Metrology, Standardization and Quality Management'],
                    'subjects' => ['Fundamentals of Metrology', 'Methods and Means of Measurement', 'Product Quality Control', 'Basics of Standardization', 'Technical Regulation and Standardization', 'Legal Foundations of Metrology and Certification'],
                    'cooperation' => ['South Kazakhstan State University named after M. Auezov', 'Kazakh National University named after al-Farabi joint master cooperation for 2025-2026'],
                    'plans' => ['Part-time training opportunities', 'Retraining courses for industry specialists', 'Certified professional training', 'Modern measuring laboratories and simulation software'],
                ],
            ],
        ];

        $models = [];
        $sort = 1;
        foreach ($departments as $slug => $item) {
            $department = Department::updateOrCreate(
                ['slug' => $slug],
                [
                    'faculty_id' => $faculty->id,
                    'code' => $item['code'],
                    'image' => 'departments/'.$slug.'.jpg',
                    'icon' => 'building-2',
                    'head_name' => $item['head'],
                    'email' => $item['email'],
                    'phone' => $item['phone'],
                    'reception_time' => $item['reception'],
                    'source_url' => null,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]
            );

            foreach ($this->locales as $locale) {
                DepartmentTranslation::updateOrCreate(
                    ['department_id' => $department->id, 'locale' => $locale],
                    [
                        'name' => $item['names'][$locale],
                        'short_name' => Str::replace('Department of ', '', $item['names'][$locale]),
                        'description' => $item['about'][$locale],
                        'content_sections' => $this->localizeSections($item['sections'], $locale),
                        'meta_title' => $item['names'][$locale].' - BSTU',
                        'meta_description' => mb_substr($item['about'][$locale], 0, 200, 'UTF-8'),
                    ]
                );
            }

            $models[$slug] = $department;
        }

        return $models;
    }

    private function seedPrograms(Faculty $faculty, array $departments): void
    {
        Program::where('faculty_id', $faculty->id)
            ->orWhereIn('department_id', collect($departments)->pluck('id')->all())
            ->update(['is_active' => false]);

        $programs = [
            ['chemical-engineering', '60710100', 'bachelor', 'chemical-technology', 'Chemical Engineering'],
            ['biotechnology', '60710200', 'bachelor', 'chemical-technology', 'Biotechnology'],
            ['printing-packaging-engineering', '60710300', 'bachelor', 'chemical-technology', 'Printing and Packaging Engineering'],
            ['metrology-standardization', '60710800', 'bachelor', 'metrology-standardization-quality-control', 'Metrology and Standardization'],
            ['agricultural-products-storage-processing', '60810700', 'bachelor', 'agricultural-products-storage-oil-fat-technology', 'Storage and Processing Technology of Agricultural Products'],
            ['horticulture-viticulture', '60811000', 'bachelor', 'agricultural-products-storage-oil-fat-technology', 'Horticulture and Viticulture'],
            ['food-technology', '60720100', 'bachelor', 'food-technology-service', 'Food Technology'],
            ['gas-processing-technology', '60720500', 'bachelor', 'oil-gas-refining-technology', 'Gas Processing Technology', 'Deep Gas Processing Technology'],
            ['oil-gas-processing-technology', '60720600', 'bachelor', 'oil-gas-refining-technology', 'Oil and Oil-Gas Processing Technology'],
            ['geology-exploration-mineral-deposits', '60720900', 'bachelor', 'oil-gas-engineering-upstream-downstream', 'Geology, Exploration and Prospecting of Mineral Deposits'],
            ['oil-gas-engineering', '60721100', 'bachelor', 'oil-gas-engineering-upstream-downstream', 'Oil and Gas Engineering'],
            ['polymer-production-technology', '70710103', 'master', 'chemical-technology', 'Chemical Technology of High-Molecular Compounds', 'Polymer Production'],
            ['grain-storage-processing-technology', '70720101', 'master', 'food-technology-service', 'Technology of Food Production and Processing', 'Grain Storage and Processing Technology'],
            ['oil-processing-technology', '70720101', 'master', 'agricultural-products-storage-oil-fat-technology', 'Technology of Food Production and Processing', 'Oil Processing Technology'],
            ['inorganic-chemical-technology', '70710101', 'master', 'chemical-technology', 'Chemical Technology', 'Inorganic Substances Chemical Technology'],
            ['silicate-refractory-materials-technology', '70710101', 'master', 'chemical-technology', 'Chemical Technology', 'Silicate and Refractory Nonmetallic Materials Technology'],
            ['organic-chemical-technology', '70710101', 'master', 'chemical-technology', 'Chemical Technology', 'Organic Substances Chemical Technology'],
            ['metrology-standardization-quality-management-master', '70710802', 'master', 'metrology-standardization-quality-control', 'Metrology, Standardization and Quality Management'],
        ];

        foreach ($programs as $index => $item) {
            [$slug, $officialCode, $degree, $departmentSlug, $name] = $item;
            $track = $item[5] ?? null;
            $department = $departments[$departmentSlug] ?? null;
            if (! $department) {
                continue;
            }

            $program = Program::updateOrCreate(
                ['slug' => $slug],
                [
                    'faculty_id' => $faculty->id,
                    'department_id' => $department->id,
                    'code' => $slug,
                    'official_code' => $officialCode,
                    'track' => $track,
                    'degree' => $degree,
                    'duration_years' => $degree === 'master' ? 2 : 4,
                    'study_mode' => 'full_time',
                    'language_of_study' => 'uzbek',
                    'tuition_fee' => 0,
                    'currency' => 'UZS',
                    'image' => 'programs/'.$slug.'.jpg',
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            foreach ($this->locales as $locale) {
                $localizedName = $this->programName($name, $track, $locale);
                ProgramTranslation::updateOrCreate(
                    ['program_id' => $program->id, 'locale' => $locale],
                    [
                        'name' => $localizedName,
                        'description' => $this->programDescription($localizedName, $degree, $locale),
                        'requirements' => $this->programRequirements($locale),
                        'documents' => $this->programDocuments($locale),
                        'curriculum_summary' => $this->programCurriculum($departmentSlug, $locale),
                        'career_opportunities' => $this->programCareer($departmentSlug, $locale),
                        'meta_title' => $officialCode.' - '.$localizedName,
                        'meta_description' => $this->programDescription($localizedName, $degree, $locale),
                    ]
                );
            }
        }
    }

    private function seedLeadership(Faculty $faculty): void
    {
        StaffProfile::where('faculty_id', $faculty->id)
            ->whereNull('department_id')
            ->update(['is_active' => false]);

        $leaders = [
            ['adizov-rashid-tokhtayevich', 'Adizov Rashid Tokhtayevich', 'Dean of the Faculty of Technology', '+998 93 479 77 65', 'adizov.rashid@mail.ru', 'Every day 14:00-16:00 except Monday and Saturday'],
            ['safarov-jasur-alijon-ogli', 'Safarov Jasur Alijon o‘g‘li', 'Deputy Dean for Academic Affairs', '+998 93 688 56 88', 'jasur.safarov1993@mail.ru', 'Every day 14:00-16:00'],
            ['bozorov-dilmurod-xolmurodovich', 'Bozorov Dilmurod Xolmurodovich', 'Deputy Dean for Youth Affairs', '+998 90 744 47 97', 'd.bozorov_78@mail.ru', 'Every day 14:00-16:00'],
        ];

        foreach ($leaders as $index => [$slug, $name, $position, $phone, $email, $office]) {
            $staff = StaffProfile::updateOrCreate(
                ['slug' => $slug],
                [
                    'department_id' => null,
                    'faculty_id' => $faculty->id,
                    'photo' => 'staff/'.$slug.'.jpg',
                    'email' => $email,
                    'phone' => $phone,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            foreach ($this->locales as $locale) {
                StaffProfileTranslation::updateOrCreate(
                    ['staff_profile_id' => $staff->id, 'locale' => $locale],
                    [
                        'full_name' => $name,
                        'position' => $this->position($position, $locale),
                        'bio' => $this->position($position, $locale).' - '.$office,
                        'office' => $office,
                    ]
                );
            }
        }
    }

    private function seedDepartmentStaff(Faculty $faculty, array $departments): void
    {
        $staffByDepartment = [
            'oil-gas-refining-technology' => [
                ['Ochilov Abduraxim Abdurasulovich', 'Head of Department, Associate Professor, PhD', 'ochilov82@gmail.ru', '+998 91 411 00 16'],
                ['Maxmudov Muxtor Jamolovich', 'DSc, Professor'],
                ['Kadirov Baxtiyor Ganijonovich', 'Associate Professor, PhD'],
                ['Nazarov Azizbek Bobir o‘g‘li', 'Assistant'],
            ],
            'food-technology-service' => [
                ['Qurbonov Murod Tashpulatovich', 'Head of Department', 'kurbanov.m@rambler.ru', '+998 90 299 85 48'],
                ['Atamuradova Tamara Ivanovna', 'Candidate of Technical Sciences, Associate Professor'],
                ['Ergasheva Khusrabo Bobonazarovna', 'Candidate of Technical Sciences, Associate Professor'],
                ['Khaydarzoda Lolitta Negmatovna', 'Candidate of Technical Sciences, Associate Professor'],
            ],
            'chemical-technology' => [
                ['Axmedov Voxid Nizomovich', 'Head of Department', 'vohid7@mail.ru', '+998 90 511 59 58'],
            ],
            'agricultural-products-storage-oil-fat-technology' => [
                ['Majidova Nargiza Kaxramonovna', 'Head of Department', 'nargiz-1234n@mail.ru', '+998 97 305 95 59'],
            ],
            'oil-gas-engineering-upstream-downstream' => [
                ['Sharipov Qaxramon Qandiyorovich', 'Head of Department, Associate Professor, PhD', 'kahramon.sharipov@mail.ru', '+998 93 453 69 69'],
                ['Bozorov Jo‘rabek To‘ronovich', 'Associate Professor, PhD'],
                ['Ochilov Abdurahim Abdurasulovich', 'Associate Professor, PhD'],
                ['Rakhimov Bobomurod Rustamovich', 'Assistant, PhD'],
                ['Obidov Hamid Olimovich', 'Senior Lecturer'],
                ['Sattorov Mirvohid Olimovich', 'Senior Lecturer'],
                ['Yamaletdinova Aygul Akhmadovna', 'Assistant'],
                ['Bokieva Shakhnoza Komilovna', 'Assistant'],
            ],
            'metrology-standardization-quality-control' => [
                ['Tairov Bakhtiyor Bobokulovich', 'Head of Department, Candidate of Technical Sciences, Associate Professor', 'b.toirov@mail.ru', '+998 93 471 00 65'],
                ['Qurbanov Abdiraxim Axmedovich', 'Professor'],
                ['Avliyakulov Nadir Nizomovich', 'Candidate of Technical Sciences, Associate Professor'],
                ['Hasanova Zebo Davlatovna', 'PhD, Associate Professor'],
                ['Tosheva Gulnora Djurayevna', 'PhD, Associate Professor'],
                ['Davlyatova Mavlyuda Bakhtiyorovna', 'PhD, Associate Professor'],
                ['Boltaeva Zulfiya Zarifovna', 'PhD, Senior Lecturer'],
                ['Sayidakhmedov Ravshan Rajabovich', 'Senior Teacher'],
                ['Khojjiyev Administrator Yangibayevich', 'PhD, Senior Lecturer'],
                ['Khaidarov Shukhrat Khikmatullayevich', 'Assistant'],
                ['Shadiyev Suxrob Sadilloyevich', 'Assistant'],
                ['Qarshiyev Zohid Abdurahim o‘g‘li', 'Assistant'],
                ['Azimova Firuza Kamolovna', 'Teacher-trainee'],
                ['Yodgorova Ma’mura Orifovna', 'Lecturer, trainee'],
                ['Kamalova Mukhlisa Khudoyberdievna', 'Teacher, trainee'],
            ],
        ];

        StaffProfile::whereIn('department_id', collect($departments)->pluck('id')->all())->update(['is_active' => false]);

        foreach ($staffByDepartment as $departmentSlug => $members) {
            $department = $departments[$departmentSlug] ?? null;
            if (! $department) {
                continue;
            }

            foreach ($members as $index => $member) {
                [$name, $position] = $member;
                $staff = StaffProfile::updateOrCreate(
                    ['slug' => Str::slug($departmentSlug.'-'.$name)],
                    [
                        'department_id' => $department->id,
                        'faculty_id' => $faculty->id,
                        'photo' => null,
                        'email' => $member[2] ?? null,
                        'phone' => $member[3] ?? null,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );

                foreach ($this->locales as $locale) {
                    StaffProfileTranslation::updateOrCreate(
                        ['staff_profile_id' => $staff->id, 'locale' => $locale],
                        [
                            'full_name' => $name,
                            'position' => $this->position($position, $locale),
                            'bio' => $this->position($position, $locale),
                            'office' => 'Department Office',
                        ]
                    );
                }
            }
        }
    }

    private function localizeSections(array $sections, string $locale): array
    {
        $labels = [
            'en' => ['prepared_specialists' => 'Prepared Specialists', 'subjects' => 'Subjects', 'research' => 'Research Work', 'cooperation' => 'Cooperation', 'publications' => 'Publications', 'plans' => 'Prospective Plans'],
            'uz' => ['prepared_specialists' => 'Tayyorlanadigan mutaxassislar', 'subjects' => 'Fanlar', 'research' => 'Ilmiy-tadqiqot ishlari', 'cooperation' => 'Hamkorlik', 'publications' => 'Nashrlar', 'plans' => 'Istiqboldagi rejalar'],
            'ru' => ['prepared_specialists' => 'Подготавливаемые специалисты', 'subjects' => 'Дисциплины', 'research' => 'Научно-исследовательская работа', 'cooperation' => 'Сотрудничество', 'publications' => 'Публикации', 'plans' => 'Перспективные планы'],
            'ar' => ['prepared_specialists' => 'التخصصات التي يتم إعدادها', 'subjects' => 'المقررات', 'research' => 'الأعمال البحثية', 'cooperation' => 'التعاون', 'publications' => 'المنشورات', 'plans' => 'الخطط المستقبلية'],
        ];

        return collect($sections)->map(fn ($items, $key) => [
            'key' => $key,
            'title' => $labels[$locale][$key] ?? Str::headline($key),
            'items' => $items,
        ])->values()->all();
    }

    private function programName(string $name, ?string $track, string $locale): string
    {
        $value = $track ? "{$name} ({$track})" : $name;

        return match ($locale) {
            'uz' => $value,
            'ru' => $value,
            'ar' => $value,
            default => $value,
        };
    }

    private function programDescription(string $name, string $degree, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} yo‘nalishi Texnologiya fakultetida kafedra bazasidagi nazariy, laboratoriya va ishlab chiqarish amaliyoti bilan olib boriladi.",
            'ru' => "Программа {$name} реализуется на факультете технологии с кафедральной теоретической подготовкой, лабораторными занятиями и производственной практикой.",
            'ar' => "يُقدَّم برنامج {$name} في كلية التكنولوجيا من خلال إعداد نظري ومختبري وتدريب عملي مرتبط بالقسم.",
            default => "{$name} is offered by the Faculty of Technology with department-based theoretical study, laboratory training and industrial practice.",
        };
    }

    private function programRequirements(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Qabul talablari amaldagi qabul qoidalari va universitet qabul komissiyasi orqali belgilanadi.',
            'ru' => 'Требования к поступлению определяются действующими правилами приёма и приёмной комиссией университета.',
            'ar' => 'تُحدَّد متطلبات القبول وفق لوائح القبول المعمول بها ومن خلال لجنة القبول في الجامعة.',
            default => 'Admission requirements are defined by the current admission rules and the university admissions office.',
        };
    }

    private function programDocuments(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Pasport, ta’lim hujjati va qabul jarayonida talab qilinadigan boshqa hujjatlar.',
            'ru' => 'Паспорт, документ об образовании и другие документы, требуемые в процессе приёма.',
            'ar' => 'جواز السفر أو وثيقة الهوية، ووثيقة التعليم، وأي مستندات أخرى مطلوبة أثناء إجراءات القبول.',
            default => 'Passport or identity document, education certificate and other documents requested during the admission process.',
        };
    }

    private function programCurriculum(string $departmentSlug, string $locale): string
    {
        return match ($locale) {
            'uz' => 'O‘quv reja kafedraning asosiy fanlari, laboratoriya ishlari, ishlab chiqarish amaliyoti va bitiruv malakaviy ishini qamrab oladi.',
            'ru' => 'Учебный план включает профильные дисциплины кафедры, лабораторные работы, производственную практику и выпускную квалификационную работу.',
            'ar' => 'تشمل الخطة الدراسية مقررات القسم الأساسية، والعمل المخبري، والتدريب الصناعي، ومشروع التخرج.',
            default => 'The curriculum includes core department subjects, laboratory work, industrial practice and a graduation project.',
        };
    }

    private function programCareer(string $departmentSlug, string $locale): string
    {
        return match ($locale) {
            'uz' => 'Bitiruvchilar ishlab chiqarish korxonalari, laboratoriyalar, sifat nazorati bo‘limlari, loyiha va ilmiy-tadqiqot tashkilotlarida faoliyat yuritishi mumkin.',
            'ru' => 'Выпускники могут работать на производственных предприятиях, в лабораториях, отделах контроля качества, проектных и научно-исследовательских организациях.',
            'ar' => 'يمكن للخريجين العمل في المؤسسات الإنتاجية والمختبرات وأقسام مراقبة الجودة ومؤسسات التصميم والبحث العلمي.',
            default => 'Graduates can work in industrial enterprises, laboratories, quality-control units, design organizations and research institutions.',
        };
    }

    private function position(string $position, string $locale): string
    {
        if ($locale === 'en') {
            return $position;
        }

        return $position;
    }
}
