<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'technological-machines-equipment')->value('id');

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
                'technological-machines-equipment-0-prof-erkin-s-rahmonov',
                'technological-machines-equipment-1-dr-zuxra-sh-kamolova',
                'technological-machines-equipment-2-bahodir-b-sodiqov',
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
        DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->where('slug', 'technological-machines-equipment-assistant-sf-muxamedjanova')
            ->update(['slug' => 'technological-machines-equipment-ibragimov-ravshan-rustamovich']);

        foreach ($this->staff() as $index => $staff) {
            $profileId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $staff['slug'])
                ->value('id');

            if (! $profileId) {
                continue;
            }

            DB::table('staff_profiles')->where('id', $profileId)->update([
                'sort_order' => ($index + 1) * 10,
                'is_active' => true,
                'updated_at' => now(),
            ]);

            foreach ($staff['translations'] as $locale => $translation) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $translation['name'],
                        'position' => $translation['position'],
                        'bio' => $translation['name'].' serves as '.$translation['position'].' in Technological Machines and Equipment, contributing to academic, methodological, and research development.',
                        'office' => $translation['office'] ?? 'Monday-Friday 14:00-16:00',
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
                'name' => 'Technological Machines and Equipment',
                'short_name' => 'Technological Machines and Equipment',
                'description' => 'Technological Machines and Equipment is part of the Faculty of Engineering and connects machine design, technological equipment, production systems, laboratory training, and industrial practice.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'About the Department', 'items' => ['Technological Machines and Equipment prepares specialists for machine design, technological equipment, production systems, repair, operation, and modern industrial processes.']],
                    ['key' => 'subjects', 'title' => 'Taught Subjects', 'items' => ['Introduction to the specialty', 'Technological machines and equipment', 'Machine parts and mechanisms', 'Materials science and structural materials', 'Hydraulics and hydraulic systems', 'Computer-aided design', 'Manufacturing processes and equipment', 'Operation and repair of technological equipment', 'Automation of production processes', 'Industrial practice and graduation project']],
                    ['key' => 'prepared_specialists', 'title' => 'Programs and Specializations', 'items' => ['60720400 - Technological Machines and Equipment', '60721800 - Manufacturing Engineering']],
                    ['key' => 'research', 'title' => 'Research Work', 'items' => ['The department conducts research on improving technological machines, equipment reliability, production efficiency, material processing, machine design, and industrial automation.']],
                    ['key' => 'cooperation', 'title' => 'International Cooperation', 'items' => ['The department develops cooperation with industrial enterprises, research organizations, and partner universities to strengthen practical training and applied research.']],
                    ['key' => 'department_structure', 'title' => 'Department Structure', 'items' => ['Head of Department: O‘rinov Uyg‘un Abdullayevich', 'Office hours: Monday-Friday 14:00-16:00', 'Phone: +998 90 744 18 22', 'Email: -']],
                ],
            ],
            'uz' => [
                'name' => 'Texnologik mashinalar va jihozlar',
                'short_name' => 'Texnologik mashinalar va jihozlar',
                'description' => 'Texnologik mashinalar va jihozlar kafedrasi Muhandislik fakulteti tarkibida mashina loyihalash, texnologik jihozlar, ishlab chiqarish tizimlari, laboratoriya tayyorgarligi va ishlab chiqarish amaliyotini birlashtiradi.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'Kafedra haqida', 'items' => ['Kafedra mashina loyihalash, texnologik jihozlar, ishlab chiqarish tizimlari, taʼmirlash, ekspluatatsiya va zamonaviy sanoat jarayonlari bo‘yicha mutaxassislar tayyorlaydi.']],
                    ['key' => 'subjects', 'title' => 'O‘qitiladigan fanlar', 'items' => ['Mutaxassislikka kirish', 'Texnologik mashinalar va jihozlar', 'Mashina detallari va mexanizmlar', 'Materialshunoslik va konstruksion materiallar', 'Gidravlika va gidravlik tizimlar', 'Kompyuter yordamida loyihalash', 'Ishlab chiqarish jarayonlari va jihozlari', 'Texnologik jihozlarni ekspluatatsiya qilish va taʼmirlash', 'Ishlab chiqarish jarayonlarini avtomatlashtirish', 'Ishlab chiqarish amaliyoti va bitiruv loyihasi']],
                    ['key' => 'prepared_specialists', 'title' => 'Dasturlar va mutaxassisliklar', 'items' => ['60720400 - Texnologik mashinalar va jihozlar', '60721800 - Ishlab chiqarish muhandisligi']],
                    ['key' => 'research', 'title' => 'Ilmiy ishlar', 'items' => ['Kafedra texnologik mashinalarni takomillashtirish, jihozlar ishonchliligi, ishlab chiqarish samaradorligi, materiallarga ishlov berish, mashina loyihalash va sanoat avtomatlashtirish yo‘nalishlarida tadqiqotlar olib boradi.']],
                    ['key' => 'cooperation', 'title' => 'Xalqaro hamkorlik', 'items' => ['Kafedra amaliy taʼlim va amaliy tadqiqotlarni kuchaytirish uchun sanoat korxonalari, ilmiy tashkilotlar va hamkor universitetlar bilan aloqalarni rivojlantiradi.']],
                    ['key' => 'department_structure', 'title' => 'Kafedra tuzilmasi', 'items' => ['Kafedra mudiri: O‘rinov Uyg‘un Abdullayevich', 'Qabul vaqti: Dushanba-Juma 14:00-16:00', 'Telefon: +998 90 744 18 22', 'Email: -']],
                ],
            ],
            'ru' => [
                'name' => 'Технологические машины и оборудование',
                'short_name' => 'Технологические машины и оборудование',
                'description' => 'Кафедра технологических машин и оборудования входит в состав инженерного факультета и объединяет проектирование машин, технологическое оборудование, производственные системы, лабораторную подготовку и производственную практику.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'О кафедре', 'items' => ['Кафедра готовит специалистов по проектированию машин, технологическому оборудованию, производственным системам, ремонту, эксплуатации и современным промышленным процессам.']],
                    ['key' => 'subjects', 'title' => 'Преподаваемые дисциплины', 'items' => ['Введение в специальность', 'Технологические машины и оборудование', 'Детали машин и механизмы', 'Материаловедение и конструкционные материалы', 'Гидравлика и гидравлические системы', 'Компьютерное проектирование', 'Производственные процессы и оборудование', 'Эксплуатация и ремонт технологического оборудования', 'Автоматизация производственных процессов', 'Производственная практика и выпускной проект']],
                    ['key' => 'prepared_specialists', 'title' => 'Программы и специализации', 'items' => ['60720400 - Технологические машины и оборудование', '60721800 - Производственная инженерия']],
                    ['key' => 'research', 'title' => 'Научная работа', 'items' => ['Кафедра проводит исследования по совершенствованию технологических машин, надежности оборудования, эффективности производства, обработке материалов, проектированию машин и промышленной автоматизации.']],
                    ['key' => 'cooperation', 'title' => 'Международное сотрудничество', 'items' => ['Кафедра развивает сотрудничество с промышленными предприятиями, научными организациями и вузами-партнерами для усиления практической подготовки и прикладных исследований.']],
                    ['key' => 'department_structure', 'title' => 'Структура кафедры', 'items' => ['Заведующий кафедрой: Оринов Уйгун Абдуллаевич', 'Время приема: понедельник-пятница 14:00-16:00', 'Телефон: +998 90 744 18 22', 'Email: -']],
                ],
            ],
            'ar' => [
                'name' => 'الآلات والمعدات التكنولوجية',
                'short_name' => 'الآلات والمعدات التكنولوجية',
                'description' => 'قسم الآلات والمعدات التكنولوجية جزء من كلية الهندسة، ويربط تصميم الآلات والمعدات التكنولوجية وأنظمة الإنتاج والتدريب المخبري والتطبيق الصناعي.',
                'sections' => [
                    ['key' => 'overview', 'title' => 'عن القسم', 'items' => ['يعد القسم متخصصين في تصميم الآلات، والمعدات التكنولوجية، وأنظمة الإنتاج، والصيانة، والتشغيل، والعمليات الصناعية الحديثة.']],
                    ['key' => 'subjects', 'title' => 'المواد الدراسية', 'items' => ['مدخل إلى التخصص', 'الآلات والمعدات التكنولوجية', 'عناصر الآلات والآليات', 'علم المواد والمواد الإنشائية', 'الهيدروليكا والأنظمة الهيدروليكية', 'التصميم بمساعدة الحاسوب', 'عمليات ومعدات الإنتاج', 'تشغيل وصيانة المعدات التكنولوجية', 'أتمتة عمليات الإنتاج', 'التدريب الصناعي ومشروع التخرج']],
                    ['key' => 'prepared_specialists', 'title' => 'البرامج والتخصصات', 'items' => ['60720400 - الآلات والمعدات التكنولوجية', '60721800 - هندسة الإنتاج']],
                    ['key' => 'research', 'title' => 'الأعمال البحثية', 'items' => ['يجري القسم أبحاثًا في تطوير الآلات التكنولوجية، وموثوقية المعدات، وكفاءة الإنتاج، ومعالجة المواد، وتصميم الآلات، والأتمتة الصناعية.']],
                    ['key' => 'cooperation', 'title' => 'التعاون الدولي', 'items' => ['يطور القسم التعاون مع المؤسسات الصناعية والمنظمات البحثية والجامعات الشريكة لتعزيز التدريب العملي والبحث التطبيقي.']],
                    ['key' => 'department_structure', 'title' => 'هيكل القسم', 'items' => ['رئيس القسم: أورينوف أويغون عبد اللهيفيتش', 'ساعات الاستقبال: الاثنين-الجمعة 14:00-16:00', 'الهاتف: +998 90 744 18 22', 'البريد الإلكتروني: -']],
                ],
            ],
        ];
    }

    private function staff(): array
    {
        return [
            $this->staffMember('technological-machines-equipment-uygun-abdullayevich-orinov', 'O‘rinov Uyg‘un Abdullayevich', 'Head of Department', 'أورينوف أويغون عبد اللهيفيتش', 'رئيس القسم', 'Оринов Уйгун Абдуллаевич', 'Заведующий кафедрой'),
            $this->staffMember('technological-machines-equipment-narziyev-mirzo-sayidovich', 'Narziyev Mirzo Sayidovich', 'Doctor of Technical Sciences, Professor', 'نارزييف ميرزو سعيدوفيتش', 'دكتور في العلوم التقنية، أستاذ', 'Нарзиев Мирзо Саидович', 'Доктор технических наук, профессор'),
            $this->staffMember('technological-machines-equipment-gafurov-karim-hakimovich', 'Gafurov Karim Hakimovich', 'Doctor of Technical Sciences, Professor', 'غافوروف كريم حكيموفيتش', 'دكتور في العلوم التقنية، أستاذ', 'Гафуров Карим Хакимович', 'Доктор технических наук, профессор'),
            $this->staffMember('technological-machines-equipment-saidmurotov-oktam-azimovich', 'Saidmurotov O‘ktam Azimovich', 'PhD, Associate Professor', 'سعيدموروتوف أوكتام عظيموفيتش', 'دكتوراه، أستاذ مشارك', 'Саидмуротов Уктам Азимович', 'PhD, доцент'),
            $this->staffMember('technological-machines-equipment-hikmatov-doniyor-nematovich', 'Hikmatov Doniyor Ne’matovich', 'PhD, Associate Professor', 'حكماتوف دونيور نعمتوفيتش', 'دكتوراه، أستاذ مشارك', 'Хикматов Дониёр Неъматович', 'PhD, доцент'),
            $this->staffMember('technological-machines-equipment-kholikov-alijon-abduraupovich', 'Kholikov Alijon Abduraupovich', 'PhD, Associate Professor', 'خوليكوف عليجون عبد الرؤوفوفيتش', 'دكتوراه، أستاذ مشارك', 'Холиков Алижон Абдураупович', 'PhD, доцент'),
            $this->staffMember('technological-machines-equipment-rustamov-elyor-samiyevich', 'Rustamov Elyor Samiyevich', 'PhD', 'رستاموف إليور سامييفيتش', 'دكتوراه', 'Рустамов Элёр Самиевич', 'PhD'),
            $this->staffMember('technological-machines-equipment-ibragimov-ravshan-rustamovich', 'Ibragimov Ravshan Rustamovich', 'Assistant', 'إبراهيموف رافشان رستاموفيتش', 'مساعد', 'Ибрагимов Равшан Рустамович', 'Ассистент'),
            $this->staffMember('technological-machines-equipment-bexbutov-shkh', 'Bexbutov Sh.Kh.', 'Doctor of Technical Sciences, Professor', 'بيخبوتوف ش.خ.', 'دكتور في العلوم التقنية، أستاذ', 'Бехбутов Ш.Х.', 'Доктор технических наук, профессор'),
            $this->staffMember('technological-machines-equipment-jo-sharipov', 'Sharipov J.O.', 'PhD, Associate Professor', 'شاريبوف ج.أ.', 'دكتوراه، أستاذ مشارك', 'Шарипов Ж.О.', 'PhD, доцент'),
            $this->staffMember('technological-machines-equipment-as-saidova', 'Saidova A.S.', 'Assistant', 'سعيدوفا أ.س.', 'مساعد', 'Саидова А.С.', 'Ассистент'),
            $this->staffMember('technological-machines-equipment-asadova-sitora-sadullayevna', 'Asadova Sitora Sadullayevna', 'PhD, Associate Professor', 'أسادوفا سيتورا سعد اللهيفنا', 'دكتوراه، أستاذ مشارك', 'Асадова Ситора Садуллаевна', 'PhD, доцент'),
            $this->staffMember('technological-machines-equipment-fayziev-sirojiddin-hayat-oglu', 'Fayziev Sirojiddin Hayat oglu', 'PhD, Associate Professor', 'فايزييف سيروج الدين حيات أوغلي', 'دكتوراه، أستاذ مشارك', 'Файзиев Сирожиддин Ҳаёт угли', 'PhD, доцент'),
            $this->staffMember('technological-machines-equipment-ismaksimanov-furkat-barotovich', 'Ismaksimanov Furkat Barotovich', 'PhD, Associate Professor', 'إسماكسيمانوف فوركات باراتوفيتش', 'دكتوراه، أستاذ مشارك', 'Исмаксиманов Фуркат Баротович', 'PhD, доцент'),
            $this->staffMember('technological-machines-equipment-qurbonov-fazliddin-aminovich', 'Qurbonov Fazliddin Aminovich', 'Candidate of Technical Sciences, Associate Professor', 'قربونوف فضل الدين أمينوفيتش', 'مرشح في العلوم التقنية، أستاذ مشارك', 'Курбонов Фазлиддин Аминович', 'Кандидат технических наук, доцент'),
        ];
    }

    private function staffMember(string $slug, string $name, string $position, string $arName, string $arPosition, string $ruName, string $ruPosition): array
    {
        return [
            'slug' => $slug,
            'translations' => [
                'en' => ['name' => $name, 'position' => $position],
                'uz' => ['name' => $name, 'position' => $position],
                'ru' => ['name' => $ruName, 'position' => $ruPosition],
                'ar' => ['name' => $arName, 'position' => $arPosition],
            ],
        ];
    }
};
