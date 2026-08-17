<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->heads() as $departmentSlug => $head) {
            $departmentId = DB::table('departments')->where('slug', $departmentSlug)->value('id');

            if (! $departmentId) {
                continue;
            }

            DB::table('departments')
                ->where('id', $departmentId)
                ->update([
                    'head_name' => $head['name'],
                    'email' => $head['email'],
                    'phone' => $head['phone'],
                    'reception_time' => $head['office'],
                    'updated_at' => now(),
                ]);

            $profileId = DB::table('staff_profiles')->where('slug', $head['profile_slug'])->value('id');

            if (! $profileId) {
                $profileId = DB::table('staff_profiles')->insertGetId([
                    'slug' => $head['profile_slug'],
                    'department_id' => $departmentId,
                    'faculty_id' => null,
                    'photo' => $head['photo'],
                    'email' => $head['email'],
                    'phone' => $head['phone'],
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('staff_profiles')
                    ->where('id', $profileId)
                    ->update([
                        'department_id' => $departmentId,
                        'photo' => $head['photo'],
                        'email' => $head['email'],
                        'phone' => $head['phone'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
            }

            foreach ($this->headTranslations($head) as $locale => $translation) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        foreach ($this->departmentContent() as $slug => $content) {
            $departmentId = DB::table('departments')->where('slug', $slug)->value('id');

            if (! $departmentId) {
                continue;
            }

            DB::table('department_translations')->updateOrInsert(
                ['department_id' => $departmentId, 'locale' => 'en'],
                [
                    'name' => $content['name'],
                    'short_name' => $content['short_name'] ?? $content['name'],
                    'description' => $content['description'],
                    'content_sections' => json_encode($content['sections'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'meta_title' => $content['name'],
                    'meta_description' => Str::limit($content['description'], 240, ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        foreach ($this->heads() as $head) {
            DB::table('staff_profiles')
                ->where('slug', $head['profile_slug'])
                ->update([
                    'photo' => null,
                    'updated_at' => now(),
                ]);
        }
    }

    private function heads(): array
    {
        return [
            'technological-processes-production-automation' => [
                'profile_slug' => 'technological-processes-production-automation-kabilov-hasan-khalilovich',
                'name' => 'Qobilov Hasan Xalilovich',
                'email' => 'h.qobilov@mail.ru',
                'phone' => '+998 91 409 66 18',
                'office' => 'Daily 14:00-16:00',
                'photo' => 'cms/staff/Qobilov Hasan Xalilovich.jpg',
            ],
            'information-communication-technologies' => [
                'profile_slug' => 'information-communication-technologies-atoyev-fazliddin-sayfiddinovich',
                'name' => 'Atoyev Fazliddin Sayfiddinovich',
                'email' => null,
                'phone' => '+998 99 380 81 88',
                'office' => 'Monday-Friday 14:00-16:00',
                'photo' => 'cms/staff/Atoyev Fazliddin Sayfiddinovich.jpg',
            ],
            'economics-management' => [
                'profile_slug' => 'economics-management-boboyev-akmal-choriyevich',
                'name' => 'Boboyev Akmal Choriyevich',
                'email' => 'boboyevakmal1974@gmail.com',
                'phone' => '+998 97 306-31-32',
                'office' => 'Monday-Friday 14:00-16:00',
                'photo' => 'cms/staff/Boboyev Akmal Choriyevich.jpg',
            ],
            'social-sciences-physical-culture' => [
                'profile_slug' => 'social-sciences-physical-culture-ijtimoiy-fanlar-va-jismoniy-madaniyat',
                'name' => 'Murodov Sanjar Aslonovich',
                'email' => null,
                'phone' => '+998 91 416 75 10',
                'office' => 'Daily 14:00-16:00',
                'photo' => 'cms/staff/Murodov Sanjar Aslonovich.jpg',
            ],
            'exact-sciences' => [
                'profile_slug' => 'exact-sciences-kasimova-guzal-karimovna',
                'name' => 'Kasimova Guzal Karimovna',
                'email' => null,
                'phone' => '+998 94 490 22 90',
                'office' => 'Monday-Friday 14:00-16:00',
                'photo' => 'cms/staff/Kasimova Guzal Karimovna.jpg',
            ],
            'uzbek-foreign-languages' => [
                'profile_slug' => 'uzbek-foreign-languages-yusupova-shoxida-batirovna',
                'name' => 'Yusupova Shokhida Batirovna',
                'email' => null,
                'phone' => '+998 93 623 74 72',
                'office' => 'Monday-Friday 14:00-16:00',
                'photo' => 'cms/staff/Yusupova Shokhida Batirovna.jpg',
            ],
        ];
    }

    private function headTranslations(array $head): array
    {
        return [
            'en' => ['full_name' => $head['name'], 'position' => 'Head of Department', 'bio' => 'Head of Department', 'office' => $head['office']],
            'uz' => ['full_name' => $head['name'], 'position' => 'Kafedra mudiri', 'bio' => 'Kafedra mudiri', 'office' => str_replace(['Daily', 'Monday-Friday'], ['Har kuni', 'Dushanba-Juma'], $head['office'])],
            'ru' => ['full_name' => $head['name'], 'position' => 'Заведующий кафедрой', 'bio' => 'Заведующий кафедрой', 'office' => str_replace(['Daily', 'Monday-Friday'], ['Ежедневно', 'Понедельник-Пятница'], $head['office'])],
            'ar' => ['full_name' => $head['name'], 'position' => 'رئيس القسم', 'bio' => 'رئيس القسم', 'office' => str_replace(['Daily', 'Monday-Friday'], ['يوميا', 'الاثنين-الجمعة'], $head['office'])],
        ];
    }

    private function departmentContent(): array
    {
        return [
            'technological-processes-production-automation' => $this->content(
                'Department of Automation of Technological Processes and Production',
                'The department prepares specialists who design, operate, and optimize automated technological processes, control systems, and digital production solutions.',
                [
                    'overview' => [
                        'The field of automation is the profession of the future. Modern factories, plants, and manufacturing enterprises depend on automated systems, robots, and control systems.',
                        'Students learn not only to operate machinery, but also to optimize the full production process from raw materials to finished products.',
                    ],
                    'history' => [
                        'The Bukhara Institute of Food and Light Industry Technology was established in 1977. In 1979, the Department of Production Processes, Apparatus and Their Automation was founded under Professor A.O. Ortikov.',
                        'In 2003 it was renamed Technological Machines, Equipment and Production Automation, in 2011 Automation of Modern Technology and Technological Processes, and from 2013 Information and Communication Systems for Technological Process Control.',
                        'The department trains bachelor and master students in Information and Communication Systems for Technological Process Control and is headed by H.X. Qobilov, Candidate of Technical Sciences, Associate Professor.',
                    ],
                    'research' => [
                        'Research areas include energy-saving technologies and equipment for processing agricultural products, information and communication systems for improving production efficiency, and digital technologies in technical education.',
                        'Projects include coal briquette production technology, resource-saving plant raw material processing using liquefied carbon dioxide, high-pressure extract production control systems, and the Erasmus+ ELBA intelligent big data project.',
                    ],
                    'subjects' => [
                        'Bachelor subjects include process control software, programming language applications, control systems, technological measurements, computer networks, control theory, modeling and optimization, automation systems design, DBMS, information security, and digital automation.',
                        'Master subjects include ICT system development for process control, optimization and diagnostics, intelligent control systems, CAD/CAE/CAM, machine learning for IoT, research work, and scientific-pedagogical work.',
                    ],
                    'prepared_specialists' => [
                        '60711900 - Information and Communication Systems for Technological Process Control.',
                        '70711901 - Information and Communication Systems for Technological Process Control.',
                    ],
                    'cooperation' => [
                        'Partners include major production enterprises in Uzbekistan and universities such as KTH Royal Institute of Technology, Turin Polytechnic University, University of Leeds, University of Leuven, University of Santiago de Compostela, University of Primorska, Johannes Kepler University, Stankin, Mogilev State University of Food Technologies, Kostanay State University, and Almaty University of International Information Technologies.',
                        'The Tempus-Mach and Erasmus+ ELBA projects supported new master, doctoral, and elective courses and helped establish mechatronics and intelligent big data training infrastructure.',
                    ],
                ]
            ),
            'information-communication-technologies' => $this->content(
                'Department of Information and Communication Technologies',
                'The department trains competitive IT specialists in programming, networks, artificial intelligence, cybersecurity, information systems, and modern digital solutions.',
                [
                    'overview' => [
                        'The future belongs to digital technologies. The ICT department equips students with knowledge in programming, computer networks, artificial intelligence, cybersecurity, and modern IT solutions.',
                    ],
                    'history' => [
                        'The department began in 1979 as Applied Mathematics. It later operated as Informatics, Informatics and Information Technologies, Information Technologies, and from August 2017 as Information and Communication Technologies.',
                        'Since 1992, Computing Technology became part of the department. The department has trained more than 3,000 specialists and now prepares students in Information Systems and Technologies, Computer Engineering, Software Engineering, and Artificial Intelligence.',
                        'The department has modern classrooms, laboratories, and multimedia rooms, and its staff includes doctoral candidates and independent researchers.',
                    ],
                    'research' => [
                        'Research focuses on education-production integration, computer technologies, telecommunication tools, distance learning systems, automated management systems, electronic textbooks, reliability of automation system elements, vaccine logistics systems, energy-saving gas installations, and automatic solid waste identification.',
                    ],
                    'subjects' => [
                        'Bachelor subjects include programming, web development, mobile development, AI systems, cybersecurity, computer networks, databases, cloud technologies, IoT, software engineering, big data, information systems design, and software testing.',
                        'Master subjects include information systems design, computer network administration, algorithms, parallel computing, mobile engineering, ERP systems, AI systems, intelligent data analysis, information retrieval, Java web technologies, and cryptography.',
                    ],
                    'prepared_specialists' => [
                        '60610200 - Information Systems and Technologies.',
                        '60610100 - Information Systems and Technologies.',
                        '60610300 - Computer Engineering.',
                        '60610400 - Software Engineering.',
                        '60610500 - Artificial Intelligence.',
                        '70610101 - Computer Systems and Their Software.',
                        '13.00.06 - Theory and Methodology of Digital Education.',
                        '10.01.05 - Information Retrieval Systems and Processes.',
                    ],
                    'cooperation' => [
                        'Cooperation includes UPSI Malaysia, Novosibirsk State Technical University, Al-Farabi Kazakh National University, Bukhoro Toza Hudud, Uzjamoaloyiha, Bukhara Real Moto, and international grants in Latvia, Sweden, Tajikistan, Kazakhstan, Slovenia, Russia, and Malaysia.',
                    ],
                ]
            ),
            'economics-management' => $this->content(
                'Department of Economics and Management',
                'The department provides education in economic analysis, strategic management, entrepreneurship, marketing, and modern business management.',
                [
                    'overview' => [
                        'Successful businesses and effective leadership begin with quality education. The department develops future managers, economists, marketers, and entrepreneurs.',
                    ],
                    'history' => [
                        'The department history begins with the Economics department established on September 1, 1969 at the General Technical Faculty of the Tashkent Polytechnic Institute.',
                        'Over the years, economics, marketing, production organization, and management units operated under different names. In the current structure, the Economics and Management department functions as a unified department.',
                    ],
                    'research' => [
                        'Research includes investment attractiveness, innovative entrepreneurship, management qualities in higher education, entrepreneurial skills, population forecasting, marketing efficiency, regional development, digital economy, tourism digitalization, and integrated marketing communications.',
                    ],
                    'subjects' => [
                        'Subjects include Economic Theory, Business, Economic Analysis, Statistics, Microeconomics, Econometrics, Management, Marketing, Operations Management, Strategic Management, Finance, Accounting, HR Management, Digital Economy, Taxes, Entrepreneurship, Project Management, International Management, Logistics, CRM Marketing, and Internet Marketing.',
                    ],
                    'prepared_specialists' => [
                        '61010100 - Tourism and Hospitality.',
                        '60410800 - Management.',
                        '60410100 - Economics.',
                        '60411200 - Marketing.',
                        '70310102 - Economics (by branches and fields).',
                        '70410801 - Management.',
                    ],
                    'cooperation' => [
                        'Academic cooperation includes scientific conferences and publications with Belgorod State National Research University and other international scientific venues focused on socio-economic systems, regional development, management, marketing, and innovation.',
                    ],
                ]
            ),
            'social-sciences-physical-culture' => $this->content(
                'Department of Social Sciences and Physical Culture',
                'The department supports well-rounded development through social sciences, ethical values, history, philosophy, civic education, and physical culture.',
                [
                    'overview' => [
                        'Well-rounded personal development is one of the key goals of modern education. The department supports social awareness, ethical values, healthy lifestyles, and active citizenship.',
                    ],
                    'history' => [
                        'The department was established in 1968. It later operated under names including Philosophy, Political History, Political Science and Law, Social Sciences, History and Philosophy, History, and History of Uzbekistan.',
                        'On September 1, 2024, it was merged with the Physical Culture department and became Social Sciences and Physical Culture. From April 1, 2025, it joined Bukhara State Technical University under this name.',
                    ],
                    'research' => [
                        'Research areas include the socio-political and spiritual situation in Bukhara in the late 19th and early 20th centuries, philosophical and methodological problems of forming a well-rounded individual, and democratization of society under independence.',
                        'Individual research topics include Rudaki social and ethical views, Jadidism and youth tolerance, youth attitudes toward life, Zarafshan valley social-economic history, modern world politics, and Yaqub Charkhi socio-philosophical ideas.',
                    ],
                    'subjects' => [
                        'Subjects include Modern History of Uzbekistan, Philosophy, Religious Studies, and the Constitution of the Republic of Uzbekistan in the New Edition.',
                    ],
                    'prepared_specialists' => [],
                    'cooperation' => [],
                ]
            ),
            'exact-sciences' => $this->content(
                'Department of Exact Sciences',
                'The department provides a foundation in mathematics, physics, scientific thinking, analytical methods, and laboratory research for engineering and technology students.',
                [
                    'overview' => [
                        'Exact Sciences develops logical thinking, analytical approaches, and scientific research skills through mathematics, physics, and modern scientific methods.',
                    ],
                    'history' => [
                        'The department was established in 1972 on the basis of the Bukhara branch of the former Tashkent Polytechnic Institute. It was headed by Prof. M.N. Rakhmatov, Prof. M.T. Toshev, Prof. S.Kh. Astanov, M.Z. Sharipov, and O.S. Komilov.',
                        'Currently, the head of department is PhD in Physical and Mathematical Sciences G.K. Kasimova.',
                        'The department operates Mechanics and Molecular Physics, Electricity and Magnetism, Optics, and Atomic and Nuclear Physics teaching laboratories, as well as research laboratories in non-traditional energy and spectral analysis.',
                    ],
                    'research' => [
                        'Research is conducted by professors, associate professors, doctoral students, and laboratory staff in physics, mathematics, non-traditional energy, spectral analysis, and related exact science fields.',
                    ],
                    'subjects' => [],
                    'prepared_specialists' => [],
                    'cooperation' => [
                        'Cooperation agreements exist with academic lyceums, agricultural technical colleges, and secondary schools, continuing mentor-student traditions with teaching staff.',
                    ],
                ]
            ),
            'uzbek-foreign-languages' => $this->content(
                'Department of Uzbek and Foreign Languages',
                'The department develops native and foreign language proficiency, translation, academic writing, communication skills, and professional language use.',
                [
                    'overview' => [
                        'The department helps students strengthen Uzbek language use and develop foreign language fluency, translation skills, written communication, and oral communication.',
                    ],
                    'history' => [
                        'The Department of Foreign Languages was established on September 1, 1969 on the basis of the evening faculty of the Tashkent Polytechnic Institute named after Abu Rayhan Beruni in Bukhara.',
                        'After the Bukhara branch became the Bukhara Institute of Food and Light Industry Technology in 1978, the department continued teaching English, German, and French while developing educational-methodological, research, and mentor-student activities.',
                        'The department currently employs 46 teachers.',
                    ],
                    'research' => [
                        'Research includes monographs on teaching foreign languages, dialectal vocabulary, language teaching methodology, vocabulary teaching, modern technology in English teaching, non-codified vocabulary, translation studies, continuous foreign language learning, homonyms, authentic reading materials, advertising language, elegy and marsiya genres, and Uzbek and world poetry.',
                    ],
                    'subjects' => [
                        'Subjects include English, German, French, Russian, Uzbek language, culture of speech, academic writing, and application of the Uzbek language in the professional field.',
                    ],
                    'prepared_specialists' => [
                        'The department teaches all bachelor degree programs at the university.',
                        '10.00.06 - Comparative Literary Studies, Comparative Linguistics and Translation Studies.',
                        '10.00.04 - Language and Literature of the Peoples of Europe, America and Australia.',
                    ],
                    'cooperation' => [
                        'The department participates in international language and intercultural communication events, including the International Kostomarov Forum organized by the Pushkin State Russian Language Institute with UNESCO support.',
                    ],
                ]
            ),
        ];
    }

    private function content(string $name, string $description, array $groups): array
    {
        $titles = [
            'overview' => 'About the Department',
            'history' => 'Department History',
            'research' => 'Scientific Activity',
            'subjects' => 'Subjects Taught at the Department',
            'prepared_specialists' => 'Specialists Trained by the Department',
            'cooperation' => 'International Cooperation',
        ];

        return [
            'name' => $name,
            'description' => $description,
            'sections' => array_values(array_map(
                fn (string $key, array $items) => ['key' => $key, 'title' => $titles[$key] ?? ucfirst($key), 'items' => $items],
                array_keys($groups),
                $groups
            )),
        ];
    }
};
