<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-technology')->value('id');

        if (! $facultyId) {
            return;
        }

        foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
            DB::table('faculty_translations')->updateOrInsert(
                ['faculty_id' => $facultyId, 'locale' => $locale],
                [
                    'name' => 'Faculty of Technology',
                    'short_name' => 'Faculty of Technology',
                    'description' => $this->facultyDescription(),
                    'content_sections' => json_encode($this->facultySections(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'meta_title' => 'Faculty of Technology',
                    'meta_description' => Str::limit($this->facultyDescription(), 240, ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

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

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                DB::table('department_translations')->updateOrInsert(
                    ['department_id' => $departmentId, 'locale' => $locale],
                    [
                        'name' => $department['name'],
                        'short_name' => $department['short_name'],
                        'description' => $department['description'],
                        'content_sections' => json_encode($this->departmentSections($department), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
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

        foreach ($this->leadership() as $leader) {
            $this->upsertStaff($leader['slug'], $facultyId, null, $leader);
        }

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
                'track' => $program['track'],
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

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                DB::table('program_translations')->updateOrInsert(
                    ['program_id' => $programId, 'locale' => $locale],
                    [
                        'name' => $program['name'],
                        'description' => $program['description'],
                        'requirements' => 'Secondary education certificate or equivalent, application documents, and admission requirements approved by the university.',
                        'documents' => 'Passport, education certificate, transcript, photo, and required application documents.',
                        'curriculum_summary' => 'Duration of study: 4 years. Scholarship fields are recorded as not specified. Tuition is stored as 0.00 UZS until official fee values are provided.',
                        'career_opportunities' => $program['career'],
                        'meta_title' => $program['name'],
                        'meta_description' => Str::limit($program['description'], 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-technology')->value('id');

        if ($facultyId) {
            DB::table('programs')
                ->where('faculty_id', $facultyId)
                ->whereIn('slug', array_column($this->programs(), 'slug'))
                ->update(['updated_at' => now()]);
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

        foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
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
        return 'The Faculty of Technology is an educational division that trains qualified specialists for modern manufacturing, processing, and engineering industries. Students gain theoretical and practical knowledge in innovative technologies, food and chemical industries, oil and gas sectors, and service fields.';
    }

    private function facultySections(): array
    {
        return [
            ['key' => 'about', 'title' => 'Fakultet ma\'lumoti', 'items' => [$this->facultyDescription()]],
            ['key' => 'management', 'title' => 'Management', 'items' => array_map(fn ($leader) => $leader['name'].' - '.$leader['position'], $this->leadership())],
            ['key' => 'departments', 'title' => 'Departments', 'items' => array_map(fn ($department) => $department['name'], $this->departments())],
            ['key' => 'programs', 'title' => 'Programs and Specializations', 'items' => array_map(fn ($program) => $program['code'].' - '.$program['name'], $this->programs())],
            ['key' => 'international', 'title' => 'International Cooperation', 'items' => ['The faculty develops academic and research cooperation with leading international universities and industrial partners.']],
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
            ['slug' => 'faculty-of-technology-dean-adizov-rashid-tukhtayevich', 'name' => 'Dr. Adizov Rashid Tokhtayevich', 'position' => 'Dean', 'phone' => '+998 93 479-77-65', 'email' => 'adizov.rashid@mail.ru', 'office' => 'Daily 14:00-16:00 (except Monday and Saturday)', 'photo' => 'cms/staff/Dr. Adizov Rashid Tokhtayevich.jpg', 'sort_order' => 10],
            ['slug' => 'faculty-of-technology-academic-safarov-jasur-alijon-ogli', 'name' => 'Safarov Jasur Alijon o‘g‘li', 'position' => 'Deputy Dean for Academic Affairs', 'phone' => '+998 93 688 56 88', 'email' => 'jasur.safarov1993@mail.ru', 'office' => 'Daily 14:00-16:00', 'photo' => 'cms/staff/Safarov Jasur Alijon o‘g‘li.jpg', 'sort_order' => 20],
            ['slug' => 'faculty-of-technology-youth-bozorov-dilmurod-kholmurodovich', 'name' => 'Bozorov Dilmurod Kholmurodovich', 'position' => 'Deputy Dean for Youth Affairs', 'phone' => '+998 90 744-47-97', 'email' => 'd.bozorov_78@mail.ru', 'office' => 'Daily 14:00-16:00', 'photo' => 'cms/staff/Bozorov Dilmurod Kholmurodovich.jpg', 'sort_order' => 30],
        ];
    }

    private function departments(): array
    {
        return [
            'oil-gas-refining-technology' => $this->department('Oil and Gas Refining Technology', 'Oil and Gas Refining Technology', 'Ochilov Abduraxim Abdurasulovich', 'ochilov82@gmail.ru', '+998 91 411 00 16', 'Monday-Friday 14:00-16:00', 10, 'oil-gas-refining-technology-0-ochilov-abduraxim-abdurasulovich', 'cms/staff/ochilov-abduraxim-abdurasulovich.jpg', 'Oil and gas refining technology studies the processing of oil and natural gas into high-quality industrial products, including fuel, lubricants, petrochemical materials, and gas processing outputs.', ['Laboratory modernization with oil and gas industry partners.', 'Research on refining processes, petrochemical products, catalysis, corrosion protection, and energy-saving technologies.'], ['60721100 - Oil and oil-gas refining technology', '60720500 - Deep gas processing technology', '70720600 - Oil and oil-gas refining technology', '02.00.08 - Oil and gas chemistry and technology'], ['Baku Higher Oil School', 'SOCAR cooperation', 'Atyrau Oil and Gas University', 'Ufa State Petroleum Technological University', 'M. Auezov South Kazakhstan University']),
            'food-technology-service' => $this->department('Food Technology and Service', 'Food Technology and Service', 'Qurbonov Murod Tashpulatovich', 'kurbanov.m@rambler.ru', '+998 90 299 85 48', 'Monday-Saturday 14:00-16:00', 20, 'food-technology-service-qurbonov-murod-tashpulatovich', 'cms/staff/Qurbonov Murod Tashpulatovich.jpg', 'Food Technology and Service focuses on production, processing, storage, quality control of food products, and effective organization of service operations.', ['International grants and laboratory modernization through GIZ and OUWOW projects.', 'Research on functional food products, bakery products, decontamination technologies, and food quality improvement.'], ['60720100 - Food Technology', '60720300 - Winemaking and fermentation products', '60720400 - Canning Technology', '70720101 - Technology of Food Production and Processing'], ['Dresden University of Technology', 'Moscow State University of Food Production', 'Mogilev State University of Food Technologies', 'Latvia University of Life Sciences and Technologies']),
            'chemical-technology' => $this->department('Chemical Technology', 'Chemical Technology', 'Akhmedov Voxid Nizomovich', 'vohid7@mail.ru', '+998 90 511 59 58', 'Monday-Friday 14:00-16:00', 30, 'chemical-technology-axmedov-voxid-nizomovich', 'cms/staff/Akhmedov Voxid Nizomovich.jpg', 'Chemical Technology trains specialists for modern chemical science and industry, combining theoretical knowledge, practical training, research, and industrial technologies.', ['Research in colloid and membrane chemistry, inorganic substances, organic substances, polymers, and food-industry apparatus.', 'Faculty research supports innovative chemical technologies and industrial cooperation.'], ['60710100 - Chemical Technology', '60710300 - Printing and Packaging Processes', '70710101 - Chemical Technology', '70710103 - High-Molecular Compounds', 'Doctoral studies in 02.00.11, 02.00.13, 02.00.14, 02.00.16, 02.00.17'], ['Beijing University of Chemical Technology', 'Cyprus International University', 'Universiti Putra Malaysia', 'Perm National Research Polytechnic University', 'M. Auezov South Kazakhstan University']),
            'agricultural-products-storage-oil-fat-technology' => $this->department('Agricultural Products Storage & Oil-Fat Technology', 'Storage and Processing of Agricultural Products and Fat-and-Oil Technology', 'Majidova Nargiza Kaxramonovna', 'nargiz-1234n@mail.ru', '+998 97 305 95 59', 'Monday-Friday 14:00-16:00', 40, 'agricultural-products-storage-oil-fat-technology-majidova-nargiza-kaxramonovna', 'cms/staff/Majidova Nargiza Kaxramonovna.jpg', 'The department combines modern knowledge and practical skills in storage, processing of agricultural products, and fat-and-oil technology.', ['Research on fat-and-oil technology, catalysts, vegetable oils, functional products, and agricultural product quality.', 'Scientific publications in Web of Science, Scopus, and international journals.'], ['60811300 - Storage and processing of agricultural products', '60811800 - Fruit growing and viticulture', '60720100 - Food Technology (Fat-and-Oil Products)', '60720200 - Fats, essential oils, perfumery and cosmetic products'], ['Al-Farabi Kazakh National University', 'Karaganda Technical University', 'M. Auezov South Kazakhstan University', 'University of Copenhagen', 'University of Extremadura']),
            'oil-gas-engineering-upstream-downstream' => $this->department('Oil and Gas Engineering (Upstream & Downstream)', 'Oil and Gas Business', 'Sharipov Qaxramon Qandiyorovich', 'kahramon.sharipov@mail.ru', '+998 93 453 69 69', 'Daily 14:00-16:00', 50, 'oil-gas-engineering-upstream-downstream-sharipov-qaxramon-qandiyorovich', 'cms/staff/Sharipov Qakhramon Qandiyorovich.jpg', 'Oil and Gas Business prepares specialists for finding, extracting, operating, and managing oil and gas fields and related engineering systems.', ['Research on oil emulsions, local clays, gas odorants, and oil and gas field equipment.', 'The department publishes research in national, international, Scopus, and Web of Science indexed sources.'], ['60721800 - Oil and Gas Engineering', '5311900 - Development and operation of oil and gas fields', '70721802 - Operation of machinery and equipment of oil and gas fields'], ['Baku Higher Oil School', 'Gubkin Russian State University of Oil and Gas', 'South Kazakhstan University named after M. Auezov', 'UAE University', 'Ukhta State Technical University']),
            'metrology-standardization-quality-control' => $this->department('Metrology and Standardization', 'Metrology and Standardization', 'Tairov Bakhtiyor Boboqulovich', 'b.toirov@mail.ru', '+998 93 471 00 65', 'Monday-Friday 14:00-16:00', 60, 'metrology-standardization-quality-control-kafedra-mudiri', 'cms/staff/Tairov Bakhtiyor Boboqulovich.jpg', 'Metrology and Standardization ensures order, precision, reliability, product quality, and safe measurement systems for industry and society.', ['Research on quality process control, drying processes, confectionery quality assessment, beverages, and professional skills methodology.', 'The department develops metrology, standardization, certification, quality control, and modern measurement systems.'], ['60710800 - Metrology and Standardization', '60711300 - Metrology, Standardization and Product Quality Management', '70710802 - Metrology, Standardization and Quality Management'], ['M. Auezov South Kazakhstan University', 'Al-Farabi Kazakh National University']),
        ];
    }

    private function department(string $name, string $shortName, string $head, ?string $email, string $phone, string $office, int $sort, string $profileSlug, string $photo, string $description, array $science, array $specialists, array $partners): array
    {
        return compact('name', 'shortName') + [
            'short_name' => $shortName,
            'head_name' => $head,
            'email' => $email,
            'phone' => $phone,
            'reception_time' => $office,
            'sort_order' => $sort,
            'profile_slug' => $profileSlug,
            'photo' => $photo,
            'description' => $description,
            'science' => $science,
            'specialists' => $specialists,
            'partners' => $partners,
        ];
    }

    private function programs(): array
    {
        return [
            $this->program('60520200', 'Ecology and Environmental Protection', 'ecology-and-environmental-protection-60520200', 'chemical-technology'),
            $this->program('60710100', 'Chemical Engineering', 'chemical-engineering-60710100', 'chemical-technology'),
            $this->program('60710200', 'Biotechnology', 'biotechnology-60710200', 'chemical-technology'),
            $this->program('60720100', 'Food Technology', 'food-technology-60720100', 'food-technology-service'),
            $this->program('60720200', 'Perfumery and Cosmetic Products Technology', 'perfumery-and-cosmetic-products-technology-60720200', 'agricultural-products-storage-oil-fat-technology'),
            $this->program('60720500', 'Light Industry Production Technology', 'light-industry-production-technology-60720500', 'oil-gas-refining-technology'),
            $this->program('60720600', 'Oil and Oil-Gas Processing Technology', 'oil-and-oil-gas-processing-technology-60720600', 'oil-gas-refining-technology'),
            $this->program('60720900', 'Geology, Prospecting and Exploration of Mineral Resources', 'geology-prospecting-and-exploration-of-mineral-resources-60720900', 'oil-gas-engineering-upstream-downstream'),
            $this->program('60721100', 'Oil and Gas Engineering', 'oil-and-gas-engineering-60721100', 'oil-gas-engineering-upstream-downstream'),
            $this->program('60810700', 'Technology of Storage and Processing of Agricultural Products', 'technology-of-storage-and-processing-of-agricultural-products-60810700', 'agricultural-products-storage-oil-fat-technology'),
            $this->program('60810800', 'Animal Husbandry Engineering', 'animal-husbandry-engineering-60810800', 'agricultural-products-storage-oil-fat-technology'),
            $this->program('60811000', 'Fruit-Vegetable Growing and Viticulture', 'fruit-vegetable-growing-and-viticulture-60811000', 'agricultural-products-storage-oil-fat-technology'),
            $this->program('61010500', 'Cosmetology', 'cosmetology-61010500', 'agricultural-products-storage-oil-fat-technology'),
            $this->program('61020200', 'Occupational Health and Safety', 'occupational-health-and-safety-61020200', 'metrology-standardization-quality-control'),
        ];
    }

    private function program(string $code, string $name, string $slug, string $departmentSlug): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'slug' => $slug,
            'track' => null,
            'department_slug' => $departmentSlug,
            'description' => $name.' is a four-year bachelor program in the Faculty of Technology. It combines theoretical study, laboratory work, practical training, and industry-oriented preparation for modern technology sectors.',
            'career' => 'Graduates can work in manufacturing, processing, food and chemical enterprises, oil and gas organizations, laboratories, quality control units, service organizations, and research or innovation projects.',
        ];
    }
};
