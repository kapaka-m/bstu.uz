<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceTranslation;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'slug' => 'international-student-dormitory',
                'icon' => 'home',
                'image' => 'services/dormitory.jpg',
                'sort_order' => 1,
                'translations' => [
                    'en' => [
                        'title' => 'International Student Dormitory',
                        'description' => 'Comfortable, fully furnished rooms on campus with high-speed Wi-Fi, laundry rooms, and security.',
                        'content' => 'The dormitory is located just a 5-minute walk from academic buildings. Rooms are shared by 2 students. Security guards are active 24/7. Fees are paid per semester.',
                    ],
                    'uz' => [
                        'title' => 'Xalqaro talabalar turar joyi',
                        'description' => 'Yuqori tezlikdagi Wi-Fi, kir yuvish xonalari va xavfsizlik xizmatiga ega qulay, to‘liq jihozlangan xonalar.',
                        'content' => 'Talabalar turar joyi o‘quv binolaridan 5 daqiqalik masofada joylashgan. Xonalar 2 kishilik. Xavfsizlik xizmati 24/7 ishlaydi. To‘lovlar har semestrda amalga oshiriladi.',
                    ],
                    'ru' => [
                        'title' => 'Общежитие для иностранных студентов',
                        'description' => 'Комфортабельные, полностью меблированные комнаты на территории кампуса с высокоскоростным Wi-Fi, прачечными и охраной.',
                        'content' => 'Общежитие расположено всего в 5 минутах ходьбы от учебных корпусов. Комнаты рассчитаны на 2 студентов. Круглосуточная охрана. Оплата производится за семестр.',
                    ],
                    'ar' => [
                        'title' => 'سكن الطلاب الدوليين',
                        'description' => 'غرف مريحة ومفروشة بالكامل في الحرم الجامعي مع خدمة Wi-Fi سريعة، وغرف غسيل، وحراسة أمنية.',
                        'content' => 'يقع السكن الجامعي على بعد 5 دقائق فقط سيرًا على الأقدام من المباني الأكاديمية. الغرف مشتركة لطالبين. الحراس الأمنيون متواجدون 24/7. تُدفع الرسوم لكل فصل دراسي.',
                    ],
                ],
            ],
            [
                'slug' => 'chemical-food-technologies',
                'icon' => 'activity',
                'image' => 'services/chemical-food.jpg',
                'sort_order' => 2,
                'translations' => [
                    'en' => [
                        'title' => 'Chemical & Food Technologies',
                        'description' => 'Focusing on industrial chemical synthesis, advanced biotechnology, safety protocols, and modern food processing methodologies.',
                        'content' => 'Comprehensive laboratory research and academic training in food safety, industrial chemical engineering, and organic compounds.',
                    ],
                    'uz' => [
                        'title' => 'Kimyoviy va oziq-ovqat texnologiyalari',
                        'description' => 'Sanoat kimyoviy sintezi, ilg‘or biotexnologiya, xavfsizlik protokollari va zamonaviy oziq-ovqat mahsulotlarini qayta ishlash metodologiyalari.',
                        'content' => 'Oziq-ovqat xavfsizligi, sanoat kimyo muhandisligi va organik birikmalar bo‘yicha keng qamrovli laboratoriya tadqiqotlari va akademik tayyorgarlik.',
                    ],
                    'ru' => [
                        'title' => 'Химические и пищевые технологии',
                        'description' => 'Фокус на промышленном химическом синтезе, передовой биотехнологии, протоколах безопасности и современных методологиях пищевой промышленности.',
                        'content' => 'Комплексные лабораторные исследования и академическая подготовка в области безопасности пищевых продуктов, промышленной химической инженерии и органических соединений.',
                    ],
                    'ar' => [
                        'title' => 'التكنولوجيا الكيميائية والغذائية',
                        'description' => 'التركيز على التخليق الكيميائي الصناعي، والتكنولوجيا الحيوية المتقدمة، وبروتوكولات السلامة، ومنهجيات معالجة الأغذية الحديثة.',
                        'content' => 'أبحاث مخبرية شاملة وتدريب أكاديمي في مجال سلامة الأغذية، والهندسة الكيميائية الصناعية، والمركبات العضوية.',
                    ],
                ],
            ],
            [
                'slug' => 'oil-gas-technology',
                'icon' => 'broadcast',
                'image' => 'services/oil-gas.jpg',
                'sort_order' => 3,
                'translations' => [
                    'en' => [
                        'title' => 'Oil & Gas Technology',
                        'description' => 'Specialized training in petroleum refining, gas processing, extraction methods, and engineering support for the national energy sector.',
                        'content' => 'Providing engineering expertise and research on upstream and downstream operations, hydrocarbon transportation, and well development.',
                    ],
                    'uz' => [
                        'title' => 'Neft va gaz texnologiyasi',
                        'description' => 'Neftni qayta ishlash, gazni qayta ishlash, qazib olish usullari va milliy energetika sektori uchun muhandislik yordami bo‘yicha ixtisoslashtirilgan tayyorgarlik.',
                        'content' => 'Neft va gaz qazib olish hamda qayta ishlash operatsiyalari, uglevodorodlarni tashish va quduqlarni o‘zlashtirish bo‘yicha muhandislik tajribasi va tadqiqotlar.',
                    ],
                    'ru' => [
                        'title' => 'Нефтегазовые технологии',
                        'description' => 'Специализированное обучение в области нефтепереработки, газопереработки, методов добычи и инженерной поддержки национального энергетического сектора.',
                        'content' => 'Предоставление инженерного опыта и исследований в области добычи и переработки углеводородов, их транспортировки и разработки скважин.',
                    ],
                    'ar' => [
                        'title' => 'تكنولوجيا النفط والغاز',
                        'description' => 'تدريب متخصص في تكرير البترول، ومعالجة الغاز، وطرق الاستخراج، والدعم الهندسي لقطاع الطاقة الوطني.',
                        'content' => 'تقديم الخبرة الهندسية والأبحاث في عمليات المنبع والمصب، ونقل الهيدروكربونات، وتطوير الآبار.',
                    ],
                ],
            ],
            [
                'slug' => 'engineering-construction',
                'icon' => 'easel',
                'image' => 'services/engineering-construction.jpg',
                'sort_order' => 4,
                'translations' => [
                    'en' => [
                        'title' => 'Engineering & Construction',
                        'description' => 'Civil engineering, architecture, structural mechanics, urban development, and structural integrity modeling of public infrastructures.',
                        'content' => 'Training in computer-aided structural design, modern architecture, geotechnical engineering, and seismic resilience standards.',
                    ],
                    'uz' => [
                        'title' => 'Muhandislik va qurilish',
                        'description' => 'Fuqaro qurilishi, arxitektura, qurilish mexanikasi, shaharsozlik va jamoat infratuzilmalarining mustahkamligini modellashtirish.',
                        'content' => 'Kompyuter yordamida qurilishni loyihalash, zamonaviy arxitektura, geotexnik muhandislik va seysmik chidamlilik standartlari bo‘yicha o‘qitish.',
                    ],
                    'ru' => [
                        'title' => 'Инженерия и строительство',
                        'description' => 'Гражданское строительство, архитектура, строительная механика, градостроительство и моделирование структурной целостности общественных инфраструктур.',
                        'content' => 'Обучение компьютерному проектированию строительных конструкций, современной архитектуре, геотехнической инженерии и стандартам сейсмостойкости.',
                    ],
                    'ar' => [
                        'title' => 'الهندسة والإنشاءات',
                        'description' => 'الهندسة المدنية، الهندسة المعمارية، ميكانيكا الهياكل، التنمية الحضرية، ونمذجة السلامة الهيكلية للبنى التحتية العامة.',
                        'content' => 'التدريب على التصميم الهيكلي بمساعدة الحاسوب، الهندسة المعمارية الحديثة، الهندسة الجيوتقنية، ومعايير مقاومة الزلازل.',
                    ],
                ],
            ],
            [
                'slug' => 'power-ict',
                'icon' => 'bounding-box',
                'image' => 'services/power-ict.jpg',
                'sort_order' => 5,
                'translations' => [
                    'en' => [
                        'title' => 'Power Engineering & ICT',
                        'description' => 'Focused on electrical power networks, renewable energy grids, computational systems, computer science, and network telecommunications.',
                        'content' => 'Modern education in software engineering, database management, smart power grid monitoring, and solar energy installations.',
                    ],
                    'uz' => [
                        'title' => 'Energetika va AKT',
                        'description' => 'Elektr tarmoqlari, qayta tiklanadigan energiya tizimlari, hisoblash tizimlari, kompyuter ilmlari va tarmoq telekommunikatsiyalariga yo‘naltirilgan.',
                        'content' => 'Dasturiy ta‘minot muhandisligi, ma‘lumotlar bazasini boshqarish, aqlli energiya tarmoqlarini monitoring qilish va quyosh energiyasi qurilmalari bo‘yicha zamonaviy ta‘lim.',
                    ],
                    'ru' => [
                        'title' => 'Энергетика и ИКТ',
                        'description' => 'Фокус на электрических сетях, системах возобновляемой энергии, вычислительных системах, информатике и сетевых телекоммуникациях.',
                        'content' => 'Современное образование в области программной инженерии, управления базами данных, мониторинга интеллектуальных энергосетей и солнечных энергоустановок.',
                    ],
                    'ar' => [
                        'title' => 'هندسة الطاقة وتكنولوجيا المعلومات والاتصالات',
                        'description' => 'التركيز على شبكات الطاقة الكهربائية، وشبكات الطاقة المتجددة، والأنظمة الحسابية، وعلوم الحاسوب، والاتصالات السلكية واللاسلكية.',
                        'content' => 'تعليم حديث في هندسة البرمجيات، إدارة قواعد البيانات، مراقبة شبكات الطاقة الذكية، وتركيبات الطاقة الشمسية.',
                    ],
                ],
            ],
            [
                'slug' => 'textile-light-industry',
                'icon' => 'calendar-4-week',
                'image' => 'services/textile.jpg',
                'sort_order' => 6,
                'translations' => [
                    'en' => [
                        'title' => 'Textile & Light Industry',
                        'description' => 'Innovations in cotton and textile manufacturing, light industry production methods, logistics, and land transport systems.',
                        'content' => 'Specialized research in raw cotton processing, spinning technologies, garment design, transport logistics, and logistics auditing.',
                    ],
                    'uz' => [
                        'title' => 'To‘qimachilik va yengil sanoat',
                        'description' => 'Paxta va to‘qimachilik ishlab chiqarishidagi innovatsiyalar, yengil sanoat ishlab chiqarish usullari, logistika va yer usti transport tizimlari.',
                        'content' => 'Paxta xomashyosini qayta ishlash, yigirish texnologiyalari, kiyim-kechak dizayni, transport logistikasi va logistika auditi sohasida ixtisoslashtirilgan tadqiqotlar.',
                    ],
                    'ru' => [
                        'title' => 'Текстильная и легкая промышленность',
                        'description' => 'Инновации в производстве хлопка и текстиля, методы производства легкой промышленности, логистика и наземные транспортные системы.',
                        'content' => 'Специализированные исследования в области переработки хлопка-сырца, прядильных технологий, дизайна одежды, транспортной логистики и логистического аудита.',
                    ],
                    'ar' => [
                        'title' => 'المنسوجات والصناعات الخفيفة',
                        'description' => 'الابتكارات في مجال تصنيع القطن والمنسوجات، وأساليب إنتاج الصناعات الخفيفة، والخدمات اللوجستية، وأنظمة النقل البري.',
                        'content' => 'أبحاث متخصصة في معالجة القطن الخام، وتقنيات الغزل، وتصميم الملابس، ولوجستيات النقل، وتدقيق الخدمات اللوجستية.',
                    ],
                ],
            ],
            [
                'slug' => 'natural-resources-management',
                'icon' => 'chat-square-text',
                'image' => 'services/natural-resources.jpg',
                'sort_order' => 7,
                'translations' => [
                    'en' => [
                        'title' => 'Natural Resources Management',
                        'description' => 'Studies in environmental conservation, water resource management, agricultural irrigation systems, and soil quality analysis.',
                        'content' => 'Advanced scientific research on water resource conservation, eco-friendly farming practices, and soil erosion prevention.',
                    ],
                    'uz' => [
                        'title' => 'Tabiiy resurslarni boshqarish',
                        'description' => 'Atrof-muhitni muhofaza qilish, suv resurslarini boshqarish, qishloq xo‘jaligi sug‘orish tizimlari va tuproq sifati tahlili bo‘yicha tadqiqotlar.',
                        'content' => 'Suv resurslarini tejash, ekologik toza dehqonchilik amaliyoti va tuproq eroziyasini oldini olish bo‘yicha ilg‘or ilmiy tadqiqotlar.',
                    ],
                    'ru' => [
                        'title' => 'Управление природными ресурсами',
                        'description' => 'Исследования в области охраны окружающей среды, управления водными ресурсами, сельскохозяйственных оросительных систем и анализа качества почв.',
                        'content' => 'Передовые научные исследования в области сохранения водных ресурсов, экологически чистых методов ведения сельского хозяйства и предотвращения эрозии почв.',
                    ],
                    'ar' => [
                        'title' => 'إدارة الموارد الطبيعية',
                        'description' => 'دراسات في الحفاظ على البيئة، وإدارة الموارد المائية، وأنظمة الري الزراعية، وتحليل جودة التربة.',
                        'content' => 'أبحاث علمية متقدمة في مجال الحفاظ على الموارد المائية، والممارسات الزراعية الصديقة للبيئة، ومنع انجراف التربة.',
                    ],
                ],
            ],
        ];

        foreach ($services as $srv) {
            $serviceModel = Service::updateOrCreate(
                ['slug' => $srv['slug']],
                [
                    'icon' => $srv['icon'],
                    'image' => $srv['image'],
                    'sort_order' => $srv['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach ($srv['translations'] as $locale => $data) {
                ServiceTranslation::updateOrCreate(
                    [
                        'service_id' => $serviceModel->id,
                        'locale' => $locale,
                    ],
                    $data
                );
            }
        }
    }
}
