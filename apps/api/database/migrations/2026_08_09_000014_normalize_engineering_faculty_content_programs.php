<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-engineering')->value('id');

        if (! $facultyId) {
            return;
        }

        DB::table('faculty_translations')->updateOrInsert(
            ['faculty_id' => $facultyId, 'locale' => 'en'],
            [
                'name' => 'Faculty of Engineering',
                'short_name' => 'Faculty of Engineering',
                'description' => $this->facultyDescription(),
                'content_sections' => json_encode($this->facultySections(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Faculty of Engineering',
                'meta_description' => Str::limit($this->facultyDescription(), 240, ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ($this->departments() as $slug => $department) {
            DB::table('departments')
                ->where('faculty_id', $facultyId)
                ->where('slug', $slug)
                ->update([
                    'head_name' => $department['head_name'],
                    'email' => $department['email'],
                    'phone' => $department['phone'],
                    'reception_time' => $department['reception_time'],
                    'sort_order' => $department['sort_order'],
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        }

        DB::table('departments')
            ->where('faculty_id', $facultyId)
            ->whereNotIn('slug', array_keys($this->departments()))
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        foreach ($this->leadership() as $item) {
            $profileId = DB::table('staff_profiles')->where('slug', $item['slug'])->value('id');

            if (! $profileId) {
                continue;
            }

            DB::table('staff_profiles')
                ->where('id', $profileId)
                ->update([
                    'faculty_id' => $facultyId,
                    'department_id' => null,
                    'photo' => $item['photo'],
                    'email' => $item['email'],
                    'phone' => $item['phone'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            foreach ($this->leadershipTranslations($item) as $locale => $translation) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        DB::table('programs')
            ->where('faculty_id', $facultyId)
            ->whereNull('official_code')
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        foreach ($this->programs() as $index => $program) {
            $departmentId = DB::table('departments')->where('slug', $program['department_slug'])->value('id');

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
                $programId = DB::table('programs')->insertGetId(array_merge($payload, ['created_at' => now()]));
            }

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                DB::table('program_translations')->updateOrInsert(
                    ['program_id' => $programId, 'locale' => $locale],
                    [
                        'name' => $program['name'],
                        'description' => $program['description'],
                        'requirements' => 'Secondary education certificate or equivalent, application documents, and admission requirements approved by the university.',
                        'documents' => 'Passport, education certificate, transcript, photo, and required application documents.',
                        'curriculum_summary' => $program['curriculum_summary'],
                        'career_opportunities' => $program['career_opportunities'],
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
        $facultyId = DB::table('faculties')->where('slug', 'faculty-of-engineering')->value('id');

        if ($facultyId) {
            DB::table('programs')
                ->where('faculty_id', $facultyId)
                ->whereIn('slug', array_column($this->programs(), 'slug'))
                ->update([
                    'updated_at' => now(),
                ]);
        }
    }

    private function facultyDescription(): string
    {
        return 'The Faculty of Engineering is an academic division that prepares highly qualified specialists in core engineering fields. Students gain theoretical knowledge and practical skills in modern technologies, design, manufacturing processes, construction, energy, and innovative engineering solutions.';
    }

    private function facultySections(): array
    {
        return [
            [
                'key' => 'overview',
                'title' => 'About the Faculty',
                'items' => [
                    'The Faculty of Engineering prepares competent professionals for industry, construction, energy, architecture, manufacturing, and other technical sectors.',
                    'Academic training combines theory, laboratory work, design practice, production processes, and innovative solutions.',
                ],
            ],
            [
                'key' => 'departments',
                'title' => 'Departments',
                'items' => array_map(fn ($department) => $department['name'], $this->departments()),
            ],
            [
                'key' => 'leadership',
                'title' => 'Faculty Leadership',
                'items' => array_map(fn ($leader) => $leader['name'].' - '.$leader['position'], $this->leadership()),
            ],
        ];
    }

    private function departments(): array
    {
        return [
            'electrical-power-engineering' => [
                'name' => 'Electrical and Power Engineering',
                'head_name' => 'Latipov Saidmurod Tuygunovich',
                'email' => 'stlatipov@gmail.com',
                'phone' => '+998 91 979 88 22',
                'reception_time' => 'Tuesday-Thursday 10:00-13:00',
                'sort_order' => 10,
            ],
            'architecture' => [
                'name' => 'Architecture',
                'head_name' => 'Mirzayev Shamsiddin Rajabovich',
                'email' => 'mirzaev.shamsiddin@mail.ru',
                'phone' => '+998 91 014 02 59',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 20,
            ],
            'civil-engineering' => [
                'name' => 'Civil Engineering',
                'head_name' => "Tojiyev In'omjon Ilhomovich",
                'email' => 'arminom@mail.ru',
                'phone' => '+998 91 444 87 03',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 30,
            ],
            'light-industry-engineering-and-design' => [
                'name' => 'Light Industry Engineering and Design',
                'head_name' => 'Qazoqov Farxod Farmonovich',
                'email' => null,
                'phone' => '+998 91 647 65 82',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 40,
            ],
            'mechanics-engineering-graphics' => [
                'name' => 'Mechanics and Engineering Graphics',
                'head_name' => 'Xabibov Faxriddin Yusupovich',
                'email' => 'faxrilo@mail.ru',
                'phone' => '+998 93 379 65 00',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 50,
            ],
            'technological-machines-equipment' => [
                'name' => 'Technological Machines and Equipment',
                'head_name' => 'O‘rinov Uyg‘un Abdullayevich',
                'email' => null,
                'phone' => '+998 90 744 18 22',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 60,
            ],
        ];
    }

    private function leadership(): array
    {
        return [
            [
                'slug' => 'faculty-of-engineering-dean-xojiyev-aziz-kholmurodovich',
                'name' => 'Dr. Khojiyev Aziz Kholmurodovich',
                'position' => 'Dean',
                'phone' => '+998 (90) 744 01 79',
                'email' => 'azizhojiyev1979y@mail.ru',
                'office' => 'Daily 14:00-16:00',
                'photo' => 'cms/staff/khojiyev-aziz-kholmurodovich.jpg',
                'sort_order' => 10,
            ],
            [
                'slug' => 'faculty-of-engineering-academic-rustamov-bobir-ismatovich',
                'name' => 'Rustamov Bobir Ismatovich',
                'position' => 'Deputy Dean for Academic Affairs',
                'phone' => '+998 (99) 704 79 72',
                'email' => 'bobir_rustamov@bk.ru',
                'office' => 'Daily 14:00-16:00',
                'photo' => 'cms/staff/rustamov-bobir-ismatovich.jpg',
                'sort_order' => 20,
            ],
            [
                'slug' => 'faculty-of-engineering-youth-ashurov-asrorjon-komilovich',
                'name' => 'Ashurov Asrorjon Komilovich',
                'position' => 'Deputy Dean for Youth Affairs',
                'phone' => '(+998 97) 488-28-22',
                'email' => 'a.asrorjon83@mail.ru',
                'office' => 'Daily 14:00-16:00',
                'photo' => 'cms/staff/ashurov-asrorjon-komilovich.jpg',
                'sort_order' => 30,
            ],
        ];
    }

    private function leadershipTranslations(array $item): array
    {
        return [
            'en' => ['full_name' => $item['name'], 'position' => $item['position'], 'bio' => $item['position'], 'office' => $item['office']],
            'uz' => ['full_name' => $item['name'], 'position' => $item['position'], 'bio' => $item['position'], 'office' => $item['office']],
            'ru' => ['full_name' => $item['name'], 'position' => $item['position'], 'bio' => $item['position'], 'office' => $item['office']],
            'ar' => ['full_name' => $item['name'], 'position' => $item['position'], 'bio' => $item['position'], 'office' => $item['office']],
        ];
    }

    private function programs(): array
    {
        return [
            $this->program('60210400', 'Design: footwear and accessories design', 'design-footwear-and-accessories-design-60210400', 'Footwear and Accessories Design', 'light-industry-engineering-and-design'),
            $this->program('60210400', 'Design: apparel and textile design', 'design-apparel-and-textile-design-60210400', 'Apparel and Textile Design', 'light-industry-engineering-and-design'),
            $this->program('60210400', 'Design: textile and light industry design', 'design-textile-and-light-industry-design-60210400', 'Textile and Light Industry Design', 'light-industry-engineering-and-design'),
            $this->program('60710400', 'Energy Engineering', 'energy-engineering-60710400', null, 'electrical-power-engineering'),
            $this->program('60710500', 'Electrical Engineering', 'electrical-engineering-60710500', null, 'electrical-power-engineering'),
            $this->program('60711800', 'Environmental Engineering', 'environmental-engineering-60711800', null, 'electrical-power-engineering'),
            $this->program('60712100', 'Renewable Energy Sources', 'renewable-energy-sources-60712100', null, 'electrical-power-engineering'),
            $this->program('60712300', 'Mechanical Engineering', 'mechanical-engineering-60712300', null, 'mechanics-engineering-graphics'),
            $this->program('60720400', 'Technological Machines and Equipment', 'technological-machines-and-equipment-60720400', null, 'technological-machines-equipment'),
            $this->program('60720700', 'Light Industry Engineering', 'light-industry-engineering-60720700', null, 'light-industry-engineering-and-design'),
            $this->program('60721800', 'Industrial Engineering', 'industrial-engineering-60721800', null, 'technological-machines-equipment'),
            $this->program('60730100', 'Architecture', 'architecture-60730100', null, 'architecture'),
            $this->program('60730300', 'Civil Engineering', 'civil-engineering-60730300', null, 'civil-engineering'),
            $this->program('60730400', 'Construction and Operation of Engineering Communications', 'construction-and-operation-of-engineering-communications-60730400', null, 'civil-engineering'),
            $this->program('60730500', 'Highway Engineering', 'highway-engineering-60730500', null, 'civil-engineering'),
            $this->program('60730800', 'Reconstruction and Restoration of Architectural Monuments', 'reconstruction-and-restoration-of-architectural-monuments-60730800', null, 'architecture'),
            $this->program('60730900', 'Urban Planning and Design', 'urban-planning-and-design-60730900', null, 'architecture'),
            $this->program('60731100', 'Production of Construction Materials, Products and Structures', 'production-of-construction-materials-products-and-structures-60731100', null, 'civil-engineering'),
        ];
    }

    private function program(string $code, string $name, string $slug, ?string $track, string $departmentSlug): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'slug' => $slug,
            'track' => $track,
            'department_slug' => $departmentSlug,
            'description' => $name.' is a four-year bachelor program in the Faculty of Engineering. It combines theoretical study, practical training, laboratory work, design projects, and industry-oriented learning.',
            'curriculum_summary' => 'Duration of study: 4 years. Scholarship fields are currently recorded as not specified. Tuition is stored as 0.00 UZS until official fee values are provided.',
            'career_opportunities' => 'Graduates can work in engineering companies, production enterprises, design organizations, construction firms, energy and industrial sectors, public institutions, and research or innovation projects.',
        ];
    }
};
