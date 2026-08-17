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

        $departmentId = DB::table('departments')
            ->where('slug', 'agricultural-water-resources-engineering-technologies')
            ->value('id');

        if (! $departmentId) {
            return;
        }

        foreach ($this->partnersByLocale() as $locale => $partners) {
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

            $found = false;
            foreach ($sections as $index => $section) {
                if (($section['key'] ?? null) === 'cooperation') {
                    $sections[$index] = [
                        'key' => 'cooperation',
                        'title' => $this->title($locale),
                        'items' => $partners,
                    ];
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $sections[] = [
                    'key' => 'cooperation',
                    'title' => $this->title($locale),
                    'items' => $partners,
                ];
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

    private function title(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Xalqaro hamkorlik',
            'ru' => 'Международное сотрудничество',
            'ar' => 'التعاون الدولي',
            default => 'International Cooperation',
        };
    }

    private function partnersByLocale(): array
    {
        return [
            'en' => [
                'Kursk State Agrarian University',
                'North Dakota State University (USA)',
                'Belarusian State Agrarian Technical University',
                'Humboldt University of Berlin (Germany)',
                'Obuda University (Hungary)',
                'Southwestern State University (Russia)',
                'Iowa State University (USA)',
                'INTI International University (Malaysia)',
            ],
            'uz' => [
                'Kursk davlat agrar universiteti',
                'Shimoliy Dakota davlat universiteti (AQSh)',
                'Belarus davlat agrar texnika universiteti',
                'Berlin Gumboldt universiteti (Germaniya)',
                'Obuda universiteti (Vengriya)',
                'Janubi-g‘arbiy davlat universiteti (Rossiya)',
                'Ayova davlat universiteti (AQSh)',
                'INTI xalqaro universiteti (Malayziya)',
            ],
            'ru' => [
                'Курский государственный аграрный университет',
                'Государственный университет Северной Дакоты (США)',
                'Белорусский государственный аграрный технический университет',
                'Берлинский университет имени Гумбольдта (Германия)',
                'Университет Обуда (Венгрия)',
                'Юго-Западный государственный университет (Россия)',
                'Университет штата Айова (США)',
                'Международный университет INTI (Малайзия)',
            ],
            'ar' => [
                'جامعة كورسك الحكومية الزراعية',
                'جامعة ولاية داكوتا الشمالية (الولايات المتحدة الأمريكية)',
                'الجامعة التقنية الزراعية الحكومية البيلاروسية',
                'جامعة هومبولت في برلين (ألمانيا)',
                'جامعة أوبودا (المجر)',
                'جامعة الجنوب الغربي الحكومية (روسيا)',
                'جامعة ولاية آيوا (الولايات المتحدة الأمريكية)',
                'جامعة INTI الدولية (ماليزيا)',
            ],
        ];
    }
};
