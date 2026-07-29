<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentTranslation;
use App\Models\StaffProfile;
use App\Models\StaffProfileTranslation;
use Database\Seeders\Concerns\ResolvesSeedLocales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademicDepartmentDetailsSeeder extends Seeder
{
    use ResolvesSeedLocales;
    private array $locales = [];

    public function run(): void
    {
        $this->locales = $this->activeSeedLocales();
        $path = database_path('data/academic_department_details.json');
        if (! is_file($path)) {
            return;
        }

        $payload = json_decode((string) file_get_contents($path), true);
        foreach (($payload['departments'] ?? []) as $departmentData) {
            $department = Department::where('slug', $departmentData['slug'] ?? null)->first();
            if (! $department) {
                continue;
            }

            $sections = $this->normalizeSections($departmentData['sections'] ?? []);
            $contact = $this->extractDepartmentContact($sections);
            if (($departmentData['slug'] ?? '') === 'electrical-power-engineering') {
                $sections = $this->electricalPowerSections();
                $contact = $this->electricalPowerContact();
            }
            if (($departmentData['slug'] ?? '') === 'architecture') {
                $sections = $this->architectureSections();
                $contact = $this->architectureContact();
            }
            if (($departmentData['slug'] ?? '') === 'civil-engineering') {
                $sections = $this->civilEngineeringSections();
                $contact = $this->civilEngineeringContact();
            }
            if (($departmentData['slug'] ?? '') === 'light-industry-engineering-and-design') {
                $sections = $this->lightIndustrySections();
                $contact = $this->lightIndustryContact();
            }
            if (($departmentData['slug'] ?? '') === 'mechanics-engineering-graphics') {
                $sections = $this->mechanicsSections();
                $contact = $this->mechanicsContact();
            }
            if (($departmentData['slug'] ?? '') === 'technological-machines-equipment') {
                $sections = $this->technologicalMachinesSections();
                $contact = $this->technologicalMachinesContact();
            }
            if (! empty($contact)) {
                $updates = $this->missingOnly($department, array_filter([
                    'head_name' => $contact['name'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                    'email' => $contact['email'] ?? null,
                    'reception_time' => $contact['reception_time'] ?? null,
                ], fn ($value) => $value !== null && $value !== ''));

                if ($updates !== []) {
                    $department->update($updates);
                }

                $this->seedDepartmentContact($department, $contact);
            }

            $description = $this->firstMeaningfulText($sections) ?: $department->translate('description', 'en');

            foreach ($this->locales as $locale) {
                DepartmentTranslation::firstOrCreate(
                    ['department_id' => $department->id, 'locale' => $locale],
                    [
                        'name' => $department->translate('name', $locale) ?: ($departmentData['name'] ?? $department->slug),
                        'short_name' => $department->translate('short_name', $locale),
                        'description' => $description,
                        'content_sections' => $this->localizeSections($sections, $locale),
                        'meta_title' => $department->translate('name', $locale) ?: ($departmentData['name'] ?? $department->slug),
                        'meta_description' => Str::limit($description, 250, ''),
                    ]
                );
            }

            $this->seedDepartmentStaff($department, $departmentData['staff'] ?? []);
            $this->deactivateInvalidStaff($department);
            $this->applyDepartmentStaffCorrections($department);
        }
    }

    private function extractDepartmentContact(array $sections): array
    {
        $overview = collect($sections)->firstWhere('key', 'overview');
        $text = trim((string) ($overview['items'][0] ?? ''));
        if ($text === '') {
            return [];
        }

        $lines = collect(preg_split('/\R+/', $text))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values();

        $name = null;
        foreach ($lines as $line) {
            if (
                Str::startsWith($line, 'Department of')
                || Str::startsWith($line, ['Reception time:', 'Phone:', 'Email:'])
                || Str::contains($line, [
                    'head of the department',
                    'department',
                    'engineering',
                    'technology',
                    'technological',
                    'machines',
                    'equipment',
                    'industry',
                    'design',
                ], true)
                || Str::contains($line, ['http://', 'https://'])
            ) {
                continue;
            }

            $name = $line;
            break;
        }

        $phone = null;
        $email = null;
        $receptionTime = null;

        foreach ($lines as $line) {
            if (preg_match('/^Phone:\s*(.+)$/i', $line, $match)) {
                $phone = trim($match[1]);
            }

            if (preg_match('/^Email:\s*(.+)$/i', $line, $match)) {
                $email = trim($match[1]);
                if ($email === '-') {
                    $email = null;
                }
            }

            if (preg_match('/^Reception time:\s*(.+)$/i', $line, $match)) {
                $receptionTime = trim($match[1]);
            }
        }

        return array_filter([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'reception_time' => $receptionTime,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function normalizeSections(array $sections): array
    {
        $allowed = ['overview', 'history', 'staff', 'prepared_specialists', 'subjects', 'activities', 'publications', 'research', 'cooperation', 'plans'];

        return collect($sections)
            ->filter(fn ($section) => in_array($section['key'] ?? '', $allowed, true))
            ->map(function ($section) {
                $items = collect($section['items'] ?? [])
                    ->map(fn ($item) => trim((string) $item))
                    ->reject(fn ($item) => $this->isInstructionalNoise($item))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'key' => $section['key'],
                    'title' => $section['title'] ?? Str::headline($section['key']),
                    'items' => $items,
                ];
            })
            ->filter(fn ($section) => count($section['items']) > 0)
            ->values()
            ->all();
    }

    private function electricalPowerContact(): array
    {
        return [
            'name' => 'Latipov Saidmurod Tuyg\'unovich',
            'phone' => '+998 91 979 88 22',
            'email' => 'stlatipov@gmail.com',
            'reception_time' => 'Tuesday-Thursday 10:00-13:00',
        ];
    }

    private function electricalPowerSections(): array
    {
        return [
            [
                'key' => 'overview',
                'title' => 'Overview',
                'items' => [
                    'The Department of Electrical and Energy Engineering is a specialized academic department dedicated to educating highly qualified engineering professionals by integrating modern technologies and innovative solutions. The department provides students with in-depth knowledge of electrical power engineering, energy efficiency, renewable energy sources, and the design and control of modern electrical equipment. The integration of theoretical education with practical training enables graduates to become competitive professionals in the labor market. The department places special emphasis on preparing highly skilled engineers capable of making significant contributions to the development of the country\'s energy sector.',
                ],
            ],
            [
                'key' => 'history',
                'title' => 'Department History',
                'items' => [
                    "The Department of Electrical Power Engineering was established in 1977 by the order of the rector of the institute under the leadership of M. T. Turdiev. It was separated from the Department of General Technical Sciences and initially named \"General Electrical Engineering.\"\n\nSince 1978, the department has been training part-time students in the field of \"Power Supply of Industrial Enterprises, Cities, and Agriculture.\" In 1985, it began training full-time students in the field of \"Electrical Power Engineering.\" Since 1998, it has been preparing specialists in \"Electrical Power Engineering,\" and since 2000, it has been admitting master's students in the specialty \"Power Supply (by sectors).\"\n\nThe department has been headed by: M. T. Turdiev (1977-1985, 1994-2005), A. M. Abdullaev (1985-1994), N. N. Sadullaev (2005-2011), M. I. Makhmudov (2011-2018), and I. I. Khafizov (2018-2019).\n\nIn the 2024-2025 academic year, as a result of internal structural changes at the university, the Department of \"Electrical Power Engineering and Electrical Engineering\" was formed based on three departments. In the same academic year, N. N. Mirzaev served as the head of the department. Since the beginning of the 2025-2026 academic year, the department has been headed by Associate Professor Latipov Saidmurod Tuyg'unovich.\n\nCurrently, the department employs 3 professors, 23 associate professors, 7 senior lecturers, 9 assistants, and 9 trainee teachers.",
                ],
            ],
            [
                'key' => 'subjects',
                'title' => 'Disciplines Taught at the Department',
                'items' => [
                    "Bachelor's Degree:\n1. Theoretical Electrical Engineering\n2. Energy Management\n3. Electrical Technological Equipment\n4. Electric Machines / Electrical Devices\n5. Electric Lighting\n6. Electrical Energy Measurement and Control Instruments\n7. Electrical Networks and Systems\n8. Installation and Operation of Power Supply Systems\n9. Transient Processes\n10. Automated Systems for Electricity Accounting and Control\n11. Power Plants and Substations\n12. Thermal Engineering\n13. Electrical Engineering and Electronics\n14. Fuel Combustion and Water Treatment Technology at Power Plants\n15. Fuel and Combustion\n16. Boiler Installations\n17. High-Temperature Processes and Equipment\n18. Energy Production Technologies and Power Plants\n19. Water Treatment Technology and Chemical Control at Power Plants\n20. Thermal Engineering Measurement and Control Instruments / Innovative Technologies in Thermal Power Engineering\n\nMaster's Degree:\n1. Modeling of Electrical Systems and Complexes\n2. Electrical Equipment and Power Sources of Industrial Complexes\n3. Design of Automated Electromechanical and Electrical Technological Systems\n4. Optimal Control of Electromechanical Systems and Complexes\n5. Automated Electric Drives of Industrial Mechanisms\n6. Automated Design Systems for Electric Machines and Transformers\n7. Reliability of Electric Machines and Transformers\n8. Recalculation of Electric Machines and Transformers\n9. Transient Processes in Electric Machines and Transformers\n10. Analytical Electromechanics\n11. Control of Electric Machines",
                ],
            ],
            [
                'key' => 'prepared_specialists',
                'title' => 'Educational Programs',
                'items' => [
                    "Bachelor's Degree:\n60710500 - Electrical Engineering\n60710400 - Energy Engineering\n60711000 - Renewable Energy Sources\n60710700 - Electrical Engineering, Electromechanics and Electrical Technologies\n60710900 - Energy Saving and Energy Audit\n60710500 - Energy Engineering (Thermal Power Engineering)\n60710600 - Power Engineering (by sectors)\n60712100 - Renewable Energy Sources\n\nMaster's Degree:\n70710701 - Electromechanics (by sectors)\n70710703 - Electrical Engineering Systems and Complexes (by sectors)\n70710901 - Energy Saving and Energy Audit (by sectors)\n70710407 - Power Supply (by sectors)\n70710411 - Renewable Energy Sources (by sectors)",
                ],
            ],
            [
                'key' => 'research',
                'title' => 'Scientific Research Works',
                'items' => [
                    'At the Energy Audit department, the scientific research activities involve 2 Doctors of Science, 3 PhD holders, 6 senior lecturers, 1 research fellow, independent researchers, and master\'s students.',
                    'Within the state scientific and technical program project ILM-20215001 (2021-2023), the project "Development of a wind energy installation efficiently operating in the climatic conditions of Uzbekistan for low-power consumers" received a state grant of 500 million UZS. The research is conducted by DSc N. N. Sadullaev, DSc M. I. Makhmudov, and Assoc. Sh. N. Nematov.',
                    'In the 2021-2022 academic year, 9 contracts totaling 92 million UZS were concluded and implemented with enterprises for energy audits and energy efficiency measures.',
                    'Mirkhanov O. K. researches energy-efficient operating modes of synchronous motors based on controlled electric drives.',
                    'Nurov S. S. researches devices for monitoring the level and concentration of activated sludge in wastewater to optimize aeration tank-settling systems.',
                    'Sidikov S. S. researches optoelectronic devices and methods for monitoring turbidity of liquid media in wastewater treatment systems.',
                ],
            ],
            [
                'key' => 'cooperation',
                'title' => 'Partners',
                'items' => [
                    'The educational programs 60710400 - Energy Engineering, 60710500 - Electrical Engineering, and 60710700 - Electronics and Instrumentation are being improved based on regulatory documents of the Electrical Power Engineering and Renewable Energy Systems programs of the Polytechnic University of Turin (Italy) and the Technical University of Dresden (Germany). The department is preparing for international accreditation in 2025 for the bachelor programs 60710400 - Energy Engineering and 60710500 - Electrical Engineering by a German international accreditation organization. The Thermal Engineering and Thermodynamics laboratory is also planned for international accreditation in 2025 by an accreditation body of the Islamic Republic of Iran. Over the last three years, 8 faculty members have completed training and scientific internships at leading foreign universities, including Kazan Federal University, Hebei Vocational University of Technology and Engineering, Belarusian National Technical University, Mukhtar Auezov South Kazakhstan University, and other partner institutions.',
                ],
            ],
        ];
    }

    private function architectureContact(): array
    {
        return [
            'name' => 'Mirzaev Shamsiddin Rajabovich',
            'phone' => '+998 91 014 02 59',
            'email' => 'mirzaev.shamsiddin@mail.ru',
            'reception_time' => 'Monday-Friday 14:00-16:00',
        ];
    }

    private function architectureSections(): array
    {
        return [
            [
                'key' => 'history',
                'title' => 'Department History',
                'items' => [
                    "The First President of the Republic of Uzbekistan, I. A. Karimov, during his visit to the Bukhara region on July 13-14, 2007, while inspecting construction works in the city of Bukhara, emphasized that the city has a very ancient history and, accordingly, should have its own unique architectural identity. He stressed the importance of thoroughly studying cultural heritage sites and taking into account national traditions and local conditions in the design and construction of buildings and structures.\n\nIn April 2008, during another visit to the Bukhara region, the President issued instructions to establish the training of qualified specialists directly in Bukhara, capable of understanding, preserving, and developing national architectural heritage, as well as designing modern buildings consistent with these traditions.\n\nIn implementation of these instructions, the Ministry of Higher and Secondary Specialized Education of the Republic of Uzbekistan issued Order No. 120 dated May 2, 2008, \"On the establishment of the Faculty of Architecture and Construction at the Bukhara Institute of Food and Light Industry Technology,\" and later Order No. 242 dated August 12, 2008, \"On the development of the Faculty of Architecture and Construction of the Bukhara Institute of Food and Light Industry Technology.\"\n\nBased on these orders, the Faculty of \"Architecture and Construction\" was established within the institute, and the educational program \"Architecture\" was opened. Since the 2008-2009 academic year, student admissions have been carried out for this program.\n\nAccording to the decision of the extended meeting of the Academic Council of the institute No. 1 dated August 29, 2009, the Department of \"Architecture\" was established within the Faculty of Architecture and Construction, and the Architecture program was assigned to this department.\n\nCurrently, the department employs 20 academic staff members, including 1 Doctor of Sciences and Professor, 4 Candidates of Sciences and Associate Professors, 5 Senior Lecturers, and 3 Assistants. The department is responsible for 2 educational programs and 34 academic disciplines.",
                ],
            ],
            [
                'key' => 'subjects',
                'title' => 'Disciplines Taught at the Department',
                'items' => [
                    "Bachelor's Degree:\n1. Architectural Composition and Fundamentals of Design\n2. Architectural Drawing, Painting and Sculpture\n3. Architectural Design\n4. Design of Residential and Public Buildings\n5. Urban Planning and Landscape Design\n6. Typology of Buildings and Structures\n7. Transport and Engineering Equipment\n8. Interior Design and Equipment\n9. Model Making\n10. Digital Design (AutoCAD, SketchUp, Revit)\n11. Engineering Systems of Buildings\n12. Restoration and Reconstruction of Architectural Monuments\n13. Fundamentals of Designing Energy-Efficient Buildings\n14. Territorial Planning and Design\n15. Architectural Heritage of Uzbekistan",
                ],
            ],
            [
                'key' => 'prepared_specialists',
                'title' => 'Educational Programs',
                'items' => [
                    "Bachelor's Degree:\n60730100 - Architecture (by types)\n60730800 - Reconstruction and Restoration of Architectural Monuments\n60730500 - Design and Operation of Water Supply and Sewerage Systems\n\nMaster's Degree:\n5A340101 - Architecture of Buildings and Structures\n70730103 - History and Theory of Architecture",
                ],
            ],
            [
                'key' => 'cooperation',
                'title' => 'Partners',
                'items' => [
                    'A cooperation agreement has been signed with the Uzbekistan-Germany joint venture IP OOO "KNAUF GIPS BUKHARA" to train students in KNAUF technologies. Cooperation has been established with the Main Department of Construction and Housing and Communal Services of the Bukhara region, and dual education programs have been introduced.',
                    'A cooperation agreement between Bukhara Institute of Engineering and Moscow State University of Civil Engineering (NRU MGSU, Russia) was signed on 07.09.2017 (No. 369-193/07), and a joint action plan for 2017-2025 has been developed. Cooperation with Russia\'s leading construction university includes faculty development, internships, and student exchange programs.',
                    'Department cooperation with foreign educational institutions includes professors and specialists from the University of Potsdam (Germany): Jorg Roder, Steffen Laue, and Volker Bley.',
                    'The department participated in the Restoration Week 2025 exhibition organized jointly with ASSORESTAURO (Italy), the Italian Trade Agency, and Salone del Restauro.',
                    'The department plans to establish joint educational programs with Azerbaijan University of Architecture and Construction and Vilnius Gediminas Technical University through memorandums and agreements.',
                    'Currently, 26 academic staff members work at the department: 22 full-time staff, 2 internal part-time staff, and 2 external part-time staff. Scientific potential is 40%, and more than 360 students are enrolled.',
                    'In 2018 (November 20-26), Associate Professor Anton Sergeevich Pilipenko from the Department of Building Materials and Materials Science at NRU MGSU, and in 2019 (May 20-27), Associate Professor Sergey Sergeevich Inozemtsev conducted master classes in Building Materials. It was agreed to send talented students to Moscow State University of Civil Engineering, and in 2020 three faculty members were planned to undergo professional training at this university.',
                ],
            ],
        ];
    }

    private function civilEngineeringContact(): array
    {
        return [
            'name' => 'Inomjon Ilhomovich Tojiyev',
            'phone' => '+998 91 444 87 03',
            'email' => 'arminom@mail.ru',
            'reception_time' => 'Monday-Friday 14:00-16:00',
        ];
    }

    private function civilEngineeringSections(): array
    {
        return [
            [
                'key' => 'history',
                'title' => 'Department History',
                'items' => [
                    "The Department of Civil Engineering is one of the oldest departments and has been operating since 1962 as a core department of the Bukhara General Technical Faculty of the Tashkent Polytechnic Institute. Throughout its long history, more than 40 highly qualified professors and associate professors have contributed significantly to the training of skilled specialists.\n\nCurrently, the department employs 32 faculty members, including 2 Doctors of Science and Professors, 15 Candidates of Science and Associate Professors, and 15 Senior Lecturers and Assistants. The scientific potential of the department is 53%.\n\nAt present, 65 academic courses are taught at the department. Currently, 654 full-time students and 758 part-time students are studying at the department.",
                ],
            ],
            [
                'key' => 'research',
                'title' => 'Research Activities',
                'items' => [
                    'Currently, the department employs 15 faculty members, including 1 Doctor of Science and Professor, 5 Candidates of Science and Associate Professors, 6 Senior Lecturers, and 3 Assistants. The department is responsible for 2 educational programs and 30 academic disciplines. The scientific potential of the department is 40%.',
                    'The department provides specialist training in 5340200 - Construction of Buildings and Structures (Industrial and Civil Buildings) and 5341800 - Technology of Wall and Finishing Construction Materials. At present, 375 full-time and 276 part-time students are studying in these programs.',
                    'During 2021-2022, the department faculty produced 7 textbooks, 4 teaching manuals, 4 monographs, obtained 2 invention patents, and developed 14 electronic textbooks.',
                    'Faculty members published 9 articles indexed in Web of Science and Scopus databases, 13 articles in international scientific journals, 6 articles in journals recognized by the Higher Attestation Commission (HAC), as well as scientific papers and abstracts presented at 8 international and 24 national and university conferences.',
                    'Current research areas include dry construction mixtures based on local materials for restoration of architectural monuments, soil strengthening through cementation methods under weak soil conditions, green roof construction in Uzbekistan, thermal conductivity improvement of external wall panels, concrete casting technologies under hot climatic conditions, and effective solutions for improving concrete properties through plasticizers.',
                    'As part of research conducted by Head of Department I. I. Tojiyev on "Modified Gypsum Mortars for the Restoration of Architectural Monuments (Case Study of Bukhara)," construction systems and restoration materials of Bukhara heritage buildings were studied. Chemical composition and mechanical properties of historical mortars were analyzed, new modified gypsum-based construction mortars were developed and tested, and the research outcomes are now applied in restoration works.',
                    'Currently, 5 department lecturers without academic degrees are conducting research as independent researchers. Department Assistant Uchqun Isroilovich Safarov prepared his dissertation "Dynamic Processes in a Cylindrical Shell Interacting with the Environment" for defense.',
                ],
            ],
            [
                'key' => 'subjects',
                'title' => 'Courses Taught at the Department',
                'items' => [
                    "Bachelor's Degree Program:\n1. Assessment of the Technical Condition of Buildings and Structures\n2. Soil Mechanics, Foundations and Footings\n3. Construction Organization and Planning\n4. Reinforced Concrete and Masonry Structures\n5. Innovative Technologies in the Construction Industry\n6. Technology of Wall Construction Materials\n7. Technology of Finishing Construction Materials\n8. Polymer Construction Materials\n9. Engineering Geodesy\n10. Architecture of Industrial and Civil Buildings\n11. Soil Mechanics, Foundations and Footings\n12. Construction Process Technology\n13. Timber Structures\n14. Steel Structures\n15. Technology of Wall Construction Materials\n16. Technology of Finishing Construction Materials\n17. Technological Equipment of the Construction Industry\n18. Technology of Paint and Coating Materials\n19. Technology of Polymer Construction Materials\n20. Engineering Geodesy\n21. Construction Materials and Products (BIQ)\n22. Construction Materials and Products (DPM)\n23. Architectural Materials Science (ARX)\n24. Construction Cost Estimation\n25. Architectural Materials Science (ARX, Evening Program)\n26. Construction Materials and Products (SKT)\n27. Engineering Geology and Hydrogeology\n28. Engineering Geology\n29. Architectural Materials Science\n30. Construction Machinery",
                ],
            ],
            [
                'key' => 'prepared_specialists',
                'title' => 'Educational Programs',
                'items' => [
                    "Bachelor's Degree Programs:\n600730300 - Civil Engineering\n600730300 - Technology of Construction Materials and Products\n600730500 - Road Engineering\n\nMaster's Degree Programs:\n70730301 - Civil Engineering\n70730308 - Construction Materials Technology",
                ],
            ],
            [
                'key' => 'cooperation',
                'title' => 'Partners',
                'items' => [
                    'Currently, the Department of Building and Structure Construction actively cooperates with more than 10 higher education institutions and leading enterprises both in Uzbekistan and abroad. In particular, cooperation agreement No. 369-193/07 has been signed between the department and Moscow State University of Civil Engineering (NRU MGSU). Under this agreement, joint activities are carried out in faculty development, student internship programs, and academic exchange initiatives.',
                    'To improve the quality of education through cooperation with modern high-tech manufacturing enterprises, an agreement was signed with the German-Uzbek joint venture IP OOO "KNAUF GIPS BUXARA". Faculty members and students of the department actively participate in conferences and events organized by the company and regularly achieve distinguished results.',
                    'To support employment of graduating fourth-year students and establish long-term cooperation, an agreement was signed with the Romitan District branch of the Chinese company "China Railway 20 Bureau Group Corporation".',
                    'Within the joint program, a meeting was organized between students of the Building and Structure Construction program and representatives of Chinese companies operating in Uzbekistan to facilitate employment opportunities. Agreements were also reached to expand cooperation with several Chinese companies in specialist training. As a result of cooperation with China Railway 20 Bureau Group Corporation, 20 graduating students were involved in the reconstruction project of the A-380 Guzar-Bukhara-Nukus-Beyneu highway starting from March 12.',
                ],
            ],
        ];
    }

    private function lightIndustryContact(): array
    {
        return [
            'name' => 'Farhod Farmonovich Qazoqov',
            'phone' => '+998 (91) 647 65 82',
            'email' => null,
            'reception_time' => 'Monday-Friday 14:00-16:00',
        ];
    }

    private function lightIndustrySections(): array
    {
        return [
            [
                'key' => 'history',
                'title' => 'Department History',
                'items' => [
                    "According to Order No. U-564 issued by the Rector of Bukhara Engineering-Technological Institute on September 5, 2024, the departments of \"Innovative Technologies of the Garment Industry,\" \"Technology and Design of Leather Products,\" and \"Technology of Textile Products\" were merged to establish the Department of \"Light Industry Engineering and Design.\"\n\nThe training of engineering technologists in this field has been carried out since 1979 on the basis of the Department of \"Spinning of Natural and Chemical Fibers\" and since 1982 on the basis of the Department of \"Technology and Equipment of Light Industry.\" Students have been admitted to full-time, evening, and part-time programs in these specialties. Currently, the department is headed by Doctor of Technical Sciences, Associate Professor F. F. Qazoqov.\n\nThe department employs 3 Doctors of Science (Professors), 2 Candidates of Science (Professors), 7 Candidates of Science (Associate Professors), 27 Doctors of Philosophy (PhD) (Associate Professors), 1 Associate Professor, 7 Senior Lecturers, and 15 Assistant Lecturers who provide instruction to students. The Department of Light Industry Engineering and Design organizes educational programs for bachelor degree fields and master degree specialties.",
                ],
            ],
            [
                'key' => 'research',
                'title' => 'Scientific-Methodological Activities and Research Publications',
                'items' => [
                    'Educational and methodological complexes - more than 100.',
                    'Methodological manuals - 85.',
                    'Lecture materials - more than 100.',
                    'Methodological guidelines - more than 150.',
                    'Scientific articles: international publications - more than 200; national publications - more than 180.',
                    'Conference papers and abstracts: international conference abstracts - more than 1,000; national conference abstracts - more than 2,000.',
                    'Development and investigation of dress fabrics with various compositions.',
                    'Improvement of production technology for knitted fabrics based on recycled fibers.',
                    'Coating natural fabric surfaces using local components, their application in medicine, and scientific justification.',
                    'Development of efficient structures of blended knitted fabrics intended for outerwear knitwear products.',
                    'Production of new assortments of blended apparel fabrics and investigation of their properties.',
                    'Development of an improved technology for moistening cotton raw materials before the fiber separation process.',
                    'Investigation of the structural composition of a new cattail fiber and its preparation for spinning.',
                    'Improving the quality of yarn produced from a blend of local wool and polyester fibers.',
                    'Improvement of wool cleaning technology during primary processing.',
                    'Improvement and scientific justification of carpet production technology based on local wool fibers.',
                ],
            ],
            [
                'key' => 'subjects',
                'title' => 'Courses Taught at the Department',
                'items' => [
                    "Bachelor's Degree Program:\n1. Materials Science\n2. Technology of Wool and Wool Fiber Processing\n3. History of Art\n4. Primary Processing of Natural Fibers\n5. Chemical Technology of Textile Products\n6. Technology and Equipment of Textile Products\n7. Spinning Technology\n8. Fundamentals of Anthropology and Biomechanics\n9. Fundamentals of Leather Goods Manufacturing Processes\n10. Chemical Processing in Leather Goods Manufacturing\n11. Leather Goods Technology\n12. Design and Construction of Leather Goods\n13. Computer-Aided Design Systems for Leather Goods\n14. Equipment of Leather and Fur Enterprises\n15. Leather and Fur Materials Science\n16. Leather and Fur Technology 1,2,3,4\n17. Innovative Technologies in Leather and Fur Production\n18. Leather and Fur Expertise and Quality Control\n19. Design of Leather and Fur Enterprises\n20. Leather and Fur Technology\n21. Leather and Fur Finishing\n22. Fabric Structure and Design\n23. Weaving Technology\n24. Leather and Fur Finishing and Design\n25. Drawing and Painting\n26. Materials Science of Leather Goods\n27. Leather Goods Technology\n28. Design and Construction of Leather Goods\n29. Fundamentals of Design Planning\n30. Conducting Experimental Research\n31. Materials Science of Light Industry Equipment\n32. Carpet Manufacturing Technology\n33. Secondary Yarn Technology\n34. Professional Skills\n35. Professional Skills and Engineering Documentation\n36. Fundamentals of Composition and Color Theory\n37. Project Implementation on Leather\n38. Introduction to the Field\n39. Primary Processing of Natural Fibers\n40. Research Methods and Tools\n41. Industrial Chemical Materials\n42. Innovations in Engineering and Technology\n43. Design of Technological Processes\n44. Fundamentals of Technological Equipment Design\n45. Garment Materials Science\n46. Chemical Technology and Processes of Leather and Fur 1,2\n47. Technical Control of Textile Enterprises\n48. Equipment of Textile Enterprises\n49. Chemical Technology of Textile Products\n50. Chemical Technology of Textile Products\n51. Quality Control of Textile Products\n52. Technology and Equipment of Textile Products\n53. Textile Materials Science\n54. Quality Control of Textile Products\n55. Leather and Fur Raw Materials and Their Primary Processing\n56. Waste-Free Fiber Processing Technology\n57. Theoretical Foundations of Textile Fiber Spinning\n58. Preparation of Raw Materials for Weaving\n59. Preparation of Raw Materials for Spinning\n60. Design of Spinning Enterprises\n\nMaster's Degree Program:\n1. Theoretical Foundations of Scientific Research\n2. Scientific and Pedagogical Activities\n3. Research Work and Preparation of the Master's Thesis\n4. Theoretical Foundations of Garment Technology\n5. Specialized Technology\n6. Textile Enterprises of Uzbekistan\n7. Theoretical Foundations of Twisted Yarn Production\n8. Preparation of Staple Fibers for Spinning\n9. Clothing Design Methodology\n10. Theoretical Foundations of Quality Management in the Production of Leather, Fur, Footwear, and Leather Goods\n11. Design of Garment Manufacturing Enterprises\n12. Theory and Calculation of Garment Construction and Design\n13. Scientific Problems of Cotton Industry Technology\n14. Product Quality Management\n15. Design of Technological Processes for Seed Preparation\n16. Modeling of Technological Processes in the Cotton Industry\n17. Fundamentals of Waste-Free Production Technologies\n18. Cotton Product Manufacturing and Quality Management\n19. Quality Management of Fiber Products\n20. Design of Technological Processes for Seed Preparation",
                ],
            ],
            [
                'key' => 'prepared_specialists',
                'title' => 'Educational Programs',
                'items' => [
                    "Bachelor's Degree Programs:\n60210400 - Design (Fashion and Textile Design)\n60210400 - Design (Footwear and Accessories Design)\n60720700 - Light Industry Engineering (Creative Design and Innovative Technologies in the Garment Industry)\n60721200 - Design and Technology of Light Industry Products (Spinning Technology)\n60721200 - Design and Technology of Light Industry Products (Weaving Technology)\n60721400 - Light Industry Technologies and Equipment (Leather and Fur Processing and Equipment)\n60721400 - Light Industry Technologies and Equipment (Footwear, Leather Goods Production and Equipment)\n60721400 - Light Industry Technologies and Equipment (Garment Manufacturing)\n\nMaster's Degree Programs:\n70721201 - Textile Products Technology (Spinning Technology)\n70721402 - Technologies and Equipment (Leather and Fur Products)\n70720704 - Technology and Design of Garments (Garment Production)",
                ],
            ],
            [
                'key' => 'cooperation',
                'title' => 'Partners',
                'items' => [
                    'Bukhara State Technical University actively develops international cooperation and has established strong partnerships with foreign higher education institutions.',
                    'Tajik Technological University - since April 1, 2025, cooperation has been carried out under a five-year agreement covering scientific research, exchange of academic staff and students, and joint projects.',
                    'Almaty Technological University (Republic of Kazakhstan) - since 2024, joint educational programs, scientific seminars, and internship projects have been implemented within a five-year international cooperation agreement.',
                    'South Kazakhstan University - a five-year cooperation agreement was established on March 13, 2023, under Agreement No. 372-86, covering scientific exchange and sharing of experience in educational methodologies.',
                    'Antalya Bilim University (Republic of Turkiye) - since April 26, 2023, academic exchange programs, joint scientific conferences, and student initiatives have been carried out under a five-year cooperation framework.',
                    'Vitebsk State Technological University (Republic of Belarus) - since April 5, 2023, joint research activities in technological fields have been conducted under a five-year international cooperation agreement.',
                    'Kostroma State University (Russian Federation) - since October 14, 2023, cooperation has been carried out in engineering, technology, and information and communication systems within a five-year partnership framework.',
                    'These collaborations enhance the international reputation of the department, advance innovative education, and support the implementation of modern scientific achievements in practice. Connections have also been established with higher education institutions of the Republic of Tajikistan and other training participants for future projects and scientific and educational cooperation.',
                ],
            ],
        ];
    }

    private function mechanicsContact(): array
    {
        return [
            'name' => 'Fakhriddin Yusupovich Khabibov',
            'phone' => '+998 93 379 65 00',
            'email' => 'faxrilo@mail.ru',
            'reception_time' => 'Monday-Friday 14:00-16:00',
        ];
    }

    private function mechanicsSections(): array
    {
        return [
            [
                'key' => 'history',
                'title' => 'Department History',
                'items' => [
                    "The Department of Fundamentals of Mechanics was established on December 8, 1970, as part of the Bukhara Evening Branch of the Tashkent Polytechnic Institute. In accordance with Resolution No. PQ-1533 of the President of the Republic of Uzbekistan dated May 20, 2011, \"On Measures to Strengthen the Material and Technical Base of Higher Educational Institutions and Fundamentally Improve the Quality of Training Highly Qualified Specialists,\" the Bukhara Institute of Food and Light Industry Technology was transformed into the Bukhara Engineering-Technological Institute of High Technologies.\n\nDue to changes in the organizational structure of the institute, by Rector's Order No. 84-U dated August 28, 2011, the department was renamed \"Mechanics,\" and since September 20, 2021, it has been known as the Department of Fundamentals of Mechanics.\n\nCurrently, the department employs 13 faculty members and 2 technical staff members, including 7 professors and instructors holding academic degrees and titles. The scientific potential of the department is 50%.",
                ],
            ],
            [
                'key' => 'subjects',
                'title' => 'Disciplines Taught at the Department',
                'items' => [
                    "Bachelor's Degree:\n1. Theoretical Mechanics\n2. Strength of Materials\n3. Applied Mechanics\n4. Technical Mechanics\n5. Mechanics\n6. Structural Mechanics",
                ],
            ],
        ];
    }

    private function technologicalMachinesContact(): array
    {
        return [
            'name' => 'Uyg‘un Abdullayevich O‘rinov',
            'phone' => '+998 90 744 18 22',
            'email' => null,
            'reception_time' => 'Monday-Friday 14:00-16:00',
        ];
    }

    private function technologicalMachinesSections(): array
    {
        return [
            [
                'key' => 'overview',
                'title' => 'Overview',
                'items' => [
                    'Technological Machines and Equipment is an important engineering field focused on the technical foundations of modern production processes. Students acquire comprehensive knowledge and practical skills in the design, operation, maintenance, and modernization of technological equipment. Graduates play a significant role in improving industrial efficiency and implementing innovative technologies across various sectors of the economy.',
                ],
            ],
            [
                'key' => 'history',
                'title' => 'Department History',
                'items' => [
                    'In accordance with Resolution No. PQ-22 of the President of the Republic of Uzbekistan dated January 24, 2025, "On the Establishment of Bukhara State Technical University," Bukhara State Technical University was established on the basis of the Bukhara Engineering-Technological Institute and the Bukhara Institute of Natural Resources Management. From April 1, 2025, pursuant to Order No. 9-Sh dated April 2, 2025, issued by the Acting Rector of Bukhara State Technical University, the Department of "Technological Machines and Equipment" was reorganized. Special attention is paid within the department to the implementation of an innovative education system, ensuring close integration of education with industry, and promoting advanced scientific research and innovative production projects. The department currently employs 4 Doctors of Science, 23 Candidates of Technical Sciences and PhD holders, 2 Senior Lecturers, 5 Assistants, 12 Trainee Lecturers, and 17 Doctoral Researchers who are actively engaged in scientific research activities.',
                ],
            ],
            [
                'key' => 'research',
                'title' => 'Research Activities Conducted at the Department',
                'items' => [
                    'On June 22, 2025, at the Research Institute of Cotton Growing in Tashkent, a Doctor of Science (DSc) dissertation in technical sciences, specialty 05.02.03 - Technological machines, robots, mechatronics and robotic systems, was successfully defended on the topic "Scientific foundations for the development of the design and parameter calculation of a polymer composite coating device for obtaining high-quality thread joints."',
                    'On the occasion of Teachers and Mentors Day, a Certificate of Appreciation was awarded for dedication and initiative in educating youth, fostering highly educated and well-rounded specialists, and raising patriotic, brave, and devoted young people.',
                    'To implement innovative projects, introduce them into production, support young researchers, and strengthen the material and technical base, the scientific-laboratory center "Innovative Technologies in Light Industry" and rooms for master students and researchers have been established.',
                    'Research is being conducted on resource-efficient wet-heat treatment technology of garments, clothing manufacturing with stable shape using felting techniques, polymer composite coating devices, adaptive shuttle mechanisms of sewing machines, natural dyeing technologies for fabrics and yarns, and smart textile production using silk and optical fibers.',
                ],
            ],
            [
                'key' => 'publications',
                'title' => 'Scientific and Methodological Works',
                'items' => [
                    'Bexbudov Sh. Improvement of Designs and Methods for Calculating the Parameters of Bobbins for a Sewing Machine. International Journal of Advanced Research in Science, Engineering and Technology, Vol. 02, Issue 10.',
                    'Bexbudov Sh. Design of the Experimental Unit and Principle of Operation. European Scholar Journal, Vol. 2, Issue 5, May 2021.',
                    'Bexbudov Sh. The effectiveness of using a polymer composite application device in sewing machines. International Scientific Journal "Global Science and Innovations 2021: Central Asia", February 2021.',
                    'Vafayeva Z. Improving the yarn winder on the sewing machine. International Journal for Innovative Engineering and Management Research, Vol. 10, Issue 04, April 2021.',
                    'Vafayeva Z., Bexbudov Sh. Improvement of Designs and Methods for Calculating the Parameters of Bobbins for a Sewing Machine. International Journal of Advanced Research in Science, Engineering and Technology, Vol. 02, Issue 10, October 2021.',
                    'Muxtarova Z. Improvement of the sewing machine bobbin. Central Asian Journal of Theoretical and Applied Sciences, Vol. 02, Issue 10.',
                ],
            ],
            [
                'key' => 'subjects',
                'title' => 'Disciplines Taught at the Department',
                'items' => [
                    "Bachelor's Degree:\n1. Research work and graduation thesis\n2. Experiment planning\n3. Scientific and pedagogical work\n4. Food process engineering\n5. Fundamentals of food processing\n6. Innovative machines and equipment of the food industry\n7. Research work and graduation thesis\n8. Scientific and pedagogical work\n9. Automatic machines in technological systems\n10. System approach in technological process research\n11. Innovative machines and equipment of the food industry\n12. Design and calculation of chemical and oil-gas industry equipment\n13. Scientific foundations of chemical processing machinery\n14. Industrial technologies and innovations (TMJ)\n15. Cotton industry engineering technology\n16. Reengineering\n17. Modern techniques for surface strengthening of machine parts\n18. Methods of experimental research\n19. Wear and repair methods of light industry machines\n20. Light industry engineering technology\n\nMaster's Degree:\n1. Engineering technological tools\n2. Introduction to the specialty\n3. Introduction to the oil and gas industry\n4. Calculation and design of industrial machines\n5. Equipment for processing and storage of agricultural products\n6. Processes and apparatus of the oil and gas industry\n7. Basic technological processes and apparatus\n8. Wool fiber primary processing technology\n9. Interchangeability, standardization, technical measurements and certification\n10. Reliability of sewing production equipment\n11. Materials science and structural materials technology\n12. Fundamentals of mechatronics\n13. Structural materials technology\n14. Pumps, fans and compressors\n15. Reliability of industrial machines\n16. Reliability of sewing production equipment\n17. Mechanical engineering technologies\n18. Wool primary processing technology\n19. Cryogenic engineering and refrigeration equipment\n20. Innovative technologies in the cotton industry\n21. Processes and apparatus in construction materials\n22. Research methods and tools\n23. Modern technology and equipment of industrial enterprises\n24. Technical maintenance of industrial machines\n25. Industrial technology and equipment\n26. Technology and equipment (cotton, silk, spinning, weaving, knitting, sewing production)\n27. Design and modeling of sewing and knitwear products\n28. Modern sewing production equipment\n29. Equipment and devices for sewing and knitwear production\n30. Fundamentals of sewing and knitwear design\n31. Reliability of light industry equipment\n32. Modeling and design of light industry equipment\n33. Textile industry technology and equipment\n34. Modern methods of surface strengthening of parts\n35. Wool primary processing technology\n36. Design of repair workshops\n37. Ventilation, aspiration and pneumatic transport systems\n38. Interchangeability and technical measurements\n39. Production technological processes\n40. Design of chemical industry enterprises\n41. Installation and repair of chemical industry equipment\n42. Fundamentals of composition\n43. Enterprise service, technology and engineering\n44. Design of food industry enterprises\n45. Installation and repair of food industry equipment\n46. Automated design of cotton primary processing equipment\n47. Calculation and design of technological machines and equipment\n48. Fundamentals of designing natural fiber primary processing enterprises\n49. Ergonomics and industrial design\n50. Special topics of chemical industry machines and equipment\n51. Technological systems of the chemical industry\n52. Pneumatic transport systems of textile, light and cotton industries\n53. Design of technological processes\n54. Automated design systems for light industry products",
                ],
            ],
            [
                'key' => 'prepared_specialists',
                'title' => 'Educational Programs',
                'items' => [
                    "Bachelor's Degree:\n60721400 - Light industry technologies and equipment (knitwear)\n60720700 - Technological machines and equipment (by sectors)\n60720400 - Technological machines and equipment (by sectors)\n\nMaster's Degree:\n70721201 - Textile products technology (spinning technology)\n70721402 - Technologies and equipment (leather and fur products)\n70720704 - Technology and design of garment products (garments)",
                ],
            ],
        ];
    }

    private function isInstructionalNoise(string $item): bool
    {
        return Str::contains($item, [
            '/faculty/',
            '/department/',
            'هاذه الاقسام خاصه',
            'هاذه البرامج خاصه',
            'الصفحات المسؤاله',
            'لا تغير التصميم',
            'الكنترول بانل',
            'ادخال جميع المحتوي',
            'كل شي يجب ان يكون مربوط',
        ], true);
    }

    private function localizeSections(array $sections, string $locale): array
    {
        return collect($sections)
            ->map(function ($section) use ($locale) {
                return [
                    'key' => $section['key'],
                    'title' => $this->sectionTitle($section['key'], $locale),
                    'items' => $section['items'],
                ];
            })
            ->values()
            ->all();
    }

    private function seedDepartmentStaff(Department $department, array $staffItems): void
    {
        foreach ($staffItems as $index => $staffData) {
            $name = trim((string) ($staffData['name'] ?? ''));
            if ($name === '' || Str::length($name) > 180) {
                continue;
            }
            if (in_array($this->nameKey($name), ['headof', 'departmenthead'], true)) {
                continue;
            }
            if ($this->samePersonName($name, (string) $department->head_name)) {
                continue;
            }

            $position = trim((string) ($staffData['position'] ?? 'Professor-Teacher'));
            if ($position === '' || Str::length($position) > 220) {
                $position = 'Professor-Teacher';
            }
            if (Str::contains($position, 'Head of Department', true)) {
                continue;
            }

            $staff = StaffProfile::firstOrCreate(
                ['slug' => Str::slug($department->slug.'-'.$name)],
                [
                    'faculty_id' => $department->faculty_id,
                    'department_id' => $department->id,
                    'photo' => null,
                    'email' => null,
                    'phone' => null,
                    'sort_order' => $index + 10,
                    'is_active' => true,
                ]
            );

            foreach ($this->locales as $locale) {
                StaffProfileTranslation::firstOrCreate(
                    ['staff_profile_id' => $staff->id, 'locale' => $locale],
                    [
                        'full_name' => $name,
                        'position' => $this->staffPosition($position, $locale),
                        'bio' => $this->staffPosition($position, $locale),
                        'office' => null,
                    ]
                );
            }
        }
    }

    private function seedDepartmentContact(Department $department, array $contact): void
    {
        $name = trim((string) ($contact['name'] ?? ''));
        if ($name === '') {
            return;
        }

        $slug = Str::slug($department->slug.'-'.$name);
        $staff = StaffProfile::where('slug', $slug)->first();
        if (! $staff && ! empty($contact['email'])) {
            $staff = StaffProfile::where('department_id', $department->id)
                ->where('email', $contact['email'])
                ->first();
        }

        if (! $staff) {
            $staff = new StaffProfile(['slug' => $slug]);
        }

        $profileValues = [
            'slug' => $slug,
            'faculty_id' => $department->faculty_id,
            'department_id' => $department->id,
            'photo' => $this->headPhoto($name),
            'email' => $contact['email'] ?? null,
            'phone' => $contact['phone'] ?? null,
            'sort_order' => 1,
            'is_active' => true,
        ];

        if (! $staff->exists) {
            $staff->fill($profileValues);
        } else {
            $staff->fill($this->missingOnly($staff, $profileValues));
        }

        if ($staff->isDirty()) {
            $staff->save();
        }

        foreach ($this->locales as $locale) {
            StaffProfileTranslation::firstOrCreate(
                ['staff_profile_id' => $staff->id, 'locale' => $locale],
                [
                    'full_name' => $name,
                    'position' => $this->sectionTitle('head', $locale),
                    'bio' => $this->sectionTitle('head', $locale),
                    'office' => $contact['reception_time'] ?? null,
                ]
            );
        }
    }

    private function deactivateInvalidStaff(Department $department): void
    {
        return;
    }

    private function applyDepartmentStaffCorrections(Department $department): void
    {
        return;
    }

    private function missingOnly(Model $model, array $values): array
    {
        $updates = [];

        foreach ($values as $key => $value) {
            if ($value !== null && ($model->{$key} === null || $model->{$key} === '')) {
                $updates[$key] = $value;
            }
        }

        return $updates;
    }

    private function headPhoto(string $name): ?string
    {
        return [
            'Latipov Saidmurod Tuyg\'unovich' => 'cms/staff/latipov-saidmurod-tuygunovich.jpg',
            'Mirzaev Shamsiddin Rajabovich' => 'cms/staff/mirzaev-shamsiddin-rajabovich.jpg',
            'Mirzayev Shamsiddin Rajabovich' => 'cms/staff/mirzaev-shamsiddin-rajabovich.jpg',
            'Inomjon Ilhomovich Tojiyev' => 'cms/staff/inomjon-ilhomovich-tojiyev.png',
            'Tojiyev In’omjon Ilhomovich' => 'cms/staff/inomjon-ilhomovich-tojiyev.png',
            'Farhod Farmonovich Qazoqov' => 'cms/staff/farhod-farmonovich-qazoqov.jpg',
            'Kazokov Farkhad Farmonovich' => 'cms/staff/farhod-farmonovich-qazoqov.jpg',
            'Fakhriddin Yusupovich Khabibov' => 'cms/staff/fakhriddin-yusupovich-khabibov.jpg',
            'Xabibov Faxriddin Yusupovich' => 'cms/staff/fakhriddin-yusupovich-khabibov.jpg',
            'Uyg‘un Abdullayevich O‘rinov' => 'cms/staff/uygun-abdullayevich-orinov.jpg',
            'O‘rinov Uyg‘un Abdullayevich' => 'cms/staff/uygun-abdullayevich-orinov.jpg',
        ][$name] ?? null;
    }

    private function samePersonName(string $first, string $second): bool
    {
        return $this->nameKey($first) === $this->nameKey($second);
    }

    private function nameKey(string $name): string
    {
        return Str::of($name)
            ->replace(['‘', '’', '`', 'ʼ'], "'")
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/u', '')
            ->toString();
    }

    private function firstMeaningfulText(array $sections): ?string
    {
        foreach (['history', 'overview'] as $key) {
            $section = collect($sections)->firstWhere('key', $key);
            $text = trim((string) ($section['items'][0] ?? ''));
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    private function sectionTitle(string $key, string $locale): string
    {
        $labels = [
            'overview' => ['en' => 'Overview', 'uz' => 'Umumiy maʼlumot', 'ru' => 'Обзор', 'ar' => 'نظرة عامة'],
            'history' => ['en' => 'Department History', 'uz' => 'Kafedra tarixi', 'ru' => 'История кафедры', 'ar' => 'تاريخ القسم'],
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'prepared_specialists' => ['en' => 'Prepared Specialists', 'uz' => 'Tayyorlanadigan mutaxassislar', 'ru' => 'Подготавливаемые специалисты', 'ar' => 'التخصصات التي يتم إعدادها'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'O‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المقررات الدراسية'],
            'activities' => ['en' => 'Activities of the Department', 'uz' => 'Kafedra faoliyati', 'ru' => 'Деятельность кафедры', 'ar' => 'أنشطة القسم'],
            'publications' => ['en' => 'Textbooks and Articles', 'uz' => 'Darsliklar va maqolalar', 'ru' => 'Учебники и статьи', 'ar' => 'الكتب والمقالات'],
            'research' => ['en' => 'Research Work', 'uz' => 'Ilmiy-tadqiqot ishlari', 'ru' => 'Научно-исследовательская работа', 'ar' => 'الأعمال البحثية'],
            'cooperation' => ['en' => 'Cooperation', 'uz' => 'Hamkorlik', 'ru' => 'Сотрудничество', 'ar' => 'التعاون'],
            'plans' => ['en' => 'Prospective Plans', 'uz' => 'Istiqboldagi rejalar', 'ru' => 'Перспективные планы', 'ar' => 'الخطط المستقبلية'],
            'head' => ['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
        ];

        return $labels[$key][$locale] ?? reset($labels[$key]) ?: Str::headline($key);
    }

    private function staffPosition(string $position, string $locale): string
    {
        return match (true) {
            Str::contains($position, 'Doctor of Technical Sciences', true) => match ($locale) {
                'uz' => 'Texnika fanlari doktori, professor',
                'ru' => 'Доктор технических наук, профессор',
                'ar' => 'دكتور في العلوم التقنية، أستاذ',
                default => $position,
            },
            Str::contains($position, ['Associate Professor', 'dotsent'], true) => match ($locale) {
                'uz' => 'Dotsent',
                'ru' => 'Доцент',
                'ar' => 'أستاذ مشارك',
                default => $position,
            },
            Str::contains($position, 'PhD', true) => match ($locale) {
                'uz' => 'PhD, dotsent',
                'ru' => 'PhD, доцент',
                'ar' => 'دكتوراه، أستاذ مشارك',
                default => $position,
            },
            default => match ($locale) {
                'uz' => 'Kafedra professor-o‘qituvchisi',
                'ru' => 'Преподаватель кафедры',
                'ar' => 'عضو هيئة تدريس في القسم',
                default => $position,
            },
        };
    }
}
