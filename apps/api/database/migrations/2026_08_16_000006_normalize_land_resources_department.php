<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments') || ! Schema::hasTable('department_translations')) {
            return;
        }

        $departmentId = DB::table('departments')->where('slug', 'land-resources-management-state-land-cadastres')->value('id');
        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeSections((int) $departmentId);
            $this->normalizeStaff((int) $departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function normalizeSections(int $departmentId): void
    {
        foreach ($this->locales() as $locale) {
            $translation = DB::table('department_translations')->where('department_id', $departmentId)->where('locale', $locale)->first();
            if (! $translation) {
                continue;
            }

            $sections = json_decode((string) $translation->content_sections, true);
            if (! is_array($sections)) {
                $sections = [];
            }

            foreach ([
                'staff' => ['title' => $this->label($locale, 'staff'), 'items' => $this->staffSectionItems($locale)],
                'prepared_specialists' => ['title' => $this->label($locale, 'programs'), 'items' => [$this->programsText($locale)]],
                'subjects' => ['title' => $this->label($locale, 'subjects'), 'items' => [$this->subjectsText($locale)]],
                'research' => ['title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)],
                'cooperation' => ['title' => $this->label($locale, 'cooperation'), 'items' => $this->cooperationItems($locale)],
            ] as $key => $payload) {
                $sections = $this->replaceSection($sections, ['key' => $key, 'title' => $payload['title'], 'items' => $payload['items']]);
            }

            DB::table('department_translations')->where('id', $translation->id)->update([
                'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteSlugs = [
            'land-resources-management-state-land-cadastres-asatov-sayitkul-rakhimberdiyevich',
            'land-resources-management-state-land-cadastres-doctor-of-philosophy-of-biological-sciences-karimov',
            'land-resources-management-state-land-cadastres-erkin-kadirovich',
            'land-resources-management-state-land-cadastres-0-dr-shodiya-b-boltayeva',
            'land-resources-management-state-land-cadastres-1-farxod-m-rahmatov',
            'land-resources-management-state-land-cadastres-2-zafar-i-saidov',
        ];
        $ids = DB::table('staff_profiles')->whereIn('slug', $deleteSlugs)->pluck('id')->all();
        if ($ids !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
            DB::table('staff_profiles')->whereIn('id', $ids)->delete();
        }

        foreach ($this->staffProfiles() as $index => $profile) {
            $profileId = DB::table('staff_profiles')->where('slug', $profile['slug'])->value('id');
            if (! $profileId) {
                continue;
            }

            DB::table('staff_profiles')->where('id', $profileId)->update([
                'department_id' => $departmentId,
                'sort_order' => $index + 10,
                'is_active' => true,
                'updated_at' => now(),
            ]);

            foreach ($this->locales() as $locale) {
                $name = $profile['names'][$locale] ?? $profile['names']['en'];
                $position = $this->position($profile['position'], $locale);
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $name,
                        'position' => $position,
                        'bio' => $this->bio($name, $position, $locale),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function programsText(string $locale): string
    {
        return $this->numberedSection($this->degreeLabel($locale, 'bachelor_programs'), $this->bachelorPrograms($locale))
            ."\n".$this->numberedSection($this->degreeLabel($locale, 'master_programs'), $this->masterPrograms($locale));
    }

    private function subjectsText(string $locale): string
    {
        return $this->numberedSection($this->degreeLabel($locale, 'bachelor'), $this->bachelorSubjects($locale))
            ."\n".$this->numberedSection($this->degreeLabel($locale, 'master'), $this->masterSubjects($locale));
    }

    private function numberedSection(string $heading, array $items): string
    {
        return $heading.":\n".collect($items)->map(fn (string $item, int $index) => ($index + 1).'. '.$item)->implode("\n");
    }

    private function bachelorPrograms(string $locale): array
    {
        return match ($locale) {
            'uz' => ['60811600 - Yer kadastri va yer tuzish', '60721700 - Kadastr', '60721500 - Geodeziya va geoinformatika', '60721600 - Kartografiya va masofadan zondlash', '60810300 - Tuproqlar bonitirovkasi va yer degradatsiyasi'],
            'ru' => ['60811600 - Земельный кадастр и землеустройство', '60721700 - Кадастр', '60721500 - Геодезия и геоинформатика', '60721600 - Картография и дистанционное зондирование', '60810300 - Бонитировка почв и деградация земель'],
            'ar' => ['60811600 - السجل العقاري وإدارة الأراضي', '60721700 - الكاداستر', '60721500 - الجيوديسيا والمعلوماتية الجغرافية', '60721600 - الخرائط والاستشعار عن بعد', '60810300 - تقييم جودة التربة وتدهور الأراضي'],
            default => ['60811600 - Land Cadastre and Land Management', '60721700 - Cadastre', '60721500 - Geodesy and Geoinformatics', '60721600 - Cartography and Remote Sensing', '60810300 - Soil Quality Assessment and Land Degradation'],
        };
    }

    private function masterPrograms(string $locale): array
    {
        return match ($locale) {
            'uz' => ['70811601 - Yer resurslaridan foydalanish va boshqarish', '70721601 - Yerni masofadan zondlash va GAT texnologiyalari'],
            'ru' => ['70811601 - Использование и управление земельными ресурсами', '70721601 - Дистанционное зондирование Земли и ГИС-технологии'],
            'ar' => ['70811601 - استخدام وإدارة موارد الأراضي', '70721601 - الاستشعار عن بعد للأرض وتقنيات نظم المعلومات الجغرافية'],
            default => ['70811601 - Land Resource Use and Management', '70721601 - Remote Sensing of the Earth and GIS Technologies'],
        };
    }

    private function bachelorSubjects(string $locale): array
    {
        $en = ['Cartographic Design', 'Geodesy', 'Fundamentals of Landscape Science', 'Cartography', 'Fundamentals of State Cadastre', 'Introduction to the Specialty', 'Higher Geodesy', 'Map Science', 'Land Resource Management', 'Modern Geodetic Instruments', 'Geodetic Works in Land Management', 'Land Management Design', 'Theoretical Foundations of Land Management', 'Territory Cadastre', 'Digital Cartography', 'Data Acquisition and Integration', 'Digital Land Cadastre', 'Automated Systems in Land Management Design', 'Organization and Planning of Land Management Works', 'Regulation of Land Relations', 'Engineering Geodesy', 'Soil Science and Plant Anatomy', 'Soil Science', 'Fundamentals of Geographic Information Systems', 'Geographic Information Systems and Technologies', 'Digital Photogrammetry', 'Three-Dimensional Modeling in GIS', 'Geodatabase and Architecture'];
        return match ($locale) {
            'uz' => ['Kartografik dizayn', 'Geodeziya', 'Landshaftshunoslik asoslari', 'Kartografiya', 'Davlat kadastri asoslari', 'Mutaxassislikka kirish', 'Oliy geodeziya', 'Kartashunoslik', 'Yer resurslarini boshqarish', 'Zamonaviy geodezik asboblar', 'Yer tuzishda geodezik ishlar', 'Yer tuzishni loyihalash', 'Yer tuzishning nazariy asoslari', 'Hududlar kadastri', 'Raqamli kartografiya', 'Ma’lumotlarni olish va integratsiyalash', 'Raqamli yer kadastri', 'Yer tuzishni loyihalashda avtomatlashtirilgan tizimlar', 'Yer tuzish ishlarini tashkil qilish va rejalashtirish', 'Yer munosabatlarini tartibga solish', 'Muhandislik geodeziyasi', 'Tuproqshunoslik va o‘simlik anatomiyasi', 'Tuproqshunoslik', 'Geografik axborot tizimlari asoslari', 'Geografik axborot tizimlari va texnologiyalari', 'Raqamli fotogrammetriya', 'GISda uch o‘lchamli modellashtirish', 'Geoma’lumotlar bazasi va arxitekturasi'],
            'ru' => ['Картографический дизайн', 'Геодезия', 'Основы ландшафтоведения', 'Картография', 'Основы государственного кадастра', 'Введение в специальность', 'Высшая геодезия', 'Картоведение', 'Управление земельными ресурсами', 'Современные геодезические приборы', 'Геодезические работы в землеустройстве', 'Проектирование землеустройства', 'Теоретические основы землеустройства', 'Кадастр территорий', 'Цифровая картография', 'Получение и интеграция данных', 'Цифровой земельный кадастр', 'Автоматизированные системы в проектировании землеустройства', 'Организация и планирование землеустроительных работ', 'Регулирование земельных отношений', 'Инженерная геодезия', 'Почвоведение и анатомия растений', 'Почвоведение', 'Основы географических информационных систем', 'Географические информационные системы и технологии', 'Цифровая фотограмметрия', 'Трехмерное моделирование в ГИС', 'Геобаза данных и архитектура'],
            'ar' => ['التصميم الخرائطي', 'الجيوديسيا', 'أساسيات علم المناظر الطبيعية', 'رسم الخرائط', 'أساسيات الكاداستر الحكومي', 'مدخل إلى التخصص', 'الجيوديسيا العليا', 'علم الخرائط', 'إدارة موارد الأراضي', 'الأجهزة الجيوديسية الحديثة', 'الأعمال الجيوديسية في إدارة الأراضي', 'تصميم إدارة الأراضي', 'الأسس النظرية لإدارة الأراضي', 'كاداستر الأقاليم', 'الخرائط الرقمية', 'الحصول على البيانات ودمجها', 'الكاداستر الرقمي للأراضي', 'الأنظمة المؤتمتة في تصميم إدارة الأراضي', 'تنظيم وتخطيط أعمال إدارة الأراضي', 'تنظيم علاقات الأراضي', 'الجيوديسيا الهندسية', 'علم التربة وتشريح النبات', 'علم التربة', 'أساسيات نظم المعلومات الجغرافية', 'نظم المعلومات الجغرافية وتقنياتها', 'المسح التصويري الرقمي', 'النمذجة ثلاثية الأبعاد في GIS', 'قاعدة البيانات الجغرافية ومعماريتها'],
            default => $en,
        };
    }

    private function masterSubjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Tadqiqot metodologiyasi', 'Yer resurslaridan integrallashgan foydalanish va boshqarish', 'Hududiy rivojlanish', 'Yerdan foydalanish iqtisodiyoti', 'Yer resurslarini boshqarishning huquqiy asoslari', 'Maxsus fanlarni o‘qitish metodikasi', 'Yer uchastkalarini shakllantirish', 'Yer uchastkalarini hisobga olish va baholash', 'Ko‘chmas mulkni boshqarish'],
            'ru' => ['Методология исследования', 'Интегрированное управление землепользованием', 'Территориальное развитие', 'Экономика землепользования', 'Правовые основы управления земельными ресурсами', 'Методика преподавания специальных дисциплин', 'Формирование земельных участков', 'Учет и оценка земельных участков', 'Управление недвижимостью'],
            'ar' => ['منهجية البحث', 'الإدارة المتكاملة لاستخدام الأراضي', 'التنمية الإقليمية', 'اقتصاديات استخدام الأراضي', 'الأسس القانونية لإدارة موارد الأراضي', 'طرائق تدريس التخصصات الخاصة', 'تشكيل قطع الأراضي', 'تسجيل وتقييم قطع الأراضي', 'إدارة العقارات'],
            default => ['Research Methodology', 'Integrated Land Use Management', 'Territorial Development', 'Economics of Land Use', 'Legal Foundations of Land Resource Management', 'Methods of Teaching Special Disciplines', 'Formation of Land Plots', 'Accounting and Valuation of Land Plots', 'Real Estate Management'],
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Kafedrada 25 nafar professor-o‘qituvchi faoliyat yuritadi: 8 nafari professor va PhD, 6 nafari dotsent, 2 nafari katta o‘qituvchi va 9 nafari assistent.', 'Ilmiy salohiyat 36 foizni tashkil etadi.', 'So‘nggi 14 yil davomida kafedra professor-o‘qituvchilari tomonidan 10 ta darslik, 15 ta o‘quv qo‘llanma, 1000 dan ortiq ilmiy maqola va 56 ta uslubiy ko‘rsatma chop etilgan.', 'Kafedra jamoasi Buxoro vohasi misolida ekologik beqarorlik sharoitida qishloq xo‘jaligi yerlaridan oqilona foydalanishni tashkil etish bo‘yicha tadqiqotlar olib boradi.', 'Klaster tizimida qishloq xo‘jaligi yer turlari tarkibini optimallashtirish masalalari o‘rganiladi.', 'Qishloq xo‘jaligi yerlarida monitoring olib borishda innovatsion texnologiyalarni joriy etish bo‘yicha tadqiqotlar olib boriladi.'],
            'ru' => ['На кафедре работают 25 преподавателей: 8 профессоров и PhD, 6 доцентов, 2 старших преподавателя и 9 ассистентов.', 'Научный потенциал кафедры составляет 36%.', 'За последние 14 лет преподавателями кафедры опубликованы 10 учебников, 15 учебных пособий, более 1000 научных статей и 56 методических указаний.', 'Коллектив кафедры проводит исследования по рациональной организации использования сельскохозяйственных земель в условиях экологической нестабильности на примере Бухарского оазиса.', 'Изучаются вопросы оптимизации состава видов сельскохозяйственных земель в кластерной системе.', 'Проводятся исследования по внедрению инновационных технологий при мониторинге сельскохозяйственных земель.'],
            'ar' => ['يعمل في القسم 25 عضوا من هيئة التدريس: 8 من الأساتذة وحملة PhD، و6 أساتذة مشاركين، و2 محاضرين أول، و9 مساعدين.', 'تبلغ القدرة العلمية للقسم 36%.', 'خلال آخر 14 عاما نشر أعضاء هيئة التدريس في القسم 10 كتب دراسية، و15 دليلا تعليميا، وأكثر من 1000 مقالة علمية، و56 دليلا منهجيا.', 'يجري فريق القسم أبحاثا حول التنظيم الرشيد لاستخدام الأراضي الزراعية في ظروف عدم الاستقرار البيئي، على مثال واحة بخارى.', 'تتم دراسة تحسين تركيبة أنواع الأراضي الزراعية ضمن نظام العناقيد.', 'تجرى بحوث حول إدخال التقنيات المبتكرة في مراقبة الأراضي الزراعية.'],
            default => ['The department has 25 faculty members: 8 professors and PhD holders, 6 associate professors, 2 senior lecturers, and 9 assistants.', 'The scientific potential of the department is 36%.', 'Over the past 14 years, department faculty have published 10 textbooks, 15 teaching aids, more than 1,000 scientific articles, and 56 methodological guidelines.', 'The department conducts research on the rational organization of agricultural land use under ecological instability, using the Bukhara oasis as an example.', 'Research is carried out on optimizing the composition of agricultural land types within the cluster system.', 'Innovative technologies are being introduced for agricultural land monitoring.'],
        };
    }

    private function cooperationItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Kafedra Turkiyaning Ushak universiteti bilan hamkorlik qiladi.', '2024-yil 25-noyabrdan 6-dekabrgacha Xudoyberdiyev F.Sh., Sattorov Sh.Y., Adizov Sh.B. va magistrant Bobojonov S.O. qisqa muddatli stajirovkada ishtirok etdi.', 'Stajirovka davomida universitetlar o‘rtasida tajriba almashildi, ma’ruzalar tashkil etildi va laboratoriya jihozlari bilan tanishildi.', 'Axborot-resurs markazidan zarur ilmiy adabiyotlarning elektron nusxalari olib kelindi.', 'Tegishli fanlar bo‘yicha onlayn ma’ruzalarni tashkil etish yuzasidan Ziraat fakulteti bilan kelishuv imzolandi.'],
            'ru' => ['Кафедра сотрудничает с Университетом Ушак в Турции.', 'С 25 ноября по 6 декабря 2024 года Худойбердиев Ф.Ш., Сатторов Ш.Я., Адизов Ш.Б. и магистрант Бобожонов С.О. прошли краткосрочную стажировку.', 'В ходе стажировки был организован обмен опытом между университетами, проведены лекции и изучено лабораторное оборудование.', 'Из информационно-ресурсного центра были получены электронные копии необходимой научной литературы.', 'С факультетом сельского хозяйства подписано соглашение об организации онлайн-лекций по профильным дисциплинам.'],
            'ar' => ['يتعاون القسم مع جامعة أوشاك في تركيا.', 'في الفترة من 25 نوفمبر إلى 6 ديسمبر 2024 شارك خدويردييف F.Sh.، ساتتوروف Sh.Y.، أديزوف Sh.B. وطالب الماجستير بوبوجونوف S.O. في تدريب قصير الأجل.', 'خلال التدريب تم تبادل الخبرات بين الجامعات، وتنظيم محاضرات، والتعرف على معدات المختبرات.', 'تم الحصول على نسخ إلكترونية من الأدبيات العلمية اللازمة من مركز مصادر المعلومات.', 'تم توقيع اتفاق مع كلية الزراعة لتنظيم محاضرات عبر الإنترنت في المواد ذات الصلة.'],
            default => ['The department cooperates with Uşak University in Turkey.', 'From November 25 to December 6, 2024, Khudoyberdiev F.Sh., Sattorov Sh.Y., Adizov Sh.B., and master’s student Bobojonov S.O. completed a short-term internship.', 'During the internship, experience was exchanged between universities, lectures were organized, and laboratory equipment was studied.', 'Electronic copies of required scientific literature were obtained from the Information Resource Center.', 'An agreement was signed with the Faculty of Agriculture to organize online lectures in relevant subjects.'],
        };
    }

    private function staffProfiles(): array
    {
        return [
            ['slug' => 'land-resources-management-state-land-cadastres-asatov-sayitqul-rahimberdiyevich', 'names' => ['en' => 'Asatov Sayitqul Rahimberdiyevich', 'uz' => 'Asatov Sayitqul Rahimberdiyevich', 'ru' => 'Асатов Сайиткул Рахимбердиевич', 'ar' => 'أساتوف ساييتقول رحيمبيردييفيتش'], 'position' => 'Head of Department'],
            ['slug' => 'land-resources-management-state-land-cadastres-hamidov-fayzullo-ramazonovich', 'names' => ['en' => 'Hamidov Fayzullo Ramazonovich', 'uz' => 'Hamidov Fayzullo Ramazonovich', 'ru' => 'Хамидов Файзулло Рамазонович', 'ar' => 'حميدوف فيض الله رمضانوفِتش'], 'position' => 'PhD in Technical Sciences, Professor'],
            ['slug' => 'land-resources-management-state-land-cadastres-islamov-ismail', 'names' => ['en' => 'Islamov Ismail', 'uz' => 'Islamov Ismail', 'ru' => 'Исламов Исмаил', 'ar' => 'إسلاموف إسماعيل'], 'position' => 'Doctor of Agricultural Sciences, Professor'],
            ['slug' => 'land-resources-management-state-land-cadastres-khudoyberdiev-feruz-shamshodovich', 'names' => ['en' => 'Khudoyberdiev Feruz Shamshodovich', 'uz' => 'Xudoyberdiyev Feruz Shamshodovich', 'ru' => 'Худойбердиев Феруз Шамшодович', 'ar' => 'خدويبيردييف فيروز شمشادوفيتش'], 'position' => 'PhD in Technical Sciences, Professor'],
            ['slug' => 'land-resources-management-state-land-cadastres-sattorov-shahzod-yarashovich', 'names' => ['en' => 'Sattorov Shahzod Yarashovich', 'uz' => 'Sattorov Shahzod Yarashovich', 'ru' => 'Сатторов Шахзод Ярашович', 'ar' => 'ساتتوروف شاهزود ياراشوفيتش'], 'position' => 'PhD in Technical Sciences, Associate Professor'],
            ['slug' => 'land-resources-management-state-land-cadastres-adizov-shukhrat-bafoyevich', 'names' => ['en' => 'Adizov Shukhrat Bafoyevich', 'uz' => 'Adizov Shukhrat Bafoyevich', 'ru' => 'Адизов Шухрат Бафоевич', 'ar' => 'أديزوف شوخرات بافوييفيتش'], 'position' => 'PhD in Agricultural Sciences, Associate Professor'],
            ['slug' => 'land-resources-management-state-land-cadastres-pirimov-jonibek-jumamurodovich', 'names' => ['en' => 'Pirimov Jonibek Jumamurodovich', 'uz' => 'Pirimov Jonibek Jumamurodovich', 'ru' => 'Пиримов Жонибек Жумамуродович', 'ar' => 'بيريموف جونيبيك جومامورودوفيتش'], 'position' => 'PhD in Technical Sciences, Associate Professor'],
            ['slug' => 'land-resources-management-state-land-cadastres-hamroyev-salahiddin-atoyevich', 'names' => ['en' => 'Hamroyev Salahiddin Atoyevich', 'uz' => 'Hamroyev Salahiddin Atoyevich', 'ru' => 'Хамроев Салахиддин Атоевич', 'ar' => 'حمرويف صلاح الدين أتوييفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'land-resources-management-state-land-cadastres-abduloyev-ashraf-muzafarovich', 'names' => ['en' => 'Abduloyev Ashraf Muzafarovich', 'uz' => 'Abduloyev Ashraf Muzafarovich', 'ru' => 'Абдулоев Ашраф Музафарович', 'ar' => 'عبدولوييف أشرف مظفروفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'land-resources-management-state-land-cadastres-ahmadov-behzod-obid-oglu', 'names' => ['en' => 'Ahmadov Behzod Obid oglu', 'uz' => 'Ahmadov Behzod Obid o‘g‘li', 'ru' => 'Ахмадов Бехзод Обид угли', 'ar' => 'أحمدوف بهزود عبيد أوغلي'], 'position' => 'Senior Lecturer'],
            ['slug' => 'land-resources-management-state-land-cadastres-izatov-elmir-najmidinovich', 'names' => ['en' => 'Izatov Elmir Najmidinovich', 'uz' => 'Izatov Elmir Najmidinovich', 'ru' => 'Изатов Эльмир Нажмиддинович', 'ar' => 'إيزاتوف إلمير نجم الدينوفيتش'], 'position' => 'Assistant'],
            ['slug' => 'land-resources-management-state-land-cadastres-egamova-dilchehra-adizovna', 'names' => ['en' => 'Egamova Dilchehra Adizovna', 'uz' => 'Egamova Dilchehra Adizovna', 'ru' => 'Эгамова Дилчехра Адизовна', 'ar' => 'إيغاموفا ديلتشهرا أديزوفنا'], 'position' => 'Assistant'],
            ['slug' => 'land-resources-management-state-land-cadastres-nuriddinov-otabek-xurramovich', 'names' => ['en' => 'Nuriddinov Otabek Xurramovich', 'uz' => 'Nuriddinov Otabek Xurramovich', 'ru' => 'Нуриддинов Отабек Хуррамович', 'ar' => 'نوريدينوف أوتابيك خوراموفيتش'], 'position' => 'Assistant'],
            ['slug' => 'land-resources-management-state-land-cadastres-oltinov-sobir-hayot-ogli', 'names' => ['en' => "Oltinov Sobir Hayot o'g'li", 'uz' => 'Oltinov Sobir Hayot o‘g‘li', 'ru' => 'Олтинов Собир Хаёт угли', 'ar' => 'أولتينوف صابر حيات أوغلي'], 'position' => 'Assistant'],
            ['slug' => 'land-resources-management-state-land-cadastres-rajabova-gullola-islomovna', 'names' => ['en' => 'Rajabova Gullola Islomovna', 'uz' => 'Rajabova Gullola Islomovna', 'ru' => 'Ражабова Гуллола Исломовна', 'ar' => 'رجبوفا غولولا إسلاموفنا'], 'position' => 'Assistant'],
        ];
    }

    private function staffSectionItems(string $locale): array
    {
        return collect($this->staffProfiles())->map(function (array $profile) use ($locale) {
            $name = $profile['names'][$locale] ?? $profile['names']['en'];
            return $name."\n".$this->position($profile['position'], $locale);
        })->all();
    }

    private function position(string $position, string $locale): string
    {
        $map = [
            'Head of Department' => ['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'PhD in Technical Sciences, Professor' => ['en' => 'PhD in Technical Sciences, Professor', 'uz' => 'Texnika fanlari bo‘yicha PhD, professor', 'ru' => 'PhD по техническим наукам, профессор', 'ar' => 'دكتوراه في العلوم التقنية، أستاذ'],
            'Doctor of Agricultural Sciences, Professor' => ['en' => 'Doctor of Agricultural Sciences, Professor', 'uz' => 'Qishloq xo‘jaligi fanlari doktori, professor', 'ru' => 'Доктор сельскохозяйственных наук, профессор', 'ar' => 'دكتور في العلوم الزراعية، أستاذ'],
            'PhD in Technical Sciences, Associate Professor' => ['en' => 'PhD in Technical Sciences, Associate Professor', 'uz' => 'Texnika fanlari bo‘yicha PhD, dotsent', 'ru' => 'PhD по техническим наукам, доцент', 'ar' => 'دكتوراه في العلوم التقنية، أستاذ مشارك'],
            'PhD in Agricultural Sciences, Associate Professor' => ['en' => 'PhD in Agricultural Sciences, Associate Professor', 'uz' => 'Qishloq xo‘jaligi fanlari bo‘yicha PhD, dotsent', 'ru' => 'PhD по сельскохозяйственным наукам, доцент', 'ar' => 'دكتوراه في العلوم الزراعية، أستاذ مشارك'],
            'Senior Lecturer' => ['en' => 'Senior Lecturer', 'uz' => 'Katta o‘qituvchi', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'Assistant' => ['en' => 'Assistant', 'uz' => 'Assistent', 'ru' => 'Ассистент', 'ar' => 'مساعد'],
        ];
        return $map[$position][$locale] ?? $position;
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Yerdan foydalanish va davlat kadastrlari kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре землепользования и государственных кадастров в должности «{$position}».",
            'ar' => "{$name} يعمل/تعمل في قسم استخدام الأراضي والكاداستر الحكومي بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Land Use and State Cadastre.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'programs' => ['en' => 'Prepared Specialists', 'uz' => 'Tayyorlanadigan mutaxassislar', 'ru' => 'Подготавливаемые специалисты', 'ar' => 'التخصصات التي يعدها القسم'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'Kafedrada o‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد التي تدرس في القسم'],
            'research' => ['en' => 'Research Work', 'uz' => 'Ilmiy-tadqiqot ishlari', 'ru' => 'Научно-исследовательские работы', 'ar' => 'الأعمال البحثية'],
            'cooperation' => ['en' => 'International Cooperation', 'uz' => 'Xalqaro hamkorlik', 'ru' => 'Международное сотрудничество', 'ar' => 'التعاون الدولي'],
        ];
        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function degreeLabel(string $locale, string $degree): string
    {
        return match ("{$locale}:{$degree}") {
            'uz:bachelor_programs' => 'Bakalavriat dasturlari',
            'uz:master_programs' => 'Magistratura dasturlari',
            'uz:bachelor' => 'Bakalavriat fanlari',
            'uz:master' => 'Magistratura fanlari',
            'ru:bachelor_programs' => 'Программы бакалавриата',
            'ru:master_programs' => 'Программы магистратуры',
            'ru:bachelor' => 'Дисциплины бакалавриата',
            'ru:master' => 'Дисциплины магистратуры',
            'ar:bachelor_programs' => 'برامج البكالوريوس',
            'ar:master_programs' => 'برامج الماجستير',
            'ar:bachelor' => 'مواد البكالوريوس',
            'ar:master' => 'مواد الماجستير',
            'en:master_programs' => 'Master programs',
            'en:master' => 'Master subjects',
            'en:bachelor_programs' => 'Bachelor programs',
            default => 'Bachelor subjects',
        };
    }

    private function replaceSection(array $sections, array $replacement): array
    {
        foreach ($sections as $index => $section) {
            if (($section['key'] ?? null) === $replacement['key']) {
                $sections[$index] = $replacement;
                return $sections;
            }
        }
        $sections[] = $replacement;
        return $sections;
    }

    private function locales(): array
    {
        if (! Schema::hasTable('locales')) {
            return ['en', 'uz', 'ru', 'ar'];
        }
        return DB::table('locales')->where('is_active', true)->orderBy('sort_order')->pluck('code')->filter()->values()->all() ?: ['en', 'uz', 'ru', 'ar'];
    }
};
