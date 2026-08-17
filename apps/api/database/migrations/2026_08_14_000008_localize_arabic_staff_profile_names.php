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
        if (! Schema::hasTable('staff_profile_translations')) {
            return;
        }

        $rows = DB::table('staff_profile_translations as ar')
            ->leftJoin('staff_profile_translations as en', function ($join) {
                $join->on('en.staff_profile_id', '=', 'ar.staff_profile_id')
                    ->where('en.locale', '=', 'en');
            })
            ->where('ar.locale', 'ar')
            ->get([
                'ar.id',
                'ar.full_name as ar_full_name',
                'ar.bio as ar_bio',
                'en.full_name as en_full_name',
                'en.position as en_position',
            ]);

        foreach ($rows as $row) {
            $sourceName = trim((string) ($row->en_full_name ?: $row->ar_full_name));
            $currentName = trim((string) $row->ar_full_name);

            if ($sourceName === '' || $this->hasArabic($currentName)) {
                continue;
            }

            $arabicName = $this->arabicPersonName($sourceName, (string) $row->en_position);
            $bio = (string) $row->ar_bio;

            if ($bio !== '') {
                $bio = str_replace($sourceName, $arabicName, $bio);
                if ($currentName !== '' && $currentName !== $sourceName) {
                    $bio = str_replace($currentName, $arabicName, $bio);
                }
            }

            DB::table('staff_profile_translations')->where('id', $row->id)->update([
                'full_name' => $arabicName,
                'bio' => $bio,
                'updated_at' => now(),
            ]);
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Intentionally not reversible: this migration fixes localized Arabic content.
    }

    private function hasArabic(string $value): bool
    {
        return preg_match('/\p{Arabic}/u', $value) === 1;
    }

    private function arabicPersonName(string $name, string $position = ''): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', str_replace(['`', '’', 'ʻ'], "'", $name)));
        $mapped = $this->exactNameMap()[$clean] ?? null;
        if ($mapped) {
            return $mapped;
        }

        $lower = Str::lower($clean);

        $roleNames = [
            'to be entered' => 'سيتم الإدخال',
            'senior lecturer' => 'محاضر أول',
            'senior teacher' => 'محاضر أول',
            'lecturer trainee' => 'محاضر متدرب',
            'teacher trainee' => 'مدرس متدرب',
            'doctoral student' => 'باحث دكتوراه',
            'basic doctoral student' => 'باحث دكتوراه أساسي',
            'phd, senior lecturer' => 'دكتوراه، محاضر أول',
            'doctor of philosophy (phd)' => 'دكتوراه',
            'doctor of agricultural sciences (dsc)' => 'دكتور علوم زراعية',
            'doctor of technical sciences' => 'دكتور علوم تقنية',
            'doctor of philosophy in technical sciences, docent.' => 'دكتوراه في العلوم التقنية، أستاذ مشارك',
        ];

        if (isset($roleNames[$lower])) {
            return $roleNames[$lower];
        }

        $titles = [
            '/^dr\.\s*/i' => 'د. ',
            '/^prof\.\s*/i' => 'أ. ',
            '/^assoc\.\s*prof\.\s*/i' => 'أ.م. ',
            '/^phd,?\s*/i' => 'د. ',
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
        $translated = array_map(fn (string $part): string => $this->transliterateWord($part), $parts);
        $translated = array_values(array_filter($translated));

        return trim($prefix.implode(' ', $translated)) ?: ($position ? 'عضو هيئة تدريس' : $name);
    }

    private function exactNameMap(): array
    {
        return [
            'Dr. Khojiyev Aziz Kholmurodovich' => 'د. خوجييف عزيز خولمورودوفيتش',
            'Xojiyev Aziz Xolmurodovich' => 'خوجييف عزيز خولمورودوفيتش',
            'Rustamov Bobir Ismatovich' => 'رستاموف بوبير إسماتوفيتش',
            'Ashurov Asrorjon Komilovich' => 'أشوروف أسرورجون كوميلوفيتش',
            'Latipov Saidmurod Tuygunovich' => 'لاتيبوف سعيد مراد تويغونوفيتش',
            'Mirzayev Shamsiddin Rajabovich' => 'ميرزاييف شمس الدين رجبوفيتش',
            "Tojiyev In'omjon Ilhomovich" => 'توجييف إنعام جون إلهوموفيتش',
            'Qazoqov Farxod Farmonovich' => 'قازوقوف فرخود فرمانوفيتش',
            'Xabibov Faxriddin Yusupovich' => 'خبيبوف فخر الدين يوسفوفيتش',
            'O‘rinov Uyg‘un Abdullayevich' => 'أورينوف أوغون عبد اللهيفيتش',
            "O'rinov Uyg'un Abdullayevich" => 'أورينوف أوغون عبد اللهيفيتش',
        ];
    }

    private function transliterateWord(string $word): string
    {
        $word = trim($word, " \t\n\r\0\x0B.,;:()[]{}");
        if ($word === '') {
            return '';
        }

        $special = [
            "o'g'li" => 'أوغلي',
            "ogli" => 'أوغلي',
            "oglu" => 'أوغلي',
            "qizi" => 'قيزي',
            "kizi" => 'قيزي',
            "dsc" => 'دكتور علوم',
            "phd" => 'دكتوراه',
        ];

        $lower = Str::lower($word);
        if (isset($special[$lower])) {
            return $special[$lower];
        }

        $map = [
            "yo" => "يو", "yu" => "يو", "ya" => "يا", "ye" => "ي", "iy" => "ي",
            "kh" => "خ", "x" => "خ", "sh" => "ش", "ch" => "تش", "ts" => "تس",
            "zh" => "ج", "g'" => "غ", "g‘" => "غ", "o'" => "أو", "o‘" => "أو",
            "a" => "ا", "b" => "ب", "c" => "ك", "d" => "د", "e" => "ي",
            "f" => "ف", "g" => "غ", "h" => "ه", "i" => "ي", "j" => "ج",
            "k" => "ك", "l" => "ل", "m" => "م", "n" => "ن", "o" => "و",
            "p" => "ب", "q" => "ق", "r" => "ر", "s" => "س", "t" => "ت",
            "u" => "و", "v" => "ف", "w" => "و", "y" => "ي", "z" => "ز",
            "'" => "",
        ];

        $value = Str::lower($word);
        uksort($map, fn ($a, $b) => strlen($b) <=> strlen($a));

        return strtr($value, $map);
    }
};
