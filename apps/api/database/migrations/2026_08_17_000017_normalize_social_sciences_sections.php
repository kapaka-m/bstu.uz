<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'social-sciences-physical-culture')->value('id');

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
            'uz' => 'Kafedra ijtimoiy fanlar, axloqiy qadriyatlar, fuqarolik tarbiyasi, sog‘lom turmush tarzi va jismoniy tarbiya orqali har tomonlama shaxsiy rivojlanishni qo‘llab-quvvatlaydi.',
            'ru' => 'Кафедра поддерживает всестороннее развитие личности через социальные науки, этические ценности, гражданское воспитание, здоровый образ жизни и физическое воспитание.',
            'ar' => 'يدعم القسم التنمية الشخصية المتكاملة من خلال العلوم الاجتماعية، القيم الأخلاقية، التربية المدنية، نمط الحياة الصحي، والتربية البدنية.',
            default => 'The department supports well-rounded personal development through social sciences, ethical values, civic education, healthy lifestyle, and physical education.',
        };
    }

    private function preparedText(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra universitetning barcha bakalavriat dasturlarida umumta’lim va shaxsiy rivojlanish fanlarini o‘qitadi.',
            'ru' => 'Кафедра преподает общеобразовательные дисциплины и предметы личностного развития на всех программах бакалавриата университета.',
            'ar' => 'يدرّس القسم مواد التعليم العام والتنمية الشخصية في جميع برامج البكالوريوس بالجامعة.',
            default => 'The department teaches general education and personal development subjects across all bachelor programs of the university.',
        };
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

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Buxoroning XIX asr oxiri va XX asr boshlaridagi ijtimoiy-siyosiy va ma’naviy holatini tadqiq qilish.', 'Barkamol shaxsni shakllantirishning falsafiy-metodologik muammolarini o‘rganish.', 'Mustaqillik sharoitida demokratlashtirish masalalarini tahlil qilish.', 'Yoshlar bag‘rikengligini shakllantirishda jadidchilik g‘oyalarining tarixiy-falsafiy tahlili.', 'Yoshlar orasida sog‘lom turmush tarzi va jismoniy madaniyatni rivojlantirish.'],
            'ru' => ['Исследование социально-политической и духовной ситуации в Бухаре в конце XIX - начале XX века.', 'Изучение философско-методологических проблем формирования гармонично развитой личности.', 'Анализ вопросов демократизации в условиях независимости.', 'Историко-философский анализ идей джадидизма в формировании толерантности молодежи.', 'Развитие здорового образа жизни и физической культуры среди молодежи.'],
            'ar' => ['دراسة الوضع الاجتماعي والسياسي والروحي في بخارى في أواخر القرن التاسع عشر وبداية القرن العشرين.', 'بحث المشكلات الفلسفية والمنهجية في تكوين الشخصية المتكاملة.', 'تحليل قضايا الدمقرطة في ظروف الاستقلال.', 'تحليل تاريخي وفلسفي لأفكار الجاديدية في تكوين التسامح لدى الشباب.', 'تنمية نمط الحياة الصحي والثقافة البدنية بين الشباب.'],
            default => ['Research on the socio-political and spiritual situation in Bukhara in the late 19th and early 20th centuries.', 'Study of philosophical and methodological problems in forming a well-rounded individual.', 'Analysis of democratization issues under conditions of independence.', 'Historical and philosophical analysis of Jadidism ideas in forming youth tolerance.', 'Development of healthy lifestyle and physical culture among youth.'],
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
