<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $photos = [
        'oil-gas-refining-technology-0-ochilov-abduraxim-abdurasulovich' => 'cms/staff/ochilov-abduraxim-abdurasulovich.jpg',
        'food-technology-service-qurbonov-murod-tashpulatovich' => 'cms/staff/Qurbonov Murod Tashpulatovich.jpg',
        'chemical-technology-axmedov-voxid-nizomovich' => 'cms/staff/Akhmedov Voxid Nizomovich.jpg',
        'agricultural-products-storage-oil-fat-technology-majidova-nargiza-kaxramonovna' => 'cms/staff/Majidova Nargiza Kaxramonovna.jpg',
        'oil-gas-engineering-upstream-downstream-sharipov-qaxramon-qandiyorovich' => 'cms/staff/Sharipov Qakhramon Qandiyorovich.jpg',
        'metrology-standardization-quality-control-kafedra-mudiri' => 'cms/staff/Tairov Bakhtiyor Boboqulovich.jpg',
    ];

    public function up(): void
    {
        foreach ($this->photos as $slug => $photo) {
            DB::table('staff_profiles')
                ->where('slug', $slug)
                ->update([
                    'photo' => $photo,
                    'updated_at' => now(),
                ]);
        }

        DB::table('departments')
            ->where('slug', 'metrology-standardization-quality-control')
            ->update([
                'head_name' => 'Tairov Bakhtiyor Boboqulovich',
                'email' => 'b.toirov@mail.ru',
                'phone' => '+998 93 471 00 65',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'updated_at' => now(),
            ]);

        $staffProfileId = DB::table('staff_profiles')
            ->where('slug', 'metrology-standardization-quality-control-kafedra-mudiri')
            ->value('id');

        if ($staffProfileId) {
            DB::table('staff_profiles')
                ->where('id', $staffProfileId)
                ->update([
                    'email' => 'b.toirov@mail.ru',
                    'phone' => '+998 93 471 00 65',
                    'updated_at' => now(),
                ]);

            foreach ($this->tairovTranslations() as $locale => $translation) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $staffProfileId, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $departmentId = DB::table('departments')
            ->where('slug', 'metrology-standardization-quality-control')
            ->value('id');

        if ($departmentId) {
            DB::table('department_translations')->updateOrInsert(
                ['department_id' => $departmentId, 'locale' => 'en'],
                [
                    'name' => 'Metrology and Standardization',
                    'short_name' => 'Metrology and Standardization',
                    'description' => $this->metrologyDescription(),
                    'content_sections' => json_encode($this->metrologySections(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'meta_title' => 'Metrology and Standardization',
                    'meta_description' => $this->metrologyDescription(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('staff_profiles')
            ->whereIn('slug', array_keys($this->photos))
            ->update([
                'photo' => null,
                'updated_at' => now(),
            ]);
    }

    private function tairovTranslations(): array
    {
        return [
            'en' => [
                'full_name' => 'Tairov Bakhtiyor Boboqulovich',
                'position' => 'Head of the Department',
                'bio' => 'Head of the Department',
                'office' => 'Monday-Friday 14:00-16:00',
            ],
            'uz' => [
                'full_name' => 'Tairov Bakhtiyor Boboqulovich',
                'position' => 'Kafedra mudiri',
                'bio' => 'Kafedra mudiri',
                'office' => 'Dushanba-Juma 14:00-16:00',
            ],
            'ru' => [
                'full_name' => 'Tairov Bakhtiyor Boboqulovich',
                'position' => 'Заведующий кафедрой',
                'bio' => 'Заведующий кафедрой',
                'office' => 'Понедельник-Пятница 14:00-16:00',
            ],
            'ar' => [
                'full_name' => 'Tairov Bakhtiyor Boboqulovich',
                'position' => 'رئيس القسم',
                'bio' => 'رئيس القسم',
                'office' => 'الاثنين-الجمعة 14:00-16:00',
            ],
        ];
    }

    private function metrologyDescription(): string
    {
        return 'Metrology and Standardization is a field that ensures order, precision, and reliability. Every measurement and every standard is a guarantee of people\'s safety and product quality.';
    }

    private function metrologySections(): array
    {
        return [
            [
                'key' => 'overview',
                'title' => 'About the Department',
                'items' => [
                    'Metrology and Standardization is a field that ensures order, precision, and reliability. Every measurement and every standard is a guarantee of people\'s safety and product quality.',
                    'A specialist who chooses this direction holds one of the invisible yet most vital pillars of society. Whether it is factory-made products, medicines, or construction materials, the quality of all of these is under the control of professionals in this field.',
                    'Where there is no standard, there is no order. Where there is no order, there is no progress.',
                ],
            ],
            [
                'key' => 'history',
                'title' => 'History of the Department',
                'items' => [
                    'The department began its activities under the name "Metrology and Standardization" from August 2019, based on the newly established structure of the institute. It has been training specialists in the bachelor\'s degree program 5310900 - Metrology, Standardization and Product Quality Management.',
                    'The department has 13 faculty members, including 1 Candidate of Sciences - Professor, 6 Candidates of Sciences - Associate Professors, 1 Senior Lecturer, 3 Assistants, and 2 Trainee Lecturers. The scientific potential amounts to 54%.',
                    'Over the past two years, 2 Doctor of Philosophy (PhD) dissertations were defended by the department\'s faculty members. Co-authored publications include 1 electronic textbook (DGU), 5 international textbooks with impact factor, 3 study guides, 6 monographs, 19 articles in 6 journals, 20 articles in VAK-listed journals, and 5 articles in the Scopus database.',
                    'The department is implementing the grant project AIF-2/20 "Improving the quality of training qualified engineering personnel and enhancing the professional development of faculty based on person-centered innovative technologies in higher technical education institutions", funded by the World Bank under the Academic Innovation Fund project "Modernization of the Higher Education System".',
                    'Today, special attention is paid to training students in Metrology, Standardization and Product Quality Management to provide production enterprises with highly qualified and competitive personnel and to meet the demands of rapidly developing production processes and improved measurement accuracy.',
                ],
            ],
            [
                'key' => 'research',
                'title' => 'Scientific Activity',
                'items' => [
                    'Kamalova Mukhlisa Khudoyberdiyevna - Improvement of the food production system and quality process control based on international standards.',
                    'Yodgorova Mamura Orifovna - Improvement of the extraction process of coniferous plants based on non-traditional methods of treatment.',
                    'Sayidakhmedov Ravshan Rajabovich - Improvement of the drying process of wheat flour gluten grown under local conditions using acoustic treatment.',
                    'Shadiyev Sukhrab Sadilloyevich - Improvement of the comprehensive quality assessment system for confectionery products.',
                    'Khaydarov Shukhrat Khikmatilloyevich - Development of a promising technology for low-alcohol and non-alcoholic beverages using phyto-additives.',
                    'Azimova Feruza Kamolovna - Methodology for developing professional skills of future engineers based on a metacognitive approach.',
                ],
            ],
            [
                'key' => 'subjects',
                'title' => 'Subjects Taught at the Department',
                'items' => [
                    'Bachelor\'s degree: Introduction to the specialization; Fundamentals of metrology; Physical fundamentals of measurements; Methods and means of measurement; Measuring instruments, elements and their design; Metrological support of production; Product quality and statistical methods of quality management; Comparison and calibration of measuring instruments; Fundamentals of conformity assessment; Assessment of laboratory competence; Comparison engineering; Fundamentals of standardization; Technical regulation and standardization; Product quality control; Quality management system; Intelligent measuring instruments; Commodity science; Commodity expertise; Technology for developing standards and normative documents and their expertise; Professional psychology.',
                    'Master\'s degree: Research methodology; Theoretical foundations of qualimetry and quality management; Theoretical foundations of automated and intelligent measuring instruments; The role of standardization and technical regulation systems in quality management; Organization and planning of experiments; Legal foundations of metrology, technical regulation, standardization and certification; Patent science, licensing and certification; Reengineering; Methods of teaching special disciplines; Research work and preparation of master\'s thesis; Scientific and pedagogical work; Research internship.',
                ],
            ],
            [
                'key' => 'prepared_specialists',
                'title' => 'Specialists Trained by the Department',
                'items' => [
                    '60710800 - Metrology and Standardization.',
                    '60711300 - Metrology, Standardization and Product Quality Management (by industries).',
                    '70710802 - Metrology, Standardization and Quality Management.',
                ],
            ],
            [
                'key' => 'cooperation',
                'title' => 'International Cooperation',
                'items' => [
                    'A three-year strategic cooperation memorandum was signed between Bukhara State Technical University and South Kazakhstan State University named after M. Auezov to strengthen cooperation in education, science and innovation.',
                    'Within this framework, faculty members of both universities participate in professional development programs, jointly develop educational literature, and provide academic supervision for PhD and DSc level research.',
                    'Based on the cooperation memorandum signed between Al-Farabi Kazakh National University and Bukhara State Technical University, cooperation is developing with the Thermal Physics and Technical Physics department of the Faculty of Physics.',
                    'A joint master\'s degree educational program was introduced to train specialists in standardization, certification and metrology. In the 2025-2026 academic year, applications are being accepted for 70710800 - Metrology, Standardization and Quality Management (by industries).',
                ],
            ],
        ];
    }
};
