<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->localizeRussianStaffNames();
        $this->cleanLocalizedAcademicSections();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Intentionally not reversible: this migration fixes localized public content.
    }

    private function localizeRussianStaffNames(): void
    {
        if (! Schema::hasTable('staff_profile_translations')) {
            return;
        }

        $rows = DB::table('staff_profile_translations as ru')
            ->leftJoin('staff_profile_translations as en', function ($join) {
                $join->on('en.staff_profile_id', '=', 'ru.staff_profile_id')
                    ->where('en.locale', '=', 'en');
            })
            ->where('ru.locale', 'ru')
            ->get([
                'ru.id',
                'ru.full_name as ru_full_name',
                'ru.bio as ru_bio',
                'ru.office as ru_office',
                'en.full_name as en_full_name',
            ]);

        foreach ($rows as $row) {
            $sourceName = trim((string) ($row->en_full_name ?: $row->ru_full_name));
            $currentName = trim((string) $row->ru_full_name);
            $russianName = $this->russianPersonName($sourceName ?: $currentName);
            $bio = (string) $row->ru_bio;

            if ($bio !== '') {
                foreach (array_unique(array_filter([$sourceName, $currentName])) as $name) {
                    $bio = str_replace($name, $russianName, $bio);
                }
                $bio = str_replace('Он/она', 'Сотрудник', $bio);
            }

            $office = $this->cleanOffice((string) $row->ru_office, 'ru');

            DB::table('staff_profile_translations')->where('id', $row->id)->update([
                'full_name' => $russianName,
                'bio' => $bio,
                'office' => $office,
                'updated_at' => now(),
            ]);
        }

        DB::table('staff_profile_translations')
            ->where('locale', 'ar')
            ->where('office', 'like', '%Department Office%')
            ->orWhere(function ($query) {
                $query->where('locale', 'ar')->where('office', 'like', '%قسمfice%');
            })
            ->update(['office' => null, 'updated_at' => now()]);
    }

    private function cleanLocalizedAcademicSections(): void
    {
        foreach (['faculty_translations', 'department_translations'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'content_sections')) {
                continue;
            }

            DB::table($table)
                ->whereIn('locale', ['ru', 'ar'])
                ->whereNotNull('content_sections')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        $cleaned = $this->cleanJsonText((string) $row->content_sections, (string) $row->locale);
                        if ($cleaned !== $row->content_sections) {
                            DB::table($table)->where('id', $row->id)->update([
                                'content_sections' => $cleaned,
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });
        }
    }

    private function cleanJsonText(string $json, string $locale): string
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return $this->cleanSectionText($json, $locale);
        }

        $walker = function ($value) use (&$walker, $locale) {
            if (is_string($value)) {
                return $this->cleanSectionText($value, $locale);
            }

            if (is_array($value)) {
                return array_map($walker, $value);
            }

            return $value;
        };

        return json_encode($walker($decoded), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function cleanSectionText(string $text, string $locale): string
    {
        if ($locale === 'ru') {
            $text = str_replace([
                'About the Кафедра',
                'Кафедра Structure',
                'Scientific Activity',
                'Specialists Trained by the Кафедра',
                'International Cooperation',
                'Head of Кафедра',
                'Head of Department',
                'Office hours:',
                'Phone:',
                'Email:',
                'Faculty member',
                'Department Office',
                'Кафедраfice',
                'Bachelor\'s Degree',
                'Master\'s Degree',
                'Oil и gas',
                'oil и gas',
                'и gas',
                'и Gas',
                'и oil',
                'и Oil',
                'и natural',
                'и effective',
                'и industrial',
                'и industry',
                'и сервис',
                'технология и',
                'технологии и',
                'инженерия systems',
                'качество control',
            ], [
                'О кафедре',
                'Структура кафедры',
                'Научная деятельность',
                'Специалисты, подготавливаемые кафедрой',
                'Международное сотрудничество',
                'Заведующий кафедрой',
                'Заведующий кафедрой',
                'Часы приема:',
                'Телефон:',
                'Электронная почта:',
                'Преподаватель',
                '',
                '',
                'Бакалавриат',
                'Магистратура',
                'Нефть и газ',
                'нефть и газ',
                'и газ',
                'и газ',
                'и нефть',
                'и нефть',
                'и природный',
                'и эффективный',
                'и промышленный',
                'и промышленность',
                'и сервис',
                'технология и',
                'технологии и',
                'инженерные системы',
                'контроль качества',
            ], $text);

            return trim($text);
        }

        $text = str_replace([
            'Head of Department',
            'Office hours:',
            'Phone:',
            'Email:',
            'Faculty member',
            'Department Office',
            'قسمfice',
        ], [
            'رئيس القسم',
            'ساعات الاستقبال:',
            'الهاتف:',
            'البريد الإلكتروني:',
            'عضو هيئة تدريس',
            '',
            '',
        ], $text);

        return trim($text);
    }

    private function cleanOffice(string $office, string $locale): ?string
    {
        $office = trim($office);
        if ($office === '' || str_contains($office, 'Department Office') || str_contains($office, 'Кафедраfice')) {
            return null;
        }

        if ($locale === 'ru') {
            return str_replace([
                'Office hours:',
                'Daily',
                'Monday-Friday',
                'Monday-Saturday',
                'except Monday and Saturday',
            ], [
                'Часы приема:',
                'Ежедневно',
                'Понедельник-пятница',
                'Понедельник-суббота',
                'кроме понедельника и субботы',
            ], $office);
        }

        return $office;
    }

    private function russianPersonName(string $name): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', str_replace(['`', '’', 'ʻ'], "'", $name)));
        if ($clean === '') {
            return $name;
        }

        $exact = $this->russianExactNameMap()[$clean] ?? null;
        if ($exact) {
            return $exact;
        }

        $roleNames = [
            'phd senior lecturer' => 'Преподаватель со степенью доктора философии',
            'phd katta is a teacher' => 'Старший преподаватель со степенью доктора философии',
            'doctor of philosophy phd' => 'Доктор философии',
            'doctor of agricultural sciences dsc' => 'Доктор сельскохозяйственных наук',
            'texnika fanlari buyicha falsafa doktori phd' => 'Доктор философии по техническим наукам',
            'texnika fanlari falsafa doktori phd dotsent' => 'Доцент, доктор философии по техническим наукам',
            'qishloq xujaligi fanlari falsafa doktori phd dotsent' => 'Доцент, доктор философии по сельскохозяйственным наукам',
            'phd dotsent v.b' => 'Исполняющий обязанности доцента, доктор философии',
            'phd dotsent vb' => 'Исполняющий обязанности доцента, доктор философии',
        ];

        $lowerClean = Str::lower(preg_replace('/[.,]+/', '', $clean) ?? $clean);
        if (isset($roleNames[$lowerClean])) {
            return $roleNames[$lowerClean];
        }

        if (preg_match('/\p{Cyrillic}/u', $clean) === 1 && preg_match('/[A-Za-z]/', $clean) !== 1) {
            return $clean;
        }

        $titles = [
            '/^assoc\.\s*prof\.\s*/i' => 'доц. ',
            '/^prof\.\s*/i' => 'проф. ',
            '/^dr\.\s*/i' => 'д-р ',
            '/^phd,?\s*/i' => 'PhD ',
        ];

        $prefix = '';
        foreach ($titles as $pattern => $replacement) {
            if (preg_match($pattern, $clean)) {
                $prefix = $replacement;
                $clean = preg_replace($pattern, '', $clean);
                break;
            }
        }

        $parts = preg_split('/\s+/', trim($clean)) ?: [];
        $translated = array_map(fn (string $part): string => $this->russianWord($part), $parts);
        $translated = array_values(array_filter($translated));

        return trim($prefix.implode(' ', $translated)) ?: $name;
    }

    private function russianExactNameMap(): array
    {
        return [
            'Dr. Adizov Rashid Tokhtayevich' => 'д-р Адизов Рашид Тухтаевич',
            'Safarov Jasur Alijon o‘g‘li' => 'Сафаров Жасур Алижон угли',
            "Safarov Jasur Alijon o'g'li" => 'Сафаров Жасур Алижон угли',
            'Bozorov Dilmurod Kholmurodovich' => 'Бозоров Дилмурод Холмуродович',
            'Dr. Khojiyev Aziz Kholmurodovich' => 'д-р Ходжиев Азиз Холмуродович',
            'Xojiyev Aziz Xolmurodovich' => 'Ходжиев Азиз Холмуродович',
            'Rustamov Bobir Ismatovich' => 'Рустамов Бобир Исматович',
            'Ashurov Asrorjon Komilovich' => 'Ашуров Асроржон Комилович',
            'Qobulova Barno Bakhriddin qizi' => 'Кобулова Барно Бахриддин кизи',
            'Gadoyeva Abera Hasanovna' => 'Гадоева Абера Хасановна',
            'Khayitov Sherbek Nayimovich' => 'Хайитов Шербек Наимович',
            'Boboqulov Farxod Baxtiyorivich' => 'Бобокулов Фарход Бахтиёрович',
            'Fayzullayev Asqar Rajabboevich' => 'Файзуллаев Аскар Ражаббоевич',
        ];
    }

    private function russianWord(string $word): string
    {
        $word = trim($word, " \t\n\r\0\x0B.,;:()[]{}");
        if ($word === '') {
            return '';
        }

        $lower = Str::lower($word);
        $special = [
            "o'g'li" => 'угли',
            "o‘g‘li" => 'угли',
            'ogli' => 'угли',
            'oglu' => 'угли',
            'qizi' => 'кизи',
            'kizi' => 'кизи',
            'phd' => 'доктор философии',
            'dsc' => 'доктор наук',
            'senior' => 'старший',
            'lecturer' => 'преподаватель',
            'assistant' => 'ассистент',
            'doctor' => 'доктор',
            'philosophy' => 'философии',
            'agricultural' => 'сельскохозяйственных',
            'sciences' => 'наук',
        ];

        if (isset($special[$lower])) {
            return $special[$lower];
        }

        $map = [
            "yo" => "ё", "yu" => "ю", "ya" => "я", "ye" => "е",
            "kh" => "х", "sh" => "ш", "ch" => "ч", "ts" => "ц",
            "zh" => "ж", "dj" => "дж", "g'" => "г", "g‘" => "г",
            "o'" => "у", "o‘" => "у", "'" => "",
            "a" => "а", "b" => "б", "c" => "к", "d" => "д", "e" => "е",
            "f" => "ф", "g" => "г", "h" => "х", "i" => "и", "j" => "ж",
            "k" => "к", "l" => "л", "m" => "м", "n" => "н", "o" => "о",
            "p" => "п", "q" => "к", "r" => "р", "s" => "с", "t" => "т",
            "u" => "у", "v" => "в", "w" => "в", "x" => "х", "y" => "й", "z" => "з",
        ];

        uksort($map, fn ($a, $b) => strlen($b) <=> strlen($a));
        $result = strtr($lower, $map);

        return Str::ucfirst($result);
    }
};
