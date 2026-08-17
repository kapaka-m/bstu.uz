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

        $departmentId = DB::table('departments')->where('slug', 'industrial-ecology-hydrogeology')->value('id');
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
            $translation = DB::table('department_translations')
                ->where('department_id', $departmentId)
                ->where('locale', $locale)
                ->first();

            if (! $translation) {
                continue;
            }

            $sections = json_decode((string) $translation->content_sections, true);
            if (! is_array($sections)) {
                $sections = [];
            }

            foreach ([
                'staff' => ['title' => $this->label($locale, 'staff'), 'items' => $this->staffSectionItems($locale)],
                'subjects' => ['title' => $this->label($locale, 'subjects'), 'items' => [$this->subjectsText($locale)]],
                'research' => ['title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)],
                'cooperation' => ['title' => $this->label($locale, 'cooperation'), 'items' => $this->cooperationItems($locale)],
                'plans' => ['title' => $this->label($locale, 'plans'), 'items' => $this->plansItems($locale)],
            ] as $key => $payload) {
                $sections = $this->replaceSection($sections, [
                    'key' => $key,
                    'title' => $payload['title'],
                    'items' => $payload['items'],
                ]);
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
            'industrial-ecology-hydrogeology-0-prof-mavlon-m-mahmudov',
            'industrial-ecology-hydrogeology-1-dr-zebo-o-sharipova',
            'industrial-ecology-hydrogeology-2-kamol-j-tursunov',
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

    private function subjectsText(string $locale): string
    {
        return $this->numberedSection($this->degreeLabel($locale, 'bachelor'), $this->bachelorSubjects($locale));
    }

    private function numberedSection(string $heading, array $items): string
    {
        return $heading.":\n".collect($items)->map(fn (string $item, int $index) => ($index + 1).'. '.$item)->implode("\n");
    }

    private function bachelorSubjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Aholi bandligini monitoring qilish', 'Atrof-muhitga ta’sirni baholash', 'Atrof-muhitni muhofaza qilish va yashil rivojlanish', 'Atrof-muhit muhofazasi', 'Ekologik resurslar iqtisodiyoti', 'Barqaror rivojlanish asoslari', 'Bioekologiya', 'Biogeografiya', 'Biologik resurslardan oqilona foydalanish', 'Biologiya va mikrobiologiya', 'Bitiruv oldi amaliyoti', 'Chiqindilarni boshqarish', 'Ekologik audit', 'Ekologik ekspertiza', 'Ekologik jarayonlarni modellashtirish', 'Ekologik menejment', 'Ekologik monitoring', 'Ekologik ta’lim va tarbiya', 'Ekologik xavflar', 'Ekologiya', 'Ekologiya asoslari', 'Ekologik huquq', 'Ekologiya va atrof-muhit muhofazasi', 'Ekologiya va hayot faoliyati xavfsizligi', 'Ekspluatatsion gidrometriya', 'Elektr xavfsizligi va elektr qurilmalaridan foydalanish qoidalari', 'Ergonomika asoslari', 'Favqulodda vaziyatlarda xavfsizlik', 'Fuqaro muhofazasi', 'GIS va gidrologiya', 'Geoekologik tadqiqot usullari', 'Geografik axborot tizimlari asoslari', 'Geologiya va geomorfologiya', 'Geologiya va gidrogeologiya', 'Gidrogeologik-meliorativ kuzatuvlar va kadastr', 'Gidrografiya', 'Gidrokimyo', 'Gidrologik prognozlash', 'Gidrologik-meliorativ prognozlash', 'Gidrologik tadqiqotlar tarixi', 'Gidrologiya, gidrogeologiya va muhandislik geologiyasi', 'Gidrologiyaga kirish', 'Gidrometriya', 'Hayot faoliyati xavfsizligi', 'Hayot faoliyati xavfsizligi va ekologiya', 'Himoya vositalari va qurilmalari', 'Ilmiy-tadqiqot ishlari va MDT SEG', 'Ilmiy-pedagogik ish SEG'],
            'ru' => ['Мониторинг занятости населения', 'Оценка воздействия на окружающую среду', 'Охрана окружающей среды и зеленое развитие', 'Охрана окружающей среды', 'Экономика экологических ресурсов', 'Основы устойчивого развития', 'Биоэкология', 'Биогеография', 'Рациональное использование биологических ресурсов', 'Биология и микробиология', 'Преддипломная практика', 'Управление отходами', 'Экологический аудит', 'Экологическая экспертиза', 'Моделирование экологических процессов', 'Экологический менеджмент', 'Экологический мониторинг', 'Экологическое образование и воспитание', 'Экологические риски', 'Экология', 'Основы экологии', 'Экологическое право', 'Экология и охрана окружающей среды', 'Экология и безопасность жизнедеятельности', 'Эксплуатационная гидрометрия', 'Электробезопасность и правила эксплуатации электроустановок', 'Основы эргономики', 'Безопасность в чрезвычайных ситуациях', 'Гражданская защита', 'ГИС и гидрология', 'Методы геоэкологических исследований', 'Основы географических информационных систем', 'Геология и геоморфология', 'Геология и гидрогеология', 'Гидрогеолого-мелиоративные наблюдения и кадастр', 'Гидрография', 'Гидрохимия', 'Гидрологическое прогнозирование', 'Гидролого-мелиоративное прогнозирование', 'История гидрологических исследований', 'Гидрология, гидрогеология и инженерная геология', 'Введение в гидрологию', 'Гидрометрия', 'Безопасность жизнедеятельности', 'Безопасность жизнедеятельности и экология', 'Средства и устройства защиты', 'Научно-исследовательская работа и MDT SEG', 'Научно-педагогическая работа SEG'],
            'ar' => ['مراقبة توظيف السكان', 'تقييم الأثر البيئي', 'حماية البيئة والتنمية الخضراء', 'حماية البيئة', 'اقتصاديات الموارد البيئية', 'أساسيات التنمية المستدامة', 'الإيكولوجيا الحيوية', 'الجغرافيا الحيوية', 'الاستخدام الرشيد للموارد البيولوجية', 'الأحياء وعلم الأحياء الدقيقة', 'التدريب قبل التخرج', 'إدارة النفايات', 'التدقيق البيئي', 'الخبرة البيئية', 'نمذجة العمليات البيئية', 'الإدارة البيئية', 'الرصد البيئي', 'التعليم والتربية البيئية', 'المخاطر البيئية', 'علم البيئة', 'أساسيات علم البيئة', 'القانون البيئي', 'علم البيئة وحماية البيئة', 'علم البيئة وسلامة الحياة', 'الهيدرومترية التشغيلية', 'السلامة الكهربائية وقواعد تشغيل التركيبات الكهربائية', 'أساسيات بيئة العمل', 'السلامة في حالات الطوارئ', 'الحماية المدنية', 'نظم المعلومات الجغرافية والهيدرولوجيا', 'طرق البحث الجيوبيئي', 'أساسيات نظم المعلومات الجغرافية', 'الجيولوجيا والجيومورفولوجيا', 'الجيولوجيا والهيدروجيولوجيا', 'الملاحظات الهيدروجيولوجية والاستصلاحية والكاداستر', 'الهيدروغرافيا', 'الكيمياء المائية', 'التنبؤ الهيدرولوجي', 'التنبؤ الهيدرولوجي والاستصلاحي', 'تاريخ البحوث الهيدرولوجية', 'الهيدرولوجيا والهيدروجيولوجيا والجيولوجيا الهندسية', 'مدخل إلى الهيدرولوجيا', 'الهيدرومترية', 'سلامة الحياة', 'سلامة الحياة والبيئة', 'وسائل وأجهزة الحماية', 'الأعمال البحثية العلمية وMDT SEG', 'العمل العلمي والتربوي SEG'],
            default => ['Population Employment Monitoring', 'Environmental Impact Assessment', 'Environmental Protection and Green Development', 'Environmental Protection', 'Environmental Resource Economics', 'Fundamentals of Sustainable Development', 'Bioecology', 'Biogeography', 'Rational Use of Biological Resources', 'Biology and Microbiology', 'Pre-Graduation Internship', 'Waste Management', 'Environmental Audit', 'Environmental Expertise', 'Modeling of Environmental Processes', 'Environmental Management', 'Environmental Monitoring', 'Environmental Education and Upbringing', 'Environmental Risks', 'Ecology', 'Fundamentals of Ecology', 'Environmental Law', 'Ecology and Environmental Protection', 'Ecology and Life Safety', 'Operational Hydrometry', 'Electrical Safety and Rules for Operation of Electrical Installations', 'Fundamentals of Ergonomics', 'Safety in Emergency Situations', 'Civil Protection', 'GIS and Hydrology', 'Methods of Geoecological Research', 'Fundamentals of Geographic Information Systems', 'Geology and Geomorphology', 'Geology and Hydrogeology', 'Hydrogeological Reclamation Observations and Cadastre', 'Hydrography', 'Hydrochemistry', 'Hydrological Forecasting', 'Hydrological Reclamation Forecasting', 'History of Hydrological Research', 'Hydrology, Hydrogeology and Engineering Geology', 'Introduction to Hydrology', 'Hydrometry', 'Life Safety', 'Life Safety and Ecology', 'Protective Equipment and Devices', 'Scientific Research Work and MDT SEG', 'Scientific and Pedagogical Work SEG'],
        };
    }

    private function researchItems(string $locale): array
    {
        $items = [
            ['en' => 'Tursunova N.N. Methods for eliminating accidents and fires. Scientific-practical conference materials on reforms in the State Fire Safety Service, Tashkent, 2018, pp. 143-146.', 'uz' => 'Tursunova N.N. Avariya va yong‘inlarni bartaraf etish usullari. Davlat yong‘in xavfsizligi xizmatidagi islohotlar bo‘yicha ilmiy-amaliy anjuman materiallari, Toshkent, 2018, 143-146-betlar.', 'ru' => 'Турсунова Н.Н. Методы ликвидации аварий и пожаров. Материалы научно-практической конференции о реформах в Государственной службе пожарной безопасности, Ташкент, 2018, с. 143-146.', 'ar' => 'تورسونوفا N.N. طرق إزالة آثار الحوادث والحرائق. مواد مؤتمر علمي تطبيقي حول إصلاحات خدمة السلامة من الحرائق، طشقند، 2018، ص 143-146.'],
            ['en' => 'Tursunova N.N. The use of bread in the daily human diet. Food Production Engineering and Technology, Mogilev, 2018, Vol. 1, pp. 214-215.', 'uz' => 'Tursunova N.N. Nonning inson kundalik ratsionidagi o‘rni. Oziq-ovqat ishlab chiqarish texnikasi va texnologiyasi, Mogilyov, 2018, 1-jild, 214-215-betlar.', 'ru' => 'Турсунова Н.Н. Использование хлеба в ежедневном рационе человека. Техника и технология пищевых производств, Могилев, 2018, т. 1, с. 214-215.', 'ar' => 'تورسونوفا N.N. استخدام الخبز في النظام الغذائي اليومي للإنسان. هندسة وتقنية الصناعات الغذائية، موغيليف، 2018، المجلد 1، ص 214-215.'],
            ['en' => 'Tursunova N.N. The importance of properly organizing nutrition for young athletes. Food Production Engineering and Technology, Mogilev, 2018, Vol. 1, pp. 324-325.', 'uz' => 'Tursunova N.N. Yosh sportchilar ovqatlanishini to‘g‘ri tashkil etishning ahamiyati. Oziq-ovqat ishlab chiqarish texnikasi va texnologiyasi, Mogilyov, 2018, 1-jild, 324-325-betlar.', 'ru' => 'Турсунова Н.Н. Значение правильной организации питания юных спортсменов. Техника и технология пищевых производств, Могилев, 2018, т. 1, с. 324-325.', 'ar' => 'تورسونوفا N.N. أهمية التنظيم الصحيح لتغذية الرياضيين الشباب. هندسة وتقنية الصناعات الغذائية، موغيليف، 2018، المجلد 1، ص 324-325.'],
            ['en' => 'Tursunova N.N. Adaptation of students to extreme situations in nature. Food Production Engineering and Technology, Mogilev, 2018, Vol. 2, pp. 415-416.', 'uz' => 'Tursunova N.N. O‘quvchilarning tabiatdagi ekstremal vaziyatlarga moslashuvi. Oziq-ovqat ishlab chiqarish texnikasi va texnologiyasi, Mogilyov, 2018, 2-jild, 415-416-betlar.', 'ru' => 'Турсунова Н.Н. Адаптация учащихся к экстремальным ситуациям в природе. Техника и технология пищевых производств, Могилев, 2018, т. 2, с. 415-416.', 'ar' => 'تورسونوفا N.N. تكيف الطلاب مع المواقف الطبيعية القاسية. هندسة وتقنية الصناعات الغذائية، موغيليف، 2018، المجلد 2، ص 415-416.'],
            ['en' => 'Tursunova N.N. Wastewater treatment at food industry enterprises. Food Production Engineering and Technology, Mogilev, 2018, Vol. 2, pp. 423-424.', 'uz' => 'Tursunova N.N. Oziq-ovqat sanoati korxonalari oqova suvlarini tozalash. Oziq-ovqat ishlab chiqarish texnikasi va texnologiyasi, Mogilyov, 2018, 2-jild, 423-424-betlar.', 'ru' => 'Турсунова Н.Н. Очистка сточных вод предприятий пищевой промышленности. Техника и технология пищевых производств, Могилев, 2018, т. 2, с. 423-424.', 'ar' => 'تورسونوفا N.N. معالجة مياه الصرف في مؤسسات الصناعات الغذائية. هندسة وتقنية الصناعات الغذائية، موغيليف، 2018، المجلد 2، ص 423-424.'],
            ['en' => 'Tursunova N.N. Air pollution as a threat to environmental safety in Uzbekistan. Food Production Engineering and Technology, Mogilev, 2018, Vol. 2, pp. 425-426.', 'uz' => 'Tursunova N.N. Atmosfera havosining ifloslanishi - O‘zbekistonda ekologik xavfsizlikka tahdid. Oziq-ovqat ishlab chiqarish texnikasi va texnologiyasi, Mogilyov, 2018, 2-jild, 425-426-betlar.', 'ru' => 'Турсунова Н.Н. Загрязнение воздушного пространства как угроза экологической безопасности в Узбекистане. Техника и технология пищевых производств, Могилев, 2018, т. 2, с. 425-426.', 'ar' => 'تورسونوفا N.N. تلوث الهواء كتهديد للأمن البيئي في أوزبكستان. هندسة وتقنية الصناعات الغذائية، موغيليف، 2018، المجلد 2، ص 425-426.'],
            ['en' => 'Tursunova N.N. The disappearance of the Aral Sea as an ecological problem of Uzbekistan. Food Production Engineering and Technology, Mogilev, 2018, Vol. 2, pp. 427-428.', 'uz' => 'Tursunova N.N. Orol dengizining yo‘qolishi - O‘zbekistonning ekologik muammosi. Oziq-ovqat ishlab chiqarish texnikasi va texnologiyasi, Mogilyov, 2018, 2-jild, 427-428-betlar.', 'ru' => 'Турсунова Н.Н. Проблема исчезновения Аральского моря как экологическая проблема Узбекистана. Техника и технология пищевых производств, Могилев, 2018, т. 2, с. 427-428.', 'ar' => 'تورسونوفا N.N. اختفاء بحر آرال بوصفه مشكلة بيئية في أوزبكستان. هندسة وتقنية الصناعات الغذائية، موغيليف، 2018، المجلد 2، ص 427-428.'],
            ['en' => 'Tursunova N.N. The role of electronic interactive boards in the educational process. Woman and Time republican conference, 2018, pp. 173-175.', 'uz' => 'Tursunova N.N. Ta’lim jarayonida elektron interaktiv doskaning ahamiyati. Ayol va zamon respublika anjumani, 2018, 173-175-betlar.', 'ru' => 'Турсунова Н.Н. Значение электронной интерактивной доски в образовательном процессе. Республиканская конференция «Женщина и время», 2018, с. 173-175.', 'ar' => 'تورسونوفا N.N. دور السبورة التفاعلية الإلكترونية في العملية التعليمية. مؤتمر المرأة والزمن الجمهوري، 2018، ص 173-175.'],
            ['en' => 'Tursunova N.N. The integrated nature of innovations in the system of additional professional education. Scientist of the 21st Century, 2018, pp. 45-46.', 'uz' => 'Tursunova N.N. Qo‘shimcha kasbiy ta’lim tizimida innovatsiyalarning kompleks xususiyati. XXI asr olimi, 2018, 45-46-betlar.', 'ru' => 'Турсунова Н.Н. Комплексный характер инноваций в системе дополнительного профессионального образования. Ученый XXI века, 2018, с. 45-46.', 'ar' => 'تورسونوفا N.N. الطبيعة المتكاملة للابتكارات في نظام التعليم المهني الإضافي. عالم القرن الحادي والعشرين، 2018، ص 45-46.'],
            ['en' => 'Tursunova N.N. Formation of ecological education among youth. The Role of Science and Education in Solving Environmental Problems, 2018, pp. 48-49.', 'uz' => 'Tursunova N.N. Yoshlarda ekologik tarbiyani shakllantirish. Ekologik muammolarni hal etishda fan va ta’limning o‘rni, 2018, 48-49-betlar.', 'ru' => 'Турсунова Н.Н. Формирование экологического воспитания у молодежи. Роль науки и образования в решении экологических проблем, 2018, с. 48-49.', 'ar' => 'تورسونوفا N.N. تكوين التربية البيئية لدى الشباب. دور العلم والتعليم في حل المشكلات البيئية، 2018، ص 48-49.'],
            ['en' => 'Tursunova N.N. Protection of atmospheric air from motor vehicle emissions. The Role of Science and Education in Solving Environmental Problems, 2018, pp. 94-95.', 'uz' => 'Tursunova N.N. Atmosfera havosini avtotransport chiqindilaridan muhofaza qilish. Ekologik muammolarni hal etishda fan va ta’limning o‘rni, 2018, 94-95-betlar.', 'ru' => 'Турсунова Н.Н. Охрана атмосферного воздуха от выбросов автотранспорта. Роль науки и образования в решении экологических проблем, 2018, с. 94-95.', 'ar' => 'تورسونوفا N.N. حماية الهواء الجوي من انبعاثات المركبات. دور العلم والتعليم في حل المشكلات البيئية، 2018، ص 94-95.'],
            ['en' => 'Tursunova N.N. Electrical equipment for fire- and explosion-hazardous premises and outdoor installations. The Role of Science and Education in Solving Environmental Problems, 2018, pp. 96-98.', 'uz' => 'Tursunova N.N. Yong‘in va portlash xavfi mavjud binolar hamda tashqi qurilmalar elektr jihozlari. Ekologik muammolarni hal etishda fan va ta’limning o‘rni, 2018, 96-98-betlar.', 'ru' => 'Турсунова Н.Н. Электрооборудование пожаро- и взрывоопасных помещений и наружных установок. Роль науки и образования в решении экологических проблем, 2018, с. 96-98.', 'ar' => 'تورسونوفا N.N. المعدات الكهربائية للأماكن والمنشآت الخارجية المعرضة للحريق والانفجار. دور العلم والتعليم في حل المشكلات البيئية، 2018، ص 96-98.'],
            ['en' => 'Tursunova N.N. Liability for violations of environmental legislation. Ecological Culture: Problems and Solutions, Bukhara, 2025, pp. 176-178.', 'uz' => 'Tursunova N.N. Ekologik qonunchilikni buzganlik uchun javobgarlik. Ekologik madaniyat: muammo va yechimlar, Buxoro, 2025, 176-178-betlar.', 'ru' => 'Турсунова Н.Н. Ответственность за нарушение экологического законодательства. Экологическая культура: проблемы и решения, Бухара, 2025, с. 176-178.', 'ar' => 'تورسونوفا N.N. المسؤولية عن انتهاك التشريعات البيئية. الثقافة البيئية: المشكلات والحلول، بخارى، 2025، ص 176-178.'],
            ['en' => 'Tursunova N.N. Ecological innovations and the role of ecological education in general education. Ecological Culture: Problems and Solutions, Bukhara, 2025, pp. 50-53.', 'uz' => 'Tursunova N.N. Ekologik innovatsiyalar va ekologik tarbiyaning umumiy ta’lim tizimidagi o‘rni. Ekologik madaniyat: muammo va yechimlar, Buxoro, 2025, 50-53-betlar.', 'ru' => 'Турсунова Н.Н. Экологические инновации и роль экологического воспитания в системе общего образования. Экологическая культура: проблемы и решения, Бухара, 2025, с. 50-53.', 'ar' => 'تورسونوفا N.N. الابتكارات البيئية ودور التربية البيئية في التعليم العام. الثقافة البيئية: المشكلات والحلول، بخارى، 2025، ص 50-53.'],
            ['en' => 'Tursunova N.N. The role of ecological education in the general education system. Ecological Culture: Problems and Solutions, Bukhara, 2025, pp. 15-17.', 'uz' => 'Tursunova N.N. Ekologik tarbiyaning umumiy ta’lim tizimidagi o‘rni. Ekologik madaniyat: muammo va yechimlar, Buxoro, 2025, 15-17-betlar.', 'ru' => 'Турсунова Н.Н. Роль экологического воспитания в системе общего образования. Экологическая культура: проблемы и решения, Бухара, 2025, с. 15-17.', 'ar' => 'تورسونوفا N.N. دور التربية البيئية في نظام التعليم العام. الثقافة البيئية: المشكلات والحلول، بخارى، 2025، ص 15-17.'],
            ['en' => 'Tursunova N.N. Structure of the food quality and safety department. Development of Science and Technology journal, 2025, No. 2, pp. 230-233.', 'uz' => 'Tursunova N.N. Oziq-ovqat sifati va xavfsizligi bo‘limining tuzilmasi. Fan va texnologiyalar taraqqiyoti jurnali, 2025, №2, 230-233-betlar.', 'ru' => 'Турсунова Н.Н. Структура отдела качества и безопасности пищевых продуктов. Журнал «Развитие науки и технологий», 2025, №2, с. 230-233.', 'ar' => 'تورسونوفا N.N. هيكل قسم جودة وسلامة الأغذية. مجلة تطور العلوم والتقنيات، 2025، العدد 2، ص 230-233.'],
            ['en' => 'Tursunova N.N. Employer liability for harm to employee health when working conditions are violated. Education News: Research in the 21st Century, Moscow, 2025, pp. 228-232.', 'uz' => 'Tursunova N.N. Mehnat sharoitlari buzilganda xodim sog‘lig‘iga yetkazilgan zarar uchun ish beruvchining javobgarligi. Ta’lim yangiliklari: XXI asr tadqiqotlari, Moskva, 2025, 228-232-betlar.', 'ru' => 'Турсунова Н.Н. Ответственность работодателя за вред здоровью работника при нарушении условий труда. Новости образования: исследование в XXI веке, Москва, 2025, с. 228-232.', 'ar' => 'تورسونوفا N.N. مسؤولية صاحب العمل عن الضرر الصحي للعامل عند انتهاك ظروف العمل. أخبار التعليم: بحوث في القرن الحادي والعشرين، موسكو، 2025، ص 228-232.'],
            ['en' => 'Tursunova N.N. Soil reserves and wind erosion. Colloid Chemistry: Innovations and Solutions for Chemical Technology, Ecology and Industry, 2025, pp. 936-939.', 'uz' => 'Tursunova N.N. Tuproq zaxiralari va ularning shamol ta’sirida eroziyaga uchrashi. Kolloid kimyo: kimyoviy texnologiya, ekologiya va sanoat uchun innovatsiyalar va yechimlar, 2025, 936-939-betlar.', 'ru' => 'Турсунова Н.Н. Почвенные ресурсы и их ветровая эрозия. Коллоидная химия: инновации и решения для химической технологии, экологии и промышленности, 2025, с. 936-939.', 'ar' => 'تورسونوفا N.N. احتياطيات التربة وتعرضها للتعرية بفعل الرياح. الكيمياء الغروية: ابتكارات وحلول للتقنية الكيميائية والبيئة والصناعة، 2025، ص 936-939.'],
            ['en' => 'Tursunova N.N. The environmental condition of New Uzbekistan. Colloid Chemistry: Innovations and Solutions for Chemical Technology, Ecology and Industry, 2025, pp. 318-322.', 'uz' => 'Tursunova N.N. Yangi O‘zbekistonning atrof-muhit holati. Kolloid kimyo: kimyoviy texnologiya, ekologiya va sanoat uchun innovatsiyalar va yechimlar, 2025, 318-322-betlar.', 'ru' => 'Турсунова Н.Н. Состояние окружающей среды Нового Узбекистана. Коллоидная химия: инновации и решения для химической технологии, экологии и промышленности, 2025, с. 318-322.', 'ar' => 'تورسونوفا N.N. الوضع البيئي في أوزبكستان الجديدة. الكيمياء الغروية: ابتكارات وحلول للتقنية الكيميائية والبيئة والصناعة، 2025، ص 318-322.'],
            ['en' => 'Tursunova N.N. Main sources of pollution. Innovative Solutions to Technological Problems in Oil, Gas and Gas Condensate Production and Preparation, Bukhara, 2024, pp. 424-426.', 'uz' => 'Tursunova N.N. Ifloslanishning asosiy manbalari. Neft, gaz va gazkondensat qazib olish va tayyorlashdagi texnologik muammolarning innovatsion yechimlari, Buxoro, 2024, 424-426-betlar.', 'ru' => 'Турсунова Н.Н. Основные источники загрязнения. Инновационные решения технологических проблем добычи и подготовки нефти, газа и газоконденсата, Бухара, 2024, с. 424-426.', 'ar' => 'تورسونوفا N.N. المصادر الرئيسية للتلوث. حلول مبتكرة للمشكلات التقنية في إنتاج وتحضير النفط والغاز ومكثفات الغاز، بخارى، 2024، ص 424-426.'],
        ];

        return array_map(fn (array $item) => $item[$locale] ?? $item['en'], $items);
    }

    private function cooperationItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Kafedra BMTning FAO tashkiloti, Erasmus+ dasturi hamda AQSH, Kanada, Buyuk Britaniya, Germaniya, Ispaniya, Italiya, Vengriya, Kipr, Belarus, Rossiya, Turkiya, Koreya, Malayziya va Qozog‘istondagi hamkor universitetlar bilan ilmiy-amaliy aloqalarni rivojlantiradi.', 'Hamkor tashkilotlar orasida Iowa University, Wyoming University, Niagara College, University of Surrey, Humboldt University of Berlin, Polytechnic University of Valencia, Turin Polytechnic University, University of Rome, University of Szeged, Obuda University, Belarusian State Technical University, Timiryazev Moscow State Agrarian University, Kursk State Agrarian University, Middle East Technical University, Uşak University, Seoul University, INTI International University va M. Auezov South Kazakhstan University mavjud.', 'Xitoyning Lanzhou University granti asosida universitet bazasida qiymati 130 ming AQSH dollariga teng “Changni oldindan prognozlash” qurilma-laboratoriyasi tashkil etilgan.', 'M. Auezov nomidagi Janubiy Qozog‘iston davlat universiteti bilan hamkorlik memorandumi imzolangan.', 'Daurenbek Nazarbek Mukhaddasuli va Lokhanova Kulzoda Mergenovna ilmiy maslahatchi sifatida kafedraga jalb qilingan.', 'Talabalar va professor-o‘qituvchilar almashinuvi yo‘lga qo‘yilgan.'],
            'ru' => ['Кафедра развивает научно-практическое сотрудничество с FAO ООН, программой Erasmus+ и партнерскими университетами США, Канады, Великобритании, Германии, Испании, Италии, Венгрии, Кипра, Беларуси, России, Турции, Кореи, Малайзии и Казахстана.', 'Среди партнеров: Университет Айовы, Университет Вайоминга, Niagara College, Университет Суррея, Берлинский университет имени Гумбольдта, Политехнический университет Валенсии, Туринский политехнический университет, Римский университет, Сегедский университет, Университет Обуда, Белорусский государственный технический университет, Московский государственный аграрный университет имени К.А. Тимирязева, Курский государственный аграрный университет, Ближневосточный технический университет, Университет Ушак, Сеульский университет, INTI International University и Южно-Казахстанский университет имени М. Ауэзова.', 'На базе университета при поддержке гранта Ланьчжоуского университета Китая создана лаборатория-установка «Предварительное прогнозирование пыли» стоимостью 130 тысяч долларов США.', 'Подписан меморандум о сотрудничестве с Южно-Казахстанским государственным университетом имени М. Ауэзова.', 'В качестве научных консультантов кафедры привлечены Дауренбек Назарбек Мухаддасули и Лоханова Кулзода Мергеновна.', 'Налажен обмен студентами и профессорско-преподавательским составом.'],
            'ar' => ['يطور القسم التعاون العلمي والتطبيقي مع منظمة FAO التابعة للأمم المتحدة وبرنامج Erasmus+ وجامعات شريكة في الولايات المتحدة وكندا والمملكة المتحدة وألمانيا وإسبانيا وإيطاليا والمجر وقبرص وبيلاروس وروسيا وتركيا وكوريا وماليزيا وكازاخستان.', 'تشمل المؤسسات الشريكة جامعة آيوا، جامعة وايومنغ، كلية نياغارا، جامعة سَري، جامعة هومبولت في برلين، جامعة فالنسيا التقنية، جامعة تورينو التقنية، جامعة روما، جامعة سيغيد، جامعة أوبودا، الجامعة التقنية الحكومية البيلاروسية، جامعة تيميريازيف الزراعية الحكومية في موسكو، جامعة كورسك الزراعية الحكومية، جامعة الشرق الأوسط التقنية، جامعة أوشاك، جامعة سيول، جامعة INTI الدولية، وجامعة جنوب كازاخستان باسم M. Auezov.', 'تم إنشاء مختبر جهاز “التنبؤ المسبق بالغبار” في الجامعة بمنحة من جامعة لانتشو الصينية بقيمة 130 ألف دولار أمريكي.', 'تم توقيع مذكرة تعاون مع جامعة جنوب كازاخستان الحكومية باسم M. Auezov.', 'تم إشراك داورينبيك نازاربيك موخاداسولي ولوخانوفا كولزودا ميرجينوفنا كمستشارين علميين للقسم.', 'تم إطلاق برامج تبادل للطلاب وأعضاء هيئة التدريس.'],
            default => ['The department develops scientific and practical cooperation with the UN FAO, the Erasmus+ program, and partner universities in the USA, Canada, the United Kingdom, Germany, Spain, Italy, Hungary, Cyprus, Belarus, Russia, Turkey, Korea, Malaysia, and Kazakhstan.', 'Partner institutions include Iowa University, Wyoming University, Niagara College, the University of Surrey, Humboldt University of Berlin, Polytechnic University of Valencia, Turin Polytechnic University, University of Rome, University of Szeged, Obuda University, Belarusian State Technical University, K.A. Timiryazev Moscow State Agrarian University, Kursk State Agrarian University, Middle East Technical University, Uşak University, Seoul University, INTI International University, and M. Auezov South Kazakhstan University.', 'A “Dust Forecasting and Prediction” laboratory device was established at the university through a grant from Lanzhou University of China worth USD 130,000.', 'A memorandum of cooperation was signed with M. Auezov South Kazakhstan State University.', 'Daurenbek Nazarbek Mukhaddasuli and Lokhanova Kulzoda Mergenovna have been involved as scientific advisers to the department.', 'Student and faculty exchange programs have been established.'],
        };
    }

    private function plansItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Xorijiy oliy ta’lim muassasalari tajribasi asosida o‘quv rejalari, adabiyotlar va me’yoriy hujjatlarni takomillashtirish.', 'TOP-500 universitetlar bilan qo‘shma ta’lim dasturlari va dual ta’lim shakllarini kengaytirish.', 'Yangi avlod o‘quv adabiyotlarini yaratish va axborot-resurs markazi fondini muntazam yangilash.', 'Talabalarda tanqidiy va ijodiy fikrlash, tizimli tahlil va tadbirkorlik ko‘nikmalarini rivojlantirish.', 'Hamkor tashkilotlar bazasida kafedra filiallarini tashkil etish va ishlab chiqarish amaliyotlarini kuchaytirish.', 'Xorijiy talabalar ulushini oshirish, akademik mobillikni rivojlantirish va ilmiy natijalarni Web of Science hamda Scopus bazalarida ko‘paytirish.'],
            'ru' => ['Совершенствование учебных планов, литературы и нормативных документов на основе опыта зарубежных вузов.', 'Расширение совместных образовательных программ и дуальной формы обучения с университетами из рейтинга TOP-500.', 'Создание учебной литературы нового поколения и регулярное обновление фонда информационно-ресурсного центра.', 'Развитие у студентов критического и творческого мышления, системного анализа и предпринимательских навыков.', 'Организация филиалов кафедры на базе партнерских организаций и усиление производственной практики.', 'Увеличение доли иностранных студентов, развитие академической мобильности и рост публикаций в Web of Science и Scopus.'],
            'ar' => ['تطوير الخطط الدراسية والمراجع والوثائق التنظيمية بالاستفادة من خبرات الجامعات الأجنبية.', 'توسيع البرامج التعليمية المشتركة ونظام التعليم المزدوج مع الجامعات المصنفة ضمن TOP-500.', 'إعداد مراجع تعليمية من الجيل الجديد وتحديث أرصدة مركز مصادر المعلومات باستمرار.', 'تنمية التفكير النقدي والإبداعي والتحليل المنهجي ومهارات ريادة الأعمال لدى الطلاب.', 'إنشاء فروع للقسم لدى المؤسسات الشريكة وتعزيز التدريب العملي في الإنتاج.', 'زيادة نسبة الطلاب الدوليين وتطوير الحراك الأكاديمي ورفع عدد المنشورات في Web of Science وScopus.'],
            default => ['Improve curricula, literature, and regulatory documents using the experience of foreign higher education institutions.', 'Expand joint educational programs and dual education formats with TOP-500 universities.', 'Create new-generation educational literature and regularly update the Information Resource Center collection.', 'Develop students’ critical and creative thinking, systematic analysis, and entrepreneurial skills.', 'Organize department branches at partner organizations and strengthen production-based internships.', 'Increase the number of international students, develop academic mobility, and expand publications indexed in Web of Science and Scopus.'],
        };
    }

    private function staffProfiles(): array
    {
        return [
            ['slug' => 'industrial-ecology-hydrogeology-xaitov-rauf-arifovich', 'names' => ['en' => 'Xaitov Rauf Arifovich', 'uz' => 'Xaitov Rauf Arifovich', 'ru' => 'Хаитов Рауф Арифович', 'ar' => 'خايتوف رؤوف عارفوفيتش'], 'position' => 'Head of Department'],
            ['slug' => 'industrial-ecology-hydrogeology-mukhamadiev-bahadir-temurovich', 'names' => ['en' => 'Mukhamadiev Bahadir Temurovich', 'uz' => 'Muhamadiyev Bahodir Temurovich', 'ru' => 'Мухамадиев Баходир Темурович', 'ar' => 'محمدييف بهادير تيموروفيتش'], 'position' => 'Associate Professor'],
            ['slug' => 'industrial-ecology-hydrogeology-fattoev-ismoil-islomovich', 'names' => ['en' => 'Fattoev Ismoil Islomovich', 'uz' => 'Fattoyev Ismoil Islomovich', 'ru' => 'Фаттоев Исмоил Исломович', 'ar' => 'فاتتوييف إسماعيل إسلاموفيتش'], 'position' => 'Associate Professor'],
            ['slug' => 'industrial-ecology-hydrogeology-baxriddinova-nasiba-murodovna', 'names' => ['en' => 'Baxriddinova Nasiba Murodovna', 'uz' => 'Baxriddinova Nasiba Murodovna', 'ru' => 'Бахриддинова Насиба Муродовна', 'ar' => 'بخريدينوفا نسيبة مرادوفنا'], 'position' => 'Candidate of Technical Sciences, Associate Professor'],
            ['slug' => 'industrial-ecology-hydrogeology-hamidov-yoqub-yodgorovich', 'names' => ['en' => 'Hamidov Yoqub Yodgorovich', 'uz' => 'Hamidov Yoqub Yodgorovich', 'ru' => 'Хамидов Якуб Ёдгорович', 'ar' => 'حميدوف يعقوب يادغوروفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'industrial-ecology-hydrogeology-zaripova-mohira-djuraeva', 'names' => ['en' => 'Zaripova Mohira Djuraeva', 'uz' => 'Zaripova Mohira Jo‘rayeva', 'ru' => 'Зарипова Мохира Джураевна', 'ar' => 'زاريبوفا موهيرا جوراييفا'], 'position' => 'Senior Lecturer'],
            ['slug' => 'industrial-ecology-hydrogeology-tursunova-nargiza-nigmatova', 'names' => ['en' => 'Tursunova Nargiza Nigmatova', 'uz' => 'Tursunova Nargiza Nigmatova', 'ru' => 'Турсунова Наргиза Нигматовна', 'ar' => 'تورسونوفا نرجيزا نيغماتوفنا'], 'position' => 'Senior Lecturer'],
            ['slug' => 'industrial-ecology-hydrogeology-qurbonov-mirshod-toshpolatovich', 'names' => ['en' => 'Qurbonov Mirshod Toshpo‘latovich', 'uz' => 'Qurbonov Mirshod Toshpo‘latovich', 'ru' => 'Курбонов Миршод Тошпулатович', 'ar' => 'قربونوف ميرشود توشبولاتوفيتش'], 'position' => 'Assistant'],
            ['slug' => 'industrial-ecology-hydrogeology-xolova-shohista-abdurashidovna', 'names' => ['en' => 'Xolova Shohista Abdurashidovna', 'uz' => 'Xolova Shohista Abdurashidovna', 'ru' => 'Холова Шохиста Абдурашидовна', 'ar' => 'خولوفا شوهيستا عبد الرشيدوفنا'], 'position' => 'Assistant'],
            ['slug' => 'industrial-ecology-hydrogeology-kamolova-feruza-raxmatovna', 'names' => ['en' => 'Kamolova Feruza Raxmatovna', 'uz' => 'Kamolova Feruza Raxmatovna', 'ru' => 'Камолова Феруза Рахматовна', 'ar' => 'كامولوفا فيروزا رحمتوفنا'], 'position' => 'Assistant'],
            ['slug' => 'industrial-ecology-hydrogeology-axmedova-mexriniso-baxronovna', 'names' => ['en' => 'Axmedova Mexriniso Baxronovna', 'uz' => 'Axmedova Mehriniso Bahronovna', 'ru' => 'Ахмедова Мехринисо Бахроновна', 'ar' => 'أحمدوفا مهرينيسو بحرونوفنا'], 'position' => 'Doctoral Student'],
            ['slug' => 'industrial-ecology-hydrogeology-tuxtayeva-xabiba-toshevna', 'names' => ['en' => 'Tuxtayeva Xabiba Toshevna', 'uz' => 'To‘xtayeva Xabiba Toshevna', 'ru' => 'Тухтаева Хабиба Тошевна', 'ar' => 'توختاييفا خبيبة توشيفنا'], 'position' => 'Doctoral Student'],
            ['slug' => 'industrial-ecology-hydrogeology-atamuratova-tamara-ivanovna', 'names' => ['en' => 'Atamuratova Tamara Ivanovna', 'uz' => 'Atamuratova Tamara Ivanovna', 'ru' => 'Атамуратова Тамара Ивановна', 'ar' => 'أتاموراتوفا تمارا إيفانوفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'industrial-ecology-hydrogeology-qobulova-barno-baxridin-qizi', 'names' => ['en' => 'Qobulova Barno Baxridin qizi', 'uz' => 'Qobulova Barno Baxridin qizi', 'ru' => 'Кобулова Барно Бахриддин кизи', 'ar' => 'قبولوفا بارنو بخريدين قيزي'], 'position' => 'Associate Professor'],
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
            'Associate Professor' => ['en' => 'Associate Professor', 'uz' => 'Dotsent', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'Candidate of Technical Sciences, Associate Professor' => ['en' => 'Candidate of Technical Sciences, Associate Professor', 'uz' => 'Texnika fanlari nomzodi, dotsent', 'ru' => 'Кандидат технических наук, доцент', 'ar' => 'مرشح في العلوم التقنية، أستاذ مشارك'],
            'Senior Lecturer' => ['en' => 'Senior Lecturer', 'uz' => 'Katta o‘qituvchi', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'Assistant' => ['en' => 'Assistant', 'uz' => 'Assistent', 'ru' => 'Ассистент', 'ar' => 'مساعد'],
            'Doctoral Student' => ['en' => 'Doctoral Student', 'uz' => 'Doktorant', 'ru' => 'Докторант', 'ar' => 'طالب دكتوراه'],
        ];

        return $map[$position][$locale] ?? $position;
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Sanoat ekologiyasi va gidrogeologiya kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре промышленной экологии и гидрогеологии в должности «{$position}».",
            'ar' => "{$name} يعمل/تعمل في قسم البيئة الصناعية والهيدروجيولوجيا بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Industrial Ecology and Hydrogeology.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'Kafedrada o‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد التي تدرس في القسم'],
            'research' => ['en' => 'Ongoing Research', 'uz' => 'Joriy ilmiy tadqiqotlar', 'ru' => 'Текущие исследования', 'ar' => 'الأبحاث الجارية'],
            'cooperation' => ['en' => 'Cooperation / International Relations', 'uz' => 'Hamkorlik / xalqaro aloqalar', 'ru' => 'Сотрудничество / международные связи', 'ar' => 'التعاون / العلاقات الدولية'],
            'plans' => ['en' => 'News / Activities / Prospective Plans', 'uz' => 'Yangiliklar / faoliyat / istiqbolli rejalar', 'ru' => 'Новости / деятельность / перспективные планы', 'ar' => 'الأخبار / الأنشطة / الخطط المستقبلية'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function degreeLabel(string $locale, string $degree): string
    {
        return match ("{$locale}:{$degree}") {
            'uz:bachelor' => 'Bachelor subjects',
            'ru:bachelor' => 'Bachelor subjects',
            'ar:bachelor' => 'Bachelor subjects',
            default => 'Bachelor subjects',
        };
    }

    private function replaceSection(array $sections, array $replacement): array
    {
        $found = false;
        foreach ($sections as $index => $section) {
            if (($section['key'] ?? null) === $replacement['key']) {
                $sections[$index] = $replacement;
                $found = true;
            }
        }

        if (! $found) {
            $sections[] = $replacement;
        }

        return $sections;
    }

    private function locales(): array
    {
        return ['en', 'uz', 'ru', 'ar'];
    }
};
