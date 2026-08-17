<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('programs') || ! Schema::hasTable('program_translations')) {
            return;
        }

        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-engineering')->value('id');
        $departmentId = DB::table('departments')->where('slug', 'electrical-power-engineering')->value('id');

        $programs = $this->programs();

        foreach ($programs as $slug => $programData) {
            $program = DB::table('programs')->where('slug', $slug)->first();

            if (! $program) {
                continue;
            }

            DB::table('programs')->where('id', $program->id)->update([
                'faculty_id' => $facultyId ?: $program->faculty_id,
                'department_id' => $departmentId ?: $program->department_id,
                'official_code' => $programData['official_code'],
                'code' => $programData['official_code'],
                'degree' => 'bachelor',
                'duration_years' => 4,
                'language_of_study' => 'english, uzbek, russian',
                'is_active' => true,
                'updated_at' => now(),
            ]);

            foreach ($programData['translations'] as $locale => $translation) {
                DB::table('program_translations')->updateOrInsert(
                    [
                        'program_id' => $program->id,
                        'locale' => $locale,
                    ],
                    array_merge($translation, [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );
            }
        }
    }

    public function down(): void
    {
        // Content normalization is intentionally not destructive.
    }

    protected function programs(): array
    {
        return [
            'energy-engineering-60710400' => [
                'official_code' => '60710400',
                'translations' => [
                    'en' => [
                        'name' => 'Energy Engineering',
                        'description' => 'Energy Engineering prepares specialists for power generation, transmission, distribution, operation, and efficient management of modern energy systems. The program combines electrical engineering fundamentals, thermal energy, renewable technologies, energy audit, laboratory work, industrial practice, and graduation design.',
                        'requirements' => "Completed secondary education\nStrong preparation in mathematics and physics\nInterest in power systems, energy equipment, and engineering analysis",
                        'documents' => "Passport or ID document\nSecondary education certificate\nTranscript or grade record\nPhoto",
                        'curriculum_summary' => "Power plants and energy systems\nElectrical circuits, machines, and transformers\nThermal engineering and renewable energy technologies\nEnergy efficiency, energy audit, industrial practice, and graduation project",
                        'career_opportunities' => "Energy engineer\nPower plant specialist\nGrid operation engineer\nEnergy audit specialist\nIndustrial energy systems engineer",
                        'meta_title' => 'Energy Engineering Bachelor Program',
                        'meta_description' => 'Bachelor program in energy engineering focused on power generation, grids, efficiency, renewable technologies, and industrial practice.',
                    ],
                    'uz' => [
                        'name' => 'Energetika muhandisligi',
                        'description' => 'Energetika muhandisligi zamonaviy energetika tizimlarida energiyani ishlab chiqarish, uzatish, taqsimlash, ekspluatatsiya qilish va samarali boshqarish bo‘yicha mutaxassislar tayyorlaydi. Dastur elektrotexnika asoslari, issiqlik energetikasi, qayta tiklanuvchi texnologiyalar, energiya auditi, laboratoriya ishlari, ishlab chiqarish amaliyoti va bitiruv loyihasini qamrab oladi.',
                        'requirements' => "O‘rta ta’limni tugatganlik\nMatematika va fizika bo‘yicha mustahkam tayyorgarlik\nEnergetika tizimlari, energetik uskunalar va muhandislik tahliliga qiziqish",
                        'documents' => "Pasport yoki shaxsni tasdiqlovchi hujjat\nO‘rta ta’lim to‘g‘risidagi hujjat\nBaholar qaydnomasi yoki ilova\nFotosurat",
                        'curriculum_summary' => "Elektr stansiyalari va energetika tizimlari\nElektr zanjirlari, mashinalar va transformatorlar\nIssiqlik energetikasi va qayta tiklanuvchi energiya texnologiyalari\nEnergiya samaradorligi, energiya auditi, ishlab chiqarish amaliyoti va bitiruv loyihasi",
                        'career_opportunities' => "Energetika muhandisi\nElektr stansiyasi mutaxassisi\nElektr tarmoqlarini ekspluatatsiya qilish muhandisi\nEnergiya auditi mutaxassisi\nSanoat energetika tizimlari muhandisi",
                        'meta_title' => 'Energetika muhandisligi bakalavriat dasturi',
                        'meta_description' => 'Energiya ishlab chiqarish, tarmoqlar, samaradorlik, qayta tiklanuvchi texnologiyalar va amaliy tayyorgarlikka yo‘naltirilgan bakalavriat dasturi.',
                    ],
                    'ru' => [
                        'name' => 'Энергетическая инженерия',
                        'description' => 'Энергетическая инженерия готовит специалистов по производству, передаче, распределению, эксплуатации и эффективному управлению современными энергетическими системами. Программа объединяет основы электротехники, тепловую энергетику, возобновляемые технологии, энергоаудит, лабораторную подготовку, производственную практику и выпускное проектирование.',
                        'requirements' => "Завершенное среднее образование\nСильная подготовка по математике и физике\nИнтерес к энергосистемам, энергетическому оборудованию и инженерному анализу",
                        'documents' => "Паспорт или удостоверение личности\nДокумент о среднем образовании\nВыписка оценок или приложение\nФотография",
                        'curriculum_summary' => "Электростанции и энергетические системы\nЭлектрические цепи, машины и трансформаторы\nТепловая энергетика и технологии возобновляемой энергии\nЭнергоэффективность, энергоаудит, производственная практика и выпускной проект",
                        'career_opportunities' => "Инженер-энергетик\nСпециалист электростанции\nИнженер по эксплуатации электрических сетей\nСпециалист по энергоаудиту\nИнженер промышленных энергетических систем",
                        'meta_title' => 'Бакалаврская программа «Энергетическая инженерия»',
                        'meta_description' => 'Бакалаврская программа по энергетике, ориентированная на генерацию, сети, эффективность, возобновляемые технологии и практическую подготовку.',
                    ],
                    'ar' => [
                        'name' => 'هندسة الطاقة',
                        'description' => 'تؤهل هندسة الطاقة متخصصين في توليد الطاقة ونقلها وتوزيعها وتشغيلها وإدارة أنظمة الطاقة الحديثة بكفاءة. يجمع البرنامج بين أساسيات الهندسة الكهربائية والطاقة الحرارية وتقنيات الطاقة المتجددة وتدقيق الطاقة والعمل المخبري والتدريب الصناعي ومشروع التخرج.',
                        'requirements' => "إتمام التعليم الثانوي\nإعداد قوي في الرياضيات والفيزياء\nاهتمام بأنظمة الطاقة والمعدات الكهربائية والتحليل الهندسي",
                        'documents' => "جواز سفر أو وثيقة هوية\nشهادة التعليم الثانوي\nكشف درجات أو ملحق الشهادة\nصورة شخصية",
                        'curriculum_summary' => "محطات الطاقة وأنظمة الطاقة\nالدوائر الكهربائية والآلات والمحولات\nالطاقة الحرارية وتقنيات الطاقة المتجددة\nكفاءة الطاقة وتدقيق الطاقة والتدريب الصناعي ومشروع التخرج",
                        'career_opportunities' => "مهندس طاقة\nمتخصص في محطات الطاقة\nمهندس تشغيل شبكات كهربائية\nمتخصص تدقيق طاقة\nمهندس أنظمة طاقة صناعية",
                        'meta_title' => 'برنامج بكالوريوس هندسة الطاقة',
                        'meta_description' => 'برنامج بكالوريوس في هندسة الطاقة يركز على التوليد والشبكات والكفاءة وتقنيات الطاقة المتجددة والتدريب العملي.',
                    ],
                ],
            ],
            'electrical-engineering-60710500' => [
                'official_code' => '60710500',
                'translations' => [
                    'en' => [
                        'name' => 'Electrical Engineering',
                        'description' => 'Electrical Engineering trains specialists in electrical circuits, machines, power supply systems, substations, relay protection, automation, power electronics, electric drives, and safe operation of electrical installations. The program combines theory, laboratory practice, design work, and industrial training.',
                        'requirements' => "Completed secondary education\nStrong preparation in mathematics and physics\nInterest in electrical systems, automation, and engineering safety",
                        'documents' => "Passport or ID document\nSecondary education certificate\nTranscript or grade record\nPhoto",
                        'curriculum_summary' => "Electrical engineering fundamentals and circuit analysis\nElectrical machines, transformers, power electronics, and electric drives\nPower supply, substations, relay protection, and automation\nHigh-voltage engineering, electrical safety, industrial practice, and graduation project",
                        'career_opportunities' => "Electrical engineer\nPower supply engineer\nSubstation operation specialist\nRelay protection and automation specialist\nIndustrial electrical systems engineer",
                        'meta_title' => 'Electrical Engineering Bachelor Program',
                        'meta_description' => 'Bachelor program in electrical engineering focused on circuits, machines, power systems, automation, safety, and industrial practice.',
                    ],
                    'uz' => [
                        'name' => 'Elektr muhandisligi',
                        'description' => 'Elektr muhandisligi elektr zanjirlari, elektr mashinalari, elektr ta’minoti tizimlari, podstansiyalar, rele himoyasi, avtomatika, kuch elektronikasi, elektr yuritmalar va elektr qurilmalarini xavfsiz ekspluatatsiya qilish bo‘yicha mutaxassislar tayyorlaydi. Dastur nazariya, laboratoriya amaliyoti, loyihalash ishlari va ishlab chiqarish tayyorgarligini birlashtiradi.',
                        'requirements' => "O‘rta ta’limni tugatganlik\nMatematika va fizika bo‘yicha mustahkam tayyorgarlik\nElektr tizimlari, avtomatika va muhandislik xavfsizligiga qiziqish",
                        'documents' => "Pasport yoki shaxsni tasdiqlovchi hujjat\nO‘rta ta’lim to‘g‘risidagi hujjat\nBaholar qaydnomasi yoki ilova\nFotosurat",
                        'curriculum_summary' => "Elektrotexnika asoslari va elektr zanjirlarini tahlil qilish\nElektr mashinalari, transformatorlar, kuch elektronikasi va elektr yuritmalar\nElektr ta’minoti, podstansiyalar, rele himoyasi va avtomatika\nYuqori kuchlanish texnikasi, elektr xavfsizligi, ishlab chiqarish amaliyoti va bitiruv loyihasi",
                        'career_opportunities' => "Elektr muhandisi\nElektr ta’minoti muhandisi\nPodstansiyalarni ekspluatatsiya qilish mutaxassisi\nRele himoyasi va avtomatika mutaxassisi\nSanoat elektr tizimlari muhandisi",
                        'meta_title' => 'Elektr muhandisligi bakalavriat dasturi',
                        'meta_description' => 'Elektr zanjirlari, mashinalar, energetika tizimlari, avtomatika, xavfsizlik va amaliy tayyorgarlikka yo‘naltirilgan bakalavriat dasturi.',
                    ],
                    'ru' => [
                        'name' => 'Электроэнергетика и электротехника',
                        'description' => 'Электроэнергетика и электротехника готовит специалистов по электрическим цепям, электрическим машинам, системам электроснабжения, подстанциям, релейной защите, автоматике, силовой электронике, электроприводам и безопасной эксплуатации электроустановок. Программа сочетает теорию, лабораторную практику, проектирование и производственное обучение.',
                        'requirements' => "Завершенное среднее образование\nСильная подготовка по математике и физике\nИнтерес к электрическим системам, автоматике и инженерной безопасности",
                        'documents' => "Паспорт или удостоверение личности\nДокумент о среднем образовании\nВыписка оценок или приложение\nФотография",
                        'curriculum_summary' => "Основы электротехники и анализ электрических цепей\nЭлектрические машины, трансформаторы, силовая электроника и электроприводы\nЭлектроснабжение, подстанции, релейная защита и автоматика\nВысоковольтная техника, электробезопасность, производственная практика и выпускной проект",
                        'career_opportunities' => "Инженер-электрик\nИнженер по электроснабжению\nСпециалист по эксплуатации подстанций\nСпециалист по релейной защите и автоматике\nИнженер промышленных электрических систем",
                        'meta_title' => 'Бакалаврская программа «Электроэнергетика и электротехника»',
                        'meta_description' => 'Бакалаврская программа по электротехнике, ориентированная на цепи, машины, энергосистемы, автоматику, безопасность и практическую подготовку.',
                    ],
                    'ar' => [
                        'name' => 'الهندسة الكهربائية',
                        'description' => 'تؤهل الهندسة الكهربائية متخصصين في الدوائر الكهربائية والآلات الكهربائية وأنظمة التغذية والمحطات الفرعية والحماية المرحلية والأتمتة وإلكترونيات القدرة والمحركات الكهربائية والتشغيل الآمن للمنشآت الكهربائية. يجمع البرنامج بين الدراسة النظرية والعمل المخبري والتصميم والتدريب الصناعي.',
                        'requirements' => "إتمام التعليم الثانوي\nإعداد قوي في الرياضيات والفيزياء\nاهتمام بالأنظمة الكهربائية والأتمتة والسلامة الهندسية",
                        'documents' => "جواز سفر أو وثيقة هوية\nشهادة التعليم الثانوي\nكشف درجات أو ملحق الشهادة\nصورة شخصية",
                        'curriculum_summary' => "أساسيات الهندسة الكهربائية وتحليل الدوائر\nالآلات الكهربائية والمحولات وإلكترونيات القدرة والمحركات الكهربائية\nالتغذية الكهربائية والمحطات الفرعية والحماية المرحلية والأتمتة\nتقنيات الجهد العالي والسلامة الكهربائية والتدريب الصناعي ومشروع التخرج",
                        'career_opportunities' => "مهندس كهرباء\nمهندس تغذية كهربائية\nمتخصص تشغيل محطات فرعية\nمتخصص حماية مرحلية وأتمتة\nمهندس أنظمة كهربائية صناعية",
                        'meta_title' => 'برنامج بكالوريوس الهندسة الكهربائية',
                        'meta_description' => 'برنامج بكالوريوس في الهندسة الكهربائية يركز على الدوائر والآلات وأنظمة الطاقة والأتمتة والسلامة والتدريب العملي.',
                    ],
                ],
            ],
            'renewable-energy-sources-60712000' => [
                'official_code' => '60712000',
                'translations' => [
                    'en' => [
                        'name' => 'Renewable Energy Sources',
                        'description' => 'Renewable Energy Sources prepares specialists for the design, operation, monitoring, and integration of sustainable energy systems. The program focuses on solar, wind, small hydropower, bioenergy, energy storage, smart grids, energy efficiency, environmental safety, laboratory work, and field practice.',
                        'requirements' => "Completed secondary education\nStrong preparation in mathematics and physics\nInterest in sustainable energy, environmental responsibility, and engineering design",
                        'documents' => "Passport or ID document\nSecondary education certificate\nTranscript or grade record\nPhoto",
                        'curriculum_summary' => "Solar, wind, hydropower, and bioenergy technologies\nEnergy storage, smart grids, and power conversion systems\nRenewable energy project design, monitoring, and economic assessment\nEnergy efficiency, environmental safety, field practice, and graduation project",
                        'career_opportunities' => "Renewable energy engineer\nSolar and wind systems specialist\nEnergy efficiency specialist\nSustainable project designer\nSmart grid and energy storage technician",
                        'meta_title' => 'Renewable Energy Sources Bachelor Program',
                        'meta_description' => 'Bachelor program focused on solar, wind, bioenergy, storage, smart grids, energy efficiency, and sustainable energy project design.',
                    ],
                    'uz' => [
                        'name' => 'Qayta tiklanuvchi energiya manbalari',
                        'description' => 'Qayta tiklanuvchi energiya manbalari barqaror energetika tizimlarini loyihalash, ekspluatatsiya qilish, monitoring qilish va tarmoqqa integratsiya qilish bo‘yicha mutaxassislar tayyorlaydi. Dastur quyosh, shamol, kichik gidroenergetika, bioenergiya, energiyani saqlash, aqlli tarmoqlar, energiya samaradorligi, ekologik xavfsizlik, laboratoriya ishlari va dala amaliyotiga yo‘naltirilgan.',
                        'requirements' => "O‘rta ta’limni tugatganlik\nMatematika va fizika bo‘yicha mustahkam tayyorgarlik\nBarqaror energetika, ekologik mas’uliyat va muhandislik loyihalashiga qiziqish",
                        'documents' => "Pasport yoki shaxsni tasdiqlovchi hujjat\nO‘rta ta’lim to‘g‘risidagi hujjat\nBaholar qaydnomasi yoki ilova\nFotosurat",
                        'curriculum_summary' => "Quyosh, shamol, gidroenergetika va bioenergiya texnologiyalari\nEnergiyani saqlash, aqlli tarmoqlar va energiyani o‘zgartirish tizimlari\nQayta tiklanuvchi energiya loyihalarini loyihalash, monitoring qilish va iqtisodiy baholash\nEnergiya samaradorligi, ekologik xavfsizlik, dala amaliyoti va bitiruv loyihasi",
                        'career_opportunities' => "Qayta tiklanuvchi energiya muhandisi\nQuyosh va shamol tizimlari mutaxassisi\nEnergiya samaradorligi mutaxassisi\nBarqaror energetika loyihachisi\nAqlli tarmoqlar va energiya saqlash texnigi",
                        'meta_title' => 'Qayta tiklanuvchi energiya manbalari bakalavriat dasturi',
                        'meta_description' => 'Quyosh, shamol, bioenergiya, energiya saqlash, aqlli tarmoqlar, samaradorlik va barqaror energetika loyihalashiga yo‘naltirilgan bakalavriat dasturi.',
                    ],
                    'ru' => [
                        'name' => 'Возобновляемые источники энергии',
                        'description' => 'Возобновляемые источники энергии готовит специалистов по проектированию, эксплуатации, мониторингу и интеграции устойчивых энергетических систем. Программа ориентирована на солнечную и ветровую энергетику, малую гидроэнергетику, биоэнергию, накопители энергии, интеллектуальные сети, энергоэффективность, экологическую безопасность, лабораторные работы и полевую практику.',
                        'requirements' => "Завершенное среднее образование\nСильная подготовка по математике и физике\nИнтерес к устойчивой энергетике, экологической ответственности и инженерному проектированию",
                        'documents' => "Паспорт или удостоверение личности\nДокумент о среднем образовании\nВыписка оценок или приложение\nФотография",
                        'curriculum_summary' => "Солнечные, ветровые, гидроэнергетические и биоэнергетические технологии\nНакопители энергии, интеллектуальные сети и системы преобразования энергии\nПроектирование, мониторинг и экономическая оценка проектов ВИЭ\nЭнергоэффективность, экологическая безопасность, полевая практика и выпускной проект",
                        'career_opportunities' => "Инженер по возобновляемой энергетике\nСпециалист по солнечным и ветровым системам\nСпециалист по энергоэффективности\nПроектировщик устойчивых энергетических проектов\nТехник по интеллектуальным сетям и накопителям энергии",
                        'meta_title' => 'Бакалаврская программа «Возобновляемые источники энергии»',
                        'meta_description' => 'Бакалаврская программа по солнечной, ветровой и биоэнергетике, накопителям, интеллектуальным сетям, эффективности и устойчивому проектированию.',
                    ],
                    'ar' => [
                        'name' => 'مصادر الطاقة المتجددة',
                        'description' => 'يؤهل برنامج مصادر الطاقة المتجددة متخصصين في تصميم وتشغيل ومراقبة ودمج أنظمة الطاقة المستدامة. يركز البرنامج على الطاقة الشمسية وطاقة الرياح والطاقة الكهرومائية الصغيرة والطاقة الحيوية وتخزين الطاقة والشبكات الذكية وكفاءة الطاقة والسلامة البيئية والعمل المخبري والتدريب الميداني.',
                        'requirements' => "إتمام التعليم الثانوي\nإعداد قوي في الرياضيات والفيزياء\nاهتمام بالطاقة المستدامة والمسؤولية البيئية والتصميم الهندسي",
                        'documents' => "جواز سفر أو وثيقة هوية\nشهادة التعليم الثانوي\nكشف درجات أو ملحق الشهادة\nصورة شخصية",
                        'curriculum_summary' => "تقنيات الطاقة الشمسية والرياح والكهرومائية والحيوية\nتخزين الطاقة والشبكات الذكية وأنظمة تحويل الطاقة\nتصميم ومراقبة وتقييم مشاريع الطاقة المتجددة اقتصادياً\nكفاءة الطاقة والسلامة البيئية والتدريب الميداني ومشروع التخرج",
                        'career_opportunities' => "مهندس طاقة متجددة\nمتخصص أنظمة شمسية ورياح\nمتخصص كفاءة الطاقة\nمصمم مشاريع طاقة مستدامة\nفني شبكات ذكية وتخزين طاقة",
                        'meta_title' => 'برنامج بكالوريوس مصادر الطاقة المتجددة',
                        'meta_description' => 'برنامج بكالوريوس يركز على الطاقة الشمسية والرياح والطاقة الحيوية والتخزين والشبكات الذكية والكفاءة وتصميم مشاريع الطاقة المستدامة.',
                    ],
                ],
            ],
        ];
    }
};
