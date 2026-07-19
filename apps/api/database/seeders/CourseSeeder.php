<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseTranslation;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
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

        $courseCount = 1;

        foreach ($deptsMetadata as $deptSlug => $meta) {
            $deptModel = Department::where('slug', $deptSlug)->first();
            if (! $deptModel) {
                continue;
            }

            // Get programs of this department to attach courses to
            $programs = Program::where('department_id', $deptModel->id)->get();

            // Extract subjects
            $subjectsEn = $meta['subjects']['bachelor'] ?? [];

            foreach ($subjectsEn as $index => $nameEn) {
                $slug = Str::slug($nameEn);
                $code = strtoupper(substr(str_replace('-', '', $slug), 0, 4)).'-'.(100 + $index);

                // Create course
                $course = Course::updateOrCreate(
                    ['code' => $code],
                    [
                        'credits' => rand(3, 5),
                        'semester' => rand(1, 8),
                        'is_active' => true,
                    ]
                );

                // Seed translations for en, uz, ru, ar
                foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                    $name = $translations[$locale]['departments'][$deptSlug]['subjects']['bachelor'][$index] ?? $nameEn;
                    if (empty($name)) {
                        $name = $nameEn;
                    }

                    CourseTranslation::updateOrCreate(
                        [
                            'course_id' => $course->id,
                            'locale' => $locale,
                        ],
                        [
                            'name' => $name,
                            'description' => "Standard course covering topics in {$name}.",
                        ]
                    );
                }

                // Link to programs
                foreach ($programs as $prog) {
                    // Check if already linked
                    if (! $prog->courses()->where('course_id', $course->id)->exists()) {
                        $prog->courses()->attach($course->id, [
                            'year' => rand(1, 4),
                            'semester' => rand(1, 2),
                            'is_required' => true,
                        ]);
                    }
                }
            }
        }
    }
}
