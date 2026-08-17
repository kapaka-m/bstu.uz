<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'irrigation-melioration')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
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

                $sections = $this->normalizeSubjects($sections);
                $sections = $this->normalizePreparedSpecialists($sections, $departmentId, $locale);

                DB::table('department_translations')
                    ->where('id', $translation->id)
                    ->update([
                        'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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

    private function normalizeSubjects(array $sections): array
    {
        foreach ($sections as $index => $section) {
            if (($section['key'] ?? null) !== 'subjects') {
                continue;
            }

            $text = implode("\n", array_map(fn ($item) => is_string($item) ? $item : '', $section['items'] ?? []));
            $masterStart = preg_match('/\b(Master subjects|Master\'?s?\s+Degree|Judiciary)\s*:?/iu', $text, $match, PREG_OFFSET_CAPTURE)
                ? $match[0][1]
                : false;

            $bachelorText = $masterStart === false ? $text : substr($text, 0, $masterStart);
            $masterText = $masterStart === false ? '' : substr($text, $masterStart);

            $sections[$index] = [
                'key' => 'subjects',
                'title' => $section['title'] ?? 'Taught Subjects',
                'items' => [],
                'bachelor' => $this->splitNumberedItems($bachelorText),
                'master' => $this->splitNumberedItems($masterText),
            ];

            return $sections;
        }

        return $sections;
    }

    private function splitNumberedItems(string $text): array
    {
        $text = preg_replace('/\b(Bachelor\'?s?\s+Degree|Bachelor subjects|Master\'?s?\s+Degree|Master subjects|Judiciary)\s*:?/iu', '', $text);
        $parts = preg_split('/(?=\b\d+\.\s*)/u', trim((string) $text)) ?: [];

        return collect($parts)
            ->map(fn (string $item) => trim(preg_replace('/^\d+\.\s*/u', '', preg_replace('/\s+/u', ' ', $item))))
            ->filter(fn (string $item) => mb_strlen($item) > 2)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizePreparedSpecialists(array $sections, int $departmentId, string $locale): array
    {
        $programs = DB::table('programs as p')
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

        foreach ($sections as $index => $section) {
            if (($section['key'] ?? null) === 'prepared_specialists') {
                $sections[$index]['items'] = $programs;

                return $sections;
            }
        }

        $sections[] = [
            'key' => 'prepared_specialists',
            'title' => 'Prepared Specialists',
            'items' => $programs,
        ];

        return $sections;
    }
};
