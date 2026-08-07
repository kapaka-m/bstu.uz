<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentTranslation;
use App\Models\Faculty;
use Database\Seeders\Concerns\ResolvesSeedLocales;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    use ResolvesSeedLocales;
    public function run(): void
    {
        $translationsPath = database_path('data/translations.json');
        $departmentsPath = database_path('data/departments.json');

        if (! file_exists($translationsPath) || ! file_exists($departmentsPath)) {
            return;
        }

        $translations = json_decode(file_get_contents($translationsPath), true);
        $deptsMetadata = json_decode(file_get_contents($departmentsPath), true);
        $locales = $this->activeSeedLocales();
        $sourceLocale = $this->sourceSeedLocale($translations, $locales);
        if ($sourceLocale === null) {
            return;
        }

        $sortOrder = 1;
        $usedCodes = [];
        foreach ($deptsMetadata as $slug => $meta) {
            $facultySlug = $meta['facultyId'] ?? null;

            // Normalize mapping aliases
            $aliases = [
                'faculty-of-engineering' => 'faculty-of-engineering',
                'faculty-of-technology' => 'faculty-of-technology',
                'faculty-of-service-and-digitalization' => 'faculty-of-service-and-digitalization',
                'faculty-of-natural-resources-management' => 'faculty-of-natural-resources-management',
            ];
            $facultySlug = $aliases[$facultySlug] ?? $facultySlug;

            $faculty = Faculty::where('slug', $facultySlug)->first();
            if (! $faculty) {
                continue;
            }

            $code = strtoupper(substr(str_replace('-', '', $slug), 0, 4));
            $originalCode = $code;
            $counter = 1;
            while (in_array($code, $usedCodes)) {
                $code = substr($originalCode, 0, 3).$counter++;
            }
            $usedCodes[] = $code;

            // Create department
            $deptModel = Department::firstOrCreate(
                ['slug' => $slug],
                [
                    'faculty_id' => $faculty->id,
                    'code' => $code,
                    'image' => 'departments/'.$slug.'.jpg',
                    'icon' => $meta['icon'] ?? 'droplet',
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]
            );

            // Populate translations
            foreach ($locales as $locale) {
                $deptTrans = $translations[$locale]['departments'][$slug] ?? [];

                $name = $deptTrans['name'] ?? null;
                if (($name === null || $name === '') && $locale === $sourceLocale) {
                    $name = $meta['name'] ?? null;
                }

                if ($name === null || $name === '') {
                    continue;
                }

                $desc = $deptTrans['about'] ?? (($locale === $sourceLocale) ? ($meta['about'] ?? '') : '');

                DepartmentTranslation::firstOrCreate(
                    [
                        'department_id' => $deptModel->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $name,
                        'short_name' => $deptTrans['short_name'] ?? (($locale === $sourceLocale) ? ($meta['short_name'] ?? null) : null),
                        'description' => $desc,
                        'meta_title' => $name.' Dept',
                        'meta_description' => mb_substr($desc, 0, 200, 'UTF-8'),
                    ]
                );
            }
        }
    }
}
