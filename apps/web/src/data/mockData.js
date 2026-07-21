export const faqData = [
  {
    id: 1,
    question: "When was Bukhara State Technical University (BSTU) established?",
    answer: "BSTU was officially established in January 2025 by Presidential Resolution No. PP-22, uniting the Bukhara Engineering-Technological Institute and the Bukhara Institute of Natural Resources Management."
  },
  {
    id: 2,
    question: "What main faculties are available at BSTU?",
    answer: "The university is structured into 4 faculties and 24 departments, including Chemical & Food Technologies, Oil & Gas Technology, Engineering & Construction, Power Engineering & ICT, Textile & Light Industry, and Natural Resources Management."
  },
  {
    id: 3,
    question: "Does BSTU offer joint or double-degree educational programs?",
    answer: "Yes, BSTU active partnerships offer double-degree and joint educational programs with leading technical universities in Latvia, Malaysia, Belarus, and Russia."
  },
  {
    id: 4,
    question: "What is the Advanced Engineering School at BSTU?",
    answer: "Established in cooperation with Uzbekneftegaz JSC, the Advanced Engineering School aims to provide energy sector students with hands-on, high-tech industrial training and direct career pathways."
  },
  {
    id: 5,
    question: "Where is the main university campus located?",
    answer: "The university campus is located at 15 Q. Murtazoyev Street, Bukhara city, Uzbekistan."
  }
];

export const facultiesData = [
  {
    id: "faculty-of-engineering",
    name: "Faculty of Engineering",
    slug: "faculty-of-engineering",
    shortName: "Engineering",
    image: "/assets/img/values-3.png",
    about: "The Faculty of Engineering at Bukhara State Technical University stands at the forefront of technical education and industrial innovation in Uzbekistan. The faculty is committed to developing engineering talent capable of tackling complex design, construction, mechanical, and architectural challenges. By blending rigorous theoretical coursework with hands-on laboratory experiments and real-world industrial projects, we ensure our graduates are fully prepared to lead the engineering and manufacturing sectors.",
    management: {
      dean: "Dr. Jasur A. Nematov",
      title: "Dean of the Faculty of Engineering, Associate Professor",
      email: "engineering-dean@bstu.uz",
      phone: "+998 65 224 64 35 (Ext. 101)",
      officeHours: "Monday - Friday, 2:00 PM - 4:00 PM"
    },
    departments: [
      "Department of Electrical & Power Engineering",
      "Department of Architecture",
      "Department of Civil Engineering",
      "Department of Light Industry Engineering and Design",
      "Department of Mechanics and Engineering Graphics",
      "Department of Technological Machines and Equipment",
      "Department of Textile Materials Science"
    ],
    programs: [
      "Power Engineering (Electrical & Thermal Power) [B.Sc. / M.Sc. / PhD]",
      "Architecture & Urban Design [B.Sc. / M.Sc.]",
      "Civil & Infrastructure Engineering [B.Sc. / M.Sc.]",
      "Light Industry Technology & Fashion Design [B.Sc. / M.Sc.]",
      "Mechanical Engineering & Production Equipment [B.Sc. / M.Sc.]"
    ]
  },
  {
    id: "faculty-of-technology",
    name: "Faculty of Technology",
    slug: "faculty-of-technology",
    shortName: "Technology",
    image: "/assets/img/faculties/dean_adizov.jpg",
    about: "The Faculty of Technology maintains continuous cooperation between its specialized departments and industrial enterprises. Department branches operate inside partner enterprises, where students reinforce theoretical knowledge through practical experience. Industry specialists participate directly in the educational process, assess graduating students, sign internship contracts, and create conditions for employment of students with strong knowledge and potential. The faculty is headed by Associate Professor Rashid Tokhtayevich Adizov, PhD in Technical Sciences.",
    management: {
      dean: "Adizov Rashid Tokhtayevich",
      title: "Dean of the Faculty of Technology, Associate Professor, PhD",
      email: "adizov.rashid@mail.ru",
      phone: "+998 93 479 77 65",
      officeHours: "Every day 14:00-16:00 (except Monday and Saturday)"
    },
    leadership: [
      {
        name: "Adizov Rashid Tokhtayevich",
        role: "Dean of the Faculty of Technology",
        officeHours: "Every day 14:00-16:00 (except Monday and Saturday)",
        phone: "+998 93 479 77 65",
        email: "adizov.rashid@mail.ru",
        image: "/assets/img/faculties/dean_adizov.jpg"
      },
      {
        name: "Safarov Jasur Alijon o'g'li",
        role: "Deputy Dean for Academic Affairs",
        officeHours: "Every day 14:00-16:00",
        phone: "+998 93 688 56 88",
        email: "jasur.safarov1993@mail.ru",
        image: "/assets/img/faculties/deputy_academic_safarov.jpg"
      },
      {
        name: "Bozorov Dilmurod Xolmurodovich",
        role: "Deputy Dean for Youth Affairs",
        officeHours: "Every day 14:00-16:00",
        phone: "+998 90 744 47 97",
        email: "d.bozorov_78@mail.ru",
        image: "/assets/img/faculties/deputy_youth_bozorov.jpg"
      }
    ],
    departments: [
      "Department of Oil and Gas Refining Technology",
      "Department of Food Technology and Service",
      "Department of Chemical Technology",
      "Department of Agricultural Products Storage & Oil-Fat Technology",
      "Department of Oil and Gas Engineering (Upstream & Downstream)",
      "Department of Metrology, Standardization, and Quality Control"
    ],
    programs: [
      "60710100 - Chemical Engineering",
      "60710200 - Biotechnology",
      "60710300 - Printing and Packaging Engineering",
      "60710800 - Metrology and Standardization",
      "60810700 - Storage and Processing Technology of Agricultural Products",
      "60811000 - Horticulture and Viticulture",
      "60720100 - Food Technology",
      "60720500 - Gas Processing Technology",
      "60720600 - Oil and Oil-Gas Processing Technology",
      "60720900 - Geology, Exploration and Prospecting of Mineral Deposits",
      "60721100 - Oil and Gas Engineering",
      "70710103 - Chemical Technology of High-Molecular Compounds (Polymer Production)",
      "70720101 - Technology of Food Production and Processing (Grain Storage and Processing Technology)",
      "70720101 - Technology of Food Production and Processing (Oil Processing Technology)",
      "70710101 - Chemical Technology (Inorganic Substances Chemical Technology)",
      "70710101 - Chemical Technology (Silicate and Refractory Nonmetallic Materials Technology)",
      "70710101 - Chemical Technology (Organic Substances Chemical Technology)"
    ]
  },
  {
    id: "faculty-of-natural-resources-management",
    name: "Faculty of Natural Resources Management",
    slug: "faculty-of-natural-resources-management",
    shortName: "Natural Resources",
    image: "/assets/img/values-2.png",
    about: "The Faculty of Natural Resources Management addresses the vital challenges of agricultural water management, irrigation, land cadastre, environmental conservation, and vehicle engineering. We aim to train specialists who balance technological development with environmental sustainability, particularly in Central Asia's arid climates.",
    management: {
      dean: "Dr. Alisher M. Kurbanov",
      title: "Dean of Faculty of Natural Resources Management, Associate Professor",
      email: "resources-dean@bstu.uz",
      phone: "+998 65 224 64 35 (Ext. 103)",
      officeHours: "Monday & Wednesday, 3:00 PM - 5:00 PM"
    },
    departments: [
      "Department of Irrigation and Melioration",
      "Department of Hydrotechnical Structures and Pump Stations",
      "Department of Agricultural and Water Resources Engineering-Technologies",
      "Department of Land Resources Management and State Land Cadastres",
      "Department of Industrial Ecology and Hydrogeology",
      "Department of Vehicle Engineering (Automotive & Transport Systems)"
    ],
    programs: [
      "Water Resource Management & Melioration [B.Sc. / M.Sc.]",
      "Hydrotechnical Engineering & Pump Stations [B.Sc. / M.Sc.]",
      "Land Surveying, Cadastre & GIS Mapping [B.Sc. / M.Sc.]",
      "Environmental Protection & Industrial Ecology [B.Sc. / M.Sc. / PhD]",
      "Automotive Engineering & Transportation Logistics [B.Sc. / M.Sc.]"
    ]
  },
  {
    id: "faculty-of-service-and-digitalization",
    name: "Faculty of Service and Digitalization",
    slug: "faculty-of-service-and-digitalization",
    shortName: "Service & Digitalization",
    image: "/assets/img/alt-features.png",
    about: "The Faculty of Service and Digitalization focuses on software engineering, industrial automation, artificial intelligence, and applied economics. In response to the global digital transformation, our courses are designed to prepare innovators who can automate industries, build AI-driven solutions, and manage digital enterprises in Uzbekistan's growing tech ecosystem.",
    management: {
      dean: "Dr. Sherbek N. Xayitov",
      title: "Dean of Faculty of Service and Digitalization, PhD in Technical Sciences",
      email: "digitalization-dean@bstu.uz",
      phone: "+998 65 224 64 35 (Ext. 104)",
      officeHours: "Wednesday & Friday, 2:00 PM - 4:00 PM"
    },
    departments: [
      "Department of Technological Processes & Production Automation",
      "Department of Information and Communication Technologies",
      "Department of Economics and Management",
      "Department of Artificial Intelligence and Digitalization",
      "Department of Social Sciences and Physical Culture",
      "Department of Exact Sciences",
      "Department of Uzbek and Foreign Languages"
    ],
    programs: [
      "Software Engineering & Computer Science [B.Sc. / M.Sc.]",
      "Industrial Automation & Robotics [B.Sc. / M.Sc.]",
      "Artificial Intelligence & Big Data Analytics [B.Sc. / M.Sc.]",
      "Economics, Business Administration & IT Management [B.Sc. / M.Sc.]",
      "Information Systems Security & Cybersecurity [B.Sc. / M.Sc.]"
    ]
  }
];
