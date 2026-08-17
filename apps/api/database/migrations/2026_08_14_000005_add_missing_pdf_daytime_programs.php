<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->programs() as $index => $program) {
            $facultyId = DB::table('faculties')->where('slug', $program['faculty_slug'])->value('id');
            $departmentId = DB::table('departments')->where('slug', $program['department_slug'])->value('id');

            if (! $facultyId || ! $departmentId) {
                continue;
            }

            $programId = DB::table('programs')->where('slug', $program['slug'])->value('id')
                ?: DB::table('programs')->where('official_code', $program['official_code'])->value('id');

            $payload = [
                'faculty_id' => $facultyId,
                'department_id' => $departmentId,
                'slug' => $program['slug'],
                'code' => $program['slug'],
                'official_code' => $program['official_code'],
                'track' => null,
                'degree' => 'bachelor',
                'duration_years' => 4,
                'study_mode' => 'full_time',
                'language_of_study' => 'en',
                'tuition_fee' => 0,
                'currency' => 'UZS',
                'image' => null,
                'is_active' => true,
                'sort_order' => 900 + $index,
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('programs', 'show_on_homepage')) {
                $payload['show_on_homepage'] = false;
            }

            if (Schema::hasColumn('programs', 'homepage_sort_order')) {
                $payload['homepage_sort_order'] = 0;
            }

            if ($programId) {
                DB::table('programs')->where('id', $programId)->update($payload);
            } else {
                $programId = DB::table('programs')->insertGetId($payload + ['created_at' => now()]);
            }

            foreach ($program['translations'] as $locale => $translation) {
                DB::table('program_translations')->updateOrInsert(
                    ['program_id' => $programId, 'locale' => $locale],
                    [
                        'name' => $translation['name'],
                        'description' => $translation['description'],
                        'requirements' => $translation['requirements'],
                        'documents' => $translation['documents'],
                        'curriculum_summary' => $translation['curriculum_summary'],
                        'career_opportunities' => $translation['career_opportunities'],
                        'meta_title' => $program['official_code'].' - '.$translation['name'],
                        'meta_description' => Str::limit($translation['description'], 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // These programs are present in the official 2026/2027 daytime admission PDF.
    }

    private function programs(): array
    {
        return [
            [
                'official_code' => '60711100',
                'slug' => 'biomedical-engineering-60711100',
                'faculty_slug' => 'faculty-of-service-and-digitalization',
                'department_slug' => 'technological-processes-production-automation',
                'translations' => [
                    'en' => [
                        'name' => 'Biomedical Engineering',
                        'description' => 'Biomedical Engineering is a four-year bachelor program focused on engineering solutions for medical technology, diagnostic systems, biomedical devices, automation, and applied digital technologies in healthcare.',
                        'requirements' => 'Secondary education certificate, passport or identity document, and application documents according to university admission requirements.',
                        'documents' => 'Passport copy, education document, transcript where applicable, photo, and required application forms.',
                        'curriculum_summary' => 'Duration of study: 4 years. The curriculum combines engineering fundamentals, electronics, measurement systems, biomedical devices, automation, laboratory practice, and industry-oriented projects.',
                        'career_opportunities' => 'Graduates can work with biomedical equipment, diagnostic systems, medical technology services, laboratories, healthcare technology companies, production enterprises, and research projects.',
                    ],
                    'uz' => [
                        'name' => 'Biotibbiyot muhandisligi',
                        'description' => 'Biotibbiyot muhandisligi tibbiyot texnologiyalari, diagnostika tizimlari, biotibbiyot qurilmalari, avtomatlashtirish va sog‘liqni saqlashdagi amaliy raqamli texnologiyalar uchun muhandislik yechimlariga yo‘naltirilgan to‘rt yillik bakalavriat dasturidir.',
                        'requirements' => 'O‘rta taʼlim hujjati, pasport yoki shaxsni tasdiqlovchi hujjat hamda universitet qabul talablariga muvofiq ariza hujjatlari.',
                        'documents' => 'Pasport nusxasi, taʼlim hujjati, zarur hollarda transkript, fotosurat va talab etilgan ariza shakllari.',
                        'curriculum_summary' => 'O‘qish muddati: 4 yil. O‘quv reja muhandislik asoslari, elektronika, o‘lchash tizimlari, biotibbiyot qurilmalari, avtomatlashtirish, laboratoriya amaliyoti va sohaga yo‘naltirilgan loyihalarni birlashtiradi.',
                        'career_opportunities' => 'Bitiruvchilar biotibbiyot uskunalari, diagnostika tizimlari, tibbiy texnologiya xizmatlari, laboratoriyalar, sog‘liqni saqlash texnologiyalari kompaniyalari, ishlab chiqarish korxonalari va tadqiqot loyihalarida ishlashi mumkin.',
                    ],
                    'ru' => [
                        'name' => 'Биомедицинская инженерия',
                        'description' => 'Биомедицинская инженерия — четырехлетняя программа бакалавриата, ориентированная на инженерные решения для медицинских технологий, диагностических систем, биомедицинских устройств, автоматизации и прикладных цифровых технологий в здравоохранении.',
                        'requirements' => 'Документ о среднем образовании, паспорт или удостоверение личности, а также документы заявления согласно требованиям приема университета.',
                        'documents' => 'Копия паспорта, документ об образовании, транскрипт при необходимости, фотография и требуемые формы заявления.',
                        'curriculum_summary' => 'Срок обучения: 4 года. Учебный план объединяет инженерные основы, электронику, измерительные системы, биомедицинские устройства, автоматизацию, лабораторную практику и отраслевые проекты.',
                        'career_opportunities' => 'Выпускники могут работать с биомедицинским оборудованием, диагностическими системами, сервисом медицинских технологий, лабораториями, компаниями медицинских технологий, производственными предприятиями и исследовательскими проектами.',
                    ],
                    'ar' => [
                        'name' => 'الهندسة الطبية الحيوية',
                        'description' => 'الهندسة الطبية الحيوية برنامج بكالوريوس مدته أربع سنوات يركز على الحلول الهندسية للتقنيات الطبية وأنظمة التشخيص والأجهزة الطبية الحيوية والأتمتة والتقنيات الرقمية التطبيقية في الرعاية الصحية.',
                        'requirements' => 'شهادة التعليم الثانوي، جواز سفر أو وثيقة هوية، ومستندات التقديم وفق متطلبات القبول في الجامعة.',
                        'documents' => 'نسخة جواز السفر، وثيقة التعليم، كشف الدرجات عند الحاجة، صورة شخصية، ونماذج التقديم المطلوبة.',
                        'curriculum_summary' => 'مدة الدراسة: 4 سنوات. يجمع المنهج بين أساسيات الهندسة والإلكترونيات وأنظمة القياس والأجهزة الطبية الحيوية والأتمتة والتدريب المخبري والمشروعات المرتبطة بالصناعة.',
                        'career_opportunities' => 'يمكن للخريجين العمل في معدات الطب الحيوي وأنظمة التشخيص وخدمات التقنية الطبية والمختبرات وشركات تكنولوجيا الرعاية الصحية والمؤسسات الإنتاجية ومشروعات البحث.',
                    ],
                ],
            ],
            [
                'official_code' => '60712000',
                'slug' => 'renewable-energy-sources-60712000',
                'faculty_slug' => 'faculty-of-engineering',
                'department_slug' => 'electrical-power-engineering',
                'translations' => [
                    'en' => [
                        'name' => 'Renewable Energy Sources',
                        'description' => 'Renewable Energy Sources is a four-year bachelor program focused on solar, wind, hydro, bioenergy, energy conversion systems, smart grids, and sustainable power engineering.',
                        'requirements' => 'Secondary education certificate, passport or identity document, and application documents according to university admission requirements.',
                        'documents' => 'Passport copy, education document, transcript where applicable, photo, and required application forms.',
                        'curriculum_summary' => 'Duration of study: 4 years. The curriculum covers electrical engineering foundations, renewable energy technologies, power electronics, energy storage, grid integration, laboratory practice, and industrial internship.',
                        'career_opportunities' => 'Graduates can work in renewable energy companies, power utilities, solar and wind projects, energy auditing, grid operations, industrial energy systems, and sustainability projects.',
                    ],
                    'uz' => [
                        'name' => 'Qayta tiklanuvchi energiya manbalari',
                        'description' => 'Qayta tiklanuvchi energiya manbalari quyosh, shamol, gidroenergiya, bioenergiya, energiyani o‘zgartirish tizimlari, aqlli tarmoqlar va barqaror energetika muhandisligiga yo‘naltirilgan to‘rt yillik bakalavriat dasturidir.',
                        'requirements' => 'O‘rta taʼlim hujjati, pasport yoki shaxsni tasdiqlovchi hujjat hamda universitet qabul talablariga muvofiq ariza hujjatlari.',
                        'documents' => 'Pasport nusxasi, taʼlim hujjati, zarur hollarda transkript, fotosurat va talab etilgan ariza shakllari.',
                        'curriculum_summary' => 'O‘qish muddati: 4 yil. O‘quv reja elektrotexnika asoslari, qayta tiklanuvchi energiya texnologiyalari, kuch elektronikasi, energiyani saqlash, tarmoqqa integratsiya, laboratoriya amaliyoti va ishlab chiqarish amaliyotini qamrab oladi.',
                        'career_opportunities' => 'Bitiruvchilar qayta tiklanuvchi energiya kompaniyalari, energetika korxonalari, quyosh va shamol loyihalari, energiya auditi, tarmoq ekspluatatsiyasi, sanoat energetika tizimlari va barqarorlik loyihalarida ishlashi mumkin.',
                    ],
                    'ru' => [
                        'name' => 'Возобновляемые источники энергии',
                        'description' => 'Возобновляемые источники энергии — четырехлетняя программа бакалавриата, ориентированная на солнечную, ветровую, гидро- и биоэнергетику, системы преобразования энергии, интеллектуальные сети и устойчивую электроэнергетику.',
                        'requirements' => 'Документ о среднем образовании, паспорт или удостоверение личности, а также документы заявления согласно требованиям приема университета.',
                        'documents' => 'Копия паспорта, документ об образовании, транскрипт при необходимости, фотография и требуемые формы заявления.',
                        'curriculum_summary' => 'Срок обучения: 4 года. Учебный план включает основы электроэнергетики, технологии возобновляемой энергетики, силовую электронику, накопление энергии, интеграцию в сеть, лабораторную практику и производственную стажировку.',
                        'career_opportunities' => 'Выпускники могут работать в компаниях возобновляемой энергетики, энергоснабжающих организациях, солнечных и ветровых проектах, энергоаудите, эксплуатации сетей, промышленных энергосистемах и проектах устойчивого развития.',
                    ],
                    'ar' => [
                        'name' => 'مصادر الطاقة المتجددة',
                        'description' => 'مصادر الطاقة المتجددة برنامج بكالوريوس مدته أربع سنوات يركز على الطاقة الشمسية والرياح والطاقة المائية والطاقة الحيوية وأنظمة تحويل الطاقة والشبكات الذكية وهندسة الطاقة المستدامة.',
                        'requirements' => 'شهادة التعليم الثانوي، جواز سفر أو وثيقة هوية، ومستندات التقديم وفق متطلبات القبول في الجامعة.',
                        'documents' => 'نسخة جواز السفر، وثيقة التعليم، كشف الدرجات عند الحاجة، صورة شخصية، ونماذج التقديم المطلوبة.',
                        'curriculum_summary' => 'مدة الدراسة: 4 سنوات. يغطي المنهج أساسيات الهندسة الكهربائية وتقنيات الطاقة المتجددة وإلكترونيات القدرة وتخزين الطاقة وربط الشبكات والتدريب المخبري والتدريب الصناعي.',
                        'career_opportunities' => 'يمكن للخريجين العمل في شركات الطاقة المتجددة ومرافق الكهرباء ومشروعات الطاقة الشمسية والرياح وتدقيق الطاقة وتشغيل الشبكات وأنظمة الطاقة الصناعية ومشروعات الاستدامة.',
                    ],
                ],
            ],
            [
                'official_code' => '61010400',
                'slug' => 'logistics-61010400',
                'faculty_slug' => 'faculty-of-service-and-digitalization',
                'department_slug' => 'economics-and-management',
                'translations' => [
                    'en' => [
                        'name' => 'Logistics',
                        'description' => 'Logistics is a four-year bachelor program focused on supply chains, transport systems, warehouse management, procurement, digital logistics, service operations, and business process optimization.',
                        'requirements' => 'Secondary education certificate, passport or identity document, and application documents according to university admission requirements.',
                        'documents' => 'Passport copy, education document, transcript where applicable, photo, and required application forms.',
                        'curriculum_summary' => 'Duration of study: 4 years. The curriculum includes economics, management, supply chain management, transport logistics, warehouse systems, procurement, digital tools, analytics, and practical training.',
                        'career_opportunities' => 'Graduates can work as logistics coordinators, supply chain specialists, warehouse managers, procurement officers, transport planners, operations analysts, and service process managers.',
                    ],
                    'uz' => [
                        'name' => 'Logistika',
                        'description' => 'Logistika taʼminot zanjirlari, transport tizimlari, ombor boshqaruvi, xaridlar, raqamli logistika, servis operatsiyalari va biznes jarayonlarini optimallashtirishga yo‘naltirilgan to‘rt yillik bakalavriat dasturidir.',
                        'requirements' => 'O‘rta taʼlim hujjati, pasport yoki shaxsni tasdiqlovchi hujjat hamda universitet qabul talablariga muvofiq ariza hujjatlari.',
                        'documents' => 'Pasport nusxasi, taʼlim hujjati, zarur hollarda transkript, fotosurat va talab etilgan ariza shakllari.',
                        'curriculum_summary' => 'O‘qish muddati: 4 yil. O‘quv reja iqtisodiyot, menejment, taʼminot zanjiri boshqaruvi, transport logistikasi, ombor tizimlari, xaridlar, raqamli vositalar, tahlil va amaliy tayyorgarlikni o‘z ichiga oladi.',
                        'career_opportunities' => 'Bitiruvchilar logistika koordinatori, taʼminot zanjiri mutaxassisi, ombor menejeri, xaridlar mutaxassisi, transport rejalashtiruvchisi, operatsion tahlilchi va servis jarayonlari menejeri sifatida ishlashi mumkin.',
                    ],
                    'ru' => [
                        'name' => 'Логистика',
                        'description' => 'Логистика — четырехлетняя программа бакалавриата, ориентированная на цепочки поставок, транспортные системы, складское управление, закупки, цифровую логистику, сервисные операции и оптимизацию бизнес-процессов.',
                        'requirements' => 'Документ о среднем образовании, паспорт или удостоверение личности, а также документы заявления согласно требованиям приема университета.',
                        'documents' => 'Копия паспорта, документ об образовании, транскрипт при необходимости, фотография и требуемые формы заявления.',
                        'curriculum_summary' => 'Срок обучения: 4 года. Учебный план включает экономику, менеджмент, управление цепями поставок, транспортную логистику, складские системы, закупки, цифровые инструменты, аналитику и практическую подготовку.',
                        'career_opportunities' => 'Выпускники могут работать координаторами логистики, специалистами по цепям поставок, менеджерами склада, специалистами по закупкам, транспортными планировщиками, операционными аналитиками и менеджерами сервисных процессов.',
                    ],
                    'ar' => [
                        'name' => 'اللوجستيات',
                        'description' => 'اللوجستيات برنامج بكالوريوس مدته أربع سنوات يركز على سلاسل الإمداد وأنظمة النقل وإدارة المستودعات والمشتريات واللوجستيات الرقمية وعمليات الخدمات وتحسين العمليات التجارية.',
                        'requirements' => 'شهادة التعليم الثانوي، جواز سفر أو وثيقة هوية، ومستندات التقديم وفق متطلبات القبول في الجامعة.',
                        'documents' => 'نسخة جواز السفر، وثيقة التعليم، كشف الدرجات عند الحاجة، صورة شخصية، ونماذج التقديم المطلوبة.',
                        'curriculum_summary' => 'مدة الدراسة: 4 سنوات. يشمل المنهج الاقتصاد والإدارة وإدارة سلاسل الإمداد ولوجستيات النقل وأنظمة المستودعات والمشتريات والأدوات الرقمية والتحليلات والتدريب العملي.',
                        'career_opportunities' => 'يمكن للخريجين العمل كمنسقي لوجستيات ومتخصصي سلاسل إمداد ومديري مستودعات ومسؤولي مشتريات ومخططي نقل ومحللي عمليات ومديري عمليات الخدمات.',
                    ],
                ],
            ],
            [
                'official_code' => '61020000',
                'slug' => 'occupational-safety-and-technical-safety-61020000',
                'faculty_slug' => 'faculty-of-natural-resources-management',
                'department_slug' => 'industrial-ecology-hydrogeology',
                'translations' => [
                    'en' => [
                        'name' => 'Occupational Safety and Technical Safety',
                        'description' => 'Occupational Safety and Technical Safety is a four-year bachelor program focused on workplace safety, industrial risk management, technical safety systems, emergency preparedness, environmental protection, and safe production processes.',
                        'requirements' => 'Secondary education certificate, passport or identity document, and application documents according to university admission requirements.',
                        'documents' => 'Passport copy, education document, transcript where applicable, photo, and required application forms.',
                        'curriculum_summary' => 'Duration of study: 4 years. The curriculum covers occupational health and safety, industrial safety, emergency protection, risk assessment, environmental safety, technical regulation, and practical training.',
                        'career_opportunities' => 'Graduates can work as occupational safety specialists, industrial safety engineers, risk assessors, environmental safety officers, emergency preparedness specialists, and safety compliance managers.',
                    ],
                    'uz' => [
                        'name' => 'Mehnat muhofazasi va texnika xavfsizligi',
                        'description' => 'Mehnat muhofazasi va texnika xavfsizligi ish joyi xavfsizligi, sanoat xavflarini boshqarish, texnik xavfsizlik tizimlari, favqulodda holatlarga tayyorgarlik, atrof-muhit muhofazasi va xavfsiz ishlab chiqarish jarayonlariga yo‘naltirilgan to‘rt yillik bakalavriat dasturidir.',
                        'requirements' => 'O‘rta taʼlim hujjati, pasport yoki shaxsni tasdiqlovchi hujjat hamda universitet qabul talablariga muvofiq ariza hujjatlari.',
                        'documents' => 'Pasport nusxasi, taʼlim hujjati, zarur hollarda transkript, fotosurat va talab etilgan ariza shakllari.',
                        'curriculum_summary' => 'O‘qish muddati: 4 yil. O‘quv reja mehnat muhofazasi, sanoat xavfsizligi, favqulodda vaziyatlardan himoya, xavflarni baholash, ekologik xavfsizlik, texnik tartibga solish va amaliy tayyorgarlikni qamrab oladi.',
                        'career_opportunities' => 'Bitiruvchilar mehnat xavfsizligi mutaxassisi, sanoat xavfsizligi muhandisi, xavflarni baholash mutaxassisi, ekologik xavfsizlik xodimi, favqulodda holatlarga tayyorgarlik mutaxassisi va xavfsizlik talablariga rioya etish menejeri sifatida ishlashi mumkin.',
                    ],
                    'ru' => [
                        'name' => 'Охрана труда и техническая безопасность',
                        'description' => 'Охрана труда и техническая безопасность — четырехлетняя программа бакалавриата, ориентированная на безопасность рабочих мест, управление промышленными рисками, системы технической безопасности, готовность к чрезвычайным ситуациям, охрану окружающей среды и безопасные производственные процессы.',
                        'requirements' => 'Документ о среднем образовании, паспорт или удостоверение личности, а также документы заявления согласно требованиям приема университета.',
                        'documents' => 'Копия паспорта, документ об образовании, транскрипт при необходимости, фотография и требуемые формы заявления.',
                        'curriculum_summary' => 'Срок обучения: 4 года. Учебный план охватывает охрану труда, промышленную безопасность, защиту в чрезвычайных ситуациях, оценку рисков, экологическую безопасность, техническое регулирование и практическую подготовку.',
                        'career_opportunities' => 'Выпускники могут работать специалистами по охране труда, инженерами промышленной безопасности, оценщиками рисков, специалистами по экологической безопасности, специалистами по готовности к чрезвычайным ситуациям и менеджерами по соблюдению требований безопасности.',
                    ],
                    'ar' => [
                        'name' => 'السلامة المهنية والسلامة التقنية',
                        'description' => 'السلامة المهنية والسلامة التقنية برنامج بكالوريوس مدته أربع سنوات يركز على سلامة أماكن العمل وإدارة المخاطر الصناعية وأنظمة السلامة التقنية والاستعداد للطوارئ وحماية البيئة وعمليات الإنتاج الآمنة.',
                        'requirements' => 'شهادة التعليم الثانوي، جواز سفر أو وثيقة هوية، ومستندات التقديم وفق متطلبات القبول في الجامعة.',
                        'documents' => 'نسخة جواز السفر، وثيقة التعليم، كشف الدرجات عند الحاجة، صورة شخصية، ونماذج التقديم المطلوبة.',
                        'curriculum_summary' => 'مدة الدراسة: 4 سنوات. يغطي المنهج الصحة والسلامة المهنية والسلامة الصناعية والحماية في حالات الطوارئ وتقييم المخاطر والسلامة البيئية والتنظيم التقني والتدريب العملي.',
                        'career_opportunities' => 'يمكن للخريجين العمل كمتخصصي سلامة مهنية ومهندسي سلامة صناعية ومقيمي مخاطر ومسؤولي سلامة بيئية ومتخصصي استعداد للطوارئ ومديري امتثال لمتطلبات السلامة.',
                    ],
                ],
            ],
        ];
    }
};
