<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-natural-resources-management')->value('id');

        if (! $facultyId) {
            return;
        }

        $this->syncFaculty((int) $facultyId);
        $this->syncDepartments((int) $facultyId);
        $this->syncLeadership((int) $facultyId);
        $this->syncPrograms((int) $facultyId);
        $this->syncMenu();
    }

    public function down(): void
    {
        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-natural-resources-management')->value('id');

        if ($facultyId) {
            DB::table('programs')
                ->where('faculty_id', $facultyId)
                ->whereIn('slug', array_column($this->programs(), 'slug'))
                ->update(['updated_at' => now()]);
        }
    }

    private function syncFaculty(int $facultyId): void
    {
        foreach ($this->locales() as $locale) {
            DB::table('faculty_translations')->updateOrInsert(
                ['faculty_id' => $facultyId, 'locale' => $locale],
                [
                    'name' => 'Faculty of Natural Resource Management',
                    'short_name' => 'Faculty of Natural Resource Management',
                    'description' => $this->facultyDescription(),
                    'content_sections' => json_encode($this->facultySections(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'meta_title' => 'Faculty of Natural Resource Management',
                    'meta_description' => Str::limit($this->facultyDescription(), 240, ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function syncDepartments(int $facultyId): void
    {
        foreach ($this->departments() as $slug => $department) {
            $departmentId = DB::table('departments')->where('slug', $slug)->value('id');

            if (! $departmentId) {
                continue;
            }

            DB::table('departments')->where('id', $departmentId)->update([
                'faculty_id' => $facultyId,
                'head_name' => $department['head_name'],
                'email' => $department['email'],
                'phone' => $department['phone'],
                'reception_time' => $department['reception_time'],
                'sort_order' => $department['sort_order'],
                'is_active' => true,
                'updated_at' => now(),
            ]);

            $existingSections = DB::table('department_translations')
                ->where('department_id', $departmentId)
                ->where('locale', 'en')
                ->value('content_sections');

            foreach ($this->locales() as $locale) {
                DB::table('department_translations')->updateOrInsert(
                    ['department_id' => $departmentId, 'locale' => $locale],
                    [
                        'name' => $department['name'],
                        'short_name' => $department['short_name'],
                        'description' => $department['description'],
                        'content_sections' => $existingSections ?: json_encode($this->departmentSections($department), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        'meta_title' => $department['name'],
                        'meta_description' => Str::limit($department['description'], 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $this->upsertStaff($department['profile_slug'], $facultyId, (int) $departmentId, [
                'name' => $department['head_name'],
                'position' => 'Head of Department',
                'phone' => $department['phone'],
                'email' => $department['email'],
                'office' => $department['reception_time'],
                'photo' => $department['photo'],
                'sort_order' => 10,
            ]);
        }

        DB::table('departments')
            ->where('faculty_id', $facultyId)
            ->whereNotIn('slug', array_keys($this->departments()))
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    private function syncLeadership(int $facultyId): void
    {
        foreach ($this->leadership() as $leader) {
            $this->upsertStaff($leader['slug'], $facultyId, null, $leader);
        }
    }

    private function syncPrograms(int $facultyId): void
    {
        DB::table('programs')
            ->where('faculty_id', $facultyId)
            ->whereNotIn('slug', array_column($this->programs(), 'slug'))
            ->update(['is_active' => false, 'updated_at' => now()]);

        foreach ($this->programs() as $index => $program) {
            $departmentId = DB::table('departments')->where('slug', $program['department_slug'])->value('id');

            if (! $departmentId) {
                continue;
            }

            $programId = DB::table('programs')->where('slug', $program['slug'])->value('id');
            $payload = [
                'faculty_id' => $facultyId,
                'department_id' => $departmentId,
                'slug' => $program['slug'],
                'code' => $program['slug'],
                'official_code' => $program['code'],
                'track' => null,
                'degree' => 'bachelor',
                'duration_years' => 4,
                'study_mode' => 'full_time',
                'language_of_study' => 'en',
                'tuition_fee' => 0,
                'currency' => 'UZS',
                'image' => null,
                'is_active' => true,
                'sort_order' => $index + 1,
                'updated_at' => now(),
            ];

            if ($programId) {
                DB::table('programs')->where('id', $programId)->update($payload);
            } else {
                $programId = DB::table('programs')->insertGetId($payload + ['created_at' => now()]);
            }

            foreach ($this->locales() as $locale) {
                DB::table('program_translations')->updateOrInsert(
                    ['program_id' => $programId, 'locale' => $locale],
                    [
                        'name' => $program['name'],
                        'description' => $program['name'].' is a four-year bachelor program in the Faculty of Natural Resource Management. It combines theoretical study, field practice, laboratory work, and applied training for sustainable natural resource use.',
                        'requirements' => 'Secondary education certificate or equivalent, application documents, and admission requirements approved by the university.',
                        'documents' => 'Passport, education certificate, transcript, photo, and required application documents.',
                        'curriculum_summary' => 'Duration of study: 4 years. Scholarship fields are recorded as not specified. Tuition is stored as 0.00 UZS until official fee values are provided.',
                        'career_opportunities' => 'Graduates can work in water and land resource management, ecology and environmental protection, geodesy, cadastre, hydrotechnical engineering, transport systems, agriculture, public agencies, laboratories, and research or innovation projects.',
                        'meta_title' => $program['name'],
                        'meta_description' => Str::limit($program['name'].' program in the Faculty of Natural Resource Management.', 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function syncMenu(): void
    {
        $menuId = DB::table('menus')->where('key', 'main')->value('id');
        $parentId = DB::table('menu_items')->where('url', '/faculty/faculty-of-natural-resources-management')->value('id');

        if (! $menuId || ! $parentId) {
            return;
        }

        foreach ($this->departments() as $slug => $department) {
            $itemId = DB::table('menu_items')->where('url', '/department/'.$slug)->value('id');
            $payload = [
                'menu_id' => $menuId,
                'parent_id' => $parentId,
                'route_name' => 'link',
                'url' => '/department/'.$slug,
                'sort_order' => $department['sort_order'] / 10,
                'is_active' => true,
                'updated_at' => now(),
            ];

            if ($itemId) {
                DB::table('menu_items')->where('id', $itemId)->update($payload);
            } else {
                $itemId = DB::table('menu_items')->insertGetId($payload + ['created_at' => now()]);
            }

            foreach ($this->locales() as $locale) {
                DB::table('menu_item_translations')->updateOrInsert(
                    ['menu_item_id' => $itemId, 'locale' => $locale],
                    ['label' => $department['name'], 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    private function upsertStaff(string $slug, int $facultyId, ?int $departmentId, array $person): void
    {
        $profileId = DB::table('staff_profiles')->where('slug', $slug)->value('id');
        $payload = [
            'faculty_id' => $facultyId,
            'department_id' => $departmentId,
            'photo' => $person['photo'],
            'email' => $person['email'],
            'phone' => $person['phone'],
            'sort_order' => $person['sort_order'],
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($profileId) {
            DB::table('staff_profiles')->where('id', $profileId)->update($payload);
        } else {
            $profileId = DB::table('staff_profiles')->insertGetId($payload + ['slug' => $slug, 'created_at' => now()]);
        }

        foreach ($this->locales() as $locale) {
            DB::table('staff_profile_translations')->updateOrInsert(
                ['staff_profile_id' => $profileId, 'locale' => $locale],
                [
                    'full_name' => $person['name'],
                    'position' => $person['position'],
                    'bio' => $person['position'],
                    'office' => $person['office'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function facultyDescription(): string
    {
        return 'The Faculty of Natural Resource Management is a modern educational division focused on the preservation of natural resources, their efficient use, and the maintenance of ecological balance. Students gain theoretical and practical knowledge in land and water resource management, ecology, geodesy, environmental protection, and sustainable development based on innovative technologies.';
    }

    private function facultySections(): array
    {
        return [
            ['key' => 'about', 'title' => 'Fakultet ma\'lumotlari', 'items' => [$this->facultyDescription()]],
            ['key' => 'management', 'title' => 'Rahbaryat', 'items' => array_map(fn ($leader) => $leader['name'].' - '.$leader['position'], $this->leadership())],
            ['key' => 'departments', 'title' => 'Departments', 'items' => array_map(fn ($department) => $department['name'], $this->departments())],
            ['key' => 'programs', 'title' => 'Programs and Specializations', 'items' => array_map(fn ($program) => $program['code'].' - '.$program['name'], $this->programs())],
            ['key' => 'international', 'title' => 'International Cooperation', 'items' => ['The faculty develops cooperation in natural resource management, water systems, ecology, geodesy, agriculture, and engineering with international academic and industrial partners.']],
        ];
    }

    private function departmentSections(array $department): array
    {
        return [
            ['key' => 'overview', 'title' => 'About the Department', 'items' => [$department['description']]],
            ['key' => 'scientific_activity', 'title' => 'Scientific Activity', 'items' => $department['science']],
            ['key' => 'prepared_specialists', 'title' => 'Specialists Trained by the Department', 'items' => $department['specialists']],
            ['key' => 'international_cooperation', 'title' => 'International Cooperation', 'items' => $department['partners']],
            ['key' => 'department_structure', 'title' => 'Department Structure', 'items' => [$department['head_name'].' - Head of Department', 'Office hours: '.$department['reception_time'], 'Phone: '.$department['phone'], 'Email: '.($department['email'] ?: '-')]],
        ];
    }

    private function leadership(): array
    {
        return [
            ['slug' => 'faculty-of-natural-resources-management-dean-qobulova-barno-bakhriddin-qizi', 'name' => 'Dr. Barno Bakriddin Kobulova', 'position' => 'Dean', 'phone' => '+998 93 721 04 85', 'email' => 'kobulovabarno@gmail.com', 'office' => 'Daily 15:00-17:00', 'photo' => 'cms/staff/Dr. Barno Bakriddin Kobulova.jpg', 'sort_order' => 10],
            ['slug' => 'faculty-of-natural-resources-management-academic-to-be-entered', 'name' => 'To be entered', 'position' => 'Deputy Dean for Academic Affairs', 'phone' => null, 'email' => null, 'office' => 'Daily 14:00-16:00', 'photo' => null, 'sort_order' => 20],
            ['slug' => 'faculty-of-natural-resources-management-youth-gadoyeva-abera-hasanovna', 'name' => 'Gadoyeva Abera Hasanovna', 'position' => 'Deputy Dean for Youth Affairs', 'phone' => '+998 (93) 620 20 98', 'email' => 'Aberaxasanovnaa2208@gmail.com', 'office' => 'Daily 15:00-17:00', 'photo' => 'cms/staff/Gadoyeva Abera Hasanovna.jpg', 'sort_order' => 30],
        ];
    }

    private function departments(): array
    {
        return [
            'irrigation-melioration' => ['name' => 'Department of Irrigation and Land Reclamation', 'short_name' => 'Irrigation and Land Reclamation', 'head_name' => 'Murodov Otabek Ulugbekovich', 'email' => 'murodovou@gmail.com', 'phone' => '+998 94 327 35 00', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 10, 'profile_slug' => 'irrigation-melioration-murodov-otabek-ulugbekovich', 'photo' => 'cms/staff/Murodov Otabek Ulugbekovich.jpg', 'description' => 'The department trains specialists in water management, land reclamation, water supply engineering systems, and water-saving irrigation technologies.', 'science' => ['Research in irrigation, land reclamation, saline soils, drip irrigation, and water-saving technologies.'], 'specialists' => ['60811200 - Water Management and Melioration', '60811500 - Water Supply Engineering Systems'], 'partners' => ['Humboldt University of Berlin', 'ZALF Leibniz Centre', 'Istanbul Technical University']],
            'hydrotechnical-structures-pump-stations' => ['name' => 'Department of Hydraulic Structures and Pumping Stations', 'short_name' => 'Hydraulic Structures and Pumping Stations', 'head_name' => 'Axmedov Sharifboy Ro‘ziyevich', 'email' => null, 'phone' => '+998 91 444 72 27', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 20, 'profile_slug' => 'hydrotechnical-structures-pump-stations-hydraulic-structures-and-pumping-stations', 'photo' => null, 'description' => 'Hydraulic Structures and Pumping Stations is an important engineering field that ensures the management, distribution, and efficient use of water resources.', 'science' => ['Design, construction, and operation of reservoirs, canals, dams, hydraulic structures, and modern pumping stations.'], 'specialists' => ['60710600 - Hydropower Engineering', '60730600 - Hydrotechnical and Geotechnical Engineering', '60811300 - Operation of Hydrotechnical Installations and Pumping Stations'], 'partners' => ['Water management and engineering partners']],
            'agricultural-water-resources-engineering-technologies' => ['name' => 'Department of Agricultural and Water Management Engineering Technologies', 'short_name' => 'Agricultural and Water Management Engineering Technologies', 'head_name' => 'Rajabov Yarash Jabborovich', 'email' => null, 'phone' => '+998 93 138 84 20', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 30, 'profile_slug' => 'agricultural-water-resources-engineering-technologies-rajabov-yarash-jabborovich', 'photo' => 'cms/staff/Rajabov Yarash Jabborovich.jpg', 'description' => 'The department focuses on agricultural engineering, water management machinery, reclamation technologies, and modern mechanization for agricultural and water systems.', 'science' => ['Research in agricultural mechanization, reclamation machinery, energy-saving tillage, and smart agriculture.'], 'specialists' => ['60810100 - Mechanization of Agriculture'], 'partners' => ['Kursk State Agrarian University', 'North Dakota State University', 'Belarusian State Agrarian Technical University', 'Humboldt University of Berlin', 'Obuda University', 'Iowa State University', 'INTI International University']],
            'land-resources-management-state-land-cadastres' => ['name' => 'Department of Land Use and State Cadastre', 'short_name' => 'Land Resource Management and State Cadastres', 'head_name' => 'Asatov Sayitqul Rahimberdiyevich', 'email' => null, 'phone' => '+998 91 408 50 13', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 40, 'profile_slug' => 'land-resources-management-state-land-cadastres-asatov-sayitqul-rahimberdiyevich', 'photo' => 'cms/staff/Asatov Sayitqul Rahimberdiyevich.jpg', 'description' => 'The department trains specialists in land cadastre, land management, geodesy, geoinformatics, cartography, remote sensing, and soil quality assessment.', 'science' => ['Research in rational land use, ecological instability, land monitoring, geodesy, and cadastral digital technologies.'], 'specialists' => ['60721500 - Geodesy and Geoinformatics', '60721600 - Cartography and Remote Sensing', '60721700 - Cadastre', '60811600 - Land Cadastre and Land Management'], 'partners' => ['Ushak University']],
            'industrial-ecology-hydrogeology' => ['name' => 'Department of Industrial Ecology and Hydrogeology', 'short_name' => 'Industrial Ecology and Hydrogeology', 'head_name' => 'Xaitov Rauf Arifovich', 'email' => 'sanoat_ekologiyasi@mail.ru', 'phone' => '+998 91 405 66 24', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 50, 'profile_slug' => 'industrial-ecology-hydrogeology-xaitov-rauf-arifovich', 'photo' => 'cms/staff/Xaitov Rauf Arifovich.jpg', 'description' => 'The department prepares specialists in ecology, hydrology, reclamation hydrogeology, occupational safety, industrial safety, and environmental protection.', 'science' => ['Research in environmental protection, hydrogeology, hydrology, occupational safety, industrial ecology, and sustainable development.'], 'specialists' => ['60530400 - Hydrology', '60811400 - Meliorative Hydrogeology'], 'partners' => ['UN FAO', 'Erasmus+', 'Iowa University', 'Wyoming University', 'Humboldt University of Berlin', 'University of Surrey', 'Lanzhou University']],
            'vehicle-engineering-automotive-transport-systems' => ['name' => 'Department of Vehicle Engineering (Automotive & Transport Systems)', 'short_name' => 'Transport Vehicle Engineering', 'head_name' => 'Gaffarov Hasan Ravshanovich', 'email' => null, 'phone' => '+998 97 306 07 37', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 60, 'profile_slug' => 'vehicle-engineering-automotive-transport-systems-kafedra-mudiri-associate-professor', 'photo' => 'cms/staff/Gaffarov Hasan Ravshanovich.jpg', 'description' => 'The department develops engineering education in vehicle technologies, agricultural machinery, mechanical engineering, and modern transport systems.', 'science' => ['Research in vehicle engineering, agricultural machinery, transport systems, mechanical engineering, and production technology.'], 'specialists' => ['60711400 - Vehicle Engineering'], 'partners' => ['Avto Service Inter Millennium', 'Vobkent Yulduzi Texservis', 'Buxoro Avtotexxizmat', 'Emirate Steel', 'ENTER MASHINERIES SERVICE']],
        ];
    }

    private function programs(): array
    {
        return [
            ['code' => '60530400', 'name' => 'Hydrology', 'slug' => 'hydrology-60530400', 'department_slug' => 'industrial-ecology-hydrogeology'],
            ['code' => '60710600', 'name' => 'Hydropower Engineering', 'slug' => 'hydropower-engineering-60710600', 'department_slug' => 'hydrotechnical-structures-pump-stations'],
            ['code' => '60711400', 'name' => 'Vehicle Engineering', 'slug' => 'vehicle-engineering-60711400', 'department_slug' => 'vehicle-engineering-automotive-transport-systems'],
            ['code' => '60721500', 'name' => 'Geodesy and Geomatics', 'slug' => 'geodesy-and-geomatics-60721500', 'department_slug' => 'land-resources-management-state-land-cadastres'],
            ['code' => '60721600', 'name' => 'Cartography and Remote Sensing', 'slug' => 'cartography-and-remote-sensing-60721600', 'department_slug' => 'land-resources-management-state-land-cadastres'],
            ['code' => '60721700', 'name' => 'Cadastre', 'slug' => 'cadastre-60721700', 'department_slug' => 'land-resources-management-state-land-cadastres'],
            ['code' => '60730600', 'name' => 'Hydraulic and Geotechnical Engineering', 'slug' => 'hydraulic-and-geotechnical-engineering-60730600', 'department_slug' => 'hydrotechnical-structures-pump-stations'],
            ['code' => '60810100', 'name' => 'Agricultural Mechanization', 'slug' => 'agricultural-mechanization-60810100', 'department_slug' => 'agricultural-water-resources-engineering-technologies'],
            ['code' => '60811200', 'name' => 'Water Management and Land Reclamation', 'slug' => 'water-management-and-land-reclamation-60811200', 'department_slug' => 'irrigation-melioration'],
            ['code' => '60811300', 'name' => 'Operation of Hydraulic Structures and Pumping Stations', 'slug' => 'operation-of-hydraulic-structures-and-pumping-stations-60811300', 'department_slug' => 'hydrotechnical-structures-pump-stations'],
            ['code' => '60811400', 'name' => 'Reclamation Hydrogeology', 'slug' => 'reclamation-hydrogeology-60811400', 'department_slug' => 'industrial-ecology-hydrogeology'],
            ['code' => '60811500', 'name' => 'Water Supply Engineering Systems', 'slug' => 'water-supply-engineering-systems-60811500', 'department_slug' => 'irrigation-melioration'],
            ['code' => '60811600', 'name' => 'Land Cadastre and Land Management', 'slug' => 'land-cadastre-and-land-management-60811600', 'department_slug' => 'land-resources-management-state-land-cadastres'],
        ];
    }

    private function locales(): array
    {
        return DB::table('locales')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all() ?: ['en', 'uz', 'ru', 'ar'];
    }
};
