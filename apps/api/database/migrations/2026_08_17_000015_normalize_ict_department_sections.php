<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'information-and-communication-technologies')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            foreach ($this->locales() as $locale) {
                $translation = DB::table('department_translations')
                    ->where('department_id', $departmentId)
                    ->where('locale', $locale)
                    ->first();

                if (! $translation) {
                    continue;
                }

                DB::table('department_translations')
                    ->where('id', $translation->id)
                    ->update([
                        'content_sections' => json_encode($this->sections($departmentId, $locale), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at' => now(),
                    ]);
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function sections(int $departmentId, string $locale): array
    {
        return [
            ['key' => 'history', 'title' => $this->label($locale, 'history'), 'items' => [$this->history($locale)]],
            ['key' => 'prepared_specialists', 'title' => $this->label($locale, 'prepared'), 'items' => $this->programItems($departmentId, $locale)],
            [
                'key' => 'subjects',
                'title' => $this->label($locale, 'subjects'),
                'items' => [],
                'bachelor' => $this->bachelorSubjects($locale),
                'master' => $this->masterSubjects($locale),
            ],
            ['key' => 'staff', 'title' => $this->label($locale, 'staff'), 'items' => $this->staffItems($departmentId, $locale)],
            ['key' => 'research', 'title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)],
            ['key' => 'cooperation', 'title' => $this->label($locale, 'cooperation'), 'items' => $this->cooperationItems($locale)],
        ];
    }

    private function programItems(int $departmentId, string $locale): array
    {
        return DB::table('programs as p')
            ->leftJoin('program_translations as t', function ($join) use ($locale) {
                $join->on('t.program_id', '=', 'p.id')->where('t.locale', '=', $locale);
            })
            ->where('p.department_id', $departmentId)
            ->where('p.is_active', true)
            ->orderBy('p.official_code')
            ->select('p.official_code', 't.name')
            ->get()
            ->map(fn ($program) => trim($program->official_code.' - '.$program->name))
            ->values()
            ->all();
    }

    private function staffItems(int $departmentId, string $locale): array
    {
        return DB::table('staff_profiles as s')
            ->join('staff_profile_translations as t', function ($join) use ($locale) {
                $join->on('t.staff_profile_id', '=', 's.id')->where('t.locale', '=', $locale);
            })
            ->where('s.department_id', $departmentId)
            ->where('s.is_active', true)
            ->orderBy('s.sort_order')
            ->select('t.full_name', 't.position')
            ->get()
            ->map(fn ($staff) => trim($staff->full_name."\n".$staff->position))
            ->values()
            ->all();
    }

    private function history(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra dasturlash, kompyuter tarmoqlari, sun’iy intellekt, kiberxavfsizlik, axborot tizimlari va zamonaviy raqamli yechimlar bo‘yicha raqobatbardosh IT mutaxassislarini tayyorlaydi.',
            'ru' => 'Кафедра готовит конкурентоспособных IT-специалистов в области программирования, компьютерных сетей, искусственного интеллекта, кибербезопасности, информационных систем и современных цифровых решений.',
            'ar' => 'يدرب القسم متخصصين تنافسيين في تقنيات المعلومات في البرمجة، شبكات الحاسوب، الذكاء الاصطناعي، الأمن السيبراني، نظم المعلومات، والحلول الرقمية الحديثة.',
            default => 'The department trains competitive IT specialists in programming, computer networks, artificial intelligence, cybersecurity, information systems, and modern digital solutions.',
        };
    }

    private function bachelorSubjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Dasturlash asoslari', 'Algoritmlar va ma’lumotlar tuzilmalari', 'Obyektga yo‘naltirilgan dasturlash', 'Veb dasturlash', 'Mobil ilovalar ishlab chiqish', 'Ma’lumotlar bazalari', 'Kompyuter tarmoqlari', 'Operatsion tizimlar', 'Kompyuter arxitekturasi', 'Axborot xavfsizligi', 'Kiberxavfsizlik asoslari', 'Sun’iy intellekt', 'Mashinali o‘qitish', 'Dasturiy injiniring', 'Axborot tizimlarini loyihalash', 'Bulutli texnologiyalar', 'Raqamli texnologiyalar', 'Kompyuter grafikasi', 'Internet texnologiyalari', 'Tizimli dasturlash', 'Ma’lumotlar tahlili', 'IoT tizimlari', 'IT loyihalarni boshqarish', 'Bitiruv loyihasi va amaliyot'],
            'ru' => ['Основы программирования', 'Алгоритмы и структуры данных', 'Объектно-ориентированное программирование', 'Веб-программирование', 'Разработка мобильных приложений', 'Базы данных', 'Компьютерные сети', 'Операционные системы', 'Архитектура компьютеров', 'Информационная безопасность', 'Основы кибербезопасности', 'Искусственный интеллект', 'Машинное обучение', 'Программная инженерия', 'Проектирование информационных систем', 'Облачные технологии', 'Цифровые технологии', 'Компьютерная графика', 'Интернет-технологии', 'Системное программирование', 'Анализ данных', 'IoT-системы', 'Управление IT-проектами', 'Выпускной проект и практика'],
            'ar' => ['أساسيات البرمجة', 'الخوارزميات وهياكل البيانات', 'البرمجة كائنية التوجه', 'برمجة الويب', 'تطوير تطبيقات الهاتف', 'قواعد البيانات', 'شبكات الحاسوب', 'أنظمة التشغيل', 'معمارية الحاسوب', 'أمن المعلومات', 'أساسيات الأمن السيبراني', 'الذكاء الاصطناعي', 'تعلم الآلة', 'هندسة البرمجيات', 'تصميم نظم المعلومات', 'تقنيات الحوسبة السحابية', 'التقنيات الرقمية', 'رسوميات الحاسوب', 'تقنيات الإنترنت', 'برمجة النظم', 'تحليل البيانات', 'أنظمة إنترنت الأشياء', 'إدارة مشاريع تقنية المعلومات', 'مشروع التخرج والتدريب العملي'],
            default => ['Programming Fundamentals', 'Algorithms and Data Structures', 'Object-Oriented Programming', 'Web Programming', 'Mobile Application Development', 'Databases', 'Computer Networks', 'Operating Systems', 'Computer Architecture', 'Information Security', 'Cybersecurity Fundamentals', 'Artificial Intelligence', 'Machine Learning', 'Software Engineering', 'Information Systems Design', 'Cloud Technologies', 'Digital Technologies', 'Computer Graphics', 'Internet Technologies', 'System Programming', 'Data Analysis', 'Internet of Things Systems', 'IT Project Management', 'Graduation Project and Internship'],
        };
    }

    private function masterSubjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Ilmiy tadqiqot metodologiyasi', 'Ilg‘or dasturiy injiniring', 'Intellektual axborot tizimlari', 'Katta ma’lumotlar tahlili', 'Kompyuter tarmoqlarining xavfsizligi', 'Bulutli va taqsimlangan hisoblash', 'Sun’iy intellekt modellari', 'Ilmiy-pedagogik ish', 'Magistrlik dissertatsiyasini tayyorlash'],
            'ru' => ['Методология научных исследований', 'Продвинутая программная инженерия', 'Интеллектуальные информационные системы', 'Анализ больших данных', 'Безопасность компьютерных сетей', 'Облачные и распределенные вычисления', 'Модели искусственного интеллекта', 'Научно-педагогическая работа', 'Подготовка магистерской диссертации'],
            'ar' => ['منهجية البحث العلمي', 'هندسة البرمجيات المتقدمة', 'نظم المعلومات الذكية', 'تحليل البيانات الضخمة', 'أمن شبكات الحاسوب', 'الحوسبة السحابية والموزعة', 'نماذج الذكاء الاصطناعي', 'العمل العلمي والتربوي', 'إعداد رسالة الماجستير'],
            default => ['Research Methodology', 'Advanced Software Engineering', 'Intelligent Information Systems', 'Big Data Analysis', 'Computer Network Security', 'Cloud and Distributed Computing', 'Artificial Intelligence Models', 'Scientific and Pedagogical Work', 'Preparation of Master’s Thesis'],
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Masofaviy ta’lim va raqamli ta’lim platformalari bo‘yicha tadqiqotlar.', 'Dasturiy tizimlar, sun’iy intellekt va ma’lumotlar tahlili bo‘yicha amaliy loyihalar.', 'Kompyuter tarmoqlari, axborot xavfsizligi va kiberxavfsizlik yechimlarini rivojlantirish.', 'Axborot qidiruv tizimlari va zamonaviy IT xizmatlarini takomillashtirish.'],
            'ru' => ['Исследования в области дистанционного обучения и цифровых образовательных платформ.', 'Прикладные проекты по программным системам, искусственному интеллекту и анализу данных.', 'Развитие решений для компьютерных сетей, информационной безопасности и кибербезопасности.', 'Совершенствование информационно-поисковых систем и современных IT-сервисов.'],
            'ar' => ['أبحاث في التعليم عن بعد والمنصات التعليمية الرقمية.', 'مشاريع تطبيقية في الأنظمة البرمجية والذكاء الاصطناعي وتحليل البيانات.', 'تطوير حلول شبكات الحاسوب وأمن المعلومات والأمن السيبراني.', 'تحسين أنظمة استرجاع المعلومات وخدمات تقنية المعلومات الحديثة.'],
            default => ['Research in distance learning and digital education platforms.', 'Applied projects in software systems, artificial intelligence, and data analysis.', 'Development of computer network, information security, and cybersecurity solutions.', 'Improvement of information retrieval systems and modern IT services.'],
        };
    }

    private function cooperationItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['UPSI Malaysia', 'Novosibirsk State Technical University', 'Al-Farabi Kazakh National University', 'Buxoro Toza Hudud', 'Uzjamoaloyiha'],
            'ru' => ['UPSI Malaysia', 'Новосибирский государственный технический университет', 'Казахский национальный университет имени Аль-Фараби', 'Buxoro Toza Hudud', 'Uzjamoaloyiha'],
            'ar' => ['UPSI Malaysia', 'جامعة نوفوسيبيرسك الحكومية التقنية', 'جامعة الفارابي الوطنية الكازاخية', 'Buxoro Toza Hudud', 'Uzjamoaloyiha'],
            default => ['UPSI Malaysia', 'Novosibirsk State Technical University', 'Al-Farabi Kazakh National University', 'Bukhoro Toza Hudud', 'Uzjamoaloyiha'],
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'history' => ['en' => 'Department History', 'uz' => 'Kafedra tarixi', 'ru' => 'История кафедры', 'ar' => 'تاريخ القسم'],
            'prepared' => ['en' => 'Prepared Specialists', 'uz' => 'Tayyorlanadigan mutaxassislar', 'ru' => 'Подготавливаемые специалисты', 'ar' => 'التخصصات التي يتم إعدادها'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'O‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد الدراسية'],
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'research' => ['en' => 'Ongoing Research', 'uz' => 'Joriy ilmiy tadqiqotlar', 'ru' => 'Текущие исследования', 'ar' => 'الأبحاث الجارية'],
            'cooperation' => ['en' => 'Cooperation / International Relations', 'uz' => 'Hamkorlik / xalqaro aloqalar', 'ru' => 'Сотрудничество / международные связи', 'ar' => 'التعاون / العلاقات الدولية'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function locales(): array
    {
        return ['en', 'uz', 'ru', 'ar'];
    }
};
