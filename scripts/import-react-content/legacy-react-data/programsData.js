import { 
  Laptop, Hammer, Building, TrendingUp, FlaskConical, 
  Zap, Wrench, Shield, Coffee, Scissors, Leaf, Package, Gauge, Pickaxe, Factory
} from "lucide-react";

const technologyProgram = ({
  id,
  code,
  name,
  degree = "Bachelor",
  departmentId,
  coordinator,
  icon = FlaskConical,
  color = "teal",
  description,
  curriculum = [],
  careerOpportunities = [],
}) => ({
  id,
  code,
  departmentId,
  facultyId: "faculty-of-technology",
  coordinator,
  name,
  degree,
  duration: degree === "Master" ? "2 years" : degree === "PhD" ? "3 years" : "4 years",
  icon,
  color,
  description,
  detailedDescription: description,
  careerOpportunities,
  curriculum,
  requirements: [
    "Admission is conducted under the applicable national higher education rules.",
    "Applicants should meet the entrance requirements for the selected technology field.",
    "Laboratory safety and practical training readiness are required after enrollment."
  ]
});

export const programsData = [
  {
    id: "software-engineering",
    departmentId: "information-communication-technologies",
    facultyId: "faculty-of-service-and-digitalization",
    coordinator: "Dr. Sherbek N. Xayitov",
    accreditations: ["Uzbek Ministry of Higher Education Accreditation", "ABET Alignment Portfolio", "Oracle Academy Partner Certification"],
    name: "Software Engineering",
    degree: "Bachelor",
    duration: "4 years",
    icon: Laptop,
    color: "cyan",
    description: "Focuses on advanced software engineering, algorithm design, software architecture, and full-stack development.",
    detailedDescription: "The Software Engineering program at Bukhara State Technical University prepares students to design, develop, and maintain complex software systems. Students learn software design patterns, object-oriented design, Agile methodologies, cloud computing, database structures, and advanced mobile application development. The curriculum emphasizes industry-aligned practical projects.",
    careerOpportunities: [
      "Software Architect",
      "Full Stack Developer",
      "Mobile App Developer",
      "DevOps Engineer",
      "QA Engineer"
    ],
    curriculum: [
      "Algorithms & Data Structures",
      "Software Design Patterns",
      "Object-Oriented Design",
      "Agile Software Development",
      "Cloud Systems & DevOps",
      "Mobile Application Development"
    ],
    requirements: [
      "Secondary school diploma",
      "High scores in Mathematics and Physics exams",
      "IELTS 5.5 or equivalent recommended"
    ]
  },
  {
    id: "cybersecurity",
    departmentId: "information-communication-technologies",
    facultyId: "faculty-of-service-and-digitalization",
    coordinator: "Dr. Sherbek N. Xayitov",
    accreditations: ["CISCO Academy Quality Endorsement", "Ministry of Digital Technologies Professional Certificate"],
    name: "Information Systems Security & Cybersecurity",
    degree: "Bachelor",
    duration: "4 years",
    icon: Shield,
    color: "indigo",
    description: "Specialized training in network security, cryptography, threat assessment, and secure software development.",
    detailedDescription: "The Information Systems Security & Cybersecurity program at BSTU provides comprehensive education in protecting networks, computers, programs, and data from attack, damage, or unauthorized access. Students study cryptography, ethical hacking, digital forensics, network security protocols, and security compliance standards. Practical training includes laboratory simulations of cyber-attack scenarios.",
    careerOpportunities: [
      "Cybersecurity Analyst",
      "Information Security Officer",
      "Ethical Hacker / Penetration Tester",
      "Network Security Engineer",
      "Digital Forensics Examiner"
    ],
    curriculum: [
      "Introduction to Cryptography",
      "Network Security Protocols",
      "Ethical Hacking and Auditing",
      "Digital Forensics & Investigation",
      "Secure Software Development",
      "Information Security Compliance"
    ],
    requirements: [
      "Secondary school diploma",
      "High scores in national Mathematics and Informatics exams",
      "Analytical and logic test clearance"
    ]
  },
  {
    id: "power-engineering",
    departmentId: "electrical-power-engineering",
    facultyId: "faculty-of-engineering",
    coordinator: "Dr. Anvar T. Sadullayev",
    accreditations: ["IEEE Power & Energy Society Audit Endorsement", "National Smart Grid Standard Alignment Certificate"],
    name: "Power Engineering",
    degree: "Bachelor",
    duration: "4 years",
    icon: Zap,
    color: "red",
    description: "Electrical power systems, electric grids, renewable energy, and power station operations.",
    detailedDescription: "Power Engineering covers electricity generation, transmission, distribution, and consumption. The curriculum focuses on electric grids, high-voltage technology, power electronics, and renewable energy integrations (solar, wind). Students practice in advanced power electronics laboratories and learn how to manage smart grids and power stations.",
    careerOpportunities: [
      "Power Grid Engineer",
      "Electrical Systems Designer",
      "Renewable Energy Consultant",
      "Substation Operations Manager",
      "Energy Auditor"
    ],
    curriculum: [
      "Electrical Circuits & Fields",
      "Power Generation Systems",
      "Transmission and Distribution Networks",
      "Renewable Energy Technology",
      "Power Electronics & Control",
      "Smart Grid Management"
    ],
    requirements: [
      "Successful completion of secondary education",
      "High scores in national Physics and Mathematics entrance exams",
      "Aptitude for electro-technical subjects"
    ]
  },
  {
    id: "mechanical-engineering",
    departmentId: "mechanics-engineering-graphics",
    facultyId: "faculty-of-engineering",
    coordinator: "Dr. Jasur A. Nematov",
    accreditations: ["National Quality Standards Accreditation", "ISO 9001:2015 Educational Standard Certificate"],
    name: "Mechanical Engineering",
    degree: "Bachelor",
    duration: "4 years",
    icon: Wrench,
    color: "indigo",
    description: "Mechanical design, thermodynamics, manufacturing technologies, robotics, and machine parts.",
    detailedDescription: "The Mechanical Engineering program at BSTU provides a comprehensive background in machine design, thermodynamics, fluid mechanics, robotics, and automated manufacturing systems. Students study computer-aided engineering (CAD/CAM/CAE), materials science, and machine assembly. The program trains engineers to build and optimize mechanical systems for industrial operations.",
    careerOpportunities: [
      "Mechanical Design Engineer",
      "Robotics Engineer",
      "Manufacturing Analyst",
      "Automated Systems Specialist",
      "Maintenance Engineer"
    ],
    curriculum: [
      "Engineering Mechanics",
      "Thermodynamics and Fluids",
      "Computer-Aided Engineering",
      "Robotics & Automation",
      "Machine Dynamics",
      "Materials Science"
    ],
    requirements: [
      "Secondary school diploma",
      "High scores in Mathematics and Physics exams",
      "Spatial thinking capabilities"
    ]
  },
  {
    id: "oil-gas-engineering",
    code: "60721100",
    departmentId: "oil-gas-engineering-upstream-downstream",
    facultyId: "faculty-of-technology",
    coordinator: "Sharipov Qaxramon Qandiyorovich",
    name: "Oil and Gas Engineering",
    degree: "Bachelor",
    duration: "4 years",
    icon: FlaskConical,
    color: "teal",
    description: "Petroleum refining, gas processing, extraction methods, and petrochemical engineering support.",
    detailedDescription: "The Oil and Gas Engineering program prepares specialists for petroleum extraction, refining, gas processing, and petrochemical synthesis. Bukhara is Uzbekistan's energy hub, offering students direct exposure to petrochemical plants and refineries. Coursework covers reservoir engineering, gas refining technologies, oil storage logistics, and environmental safety protocols.",
    careerOpportunities: [
      "Refinery Process Engineer",
      "Drilling Engineer",
      "Reservoir Engineer",
      "Petrochemical Quality Specialist",
      "Pipeline Operations Manager"
    ],
    curriculum: [
      "Reservoir Engineering",
      "Drilling and Extraction",
      "Petroleum Refining Technology",
      "Natural Gas Processing",
      "Petrochemical Synthesis",
      "Pipeline Logistics and Safety"
    ],
    requirements: [
      "Secondary school diploma",
      "High scores in Chemistry and Mathematics exams",
      "Industrial safety compliance"
    ]
  },
  {
    id: "automotive-engineering",
    departmentId: "vehicle-engineering-automotive-transport-systems",
    facultyId: "faculty-of-natural-resources-management",
    coordinator: "Dr. Alisher M. Kurbanov",
    accreditations: ["ISO/TC 22 Automotive Standards Compliance", "National Transportation Safety Council Endorsement"],
    name: "Automotive Engineering",
    degree: "Bachelor",
    duration: "4 years",
    icon: Hammer,
    color: "orange",
    description: "Automotive systems design, internal combustion engines, transport logistics, and vehicle assembly.",
    detailedDescription: "The Automotive Engineering program covers vehicle design, engine performance, transmission systems, automotive electronics, and assembly logistics. Given the scale of automotive manufacturing in Uzbekistan, this program prepares engineers to lead design and assembly lines, conduct vehicle safety audits, and implement smart transport technologies.",
    careerOpportunities: [
      "Automotive Design Engineer",
      "Assembly Line Manager",
      "Vehicle Safety Auditor",
      "Automotive Electronics Specialist",
      "Logistics Engineer"
    ],
    curriculum: [
      "Vehicle Design and Dynamics",
      "Internal Combustion Engines",
      "Automotive Electronics",
      "Transmission Systems",
      "Vehicle Safety Standards",
      "Transportation Logistics"
    ],
    requirements: [
      "Secondary school diploma",
      "High scores in Physics and Mathematics exams",
      "CAD modeling skills recommended"
    ]
  },
  {
    id: "architecture",
    departmentId: "architecture",
    facultyId: "faculty-of-engineering",
    coordinator: "Dr. Shamsiddin R. Mirzayev",
    accreditations: ["National Historical Preservation Council Endorsement", "UIA Syllabus Quality Seal"],
    name: "Architecture",
    degree: "Bachelor",
    duration: "5 years",
    icon: Building,
    color: "orange",
    description: "Architectural design, urban planning, structural physics, history of architecture, and building materials.",
    detailedDescription: "The Architecture program is a rigorous 5-year curriculum blending art, history, science, and technology. Students learn architectural drawing, 3D modeling, sustainable design principles, building safety codes, and urban design. The coursework includes studio projects where students design buildings and public spaces, responding to cultural, environmental, and technological requirements.",
    careerOpportunities: [
      "Architect",
      "Urban Planner",
      "Interior Architect",
      "Project Manager",
      "CAD/BIM Specialist"
    ],
    curriculum: [
      "Architectural Design Studios (I to VIII)",
      "History of World and National Architecture",
      "Building Construction and Materials",
      "Computer-Aided Architectural Drafting (Revit/AutoCAD)",
      "Urban Planning and Development",
      "Sustainable Architecture & Green Building"
    ],
    requirements: [
      "Successful completion of secondary education",
      "BSTU Architectural Drawing & Sketching Entrance Exam",
      "Basic understanding of structural physics"
    ]
  },
  {
    id: "economics",
    departmentId: "economics-management",
    facultyId: "faculty-of-service-and-digitalization",
    coordinator: "Dr. Sherzod N. Xashimov",
    accreditations: ["EFMD Quality Improvement System Membership", "State Economy Development Index Validation"],
    name: "Economics",
    degree: "Bachelor",
    duration: "4 years",
    icon: TrendingUp,
    color: "pink",
    description: "Microeconomics, macroeconomics, industrial economics, econometrics, and financial systems.",
    detailedDescription: "The Economics program gives students the analytical and quantitative skills to understand economic systems, market behaviors, and corporate finances. The curriculum covers econometrics, public finance, corporate strategy, international trade, and resource management. Students learn to use statistical software (SPSS, R, Excel) to model market trends and evaluate industrial investment projects.",
    careerOpportunities: [
      "Financial Analyst",
      "Economic Advisor",
      "Market Researcher",
      "Data Analyst",
      "Budget Analyst"
    ],
    curriculum: [
      "Microeconomics & Macroeconomics",
      "Introduction to Econometrics",
      "Corporate Finance and Accounting",
      "Industrial Economics and Organization",
      "Public Finance and Economic Policy",
      "Statistical Analysis & Data Modeling"
    ],
    requirements: [
      "Successful completion of secondary education",
      "High scores in Mathematics and Social Sciences/History exams",
      "Analytical thinking and problem-solving aptitude"
    ]
  },
  {
    id: "construction",
    departmentId: "civil-engineering",
    facultyId: "faculty-of-engineering",
    coordinator: "Dr. Baxtiyor S. Kobilov",
    accreditations: ["FIDIC Project Management Quality Endorsement", "Uzbekistan Civil Engineers Association Certificate"],
    name: "Construction",
    degree: "Bachelor",
    duration: "4 years",
    icon: Building,
    color: "teal",
    description: "Civil engineering, building construction technologies, structural mechanics, and project estimation.",
    detailedDescription: "The Construction Engineering program trains professionals in building design, construction management, and structural safety. Coursework covers soil mechanics, concrete and steel structures, quantity surveying, project scheduling, and occupational safety. Graduates are equipped to supervise construction sites, estimate project budgets, and design robust infrastructures.",
    careerOpportunities: [
      "Construction Project Manager",
      "Structural Engineer",
      "Site Supervisor",
      "Quantity Surveyor / Estimator",
      "Safety Inspector"
    ],
    curriculum: [
      "Introduction to Civil Engineering",
      "Soil Mechanics and Foundation Design",
      "Reinforced Concrete and Steel Structures",
      "Construction Technologies and Equipment",
      "Project Management & Estimation",
      "Structural Health Monitoring"
    ],
    requirements: [
      "Successful completion of secondary education",
      "High scores in national Mathematics and Physics entrance exams",
      "Understanding of construction graphics and CAD tools"
    ]
  },
  {
    id: "metallurgy",
    departmentId: "technological-machines-equipment",
    facultyId: "faculty-of-engineering",
    coordinator: "Dr. Jamshid R. Jalolov",
    accreditations: ["ISO/TC 135 Materials Testing Certification", "National Metallurgy Quality Index Endorsement"],
    name: "Metallurgy",
    degree: "Bachelor",
    duration: "4 years",
    icon: Shield,
    color: "indigo",
    description: "Metal extraction, physical metallurgy, heat treatment, alloy design, and materials characterization.",
    detailedDescription: "Metallurgy deals with the extraction of metals from ores, their refining, alloying, and fabrication into useful products. Students learn about physical and chemical metallurgy, material characterization techniques (microscopy, XRD), heat treatment processes, and non-destructive testing (NDT). The program highlights alloy engineering and metal recycling.",
    careerOpportunities: [
      "Metallurgical Engineer",
      "Materials Characterization Specialist",
      "Heat Treatment Supervisor",
      "Foundry Engineer",
      "Corrosion Control Specialist"
    ],
    curriculum: [
      "Extractive Metallurgy",
      "Physical Metallurgy & Alloy Design",
      "Mechanical Behavior of Metals",
      "Foundry and Casting Technologies",
      "Heat Treatment and Processing",
      "Non-Destructive Testing (NDT)"
    ],
    requirements: [
      "Successful completion of secondary education",
      "High scores in Chemistry, Physics, and Mathematics entrance exams",
      "Strong laboratory research aptitude"
    ]
  },
  {
    id: "food-technology",
    code: "60720100",
    departmentId: "food-technology-service",
    facultyId: "faculty-of-technology",
    coordinator: "Qurbonov Murod Tashpulatovich",
    name: "Food Technology",
    degree: "Bachelor",
    duration: "4 years",
    icon: Coffee,
    color: "orange",
    description: "Food chemistry, food preservation, processing technologies, quality control, and safety standard certifications.",
    detailedDescription: "Food Technology covers the processing, preservation, packaging, and distribution of food products. Students study food chemistry, industrial biotechnology, food safety management (HACCP), and sensory evaluation. Practical laboratory training equips students to design high-quality, safe, and nutritious food products.",
    careerOpportunities: [
      "Food Safety Auditor (HACCP)",
      "Quality Assurance Officer",
      "Food Product Developer",
      "Processing Line Manager",
      "Sensory Analyst"
    ],
    curriculum: [
      "Introduction to Food Science",
      "Food Chemistry and Analysis",
      "Food Microbiology & Safety",
      "Preservation and Processing Technologies",
      "Food Packaging and Logistics",
      "HACCP and Quality Control Systems"
    ],
    requirements: [
      "Successful completion of secondary education",
      "High scores in national Chemistry and Biology exams",
      "Strict compliance with hygiene and lab guidelines"
    ]
  },
  {
    id: "textile-engineering",
    departmentId: "light-industry-engineering-and-design",
    facultyId: "faculty-of-engineering",
    coordinator: "Dr. Nodira M. Adilova",
    accreditations: ["Oeko-Tex Standard 100 Process Alignment Certificate", "National Textile Cluster Quality Endorsement"],
    name: "Textile Engineering",
    degree: "Bachelor",
    duration: "4 years",
    icon: Scissors,
    color: "pink",
    description: "Fiber production, yarn manufacturing, textile design, weaving technologies, and garment fabrication.",
    detailedDescription: "Textile Engineering focuses on the manufacturing of yarn, fabric, and garments from cotton, wool, and synthetic fibers. Uzbekistan is a major cotton producer, making this program highly relevant. Students study fiber physics, spinning and weaving technologies, textile dyeing, and computer-aided pattern design (CAD) for fashion and industrial garments.",
    careerOpportunities: [
      "Textile Processing Engineer",
      "Garment Production Manager",
      "Textile Designer / CAD Specialist",
      "Quality Control Inspector",
      "Raw Cotton Processing Expert"
    ],
    curriculum: [
      "Fiber Science and Physics",
      "Yarn Spinning Technologies",
      "Weaving and Fabric Structure",
      "Textile Dyeing and Finishing",
      "Garment Manufacturing Technology",
      "Computer-Aided Garment Design"
    ],
    requirements: [
      "Successful completion of secondary education",
      "High scores in Physics, Chemistry, and Mathematics entrance exams",
      "Creativity and industrial design interest"
    ]
  },
  technologyProgram({
    id: "chemical-engineering",
    code: "60710100",
    name: "Chemical Engineering",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: FlaskConical,
    color: "teal",
    description: "Training in chemical engineering, inorganic and organic substances, high molecular compounds, silicate materials, and industrial chemical processes.",
    curriculum: ["General chemical technology", "Inorganic substances technology", "Organic substances technology", "Polymer production technology", "Silicate materials technology", "Industrial process equipment"],
    careerOpportunities: ["Chemical process engineer", "Laboratory technologist", "Production technologist", "Quality control specialist"]
  }),
  technologyProgram({
    id: "biotechnology",
    code: "60710200",
    name: "Biotechnology",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: FlaskConical,
    color: "green",
    description: "Biotechnology training connected with industrial, food, agricultural, and chemical technology processes.",
    curriculum: ["Industrial biotechnology", "Biochemical processes", "Microbiology", "Bioprocess equipment", "Product quality control", "Research methods"],
    careerOpportunities: ["Biotechnologist", "Laboratory specialist", "Production technologist", "Research assistant"]
  }),
  technologyProgram({
    id: "printing-packaging-engineering",
    code: "60710300",
    name: "Printing and Packaging Engineering",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: Package,
    color: "orange",
    description: "Engineering training for printing and packaging processes, materials, equipment, and industrial production quality.",
    curriculum: ["Printing process technology", "Packaging materials", "Industrial equipment", "Quality control", "Chemical materials for packaging", "Production practice"],
    careerOpportunities: ["Packaging engineer", "Printing process technologist", "Quality specialist", "Production supervisor"]
  }),
  technologyProgram({
    id: "metrology-standardization",
    code: "60710800",
    name: "Metrology and Standardization",
    departmentId: "metrology-standardization-quality-control",
    coordinator: "Tairov Bakhtiyor Bobokulovich",
    icon: Gauge,
    color: "blue",
    description: "Preparation of specialists in metrology, standardization, calibration, certification, and quality control.",
    curriculum: ["Metrology", "Standardization", "Certification", "Measurement methods", "Quality management", "Calibration practice"],
    careerOpportunities: ["Metrology engineer", "Standardization specialist", "Certification specialist", "Quality control engineer"]
  }),
  technologyProgram({
    id: "agricultural-products-storage-processing",
    code: "60810700",
    name: "Storage and Processing Technology of Agricultural Products",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Technology of storing, processing, and quality control of agricultural products with practical industry training.",
    curriculum: ["Agricultural product storage", "Processing technology", "Oil-fat raw materials", "Food safety", "Quality assessment", "Production practice"],
    careerOpportunities: ["Storage technologist", "Processing engineer", "Quality specialist", "Food production supervisor"]
  }),
  technologyProgram({
    id: "horticulture-viticulture",
    code: "60811000",
    name: "Horticulture and Viticulture",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Preparation in horticulture, viticulture, product storage, and post-harvest processing technologies.",
    curriculum: ["Horticulture", "Viticulture", "Fruit storage", "Post-harvest technology", "Processing practice", "Quality control"],
    careerOpportunities: ["Horticulture specialist", "Viticulture technologist", "Storage specialist", "Agricultural processing supervisor"]
  }),
  technologyProgram({
    id: "gas-processing-technology",
    code: "60720500",
    name: "Gas Processing Technology",
    departmentId: "oil-gas-refining-technology",
    coordinator: "Ochilov Abduraxim Abdurasulovich",
    icon: Factory,
    color: "teal",
    description: "Training in hydrocarbon gas processing, deep gas processing, process equipment, and product quality control.",
    curriculum: ["Hydrocarbon gas processing", "Gas refining equipment", "Chemical technology", "Product quality control", "Energy-saving technologies", "Industrial practice"],
    careerOpportunities: ["Gas processing technologist", "Refinery process operator", "Quality control specialist", "Production engineer"]
  }),
  technologyProgram({
    id: "oil-gas-processing-technology",
    code: "60720600",
    name: "Oil and Oil-Gas Processing Technology",
    departmentId: "oil-gas-refining-technology",
    coordinator: "Ochilov Abduraxim Abdurasulovich",
    icon: Factory,
    color: "teal",
    description: "Technology of oil, petroleum products, and oil-gas processing for refinery and petrochemical enterprises.",
    curriculum: ["Oil and gas refining technology", "Fuels and oils chemistry", "Refinery equipment", "Process design", "Waste utilization", "Product research methods"],
    careerOpportunities: ["Oil refining technologist", "Petrochemical specialist", "Refinery engineer", "Quality control specialist"]
  }),
  technologyProgram({
    id: "geology-exploration-mineral-deposits",
    code: "60720900",
    name: "Geology, Exploration and Prospecting of Mineral Deposits",
    departmentId: "oil-gas-engineering-upstream-downstream",
    coordinator: "Sharipov Qaxramon Qandiyorovich",
    icon: Pickaxe,
    color: "gray",
    description: "Training connected with geology, exploration, prospecting, and mineral deposit assessment for resource industries.",
    curriculum: ["General geology", "Mineral deposits", "Exploration methods", "Field practice", "Resource assessment", "Industrial safety"],
    careerOpportunities: ["Geology specialist", "Exploration technician", "Field engineer", "Resource assessment assistant"]
  }),
  technologyProgram({
    id: "polymer-production-technology",
    code: "70710103",
    name: "Chemical Technology of High-Molecular Compounds (Polymer Production)",
    degree: "Master",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: FlaskConical,
    color: "purple",
    description: "Master specialization in polymer production, high molecular compounds, and advanced chemical technology.",
    curriculum: ["Polymer production technology", "High molecular compounds", "Research methodology", "Process optimization", "Scientific-pedagogical work", "Master thesis research"],
    careerOpportunities: ["Polymer technologist", "Research engineer", "Chemical production specialist", "Laboratory manager"]
  }),
  technologyProgram({
    id: "grain-storage-processing-technology",
    code: "70720101",
    name: "Technology of Food Production and Processing (Grain Storage and Processing Technology)",
    degree: "Master",
    departmentId: "food-technology-service",
    coordinator: "Qurbonov Murod Tashpulatovich",
    icon: Coffee,
    color: "orange",
    description: "Master specialization in grain storage, grain processing, bakery products, and food production research.",
    curriculum: ["Grain biochemistry", "Grain storage technology", "Bakery technology", "Food processing research", "Scientific-pedagogical work", "Master thesis preparation"],
    careerOpportunities: ["Grain processing technologist", "Food production researcher", "Quality manager", "Bakery production specialist"]
  }),
  technologyProgram({
    id: "oil-processing-technology",
    code: "70720101",
    name: "Technology of Food Production and Processing (Oil Processing Technology)",
    degree: "Master",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Master specialization in oil and oil-fat products processing, quality, and production technologies.",
    curriculum: ["Oil processing technology", "Oil-fat products", "Raw material quality", "Food production research", "Scientific-pedagogical work", "Master thesis preparation"],
    careerOpportunities: ["Oil processing technologist", "Oil-fat quality specialist", "Food production researcher", "Production manager"]
  }),
  technologyProgram({
    id: "inorganic-chemical-technology",
    code: "70710101",
    name: "Chemical Technology (Inorganic Substances Chemical Technology)",
    degree: "Master",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: FlaskConical,
    color: "teal",
    description: "Master specialization in inorganic substances, industrial chemical technology, and applied research.",
    curriculum: ["Inorganic substances technology", "Mineral fertilizers technology", "Chemical process design", "Research methodology", "Scientific-pedagogical work", "Master thesis research"],
    careerOpportunities: ["Inorganic chemical technologist", "Research specialist", "Production engineer", "Quality control specialist"]
  }),
  technologyProgram({
    id: "silicate-refractory-materials-technology",
    code: "70710101",
    name: "Chemical Technology (Silicate and Refractory Nonmetallic Materials Technology)",
    degree: "Master",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: Factory,
    color: "gray",
    description: "Master specialization in silicate, refractory, and hard-melting nonmetallic materials technology.",
    curriculum: ["Silicate materials technology", "Refractory materials", "Material properties", "Industrial furnaces", "Research methodology", "Master thesis research"],
    careerOpportunities: ["Silicate materials technologist", "Refractory production specialist", "Materials researcher", "Quality engineer"]
  }),
  technologyProgram({
    id: "organic-chemical-technology",
    code: "70710101",
    name: "Chemical Technology (Organic Substances Chemical Technology)",
    degree: "Master",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: FlaskConical,
    color: "purple",
    description: "Master specialization in organic substances, chemical production, and industrial organic synthesis.",
    curriculum: ["Organic substances technology", "Organic synthesis", "Industrial process equipment", "Research methodology", "Scientific-pedagogical work", "Master thesis research"],
    careerOpportunities: ["Organic chemical technologist", "Production engineer", "Research specialist", "Laboratory manager"]
  }),
  technologyProgram({
    id: "oil-gas-refining-technology-program",
    code: "60721100",
    name: "Technology of Oil and Gas Refining",
    departmentId: "oil-gas-refining-technology",
    coordinator: "Ochilov Abduraxim Abdurasulovich",
    icon: Factory,
    color: "teal",
    description: "Bachelor training in oil and gas refining technology for refinery and petrochemical enterprises.",
    curriculum: ["Oil and gas refining technology", "Refining processes", "Refinery equipment", "Product quality control", "Energy-saving technologies", "Industrial internship"],
    careerOpportunities: ["Refinery technologist", "Process engineer", "Product quality specialist", "Petrochemical operator"]
  }),
  technologyProgram({
    id: "deep-gas-processing-technology",
    code: "60720500",
    name: "Technology of Deep Gas Processing",
    departmentId: "oil-gas-refining-technology",
    coordinator: "Ochilov Abduraxim Abdurasulovich",
    icon: Factory,
    color: "teal",
    description: "Bachelor training in deep processing of natural and hydrocarbon gases.",
    curriculum: ["Deep gas processing", "Hydrocarbon gas technology", "Gas processing equipment", "Chemical process control", "Product quality", "Industrial practice"],
    careerOpportunities: ["Gas processing technologist", "Process operator", "Quality control specialist", "Production engineer"]
  }),
  technologyProgram({
    id: "oil-gas-processing-master",
    code: "70720600",
    name: "Technology of Oil and Gas Processing",
    degree: "Master",
    departmentId: "oil-gas-refining-technology",
    coordinator: "Ochilov Abduraxim Abdurasulovich",
    icon: Factory,
    color: "teal",
    description: "Master specialization in oil and gas processing technology, research, and process improvement.",
    curriculum: ["Oil and gas processing research", "Advanced refining processes", "Process optimization", "Scientific-pedagogical work", "Industrial cooperation", "Master thesis research"],
    careerOpportunities: ["Senior refinery technologist", "Research engineer", "Process improvement specialist", "Production manager"]
  }),
  technologyProgram({
    id: "metrology-quality-management",
    code: "60711300",
    name: "Metrology, Standardization and Product Quality Management",
    departmentId: "metrology-standardization-quality-control",
    coordinator: "Tairov Bakhtiyor Bobokulovich",
    icon: Gauge,
    color: "blue",
    description: "Bachelor program in metrology, standardization, product quality management, and measurement systems.",
    curriculum: ["Metrology", "Standardization", "Product quality management", "Certification", "Measurement systems", "Calibration practice"],
    careerOpportunities: ["Quality management specialist", "Metrology engineer", "Certification specialist", "Standards specialist"]
  }),
  technologyProgram({
    id: "metrology-standardization-quality-management-master",
    code: "70710802",
    name: "Metrology, Standardization and Quality Management",
    degree: "Master",
    departmentId: "metrology-standardization-quality-control",
    coordinator: "Tairov Bakhtiyor Bobokulovich",
    icon: Gauge,
    color: "blue",
    description: "Master specialization in metrology, standardization, quality management, and advanced measurement research.",
    curriculum: ["Advanced metrology", "Quality management systems", "International standards", "Measurement uncertainty", "Research methodology", "Master thesis research"],
    careerOpportunities: ["Quality systems manager", "Senior metrologist", "Certification expert", "Standards researcher"]
  }),
  technologyProgram({
    id: "oil-gas-field-development",
    code: "60721800",
    name: "Oil and Gas Field Development",
    departmentId: "oil-gas-engineering-upstream-downstream",
    coordinator: "Sharipov Qaxramon Qandiyorovich",
    icon: Pickaxe,
    color: "gray",
    description: "Bachelor training for start-up, development, and exploitation of oil and gas fields.",
    curriculum: ["Oil and gas field development", "Field exploitation", "Reservoir basics", "Production equipment", "Industrial safety", "Field practice"],
    careerOpportunities: ["Field development specialist", "Production engineer", "Oilfield operator", "Field safety technician"]
  }),
  technologyProgram({
    id: "oil-gas-field-machinery-master",
    code: "70721802",
    name: "Machinery and Equipment for Oil and Gas Fields",
    degree: "Master",
    departmentId: "oil-gas-engineering-upstream-downstream",
    coordinator: "Sharipov Qaxramon Qandiyorovich",
    icon: Wrench,
    color: "gray",
    description: "Master specialization in machinery, equipment, and operation systems for oil and gas fields.",
    curriculum: ["Oilfield machinery", "Equipment operation", "Maintenance systems", "Field safety", "Scientific-pedagogical work", "Master thesis research"],
    careerOpportunities: ["Oilfield equipment engineer", "Maintenance manager", "Field machinery specialist", "Operations engineer"]
  }),
  technologyProgram({
    id: "wine-fermentation-soft-drinks-technology",
    code: "60720300",
    name: "Technology of Wine, Fermentation Products and Soft Drinks",
    departmentId: "food-technology-service",
    coordinator: "Qurbonov Murod Tashpulatovich",
    icon: Coffee,
    color: "orange",
    description: "Bachelor training in wine, fermentation products, soft drinks, and related food technology processes.",
    curriculum: ["Fermentation technology", "Wine technology", "Soft drinks technology", "Food microbiology", "Product quality", "Production practice"],
    careerOpportunities: ["Fermentation technologist", "Beverage production specialist", "Quality control specialist", "Food production supervisor"]
  }),
  technologyProgram({
    id: "food-conservation-technology",
    code: "60720400",
    name: "Conservation Technology",
    departmentId: "food-technology-service",
    coordinator: "Qurbonov Murod Tashpulatovich",
    icon: Coffee,
    color: "orange",
    description: "Bachelor training in food conservation, preservation, processing, and product safety.",
    curriculum: ["Food conservation", "Preservation methods", "Food microbiology", "Packaging", "Food safety", "Production practice"],
    careerOpportunities: ["Food conservation technologist", "Quality specialist", "Processing line supervisor", "Food safety specialist"]
  }),
  technologyProgram({
    id: "bakery-products-technology",
    code: "70720101",
    name: "Technology of Processing Food Products (Bakery Products)",
    degree: "Master",
    departmentId: "food-technology-service",
    coordinator: "Qurbonov Murod Tashpulatovich",
    icon: Coffee,
    color: "orange",
    description: "Master specialization in bakery products, bread, pasta, confectionery, and food processing research.",
    curriculum: ["Bakery products technology", "Confectionery innovation", "Food processing research", "Ventilation and pneumatic transport", "Scientific-pedagogical work", "Master thesis preparation"],
    careerOpportunities: ["Bakery production technologist", "Food processing researcher", "Quality manager", "Production supervisor"]
  }),
  technologyProgram({
    id: "grain-products-technology",
    code: "70720101",
    name: "Technology of Processing Food Products (Grain Products)",
    degree: "Master",
    departmentId: "food-technology-service",
    coordinator: "Qurbonov Murod Tashpulatovich",
    icon: Coffee,
    color: "orange",
    description: "Master specialization in grain products, grain biochemistry, storage, and processing technology.",
    curriculum: ["Grain biochemistry", "Grain product technology", "Storage processes", "Food processing research", "Scientific-pedagogical work", "Master thesis preparation"],
    careerOpportunities: ["Grain products technologist", "Food researcher", "Quality control manager", "Production specialist"]
  }),
  technologyProgram({
    id: "inorganic-chemical-technology-bachelor",
    code: "60710100",
    name: "Chemical Technology of Inorganic Substances",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    description: "Bachelor track in inorganic substances chemical technology and industrial inorganic production.",
    curriculum: ["Inorganic chemistry", "Inorganic substances technology", "Chemical process equipment", "Material analysis", "Industrial safety", "Production practice"],
    careerOpportunities: ["Inorganic production technologist", "Laboratory specialist", "Quality engineer", "Process operator"]
  }),
  technologyProgram({
    id: "silicate-hard-melting-materials-technology",
    code: "60710100",
    name: "Silicate and Hard-to-Melt Nonmetallic Materials Technology",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: Factory,
    color: "gray",
    description: "Bachelor track in silicate and hard-to-melt nonmetallic materials.",
    curriculum: ["Silicate materials", "Refractory materials", "Material properties", "Thermal processes", "Quality control", "Production practice"],
    careerOpportunities: ["Materials technologist", "Refractory production specialist", "Quality engineer", "Laboratory assistant"]
  }),
  technologyProgram({
    id: "organic-substances-chemical-technology-bachelor",
    code: "60710100",
    name: "Chemical Technology of Organic Substances",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    description: "Bachelor track in organic substances, organic synthesis, and industrial organic chemical processes.",
    curriculum: ["Organic chemistry", "Organic substances technology", "Industrial synthesis", "Process equipment", "Quality control", "Production practice"],
    careerOpportunities: ["Organic production technologist", "Laboratory specialist", "Chemical process operator", "Quality engineer"]
  }),
  technologyProgram({
    id: "high-molecular-compounds-technology-bachelor",
    code: "60710100",
    name: "Chemical Technology of High Molecular Compounds",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    color: "purple",
    description: "Bachelor track in high molecular compounds and polymer-related chemical technology.",
    curriculum: ["Polymer chemistry", "High molecular compounds", "Polymer processing", "Material testing", "Industrial equipment", "Production practice"],
    careerOpportunities: ["Polymer technologist", "Materials specialist", "Laboratory technologist", "Quality engineer"]
  }),
  technologyProgram({
    id: "mineral-fertilizers-technology",
    code: "70710101",
    name: "Technology of Mineral Fertilizers",
    degree: "Master",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    description: "Master specialization in mineral fertilizers and inorganic chemical production technology.",
    curriculum: ["Mineral fertilizers technology", "Inorganic raw materials", "Industrial process optimization", "Research methodology", "Scientific-pedagogical work", "Master thesis research"],
    careerOpportunities: ["Mineral fertilizer technologist", "Research engineer", "Production specialist", "Quality manager"]
  }),
  technologyProgram({
    id: "agricultural-products-storage-processing-60811300",
    code: "60811300",
    name: "Technology of Storage and Processing of Agricultural Products",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Bachelor training in storage and processing technology for agricultural products.",
    curriculum: ["Storage technology", "Agricultural processing", "Raw material quality", "Food safety", "Production equipment", "Practical training"],
    careerOpportunities: ["Storage technologist", "Processing engineer", "Quality specialist", "Agricultural production supervisor"]
  }),
  technologyProgram({
    id: "fruit-viticulture",
    code: "60811800",
    name: "Fruit and Viticulture",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Bachelor training in fruit growing, viticulture, storage, and processing technologies.",
    curriculum: ["Fruit growing", "Viticulture", "Post-harvest storage", "Processing technology", "Quality assessment", "Field practice"],
    careerOpportunities: ["Fruit production specialist", "Viticulture technologist", "Storage specialist", "Processing supervisor"]
  }),
  technologyProgram({
    id: "food-technology-oil-products",
    code: "60720100",
    name: "Food Technology (Oil and Oil Products)",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Bachelor track in food technology focused on oil and oil products.",
    curriculum: ["Food technology", "Oil products", "Oil-fat processing", "Quality control", "Food safety", "Production practice"],
    careerOpportunities: ["Oil products technologist", "Food quality specialist", "Production supervisor", "Laboratory assistant"]
  }),
  technologyProgram({
    id: "fats-essential-oils-perfume-cosmetics-technology",
    code: "60720200",
    name: "Technology of Fats, Essential Oils and Perfume-Cosmetics",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Bachelor training in fats, essential oils, and perfume-cosmetics technology.",
    curriculum: ["Fats technology", "Essential oils", "Perfume-cosmetics raw materials", "Oil-fat processing", "Quality assessment", "Production practice"],
    careerOpportunities: ["Fats technologist", "Essential oils specialist", "Cosmetics production technologist", "Quality specialist"]
  }),
  technologyProgram({
    id: "oil-fat-products-processing-technology",
    code: "70720101",
    name: "Technology of Production and Processing of Food Products (Oil and Oil Products)",
    degree: "Master",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Master specialization in production and processing of oil and oil products.",
    curriculum: ["Oil products processing", "Food production research", "Raw material quality", "Process optimization", "Scientific-pedagogical work", "Master thesis preparation"],
    careerOpportunities: ["Oil-fat production researcher", "Senior technologist", "Quality manager", "Production engineer"]
  }),
  technologyProgram({
    id: "agricultural-products-storage-processing-master",
    code: "70720701",
    name: "Technology of Storage and Processing of Agricultural Products",
    degree: "Master",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Master specialization in storage and processing of agricultural products.",
    curriculum: ["Advanced storage technology", "Agricultural processing research", "Quality management", "Scientific-pedagogical work", "Production projects", "Master thesis research"],
    careerOpportunities: ["Storage technology researcher", "Processing manager", "Quality systems specialist", "Production engineer"]
  }),
  technologyProgram({
    id: "oil-gas-chemistry-technology-doctoral",
    code: "02.00.08",
    name: "Chemistry and Technology of Oil and Gas",
    degree: "PhD",
    departmentId: "oil-gas-refining-technology",
    coordinator: "Ochilov Abduraxim Abdurasulovich",
    description: "Doctoral research direction in chemistry and technology of oil and gas.",
    curriculum: ["Doctoral research", "Oil and gas chemistry", "Scientific publication", "Research methodology", "Dissertation work", "Scientific seminars"],
    careerOpportunities: ["Researcher", "University teacher", "Scientific council applicant", "Industrial research specialist"]
  }),
  technologyProgram({
    id: "oil-gas-field-development-5311900",
    code: "5311900",
    name: "Oil and Gas Field Development",
    departmentId: "oil-gas-engineering-upstream-downstream",
    coordinator: "Sharipov Qaxramon Qandiyorovich",
    icon: Pickaxe,
    color: "gray",
    description: "Legacy bachelor direction for start-up and development of oil and gas fields.",
    curriculum: ["Oil and gas field development", "Field exploitation", "Production equipment", "Field practice", "Industrial safety", "Resource operations"],
    careerOpportunities: ["Field development specialist", "Production technician", "Oilfield operator", "Field engineer assistant"]
  }),
  technologyProgram({
    id: "oil-gas-fields-exploitation",
    code: "5311900",
    name: "Start-up and Exploitation of Oil and Gas Fields",
    departmentId: "oil-gas-engineering-upstream-downstream",
    coordinator: "Sharipov Qaxramon Qandiyorovich",
    icon: Pickaxe,
    color: "gray",
    description: "Legacy bachelor direction in start-up and exploitation of oil and gas fields.",
    curriculum: ["Field start-up", "Oil and gas exploitation", "Production systems", "Field equipment", "Industrial safety", "Practical training"],
    careerOpportunities: ["Oilfield operator", "Production specialist", "Field equipment technician", "Operations assistant"]
  }),
  technologyProgram({
    id: "oil-gas-field-equipment-5a311902",
    code: "5A311902",
    name: "Machinery and Equipment of Oil and Gas Fields",
    degree: "Master",
    departmentId: "oil-gas-engineering-upstream-downstream",
    coordinator: "Sharipov Qaxramon Qandiyorovich",
    icon: Wrench,
    color: "gray",
    description: "Legacy master specialization in machinery and equipment of oil and gas fields.",
    curriculum: ["Oilfield equipment", "Machinery operation", "Maintenance planning", "Field safety", "Scientific-pedagogical work", "Master thesis research"],
    careerOpportunities: ["Oilfield equipment engineer", "Maintenance specialist", "Operations engineer", "Field machinery supervisor"]
  }),
  technologyProgram({
    id: "colloids-membranes-chemistry-doctoral",
    code: "02.00.11",
    name: "Chemistry of Colloids and Membranes",
    degree: "PhD",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    description: "Doctoral research direction in chemistry of colloids and membranes.",
    curriculum: ["Doctoral research", "Colloid chemistry", "Membrane chemistry", "Scientific publication", "Dissertation work", "Scientific seminars"],
    careerOpportunities: ["Researcher", "University teacher", "Laboratory researcher", "Scientific council applicant"]
  }),
  technologyProgram({
    id: "inorganic-substances-materials-doctoral",
    code: "02.00.13",
    name: "Technology of Inorganic Substances and Materials Based on Them",
    degree: "PhD",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    description: "Doctoral research direction in inorganic substances and materials based on them.",
    curriculum: ["Doctoral research", "Inorganic materials", "Research methodology", "Scientific publication", "Dissertation work", "Scientific seminars"],
    careerOpportunities: ["Researcher", "University teacher", "Industrial research specialist", "Scientific council applicant"]
  }),
  technologyProgram({
    id: "organic-substances-materials-doctoral",
    code: "02.00.14",
    name: "Technology of Organic Substances and Materials Based on Them",
    degree: "PhD",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    description: "Doctoral research direction in organic substances and materials based on them.",
    curriculum: ["Doctoral research", "Organic materials", "Research methodology", "Scientific publication", "Dissertation work", "Scientific seminars"],
    careerOpportunities: ["Researcher", "University teacher", "Organic materials specialist", "Scientific council applicant"]
  }),
  technologyProgram({
    id: "food-industry-chemical-technology-doctoral",
    code: "02.00.16",
    name: "Chemical Technology, Processes and Devices of Food Industry",
    degree: "PhD",
    departmentId: "chemical-technology",
    coordinator: "Axmedov Voxid Nizomovich",
    icon: Coffee,
    color: "orange",
    description: "Doctoral research direction in chemical technology, processes, and devices of the food industry.",
    curriculum: ["Doctoral research", "Food industry processes", "Chemical technology devices", "Scientific publication", "Dissertation work", "Scientific seminars"],
    careerOpportunities: ["Researcher", "University teacher", "Food process researcher", "Scientific council applicant"]
  }),
  technologyProgram({
    id: "agricultural-food-products-biotechnology-doctoral",
    code: "02.00.17",
    name: "Technologies and Biotechnologies for Processing, Storage and Processing of Agricultural and Food Products",
    degree: "PhD",
    departmentId: "agricultural-products-storage-oil-fat-technology",
    coordinator: "Majidova Nargiza Kaxramonovna",
    icon: Leaf,
    color: "green",
    description: "Doctoral research direction in technologies and biotechnologies for agricultural and food products.",
    curriculum: ["Doctoral research", "Agricultural product technologies", "Food product biotechnology", "Scientific publication", "Dissertation work", "Scientific seminars"],
    careerOpportunities: ["Researcher", "University teacher", "Food biotechnology researcher", "Scientific council applicant"]
  })
];
