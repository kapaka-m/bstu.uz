<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\FacultyTranslation;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/translations.json');
        if (! file_exists($filePath)) {
            return;
        }

        $translations = json_decode(file_get_contents($filePath), true);

        $faculties = [
            'engineering' => [
                'slug' => 'faculty-of-engineering',
                'code' => 'ENG',
                'image' => 'faculties/engineering.jpg',
                'icon' => 'wrench',
                'sort_order' => 1,
            ],
            'technology' => [
                'slug' => 'faculty-of-technology',
                'code' => 'TECH',
                'image' => 'faculties/technology.jpg',
                'icon' => 'flask-conical',
                'sort_order' => 2,
            ],
            'service' => [
                'slug' => 'faculty-of-service-and-digitalization',
                'code' => 'SERVICE',
                'image' => 'faculties/service.jpg',
                'icon' => 'laptop',
                'sort_order' => 3,
            ],
            'natural' => [
                'slug' => 'faculty-of-natural-resources-management',
                'code' => 'NATURAL',
                'image' => 'faculties/natural.jpg',
                'icon' => 'leaf',
                'sort_order' => 4,
            ],
        ];

        foreach ($faculties as $key => $meta) {
            $facModel = Faculty::updateOrCreate(
                ['slug' => $meta['slug']],
                [
                    'code' => $meta['code'],
                    'image' => $meta['image'],
                    'icon' => $meta['icon'],
                    'sort_order' => $meta['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $fl = $translations[$locale]['about']['facultiesList'] ?? [];

                // Get localized values, fallback to English if missing
                $name = $fl[$key] ?? ($translations['en']['about']['facultiesList'][$key] ?? '');
                $desc = $fl[$key.'Desc'] ?? ($translations['en']['about']['facultiesList'][$key.'Desc'] ?? '');

                FacultyTranslation::updateOrCreate(
                    [
                        'faculty_id' => $facModel->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $name,
                        'short_name' => str_replace('Faculty of ', '', $name),
                        'description' => $desc,
                        'meta_title' => $name.' - BSTU',
                        'meta_description' => $desc,
                    ]
                );
            }
        }
    }
}
