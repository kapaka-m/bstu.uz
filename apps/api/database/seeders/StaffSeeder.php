<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\StaffProfile;
use App\Models\StaffProfileTranslation;
use Database\Seeders\Concerns\ResolvesSeedLocales;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StaffSeeder extends Seeder
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
        $sourceLocale = $this->sourceLocale($translations, $locales);
        if ($sourceLocale === null) {
            return;
        }

        $sortOrder = 1;

        // 1. Seed Faculty Deans
        $faculties = Faculty::all();
        foreach ($faculties as $fac) {
            // Find dean key based on slug mapping
            // e.g. faculty-of-engineering -> engineeringDean
            $key = str_replace('faculty-of-', '', $fac->slug);
            $deanKey = $key.'Dean';
            $descKey = $key.'Desc';

            $sourceDeanName = $translations[$sourceLocale]['about']['facultiesList'][$deanKey] ?? null;
            if (! $sourceDeanName) {
                continue;
            }

            $slug = Str::slug($key.'-dean');

            $staff = StaffProfile::firstOrCreate(
                ['slug' => $slug],
                [
                    'department_id' => null,
                    'faculty_id' => $fac->id,
                    'photo' => 'staff/'.$key.'-dean.jpg',
                    'email' => null,
                    'phone' => null,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]
            );

            foreach ($locales as $locale) {
                $fl = $translations[$locale]['about']['facultiesList'] ?? [];

                $name = $fl[$deanKey] ?? null;
                $bio = $fl[$descKey] ?? null;
                $position = $fl[$deanKey.'Title'] ?? null;

                if ($name === null || $name === '' || $position === null || $position === '') {
                    continue;
                }

                StaffProfileTranslation::firstOrCreate(
                    [
                        'staff_profile_id' => $staff->id,
                        'locale' => $locale,
                    ],
                    [
                        'full_name' => $name,
                        'position' => $position,
                        'bio' => $bio,
                        'office' => null,
                    ]
                );
            }
        }

        // 2. Seed Department Staff
        foreach ($deptsMetadata as $deptSlug => $meta) {
            $deptModel = Department::where('slug', $deptSlug)->first();
            if (! $deptModel) {
                continue;
            }

            $staffList = $meta['staff'] ?? [];
            foreach ($staffList as $index => $staffEn) {
                $slug = Str::slug($deptSlug.'-'.$index.'-'.$staffEn['name']);

                $staff = StaffProfile::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'department_id' => $deptModel->id,
                        'faculty_id' => $deptModel->faculty_id,
                        'photo' => 'staff/'.$deptSlug.'-'.$index.'.jpg',
                        'email' => null,
                        'phone' => null,
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]
                );

                foreach ($locales as $locale) {
                    $deptStaff = $translations[$locale]['departments'][$deptSlug]['staff'][$index] ?? [];

                    $name = $deptStaff['name'] ?? null;
                    $position = $deptStaff['title'] ?? null;
                    if ($locale === $sourceLocale) {
                        $name = $name ?? ($staffEn['name'] ?? null);
                        $position = $position ?? ($staffEn['title'] ?? null);
                    }

                    if ($name === null || $name === '' || $position === null || $position === '') {
                        continue;
                    }

                    StaffProfileTranslation::firstOrCreate(
                        [
                            'staff_profile_id' => $staff->id,
                            'locale' => $locale,
                        ],
                        [
                            'full_name' => $name,
                            'position' => $position,
                            'bio' => null,
                            'office' => null,
                        ]
                    );
                }
            }
        }
    }

    private function sourceLocale(array $translations, array $locales): ?string
    {
        foreach ($locales as $locale) {
            if (isset($translations[$locale]) && is_array($translations[$locale])) {
                return $locale;
            }
        }

        foreach ($translations as $locale => $localeData) {
            if (is_array($localeData)) {
                return $locale;
            }
        }

        return null;
    }
}
