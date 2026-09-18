<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CompleteContentLocalizationSeeder extends Seeder
{
    public function run(): void
    {
        $dictionary = json_decode(file_get_contents(database_path('data/localization_repairs.json')), true, flags: JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($dictionary) {
            foreach (Schema::getTableListing() as $table) {
                if (! str_ends_with($table, '_translations') && ! str_ends_with($table, 'translation_values')) {
                    continue;
                }
                $columns = Schema::getColumnListing($table);
                if (! in_array('locale', $columns, true)) {
                    continue;
                }
                $fields = array_filter($columns, fn ($field) => ! in_array($field, ['id', 'locale', 'created_at', 'updated_at'], true) && ! str_ends_with($field, '_id'));
                foreach (DB::table($table)->whereIn('locale', ['uz', 'ru', 'ar'])->get() as $row) {
                    $changes = [];
                    foreach ($fields as $field) {
                        if (! is_string($row->$field)) {
                            continue;
                        }
                        $fixed = $this->translateValue($row->$field, $row->locale, $dictionary);
                        if ($fixed !== $row->$field) {
                            $changes[$field] = $fixed;
                        }
                    }
                    if ($changes) {
                        DB::table($table)->where('id', $row->id)->update($changes + ['updated_at' => now()]);
                    }
                }
            }
            $this->repairStaff();
            $this->repairCourses();
            $this->reuseReviewedUiTranslations();
        });
        Cache::forever('public_content_cache_version', (string) Str::uuid());
    }

    private function translateValue(string $value, string $locale, array $dictionary): string
    {
        $index = array_search($locale, ['uz', 'ru', 'ar'], true);
        if (isset($dictionary[$value][$index])) {
            return $dictionary[$value][$index];
        }

        // CMS JSON contains both prose and structural values. Replace exact reviewed text only.
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $before = $decoded;
            array_walk_recursive($decoded, function (&$item) use ($dictionary, $index) {
                if (is_string($item) && isset($dictionary[$item][$index])) {
                    $item = $dictionary[$item][$index];
                }
            });
            if ($before !== $decoded) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            }
        }

        return $value;
    }

    private function repairStaff(): void
    {
        $departments = DB::table('department_translations')->get()->groupBy('department_id');
        $profiles = DB::table('staff_profiles')->get()->keyBy('id');
        $english = DB::table('staff_profile_translations')->where('locale', 'en')->get()->keyBy('staff_profile_id');
        $contributions = [
            'teaching, research, and academic development' => ['ta’lim, tadqiqot va akademik rivojlanishga', 'в преподавание, исследования и академическое развитие', 'في التدريس والبحث والتطوير الأكاديمي'],
            'teaching, laboratory work, and academic development' => ['ta’lim, laboratoriya ishlari va akademik rivojlanishga', 'в преподавание, лабораторную работу и академическое развитие', 'في التدريس والعمل المخبري والتطوير الأكاديمي'],
            'teaching, laboratory work, research, and academic development' => ['ta’lim, laboratoriya ishlari, tadqiqot va akademik rivojlanishga', 'в преподавание, лабораторную работу, исследования и академическое развитие', 'في التدريس والعمل المخبري والبحث والتطوير الأكاديمي'],
            'teaching, field practice, research, and academic development' => ['ta’lim, amaliyot, tadqiqot va akademik rivojlanishga', 'в преподавание, производственную практику, исследования и академическое развитие', 'في التدريس والتدريب الميداني والبحث والتطوير الأكاديمي'],
            'teaching, laboratory practice, quality control, and academic development' => ['ta’lim, laboratoriya amaliyoti, sifat nazorati va akademik rivojlanishga', 'в преподавание, лабораторную практику, контроль качества и академическое развитие', 'في التدريس والتدريب المخبري وضبط الجودة والتطوير الأكاديمي'],
            'academic, methodological, and research development' => ['akademik, uslubiy va ilmiy rivojlanishga', 'в академическое, методическое и научное развитие', 'في التطوير الأكاديمي والمنهجي والبحثي'],
        ];
        foreach (DB::table('staff_profile_translations')->whereIn('locale', ['uz', 'ru', 'ar'])->get() as $row) {
            $changes = [];
            $departmentId = $profiles[$row->staff_profile_id]->department_id ?? null;
            $department = ($departments[$departmentId] ?? collect())->firstWhere('locale', $row->locale);
            if ($department && preg_match('/ serves as .+ in .+, contributing to (.+)\.$/u', $row->bio ?? '', $match) && isset($contributions[$match[1]])) {
                $parts = $contributions[$match[1]];
                $changes['bio'] = match ($row->locale) {
                    'uz' => "{$row->full_name} {$department->name} kafedrasida {$row->position} lavozimida faoliyat yuritib, {$parts[0]} hissa qo‘shadi.",
                    'ru' => "{$row->full_name} работает на кафедре «{$department->name}» в должности «{$row->position}» и вносит вклад {$parts[1]}.",
                    'ar' => "{$row->full_name} يعمل في قسم {$department->name} بصفة {$row->position}، ويسهم {$parts[2]}.",
                };
            }
            $office = trim($row->office ?? '');
            if ($office === '' && ($english[$row->staff_profile_id]->office ?? '') === 'Department Office') {
                $changes['office'] = match ($row->locale) {
                    'uz' => 'Kafedra xonasi', 'ru' => 'Кабинет кафедры', 'ar' => 'مكتب القسم',
                };
            } elseif (preg_match('/^(Monday-Friday|Monday-Saturday|Daily) (\d{1,2}:\d{2}-\d{1,2}:\d{2})$/', $office, $match)) {
                $days = [
                    'Monday-Friday' => ['Dushanba-juma', 'Понедельник-пятница', 'الاثنين-الجمعة'],
                    'Monday-Saturday' => ['Dushanba-shanba', 'Понедельник-суббота', 'الاثنين-السبت'],
                    'Daily' => ['Har kuni', 'Ежедневно', 'يومياً'],
                ];
                $changes['office'] = $days[$match[1]][array_search($row->locale, ['uz', 'ru', 'ar'], true)].' '.$match[2];
            }
            if ($changes) {
                DB::table('staff_profile_translations')->where('id', $row->id)->update($changes + ['updated_at' => now()]);
            }
        }
    }

    private function repairCourses(): void
    {
        foreach (DB::table('course_translations')->whereIn('locale', ['uz', 'ru', 'ar'])->get() as $row) {
            if (str_starts_with($row->description ?? '', 'Standard course covering topics in ')) {
                $description = match ($row->locale) {
                    'uz' => "«{$row->name}» fanining mavzularini qamrab oluvchi asosiy kurs.",
                    'ru' => "Базовый курс, охватывающий темы дисциплины «{$row->name}».",
                    'ar' => "مقرر أساسي يتناول موضوعات «{$row->name}».",
                };
                DB::table('course_translations')->where('id', $row->id)->update(['description' => $description, 'updated_at' => now()]);
            }
        }
    }

    private function reuseReviewedUiTranslations(): void
    {
        $groups = DB::table('translation_values')->get()->groupBy('translation_key_id');
        $candidates = [];
        foreach ($groups as $rows) {
            $source = $rows->firstWhere('locale', 'en')?->value;
            if (! $source) {
                continue;
            }
            foreach ($rows as $row) {
                if ($row->locale !== 'en' && filled($row->value) && $row->value !== $source) {
                    $candidates[$source][$row->locale][] = $row->value;
                }
            }
        }
        foreach ($groups as $rows) {
            $source = $rows->firstWhere('locale', 'en')?->value;
            foreach ($rows as $row) {
                $values = array_unique($candidates[$source][$row->locale] ?? []);
                // Ambiguous translations require editorial review, not an arbitrary first match.
                if ($row->locale !== 'en' && $row->value === $source && count($values) === 1) {
                    DB::table('translation_values')->where('id', $row->id)->update(['value' => reset($values), 'updated_at' => now()]);
                }
            }
        }
    }
}
