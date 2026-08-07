<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\ProgramTranslation;
use Database\Seeders\Concerns\ResolvesSeedLocales;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    use ResolvesSeedLocales;
    public function run(): void
    {
        $translationsPath = database_path('data/translations.json');
        $programsPath = database_path('data/programs.json');

        if (! file_exists($translationsPath) || ! file_exists($programsPath)) {
            return;
        }

        $translations = json_decode(file_get_contents($translationsPath), true);
        $programsMetadata = json_decode(file_get_contents($programsPath), true);
        $locales = $this->activeSeedLocales();
        $sourceLocale = $this->sourceSeedLocale($translations, $locales);
        if ($sourceLocale === null) {
            return;
        }

        $sortOrder = 1;
        $usedCodes = [];
        foreach ($programsMetadata as $meta) {
            $id = $meta['id'];
            $facultySlug = $meta['facultyId'] ?? null;
            $deptSlug = $meta['departmentId'] ?? null;

            // Aliases normalize
            $aliases = [
                'faculty-of-engineering' => 'faculty-of-engineering',
                'faculty-of-technology' => 'faculty-of-technology',
                'faculty-of-service-and-digitalization' => 'faculty-of-service-and-digitalization',
                'faculty-of-natural-resources-management' => 'faculty-of-natural-resources-management',
            ];
            $facultySlug = $aliases[$facultySlug] ?? $facultySlug;

            $faculty = Faculty::where('slug', $facultySlug)->first();
            $dept = Department::where('slug', $deptSlug)->first();

            if (! $faculty || ! $dept) {
                continue;
            }

            // Duration parse
            $durationStr = $meta['duration'] ?? '';
            $duration = 4.0;
            if (str_contains($durationStr, '2')) {
                $duration = 2.0;
            } elseif (str_contains($durationStr, '3')) {
                $duration = 3.0;
            }

            $code = $meta['code'] ?? strtoupper(substr(str_replace('-', '', $id), 0, 8));
            $originalCode = $code;
            $counter = 1;
            while (in_array($code, $usedCodes)) {
                $code = $originalCode.'-'.$counter++;
            }
            $usedCodes[] = $code;

            $studyMode = $meta['studyMode'] ?? $meta['study_mode'] ?? null;
            $languageOfStudy = $meta['languageOfStudy'] ?? $meta['language_of_study'] ?? null;
            $tuitionFee = $meta['tuitionFee'] ?? $meta['tuition_fee'] ?? null;
            $currency = $meta['currency'] ?? null;

            if ($studyMode === null || $languageOfStudy === null || $tuitionFee === null || $currency === null) {
                continue;
            }

            $progModel = Program::firstOrCreate(
                ['slug' => $id],
                [
                    'faculty_id' => $faculty->id,
                    'department_id' => $dept->id,
                    'code' => $code,
                    'degree' => $meta['degree'] ?? null,
                    'duration_years' => $duration,
                    'study_mode' => $studyMode,
                    'language_of_study' => $languageOfStudy,
                    'tuition_fee' => $tuitionFee,
                    'currency' => $currency,
                    'image' => 'programs/'.$id.'.jpg',
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );

            foreach ($locales as $locale) {
                $progTrans = $translations[$locale]['programs'][$id] ?? [];

                $name = $progTrans['name'] ?? null;
                if (($name === null || $name === '') && $locale === $sourceLocale) {
                    $name = $meta['name'] ?? null;
                }

                if ($name === null || $name === '') {
                    continue;
                }

                $desc = $progTrans['description'] ?? (($locale === $sourceLocale) ? ($meta['description'] ?? '') : '');
                $detailedDesc = $progTrans['detailedDescription'] ?? (($locale === $sourceLocale) ? ($meta['detailedDescription'] ?? $desc) : $desc);

                $reqs = $progTrans['requirements'] ?? (($locale === $sourceLocale) ? ($meta['requirements'] ?? null) : null);
                $docs = $progTrans['documents'] ?? (($locale === $sourceLocale) ? ($meta['documents'] ?? null) : null);
                $careers = $progTrans['careerOpportunities'] ?? (($locale === $sourceLocale) ? ($meta['careerOpportunities'] ?? null) : null);
                $curriculum = $progTrans['curriculum'] ?? (($locale === $sourceLocale) ? ($meta['curriculum'] ?? null) : null);

                if (is_array($reqs)) {
                    $reqs = implode("\n", $reqs);
                }
                if (is_array($docs)) {
                    $docs = implode("\n", $docs);
                }
                if (is_array($careers)) {
                    $careers = implode("\n", $careers);
                }
                if (is_array($curriculum)) {
                    $curriculum = implode("\n", $curriculum);
                }

                ProgramTranslation::firstOrCreate(
                    [
                        'program_id' => $progModel->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $name,
                        'description' => $desc,
                        'requirements' => $reqs,
                        'documents' => $docs,
                        'curriculum_summary' => $curriculum,
                        'career_opportunities' => $careers,
                        'meta_title' => trim($name.' '.($meta['degree'] ?? '')),
                        'meta_description' => mb_substr($detailedDesc, 0, 200, 'UTF-8'),
                    ]
                );
            }
        }
    }
}
