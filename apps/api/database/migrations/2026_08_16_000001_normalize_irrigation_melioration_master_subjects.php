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

        $departmentId = DB::table('departments')->where('slug', 'irrigation-melioration')->value('id');

        if (! $departmentId) {
            return;
        }

        foreach ($this->subjectsByLocale() as $locale => $subjects) {
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

            $subjectsSection = [
                'key' => 'subjects',
                'title' => $subjects['title'],
                'items' => [
                    $this->numberedSection("Bachelor's Degree", $subjects['bachelor'])."\n".$this->numberedSection('Master subjects', $subjects['master']),
                ],
            ];

            $found = false;
            foreach ($sections as $index => $section) {
                if (($section['key'] ?? null) === 'subjects') {
                    $sections[$index] = $subjectsSection;
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $sections[] = $subjectsSection;
            }

            DB::table('department_translations')
                ->where('id', $translation->id)
                ->update([
                    'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function numberedSection(string $heading, array $items): string
    {
        return $heading.":\n".collect($items)
            ->map(fn (string $item, int $index) => ($index + 1).'. '.$item)
            ->implode("\n");
    }

    private function subjectsByLocale(): array
    {
        $bachelor = [
            'en' => [
                'Introduction to the Specialty',
                'Automation Systems for Drip Irrigation',
                'Smart Water Metering Devices',
                'Irrigation and Land Reclamation',
                'Salt Leaching Technology',
                'Innovative Technologies in Water Management',
                'Innovative Technologies in Land Reclamation',
                'Irrigation Land Reclamation',
                'Rational Use of Water Resources',
                'Water-Saving Irrigation Technologies',
                'Construction and Operation of Water Intake Wells',
                'Organization and Management of Communal Infrastructure',
                'Rational Use of Water Resources',
                'Modern Irrigation Technologies',
                'Water Supply',
                'Water Purification',
                'Installation of Water Supply and Sewage Networks',
                'Modeling of Water Supply Technologies',
                'Fundamentals of Wastewater Reuse',
                'Design of Water Distribution Networks',
                'Engineering Works in Water Supply',
                'Operation of Water Supply Systems',
                'Water Treatment and Water Intake Structures',
                'Fundamentals of Urban Housing, Communal Services and Maintenance',
                'Installation of Water Supply and Sewage Networks',
                'Control and Measuring Instruments Installed in Networks',
                'Drinking Water Supply',
                'Rational Use and Protection of Water Resources',
                'Drip Irrigation Networks and Their Maintenance',
                'Irrigation Technology',
            ],
            'uz' => [
                'Mutaxassislikka kirish',
                'Tomchilatib sug‘orishni avtomatlashtirish tizimlari',
                'Aqlli suv hisoblagich qurilmalari',
                'Irrigatsiya va melioratsiya',
                'Sho‘r yuvish texnologiyasi',
                'Suv xo‘jaligida innovatsion texnologiyalar',
                'Melioratsiyada innovatsion texnologiyalar',
                'Sug‘oriladigan yerlar melioratsiyasi',
                'Suv resurslaridan oqilona foydalanish',
                'Suv tejovchi sug‘orish texnologiyalari',
                'Suv olish quduqlarini qurish va ulardan foydalanish',
                'Kommunal infratuzilmani tashkil etish va boshqarish',
                'Suv resurslaridan oqilona foydalanish',
                'Zamonaviy sug‘orish texnologiyalari',
                'Suv ta’minoti',
                'Suvni tozalash',
                'Suv ta’minoti va kanalizatsiya tarmoqlarini montaj qilish',
                'Suv ta’minoti texnologiyalarini modellashtirish',
                'Oqova suvlardan qayta foydalanish asoslari',
                'Suv taqsimlash tarmoqlarini loyihalash',
                'Suv ta’minotida muhandislik ishlari',
                'Suv ta’minoti tizimlaridan foydalanish',
                'Suvni tozalash va suv olish inshootlari',
                'Shahar uy-joy kommunal xo‘jaligi va ekspluatatsiyasi asoslari',
                'Suv ta’minoti va kanalizatsiya tarmoqlarini montaj qilish',
                'Tarmoqlarda o‘rnatiladigan nazorat-o‘lchov asboblari',
                'Ichimlik suvi ta’minoti',
                'Suv resurslaridan oqilona foydalanish va ularni muhofaza qilish',
                'Tomchilatib sug‘orish tarmoqlari va ularga xizmat ko‘rsatish',
                'Sug‘orish texnologiyasi',
            ],
            'ru' => [
                'Введение в специальность',
                'Системы автоматизации капельного орошения',
                'Умные приборы учета воды',
                'Ирригация и мелиорация',
                'Технология промывки засоленных почв',
                'Инновационные технологии в водном хозяйстве',
                'Инновационные технологии в мелиорации',
                'Мелиорация орошаемых земель',
                'Рациональное использование водных ресурсов',
                'Водосберегающие технологии орошения',
                'Строительство и эксплуатация водозаборных скважин',
                'Организация и управление коммунальной инфраструктурой',
                'Рациональное использование водных ресурсов',
                'Современные технологии орошения',
                'Водоснабжение',
                'Очистка воды',
                'Монтаж сетей водоснабжения и канализации',
                'Моделирование технологий водоснабжения',
                'Основы повторного использования сточных вод',
                'Проектирование водораспределительных сетей',
                'Инженерные работы в водоснабжении',
                'Эксплуатация систем водоснабжения',
                'Водоочистные и водозаборные сооружения',
                'Основы городского жилищно-коммунального хозяйства и эксплуатации',
                'Монтаж сетей водоснабжения и канализации',
                'Контрольно-измерительные приборы, установленные в сетях',
                'Питьевое водоснабжение',
                'Рациональное использование и охрана водных ресурсов',
                'Сети капельного орошения и их обслуживание',
                'Технология орошения',
            ],
            'ar' => [
                'مدخل إلى التخصص',
                'أنظمة أتمتة الري بالتنقيط',
                'أجهزة القياس الذكية للمياه',
                'الري واستصلاح الأراضي',
                'تقنية غسل الأملاح',
                'التقنيات المبتكرة في إدارة المياه',
                'التقنيات المبتكرة في استصلاح الأراضي',
                'استصلاح الأراضي المروية',
                'الاستخدام الرشيد للموارد المائية',
                'تقنيات الري الموفرة للمياه',
                'إنشاء وتشغيل آبار سحب المياه',
                'تنظيم وإدارة البنية التحتية للمرافق',
                'الاستخدام الرشيد للموارد المائية',
                'تقنيات الري الحديثة',
                'إمدادات المياه',
                'تنقية المياه',
                'تركيب شبكات إمدادات المياه والصرف الصحي',
                'نمذجة تقنيات إمدادات المياه',
                'أساسيات إعادة استخدام مياه الصرف',
                'تصميم شبكات توزيع المياه',
                'الأعمال الهندسية في إمدادات المياه',
                'تشغيل أنظمة إمدادات المياه',
                'منشآت معالجة المياه وسحبها',
                'أساسيات الإسكان الحضري والخدمات البلدية والصيانة',
                'تركيب شبكات إمدادات المياه والصرف الصحي',
                'أجهزة القياس والتحكم المثبتة في الشبكات',
                'إمدادات مياه الشرب',
                'الاستخدام الرشيد وحماية الموارد المائية',
                'شبكات الري بالتنقيط وصيانتها',
                'تكنولوجيا الري',
            ],
        ];

        $master = [
            'en' => [
                'Improving Natural Water Quality',
                'Methods of Teaching Special Disciplines',
                'Water-Saving Irrigation Technologies',
                'Methods of Teaching Special Disciplines',
                'Resource-Efficient Irrigation Technologies',
                'Methods of Teaching Special Disciplines',
                'Meliorative Soil Science and Farming',
                'Rational Use and Protection of Water Resources',
                'Water Cadastre and Integrated Water Resources Management',
                'International and State Water Relations',
                'Water-Saving Irrigation Technologies',
                'Methodology for Conducting Field Research',
                'Operation and Automation of Irrigation Networks',
                'Landscape Irrigation',
                'Water-Efficient Irrigation Technologies',
                'Moisture-Retention Technologies on Irrigated Lands',
                'Rural and Pasture Water Supply',
                'Methods of Teaching Special Subjects',
                'Irrigation Technologies',
            ],
            'uz' => [
                'Tabiiy suvlar sifatini yaxshilash',
                'Maxsus fanlarni o‘qitish metodikasi',
                'Suv tejovchi sug‘orish texnologiyalari',
                'Maxsus fanlarni o‘qitish metodikasi',
                'Resurs tejamkor sug‘orish texnologiyalari',
                'Maxsus fanlarni o‘qitish metodikasi',
                'Meliorativ tuproqshunoslik va dehqonchilik',
                'Suv resurslaridan oqilona foydalanish va ularni muhofaza qilish',
                'Suv kadastri va suv resurslarini integrallashgan boshqarish',
                'Xalqaro va davlat suv munosabatlari',
                'Suv tejovchi sug‘orish texnologiyalari',
                'Dala tadqiqotlarini olib borish metodologiyasi',
                'Sug‘orish tarmoqlaridan foydalanish va ularni avtomatlashtirish',
                'Landshaft sug‘orish',
                'Suvdan samarali foydalanishga yo‘naltirilgan sug‘orish texnologiyalari',
                'Sug‘oriladigan yerlarda namlikni saqlash texnologiyalari',
                'Qishloq va yaylov suv ta’minoti',
                'Maxsus fanlarni o‘qitish metodikasi',
                'Sug‘orish texnologiyalari',
            ],
            'ru' => [
                'Улучшение качества природных вод',
                'Методика преподавания специальных дисциплин',
                'Водосберегающие технологии орошения',
                'Методика преподавания специальных дисциплин',
                'Ресурсосберегающие технологии орошения',
                'Методика преподавания специальных дисциплин',
                'Мелиоративное почвоведение и земледелие',
                'Рациональное использование и охрана водных ресурсов',
                'Водный кадастр и интегрированное управление водными ресурсами',
                'Международные и государственные водные отношения',
                'Водосберегающие технологии орошения',
                'Методология проведения полевых исследований',
                'Эксплуатация и автоматизация оросительных сетей',
                'Ландшафтное орошение',
                'Водоэффективные технологии орошения',
                'Технологии сохранения влаги на орошаемых землях',
                'Сельское и пастбищное водоснабжение',
                'Методика преподавания специальных предметов',
                'Технологии орошения',
            ],
            'ar' => [
                'تحسين جودة المياه الطبيعية',
                'طرائق تدريس التخصصات الخاصة',
                'تقنيات الري الموفرة للمياه',
                'طرائق تدريس التخصصات الخاصة',
                'تقنيات الري الموفرة للموارد',
                'طرائق تدريس التخصصات الخاصة',
                'علم التربة الاستصلاحية والزراعة',
                'الاستخدام الرشيد وحماية الموارد المائية',
                'سجل المياه والإدارة المتكاملة للموارد المائية',
                'العلاقات المائية الدولية والحكومية',
                'تقنيات الري الموفرة للمياه',
                'منهجية إجراء البحوث الميدانية',
                'تشغيل وأتمتة شبكات الري',
                'الري الطبيعي وتنسيق المناظر',
                'تقنيات الري عالية الكفاءة في استخدام المياه',
                'تقنيات حفظ الرطوبة في الأراضي المروية',
                'إمدادات المياه للقرى والمراعي',
                'طرائق تدريس المواد الخاصة',
                'تقنيات الري',
            ],
        ];

        return collect(['en', 'uz', 'ru', 'ar'])
            ->mapWithKeys(fn (string $locale) => [$locale => [
                'title' => match ($locale) {
                    'uz' => 'Kafedrada o‘qitiladigan fanlar',
                    'ru' => 'Преподаваемые дисциплины',
                    'ar' => 'المواد التي تدرس في القسم',
                    default => 'Taught Subjects',
                },
                'bachelor' => $bachelor[$locale],
                'master' => $master[$locale],
            ]])
            ->all();
    }
};
