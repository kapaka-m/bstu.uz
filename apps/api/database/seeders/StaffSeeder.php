<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\StaffProfile;
use App\Models\StaffProfileTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StaffSeeder extends Seeder
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

        // 1. Seed Faculty Deans
        $faculties = Faculty::all();
        foreach ($faculties as $fac) {
            // Find dean key based on slug mapping
            // e.g. faculty-of-engineering -> engineeringDean
            $key = str_replace('faculty-of-', '', $fac->slug);
            $deanKey = $key.'Dean';
            $descKey = $key.'Desc';

            // Find dean details in translations
            $deanNameEn = $translations['en']['about']['facultiesList'][$deanKey] ?? null;
            if (! $deanNameEn) {
                continue;
            }

            $email = $key.'.dean@bstu.uz';
            $existingPhone = StaffProfile::where('email', $email)->value('phone');

            $staff = StaffProfile::updateOrCreate(
                ['email' => $email],
                [
                    'slug' => Str::slug($key.'-dean'),
                    'department_id' => null,
                    'faculty_id' => $fac->id,
                    'photo' => 'staff/'.$key.'-dean.jpg',
                    'phone' => $existingPhone ?? '+998 65 223 12 34',
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]
            );

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $fl = $translations[$locale]['about']['facultiesList'] ?? [];

                $name = $fl[$deanKey] ?? $translations['en']['about']['facultiesList'][$deanKey];
                $bio = $fl[$descKey] ?? $translations['en']['about']['facultiesList'][$descKey];
                $position = ($locale === 'en') ? 'Dean of Faculty' : (($locale === 'uz') ? 'Fakultet dekani' : (($locale === 'ru') ? 'Декан факультета' : 'عميد الكلية'));

                StaffProfileTranslation::updateOrCreate(
                    [
                        'staff_profile_id' => $staff->id,
                        'locale' => $locale,
                    ],
                    [
                        'full_name' => $name,
                        'position' => $position,
                        'bio' => $bio,
                        'office' => 'Dean Office, '.$fac->name,
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
                $email = strtolower(str_replace(' ', '.', preg_replace('/[^A-Za-z ]/', '', $staffEn['name']))).'@bstu.uz';
                $existingPhone = StaffProfile::where('email', $email)->value('phone');

                $staff = StaffProfile::updateOrCreate(
                    ['email' => $email],
                    [
                        'slug' => Str::slug($deptSlug.'-'.$index.'-'.$staffEn['name']),
                        'department_id' => $deptModel->id,
                        'faculty_id' => $deptModel->faculty_id,
                        'photo' => 'staff/'.$deptSlug.'-'.$index.'.jpg',
                        'phone' => $existingPhone ?? '+998 90 '.rand(100, 999).' '.rand(10, 99).' '.rand(10, 99),
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]
                );

                foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                    $deptStaff = $translations[$locale]['departments'][$deptSlug]['staff'][$index] ?? [];

                    $name = $deptStaff['name'] ?? ($staffEn['name'] ?? '');
                    $position = $deptStaff['title'] ?? ($staffEn['title'] ?? 'Instructor');

                    if (empty($name)) {
                        $name = $translations['en']['departments'][$deptSlug]['staff'][$index]['name'] ?? ($staffEn['name'] ?? '');
                    }
                    if (empty($position)) {
                        $position = $translations['en']['departments'][$deptSlug]['staff'][$index]['title'] ?? ($staffEn['title'] ?? 'Instructor');
                    }

                    StaffProfileTranslation::updateOrCreate(
                        [
                            'staff_profile_id' => $staff->id,
                            'locale' => $locale,
                        ],
                        [
                            'full_name' => $name,
                            'position' => $position,
                            'bio' => 'Faculty member at the Department of '.$deptModel->name,
                            'office' => 'Department Office',
                        ]
                    );
                }
            }
        }
    }
}
