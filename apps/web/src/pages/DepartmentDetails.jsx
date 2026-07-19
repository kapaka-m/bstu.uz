import React, { useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ArrowRight,
  Award,
  BookOpen,
  Building2,
  ChevronRight,
  Clock,
  FileText,
  Globe,
  GraduationCap,
  Mail,
  Microscope,
  Phone,
  UserCheck,
  Users,
  Zap,
} from "lucide-react";
import { departmentsData, convertToSlug } from "../data/departmentsData";
import { facultiesData } from "../data/mockData";
import { programsData } from "../data/programsData";
import { useLanguage } from "../context/LanguageContext";

const getInitials = (name) => {
  if (!name) return "";
  const cleanName = name.replace(
    /^(Dr\.|Prof\.|Candidate|Associate|PhD|M\.Sc\.|B\.Sc\.)\s+/i,
    "",
  );
  const parts = cleanName.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return (parts[0][0] + (parts[1] ? parts[1][0] : "")).toUpperCase();
};

const telHref = (phone) => `tel:${String(phone || "").replace(/[^\d+]/g, "")}`;

const shortText = (text, max = 360) => {
  if (!text) return "";
  return text.length > max ? `${text.slice(0, max).trim()}...` : text;
};

const splitParagraphs = (value) =>
  String(value || "")
    .split(/\n{2,}|(?<=\.)\s+(?=[A-ZА-ЯЁЎҚҒҲO‘G‘])/)
    .map((item) => item.trim())
    .filter(Boolean);

const departmentSlugAliases = {
  "mechanics-and-engineering-graphics": "mechanics-engineering-graphics",
  "technological-machines-and-equipment": "technological-machines-equipment",
  "oil-and-gas-refining-technology": "oil-gas-refining-technology",
  "food-technology-and-service": "food-technology-service",
  "oil-and-gas-engineering-upstream-downstream":
    "oil-gas-engineering-upstream-downstream",
  "metrology-standardization-and-quality-control":
    "metrology-standardization-quality-control",
  "agricultural-and-water-resources-engineering-technologies":
    "agricultural-water-resources-engineering-technologies",
  "artificial-intelligence-and-digitalization":
    "artificial-intelligence-digitalization",
  "social-sciences-and-physical-culture": "social-sciences-physical-culture",
};

function ImageWithFallback({ src, alt, className, initialsClassName }) {
  const [failed, setFailed] = React.useState(!src);

  if (failed) {
    return <div className={initialsClassName}>{getInitials(alt)}</div>;
  }

  return (
    <img
      src={src}
      alt={alt}
      className={className}
      loading="lazy"
      width="160"
      height="160"
      onError={() => setFailed(true)}
    />
  );
}

function DepartmentSection({ id, icon: Icon, title, children }) {
  if (!children) return null;

  return (
    <section id={id} className="flex flex-col gap-5 text-start">
      <h2 className="text-xl md:text-2xl font-extrabold text-navy flex items-center gap-2">
        <Icon className="w-5 h-5 text-primary shrink-0" />
        {title}
      </h2>
      {children}
    </section>
  );
}

function TextPanel({ children }) {
  const paragraphs = splitParagraphs(children);
  if (paragraphs.length === 0) return null;

  return (
    <div className="bg-gray-50 border border-gray-100 rounded-3xl p-6 md:p-8 text-gray-500 text-sm md:text-base leading-relaxed flex flex-col gap-4">
      {paragraphs.map((paragraph, index) => (
        <p key={index}>{paragraph}</p>
      ))}
    </div>
  );
}

function ListPanel({ items, marker = "dot" }) {
  if (!items || items.length === 0) return null;

  return (
    <ul className="flex flex-col gap-3 bg-gray-50 border border-gray-100 rounded-3xl p-6 md:p-8">
      {items.map((item, index) => (
        <li
          key={`${String(item)}-${index}`}
          className="flex items-start gap-3 text-sm text-gray-500 font-semibold leading-relaxed"
        >
          <span
            className={`${marker === "number" ? "w-7 h-7 text-[11px]" : "w-2 h-2 mt-2"} rounded-full bg-primary text-white shrink-0 flex items-center justify-center font-extrabold`}
          >
            {marker === "number" ? index + 1 : ""}
          </span>
          <span>{item}</span>
        </li>
      ))}
    </ul>
  );
}

function resolveProgram(programName) {
  const clean = (value) =>
    String(value || "")
      .replace(/\[\s*(b\.sc\.|m\.sc\.|phd|dsc|phd\/dsc|bachelor|master)\s*\]/gi, "")
      .replace(/^\d+[\s–-]+/, "")
      .trim()
      .toLowerCase();

  const cleanName = clean(programName);
  const codeMatch = String(programName).match(/\b\d{8}\b|\b\d{2}\.\d{2}\.\d{2}\b/);

  if (codeMatch) {
    const byCode = programsData.find(
      (program) => program.code && program.code.replace(/-.*/, "") === codeMatch[0],
    );
    if (byCode) return byCode;
  }

  return programsData.find((program) => {
    const candidate = clean(program.name);
    return candidate && (candidate === cleanName || cleanName.includes(candidate));
  });
}

export default function DepartmentDetails() {
  const { id } = useParams();
  const { t, language } = useLanguage();
  const isRtl = language === "ar";
  const resolvedId = departmentSlugAliases[id] || id;
  const department = departmentsData[resolvedId];

  useEffect(() => {
    window.scrollTo(0, 0);
    if (department) {
      document.title = `${department.name} | BSTU`;
    }
  }, [department, id]);

  if (!department) {
    return (
      <div className="pt-24 min-h-screen bg-white flex flex-col items-center justify-center text-center p-4">
        <h2 className="text-2xl font-extrabold text-navy mb-4">
          {t("common.notFound", "Page not found")}
        </h2>
        <p className="text-gray-500 mb-6">
          {t("common.notFoundDesc", "The requested page could not be found.")}
        </p>
        <Link
          to="/"
          className="bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-xl font-bold text-sm transition-colors"
        >
          {t("common.goHome", "Go home")}
        </Link>
      </div>
    );
  }

  const parentFaculty = facultiesData.find((faculty) => faculty.id === department.facultyId);
  const facultyName = parentFaculty
    ? t(`faculties.${parentFaculty.id}.name`, parentFaculty.name)
    : t("common.faculty", "Faculty");
  const departmentName = t(`departments.${resolvedId}.name`, department.name);
  const about = t(`departments.${resolvedId}.about`, department.about || "");
  const translatedHeadName = t(`departments.${resolvedId}.head`, department.head);

  const siblingDepartments = Object.entries(departmentsData)
    .filter(([, item]) => item.facultyId === department.facultyId)
    .map(([slug, item]) => ({
      slug,
      name: t(`departments.${slug}.name`, item.name),
    }));

  const labels = {
    home: t("nav.home", "Home"),
    faculties: t("common.faculties", "Faculties"),
    quickContact: t("common.quickContact", "Quick Contact"),
    backToFaculty: t("common.backToFaculty", "Back to Faculty"),
    headOfDepartment: t("common.headOfDepartment", "Head of Department"),
    aboutDepartment: t("common.aboutDepartment", "About Department"),
    departmentStaff: t("common.departmentStaff", "Professor-Teachers"),
    academicPrograms: t("common.academicPrograms", "Academic Programs"),
    researchLabs: t("common.researchLabs", "Research Labs"),
    departmentHistory: t("common.departmentHistory", "Department History"),
    curriculumSubjects: t("common.curriculumSubjects", "Curriculum & Subjects"),
    researchInnovation: t("common.researchInnovation", "Research & Innovation"),
    publications: t("common.publicationsTextbooks", "Publications & Textbooks"),
    cooperation: t("common.internationalCooperation", "International Cooperation"),
    futurePlans: t("common.futurePlans", "Future Perspective & Plans"),
    activities: t("common.departmentActivities", "Department Life & Activities"),
    bachelor: t("common.bachelorPrograms", "Bachelor's Courses"),
    master: t("common.masterPrograms", "Master's Courses"),
  };

  return (
    <div className="pt-20 bg-white" dir={isRtl ? "rtl" : "ltr"}>
      <section className="bg-primary-light border-b border-gray-100">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-14 md:py-20">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <motion.div
              initial={{ opacity: 0, y: 18 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.45 }}
              className="lg:col-span-8 text-start"
            >
              <nav className="flex flex-wrap items-center gap-2 text-xs md:text-sm font-semibold text-gray-500 mb-6">
                <Link to="/" className="hover:text-primary transition-colors">
                  {labels.home}
                </Link>
                <ChevronRight className="w-3.5 h-3.5 text-gray-300 shrink-0" />
                <span>{labels.faculties}</span>
                <ChevronRight className="w-3.5 h-3.5 text-gray-300 shrink-0" />
                {parentFaculty ? (
                  <Link
                    to={`/faculty/${parentFaculty.id}`}
                    className="hover:text-primary transition-colors"
                  >
                    {facultyName}
                  </Link>
                ) : (
                  <span>{facultyName}</span>
                )}
                <ChevronRight className="w-3.5 h-3.5 text-gray-300 shrink-0" />
                <span className="text-gray-400 font-bold">{departmentName}</span>
              </nav>

              <span className="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider text-primary bg-white/70 border border-white px-3 py-1.5 rounded-full mb-5">
                <Building2 className="w-4 h-4" />
                {facultyName}
              </span>
              <h1 className="text-3xl md:text-5xl font-extrabold text-navy leading-tight tracking-tight mb-5">
                {departmentName}
              </h1>
              <p className="text-gray-500 text-sm md:text-lg leading-relaxed max-w-3xl">
                {shortText(about || department.history || department.research)}
              </p>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, y: 18 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.45, delay: 0.08 }}
              className="lg:col-span-4"
            >
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
                <div className="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-5">
                  <UserCheck className="w-7 h-7" />
                </div>
                <p className="text-[11px] font-extrabold text-primary uppercase tracking-wider mb-2">
                  {labels.quickContact}
                </p>
                <h2 className="text-lg font-extrabold text-navy leading-snug">
                  {translatedHeadName}
                </h2>
                <div className="flex flex-col gap-2 mt-4 text-xs font-semibold text-gray-500">
                  {department.headPhone && (
                    <a
                      href={telHref(department.headPhone)}
                      className="flex items-center gap-2 hover:text-primary transition-colors"
                    >
                      <Phone className="w-4 h-4 text-primary shrink-0" />
                      <span dir="ltr">{department.headPhone}</span>
                    </a>
                  )}
                  {department.headEmail && (
                    <a
                      href={`mailto:${department.headEmail}`}
                      className="flex items-center gap-2 hover:text-primary transition-colors min-w-0"
                    >
                      <Mail className="w-4 h-4 text-primary shrink-0" />
                      <span className="truncate">{department.headEmail}</span>
                    </a>
                  )}
                  {department.headOfficeHours && (
                    <span className="flex items-start gap-2">
                      <Clock className="w-4 h-4 text-primary shrink-0 mt-0.5" />
                      <span>
                        {t(
                          `departments.${resolvedId}.headOfficeHours`,
                          department.headOfficeHours,
                        )}
                      </span>
                    </span>
                  )}
                </div>
                {parentFaculty && (
                  <div className="mt-6">
                    <Link
                      to={`/faculty/${parentFaculty.id}`}
                      className="inline-flex items-center gap-2 text-xs font-extrabold text-primary hover:text-primary-hover transition-colors"
                    >
                      {labels.backToFaculty}
                      <ArrowRight className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`} />
                    </Link>
                  </div>
                )}
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">
          <aside className="lg:col-span-4 flex flex-col gap-6">
            <div className="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm flex flex-col items-center text-center gap-5">
              <ImageWithFallback
                src={department.headImage}
                alt={translatedHeadName}
                className="w-28 h-28 rounded-full object-cover border-4 border-white shadow-md"
                initialsClassName="w-28 h-28 rounded-full bg-linear-to-br from-primary to-primary-hover text-white shadow-md flex items-center justify-center font-extrabold text-xl border-4 border-white"
              />
              <div>
                <p className="text-primary text-[11px] font-extrabold uppercase tracking-wider mb-2">
                  {labels.headOfDepartment}
                </p>
                <Link
                  to={`/profile/${convertToSlug(department.head)}`}
                  className="text-xl font-extrabold text-navy leading-snug hover:text-primary transition-colors"
                >
                  {translatedHeadName}
                </Link>
                {department.headTitle && (
                  <p className="text-sm text-gray-500 font-semibold mt-2">
                    {t(`departments.${resolvedId}.headTitle`, department.headTitle)}
                  </p>
                )}
              </div>
            </div>

            <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
              <h2 className="text-lg font-extrabold text-navy mb-4">{facultyName}</h2>
              <div className="flex flex-col gap-2">
                {siblingDepartments.map((item) => (
                  <Link
                    key={item.slug}
                    to={`/department/${item.slug}`}
                    className={`text-xs font-bold p-3 rounded-xl transition-all ${
                      item.slug === resolvedId
                        ? "bg-primary text-white shadow-md shadow-primary/10"
                        : "text-gray-500 hover:bg-gray-50"
                    }`}
                  >
                    {item.name}
                  </Link>
                ))}
              </div>
            </div>
          </aside>

          <main className="lg:col-span-8 flex flex-col gap-12">
            <DepartmentSection id="about" icon={BookOpen} title={labels.aboutDepartment}>
              <TextPanel>{about}</TextPanel>
            </DepartmentSection>

            {department.staff?.length > 0 && (
              <DepartmentSection id="staff" icon={Users} title={labels.departmentStaff}>
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                  {department.staff.map((member, index) => {
                    const staffName = t(
                      `departments.${resolvedId}.staff.${index}.name`,
                      member.name,
                    );
                    return (
                      <Link
                        to={`/profile/${convertToSlug(member.name)}`}
                        key={`${member.name}-${index}`}
                        className="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex gap-3 items-center text-start hover:shadow-md hover:border-primary/20 transition-all"
                      >
                        <ImageWithFallback
                          src={member.image}
                          alt={staffName}
                          className="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm shrink-0"
                          initialsClassName="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm shrink-0"
                        />
                        <div className="min-w-0">
                          <h3 className="text-sm font-extrabold text-navy leading-snug">
                            {staffName}
                          </h3>
                          <p className="text-[11px] text-gray-500 font-semibold leading-relaxed mt-1">
                            {t(
                              `departments.${resolvedId}.staff.${index}.title`,
                              member.title,
                            )}
                          </p>
                        </div>
                      </Link>
                    );
                  })}
                </div>
              </DepartmentSection>
            )}

            {department.programs?.length > 0 && (
              <DepartmentSection
                id="programs"
                icon={GraduationCap}
                title={labels.academicPrograms}
              >
                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                  {department.programs.map((programName, index) => {
                    const translatedName = t(
                      `departments.${resolvedId}.programs.${index}`,
                      programName,
                    );
                    const matchedProgram = resolveProgram(programName);
                    const content = (
                      <>
                        <div className="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                          <GraduationCap className="w-5 h-5" />
                        </div>
                        <div className="min-w-0">
                          {matchedProgram?.code && (
                            <span className="text-[11px] font-extrabold text-primary uppercase tracking-wider">
                              {matchedProgram.code}
                            </span>
                          )}
                          <h3 className="text-sm md:text-base font-bold text-navy leading-snug mt-1">
                            {translatedName}
                          </h3>
                        </div>
                      </>
                    );

                    return matchedProgram ? (
                      <Link
                        key={`${programName}-${index}`}
                        to={`/programs/${matchedProgram.id}`}
                        className="bg-white border border-gray-100 hover:border-primary/25 rounded-2xl p-5 shadow-sm flex gap-4 items-start transition-colors text-start w-full"
                      >
                        {content}
                      </Link>
                    ) : (
                      <div
                        key={`${programName}-${index}`}
                        className="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex gap-4 items-start text-start w-full"
                      >
                        {content}
                      </div>
                    );
                  })}
                </div>
              </DepartmentSection>
            )}

            {department.labs?.length > 0 && (
              <DepartmentSection id="labs" icon={Microscope} title={labels.researchLabs}>
                <ListPanel
                  items={department.labs.map((lab, index) =>
                    t(`departments.${resolvedId}.labs.${index}`, lab),
                  )}
                  marker="number"
                />
              </DepartmentSection>
            )}

            {department.history && (
              <DepartmentSection id="history" icon={Clock} title={labels.departmentHistory}>
                <TextPanel>
                  {t(`departments.${resolvedId}.history`, department.history)}
                </TextPanel>
              </DepartmentSection>
            )}

            {department.subjects && (
              <DepartmentSection
                id="subjects"
                icon={BookOpen}
                title={labels.curriculumSubjects}
              >
                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                  {department.subjects.bachelor?.length > 0 && (
                    <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                      <h3 className="font-extrabold text-navy mb-4 flex items-center gap-2">
                        <GraduationCap className="w-4 h-4 text-primary" />
                        {labels.bachelor}
                      </h3>
                      <ListPanel
                        items={department.subjects.bachelor.map((subject, index) =>
                          t(`departments.${resolvedId}.subjects.bachelor.${index}`, subject),
                        )}
                      />
                    </div>
                  )}
                  {department.subjects.master?.length > 0 && (
                    <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                      <h3 className="font-extrabold text-navy mb-4 flex items-center gap-2">
                        <GraduationCap className="w-4 h-4 text-primary" />
                        {labels.master}
                      </h3>
                      <ListPanel
                        items={department.subjects.master.map((subject, index) =>
                          t(`departments.${resolvedId}.subjects.master.${index}`, subject),
                        )}
                      />
                    </div>
                  )}
                </div>
              </DepartmentSection>
            )}

            {department.research && (
              <DepartmentSection
                id="research"
                icon={Microscope}
                title={labels.researchInnovation}
              >
                <TextPanel>
                  {t(`departments.${resolvedId}.research`, department.research)}
                </TextPanel>
              </DepartmentSection>
            )}

            {department.publications && (
              <DepartmentSection id="publications" icon={Award} title={labels.publications}>
                <div className="flex flex-col gap-5">
                  {Object.entries(department.publications).map(([key, items]) =>
                    Array.isArray(items) && items.length > 0 ? (
                      <div
                        key={key}
                        className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm"
                      >
                        <h3 className="font-extrabold text-navy mb-4 flex items-center gap-2">
                          <FileText className="w-4 h-4 text-primary" />
                          {key
                            .replace(/([A-Z])/g, " $1")
                            .replace(/^./, (char) => char.toUpperCase())}
                        </h3>
                        <ListPanel
                          items={items.map((item, index) =>
                            t(`departments.${resolvedId}.publications.${key}.${index}`, item),
                          )}
                        />
                      </div>
                    ) : null,
                  )}
                </div>
              </DepartmentSection>
            )}

            {department.partnerships?.length > 0 && (
              <DepartmentSection id="cooperation" icon={Globe} title={labels.cooperation}>
                <ListPanel
                  items={department.partnerships.map((item, index) =>
                    t(`departments.${resolvedId}.partnerships.${index}`, item),
                  )}
                />
              </DepartmentSection>
            )}

            {department.futurePlans?.length > 0 && (
              <DepartmentSection id="future-plans" icon={Zap} title={labels.futurePlans}>
                <ListPanel
                  items={department.futurePlans.map((item, index) =>
                    t(`departments.${resolvedId}.futurePlans.${index}`, item),
                  )}
                  marker="number"
                />
              </DepartmentSection>
            )}

            {department.activities?.length > 0 && (
              <DepartmentSection id="activities" icon={Zap} title={labels.activities}>
                <ListPanel
                  items={department.activities.map((item, index) =>
                    t(`departments.${resolvedId}.activities.${index}`, item),
                  )}
                  marker="number"
                />
              </DepartmentSection>
            )}
          </main>
        </div>
      </div>
    </div>
  );
}
