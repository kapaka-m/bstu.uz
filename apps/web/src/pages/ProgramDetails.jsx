import React, { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import { 
  GraduationCap, Clock, BookOpen, Briefcase, 
  HelpCircle, ArrowLeft, Mail, Phone, MapPin, ShieldCheck
} from "lucide-react";
import PageHeader from "../components/PageHeader";
import { programsData } from "../data/programsData";
import { facultiesData } from "../data/mockData";
import { departmentsData } from "../data/departmentsData";
import { technologyDepartments } from "../data/facultyTechnology";
import { useLanguage } from "../context/LanguageContext";
import { footerService } from "../services/footerService";

const keyMap = {
  "computer-science": "computerScience",
  "engineering": "engineering",
  "architecture": "architecture",
  "economics": "economics",
  "chemical-technology": "chemicalTechnology",
  "power-engineering": "powerEngineering",
  "construction": "construction",
  "metallurgy": "metallurgy",
  "ecology": "ecology",
  "information-technology": "informationTechnology",
  "food-technology": "foodTechnology",
  "food-technology-60720100": "foodTechnology",
  "textile-engineering": "textileEngineering"
};

export default function ProgramDetails() {
  const { id } = useParams();
  const { t, language } = useLanguage();
  const [footerContact, setFooterContact] = useState(null);
  
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;

    footerService
      .getPublicFooter()
      .then((data) => {
        if (active) setFooterContact(data);
      })
      .catch(() => {
        if (active) setFooterContact(null);
      });

    return () => {
      active = false;
    };
  }, []);

    const idAliases = {
      "food-technology-60720100": "food-technology",
      "oil-and-gas-engineering-upstream-downstream": "oil-gas-engineering"
    };

    const resolvedId = idAliases[id] || id;
    const program = programsData.find((p) => p.id === resolvedId);

  if (!program) {
    return (
      <div className="pt-20 min-h-screen bg-primary-light flex flex-col items-center justify-center text-center p-8">
        <HelpCircle className="w-16 h-16 text-red-500 mb-4 animate-bounce" />
        <h1 className="text-3xl font-extrabold text-navy mb-2">{t("common.notFound", "Program Not Found")}</h1>
        <p className="text-gray-500 max-w-md mb-8">
          {t("common.notFoundDesc", "The academic program you are looking for does not exist or has been relocated.")}
        </p>
        <Link
          to="/programs"
          className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md"
        >
          <ArrowLeft className={`w-4 h-4 transition-transform ${language === 'ar' ? 'rotate-180' : ''}`} /> {t("common.backToPrograms", "Back to Programs")}
        </Link>
      </div>
    );
  }

  const Icon = program.icon;
  const key = keyMap[program.id] || program.id;
  const pName = t(`programs.${program.id}.name`, t(`home.programs.list.${key}.name`, program.name));
  
  const degreeKey = program.degree.toLowerCase();
  const pDegree = t(`home.programs.degrees.${degreeKey}`, program.degree);
  
  const durationKey = program.duration.includes("4") ? "years4" : program.duration.includes("5") ? "years5" : "years2";
  const pDuration = t(`home.programs.durations.${durationKey}`, program.duration);

  const parentFaculty = facultiesData.find((f) => f.id === program.facultyId);
  const technologyDepartment = technologyDepartments.find((department) => department.slug === program.departmentId);
  const parentDept = departmentsData[program.departmentId] || technologyDepartment;

  const facultyName = parentFaculty 
    ? t(`faculties.${parentFaculty.id}.name`, parentFaculty.name)
    : "";
  const departmentName = parentDept
    ? t(`departments.${program.departmentId}.name`, parentDept.name)
    : "";
  const pCoordinator = t(`programs.${program.id}.coordinator`, program.coordinator);

  return (
    <div className="pt-20 bg-white">
      <PageHeader 
        title={pName} 
        breadcrumbs={[
          { label: t("common.academicPrograms", "Programs"), path: "/programs" },
          { label: pName }
        ]} 
      />

      <div className="py-16 md:py-24 bg-white">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            {/* Left Column (Primary Content) */}
            <div className="lg:col-span-8 flex flex-col gap-10">
              
              {/* Introduction Card */}
              <div className="bg-primary-light/50 border border-gray-100 p-8 rounded-3xl text-start">
                <div className="flex items-center gap-4 mb-6">
                  <div className="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center shrink-0">
                    <Icon className="w-6 h-6" />
                  </div>
                  <div>
                    <span className="text-[10px] font-extrabold uppercase tracking-wider text-primary bg-primary/5 px-3 py-1 rounded-full">
                      {pDegree}
                    </span>
                    <h2 className="text-2xl font-extrabold text-navy mt-1">{pName}</h2>
                  </div>
                </div>
                <p className="text-gray-600 text-sm md:text-base leading-relaxed whitespace-pre-line">
                  {t(`programs.${program.id}.detailedDescription`, program.detailedDescription)}
                </p>
              </div>

              {/* Curriculum Block */}
              <div className="border border-gray-100 rounded-3xl p-8 shadow-sm text-start">
                <h3 className="text-xl font-extrabold text-navy mb-6 flex items-center gap-2">
                  <BookOpen className="w-5 h-5 text-primary" /> {t("common.courseCurriculum", "Core Curriculum & Subjects")}
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {program.curriculum.map((subject, idx) => (
                    <div key={idx} className="flex items-center gap-3 p-3.5 bg-gray-50 border border-gray-100 rounded-2xl hover:border-primary/20 transition-all duration-300">
                      <div className="w-2.5 h-2.5 rounded-full bg-primary shrink-0" />
                      <span className="text-sm font-bold text-gray-700">
                        {t(`programs.${program.id}.curriculum.${idx}`, subject)}
                      </span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Admission Requirements */}
              <div className="border border-gray-100 rounded-3xl p-8 shadow-sm text-start">
                <h3 className="text-xl font-extrabold text-navy mb-6 flex items-center gap-2">
                  <GraduationCap className="w-5 h-5 text-primary" /> {t("common.admissionRequirements", "Admission Requirements")}
                </h3>
                <ul className="space-y-4">
                  {program.requirements.map((req, idx) => (
                    <li key={idx} className="flex gap-3 items-start">
                      <div className="w-6 h-6 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0 mt-0.5 border border-green-100">
                        ✓
                      </div>
                      <span className="text-sm font-semibold text-gray-600 leading-relaxed">
                        {t(`programs.${program.id}.requirements.${idx}`, req)}
                      </span>
                    </li>
                  ))}
                </ul>
              </div>

              {/* Professional Accreditations */}
              {program.accreditations && program.accreditations.length > 0 && (
                <div className="border border-gray-100 rounded-3xl p-8 shadow-sm text-start">
                  <h3 className="text-xl font-extrabold text-navy mb-6 flex items-center gap-2">
                    <ShieldCheck className="w-5 h-5 text-primary" /> {t("common.accreditations", "Accreditations & Quality Certifications")}
                  </h3>
                  <ul className="space-y-4">
                    {program.accreditations.map((acc, idx) => (
                      <li key={idx} className="flex gap-3 items-start">
                        <div className="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5 border border-blue-100">
                          ✓
                        </div>
                        <span className="text-sm font-semibold text-gray-600 leading-relaxed">
                          {t(`programs.${program.id}.accreditations.${idx}`, acc)}
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              )}

            </div>

            {/* Right Column (Sidebar) */}
            <div className="lg:col-span-4 flex flex-col gap-8 text-start">

              {/* Apply Now CTA */}
              <div className="bg-linear-to-br from-primary to-primary-hover text-white rounded-3xl p-8 shadow-lg shadow-primary/20 flex flex-col items-start gap-4">
                <GraduationCap className="w-8 h-8 text-white/80" />
                <div>
                  <h3 className="text-lg font-extrabold mb-1">{t("common.applyNow", "Apply Now")}</h3>
                  <p className="text-xs text-white/80 leading-relaxed">{t("common.applyDesc", "Ready to join? Submit your application for the next intake and begin your journey at BSTU.")}</p>
                </div>
                <Link
                  to="/apply"
                  className="w-full text-center bg-white text-primary font-extrabold text-sm px-6 py-3 rounded-xl hover:bg-primary-light transition-colors duration-200 shadow-sm"
                >
                  {t("common.applyNow", "Apply Now")} →
                </Link>
              </div>
              
              {/* Program Quick Facts */}
              <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl shadow-sm">
                <h3 className="text-lg font-extrabold text-navy mb-6 border-b border-gray-200/50 pb-3">
                  {t("common.quickFacts", "Quick Facts")}
                </h3>
                <div className="space-y-4">
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{t("common.degreeLevel", "Degree Level")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs">
                      {pDegree}
                    </span>
                  </div>
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{t("common.duration", "Duration")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs flex items-center gap-1">
                      <Clock className="w-3.5 h-3.5 text-primary shrink-0" />
                      {pDuration}
                    </span>
                  </div>
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{t("common.languageOfInstruction", "Language")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs">
                      {t("common.programLanguages", "English / Uzbek")}
                    </span>
                  </div>
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{t("common.intakePeriod", "Intake Period")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs">
                      {t("common.intakeDate", "September 2026")}
                    </span>
                  </div>
                  <div className="flex flex-col gap-1 text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-400 text-[10px] uppercase tracking-wider font-extrabold">{t("common.parentFaculty", "Parent Faculty")}</span>
                    {parentFaculty ? (
                      <Link 
                        to={`/faculty/${parentFaculty.id}`}
                        className="text-primary hover:text-primary-hover font-bold hover:underline text-xs"
                      >
                        {facultyName}
                      </Link>
                    ) : (
                      <span className="text-navy font-bold text-xs">—</span>
                    )}
                  </div>
                  <div className="flex flex-col gap-1 text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-400 text-[10px] uppercase tracking-wider font-extrabold">{t("common.parentDepartment", "Parent Department")}</span>
                    {parentDept ? (
                      <Link 
                        to={`/department/${program.departmentId}`}
                        className="text-primary hover:text-primary-hover font-bold hover:underline text-xs"
                      >
                        {departmentName}
                      </Link>
                    ) : (
                      <span className="text-navy font-bold text-xs">—</span>
                    )}
                  </div>
                  <div className="flex flex-col gap-1 text-sm font-semibold">
                    <span className="text-gray-400 text-[10px] uppercase tracking-wider font-extrabold">{t("common.programCoordinator", "Program Coordinator")}</span>
                    <span className="text-navy font-bold text-xs">{pCoordinator}</span>
                  </div>
                </div>
              </div>

              {/* Career Opportunities */}
              <div className="border border-gray-100 p-8 rounded-3xl shadow-sm">
                <h3 className="text-lg font-extrabold text-navy mb-6 border-b border-gray-200/50 pb-3 flex items-center gap-2">
                  <Briefcase className="w-5 h-5 text-primary" /> {t("common.careerOpportunities", "Career Paths")}
                </h3>
                <div className="flex flex-col gap-3">
                  {program.careerOpportunities.map((opportunity, idx) => (
                    <div key={idx} className="flex gap-2.5 items-start p-3 bg-gray-50/50 border border-gray-100 rounded-xl hover:bg-white transition-colors duration-300">
                      <div className="w-5 h-5 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                        →
                      </div>
                      <span className="text-xs font-bold text-navy">
                        {t(`programs.${program.id}.careerOpportunities.${idx}`, opportunity)}
                      </span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Sidebar Contact Info */}
              <div className="border border-gray-100 p-8 rounded-3xl shadow-sm flex flex-col gap-6 bg-navy text-white">
                <div>
                  <h4 className="text-base font-extrabold mb-1">{t("common.facultyHelpdesk", "Admissions Help")}</h4>
                  <p className="text-[11px] text-white/70 leading-normal">
                    {t("common.facultyHelpdeskDesc", "Have questions about this program or the admissions process? Reach out to our Registrar Office.")}
                  </p>
                </div>
                <div className="space-y-4 text-xs font-bold border-t border-white/10 pt-4 text-white/90">
                  {footerContact?.phone && (
                    <div className="flex items-center gap-3">
                      <Phone className="w-4 h-4 text-white/70 shrink-0" />
                      <a
                        href={`tel:${footerContact.phone.replace(/[^\d+]/g, "")}`}
                        className="hover:underline"
                      >
                        {footerContact.phone}
                      </a>
                    </div>
                  )}
                  {footerContact?.email && (
                    <div className="flex items-center gap-3">
                      <Mail className="w-4 h-4 text-white/70 shrink-0" />
                      <a
                        href={`mailto:${footerContact.email}`}
                        className="hover:underline"
                      >
                        {footerContact.email}
                      </a>
                    </div>
                  )}
                  {(footerContact?.address_line_1 ||
                    footerContact?.address_line_2) && (
                    <div className="flex items-center gap-3">
                      <MapPin className="w-4 h-4 text-white/70 shrink-0" />
                      <span>
                        {[footerContact.address_line_1, footerContact.address_line_2]
                          .filter(Boolean)
                          .join(", ")}
                      </span>
                    </div>
                  )}
                </div>
              </div>

            </div>

          </div>
        </div>
      </div>
    </div>
  );
}
