<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $photos = [
        'irrigation-melioration-murodov-otabek-ulugbekovich' => 'cms/staff/Murodov Otabek Ulugbekovich.jpg',
        'agricultural-water-resources-engineering-technologies-rajabov-yarash-jabborovich' => 'cms/staff/Rajabov Yarash Jabborovich.jpg',
        'land-resources-management-state-land-cadastres-asatov-sayitqul-rahimberdiyevich' => 'cms/staff/Asatov Sayitqul Rahimberdiyevich.jpg',
        'industrial-ecology-hydrogeology-xaitov-rauf-arifovich' => 'cms/staff/Xaitov Rauf Arifovich.jpg',
        'vehicle-engineering-automotive-transport-systems-kafedra-mudiri-associate-professor' => 'cms/staff/Gaffarov Hasan Ravshanovich.jpg',
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

        foreach ($this->heads() as $departmentSlug => $head) {
            DB::table('departments')
                ->where('slug', $departmentSlug)
                ->update([
                    'head_name' => $head['name'],
                    'email' => $head['email'],
                    'phone' => $head['phone'],
                    'reception_time' => $head['office'],
                    'updated_at' => now(),
                ]);

            $profileId = DB::table('staff_profiles')
                ->where('slug', $head['profile_slug'])
                ->value('id');

            if ($profileId) {
                DB::table('staff_profiles')
                    ->where('id', $profileId)
                    ->update([
                        'email' => $head['email'],
                        'phone' => $head['phone'],
                        'updated_at' => now(),
                    ]);

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
                    'short_name' => $content['name'],
                    'description' => $content['description'],
                    'content_sections' => json_encode($content['sections'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'meta_title' => $content['name'],
                    'meta_description' => $content['description'],
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

    private function heads(): array
    {
        return [
            'irrigation-melioration' => [
                'profile_slug' => 'irrigation-melioration-murodov-otabek-ulugbekovich',
                'name' => 'Murodov Otabek Ulugbekovich',
                'email' => 'murodovou@gmail.com',
                'phone' => '+998 94 327 35 00',
                'office' => 'Monday-Friday 14:00-16:00',
            ],
            'agricultural-water-resources-engineering-technologies' => [
                'profile_slug' => 'agricultural-water-resources-engineering-technologies-rajabov-yarash-jabborovich',
                'name' => 'Rajabov Yarash Jabborovich',
                'email' => null,
                'phone' => '+998 93 138 84 20',
                'office' => 'Monday-Friday 14:00-16:00',
            ],
            'land-resources-management-state-land-cadastres' => [
                'profile_slug' => 'land-resources-management-state-land-cadastres-asatov-sayitqul-rahimberdiyevich',
                'name' => 'Asatov Sayitqul Rahimberdiyevich',
                'email' => null,
                'phone' => '+998 91 408 50 13',
                'office' => 'Monday-Friday 14:00-16:00',
            ],
            'industrial-ecology-hydrogeology' => [
                'profile_slug' => 'industrial-ecology-hydrogeology-xaitov-rauf-arifovich',
                'name' => 'Xaitov Rauf Arifovich',
                'email' => 'sanoat_ekologiyasi@mail.ru',
                'phone' => '+998 91 405 66 24',
                'office' => 'Monday-Friday 14:00-16:00',
            ],
            'vehicle-engineering-automotive-transport-systems' => [
                'profile_slug' => 'vehicle-engineering-automotive-transport-systems-kafedra-mudiri-associate-professor',
                'name' => 'Gaffarov Hasan Ravshanovich',
                'email' => null,
                'phone' => '+998 97 306 07 37',
                'office' => 'Monday-Friday 14:00-16:00',
            ],
        ];
    }

    private function headTranslations(array $head): array
    {
        return [
            'en' => [
                'full_name' => $head['name'],
                'position' => 'Head of Department',
                'bio' => 'Head of Department',
                'office' => $head['office'],
            ],
            'uz' => [
                'full_name' => $head['name'],
                'position' => 'Kafedra mudiri',
                'bio' => 'Kafedra mudiri',
                'office' => 'Dushanba-Juma 14:00-16:00',
            ],
            'ru' => [
                'full_name' => $head['name'],
                'position' => 'Заведующий кафедрой',
                'bio' => 'Заведующий кафедрой',
                'office' => 'Понедельник-Пятница 14:00-16:00',
            ],
            'ar' => [
                'full_name' => $head['name'],
                'position' => 'رئيس القسم',
                'bio' => 'رئيس القسم',
                'office' => 'الاثنين-الجمعة 14:00-16:00',
            ],
        ];
    }

    private function departmentContent(): array
    {
        return [
            'irrigation-melioration' => [
                'name' => 'Department of Irrigation and Land Reclamation',
                'description' => 'The department trains specialists in water management, land reclamation, water supply engineering systems, and water-saving irrigation technologies.',
                'sections' => $this->sections([
                    'history' => [
                        'In 2024, due to a reorganization of the institute internal structure, the departments of Water Resources Management and Land Reclamation and Water-Saving Irrigation Technologies were optimized and merged into the Department of Irrigation and Land Reclamation.',
                        'By Resolution No. PQ-22 of the President of the Republic of Uzbekistan dated January 24, 2025, the name of the department was retained without changes within Bukhara State Technical University.',
                        'Since 2010, the department has trained specialists in Water Management and Land Reclamation. From 2024, it became a specialized department for bachelor and master programs connected with water management, land reclamation, and irrigation technologies.',
                        'The department has modern classrooms, laboratories, and branch offices at production enterprises. In 2024-2025, one DSc and seven PhD dissertations were successfully defended.',
                    ],
                    'research' => [
                        'Research includes cost-effective irrigation technologies for winter wheat in saline soils, irrigation with low-mineralized drainage water, drip irrigation of cotton in saline soils of the Bukhara oasis, and technologies for improving reclamation conditions.',
                        'Faculty members have published scientific papers in AIP Conference Proceedings, IOP Conference Series, BIO Web of Conferences, and European Journal of Agricultural and Rural Education, and have registered software certificates for irrigation and reclamation assessment models.',
                    ],
                    'subjects' => [
                        'Bachelor programs include Introduction to the Specialty, Automation Systems for Drip Irrigation, Smart Water Metering Devices, Irrigation and Land Reclamation, Salt Leaching Technology, Water-Saving Irrigation Technologies, Water Supply, Water Purification, Drip Irrigation Networks, and Irrigation Technology.',
                        'Master programs include Improving the Quality of Natural Waters, Water-Saving Irrigation Technologies, Reclamation Soil Science and Agriculture, Water Cadastre, Integrated Water Resources Management, Operation and Automation of Irrigation Networks, and Rural and Pasture Water Supply.',
                    ],
                    'prepared_specialists' => [
                        '60812300 - Water Management and Land Reclamation.',
                        '60812900 - Engineering Systems of Water Supply.',
                        '60813000 - Innovative Technologies in Water Management and Their Application.',
                        '70812306 - Water-Saving Irrigation Technologies.',
                        '70811209 - Pasture Land Reclamation.',
                        '70811204 - Engineering Systems of Agricultural Water Supply.',
                        '70812306 - Land Reclamation and Irrigated Agriculture.',
                    ],
                    'cooperation' => [
                        'The department cooperates with Humboldt University of Berlin and the ZALF Leibniz Centre for Agricultural Landscape Research in Germany.',
                        'Academic mobility and traineeship activities have been carried out through Erasmus+ programs, and cooperation with Istanbul Technical University is planned for the 2025-2026 academic year.',
                    ],
                ]),
            ],
            'agricultural-water-resources-engineering-technologies' => [
                'name' => 'Department of Agricultural and Water Management Engineering Technologies',
                'description' => 'The department focuses on agricultural engineering, water management machinery, reclamation technologies, and modern mechanization for agricultural and water systems.',
                'sections' => $this->sections([
                    'history' => [
                        'The department was initially formed after the establishment of the Bukhara Branch of the Tashkent Institute of Irrigation and Melioration in 2010.',
                        'It began within the Faculty of Hydro-Melioration under the name General Professional Disciplines, Mechanization of Water Management and Melioration Works.',
                        'In the 2010-2011 academic year, the department had 7 faculty members and 2 technical staff, with an academic potential of 71%.',
                    ],
                    'research' => [
                        'Research areas include improving mechanized processes in agricultural and water management, reducing energy consumption, increasing machinery service life, land-leveling machines, mechanized hilling processes, energy-saving primary tillage systems, subsurface irrigation with liquid bio-fertilizers, and peat pot production from biohumus.',
                    ],
                    'subjects' => [
                        'Bachelor programs include Fundamentals of Agricultural Engineering, Reclamation and Construction Machinery, Materials Science, Mechanization of Agricultural Production, Agricultural Machinery, Construction Equipment, Irrigation Equipment, Tractors and Transport Vehicles, Precision Agriculture, and Fuels and Lubricants.',
                        'Master programs include Reclamation Dredging Vessels and Equipment, Scientific Foundations of Reclamation Construction Organization, Engineering Logistics and Modeling, Theoretical Foundations of Reclamation Machine Design, Hydromechanization Equipment, and Smart Agriculture.',
                    ],
                    'prepared_specialists' => [
                        '60812400 - Mechanization of Water Management and Reclamation Works.',
                        '60810100 - Mechanization of Agriculture.',
                        '70811210 - Mechanization of Hydromechanization Works.',
                        '70811211 - Machines and Equipment for Reclamation Construction.',
                        '70810100 - Mechanization of Agriculture.',
                        '05.07.01 - Agricultural and Reclamation Machinery. Mechanization of Agricultural and Reclamation Works.',
                    ],
                    'cooperation' => [
                        'International partners include Kursk State Agrarian University, North Dakota State University, Belarusian State Agrarian Technical University, Humboldt University of Berlin, Obuda University, Southwestern State University, Iowa State University, and INTI International University.',
                    ],
                ]),
            ],
            'land-resources-management-state-land-cadastres' => [
                'name' => 'Department of Land Use and State Cadastre',
                'description' => 'The department trains specialists in land cadastre, land management, geodesy, geoinformatics, cartography, remote sensing, and soil quality assessment.',
                'sections' => $this->sections([
                    'history' => [
                        'The department was established in 2010 as Land Management and Land Cadastre within the Faculty of Hydro-Reclamation.',
                        'Today it operates as Land Resource Management and State Cadastres. Since 2022, it has been led by Associate Professor S. Asatov.',
                        'The department uses scientific research results in lectures, laboratory and practical classes, course projects, and internships. Electronic textbooks and video lessons are made available through Ziyonet, Internet resources, and YouTube.',
                    ],
                    'research' => [
                        'The department has 25 faculty members. Over the past 14 years, faculty have published 10 textbooks, 15 teaching aids, more than 1,000 scientific articles, and 56 methodological guidelines.',
                        'Research topics include rational organization of agricultural land use under ecological instability, optimization of agricultural land types within cluster systems, and innovative technologies for land monitoring.',
                    ],
                    'subjects' => [
                        'Bachelor programs include Cartographic Design, Geodesy, Cartography, Fundamentals of State Cadastre, Higher Geodesy, Land Resource Management, Modern Geodetic Instruments, Land Management Design, Digital Cartography, Digital Land Cadastre, GIS, Digital Photogrammetry, and Geodatabase Architecture.',
                        'Master programs include Research Methodology, Integrated Land Use Management, Territorial Development, Economics of Land Use, Legal Foundations of Land Resource Management, Formation of Land Plots, Accounting and Valuation of Land Plots, and Real Estate Management.',
                    ],
                    'prepared_specialists' => [
                        '60811600 - Land Cadastre and Land Management.',
                        '60721700 - Cadastre.',
                        '60721500 - Geodesy and Geoinformatics.',
                        '60721600 - Cartography and Remote Sensing.',
                        '60810300 - Soil Quality Assessment and Land Degradation.',
                        '70811601 - Land Resource Use and Management.',
                        '70721601 - Remote Sensing of the Earth and GIS Technologies.',
                    ],
                    'cooperation' => [
                        'The department cooperates with Ushak University in Turkey. Faculty members and master students completed short-term traineeships, exchanged experience, attended lectures, and studied laboratory equipment.',
                    ],
                ]),
            ],
            'industrial-ecology-hydrogeology' => [
                'name' => 'Department of Industrial Ecology and Hydrogeology',
                'description' => 'The department prepares specialists in ecology, hydrology, reclamation hydrogeology, occupational safety, industrial safety, and environmental protection.',
                'sections' => $this->sections([
                    'history' => [
                        'In accordance with Resolution No. PQ-22 of January 24, 2025, the Department of Industrial Ecology and Hydrology was established after reorganizing related departments of Bukhara Engineering and Technology Institute and Bukhara Institute of Natural Resources Management.',
                        'The department trains bachelors and masters in reclamation hydrogeology, hydrology, ecology and environmental protection, occupational safety, and industrial safety according to the needs of Bukhara region economic sectors.',
                    ],
                    'research' => [
                        'Research publications cover accident and fire prevention, environmental safety, wastewater treatment, air pollution, Aral Sea ecological issues, ecological education, environmental legislation, soil resources, and food safety quality systems.',
                    ],
                    'subjects' => [
                        'Subjects include Environmental Impact Assessment, Environmental Protection and Green Development, Environmental Resource Economics, Sustainable Development, Waste Management, Environmental Audit, Environmental Monitoring, Ecology, Environmental Law, Operational Hydrometry, Civil Protection, GIS and Hydrology, Geology and Hydrogeology, Hydrochemistry, Hydrometry, and Life Safety.',
                    ],
                    'prepared_specialists' => [
                        '60520200 - Ecology and Environmental Protection.',
                        '61020200 - Occupational Safety and Technical Safety.',
                        '60530800 - Hydrology (Hydrology of Rivers and Reservoirs).',
                        '60812600 - Reclamation Hydrogeology.',
                        '60710400 - Ecology and Environmental Protection (in Water Management).',
                        '71020201 - Occupational Safety and Technical Safety.',
                        '70520202 - Ecology.',
                    ],
                    'cooperation' => [
                        'The department cooperates with UN FAO, Erasmus+, universities in the USA, Canada, the United Kingdom, Germany, Spain, Italy, Hungary, Cyprus, Belarus, Russia, Turkey, Korea, Malaysia, Kazakhstan, and China.',
                        'With Lanzhou University, a Dust Forecasting and Prediction laboratory facility has been established through grant funding worth 130 thousand US dollars.',
                    ],
                ]),
            ],
            'vehicle-engineering-automotive-transport-systems' => [
                'name' => 'Department of Vehicle Engineering (Automotive & Transport Systems)',
                'description' => 'The department develops engineering education in vehicle technologies, agricultural machinery, mechanical engineering, and modern transport systems.',
                'sections' => $this->sections([
                    'history' => [
                        'In 2020, the Department of Fundamentals of Mechanics and Surface Transport Systems was established within the Faculty of Mechanics and Architectural Construction.',
                        'In August 2019, Kh.R. Gaffarov was appointed head of the department. In 2021, after restructuring, the department was split into Fundamentals of Mechanics and Vehicle Engineering.',
                        'Today, the Department of Vehicle Engineering trains specialists in 9 bachelor programs and 3 master specializations and employs 36 faculty members and staff.',
                    ],
                    'research' => [
                        'Research areas include agricultural and land reclamation machinery, food industry machines and equipment, technological machines, robots and mechatronics, materials science in mechanical engineering, vocational education methodology, and processes and equipment of chemical technology and food production.',
                    ],
                    'subjects' => [
                        'Bachelor subjects include Materials Science, Hydraulics, Cutting Theory, Mechanical Engineering Technology, Casting Technologies, Tractors and Transport Vehicles, Agricultural Machinery, Precision Agricultural Systems, Automated Production Technology, Vehicle Engineering, Road Safety, and Technical Service.',
                        'Master subjects include Research Methodology, Machine Tools and CNC Complexes, Reliability of Machines and Processes, Resource-Saving Technologies, Vehicle Engineering, Logistics and Resource Conservation in Road Transport, and New Technical Solutions for Agricultural Machinery.',
                    ],
                    'prepared_specialists' => [
                        '60712300 - Mechanical Engineering.',
                        '60711300 - Metal Technology.',
                        '60720300 - Materials Science.',
                        '60810100 - Mechanization of Agriculture.',
                        '60711400 - Vehicle Engineering.',
                        '60720800 - Mechanical Engineering Technology, Equipment and Automation of Mechanical Engineering Production.',
                        '60720600 - Materials Science and New Materials Technology (Mechanical Engineering).',
                        '60712500 - Vehicle Engineering (Road Transport).',
                        '70712301 - Mechanical Engineering Technology.',
                        '70711401 - Vehicle Engineering (Road Transport).',
                    ],
                    'cooperation' => [
                        'Partners include Avto Service Inter Millennium, Vobkent Yulduzi Texservis, Buxoro Avtotexxizmat, Vatanparvar, Neftgazavtotransxizmat, Maxsus avtotrans xizmat, Buxsozta\'mirservis, Nasos ta\'mirlash, Trubodetal, Emirate Steel, XXI Metall Works Buxara, ENTER MASHINERIES SERVICE, and China Nuclear Industry 22ND Construction CO., LTD.',
                    ],
                ]),
            ],
        ];
    }

    private function sections(array $sections): array
    {
        $titles = [
            'history' => 'History of the Department',
            'research' => 'Research Activities Conducted at the Department',
            'subjects' => 'Courses Taught at the Department',
            'prepared_specialists' => 'Specialists Trained by the Department',
            'cooperation' => 'International Cooperation',
        ];

        return array_map(
            fn (string $key, array $items) => [
                'key' => $key,
                'title' => $titles[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                'items' => $items,
            ],
            array_keys($sections),
            $sections
        );
    }
};
