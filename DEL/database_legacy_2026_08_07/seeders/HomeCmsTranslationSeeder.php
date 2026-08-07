<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class HomeCmsTranslationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->sections() as $sectionData) {
            $section = HomeSection::updateOrCreate(
                ['section_key' => $sectionData['section_key']],
                [
                    'section_type' => $sectionData['section_type'],
                    'sort_order' => $sectionData['sort_order'],
                    'is_active' => true,
                    'settings' => $sectionData['settings'] ?? [],
                ],
            );

            foreach ($sectionData['translations'] as $locale => $translation) {
                $section->translations()->updateOrCreate(['locale' => $locale], $translation + ['locale' => $locale]);
            }

            foreach ($sectionData['items'] ?? [] as $index => $itemData) {
                $item = HomeSectionItem::updateOrCreate(
                    ['home_section_id' => $section->id, 'item_key' => $itemData['item_key']],
                    [
                        'icon' => $itemData['icon'] ?? null,
                        'value' => $itemData['value'] ?? null,
                        'suffix' => $itemData['suffix'] ?? null,
                        'url' => $itemData['url'] ?? null,
                        'sort_order' => $itemData['sort_order'] ?? $index,
                        'is_active' => true,
                        'settings' => $itemData['settings'] ?? [],
                    ],
                );

                foreach ($itemData['translations'] as $locale => $translation) {
                    $item->translations()->updateOrCreate(['locale' => $locale], $translation + ['locale' => $locale]);
                }
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    protected function tr(array $en, array $uz, array $ru, array $ar): array
    {
        return compact('en', 'uz', 'ru', 'ar');
    }

    protected function sections(): array
    {
        $ethicsDesc = [
            'en' => 'Upholding international ethics, transparency in ECTS grading, and zero tolerance for corruption through our Compliance Control systems.',
            'uz' => 'Xalqaro etika, ECTS baholashdagi shaffoflik va Komplayens nazorati tizimlari orqali korrupsiyaga mutlaq murosasizlikni taʼminlash.',
            'ru' => 'Поддержка международной этики, прозрачности оценивания ECTS и нулевой терпимости к коррупции через системы комплаенс-контроля.',
            'ar' => 'تعزيز الأخلاقيات الدولية والشفافية في تقييم ECTS وعدم التسامح مطلقًا مع الفساد من خلال أنظمة الرقابة والامتثال.',
        ];
        $innovationDesc = [
            'en' => 'Fostering student startup talent, supporting national patented technologies, and driving digital engineering solutions.',
            'uz' => 'Talabalar startap salohiyatini rivojlantirish, milliy patentlangan texnologiyalarni qoʻllab-quvvatlash va raqamli muhandislik yechimlarini ilgari surish.',
            'ru' => 'Развитие студенческих стартапов, поддержка национальных запатентованных технологий и продвижение цифровых инженерных решений.',
            'ar' => 'تنمية مواهب الطلاب في الشركات الناشئة، ودعم التقنيات الوطنية الحاصلة على براءات اختراع، ودفع حلول الهندسة الرقمية.',
        ];
        $inclusivityDesc = [
            'en' => 'Supporting diverse international students, executing dual-degrees, and offering specialized inclusive training for physically challenged youth.',
            'uz' => 'Turli mamlakatlardan kelgan talabalarni qoʻllab-quvvatlash, qoʻshma diplom dasturlarini amalga oshirish va imkoniyati cheklangan yoshlar uchun inklyuziv taʼlimni taklif etish.',
            'ru' => 'Поддержка иностранных студентов, реализация программ двойных дипломов и предоставление специализированного инклюзивного обучения для молодежи с ограниченными возможностями.',
            'ar' => 'دعم الطلاب الدوليين من خلفيات متنوعة، وتنفيذ برامج الدرجات المزدوجة، وتقديم تدريب شامل متخصص للشباب ذوي الإعاقات الجسدية.',
        ];
        $goalDesc = [
            'en' => 'Our university aims to become a major technological base in Uzbekistan, equipping students with deep engineering expertise, research capabilities, and global industrial integration, partnering directly with conglomerates like Uzbekneftegaz JSC.',
            'uz' => 'Universitetimiz Oʻzbekistonda yirik texnologik bazaga aylanishni, talabalarni chuqur muhandislik bilimlari, tadqiqot salohiyati va global sanoat integratsiyasi bilan taʼminlashni hamda Uzbekneftegaz AJ kabi yirik korxonalar bilan bevosita hamkorlik qilishni maqsad qiladi.',
            'ru' => 'Наш университет стремится стать крупной технологической базой Узбекистана, предоставляя студентам глубокие инженерные знания, исследовательские возможности и интеграцию с мировой промышленностью, сотрудничая напрямую с такими объединениями, как АО «Узбекнефтегаз».',
            'ar' => 'تهدف جامعتنا إلى أن تصبح قاعدة تكنولوجية كبرى في أوزبكستان، من خلال تزويد الطلاب بخبرة هندسية عميقة وقدرات بحثية وتكامل صناعي عالمي، مع شراكات مباشرة مع مؤسسات كبرى مثل Uzbekneftegaz JSC.',
        ];

        return [
            [
                'section_key' => 'hero',
                'section_type' => 'hero',
                'sort_order' => 10,
                'settings' => [
                    'cta_url' => '/apply',
                    'video_url' => 'cms/videos/files/graduation-2026.mp4',
                    'image' => 'cms/home/hero/hero-university.jpg',
                ],
                'translations' => $this->tr(
                    ['title' => 'Bukhara State Technical University', 'subtitle' => 'Empowering international students with accredited engineering programs, applied research opportunities, and dedicated academic support.', 'cta_label' => 'Apply Now', 'secondary_title' => 'Watch video', 'image_alt' => 'University campus'],
                    ['title' => 'Buxoro davlat texnika universiteti', 'subtitle' => 'Xalqaro talabalarni akkreditatsiyadan o‘tgan muhandislik dasturlari, amaliy tadqiqot imkoniyatlari va doimiy akademik qo‘llab-quvvatlash bilan kuchaytiramiz.', 'cta_label' => 'Hozir ariza topshiring', 'secondary_title' => 'Videoni ko‘rish', 'image_alt' => 'Universitet kampusi'],
                    ['title' => 'Бухарский государственный технический университет', 'subtitle' => 'Мы поддерживаем иностранных студентов аккредитованными инженерными программами, возможностями прикладных исследований и целевым академическим сопровождением.', 'cta_label' => 'Подать заявку', 'secondary_title' => 'Смотреть видео', 'image_alt' => 'Университетский кампус'],
                    ['title' => 'جامعة بخارى التقنية الحكومية', 'subtitle' => 'نُمكّن الطلاب الدوليين من خلال برامج هندسية معتمدة، وفرص بحث تطبيقي، ودعم أكاديمي مخصص.', 'cta_label' => 'قدّم الآن', 'secondary_title' => 'مشاهدة الفيديو', 'image_alt' => 'الحرم الجامعي'],
                ),
                'items' => [
                    [
                        'item_key' => 'student_count',
                        'value' => '15,000',
                        'suffix' => '+',
                        'sort_order' => 1,
                        'translations' => $this->tr(
                            ['label' => 'Active students'],
                            ['label' => 'Faol talabalar'],
                            ['label' => 'Активные студенты'],
                            ['label' => 'الطلاب النشطون'],
                        ),
                    ],
                    [
                        'item_key' => 'accreditation',
                        'sort_order' => 2,
                        'translations' => $this->tr(
                            ['title' => 'Accredited', 'label' => 'State programs'],
                            ['title' => 'Akkreditatsiyadan o‘tgan', 'label' => 'Davlat dasturlari'],
                            ['title' => 'Аккредитовано', 'label' => 'Государственные программы'],
                            ['title' => 'معتمد', 'label' => 'برامج حكومية'],
                        ),
                    ],
                ],
            ],
            [
                'section_key' => 'registrar_office',
                'section_type' => 'registrar',
                'sort_order' => 20,
                'settings' => ['image' => 'cms/university-centers/registrar_office.jpg'],
                'translations' => $this->tr(
                    ['eyebrow' => 'Access Registrar Office', 'title' => 'Office of the Registrar', 'description' => 'Manage your academic records, requests, official transcripts, and enrollment certificates.', 'cta_label' => 'Access Registrar Office', 'cta_url' => 'https://student.bstu.uz/dashboard/login', 'image_alt' => 'Office of the Registrar'],
                    ['eyebrow' => 'Registrar ofisiga kirish', 'title' => 'Registrar ofisi', 'description' => 'Akademik yozuvlaringiz, soʻrovlaringiz, rasmiy transkriptlaringiz va oʻqishga qabul sertifikatlaringizni boshqaring.', 'cta_label' => 'Registrar ofisiga kirish', 'cta_url' => 'https://student.bstu.uz/dashboard/login', 'image_alt' => 'Registrar ofisi'],
                    ['eyebrow' => 'Вход в офис регистратора', 'title' => 'Офис регистратора', 'description' => 'Управляйте академическими записями, запросами, официальными транскриптами и справками о зачислении.', 'cta_label' => 'Перейти в офис регистратора', 'cta_url' => 'https://student.bstu.uz/dashboard/login', 'image_alt' => 'Офис регистратора'],
                    ['eyebrow' => 'الدخول إلى مكتب المسجل', 'title' => 'مكتب المسجل', 'description' => 'إدارة سجلاتك الأكاديمية وطلباتك وكشوف الدرجات الرسمية وشهادات القيد.', 'cta_label' => 'الدخول إلى مكتب المسجل', 'cta_url' => 'https://student.bstu.uz/dashboard/login', 'image_alt' => 'مكتب المسجل'],
                ),
                'items' => [
                    $this->item('hemis', 1, ['en' => 'HEMIS Student Portal', 'uz' => 'HEMIS talabalar portali', 'ru' => 'Студенческий портал HEMIS', 'ar' => 'بوابة الطلاب HEMIS']),
                    $this->item('distance_learning', 2, ['en' => 'Distance Learning Platform', 'uz' => 'Masofaviy taʼlim platformasi', 'ru' => 'Платформа дистанционного обучения', 'ar' => 'منصة التعليم عن بُعد']),
                    $this->item('contract_invoice', 3, ['en' => 'Contract & Invoice Portal', 'uz' => 'Shartnoma va hisob-faktura portali', 'ru' => 'Портал договоров и счетов', 'ar' => 'بوابة العقود والفواتير']),
                    $this->item('remote_education', 4, ['en' => 'Remote Education Management', 'uz' => 'Masofaviy taʼlim boshqaruvi', 'ru' => 'Управление дистанционным обучением', 'ar' => 'إدارة التعليم عن بُعد']),
                ],
            ],
            $this->featureList('alt_features', 'feature_list', 30, $ethicsDesc, $innovationDesc, $inclusivityDesc),
            [
                'section_key' => 'strategic_goals',
                'section_type' => 'feature_detail',
                'sort_order' => 40,
                'settings' => ['image' => 'cms/about-page/bstu-about-identity.jpg'],
                'translations' => $this->tr(
                    ['eyebrow' => 'Strategic Goals', 'title' => 'Mission & Vision', 'subtitle' => 'Institutional Merge', 'secondary_title' => 'Our Identity', 'description' => $goalDesc['en'], 'cta_label' => 'Explore Campus Life', 'cta_url' => '/video-bdtu', 'image_alt' => 'Mission & Vision BSTU'],
                    ['eyebrow' => 'Strategik maqsadlar', 'title' => 'Missiya va qarash', 'subtitle' => 'Institutsional birlashuv', 'secondary_title' => 'Bizning identitetimiz', 'description' => $goalDesc['uz'], 'cta_label' => 'Kampus hayotini ko‘rish', 'cta_url' => '/video-bdtu', 'image_alt' => 'BSTU missiyasi va qarashi'],
                    ['eyebrow' => 'Стратегические цели', 'title' => 'Миссия и видение', 'subtitle' => 'Институциональное объединение', 'secondary_title' => 'Наша идентичность', 'description' => $goalDesc['ru'], 'cta_label' => 'Изучить жизнь кампуса', 'cta_url' => '/video-bdtu', 'image_alt' => 'Миссия и видение БГТУ'],
                    ['eyebrow' => 'الأهداف الاستراتيجية', 'title' => 'الرسالة والرؤية', 'subtitle' => 'الاندماج المؤسسي', 'secondary_title' => 'هويتنا', 'description' => $goalDesc['ar'], 'cta_label' => 'استكشف حياة الحرم الجامعي', 'cta_url' => '/video-bdtu', 'image_alt' => 'رسالة ورؤية BSTU'],
                ),
                'items' => [
                    $this->item('mission', 1, ['en' => 'Our Mission', 'uz' => 'Missiyamiz', 'ru' => 'Наша миссия', 'ar' => 'رسالتنا']),
                    $this->item('vision', 2, ['en' => 'Our Vision', 'uz' => 'Qarashimiz', 'ru' => 'Наше видение', 'ar' => 'رؤيتنا']),
                    $this->item('innovation', 3, ['en' => 'Innovation Leadership', 'uz' => 'Innovatsion yetakchilik', 'ru' => 'Инновационное лидерство', 'ar' => 'ريادة الابتكار']),
                    $this->item('inclusivity', 4, ['en' => 'Global Inclusivity', 'uz' => 'Global inklyuzivlik', 'ru' => 'Глобальная инклюзивность', 'ar' => 'الشمول العالمي']),
                ],
            ],
            $this->featureList('core_values', 'cards', 50, $ethicsDesc, $innovationDesc, $inclusivityDesc, true),
            [
                'section_key' => 'identity',
                'section_type' => 'about',
                'sort_order' => 60,
                'settings' => ['image' => 'cms/about-page/bstu-about-identity.jpg'],
                'translations' => $this->tr(
                    ['eyebrow' => 'Institutional Merge', 'title' => 'Our Identity', 'description' => 'Formed through the integration of the Bukhara Engineering-Technological Institute and the Bukhara Institute of Natural Resources Management (Presidential Resolution No. PP-22), BSTU brings together the region\'s elite scholars, researchers, and campus facilities.', 'secondary_description' => $goalDesc['en'], 'cta_label' => 'About Us', 'cta_url' => '/about', 'image_alt' => 'Our university aims to become a major technological base in Uzbekistan'],
                    ['eyebrow' => 'Institutsional birlashuv', 'title' => 'Bizning identitetimiz', 'description' => 'Buxoro muhandislik-texnologiya instituti va Buxoro tabiiy resurslarni boshqarish instituti integratsiyasi asosida tashkil etilgan BSTU (Prezident qarori No. PP-22) hududning yetakchi olimlari, tadqiqotchilari va kampus imkoniyatlarini birlashtiradi.', 'secondary_description' => $goalDesc['uz'], 'cta_label' => 'Biz haqimizda', 'cta_url' => '/about', 'image_alt' => 'Universitetimiz O‘zbekistonda yirik texnologik bazaga aylanishni maqsad qilgan'],
                    ['eyebrow' => 'Институциональное объединение', 'title' => 'Наша идентичность', 'description' => 'Созданный путем интеграции Бухарского инженерно-технологического института и Бухарского института управления природными ресурсами (Постановление Президента No. PP-22), БГТУ объединяет ведущих ученых, исследователей и инфраструктуру кампусов региона.', 'secondary_description' => $goalDesc['ru'], 'cta_label' => 'О нас', 'cta_url' => '/about', 'image_alt' => 'Наш университет стремится стать крупной технологической базой Узбекистана'],
                    ['eyebrow' => 'الاندماج المؤسسي', 'title' => 'هويتنا', 'description' => 'تأسست BSTU من خلال دمج معهد بخارى للهندسة والتكنولوجيا ومعهد بخارى لإدارة الموارد الطبيعية (قرار الرئيس رقم PP-22)، لتجمع نخبة العلماء والباحثين ومرافق الحرم الجامعي في المنطقة.', 'secondary_description' => $goalDesc['ar'], 'cta_label' => 'من نحن', 'cta_url' => '/about', 'image_alt' => 'تهدف جامعتنا إلى أن تصبح قاعدة تكنولوجية كبرى في أوزبكستان'],
                ),
            ],
            [
                'section_key' => 'stats',
                'section_type' => 'stats',
                'sort_order' => 70,
                'translations' => $this->tr([], [], [], []),
                'items' => [
                    $this->stat('active_students', '18000', '+', 1, ['en' => 'Active Students', 'uz' => 'Faol talabalar', 'ru' => 'Активные студенты', 'ar' => 'الطلاب النشطون']),
                    $this->stat('professors', '730', '+', 2, ['en' => 'Professors & Instructors', 'uz' => 'Professorlar va o‘qituvchilar', 'ru' => 'Профессора и преподаватели', 'ar' => 'الأساتذة والمحاضرون']),
                    $this->stat('faculties', '4', '', 3, ['en' => 'Faculties', 'uz' => 'Fakultetlar', 'ru' => 'Факультеты', 'ar' => 'الكليات']),
                    $this->stat('departments', '24', '', 4, ['en' => 'Departments', 'uz' => 'Kafedralar', 'ru' => 'Кафедры', 'ar' => 'الأقسام']),
                    $this->stat('research_centers', '13', '', 5, ['en' => 'Research Centers & Labs', 'uz' => 'Tadqiqot markazlari va laboratoriyalar', 'ru' => 'Исследовательские центры и лаборатории', 'ar' => 'مراكز البحث والمختبرات']),
                    $this->stat('technology_parks', '2', '', 6, ['en' => 'Technology Parks', 'uz' => 'Texnoparklar', 'ru' => 'Технопарки', 'ar' => 'المجمعات التكنولوجية']),
                    $this->stat('international_students', '250', '+', 7, ['en' => 'International Students', 'uz' => 'Xalqaro talabalar', 'ru' => 'Иностранные студенты', 'ar' => 'الطلاب الدوليون']),
                    $this->stat('technical_schools', '18', '', 8, ['en' => 'Affiliated Technical Schools', 'uz' => 'Hamkor texnik maktablar', 'ru' => 'Аффилированные технические школы', 'ar' => 'المدارس التقنية التابعة']),
                ],
            ],
        ];
    }

    protected function featureList(string $key, string $type, int $order, array $ethicsDesc, array $innovationDesc, array $inclusivityDesc, bool $withHeader = false): array
    {
        $settings = $key === 'alt_features'
            ? ['image' => 'cms/home/feature-list.png']
            : [];

        return [
            'section_key' => $key,
            'section_type' => $type,
            'sort_order' => $order,
            'settings' => $settings,
            'translations' => $key === 'alt_features'
                ? $this->tr(
                    ['image_alt' => 'Academic Integrity'],
                    ['image_alt' => 'Akademik halollik'],
                    ['image_alt' => 'Академическая честность'],
                    ['image_alt' => 'النزاهة الأكاديمية'],
                )
                : ($withHeader
                ? $this->tr(
                    ['eyebrow' => 'Operational Ethics', 'title' => 'Core Values'],
                    ['eyebrow' => 'Operatsion etika', 'title' => 'Asosiy qadriyatlar'],
                    ['eyebrow' => 'Операционная этика', 'title' => 'Ключевые ценности'],
                    ['eyebrow' => 'الأخلاقيات التشغيلية', 'title' => 'القيم الأساسية'],
                )
                : $this->tr([], [], [], [])),
            'items' => [
                $this->item('academic_integrity', 1, ['en' => 'Academic Integrity', 'uz' => 'Akademik halollik', 'ru' => 'Академическая честность', 'ar' => 'النزاهة الأكاديمية'], $ethicsDesc, $withHeader ? ['image' => 'cms/home/core-values/academic-integrity.png'] : []),
                $this->item('innovation_leadership', 2, ['en' => 'Innovation Leadership', 'uz' => 'Innovatsion yetakchilik', 'ru' => 'Инновационное лидерство', 'ar' => 'ريادة الابتكار'], $innovationDesc, $withHeader ? ['image' => 'cms/home/core-values/innovation-leadership.png'] : []),
                $this->item('global_inclusivity', 3, ['en' => 'Global Inclusivity', 'uz' => 'Global inklyuzivlik', 'ru' => 'Глобальная инклюзивность', 'ar' => 'الشمول العالمي'], $inclusivityDesc, $withHeader ? ['image' => 'cms/home/core-values/global-inclusivity.png'] : []),
                ...($withHeader ? [] : [
                    $this->item('active_students', 4, ['en' => 'Active Students', 'uz' => 'Faol talabalar', 'ru' => 'Активные студенты', 'ar' => 'الطلاب النشطون'], ['en' => 'Enrolled in undergraduate and graduate courses', 'uz' => 'Bakalavriat va magistratura kurslarida tahsil olayotganlar', 'ru' => 'Обучаются на программах бакалавриата и магистратуры', 'ar' => 'ملتحقون ببرامج البكالوريوس والدراسات العليا']),
                    $this->item('professors', 5, ['en' => 'Professors & Instructors', 'uz' => 'Professorlar va o‘qituvchilar', 'ru' => 'Профессора и преподаватели', 'ar' => 'الأساتذة والمحاضرون'], ['en' => 'Experienced researchers and academic educators', 'uz' => 'Tajribali tadqiqotchilar va akademik pedagoglar', 'ru' => 'Опытные исследователи и академические преподаватели', 'ar' => 'باحثون ومعلمون أكاديميون ذوو خبرة']),
                    $this->item('faculties', 6, ['en' => 'Faculties', 'uz' => 'Fakultetlar', 'ru' => 'Факультеты', 'ar' => 'الكليات'], ['en' => 'Engineering, Technology, Service, and Natural Resources', 'uz' => 'Muhandislik, texnologiya, servis va tabiiy resurslar', 'ru' => 'Инженерия, технологии, сервис и природные ресурсы', 'ar' => 'الهندسة والتكنولوجيا والخدمات والموارد الطبيعية']),
                ]),
            ],
        ];
    }

    protected function item(string $key, int $order, array $titles, array $descriptions = [], array $settings = []): array
    {
        return [
            'item_key' => $key,
            'sort_order' => $order,
            'settings' => $settings,
            'translations' => [
                'en' => ['title' => $titles['en'] ?? '', 'description' => $descriptions['en'] ?? ''],
                'uz' => ['title' => $titles['uz'] ?? '', 'description' => $descriptions['uz'] ?? ''],
                'ru' => ['title' => $titles['ru'] ?? '', 'description' => $descriptions['ru'] ?? ''],
                'ar' => ['title' => $titles['ar'] ?? '', 'description' => $descriptions['ar'] ?? ''],
            ],
        ];
    }

    protected function stat(string $key, string $value, string $suffix, int $order, array $labels): array
    {
        return [
            'item_key' => $key,
            'value' => $value,
            'suffix' => $suffix,
            'sort_order' => $order,
            'translations' => [
                'en' => ['label' => $labels['en'] ?? ''],
                'uz' => ['label' => $labels['uz'] ?? ''],
                'ru' => ['label' => $labels['ru'] ?? ''],
                'ar' => ['label' => $labels['ar'] ?? ''],
            ],
        ];
    }
}
