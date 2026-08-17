<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-service-and-digitalization')->value('id');

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
        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-service-and-digitalization')->value('id');

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
                    'name' => 'Faculty of Service and Digitalization',
                    'short_name' => 'Faculty of Service and Digitalization',
                    'description' => $this->facultyDescription(),
                    'content_sections' => json_encode($this->facultySections(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'meta_title' => 'Faculty of Service and Digitalization',
                    'meta_description' => Str::limit($this->facultyDescription(), 240, ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function syncDepartments(int $facultyId): void
    {
        DB::table('departments')
            ->whereIn('slug', ['information-communication-technologies', 'economics-management', 'artificial-intelligence-digitalization'])
            ->update(['is_active' => false, 'updated_at' => now()]);

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

            foreach ($this->locales() as $locale) {
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
                        'description' => $program['name'].' is a four-year bachelor program in the Faculty of Service and Digitalization. It combines theoretical study, practical training, digital tools, service-sector skills, and industry-oriented learning.',
                        'requirements' => 'Secondary education certificate or equivalent, application documents, and admission requirements approved by the university.',
                        'documents' => 'Passport, education certificate, transcript, photo, and required application documents.',
                        'curriculum_summary' => 'Duration of study: 4 years. Scholarship fields are recorded as not specified. Tuition is stored as 0.00 UZS until official fee values are provided.',
                        'career_opportunities' => 'Graduates can work in digital economy, IT companies, software development, cybersecurity, automation, finance, accounting, management, marketing, tourism, hospitality, service organizations, startups, and innovation projects.',
                        'meta_title' => $program['name'],
                        'meta_description' => Str::limit($program['name'].' program in the Faculty of Service and Digitalization.', 240, ''),
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
        $parentId = DB::table('menu_items')->where('url', '/faculty/faculty-of-service-and-digitalization')->value('id');

        if (! $menuId || ! $parentId) {
            return;
        }

        DB::table('menu_items')
            ->whereIn('url', ['/department/artificial-intelligence-digitalization', '/department/information-communication-technologies', '/department/economics-management'])
            ->update(['is_active' => false, 'updated_at' => now()]);

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
        return 'The Faculty of Service and Digitalization is an advanced educational division that combines modern technologies, innovation, and the service sector. Students gain profound knowledge and practical skills in digital economy, information technologies, service industries, management, startup ideas, IT projects, and modern service solutions.';
    }

    private function facultySections(): array
    {
        return [
            ['key' => 'about', 'title' => 'Fakultet ma\'lumoti', 'items' => [$this->facultyDescription()]],
            ['key' => 'management', 'title' => 'Rahbaryat', 'items' => array_map(fn ($leader) => $leader['name'].' - '.$leader['position'], $this->leadership())],
            ['key' => 'departments', 'title' => 'Departments', 'items' => array_map(fn ($department) => $department['name'], $this->departments())],
            ['key' => 'programs', 'title' => 'Programs and Specializations', 'items' => array_map(fn ($program) => $program['code'].' - '.$program['name'], $this->programs())],
            ['key' => 'international', 'title' => 'International Cooperation', 'items' => ['The faculty supports academic mobility, research projects, international cooperation, startup development, and industry partnerships in technology, service, economics, languages, and digital transformation.']],
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
            ['slug' => 'faculty-of-service-and-digitalization-dean-khayitov-sherbek-nayimovich', 'name' => 'Dr. Khayitov Sherbek Nayimovich', 'position' => 'Dean', 'phone' => '+998 93 686 95 55', 'email' => 'Sherbek-market@mail.ru', 'office' => 'Daily 14:00-16:00', 'photo' => 'cms/staff/Dr. Khayitov Sherbek Nayimovich.jpg', 'sort_order' => 10],
            ['slug' => 'faculty-of-service-and-digitalization-academic-boboqulov-farxod-baxtiyorivich', 'name' => 'Boboqulov Farxod Baxtiyorivich', 'position' => 'Deputy Dean for Academic Affairs', 'phone' => '+998 93 471 16 61', 'email' => 'boboqulovfarxod1993@gmail.ru', 'office' => 'Daily 14:00-16:00', 'photo' => 'cms/staff/Boboqulov Farxod Baxtiyorivich.jpg', 'sort_order' => 20],
            ['slug' => 'faculty-of-service-and-digitalization-youth-fayzullayev-asqar-rajabboevich', 'name' => 'Fayzullayev Asqar Rajabboevich', 'position' => 'Deputy Dean for Youth Affairs', 'phone' => '+998 93 194 75 20', 'email' => 'ayzullayev_asqar_2023@mail.ru', 'office' => 'Daily 14:00-16:00', 'photo' => 'cms/staff/Fayzullayev Asqar Rajabboevich.jpg', 'sort_order' => 30],
        ];
    }

    private function departments(): array
    {
        return [
            'technological-processes-production-automation' => ['name' => 'Department of Automation of Technological Processes and Production', 'short_name' => 'Automation of Technological Processes and Production', 'head_name' => 'Qobilov Hasan Xalilovich', 'email' => 'h.qobilov@mail.ru', 'phone' => '+998 91 409 66 18', 'reception_time' => 'Daily 14:00-16:00', 'sort_order' => 10, 'profile_slug' => 'technological-processes-production-automation-qobilov-hasan-xalilovich', 'photo' => 'cms/staff/Qobilov Hasan Xalilovich.jpg', 'description' => 'The department prepares specialists for automated technological processes, production control systems, mechatronics, robotics, and digital production.', 'science' => ['Research in energy-saving technologies, production automation, ICT systems for technological process control, mechatronics, and intelligent big data.'], 'specialists' => ['60710900 - Automation of Technological Processes and Production', '60711000 - Mechatronics and Robotics', '60711900 - Information and Communication Systems for Technological Process Control'], 'partners' => ['KTH Royal Institute of Technology', 'Turin Polytechnic University', 'University of Leeds', 'University of Leuven', 'Johannes Kepler University', 'Mogilev State University of Food Technologies']],
            'information-and-communication-technologies' => ['name' => 'Department of Information and Communication Technologies', 'short_name' => 'Information and Communication Technologies', 'head_name' => 'Atoyev Fazliddin Sayfiddinovich', 'email' => null, 'phone' => '+998 99 380 81 88', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 20, 'profile_slug' => 'information-and-communication-technologies-atoyev-fazliddin-sayfiddinovich', 'photo' => 'cms/staff/Atoyev Fazliddin Sayfiddinovich.jpg', 'description' => 'The department trains competitive IT specialists in programming, computer networks, artificial intelligence, cybersecurity, information systems, and modern digital solutions.', 'science' => ['Research in distance learning, software systems, AI, cybersecurity, computer networks, data analysis, information retrieval, and applied IT projects.'], 'specialists' => ['60610100 - Information Systems and Technologies', '60610300 - Computer Engineering', '60610400 - Software Engineering', '60610500 - Artificial Intelligence', '60611200 - Cybersecurity Engineering'], 'partners' => ['UPSI Malaysia', 'Novosibirsk State Technical University', 'Al-Farabi Kazakh National University', 'Bukhoro Toza Hudud', 'Uzjamoaloyiha']],
            'economics-and-management' => ['name' => 'Department of Economics and Management', 'short_name' => 'Economics and Management', 'head_name' => 'Boboyev Akmal Choriyevich', 'email' => 'boboyevakmal1974@gmail.com', 'phone' => '+998 97 306 31 32', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 30, 'profile_slug' => 'economics-and-management-boboyev-akmal-choriyevich', 'photo' => 'cms/staff/Boboyev Akmal Choriyevich.jpg', 'description' => 'The department provides education in economic analysis, strategic management, entrepreneurship, marketing, finance, accounting, and modern business management.', 'science' => ['Research in innovative entrepreneurship, digital economy, marketing, management, population forecasting, socio-economic systems, and regional development.'], 'specialists' => ['60410100 - Economics', '60410200 - Accounting', '60410500 - Finance and Financial Technologies', '60410800 - Management', '60411200 - Marketing', '61010100 - Tourism and Hospitality'], 'partners' => ['Belgorod State National Research University', 'International scientific conferences on economics, management, marketing, and innovation']],
            'social-sciences-physical-culture' => ['name' => 'Department of Social Sciences and Physical Culture', 'short_name' => 'Social Sciences and Physical Culture', 'head_name' => 'Murodov Sanjar Aslonovich', 'email' => null, 'phone' => '+998 91 416 75 10', 'reception_time' => 'Daily 14:00-16:00', 'sort_order' => 40, 'profile_slug' => 'social-sciences-physical-culture-murodov-sanjar-aslonovich', 'photo' => 'cms/staff/Murodov Sanjar Aslonovich.jpg', 'description' => 'The department supports well-rounded personal development through social sciences, ethical values, civic education, healthy lifestyle, and physical education.', 'science' => ['Research in social and political history, philosophy, youth tolerance, social attitudes, democratization, and physical culture education.'], 'specialists' => ['Modern History of Uzbekistan', 'Philosophy', 'Religious Studies', 'Constitution of the Republic of Uzbekistan'], 'partners' => ['University and national academic cooperation in social sciences and physical culture']],
            'exact-sciences' => ['name' => 'Department of Exact Sciences', 'short_name' => 'Exact Sciences', 'head_name' => 'Kasimova Guzal Karimovna', 'email' => null, 'phone' => '+998 94 490 22 90', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 50, 'profile_slug' => 'exact-sciences-kasimova-guzal-karimovna', 'photo' => 'cms/staff/Kasimova Guzal Karimovna.jpg', 'description' => 'The department provides foundations in mathematics, physics, logical thinking, analytical methods, and scientific research for engineering and IT fields.', 'science' => ['Research in physics, mathematics, non-traditional energy, spectral analysis, mechanics, molecular physics, electricity, magnetism, optics, and atomic physics.'], 'specialists' => ['Mathematics', 'Physics', 'Analytical methods', 'Scientific foundations for engineering and IT programs'], 'partners' => ['Academic Lyceum No. 1', 'Shofirkon Agricultural Technical College', 'Bukhara and regional schools']],
            'uzbek-foreign-languages' => ['name' => 'Department of Uzbek and Foreign Languages', 'short_name' => 'Uzbek and Foreign Languages', 'head_name' => 'Yusupova Shokhida Batirovna', 'email' => null, 'phone' => '+998 93 623 74 72', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 60, 'profile_slug' => 'uzbek-foreign-languages-yusupova-shokhida-batirovna', 'photo' => 'cms/staff/Yusupova Shokhida Batirovna.jpg', 'description' => 'The department develops students native-language competence, foreign-language fluency, translation, academic writing, and professional communication skills.', 'science' => ['Research in foreign language teaching, dialectal vocabulary, translation studies, advertising language, comparative literature, linguistics, and methodology.'], 'specialists' => ['English', 'German', 'French', 'Russian', 'Uzbek language', 'Academic writing', 'Professional communication'], 'partners' => ['Pushkin State Russian Language Institute', 'International Kostomarov Forum', 'UNESCO-supported academic events']],
        ];
    }

    private function programs(): array
    {
        return [
            ['code' => '60410100', 'name' => 'Economics', 'slug' => 'economics-60410100', 'department_slug' => 'economics-and-management'],
            ['code' => '60410200', 'name' => 'Accounting', 'slug' => 'accounting-60410200', 'department_slug' => 'economics-and-management'],
            ['code' => '60410500', 'name' => 'Finance and Financial Technologies', 'slug' => 'finance-and-financial-technologies-60410500', 'department_slug' => 'economics-and-management'],
            ['code' => '60410800', 'name' => 'Management', 'slug' => 'management-60410800', 'department_slug' => 'economics-and-management'],
            ['code' => '60411200', 'name' => 'Marketing', 'slug' => 'marketing-60411200', 'department_slug' => 'economics-and-management'],
            ['code' => '60610100', 'name' => 'Information Systems and Technologies', 'slug' => 'information-systems-and-technologies-60610100', 'department_slug' => 'information-and-communication-technologies'],
            ['code' => '60610300', 'name' => 'Computer Engineering', 'slug' => 'computer-engineering-60610300', 'department_slug' => 'information-and-communication-technologies'],
            ['code' => '60610400', 'name' => 'Software Engineering', 'slug' => 'software-engineering-60610400', 'department_slug' => 'information-and-communication-technologies'],
            ['code' => '60610500', 'name' => 'Artificial Intelligence', 'slug' => 'artificial-intelligence-60610500', 'department_slug' => 'information-and-communication-technologies'],
            ['code' => '60611200', 'name' => 'Cybersecurity Engineering', 'slug' => 'cybersecurity-engineering-60611200', 'department_slug' => 'information-and-communication-technologies'],
            ['code' => '60710900', 'name' => 'Automation of Technological Processes and Production', 'slug' => 'automation-of-technological-processes-and-production-60710900', 'department_slug' => 'technological-processes-production-automation'],
            ['code' => '60711000', 'name' => 'Mechatronics and Robotics', 'slug' => 'mechatronics-and-robotics-60711000', 'department_slug' => 'technological-processes-production-automation'],
            ['code' => '61010100', 'name' => 'Tourism and Hospitality', 'slug' => 'tourism-and-hospitality-61010100', 'department_slug' => 'economics-and-management'],
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
