<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentTranslation;
use App\Models\Faculty;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $translationsPath = database_path('data/translations.json');
        $departmentsPath = database_path('data/departments.json');

        if (! file_exists($translationsPath) || ! file_exists($departmentsPath)) {
            return;
        }

        $translations = json_decode(file_get_contents($translationsPath), true);
        $deptsMetadata = json_decode(file_get_contents($departmentsPath), true);

        $sortOrder = 1;
        $usedCodes = [];
        foreach ($deptsMetadata as $slug => $meta) {
            $facultySlug = $meta['facultyId'] ?? 'faculty-of-technology';

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
                // Fallback to first faculty if not found
                $faculty = Faculty::first();
            }

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
            $deptModel = Department::updateOrCreate(
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
            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $deptTrans = $translations[$locale]['departments'][$slug] ?? [];

                $name = $deptTrans['name'] ?? ($meta['name'] ?? '');
                $desc = $deptTrans['about'] ?? ($meta['about'] ?? '');

                // Fallbacks
                if (empty($name)) {
                    $name = $translations['en']['departments'][$slug]['name'] ?? ($meta['name'] ?? '');
                }
                if (empty($desc)) {
                    $desc = $translations['en']['departments'][$slug]['about'] ?? ($meta['about'] ?? '');
                }

                DepartmentTranslation::updateOrCreate(
                    [
                        'department_id' => $deptModel->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $name,
                        'short_name' => $deptTrans['short_name'] ?? ($meta['short_name'] ?? str_replace('Department of ', '', $name)),
                        'description' => $desc,
                        'meta_title' => $name.' Dept',
                        'meta_description' => mb_substr($desc, 0, 200, 'UTF-8'),
                    ]
                );
            }
        }
    }
}
