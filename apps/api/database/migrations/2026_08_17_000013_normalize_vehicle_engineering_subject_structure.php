<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'vehicle-engineering-automotive-transport-systems')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeStaffSlugs($departmentId);

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

            $rawItems = array_merge($section['items'] ?? [], $section['bachelor'] ?? [], $section['master'] ?? []);
            $text = implode("\n", array_map(fn ($item) => is_string($item) ? $item : '', $rawItems));

            [$bachelorText, $masterText] = $this->splitSubjectText($text);

            $sections[$index] = [
                'key' => 'subjects',
                'title' => $section['title'] ?? 'Taught Subjects',
                'items' => [],
                'bachelor' => $this->splitSubjectItems($bachelorText),
                'master' => $this->splitSubjectItems($masterText),
            ];

            return $sections;
        }

        return $sections;
    }

    private function splitSubjectText(string $text): array
    {
        $masterPattern = '/(?:^|\R)\s*(Master subjects|Master\'?s?\s+subjects|Master\'?s?\s+Degree|Magistratura fanlari|Дисциплины магистратуры|مواد الماجستير)\s*:?\s*(?:\R|$)/iu';
        if (preg_match($masterPattern, $text, $match, PREG_OFFSET_CAPTURE)) {
            $masterStart = $match[0][1];

            return [substr($text, 0, $masterStart), substr($text, $masterStart)];
        }

        return [$text, ''];
    }

    private function splitSubjectItems(string $text): array
    {
        $text = preg_replace('/(?:^|\R)\s*(Bachelor subjects|Bachelor\'?s?\s+subjects|Bachelor\'?s?\s+Degree|Bakalavriat fanlari|Дисциплины бакалавриата|مواد البكالوريوس|Master subjects|Master\'?s?\s+subjects|Master\'?s?\s+Degree|Magistratura fanlari|Дисциплины магистратуры|مواد الماجستير)\s*:?\s*(?:\R|$)/iu', "\n", $text);
        $lines = preg_split('/\R+/u', trim((string) $text)) ?: [];

        return collect($lines)
            ->map(fn (string $item) => trim(preg_replace('/^\d+[\.)]?\s*/u', '', preg_replace('/\s+/u', ' ', $item))))
            ->filter(fn (string $item) => mb_strlen($item) > 2 && ! preg_match('/^\d+$/u', $item))
            ->unique(fn (string $item) => $this->subjectKey($item))
            ->values()
            ->all();
    }

    private function subjectKey(string $item): string
    {
        $key = mb_strtolower($item);
        $key = str_replace('kukunli kompozitsion materiallar', 'kukun kompozitsion materiallar', $key);

        return preg_replace('/[^\p{L}\p{N}]+/u', ' ', $key) ?: $key;
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

    private function normalizeStaffSlugs(int $departmentId): void
    {
        foreach ($this->staffSlugMap() as $oldSlug => $newSlug) {
            $oldId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $oldSlug)
                ->value('id');

            if (! $oldId) {
                continue;
            }

            $newId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $newSlug)
                ->value('id');

            if ($newId && $newId !== $oldId) {
                DB::table('staff_profile_translations')->where('staff_profile_id', $oldId)->delete();
                DB::table('staff_profiles')->where('id', $oldId)->delete();

                continue;
            }

            DB::table('staff_profiles')
                ->where('id', $oldId)
                ->update(['slug' => $newSlug, 'updated_at' => now()]);
        }
    }

    private function staffSlugMap(): array
    {
        return [
            'vehicle-engineering-automotive-transport-systems-gafforov-hasan-ravshanovich' => 'vehicle-engineering-automotive-transport-systems-gaffarov-hasan-ravshanovich',
            'vehicle-engineering-automotive-transport-systems-duskarayev-nartaylak' => 'vehicle-engineering-automotive-transport-systems-duskarayev-nortaylak',
            'vehicle-engineering-automotive-transport-systems-qishloq-xojaligi-fanlari-falsafa-doktori-phd-dotsent' => 'vehicle-engineering-automotive-transport-systems-fazliyev-jamoliddin-sharofiddinovich',
            'vehicle-engineering-automotive-transport-systems-katta-oqituvchit' => 'vehicle-engineering-automotive-transport-systems-rajabov-bobir-bozorovich',
        ];
    }
};
