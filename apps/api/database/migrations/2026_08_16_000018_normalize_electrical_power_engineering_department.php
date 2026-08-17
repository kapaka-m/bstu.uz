<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'electrical-power-engineering')->value('id');

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
        $slugs = [
            'electrical-power-engineering-0-dr-nematulla-s-rajabov',
            'electrical-power-engineering-1-dr-gulbahor-o-hamidova',
            'electrical-power-engineering-2-salim-i-fayziyev',
            'electrical-power-engineering-3-nurbek-a-safarov',
        ];

        $ids = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereIn('slug', $slugs)
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
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    private function normalizeStaff(int $departmentId): void
    {
        $staff = $this->staff();
        $sort = 1;

        foreach ($staff as $slug => $item) {
            $currentSlug = $item['slug'] ?? $slug;

            DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->whereIn('slug', array_values(array_unique([$slug, $currentSlug])))
                ->update([
                    'slug' => $currentSlug,
                    'sort_order' => $sort++,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            $profileId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $currentSlug)
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
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        }
    }

    private function departmentTranslations(): array
    {
        return [
            'en' => [
                'name' => 'Electrical and Power Engineering',
                'short_name' => 'Electrical and Power Engineering',
                'description' => 'The Department of Electrical and Power Engineering prepares specialists in electrical systems, power engineering, renewable energy, automation, and safe operation of modern energy infrastructure.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'About the Department', 'items' => ['The department combines theoretical engineering training with laboratory practice, design projects, industrial internships, and applied research in electrical and power systems.']],
                    ['key' => 'subjects', 'title' => 'Taught Subjects', 'items' => [
                        'Electrical engineering fundamentals',
                        'Electrical circuits and network analysis',
                        'Electrical machines and transformers',
                        'Power systems and substations',
                        'Relay protection and automation',
                        'Power electronics and electric drives',
                        'Renewable energy sources',
                        'Energy efficiency and energy audit',
                        'High-voltage engineering and electrical safety',
                        'Industrial practice and graduation project',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Programs and Specializations', 'items' => ['60710400 - Energy Engineering', '60710500 - Electrical Engineering', '60712000 - Renewable Energy Sources']],
                    ['key' => 'research', 'title' => 'Research Work', 'items' => [
                        'Improving reliability and efficiency of electrical power supply systems.',
                        'Development of renewable energy technologies and energy-saving solutions.',
                        'Automation, protection, diagnostics, and safe operation of electrical networks and industrial equipment.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'International Cooperation', 'items' => [
                        'The department cooperates with industrial enterprises, energy organizations, research institutions, and partner universities to strengthen practical training and applied research.',
                    ]],
                    ['key' => 'department_structure', 'title' => 'Department Structure', 'items' => ['Head of Department: Latipov Saidmurod Tuygunovich', 'Office hours: Tuesday-Thursday 10:00-13:00', 'Phone: +998 91 979 88 22', 'Email: stlatipov@gmail.com']],
                ],
            ],
            'uz' => [
                'name' => 'Elektr energetikasi kafedrasi',
                'short_name' => 'Elektr energetikasi kafedrasi',
                'description' => 'Elektr energetikasi kafedrasi elektr tizimlari, energetika, qayta tiklanuvchi energiya, avtomatlashtirish va zamonaviy energetika infratuzilmasini xavfsiz ishlatish bo‘yicha mutaxassislar tayyorlaydi.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'Kafedra haqida', 'items' => ['Kafedra nazariy muhandislik ta’limini laboratoriya amaliyoti, loyiha ishlari, ishlab chiqarish amaliyoti va elektr energetikasi sohasidagi amaliy tadqiqotlar bilan uyg‘unlashtiradi.']],
                    ['key' => 'subjects', 'title' => 'O‘qitiladigan fanlar', 'items' => [
                        'Elektr muhandisligi asoslari',
                        'Elektr zanjirlari va tarmoqlar tahlili',
                        'Elektr mashinalari va transformatorlar',
                        'Energetika tizimlari va podstansiyalar',
                        'Rele himoyasi va avtomatika',
                        'Kuch elektronikasi va elektr yuritmalar',
                        'Qayta tiklanuvchi energiya manbalari',
                        'Energiya samaradorligi va energiya auditi',
                        'Yuqori kuchlanish texnikasi va elektr xavfsizligi',
                        'Ishlab chiqarish amaliyoti va bitiruv loyihasi',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Dasturlar va mutaxassisliklar', 'items' => ['60710400 - Energetika muhandisligi', '60710500 - Elektr muhandisligi', '60712000 - Qayta tiklanuvchi energiya manbalari']],
                    ['key' => 'research', 'title' => 'Ilmiy ishlar', 'items' => [
                        'Elektr ta’minoti tizimlarining ishonchliligi va samaradorligini oshirish.',
                        'Qayta tiklanuvchi energiya texnologiyalari va energiya tejovchi yechimlarni rivojlantirish.',
                        'Elektr tarmoqlari va sanoat uskunalarini avtomatlashtirish, himoya qilish, diagnostika qilish va xavfsiz ishlatish.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Xalqaro hamkorlik', 'items' => [
                        'Kafedra amaliy tayyorgarlik va amaliy tadqiqotlarni kuchaytirish uchun ishlab chiqarish korxonalari, energetika tashkilotlari, ilmiy muassasalar va hamkor universitetlar bilan hamkorlik qiladi.',
                    ]],
                    ['key' => 'department_structure', 'title' => 'Kafedra tuzilmasi', 'items' => ['Kafedra mudiri: Latipov Saidmurod Tuygunovich', 'Qabul vaqti: Seshanba-payshanba 10:00-13:00', 'Telefon: +998 91 979 88 22', 'Elektron pochta: stlatipov@gmail.com']],
                ],
            ],
            'ru' => [
                'name' => 'Кафедра электроэнергетики',
                'short_name' => 'Кафедра электроэнергетики',
                'description' => 'Кафедра электроэнергетики готовит специалистов в области электрических систем, энергетики, возобновляемой энергетики, автоматизации и безопасной эксплуатации современной энергетической инфраструктуры.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'О кафедре', 'items' => ['Кафедра объединяет теоретическую инженерную подготовку с лабораторной практикой, проектной работой, производственной практикой и прикладными исследованиями в электроэнергетике.']],
                    ['key' => 'subjects', 'title' => 'Учебные дисциплины', 'items' => [
                        'Основы электротехники',
                        'Электрические цепи и анализ сетей',
                        'Электрические машины и трансформаторы',
                        'Энергетические системы и подстанции',
                        'Релейная защита и автоматика',
                        'Силовая электроника и электроприводы',
                        'Возобновляемые источники энергии',
                        'Энергоэффективность и энергетический аудит',
                        'Техника высоких напряжений и электробезопасность',
                        'Производственная практика и выпускной проект',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'Программы и специализации', 'items' => ['60710400 - Энергетическая инженерия', '60710500 - Электротехника', '60712000 - Возобновляемые источники энергии']],
                    ['key' => 'research', 'title' => 'Научная работа', 'items' => [
                        'Повышение надежности и эффективности систем электроснабжения.',
                        'Развитие технологий возобновляемой энергетики и энергосберегающих решений.',
                        'Автоматизация, защита, диагностика и безопасная эксплуатация электрических сетей и промышленного оборудования.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'Международное сотрудничество', 'items' => [
                        'Кафедра сотрудничает с производственными предприятиями, энергетическими организациями, научными учреждениями и партнерскими университетами для усиления практической подготовки и прикладных исследований.',
                    ]],
                    ['key' => 'department_structure', 'title' => 'Структура кафедры', 'items' => ['Заведующий кафедрой: Латипов Саидмурод Туйгунович', 'Часы приема: вторник-четверг 10:00-13:00', 'Телефон: +998 91 979 88 22', 'Электронная почта: stlatipov@gmail.com']],
                ],
            ],
            'ar' => [
                'name' => 'قسم الهندسة الكهربائية والطاقة',
                'short_name' => 'قسم الهندسة الكهربائية والطاقة',
                'description' => 'يعد قسم الهندسة الكهربائية والطاقة متخصصين في الأنظمة الكهربائية وهندسة الطاقة والطاقة المتجددة والأتمتة والتشغيل الآمن للبنية التحتية الحديثة للطاقة.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'عن القسم', 'items' => ['يجمع القسم بين التعليم الهندسي النظري والتدريب المخبري ومشروعات التصميم والتدريب الصناعي والبحث التطبيقي في أنظمة الكهرباء والطاقة.']],
                    ['key' => 'subjects', 'title' => 'المواد التي تدرس في القسم', 'items' => [
                        'أساسيات الهندسة الكهربائية',
                        'الدوائر الكهربائية وتحليل الشبكات',
                        'الآلات الكهربائية والمحولات',
                        'أنظمة الطاقة والمحطات الفرعية',
                        'الحماية المرحلية والأتمتة',
                        'إلكترونيات القدرة والمحركات الكهربائية',
                        'مصادر الطاقة المتجددة',
                        'كفاءة الطاقة وتدقيق الطاقة',
                        'هندسة الجهد العالي والسلامة الكهربائية',
                        'التدريب الصناعي ومشروع التخرج',
                    ]],
                    ['key' => 'prepared_specialists', 'title' => 'البرامج والتخصصات', 'items' => ['60710400 - هندسة الطاقة', '60710500 - الهندسة الكهربائية', '60712000 - مصادر الطاقة المتجددة']],
                    ['key' => 'research', 'title' => 'الأعمال البحثية', 'items' => [
                        'تحسين موثوقية وكفاءة أنظمة الإمداد الكهربائي.',
                        'تطوير تقنيات الطاقة المتجددة وحلول ترشيد الطاقة.',
                        'أتمتة وحماية وتشخيص وتشغيل الشبكات الكهربائية والمعدات الصناعية بأمان.',
                    ]],
                    ['key' => 'cooperation', 'title' => 'التعاون الدولي', 'items' => [
                        'يتعاون القسم مع المؤسسات الصناعية ومنظمات الطاقة والمؤسسات البحثية والجامعات الشريكة لتعزيز التدريب العملي والبحث التطبيقي.',
                    ]],
                    ['key' => 'department_structure', 'title' => 'هيكل القسم', 'items' => ['رئيس القسم: لاتيبوف سعيد مراد تويغونوفيتش', 'ساعات الاستقبال: الثلاثاء-الخميس 10:00-13:00', 'الهاتف: +998 91 979 88 22', 'البريد الإلكتروني: stlatipov@gmail.com']],
                ],
            ],
        ];
    }

    private function staff(): array
    {
        return [
            'electrical-power-engineering-latipov-saidmurod-tuygunovich' => $this->staffItem('Latipov Saidmurod Tuygunovich', 'Латипов Саидмурод Туйгунович', 'لاتيبوف سعيد مراد تويغونوفيتش', 'Head of Department', 'Kafedra mudiri', 'Заведующий кафедрой', 'رئيس القسم'),
            'electrical-power-engineering-vakhitov-mubin-muminovich' => $this->staffItem('Vakhitov Mubin Muminovich', 'Вахитов Мубин Муминович', 'فاخيتوف موبين مومينوفيتش', 'Doctor of Technical Sciences, Professor', 'Texnika fanlari doktori, professor', 'Доктор технических наук, профессор', 'دكتور في العلوم التقنية، أستاذ'),
            'electrical-power-engineering-makhmudov-makhsud-idrisovich' => $this->staffItem('Makhmudov Makhsud Idrisovich', 'Махмудов Махсуд Идрисович', 'ماخمودوف ماخسود إدريسوفيتش', 'Doctor of Technical Sciences, Professor', 'Texnika fanlari doktori, professor', 'Доктор технических наук, профессор', 'دكتور في العلوم التقنية، أستاذ'),
            'electrical-power-engineering-jalilov-rashid-babakulovich' => $this->staffItem('Jalilov Rashid Babakulovich', 'Жалилов Рашид Бабакулович', 'جاليلوف راشيد باباكولوفيتش', 'Doctor of Technical Sciences, Professor', 'Texnika fanlari doktori, professor', 'Доктор технических наук, профессор', 'دكتور في العلوم التقنية، أستاذ'),
            'electrical-power-engineering-mirzoyev-narzullo-nuriddinovich' => $this->staffItem('Mirzoyev Narzullo Nuriddinovich', 'Мирзоев Нарзулло Нуриддинович', 'ميرزويف نارزوللو نوريددينوفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-khafizov-islam-ikramovich' => $this->staffItem('Khafizov Islam Ikramovich', 'Хафизов Ислам Икрамович', 'خافيزوف إسلام إكراموفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-khajiyev-qayim-beshimovich' => $this->staffItem('Khajiyev Qayim Beshimovich', 'Хажиев Кайим Бешимович', 'خاجييف قاييم بيشيموفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-toyirov-zuvur' => $this->staffItem('Toyirov Zuvur', 'Тойиров Зувур', 'توييروف زوفور', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-mirkhonov-utkir-kahramanovich' => $this->staffItem('Mirkhonov Utkir Kahramanovich', 'Мирхонов Уткир Кахраманович', 'ميرخونوف أوتكير قهرامانوفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-of-kathedra' => $this->staffItem('Nurov Siroj Sobirovich', 'Нуров Сирож Собирович', 'نوروف سيروج سوبيوروفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك', 'electrical-power-engineering-nurov-siroj-sobirovich'),
            'electrical-power-engineering-babanazarova-nargisa-kamilovna' => $this->staffItem('Babanazarova Nargisa Kamilovna', 'Бабаназарова Наргиса Камиловна', 'بابانازاروفا نارغيسا كاميلوفنا', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-nematov-laziz-alisherovich' => $this->staffItem('Nematov Laziz Alisherovich', 'Нематов Лазиз Алишерович', 'نيماتوف لازيز أليشيروفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-shoboyev-alisher-hikmatilloyevich' => $this->staffItem('Shoboyev Alisher Hikmatilloyevich', 'Шобоев Алишер Хикматиллоевич', 'شوبويف أليشير هيكماتيللويفيتش', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-nematov-shukhrat-nasullo-oglu' => $this->staffItem('Nematov Shukhrat Nasullo oglu', 'Нематов Шухрат Насулло угли', 'نيماتوف شوخرات ناسوللو أوغلي', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
            'electrical-power-engineering-mamedov-rasul-akif-ogli' => $this->staffItem("Mamedov Rasul Akif o'g'li", 'Мамедов Расул Акиф угли', 'ماميدوف راسول أكيف أوغلي', 'Associate Professor', 'Dotsent', 'Доцент', 'أستاذ مشارك'),
        ];
    }

    private function staffItem(string $enName, string $ruName, string $arName, string $enPosition, string $uzPosition, string $ruPosition, string $arPosition, ?string $slug = null): array
    {
        return [
            'slug' => $slug,
            'translations' => [
                'en' => ['name' => $enName, 'position' => $enPosition, 'bio' => "$enName serves as $enPosition in Electrical and Power Engineering, contributing to academic, methodological, and research development."],
                'uz' => ['name' => $enName, 'position' => $uzPosition, 'bio' => "$enName Elektr energetikasi kafedrasida $uzPosition sifatida ta’lim, metodik va ilmiy faoliyatga hissa qo‘shadi."],
                'ru' => ['name' => $ruName, 'position' => $ruPosition, 'bio' => "$ruName работает на кафедре электроэнергетики в должности: $ruPosition."],
                'ar' => ['name' => $arName, 'position' => $arPosition, 'bio' => "$arName يعمل في قسم الهندسة الكهربائية والطاقة بصفة $arPosition ويساهم في التطوير الأكاديمي والمنهجي والبحثي."],
            ],
        ];
    }
};
