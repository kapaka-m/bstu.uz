<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'light-industry-engineering-and-design')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->deletePlaceholderStaff($departmentId);
            $this->normalizeDepartmentSections($departmentId);
            $this->normalizeStaff($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function deletePlaceholderStaff(int $departmentId): void
    {
        $ids = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereIn('slug', [
                'light-industry-engineering-and-design-0-dr-lola-sh-kadirova',
                'light-industry-engineering-and-design-1-rustam-u-inoyatov',
                'light-industry-engineering-and-design-2-madina-b-xamrayeva',
            ])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
        DB::table('staff_profiles')->whereIn('id', $ids)->delete();
    }

    private function normalizeDepartmentSections(int $departmentId): void
    {
        foreach ($this->departmentTranslations() as $locale => $payload) {
            DB::table('department_translations')->updateOrInsert(
                ['department_id' => $departmentId, 'locale' => $locale],
                [
                    'name' => $payload['name'],
                    'short_name' => $payload['short_name'],
                    'description' => $payload['description'],
                    'content_sections' => json_encode($payload['sections'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'meta_title' => $payload['name'],
                    'meta_description' => $payload['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function normalizeStaff(int $departmentId): void
    {
        $sort = 1;

        foreach ($this->staff() as $oldSlug => $item) {
            $slug = $item['slug'] ?? $oldSlug;

            DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->whereIn('slug', array_values(array_unique([$oldSlug, $slug])))
                ->update([
                    'slug' => $slug,
                    'sort_order' => $sort++,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            $profileId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $slug)
                ->value('id');

            if (! $profileId) {
                continue;
            }

            foreach ($item['translations'] as $locale => $translation) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $translation['name'],
                        'position' => $translation['position'],
                        'bio' => $translation['bio'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }
    }

    private function departmentTranslations(): array
    {
        return [
            'en' => [
                'name' => 'Light Industry Engineering and Design',
                'short_name' => 'Light Industry Engineering and Design',
                'description' => 'The Department of Light Industry Engineering and Design prepares specialists in textile, garment, footwear, accessories, light industry technology, product design, and modern production processes.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'About the Department', 'items' => ['The department combines engineering training, design studios, materials science, laboratory practice, production technology, and industry-oriented creative projects.']],
                    ['key' => 'subjects', 'title' => 'Taught Subjects', 'items' => [
                        'Textile materials science',
                        'Garment design and construction',
                        'Footwear and accessories design',
                        'Light industry production technology',
                        'Sewing and knitting technology',
                        'Leather goods technology',
                        'Computer-aided design for light industry',
                        'Quality control and standardization',
                        'Product modeling and prototyping',
                        'Industrial practice and graduation project',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Programs and Specializations', 'items' => ['60210400 - Design: Footwear and Accessories Design', '60210400 - Design: Clothing and Textile Design', '60210400 - Design: Textile and Light Industry Design', '60720500 - Light Industry Production Technology', '60720700 - Light Industry Engineering']],
                    ['key' => 'research', 'title' => 'Research Work', 'items' => [
                        'Development of textile, garment, footwear, and accessory technologies for modern light industry.',
                        'Research on material quality, product design, ergonomic solutions, and production efficiency.',
                        'Application of digital design, modeling, and innovative technologies in light industry production.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'International Cooperation', 'items' => ['The department cooperates with textile and garment enterprises, design studios, production organizations, research centers, and partner universities.']],
                    ['key' => 'department_structure', 'title' => 'Department Structure', 'items' => ['Head of Department: Qazoqov Farxod Farmonovich', 'Office hours: Monday-Friday 14:00-16:00', 'Phone: +998 91 647 65 82']],
                ],
            ],
            'uz' => [
                'name' => 'Yengil sanoat muhandisligi va dizayni kafedrasi',
                'short_name' => 'Yengil sanoat muhandisligi va dizayni kafedrasi',
                'description' => 'Yengil sanoat muhandisligi va dizayni kafedrasi to‘qimachilik, tikuvchilik, poyabzal, aksessuarlar, yengil sanoat texnologiyasi, mahsulot dizayni va zamonaviy ishlab chiqarish jarayonlari bo‘yicha mutaxassislar tayyorlaydi.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'Kafedra haqida', 'items' => ['Kafedra muhandislik tayyorgarligi, dizayn studiyalari, materialshunoslik, laboratoriya amaliyoti, ishlab chiqarish texnologiyasi va sanoatga yo‘naltirilgan ijodiy loyihalarni uyg‘unlashtiradi.']],
                    ['key' => 'subjects', 'title' => 'O‘qitiladigan fanlar', 'items' => [
                        'To‘qimachilik materialshunosligi',
                        'Kiyim dizayni va konstruksiyalash',
                        'Poyabzal va aksessuarlar dizayni',
                        'Yengil sanoat ishlab chiqarish texnologiyasi',
                        'Tikuv va trikotaj texnologiyasi',
                        'Charm buyumlar texnologiyasi',
                        'Yengil sanoatda kompyuter yordamida loyihalash',
                        'Sifat nazorati va standartlashtirish',
                        'Mahsulotlarni modellashtirish va prototiplash',
                        'Ishlab chiqarish amaliyoti va bitiruv loyihasi',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Dasturlar va mutaxassisliklar', 'items' => ['60210400 - Dizayn: poyabzal va aksessuarlar dizayni', '60210400 - Dizayn: kiyim va to‘qimachilik dizayni', '60210400 - Dizayn: to‘qimachilik va yengil sanoat dizayni', '60720500 - Yengil sanoat ishlab chiqarish texnologiyasi', '60720700 - Yengil sanoat muhandisligi']],
                    ['key' => 'research', 'title' => 'Ilmiy ishlar', 'items' => [
                        'Zamonaviy yengil sanoat uchun to‘qimachilik, kiyim, poyabzal va aksessuarlar texnologiyalarini rivojlantirish.',
                        'Material sifati, mahsulot dizayni, ergonomik yechimlar va ishlab chiqarish samaradorligi bo‘yicha tadqiqotlar.',
                        'Yengil sanoat ishlab chiqarishida raqamli dizayn, modellashtirish va innovatsion texnologiyalarni qo‘llash.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Xalqaro hamkorlik', 'items' => ['Kafedra to‘qimachilik va tikuvchilik korxonalari, dizayn studiyalari, ishlab chiqarish tashkilotlari, ilmiy markazlar va hamkor universitetlar bilan hamkorlik qiladi.']],
                    ['key' => 'department_structure', 'title' => 'Kafedra tuzilmasi', 'items' => ['Kafedra mudiri: Qazoqov Farxod Farmonovich', 'Qabul vaqti: Dushanba-juma 14:00-16:00', 'Telefon: +998 91 647 65 82']],
                ],
            ],
            'ru' => [
                'name' => 'Кафедра инженерии и дизайна легкой промышленности',
                'short_name' => 'Кафедра инженерии и дизайна легкой промышленности',
                'description' => 'Кафедра инженерии и дизайна легкой промышленности готовит специалистов в области текстиля, швейных изделий, обуви, аксессуаров, технологий легкой промышленности, дизайна продукции и современных производственных процессов.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'О кафедре', 'items' => ['Кафедра объединяет инженерную подготовку, дизайн-студии, материаловедение, лабораторную практику, производственные технологии и творческие проекты, ориентированные на индустрию.']],
                    ['key' => 'subjects', 'title' => 'Учебные дисциплины', 'items' => [
                        'Материаловедение текстильных материалов',
                        'Дизайн и конструирование одежды',
                        'Дизайн обуви и аксессуаров',
                        'Технология производства легкой промышленности',
                        'Технология швейных и трикотажных изделий',
                        'Технология изделий из кожи',
                        'Компьютерное проектирование в легкой промышленности',
                        'Контроль качества и стандартизация',
                        'Моделирование и прототипирование продукции',
                        'Производственная практика и выпускной проект',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Программы и специализации', 'items' => ['60210400 - Дизайн: дизайн обуви и аксессуаров', '60210400 - Дизайн: дизайн одежды и текстиля', '60210400 - Дизайн: дизайн текстиля и легкой промышленности', '60720500 - Технология производства легкой промышленности', '60720700 - Инженерия легкой промышленности']],
                    ['key' => 'research', 'title' => 'Научная работа', 'items' => [
                        'Развитие технологий текстиля, одежды, обуви и аксессуаров для современной легкой промышленности.',
                        'Исследования качества материалов, дизайна продукции, эргономических решений и эффективности производства.',
                        'Применение цифрового дизайна, моделирования и инновационных технологий в производстве легкой промышленности.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Международное сотрудничество', 'items' => ['Кафедра сотрудничает с текстильными и швейными предприятиями, дизайн-студиями, производственными организациями, научными центрами и партнерскими университетами.']],
                    ['key' => 'department_structure', 'title' => 'Структура кафедры', 'items' => ['Заведующий кафедрой: Казоков Фарход Фармонович', 'Часы приема: понедельник-пятница 14:00-16:00', 'Телефон: +998 91 647 65 82']],
                ],
            ],
            'ar' => [
                'name' => 'قسم هندسة وتصميم الصناعات الخفيفة',
                'short_name' => 'قسم هندسة وتصميم الصناعات الخفيفة',
                'description' => 'يعد قسم هندسة وتصميم الصناعات الخفيفة متخصصين في النسيج والملابس والأحذية والإكسسوارات وتقنيات الصناعات الخفيفة وتصميم المنتجات وعمليات الإنتاج الحديثة.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'عن القسم', 'items' => ['يجمع القسم بين التدريب الهندسي واستوديوهات التصميم وعلوم المواد والتطبيقات المخبرية وتقنيات الإنتاج والمشروعات الإبداعية المرتبطة بالصناعة.']],
                    ['key' => 'subjects', 'title' => 'المواد التي تدرس في القسم', 'items' => [
                        'علم مواد النسيج',
                        'تصميم الملابس وإنشاؤها',
                        'تصميم الأحذية والإكسسوارات',
                        'تقنية إنتاج الصناعات الخفيفة',
                        'تقنية الخياطة والتريكو',
                        'تقنية المنتجات الجلدية',
                        'التصميم بالحاسوب في الصناعات الخفيفة',
                        'مراقبة الجودة والتقييس',
                        'نمذجة المنتجات وإعداد النماذج الأولية',
                        'التدريب الصناعي ومشروع التخرج',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'البرامج والتخصصات', 'items' => ['60210400 - التصميم: تصميم الأحذية والإكسسوارات', '60210400 - التصميم: تصميم الملابس والنسيج', '60210400 - التصميم: تصميم النسيج والصناعات الخفيفة', '60720500 - تقنية إنتاج الصناعات الخفيفة', '60720700 - هندسة الصناعات الخفيفة']],
                    ['key' => 'research', 'title' => 'الأعمال البحثية', 'items' => [
                        'تطوير تقنيات النسيج والملابس والأحذية والإكسسوارات للصناعات الخفيفة الحديثة.',
                        'أبحاث في جودة المواد وتصميم المنتجات والحلول المريحة وكفاءة الإنتاج.',
                        'تطبيق التصميم الرقمي والنمذجة والتقنيات المبتكرة في إنتاج الصناعات الخفيفة.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'التعاون الدولي', 'items' => ['يتعاون القسم مع مؤسسات النسيج والملابس واستوديوهات التصميم ومؤسسات الإنتاج والمراكز البحثية والجامعات الشريكة.']],
                    ['key' => 'department_structure', 'title' => 'هيكل القسم', 'items' => ['رئيس القسم: قازوقوف فرخود فرمانوفيتش', 'ساعات الاستقبال: الاثنين-الجمعة 14:00-16:00', 'الهاتف: +998 91 647 65 82']],
                ],
            ],
        ];
    }

    private function staff(): array
    {
        return [
            'light-industry-engineering-and-design-farhod-farmonovich-qazoqov' => $this->staffItem('Qazoqov Farxod Farmonovich', 'Казоков Фарход Фармонович', 'قازوقوف فرخود فرمانوفيتش', 'Head of Department', 'Kafedra mudiri', 'Заведующий кафедрой', 'رئيس القسم'),
            'light-industry-engineering-and-design-nurboyev-rashid-xudoyberdiyevich' => $this->staffItem('Nurboyev Rashid Xudoyberdiyevich', 'Нурбоев Рашид Худойбердиевич', 'نوربوييف رشيد خدويبردييفيتش', 'Professor', 'Professor', 'Профессор', 'أستاذ'),
            'light-industry-engineering-and-design-musayev-sayfullo-safoyevich' => $this->staffItem('Musayev Sayfullo Safoyevich', 'Мусаев Сайфулло Сафоевич', 'موساييف سيف الله صافوييفيتش', 'Professor', 'Professor', 'Профессор', 'أستاذ'),
            'light-industry-engineering-and-design-polatova-sabohat-usmanovna' => $this->staffItem('Po‘latova Sabohat Usmanovna', 'Пулатова Сабохат Усмановна', 'بولاتوفا صباحات عثمانوفنا', 'Professor', 'Professor', 'Профессор', 'أستاذ'),
            'light-industry-engineering-and-design-ubaydov-kadir-zokirovich' => $this->staffItem('Ubaydov Kadir Zokirovich', 'Убайдов Кадир Зокирович', 'أوبايدوف قدير زوكيروفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'light-industry-engineering-and-design-saidova-xulkar-hamidovna' => $this->staffItem('Saidova Xulkar Hamidovna', 'Саидова Хулкар Хамидовна', 'سعيدوفا خولكار حميدوفنا', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'light-industry-engineering-and-design-djalolova-dilafroz-fattoxovna' => $this->staffItem('Djalolova Dilafro‘z Fattoxovna', 'Джалолова Дилафруз Фаттоховна', 'جلالوفا ديلافروز فتوحوفنا', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'light-industry-engineering-and-design-temirova-gulnoz-ibodovna' => $this->staffItem('Temirova Gulnoz Ibodovna', 'Темирова Гулноз Ибодовна', 'تيميروفا غولنوز عبادوفنا', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'light-industry-engineering-and-design-gafurova-nigora-toymurodovna' => $this->staffItem("Gafurova Nigora To'ymurodovna", 'Гафурова Нигора Тоймуродовна', 'غافوروفا نيغورا تويمورودوفنا', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'light-industry-engineering-and-design-mardonov-saloxiddin-ergashovich' => $this->staffItem('Mardonov Saloxiddin Ergashovich', 'Мардонов Салохиддин Эргашович', 'ماردونوف صلاح الدين إرغاشوفيتش', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
            'light-industry-engineering-and-design-uzakova-laylo-polvonovna' => $this->staffItem('Uzakova Laylo Polvonovna', 'Узакова Лайло Полвоновна', 'أوزاكوفا ليلى بولفونوفنا', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'light-industry-engineering-and-design-khaitov-akhror-akhmadovich' => $this->staffItem('Khaitov Akhror Akhmadovich', 'Хаитов Ахрор Ахмадович', 'خايتوف أخرور أحمدوفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'light-industry-engineering-and-design-avezov-mavlon-fazliyevich' => $this->staffItem('Avezov Mavlon Fazliyevich', 'Авезов Мавлон Фазлиевич', 'أفيزوف مافلون فازلييفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'light-industry-engineering-and-design-azimov-juma-sharopovich' => $this->staffItem('Azimov Juma Sharopovich', 'Азимов Жума Шаропович', 'عظيموف جمعة شاروبوفيتش', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
            'light-industry-engineering-and-design-samiyeva-gulnoz-olimovna-strong' => $this->staffItem('Samiyeva Gulnoz Olimovna', 'Самиева Гулноз Олимовна', 'سامييفا غولنوز أوليموفنا', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك', 'light-industry-engineering-and-design-samiyeva-gulnoz-olimovna'),
            'light-industry-engineering-and-design-sharipova-saodat-islomovna' => $this->staffItem('Sharipova Saodat Islomovna', 'Шарипова Саодат Исломовна', 'شاريبوفا سعادت إسلاموفنا', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
            'light-industry-engineering-and-design-bebutova-nargiza-narzullayevna' => $this->staffItem('Bebutova Nargiza Narzullayevna', 'Бебутова Наргиза Нарзуллаевна', 'بيبوتوفا نارغيزا نارزوللاييفنا', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
            'light-industry-engineering-and-design-kadirova-dilnoza-khariddinovna' => $this->staffItem('Kadirova Dilnoza Khariddinovna', 'Кадирова Дилноза Хариддиновна', 'قاديروفا ديلنوزا خريدينوفنا', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
            'light-industry-engineering-and-design-kadirova-sevara-khairiddinovna' => $this->staffItem('Kadirova Sevara Khairiddinovna', 'Кадирова Севара Хайриддиновна', 'قاديروفا سيفارا خيريدينوفنا', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
            'light-industry-engineering-and-design-ubaydova-vazira-erkinovna' => $this->staffItem('Ubaydova Vazira Erkinovna', 'Убайдова Вазира Эркиновна', 'أوبايدوفا فازيرا إركينوفنا', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
            'light-industry-engineering-and-design-nutfullayeva-lobar-nurullayevna' => $this->staffItem('Nutfullayeva Lobar Nurullayevna', 'Нутфуллаева Лобар Нуруллаевна', 'نوتفوللاييفا لوبار نوروللاييفنا', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
            'light-industry-engineering-and-design-muhammedova-madina-olimovna' => $this->staffItem('Muhammedova Madina Olimovna', 'Мухаммедова Мадина Олимовна', 'محمدوفا مادينا أوليموفنا', 'PhD, Associate Professor', 'PhD, dotsent', 'PhD, доцент', 'دكتوراه، أستاذ مشارك'),
        ];
    }

    private function staffItem(string $enName, string $ruName, string $arName, string $enPosition, string $uzPosition, string $ruPosition, string $arPosition, ?string $slug = null): array
    {
        return [
            'slug' => $slug,
            'translations' => [
                'en' => ['name' => $enName, 'position' => $enPosition, 'bio' => "$enName serves as $enPosition in Light Industry Engineering and Design, contributing to academic, design, technological, and research development."],
                'uz' => ['name' => $enName, 'position' => $uzPosition, 'bio' => "$enName Yengil sanoat muhandisligi va dizayni kafedrasida $uzPosition sifatida ta’lim, dizayn, texnologik va ilmiy faoliyatga hissa qo‘shadi."],
                'ru' => ['name' => $ruName, 'position' => $ruPosition, 'bio' => "$ruName работает на кафедре инженерии и дизайна легкой промышленности в должности: $ruPosition."],
                'ar' => ['name' => $arName, 'position' => $arPosition, 'bio' => "$arName يعمل في قسم هندسة وتصميم الصناعات الخفيفة بصفة $arPosition ويساهم في التطوير الأكاديمي والتصميمي والتقني والبحثي."],
            ],
        ];
    }
};
