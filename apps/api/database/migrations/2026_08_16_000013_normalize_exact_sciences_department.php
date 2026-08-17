<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments') || ! Schema::hasTable('staff_profiles')) {
            return;
        }

        $departmentId = DB::table('departments')->where('slug', 'exact-sciences')->value('id');
        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeDepartment((int) $departmentId);
            $this->normalizeStaff((int) $departmentId);
            $this->normalizeSections((int) $departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function normalizeDepartment(int $departmentId): void
    {
        DB::table('departments')->where('id', $departmentId)->update([
            'head_name' => 'Kasimova Guzal Karimovna',
            'phone' => '+998 94 490 22 90',
            'email' => null,
            'reception_time' => 'Daily 14:00-16:00',
            'updated_at' => now(),
        ]);
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteSlugs = [
            'exact-sciences-0-dr-lola-j-toshova',
            'exact-sciences-1-barno-b-sodiqova',
            'exact-sciences-2-maxmud-o-safarov',
        ];

        $ids = DB::table('staff_profiles')->whereIn('slug', $deleteSlugs)->pluck('id')->all();
        if ($ids !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
            DB::table('staff_profiles')->whereIn('id', $ids)->delete();
        }

        $profile = $this->headProfile();
        $profileId = DB::table('staff_profiles')->where('slug', $profile['slug'])->value('id');
        if (! $profileId) {
            $profileId = DB::table('staff_profiles')->insertGetId([
                'slug' => $profile['slug'],
                'department_id' => $departmentId,
                'faculty_id' => DB::table('departments')->where('id', $departmentId)->value('faculty_id'),
                'photo' => null,
                'email' => null,
                'phone' => '+998 94 490 22 90',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('staff_profiles')->where('id', $profileId)->update([
            'department_id' => $departmentId,
            'sort_order' => 10,
            'is_active' => true,
            'updated_at' => now(),
        ]);

        foreach ($this->locales() as $locale) {
            $name = $profile['names'][$locale] ?? $profile['names']['en'];
            $position = $this->position($locale);
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

    private function normalizeSections(int $departmentId): void
    {
        if (! Schema::hasTable('department_translations')) {
            return;
        }

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

            $sections = $this->replaceSection($sections, [
                'key' => 'overview',
                'title' => $this->label($locale, 'overview'),
                'items' => [$this->overview($locale)],
            ]);
            $sections = $this->replaceSection($sections, [
                'key' => 'prepared_specialists',
                'title' => $this->label($locale, 'programs'),
                'items' => [$this->programsText($locale)],
            ]);
            $sections = $this->replaceSection($sections, [
                'key' => 'subjects',
                'title' => $this->label($locale, 'subjects'),
                'items' => [$this->subjectsText($locale)],
            ]);
            $sections = $this->replaceSection($sections, [
                'key' => 'staff',
                'title' => $this->label($locale, 'staff'),
                'items' => [$this->staffText($locale)],
            ]);
            $sections = $this->replaceSection($sections, [
                'key' => 'research',
                'title' => $this->label($locale, 'research'),
                'items' => $this->researchItems($locale),
            ]);

            $sections = array_values(array_filter($sections, function (array $section): bool {
                $key = $section['key'] ?? '';
                return ! str_contains($key, 'structure') && $key !== 'department_structure';
            }));

            DB::table('department_translations')->where('id', $translation->id)->update([
                'content_sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    private function overview(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra muhandislik va IT yo‘nalishlari uchun matematika, fizika, mantiqiy fikrlash, analitik usullar va ilmiy tadqiqot asoslarini shakllantiradi.',
            'ru' => 'Кафедра формирует основы математики, физики, логического мышления, аналитических методов и научных исследований для инженерных и IT-направлений.',
            'ar' => 'يوفر القسم الأسس العلمية في الرياضيات والفيزياء والتفكير المنطقي والأساليب التحليلية والبحث العلمي لبرامج الهندسة وتقنية المعلومات.',
            default => 'The department provides foundations in mathematics, physics, logical thinking, analytical methods, and scientific research for engineering and IT fields.',
        };
    }

    private function programsText(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra universitetning muhandislik, texnologiya va raqamli yo‘nalishlarida tayanch aniq fanlarni o‘qitadi.',
            'ru' => 'Кафедра преподает базовые точные науки для инженерных, технологических и цифровых направлений университета.',
            'ar' => 'يدرّس القسم العلوم الدقيقة الأساسية لبرامج الهندسة والتكنولوجيا والاتجاهات الرقمية في الجامعة.',
            default => 'The department teaches foundational exact-science disciplines for engineering, technology, and digital programs of the university.',
        };
    }

    private function subjectsText(string $locale): string
    {
        return 'Bachelor subjects:'."\n".collect($this->subjects($locale))
            ->map(fn (string $item, int $index) => ($index + 1).'. '.$item)
            ->implode("\n");
    }

    private function subjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Oliy matematika', 'Fizika', 'Matematik tahlil', 'Analitik geometriya', 'Ehtimollar nazariyasi va matematik statistika', 'Muhandislik va IT yo‘nalishlari uchun ilmiy tadqiqot asoslari'],
            'ru' => ['Высшая математика', 'Физика', 'Математический анализ', 'Аналитическая геометрия', 'Теория вероятностей и математическая статистика', 'Основы научных исследований для инженерных и IT-направлений'],
            'ar' => ['الرياضيات العليا', 'الفيزياء', 'التحليل الرياضي', 'الهندسة التحليلية', 'نظرية الاحتمالات والإحصاء الرياضي', 'أساسيات البحث العلمي لبرامج الهندسة وتقنية المعلومات'],
            default => ['Higher Mathematics', 'Physics', 'Mathematical Analysis', 'Analytical Geometry', 'Probability Theory and Mathematical Statistics', 'Scientific Research Foundations for Engineering and IT Programs'],
        };
    }

    private function staffText(string $locale): string
    {
        $profile = $this->headProfile();
        $name = $profile['names'][$locale] ?? $profile['names']['en'];
        return $name."\n".$this->position($locale);
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Matematika va fizika fanlarini muhandislik ta’limi bilan integratsiya qilish.', 'Talabalarda analitik va mantiqiy fikrlash ko‘nikmalarini rivojlantirish.', 'Energiya, mexanika, optika va atom fizikasi yo‘nalishlarida ilmiy-uslubiy izlanishlar.', 'Aniq fanlarni o‘qitishda raqamli vositalar va zamonaviy laboratoriya yondashuvlarini joriy etish.'],
            'ru' => ['Интеграция математики и физики с инженерным образованием.', 'Развитие аналитического и логического мышления у студентов.', 'Научно-методические исследования в области энергетики, механики, оптики и атомной физики.', 'Внедрение цифровых инструментов и современных лабораторных подходов в преподавание точных наук.'],
            'ar' => ['دمج الرياضيات والفيزياء مع التعليم الهندسي.', 'تنمية مهارات التفكير التحليلي والمنطقي لدى الطلاب.', 'إجراء بحوث علمية ومنهجية في مجالات الطاقة والميكانيكا والبصريات والفيزياء الذرية.', 'تطبيق الأدوات الرقمية والمناهج المخبرية الحديثة في تدريس العلوم الدقيقة.'],
            default => ['Integration of mathematics and physics with engineering education.', 'Development of analytical and logical thinking skills among students.', 'Scientific and methodological research in energy, mechanics, optics, and atomic physics.', 'Use of digital tools and modern laboratory approaches in teaching exact sciences.'],
        };
    }

    private function headProfile(): array
    {
        return [
            'slug' => 'exact-sciences-kasimova-guzal-karimovna',
            'names' => [
                'en' => 'Kasimova Guzal Karimovna',
                'uz' => 'Kasimova Guzal Karimovna',
                'ru' => 'Касимова Гузаль Каримовна',
                'ar' => 'كاسيموفا غوزال كريموفنا',
            ],
        ];
    }

    private function position(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra mudiri',
            'ru' => 'Заведующая кафедрой',
            'ar' => 'رئيسة القسم',
            default => 'Head of Department',
        };
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Aniq fanlar kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре точных наук в должности «{$position}».",
            'ar' => "{$name} تعمل في قسم العلوم الدقيقة بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Exact Sciences.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'overview' => ['en' => 'About the Department', 'uz' => 'Kafedra haqida', 'ru' => 'О кафедре', 'ar' => 'عن القسم'],
            'programs' => ['en' => 'Prepared Specialists', 'uz' => 'Tayyorlanadigan yo‘nalishlar', 'ru' => 'Подготавливаемые направления', 'ar' => 'المجالات التي يخدمها القسم'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'Kafedrada o‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد التي تدرس في القسم'],
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'research' => ['en' => 'Ongoing Research', 'uz' => 'Joriy ilmiy tadqiqotlar', 'ru' => 'Текущие исследования', 'ar' => 'الأبحاث الجارية'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
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
