import React, { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ArrowRight,
  BookOpen,
  BriefcaseBusiness,
  Building2,
  Clock,
  GraduationCap,
  Mail,
  Phone,
  ShieldCheck,
  UserCheck,
  Users,
} from "lucide-react";
import { facultiesData } from "../data/mockData";
import { programsData } from "../data/programsData";
import { departmentsData } from "../data/departmentsData";
import { useLanguage } from "../context/LanguageContext";

const getInitials = (name) => {
  if (!name) return "";
  const cleanName = name.replace(
    /^(Dr\.|Prof\.|Candidate|Associate|PhD|M\.Sc\.|B\.Sc\.)\s+/i,
    "",
  );
  const parts = cleanName.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
};

const telHref = (phone) => `tel:${phone.replace(/[^\d+]/g, "")}`;

const shortText = (text, max = 170) => {
  if (!text) return "";
  return text.length > max ? `${text.slice(0, max).trim()}...` : text;
};

const parseProgramString = (str) => {
  if (!str) return { code: "", name: "" };
  const codeMatch = str.match(/^(\d+)\s*[-–—]\s*(.*)$/);
  if (codeMatch) {
    return {
      code: codeMatch[1],
      name: codeMatch[2].replace(/\[[^\]]*\]/g, "").trim(),
    };
  }
  return {
    code: "",
    name: str.replace(/\[[^\]]*\]/g, "").trim(),
  };
};

// Helper function for matching
const findProgramMatch = (pName, tName, list) => {
  if (!pName) return null;
  const clean = (n) =>
    n
      .replace(
        /\[\s*(b\.sc\.|m\.sc\.|phd|dsc|phd\/dsc|bachelor|master)\s*\]/gi,
        "",
      )
      .replace(/^\d+[\s–-–]+/, "")
      .trim()
      .toLowerCase();
  const cleanOrig = clean(pName);
  const cleanTrans = clean(tName || "");

  // Match by original cleaned name first to avoid collisions from stale translations
  let found = list.find((p) => clean(p.name) === cleanOrig);
  if (found) return found;

  // Then match by translated name when available
  found = list.find((p) => clean(p.name) === cleanTrans);
  if (found) return found;

  // Match by code fallback (for legacy strings with exact numeric identifiers)
  const codeMatch = pName.match(/\b\d{8}\b|\b\d{2}\.\d{2}\.\d{2}\b/);
  if (codeMatch) {
    found = list.find(
      (p) => p.code && p.code.replace(/-.*/, "") === codeMatch[0],
    );
    if (found) return found;
  }

  // Match by substring (longest name first to avoid "Management" false positives)
  const sorted = [...list].sort((a, b) => b.name.length - a.name.length);
  found = sorted.find((p) => {
    const cName = clean(p.name);
    if (cName.length < 4) return false;
    return cleanOrig.includes(cName) || cleanTrans.includes(cName);
  });
  return found;
};

function ImageWithFallback({
  src,
  fallbackSrc,
  alt,
  className,
  initialsClassName,
}) {
  const [currentSrc, setCurrentSrc] = useState(src);
  const [failed, setFailed] = useState(false);

  if (failed || !currentSrc) {
    return <div className={initialsClassName}>{getInitials(alt)}</div>;
  }

  return (
    <img
      src={currentSrc}
      alt={alt}
      className={className}
      loading="lazy"
      width="160"
      height="160"
      onError={() => {
        if (fallbackSrc && currentSrc !== fallbackSrc) {
          setCurrentSrc(fallbackSrc);
          return;
        }
        setFailed(true);
      }}
    />
  );
}

function SectionTitle({ icon: Icon, eyebrow, title, description }) {
  return (
    <div className="flex flex-col gap-3 text-start">
      {eyebrow && (
        <span className="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider text-primary">
          <Icon className="w-4 h-4" />
          {eyebrow}
        </span>
      )}
      <h2 className="text-2xl md:text-3xl font-extrabold text-navy leading-tight">
        {title}
      </h2>
      {description && (
        <p className="text-sm md:text-base text-gray-500 leading-relaxed max-w-3xl">
          {description}
        </p>
      )}
    </div>
  );
}

function ProgramGrid({ programs, label, icon: Icon, t }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      {programs.map((program, index) => {
        const keyMap = {
          "software-engineering": "softwareEngineering",
          "mechanical-engineering": "mechanicalEngineering",
          architecture: "architecture",
          economics: "economics",
          "oil-gas-engineering": "oilGasEngineering",
          "power-engineering": "powerEngineering",
          construction: "construction",
          metallurgy: "metallurgy",
          "automotive-engineering": "automotiveEngineering",
          cybersecurity: "cybersecurity",
          "food-technology": "foodTechnology",
          "food-technology-60720100": "foodTechnology",
          "textile-engineering": "textileEngineering",
        };
        const displayName = program.id
          ? t(
              `home.programs.list.${keyMap[program.id] || program.id}.name`,
              program.name,
            )
          : program.name;

        const content = (
          <>
            <div className="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
              <Icon className="w-5 h-5" />
            </div>
            <div className="min-w-0">
              {program.code && (
                <span className="text-[11px] font-extrabold text-primary uppercase tracking-wider">
                  {program.code}
                </span>
              )}
              <h3 className="text-sm md:text-base font-bold text-navy leading-snug mt-1">
                {displayName}
              </h3>
              <p className="text-xs font-semibold text-gray-400 mt-2">
                {label}
              </p>
            </div>
          </>
        );

        return program.route ? (
          <Link
            key={`${program.code}-${program.name}-${index}`}
            to={program.route}
            className="bg-white border border-gray-100 hover:border-primary/25 rounded-2xl p-5 shadow-sm flex gap-4 items-start transition-colors text-start w-full"
          >
            {content}
          </Link>
        ) : (
          <div
            key={`${program.code}-${program.name}-${index}`}
            className="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex gap-4 items-start text-start w-full"
          >
            {content}
          </div>
        );
      })}
    </div>
  );
}

export default function FacultyDetails() {
  const { id } = useParams();
  const { t, language } = useLanguage();
  const isRtl = language === "ar";

  // Find dynamic faculty
  const faculty = facultiesData.find((f) => f.id === id) || facultiesData[0];

  useEffect(() => {
    window.scrollTo(0, 0);
    document.title = `${t(`faculties.${faculty.id}.name`, faculty.name)} | BSTU`;
  }, [id, faculty, t]);

  const labels = {
    home: t("nav.home", "Home"),
    faculties: t("common.faculties", "Faculties"),
    departments: t("common.departments", "Departments"),
    bachelorPrograms: t("common.bachelorPrograms", "Bachelor Programs"),
    masterSpecializations: t(
      "facultyTechnology.masterSpecializations",
      "Master Specializations",
    ),
    contact: t("common.contact", "Contact"),
    overview: t("common.aboutFaculty", "Faculty Overview"),
    leadership: t("common.managementDean", "Faculty Leadership"),
    learnMore: t("common.learnMore", "Learn more"),
    head: t("common.headOfDepartment", "Head of Department"),
    phone: t("common.phone", "Phone"),
    email: t("common.email", "Email"),
    quickDepartmentLinks: t(
      "facultyTechnology.quickDepartmentLinks",
      "Quick Department Links",
    ),
    deanContact: t("facultyTechnology.deanContact", "Dean Contact"),
    deputyDeanContacts: t(
      "facultyTechnology.deputyDeanContacts",
      "Deputy Dean Contacts",
    ),
    industryCooperation: t(
      "facultyTechnology.industryCooperation",
      "Industry Cooperation",
    ),
    academicPathways: t(
      "facultyTechnology.academicPathways",
      "Academic Pathways",
    ),
  };

  const title = t(`faculties.${faculty.id}.name`, faculty.name);
  const overview = t(`faculties.${faculty.id}.about`, faculty.about);
  const overviewParagraphs = overview.split(/(?<=\.)\s+/).filter(Boolean);

  // 1. Resolve and deduplicate departments belonging to this faculty
  const uniqueDeptsMap = new Map();
  Object.entries(departmentsData)
    .filter(([_, dept]) => dept.facultyId === faculty.id)
    .forEach(([key, dept]) => {
      const normalizedName = dept.name.toLowerCase().trim();
      if (!uniqueDeptsMap.has(normalizedName)) {
        uniqueDeptsMap.set(normalizedName, { key, dept });
      } else {
        if (key.includes("-and-")) {
          uniqueDeptsMap.set(normalizedName, { key, dept });
        }
      }
    });

  const facultyDepartmentsList = Array.from(uniqueDeptsMap.values()).map(
    ({ key, dept }) => ({
      slug: key,
      name: dept.name,
      route: `/department/${key}`,
      about: dept.about || "",
      contact: dept.head
        ? {
            name: t(`departments.${key}.head`, dept.head),
            role: t(
              `departments.${key}.headTitle`,
              dept.headTitle || "Head of Department",
            ),
            phone: dept.headPhone,
            email: dept.headEmail,
          }
        : null,
    }),
  );

  // 2. Parse the programs array from mockData into Bachelor and Master grids
  const bachelorPrograms = [];
  const masterPrograms = [];

  if (faculty.programs) {
    faculty.programs.forEach((progStr, index) => {
      const localizedStr = t(
        `faculties.${faculty.id}.programs.${index}`,
        progStr,
      );
      const { code, name } = parseProgramString(localizedStr);
      const matched = findProgramMatch(progStr, localizedStr, programsData);

      const programObj = {
        code: code || matched?.code || "",
        name: name,
        id: matched?.id || "",
        route: matched ? `/programs/${matched.id}` : "",
        originalStr: progStr,
      };

      const hasBachelor =
        /b\.sc\.|bachelor/i.test(localizedStr) ||
        /b\.sc\.|bachelor/i.test(progStr);
      const hasMaster =
        /m\.sc\.|master/i.test(localizedStr) || /m\.sc\.|master/i.test(progStr);
      const hasPhD = /phd|dsc/i.test(localizedStr) || /phd|dsc/i.test(progStr);

      const isBachelor = hasBachelor || (!hasMaster && !hasPhD);

      if (isBachelor) {
        bachelorPrograms.push(programObj);
      }
      if (hasMaster) {
        masterPrograms.push({
          ...programObj,
          name: programObj.name.includes("Master")
            ? programObj.name
            : `${programObj.name} [M.Sc.]`,
        });
      }
      if (hasPhD) {
        masterPrograms.push({
          ...programObj,
          name:
            programObj.name.includes("PhD") ||
            programObj.name.includes("Doctoral")
              ? programObj.name
              : `${programObj.name} [PhD]`,
        });
      }
    });
  }

  // 3. Set up leadership list
  let leadership = [];
  if (faculty.id === "faculty-of-natural-resources-management") {
    leadership = [
      {
        name: t(
          "faculties.faculty-of-natural-resources-management.dean",
          faculty.management.dean,
        ),
        role: t(
          "faculties.faculty-of-natural-resources-management.deanTitle",
          faculty.management.title,
        ),
        reception: t(
          "faculties.faculty-of-natural-resources-management.deanOfficeHours",
          faculty.management.officeHours,
        ),
        phone: faculty.management.phone,
        email: faculty.management.email,
        image: null,
        fallbackImage: null,
      },
      {
        name: t(
          "faculties.faculty-of-natural-resources-management.deputy1Name",
          "To be announced",
        ),
        role: t(
          "faculties.faculty-of-natural-resources-management.deputy1Role",
          "Deputy Dean for Academic Affairs",
        ),
        reception: t(
          "faculties.faculty-of-natural-resources-management.deputy1Hours",
          "Every day 14:00–16:00",
        ),
        phone: faculty.management.phone,
        email: "resources-dean@bstu.uz",
        image: null,
        fallbackImage: null,
      },
      {
        name: t(
          "faculties.faculty-of-natural-resources-management.deputy2Name",
          "Gadoyeva Abera Hasanovna",
        ),
        role: t(
          "faculties.faculty-of-natural-resources-management.deputy2Role",
          "Deputy Dean for Youth Affairs",
        ),
        reception: t(
          "faculties.faculty-of-natural-resources-management.deputy2Hours",
          "Every day 14:00–16:00",
        ),
        phone: faculty.management.phone,
        email: "resources-dean@bstu.uz",
        image: null,
        fallbackImage: null,
      },
    ];
  } else if (faculty.leadership) {
    leadership = faculty.leadership.map((member, idx) => ({
      name: t(`faculties.${faculty.id}.leadership.${idx}.name`, member.name),
      role: t(`faculties.${faculty.id}.leadership.${idx}.role`, member.role),
      reception: t(
        `faculties.${faculty.id}.leadership.${idx}.officeHours`,
        member.officeHours || member.reception,
      ),
      phone: member.phone,
      email: member.email,
      image: member.image,
      fallbackImage: member.fallbackImage,
    }));
  } else if (faculty.management) {
    leadership = [
      {
        name: faculty.management.dean,
        role: t(
          `faculties.${faculty.id}.management.title`,
          faculty.management.title,
        ),
        reception: t(
          `faculties.${faculty.id}.management.officeHours`,
          faculty.management.officeHours,
        ),
        phone: faculty.management.phone,
        email: faculty.management.email,
        image: null,
        fallbackImage: null,
      },
      {
        name: t(
          `faculties.${faculty.id}.deputyAcademicName`,
          "To be announced",
        ),
        role: t(
          `faculties.${faculty.id}.deputyAcademicRole`,
          "Deputy Dean for Academic Affairs",
        ),
        reception: t(
          `faculties.${faculty.id}.deputyAcademicHours`,
          faculty.management.officeHours,
        ),
        phone: faculty.management.phone,
        email: faculty.management.email,
        image: null,
        fallbackImage: null,
      },
      {
        name: t(`faculties.${faculty.id}.deputyYouthName`, "To be announced"),
        role: t(
          `faculties.${faculty.id}.deputyYouthRole`,
          "Deputy Dean for Youth Affairs",
        ),
        reception: t(
          `faculties.${faculty.id}.deputyYouthHours`,
          faculty.management.officeHours,
        ),
        phone: faculty.management.phone,
        email: faculty.management.email,
        image: null,
        fallbackImage: null,
      },
    ];
  }

  return (
    <div className="pt-20 bg-white" dir={isRtl ? "rtl" : "ltr"}>
      {/* Top Banner/Hero */}
      <section className="bg-primary-light border-b border-gray-100">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-14 md:py-20">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <motion.div
              initial={{ opacity: 0, y: 18 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.45 }}
              className="lg:col-span-8 text-start"
            >
              <span className="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider text-primary bg-white/70 border border-white px-3 py-1.5 rounded-full mb-5">
                <BriefcaseBusiness className="w-4 h-4" />
                {labels.industryCooperation}
              </span>
              <h1 className="text-3xl md:text-5xl font-extrabold text-navy leading-tight tracking-tight mb-5">
                {title}
              </h1>
              <p className="text-gray-500 text-sm md:text-lg leading-relaxed max-w-3xl">
                {shortText(overview, 330)}
              </p>

              <div className="flex flex-wrap gap-3 mt-8">
                {[
                  [labels.departments, "#departments"],
                  [labels.bachelorPrograms, "#bachelor-programs"],
                  [labels.masterSpecializations, "#master-specializations"],
                  [labels.contact, "#contact"],
                ].map(([label, href]) => (
                  <a
                    key={href}
                    href={href}
                    className="inline-flex items-center justify-center gap-2 bg-white border border-gray-100 hover:border-primary/30 text-navy hover:text-primary px-5 py-3 rounded-xl text-xs font-extrabold transition-all shadow-sm"
                  >
                    {label}
                    <ArrowRight
                      className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`}
                    />
                  </a>
                ))}
              </div>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, y: 18 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.45, delay: 0.08 }}
              className="lg:col-span-4"
            >
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                <div className="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-5">
                  <GraduationCap className="w-7 h-7" />
                </div>
                <p className="text-3xl font-extrabold text-navy">
                  {facultyDepartmentsList.length}
                </p>
                <p className="text-xs font-extrabold uppercase tracking-wider text-primary mt-1">
                  {labels.departments}
                </p>
                <div className="mt-6 grid grid-cols-2 gap-3">
                  <div className="bg-primary-light rounded-2xl p-4">
                    <p className="text-xl font-extrabold text-navy">
                      {bachelorPrograms.length}
                    </p>
                    <p className="text-[10px] font-bold text-gray-500 mt-1">
                      {labels.bachelorPrograms}
                    </p>
                  </div>
                  <div className="bg-primary-light rounded-2xl p-4">
                    <p className="text-xl font-extrabold text-navy">
                      {masterPrograms.length}
                    </p>
                    <p className="text-[10px] font-bold text-gray-500 mt-1">
                      {labels.masterSpecializations}
                    </p>
                  </div>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Main Content Area */}
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 flex flex-col gap-16">
        {/* Faculty Overview */}
        <section
          id="overview"
          className="grid grid-cols-1 lg:grid-cols-12 gap-8"
        >
          <div className="lg:col-span-4">
            <SectionTitle
              icon={BookOpen}
              eyebrow={labels.overview}
              title={labels.overview}
              description={labels.industryCooperation}
            />
          </div>
          <div className="lg:col-span-8 bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm">
            <div className="flex flex-col gap-4 text-gray-500 text-sm md:text-base leading-relaxed text-start">
              {overviewParagraphs.map((paragraph, index) => (
                <p key={index}>{paragraph}</p>
              ))}
            </div>
          </div>
        </section>

        {/* Leadership */}
        <section id="leadership" className="flex flex-col gap-8">
          <SectionTitle
            icon={UserCheck}
            eyebrow={labels.leadership}
            title={labels.leadership}
            description={t(
              "facultyTechnology.leadershipDesc",
              "Faculty leadership and contact details for academic, youth, and administrative affairs.",
            )}
          />
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {leadership.map((member, idx) => (
              <article
                key={member.email || idx}
                className="bg-gray-50 border border-gray-100 rounded-3xl p-6 shadow-sm text-center flex flex-col items-center"
              >
                <ImageWithFallback
                  src={member.image}
                  fallbackSrc={member.fallbackImage}
                  alt={member.name}
                  className="w-28 h-28 rounded-full object-cover border-4 border-white shadow-md mb-5"
                  initialsClassName="w-28 h-28 rounded-full bg-linear-to-br from-primary to-primary-hover text-white shadow-md flex items-center justify-center font-extrabold text-xl border-4 border-white mb-5"
                />
                <p className="text-primary text-[10px] font-extrabold uppercase tracking-wider mb-2">
                  {member.role}
                </p>
                <h3 className="text-base font-extrabold text-navy leading-snug">
                  {member.name}
                </h3>
                <div className="w-full border-t border-gray-200/60 mt-5 pt-4 flex flex-col gap-2 text-xs font-semibold text-gray-500 text-start">
                  {member.reception && (
                    <span className="flex items-start gap-2">
                      <Clock className="w-4 h-4 text-primary shrink-0 mt-0.5" />
                      <span>{member.reception}</span>
                    </span>
                  )}
                  {member.phone && (
                    <a
                      href={telHref(member.phone)}
                      className="flex items-center gap-2 hover:text-primary transition-colors"
                    >
                      <Phone className="w-4 h-4 text-primary shrink-0" />
                      <span dir="ltr">{member.phone}</span>
                    </a>
                  )}
                  {member.email && (
                    <a
                      href={`mailto:${member.email}`}
                      className="flex items-center gap-2 hover:text-primary transition-colors min-w-0"
                    >
                      <Mail className="w-4 h-4 text-primary shrink-0" />
                      <span className="truncate">{member.email}</span>
                    </a>
                  )}
                </div>
              </article>
            ))}
          </div>
        </section>

        {/* Departments */}
        <section id="departments" className="flex flex-col gap-8">
          <SectionTitle
            icon={ShieldCheck}
            eyebrow={labels.departments}
            title={labels.departments}
            description={t(
              "facultyTechnology.departmentsDesc",
              "Specialized departments connect academic training with industrial practice and applied research.",
            )}
          />
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            {facultyDepartmentsList.map((department, index) => {
              const summary = shortText(
                t(`departments.${department.slug}.about`, department.about),
                160,
              );
              return (
                <Link
                  key={department.slug}
                  to={department.route}
                  className="group bg-white border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all flex flex-col gap-5 text-start w-full"
                >
                  <div className="flex items-start justify-between gap-4">
                    <div className="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                      <Building2 className="w-6 h-6" />
                    </div>
                    <span className="text-[11px] font-extrabold text-gray-300">
                      {String(index + 1).padStart(2, "0")}
                    </span>
                  </div>
                  <div>
                    <h3 className="text-base font-extrabold text-navy group-hover:text-primary transition-colors leading-snug">
                      {t(
                        `departments.${department.slug}.name`,
                        department.name,
                      )}
                    </h3>
                    {summary && (
                      <p className="text-xs md:text-sm text-gray-500 font-medium leading-relaxed mt-3">
                        {summary}
                      </p>
                    )}
                  </div>
                  {department.contact?.name && (
                    <div className="border-t border-gray-100 pt-4 text-xs font-semibold text-gray-500 flex items-start gap-2 mt-auto">
                      <Users className="w-4 h-4 text-primary shrink-0 mt-0.5" />
                      <span>
                        <span className="text-navy font-bold">
                          {labels.head}:{" "}
                        </span>
                        {department.contact.name}
                      </span>
                    </div>
                  )}
                  <span className="inline-flex items-center gap-2 text-xs font-extrabold text-primary mt-auto">
                    {labels.learnMore}
                    <ArrowRight
                      className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`}
                    />
                  </span>
                </Link>
              );
            })}
          </div>
        </section>

        {/* Bachelor Programs */}
        {bachelorPrograms.length > 0 && (
          <section id="bachelor-programs" className="flex flex-col gap-8">
            <SectionTitle
              icon={GraduationCap}
              eyebrow={labels.academicPathways}
              title={labels.bachelorPrograms}
              description={t(
                "facultyTechnology.bachelorDesc",
                "Bachelor degree programs offered by the Faculty.",
              )}
            />
            <ProgramGrid
              programs={bachelorPrograms}
              label={labels.bachelorPrograms}
              icon={GraduationCap}
              t={t}
            />
          </section>
        )}

        {/* Master Programs */}
        {masterPrograms.length > 0 && (
          <section id="master-specializations" className="flex flex-col gap-8">
            <SectionTitle
              icon={BookOpen}
              eyebrow={labels.academicPathways}
              title={labels.masterSpecializations}
              description={t(
                "facultyTechnology.masterDesc",
                "Master and PhD degree specializations available through the Faculty.",
              )}
            />
            <ProgramGrid
              programs={masterPrograms}
              label={labels.masterSpecializations}
              icon={BookOpen}
              t={t}
            />
          </section>
        )}

        {/* Contact and Footer Links */}
        <section
          id="contact"
          className="grid grid-cols-1 lg:grid-cols-12 gap-8"
        >
          <div className="lg:col-span-4">
            <SectionTitle
              icon={Mail}
              eyebrow={labels.contact}
              title={labels.contact}
              description={t(
                "facultyTechnology.contactDesc",
                "Faculty and department contact paths for students, applicants, and partners.",
              )}
            />
          </div>
          <div className="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-5">
            {/* Dean Contact Card */}
            <div className="bg-primary-light border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
              <h3 className="text-lg font-extrabold text-navy mb-4">
                {labels.deanContact}
              </h3>
              <div className="flex flex-col gap-3 text-sm font-semibold text-gray-500">
                {leadership.slice(0, 1).map((member, idx) => (
                  <React.Fragment key={idx}>
                    <p className="text-navy font-extrabold">{member.name}</p>
                    {member.phone && (
                      <a
                        href={telHref(member.phone)}
                        className="flex items-center gap-2 hover:text-primary transition-colors"
                      >
                        <Phone className="w-4 h-4 text-primary shrink-0" />
                        <span dir="ltr">{member.phone}</span>
                      </a>
                    )}
                    {member.email && (
                      <a
                        href={`mailto:${member.email}`}
                        className="flex items-center gap-2 hover:text-primary transition-colors min-w-0"
                      >
                        <Mail className="w-4 h-4 text-primary shrink-0" />
                        <span className="truncate">{member.email}</span>
                      </a>
                    )}
                  </React.Fragment>
                ))}
              </div>
            </div>

            <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
              <h3 className="text-lg font-extrabold text-navy mb-4">
                {labels.deputyDeanContacts}
              </h3>
              <div className="flex flex-col gap-4">
                {leadership.slice(1).map((member, idx) => (
                  <div
                    key={idx}
                    className="border-b border-gray-100 last:border-b-0 pb-4 last:pb-0"
                  >
                    <p className="text-sm font-extrabold text-navy">
                      {member.name}
                    </p>
                    <p className="text-[11px] font-bold text-primary uppercase tracking-wider mt-1">
                      {member.role}
                    </p>
                    <div className="flex flex-col gap-1.5 mt-3 text-xs font-semibold text-gray-500">
                      {member.phone && (
                        <a
                          href={telHref(member.phone)}
                          className="hover:text-primary transition-colors"
                          dir="ltr"
                        >
                          {member.phone}
                        </a>
                      )}
                      {member.email && (
                        <a
                          href={`mailto:${member.email}`}
                          className="hover:text-primary transition-colors truncate"
                        >
                          {member.email}
                        </a>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Quick Department Links */}
            <div className="md:col-span-2 bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
              <h3 className="text-lg font-extrabold text-navy mb-5">
                {labels.quickDepartmentLinks}
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                {facultyDepartmentsList.map((department) => (
                  <Link
                    key={department.slug}
                    to={department.route}
                    className="flex items-center justify-between gap-3 border border-gray-100 rounded-2xl px-4 py-3 text-sm font-bold text-gray-500 hover:text-primary hover:border-primary/20 transition-colors"
                  >
                    <span>
                      {t(
                        `departments.${department.slug}.name`,
                        department.name,
                      )}
                    </span>
                    <ArrowRight
                      className={`w-4 h-4 shrink-0 ${isRtl ? "rotate-180" : ""}`}
                    />
                  </Link>
                ))}
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  );
}
