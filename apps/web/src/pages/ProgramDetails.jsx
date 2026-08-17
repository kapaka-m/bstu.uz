import React, { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import { 
  GraduationCap, Clock, BookOpen, Briefcase, 
  HelpCircle, ArrowLeft, Mail, Phone, MapPin, ShieldCheck, Users, Hash, WalletCards, Languages
} from "lucide-react";
import PageHeader from "../components/PageHeader";
import { useLanguage } from "../context/LanguageContext";
import { footerService } from "../services/footerService";
import { programPageCmsService } from "../services/programPageCmsService";
import { programService } from "../services/programService";

const splitLines = (value) =>
  String(value || "")
    .split(/\n+/)
    .map((item) => item.trim())
    .filter(Boolean);

const degreeLabelKey = (degree) => String(degree || "").toLowerCase();

const formatMoney = (amount, currency) => {
  const numeric = Number(amount);
  if (!Number.isFinite(numeric) || numeric <= 0) return "";

  return `${numeric.toLocaleString("en-US", {
    maximumFractionDigits: numeric % 1 === 0 ? 0 : 2,
  })} ${currency || ""}`.trim();
};

const localizedOption = (value, language, maps) => {
  const key = String(value || "").toLowerCase();
  const locale = language || "en";

  return maps[key]?.[locale] || maps[key]?.en || value || "";
};

const localizedOptionList = (value, language, maps) =>
  String(value || "")
    .split(/[,;/|]+/)
    .map((item) => item.trim())
    .filter(Boolean)
    .map((item) => localizedOption(item, language, maps))
    .filter(Boolean)
    .join(", ");

const defaultLabels = {
  back_to_programs: "Back to Programs",
  course_curriculum: "Course Curriculum",
  courses: "Courses",
  year: "Year",
  semester: "Semester",
  admission_requirements: "Admission Requirements",
  documents: "Required Documents",
  quick_facts: "Quick Facts",
  program_code: "Program code",
  degree_level: "Degree Level",
  duration: "Duration",
  years: "years",
  language_of_instruction: "Language of Instruction",
  study_mode: "Study mode",
  tuition_fee: "Tuition Fee",
  intake_period: "Intake Period",
  intake_date: "September Intake",
  parent_faculty: "Parent Faculty",
  parent_department: "Parent Department",
  program_coordinator: "Program Coordinator",
  academic_staff: "Academic Staff",
  faculty_helpdesk: "Faculty Helpdesk",
  faculty_helpdesk_description: "Contact the university for admission, program, and documentation support.",
  contact_university: "Contact University",
  career_opportunities: "Career Opportunities",
  apply_now: "Apply Now",
  apply_description: "Start your application and submit the required documents through the admission system.",
  not_found_title: "Program Not Found",
  not_found_description: "The requested program page could not be found.",
  no_details: "No additional program details have been published yet.",
  degree_bachelor: "Bachelor",
  degree_master: "Master",
  degree_phd: "PhD",
  mode_full_time: "Full-time",
  mode_part_time: "Part-time",
  mode_evening: "Evening",
  mode_distance: "Distance",
  language_english: "English",
  language_uzbek: "Uzbek",
  language_russian: "Russian",
  language_arabic: "Arabic",
};

export default function ProgramDetails() {
  const { id } = useParams();
  const { language, isRtl } = useLanguage();
  const [program, setProgram] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [footerContact, setFooterContact] = useState(null);
  const [pageLabels, setPageLabels] = useState(defaultLabels);
  
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;
    setLoading(true);
    setError("");

    const idAliases = {
      "food-technology": "food-technology-60720100",
      "oil-gas-engineering": "oil-and-gas-business-60721100",
    };

    programService
      .getProgram(idAliases[id] || id)
      .then((data) => {
        if (!active) return;
        setProgram(data || null);
      })
      .catch(() => {
        if (!active) return;
        setProgram(null);
        setError(pageLabels.not_found_description || defaultLabels.not_found_description);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [id, language, pageLabels.not_found_description]);

  useEffect(() => {
    let active = true;

    programPageCmsService
      .get(language)
      .then((payload) => {
        if (!active) return;
        setPageLabels({ ...defaultLabels, ...(payload?.labels || {}) });
      })
      .catch(() => {
        if (active) setPageLabels(defaultLabels);
      });

    return () => {
      active = false;
    };
  }, [language]);

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

  if (loading) {
    return null;
  }

  if (!program || error) {
    return (
      <div className="pt-20 min-h-screen bg-primary-light flex flex-col items-center justify-center text-center p-8">
        <HelpCircle className="w-16 h-16 text-red-500 mb-4 animate-bounce" />
        <h1 className="text-3xl font-extrabold text-navy mb-2">{pageLabels.not_found_title}</h1>
        <p className="text-gray-500 max-w-md mb-8">
          {error || pageLabels.not_found_description}
        </p>
        <Link
          to="/programs"
          className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md"
        >
          <ArrowLeft className={`w-4 h-4 transition-transform ${isRtl ? 'rotate-180' : ''}`} /> {pageLabels.back_to_programs}
        </Link>
      </div>
    );
  }

  const Icon = GraduationCap;
  const label = (key, fallback = "") => pageLabels[key] || fallback || key;
  const pName = program.name || "";
  const pCode = program.display_code || program.official_code || program.code || "";
  
  const degreeKey = degreeLabelKey(program.degree);
  const pDegree = label(`degree_${degreeKey}`, program.degree);
  
  const durationYears = Number(program.duration_years || 0);
  const durationFallback = durationYears
    ? `${durationYears} ${label("years", durationYears === 1 ? "year" : "years")}`
    : "";
  const pDuration =
    String(program.duration || "").trim() ||
    durationFallback ||
    "—";
  const pLanguage = localizedOptionList(program.language_of_study || program.language, language, {
    en: { [language]: label("language_english") },
    english: { [language]: label("language_english") },
    uz: { [language]: label("language_uzbek") },
    uzbek: { [language]: label("language_uzbek") },
    ru: { [language]: label("language_russian") },
    russian: { [language]: label("language_russian") },
    ar: { [language]: label("language_arabic") },
    arabic: { [language]: label("language_arabic") },
  });
  const pStudyMode = localizedOptionList(program.study_mode, language, {
    full_time: { [language]: label("mode_full_time") },
    part_time: { [language]: label("mode_part_time") },
    evening: { [language]: label("mode_evening") },
    distance: { [language]: label("mode_distance") },
  });
  const pTuition = formatMoney(program.tuition_fee, program.currency);
  const directionArrow = isRtl ? "←" : "→";
  const curriculum = splitLines(program.curriculum_summary);
  const requirements = splitLines(program.requirements);
  const careerOpportunities = splitLines(program.career_opportunities);
  const documents = splitLines(program.documents);
  const facultyName = program.faculty?.name || "";
  const departmentName = program.department?.name || "";
  const pCoordinator = program.department?.head_name || program.department?.name || "";
  const pCoordinatorRoute = program.department?.head_profile_slug ? `/profile/${program.department.head_profile_slug}` : "";
  const programStaff = (program.staff || []).map((member) => ({
    slug: member.slug,
    route: member.slug ? `/profile/${member.slug}` : "",
    name: member.full_name || member.name || "",
    position: member.position || "",
  })).filter((member) => member.name);
  const courses = program.courses || [];

  return (
    <div className="pt-20 bg-white">
      <PageHeader 
        title={pName}
      />

      <div className="py-16 md:py-24 bg-white">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            {/* Left Column (Primary Content) */}
            <div className="lg:col-span-8 flex flex-col gap-10">
              
              {/* Introduction Card */}
              <div className="bg-primary-light/50 border border-gray-100 p-5 sm:p-8 rounded-3xl text-start">
                <div className="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center">
                  <div className="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center shrink-0">
                    <Icon className="w-6 h-6" />
                  </div>
                  <div className="min-w-0">
                    <div className="flex flex-wrap gap-2">
                      {pDegree && (
                        <span className="text-[10px] font-extrabold uppercase tracking-wider text-primary bg-primary/5 px-3 py-1 rounded-full">
                          {pDegree}
                        </span>
                      )}
                      {pCode && (
                        <span className="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-100">
                          <Hash className="w-3 h-3 text-primary" />
                          {pCode}
                        </span>
                      )}
                    </div>
                    <h2 className="text-2xl font-extrabold text-navy mt-2 break-words">{pName}</h2>
                  </div>
                </div>
                <p className="text-gray-600 text-sm md:text-base leading-relaxed whitespace-pre-line">
                  {program.description}
                </p>
              </div>

              {/* Curriculum Block */}
              {curriculum.length > 0 && (
                <div className="border border-gray-100 rounded-3xl p-5 sm:p-8 shadow-sm text-start">
                  <h3 className="text-xl font-extrabold text-navy mb-6 flex items-center gap-2">
                    <BookOpen className="w-5 h-5 text-primary" /> {label("course_curriculum")}
                  </h3>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {curriculum.map((subject, idx) => (
                      <div key={idx} className="flex items-center gap-3 p-3.5 bg-gray-50 border border-gray-100 rounded-2xl hover:border-primary/20 transition-all duration-300">
                        <div className="w-2.5 h-2.5 rounded-full bg-primary shrink-0" />
                        <span className="text-sm font-bold text-gray-700">
                          {subject}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {courses.length > 0 && (
                <div className="border border-gray-100 rounded-3xl p-5 sm:p-8 shadow-sm text-start">
                  <h3 className="text-xl font-extrabold text-navy mb-6 flex items-center gap-2">
                    <BookOpen className="w-5 h-5 text-primary" /> {label("courses")}
                  </h3>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {courses.map((course) => (
                      <div key={course.slug || course.id} className="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p className="text-sm font-extrabold text-navy">{course.name || course.title}</p>
                        {(course.code || course.pivot?.semester || course.pivot?.year) && (
                          <p className="mt-1 text-[11px] font-bold text-gray-400">
                            {[course.code, course.pivot?.year ? `${label("year")} ${course.pivot.year}` : "", course.pivot?.semester ? `${label("semester")} ${course.pivot.semester}` : ""]
                              .filter(Boolean)
                              .join(" / ")}
                          </p>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Admission Requirements */}
              {requirements.length > 0 && (
                <div className="border border-gray-100 rounded-3xl p-5 sm:p-8 shadow-sm text-start">
                  <h3 className="text-xl font-extrabold text-navy mb-6 flex items-center gap-2">
                    <GraduationCap className="w-5 h-5 text-primary" /> {label("admission_requirements")}
                  </h3>
                  <ul className="space-y-4">
                    {requirements.map((req, idx) => (
                      <li key={idx} className="flex gap-3 items-start">
                        <div className="w-6 h-6 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0 mt-0.5 border border-green-100">
                          ✓
                        </div>
                        <span className="text-sm font-semibold text-gray-600 leading-relaxed">
                          {req}
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              )}

              {curriculum.length === 0 && requirements.length === 0 && courses.length === 0 && (
                <div className="rounded-3xl border border-gray-100 bg-white p-8 text-center text-sm font-bold text-gray-400 shadow-sm">
                  {label("no_details")}
                </div>
              )}

              {/* Professional Accreditations */}
              {documents.length > 0 && (
                <div className="border border-gray-100 rounded-3xl p-5 sm:p-8 shadow-sm text-start">
                  <h3 className="text-xl font-extrabold text-navy mb-6 flex items-center gap-2">
                    <ShieldCheck className="w-5 h-5 text-primary" /> {label("documents")}
                  </h3>
                  <ul className="space-y-4">
                    {documents.map((acc, idx) => (
                      <li key={idx} className="flex gap-3 items-start">
                        <div className="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5 border border-blue-100">
                          ✓
                        </div>
                        <span className="text-sm font-semibold text-gray-600 leading-relaxed">
                          {acc}
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
                  <h3 className="text-lg font-extrabold mb-1">{label("apply_now")}</h3>
                  <p className="text-xs text-white/80 leading-relaxed">{label("apply_description")}</p>
                </div>
                <Link
                  to="/apply"
                  className="w-full text-center bg-white text-primary font-extrabold text-sm px-6 py-3 rounded-xl hover:bg-primary-light transition-colors duration-200 shadow-sm"
                >
                  {label("apply_now")} {directionArrow}
                </Link>
              </div>
              
              {/* Program Quick Facts */}
              <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl shadow-sm">
                <h3 className="text-lg font-extrabold text-navy mb-6 border-b border-gray-200/50 pb-3">
                  {label("quick_facts")}
                </h3>
                <div className="space-y-4">
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{label("program_code")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs flex items-center gap-1">
                      <Hash className="w-3.5 h-3.5 text-primary shrink-0" />
                      {pCode || "—"}
                    </span>
                  </div>
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{label("degree_level")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs">
                      {pDegree}
                    </span>
                  </div>
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{label("duration")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs flex items-center gap-1">
                      <Clock className="w-3.5 h-3.5 text-primary shrink-0" />
                      {pDuration}
                    </span>
                  </div>
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{label("language_of_instruction")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs flex items-center gap-1">
                      <Languages className="w-3.5 h-3.5 text-primary shrink-0" />
                      {pLanguage}
                    </span>
                  </div>
                  {pStudyMode && (
                    <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                      <span className="text-gray-500">{label("study_mode")}</span>
                      <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs">
                        {pStudyMode}
                      </span>
                    </div>
                  )}
                  {pTuition && (
                    <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                      <span className="text-gray-500">{label("tuition_fee")}</span>
                      <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs flex items-center gap-1">
                        <WalletCards className="w-3.5 h-3.5 text-primary shrink-0" />
                        {pTuition}
                      </span>
                    </div>
                  )}
                  <div className="flex justify-between items-center text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-500">{label("intake_period")}</span>
                    <span className="text-navy font-extrabold bg-white border border-gray-100 px-3 py-1 rounded-full text-xs">
                      {program.intake_period || label("intake_date")}
                    </span>
                  </div>
                  <div className="flex flex-col gap-1 text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-400 text-[10px] uppercase tracking-wider font-extrabold">{label("parent_faculty")}</span>
                    {program.faculty ? (
                      <Link 
                        to={`/faculty/${program.faculty.slug}`}
                        className="text-primary hover:text-primary-hover font-bold hover:underline text-xs"
                      >
                        {facultyName}
                      </Link>
                    ) : (
                      <span className="text-navy font-bold text-xs">—</span>
                    )}
                  </div>
                  <div className="flex flex-col gap-1 text-sm font-semibold border-b border-gray-200/20 pb-3">
                    <span className="text-gray-400 text-[10px] uppercase tracking-wider font-extrabold">{label("parent_department")}</span>
                    {program.department ? (
                      <Link 
                        to={`/department/${program.department.slug}`}
                        className="text-primary hover:text-primary-hover font-bold hover:underline text-xs"
                      >
                        {departmentName}
                      </Link>
                    ) : (
                      <span className="text-navy font-bold text-xs">—</span>
                    )}
                  </div>
                  <div className="flex flex-col gap-1 text-sm font-semibold">
                    <span className="text-gray-400 text-[10px] uppercase tracking-wider font-extrabold">{label("program_coordinator")}</span>
                    {pCoordinatorRoute ? (
                      <Link to={pCoordinatorRoute} className="text-primary hover:text-primary-hover font-bold hover:underline text-xs">
                        {pCoordinator}
                      </Link>
                    ) : (
                      <span className="text-navy font-bold text-xs">{pCoordinator}</span>
                    )}
                  </div>
                </div>
              </div>

              {programStaff.length > 0 && (
                <div className="border border-gray-100 p-8 rounded-3xl shadow-sm">
                  <h3 className="text-lg font-extrabold text-navy mb-6 border-b border-gray-200/50 pb-3 flex items-center gap-2">
                    <Users className="w-5 h-5 text-primary" /> {label("academic_staff")}
                  </h3>
                  <div className="flex flex-col gap-3">
                    {programStaff.slice(0, 8).map((member) => (
                      <div key={member.slug || member.name} className="p-3 bg-gray-50/50 border border-gray-100 rounded-xl">
                        {member.route ? (
                          <Link to={member.route} className="text-xs font-extrabold text-navy hover:text-primary transition-colors">
                            {member.name}
                          </Link>
                        ) : (
                          <p className="text-xs font-extrabold text-navy">{member.name}</p>
                        )}
                        {member.position && (
                          <p className="text-[11px] font-semibold text-gray-500 mt-1 leading-relaxed">
                            {member.position}
                          </p>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {careerOpportunities.length > 0 && (
                <div className="border border-gray-100 p-8 rounded-3xl shadow-sm">
                  <h3 className="text-lg font-extrabold text-navy mb-6 border-b border-gray-200/50 pb-3 flex items-center gap-2">
                    <Briefcase className="w-5 h-5 text-primary" /> {label("career_opportunities")}
                  </h3>
                  <div className="flex flex-col gap-3">
                    {careerOpportunities.map((opportunity, idx) => (
                      <div key={idx} className="flex gap-2.5 items-start p-3 bg-gray-50/50 border border-gray-100 rounded-xl hover:bg-white transition-colors duration-300">
                        <div className="w-5 h-5 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                          {directionArrow}
                        </div>
                        <span className="text-xs font-bold text-navy">
                          {opportunity}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Sidebar Contact Info */}
              <div className="border border-gray-100 p-8 rounded-3xl shadow-sm flex flex-col gap-6 bg-navy text-white">
                <div>
                  <h4 className="text-base font-extrabold mb-1 !text-white">{label("faculty_helpdesk")}</h4>
                  <p className="text-[11px] !text-white leading-normal">
                    {label("faculty_helpdesk_description")}
                  </p>
                </div>
                <div className="space-y-4 text-xs font-bold border-t border-white/10 pt-4 text-white/90">
                  {footerContact?.phone && (
                    <div className="flex items-center gap-3">
                      <Phone className="w-4 h-4 text-white/70 shrink-0" />
                      <a
                        href={`tel:${footerContact.phone.replace(/[^\d+]/g, "")}`}
                        dir="ltr"
                        style={{ unicodeBidi: "isolate" }}
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
                        dir="ltr"
                        style={{ unicodeBidi: "isolate" }}
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
