<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'uzbek-foreign-languages')->value('id');

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
            'uz' => 'Kafedra talabalarning ona tili bo‘yicha kompetensiyasi, xorijiy tillarda erkin muloqoti, tarjima, akademik yozuv va kasbiy kommunikatsiya ko‘nikmalarini rivojlantiradi.',
            'ru' => 'Кафедра развивает у студентов компетенции родного языка, свободное владение иностранными языками, перевод, академическое письмо и навыки профессиональной коммуникации.',
            'ar' => 'يطوّر القسم كفاءة الطلاب في اللغة الأم والطلاقة في اللغات الأجنبية والترجمة والكتابة الأكاديمية ومهارات التواصل المهني.',
            default => 'The department develops students’ native-language competence, foreign-language fluency, translation, academic writing, and professional communication skills.',
        };
    }

    private function preparedText(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra universitet dasturlari bo‘yicha til, tarjima, akademik yozuv va kasbiy kommunikatsiya fanlarini o‘qitadi.',
            'ru' => 'Кафедра преподает языковые дисциплины, перевод, академическое письмо и профессиональную коммуникацию по программам университета.',
            'ar' => 'يدرّس القسم مقررات اللغة والترجمة والكتابة الأكاديمية والتواصل المهني في برامج الجامعة.',
            default => 'The department teaches language, translation, academic writing, and professional communication courses across the university programs.',
        };
    }

    private function subjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['O‘zbek tili', 'Rus tili', 'Ingliz tili', 'Nemis tili', 'Fransuz tili', 'Akademik yozuv', 'Tarjima nazariyasi va amaliyoti', 'Kasbiy kommunikatsiya'],
            'ru' => ['Узбекский язык', 'Русский язык', 'Английский язык', 'Немецкий язык', 'Французский язык', 'Академическое письмо', 'Теория и практика перевода', 'Профессиональная коммуникация'],
            'ar' => ['اللغة الأوزبكية', 'اللغة الروسية', 'اللغة الإنجليزية', 'اللغة الألمانية', 'اللغة الفرنسية', 'الكتابة الأكاديمية', 'نظرية الترجمة وتطبيقاتها', 'التواصل المهني'],
            default => ['Uzbek Language', 'Russian Language', 'English Language', 'German Language', 'French Language', 'Academic Writing', 'Translation Theory and Practice', 'Professional Communication'],
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Xorijiy tillarni o‘qitish metodikasi va kommunikativ kompetensiyani rivojlantirish.', 'Tarjimashunoslik, qiyosiy adabiyotshunoslik va lingvistik tahlil bo‘yicha tadqiqotlar.', 'O‘zbek va xorijiy tillarda akademik yozuv ko‘nikmalarini takomillashtirish.', 'Kasbiy kommunikatsiya va terminologiyani o‘qitishning zamonaviy metodlarini joriy etish.'],
            'ru' => ['Методика преподавания иностранных языков и развитие коммуникативной компетенции.', 'Исследования в области переводоведения, сравнительного литературоведения и лингвистического анализа.', 'Совершенствование навыков академического письма на узбекском и иностранных языках.', 'Внедрение современных методов преподавания профессиональной коммуникации и терминологии.'],
            'ar' => ['منهجية تدريس اللغات الأجنبية وتطوير الكفاءة التواصلية.', 'أبحاث في دراسات الترجمة والأدب المقارن والتحليل اللغوي.', 'تحسين مهارات الكتابة الأكاديمية باللغتين الأوزبكية واللغات الأجنبية.', 'تطبيق طرائق حديثة لتدريس التواصل المهني والمصطلحات.'],
            default => ['Methodology of foreign-language teaching and development of communicative competence.', 'Research in translation studies, comparative literature, and linguistic analysis.', 'Improvement of academic writing skills in Uzbek and foreign languages.', 'Implementation of modern methods for teaching professional communication and terminology.'],
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
