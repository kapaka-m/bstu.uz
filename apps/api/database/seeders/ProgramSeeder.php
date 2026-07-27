<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\ProgramTranslation;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $translationsPath = database_path('data/translations.json');
        $programsPath = database_path('data/programs.json');

        if (! file_exists($translationsPath) || ! file_exists($programsPath)) {
            return;
        }

        $translations = json_decode(file_get_contents($translationsPath), true);
        $programsMetadata = json_decode(file_get_contents($programsPath), true);

        $sortOrder = 1;
        $usedCodes = [];
        foreach ($programsMetadata as $meta) {
            $id = $meta['id'];
            $facultySlug = $meta['facultyId'] ?? 'faculty-of-technology';
            $deptSlug = $meta['departmentId'] ?? 'oil-gas-refining-technology';

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

            if (! $faculty) {
                $faculty = Faculty::first();
            }
            if (! $dept) {
                $dept = Department::first();
            }

            if (! $faculty || ! $dept) {
                continue;
            }

            // Duration parse
            $durationStr = $meta['duration'] ?? '4 years';
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

            $progModel = Program::updateOrCreate(
                ['slug' => $id],
                [
                    'faculty_id' => $faculty->id,
                    'department_id' => $dept->id,
                    'code' => $code,
                    'degree' => $meta['degree'] ?? 'Bachelor',
                    'duration_years' => $duration,
                    'study_mode' => 'Full-time',
                    'language_of_study' => 'English',
                    'tuition_fee' => 3500.00,
                    'currency' => 'USD',
                    'image' => 'programs/'.$id.'.jpg',
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );

            // Populate translations for en, uz, ru, ar
            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $progTrans = $translations[$locale]['programs'][$id] ?? [];

                $name = $progTrans['name'] ?? ($meta['name'] ?? '');
                $desc = $progTrans['description'] ?? ($meta['description'] ?? '');
                $detailedDesc = $progTrans['detailedDescription'] ?? ($meta['detailedDescription'] ?? $desc);

                if (empty($name)) {
                    $name = $translations['en']['programs'][$id]['name'] ?? ($meta['name'] ?? '');
                }
                if (empty($desc)) {
                    $desc = $translations['en']['programs'][$id]['description'] ?? ($meta['description'] ?? '');
                }
                if (empty($detailedDesc)) {
                    $detailedDesc = $translations['en']['programs'][$id]['detailedDescription'] ?? ($meta['detailedDescription'] ?? $desc);
                }

                // Requirements / Documents / Career Opportunities / Curriculum lists
                $reqs = $progTrans['requirements'] ?? ($meta['requirements'] ?? ['Secondary school diploma.']);
                $docs = $progTrans['documents'] ?? ($meta['documents'] ?? ['Passport, High school certificate.']);
                $careers = $progTrans['careerOpportunities'] ?? ($meta['careerOpportunities'] ?? ['Specialist Engineer.']);
                $curriculum = $progTrans['curriculum'] ?? ($meta['curriculum'] ?? ['Applied Sciences.']);

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

                ProgramTranslation::updateOrCreate(
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
                        'meta_title' => $name.' '.($meta['degree'] ?? 'Bachelor'),
                        'meta_description' => mb_substr($detailedDesc, 0, 200, 'UTF-8'),
                    ]
                );
            }
        }
    }
}
