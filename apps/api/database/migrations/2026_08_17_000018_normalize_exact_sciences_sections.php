<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'exact-sciences')->value('id');

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
            ['key' => 'prepared_specialists', 'title' => $this->label($locale, 'prepared'), 'items' => [$this->preparedText($locale)]],
            [
                'key' => 'subjects',
                'title' => $this->label($locale, 'subjects'),
                'items' => [],
                'bachelor' => $this->subjects($locale),
                'master' => [],
            ],
            ['key' => 'staff', 'title' => $this->label($locale, 'staff'), 'items' => $this->staffItems($departmentId, $locale)],
            ['key' => 'research', 'title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)],
        ];
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
            'uz' => 'Kafedra muhandislik va IT yo‘nalishlari uchun matematika, fizika, mantiqiy fikrlash, analitik metodlar va ilmiy tadqiqot asoslarini beradi.',
            'ru' => 'Кафедра обеспечивает основы математики, физики, логического мышления, аналитических методов и научных исследований для инженерных и IT-направлений.',
            'ar' => 'يوفر القسم أسس الرياضيات والفيزياء والتفكير المنطقي والطرائق التحليلية والبحث العلمي للمجالات الهندسية وتقنية المعلومات.',
            default => 'The department provides foundations in mathematics, physics, logical thinking, analytical methods, and scientific research for engineering and IT fields.',
        };
    }

    private function preparedText(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra universitetning muhandislik, texnologiya va raqamli dasturlari uchun fundamental aniq fanlarni o‘qitadi.',
            'ru' => 'Кафедра преподает фундаментальные дисциплины точных наук для инженерных, технологических и цифровых программ университета.',
            'ar' => 'يدرّس القسم مواد العلوم الدقيقة الأساسية للبرامج الهندسية والتكنولوجية والرقمية في الجامعة.',
            default => 'The department teaches foundational exact-science disciplines for engineering, technology, and digital programs of the university.',
        };
    }

    private function subjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Oliy matematika', 'Fizika', 'Matematik analiz', 'Analitik geometriya', 'Ehtimollar nazariyasi va matematik statistika', 'Muhandislik va IT dasturlari uchun ilmiy tadqiqot asoslari'],
            'ru' => ['Высшая математика', 'Физика', 'Математический анализ', 'Аналитическая геометрия', 'Теория вероятностей и математическая статистика', 'Основы научных исследований для инженерных и IT-программ'],
            'ar' => ['الرياضيات العليا', 'الفيزياء', 'التحليل الرياضي', 'الهندسة التحليلية', 'نظرية الاحتمالات والإحصاء الرياضي', 'أسس البحث العلمي للبرامج الهندسية وتقنية المعلومات'],
            default => ['Higher Mathematics', 'Physics', 'Mathematical Analysis', 'Analytical Geometry', 'Probability Theory and Mathematical Statistics', 'Scientific Research Foundations for Engineering and IT Programs'],
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Matematika va fizikani muhandislik ta’limi bilan integratsiya qilish.', 'Talabalarda analitik va mantiqiy fikrlash ko‘nikmalarini rivojlantirish.', 'Energetika, mexanika, optika va atom fizikasi bo‘yicha ilmiy-metodik tadqiqotlar.', 'Aniq fanlarni o‘qitishda raqamli vositalar va zamonaviy laboratoriya yondashuvlaridan foydalanish.'],
            'ru' => ['Интеграция математики и физики с инженерным образованием.', 'Развитие аналитического и логического мышления у студентов.', 'Научно-методические исследования в области энергетики, механики, оптики и атомной физики.', 'Использование цифровых инструментов и современных лабораторных подходов в преподавании точных наук.'],
            'ar' => ['دمج الرياضيات والفيزياء مع التعليم الهندسي.', 'تنمية مهارات التفكير التحليلي والمنطقي لدى الطلاب.', 'أبحاث علمية ومنهجية في الطاقة والميكانيكا والبصريات والفيزياء الذرية.', 'استخدام الأدوات الرقمية والمناهج المخبرية الحديثة في تدريس العلوم الدقيقة.'],
            default => ['Integration of mathematics and physics with engineering education.', 'Development of analytical and logical thinking skills among students.', 'Scientific and methodological research in energy, mechanics, optics, and atomic physics.', 'Use of digital tools and modern laboratory approaches in teaching exact sciences.'],
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
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function locales(): array
    {
        return ['en', 'uz', 'ru', 'ar'];
    }
};
