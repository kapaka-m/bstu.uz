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

        $departmentId = DB::table('departments')->where('slug', 'social-sciences-physical-culture')->value('id');
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
            'head_name' => 'Murodov Sanjar Aslonovich',
            'phone' => '+998 91 416 75 10',
            'email' => null,
            'reception_time' => 'Daily 14:00-16:00',
            'updated_at' => now(),
        ]);
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteSlugs = [
            'social-sciences-physical-culture-ijtimoiy-fanlar-va-jismoniy-madaniyat',
            'social-sciences-physical-culture-0-dr-zebo-a-hamidova',
            'social-sciences-physical-culture-1-sobir-b-karimov',
            'social-sciences-physical-culture-2-guli-k-saidova',
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
                'phone' => '+998 91 416 75 10',
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
            $translation = DB::table('department_translations')->where('department_id', $departmentId)->where('locale', $locale)->first();
            if (! $translation) {
                continue;
            }

            $sections = json_decode((string) $translation->content_sections, true);
            if (! is_array($sections)) {
                $sections = [];
            }

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

            DB::table('department_translations')->where('id', $translation->id)->update([
                'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    private function programsText(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra universitetning barcha bakalavriat yo‘nalishlarida umumta’lim va tarbiyaviy fanlarni o‘qitadi.',
            'ru' => 'Кафедра преподает общеобразовательные и воспитательные дисциплины на всех направлениях бакалавриата университета.',
            'ar' => 'يدرّس القسم مواد التعليم العام والتربية في جميع برامج البكالوريوس بالجامعة.',
            default => 'The department teaches general education and personal development subjects across all bachelor programs of the university.',
        };
    }

    private function subjectsText(string $locale): string
    {
        return 'Bachelor subjects:'."\n".collect($this->subjects($locale))->map(fn (string $item, int $index) => ($index + 1).'. '.$item)->implode("\n");
    }

    private function subjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['O‘zbekistonning eng yangi tarixi', 'Falsafa', 'Dinshunoslik', 'O‘zbekiston Respublikasi Konstitutsiyasi yangi tahrirda', 'Jismoniy madaniyat', 'Sog‘lom turmush tarzi asoslari'],
            'ru' => ['Новейшая история Узбекистана', 'Философия', 'Религиоведение', 'Конституция Республики Узбекистан в новой редакции', 'Физическая культура', 'Основы здорового образа жизни'],
            'ar' => ['التاريخ الحديث لأوزبكستان', 'الفلسفة', 'دراسات الأديان', 'دستور جمهورية أوزبكستان في صيغته الجديدة', 'التربية البدنية', 'أساسيات نمط الحياة الصحي'],
            default => ['Modern History of Uzbekistan', 'Philosophy', 'Religious Studies', 'Constitution of the Republic of Uzbekistan in the New Edition', 'Physical Culture', 'Fundamentals of Healthy Lifestyle'],
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
            'uz' => ['Buxoroning XIX asr oxiri va XX asr boshlaridagi ijtimoiy-siyosiy va ma’naviy holatini tadqiq qilish.', 'Barkamol shaxsni shakllantirishning falsafiy-metodologik muammolarini o‘rganish.', 'Mustaqillik sharoitida jamiyatni demokratlashtirish masalalarini tahlil qilish.', 'Yoshlar bag‘rikengligini shakllantirishda jadidchilik g‘oyalarining tarixiy-falsafiy tahlili.', 'Yoshlar orasida sog‘lom turmush tarzi va jismoniy madaniyatni rivojlantirish.'],
            'ru' => ['Исследование социально-политической и духовной ситуации в Бухаре в конце XIX - начале XX века.', 'Изучение философско-методологических проблем формирования гармонично развитой личности.', 'Анализ вопросов демократизации общества в условиях независимости.', 'Историко-философский анализ идей джадидизма в формировании толерантности молодежи.', 'Развитие здорового образа жизни и физической культуры среди молодежи.'],
            'ar' => ['دراسة الوضع الاجتماعي والسياسي والروحي في بخارى في أواخر القرن التاسع عشر وبداية القرن العشرين.', 'بحث المشكلات الفلسفية والمنهجية لتكوين الشخصية المتكاملة.', 'تحليل قضايا دمقرطة المجتمع في ظروف الاستقلال.', 'تحليل تاريخي وفلسفي لأفكار الجاديدية في تكوين التسامح لدى الشباب.', 'تنمية نمط الحياة الصحي والثقافة البدنية بين الشباب.'],
            default => ['Research on the socio-political and spiritual situation in Bukhara in the late 19th and early 20th centuries.', 'Study of philosophical and methodological problems in forming a well-rounded individual.', 'Analysis of democratization issues under conditions of independence.', 'Historical and philosophical analysis of Jadidism ideas in forming youth tolerance.', 'Development of healthy lifestyle and physical culture among youth.'],
        };
    }

    private function headProfile(): array
    {
        return [
            'slug' => 'social-sciences-physical-culture-murodov-sanjar-aslonovich',
            'names' => [
                'en' => 'Murodov Sanjar Aslonovich',
                'uz' => 'Murodov Sanjar Aslonovich',
                'ru' => 'Муродов Санжар Аслонович',
                'ar' => 'مورودوف سنجر أسلونوفيتش',
            ],
        ];
    }

    private function position(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra mudiri',
            'ru' => 'Заведующий кафедрой',
            'ar' => 'رئيس القسم',
            default => 'Head of Department',
        };
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Ijtimoiy fanlar va jismoniy madaniyat kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре социальных наук и физической культуры в должности «{$position}».",
            'ar' => "{$name} يعمل في قسم العلوم الاجتماعية والثقافة البدنية بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Social Sciences and Physical Culture.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
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
