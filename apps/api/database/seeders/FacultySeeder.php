<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\FacultyTranslation;
use Database\Seeders\Concerns\ResolvesSeedLocales;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    use ResolvesSeedLocales;
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
            $facModel = Faculty::firstOrCreate(
                ['slug' => $meta['slug']],
                [
                    'code' => $meta['code'],
                    'image' => $meta['image'],
                    'icon' => $meta['icon'],
                    'sort_order' => $meta['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach ($this->activeSeedLocales() as $locale) {
                $fl = $translations[$locale]['about']['facultiesList'] ?? [];

                $name = $fl[$key] ?? null;
                if ($name === null || $name === '') {
                    continue;
                }

                $desc = $fl[$key.'Desc'] ?? '';

                FacultyTranslation::firstOrCreate(
                    [
                        'faculty_id' => $facModel->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $name,
                        'short_name' => $name,
                        'description' => $desc,
                        'meta_title' => $name.' - BSTU',
                        'meta_description' => $desc,
                    ]
                );
            }
        }
    }
}
