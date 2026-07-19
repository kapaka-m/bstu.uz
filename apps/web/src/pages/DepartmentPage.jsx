import React, { useEffect, useMemo, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ArrowRight,
  Award,
  BookOpen,
  Building2,
  ChevronRight,
  Clock,
  ExternalLink,
  FileText,
  Globe,
  GraduationCap,
  Image as ImageIcon,
  Mail,
  Microscope,
  Phone,
  UserCheck,
  Users,
  Zap,
} from "lucide-react";
import DepartmentDetails from "./DepartmentDetails";
import {
  facultyTechnology,
  getTechnologyDepartmentByRoute,
  technologyDepartments,
} from "../data/facultyTechnology";
import { useLanguage } from "../context/LanguageContext";

const getLocalized = (value, language) => {
  if (!value || typeof value !== "object") return value;
  return value[language] || value.en || Object.values(value).find(Boolean) || "";
};

const getInitials = (name) => {
  if (!name) return "";
  const cleanName = name.replace(/^(Dr\.|Prof\.|Candidate|Associate|PhD|DSc|M\.Sc\.|B\.Sc\.)\s+/i, "");
  const parts = cleanName.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
};

const telHref = (phone) => `tel:${String(phone || "").replace(/[^\d+]/g, "")}`;

const shortText = (text, max = 260) => {
  if (!text) return "";
  return text.length > max ? `${text.slice(0, max).trim()}...` : text;
};

const scrubRenderedText = (text) =>
  String(text || "")
    .replace(/public\\assets\\img\\/g, "/assets/img/")
    .replace(/\\/g, "/")
    .replace(/^\s*(BULL OF THE DEPARTMENT|PROFESSOR-TEACHERS OF THE DEPARTMENT|PLATES FROM THE ACTIVITIES OF THE DEPARTMENT)\s*:?\s*$/gim, "")
    .replace(/^\s*(TEXTBOOKS AND MANUALS|MONOGRAPHS|MONOGRAPHS CREATED BY THE DEPARTMENT|TEXTBOOK AND ARTICLES)\s*:?\s*$/gim, "")
    .replace(/^\s*(SCIENTIFIC AND METHODICAL WORK OF THE DEPARTMENT, SCIENTIFIC ARTICLES|SCIENTIFIC AND METHODICAL WORK OF THE DEPARTMENT|ONGOING RESEARCH WORK OF THE DEPARTMENT|LEADING SCIENTIFIC WORK AT THE DEPARTMENT)\s*:?\s*$/gim, "")
    .replace(/^\s*(THE DEPARTMENT OFFERS COOPERATION WITH FOREIGN EDUCATIONAL INSTITUTIONS|PROSPECTIVE PLANS OF THE DEPARTMENT|ABSTRACTS AND PAPERS FROM THE CONFERENCES \(INTERNATIONAL AND REPUBLICAN\)|SCIENTIFIC WORKS INCLUDED IN SCOPUS AND WEB OF SCIENCE)\s*:?\s*$/gim, "")
    .replace(/\n{3,}/g, "\n\n")
    .trim();

const splitParagraphs = (value) => {
  if (Array.isArray(value)) return value.filter(Boolean);
  return scrubRenderedText(value)
    .split(/\n{2,}|(?<=\.)\s+(?=[A-ZА-ЯЁЎҚҒҲO‘G‘])/)
    .map((item) => item.trim())
    .filter(Boolean);
};

const extractRawSection = (raw, startPatterns, endPatterns) => {
  if (!raw) return "";
  const starts = Array.isArray(startPatterns) ? startPatterns : [startPatterns];
  const ends = Array.isArray(endPatterns) ? endPatterns : [endPatterns];
  const lowerRaw = raw.toLowerCase();

  let start = -1;
  let startLength = 0;
  for (const pattern of starts) {
    const index = lowerRaw.indexOf(String(pattern).toLowerCase());
    if (index !== -1 && (start === -1 || index < start)) {
      start = index;
      startLength = String(pattern).length;
    }
  }

  if (start === -1) return "";

  let end = -1;
  for (const pattern of ends) {
    const index = lowerRaw.indexOf(String(pattern).toLowerCase(), start + startLength);
    if (index !== -1 && (end === -1 || index < end)) end = index;
  }

  return scrubRenderedText(raw.slice(start + startLength, end === -1 ? undefined : end).trim());
};

const extractFirstRawSection = (raw, candidates) => {
  for (const candidate of candidates) {
    const section = extractRawSection(raw, candidate.starts, candidate.ends);
    if (section) return section;
  }
  return "";
};

const normalizePublicationGroups = (publications = {}) =>
  Object.entries(publications)
    .filter(([, items]) => Array.isArray(items) && items.length > 0)
    .map(([key, items]) => ({
      key,
      title: key
        .replace(/([A-Z])/g, " $1")
        .replace(/^./, (char) => char.toUpperCase())
        .replace(/Scopus Web Of Science/, "Scopus / Web of Science"),
      items,
    }));

function ImageWithFallback({ src, fallbackSrc, alt, className, initialsClassName }) {
  const [currentSrc, setCurrentSrc] = useState(src || fallbackSrc || "");
  const [failed, setFailed] = useState(!src && !fallbackSrc);

  if (failed) {
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

function DepartmentSection({ id, icon: Icon, title, description, children }) {
  if (!children) return null;

  return (
    <section id={id} className="flex flex-col gap-5 text-start">
      <div className="flex flex-col gap-2">
        <h2 className="text-xl md:text-2xl font-extrabold text-navy flex items-center gap-2">
          <Icon className="w-5 h-5 text-primary shrink-0" />
          {title}
        </h2>
        {description && (
          <p className="text-sm text-gray-500 leading-relaxed max-w-3xl">
            {description}
          </p>
        )}
      </div>
      {children}
    </section>
  );
}

function TextPanel({ paragraphs }) {
  const content = splitParagraphs(paragraphs);
  if (content.length === 0) return null;

  return (
    <div className="bg-gray-50 border border-gray-100 rounded-3xl p-6 md:p-8 text-gray-500 text-sm md:text-base leading-relaxed flex flex-col gap-4">
      {content.map((paragraph, index) => (
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
        <li key={`${String(item.name || item.title || item)}-${index}`} className="flex items-start gap-3 text-sm text-gray-500 font-semibold leading-relaxed">
          <span className={`${marker === "number" ? "w-7 h-7 text-[11px]" : "w-2 h-2 mt-2"} rounded-full bg-primary text-white shrink-0 flex items-center justify-center font-extrabold`}>
            {marker === "number" ? index + 1 : ""}
          </span>
          <span>
            {typeof item === "string" ? item : item.title || item.name}
          </span>
        </li>
      ))}
    </ul>
  );
}

function DepartmentHero({ department, labels, isRtl }) {
  const summary = shortText(
    department.history?.[0] ||
      department.rawContent?.original ||
      `${department.name} at the Faculty of Technology.`,
    360
  );

  return (
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
              <Link to="/" className="hover:text-primary transition-colors">{labels.home}</Link>
              <ChevronRight className="w-3.5 h-3.5 text-gray-300 shrink-0" />
              <span>{labels.faculties}</span>
              <ChevronRight className="w-3.5 h-3.5 text-gray-300 shrink-0" />
              <Link to={facultyTechnology.route} className="hover:text-primary transition-colors">
                {facultyTechnology.name}
              </Link>
              <ChevronRight className="w-3.5 h-3.5 text-gray-300 shrink-0" />
              <span className="text-gray-400 font-bold">{department.name}</span>
            </nav>

            <span className="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider text-primary bg-white/70 border border-white px-3 py-1.5 rounded-full mb-5">
              <Building2 className="w-4 h-4" />
              {labels.facultyTechnology}
            </span>
            <h1 className="text-3xl md:text-5xl font-extrabold text-navy leading-tight tracking-tight mb-5">
              {department.name}
            </h1>
            <p className="text-gray-500 text-sm md:text-lg leading-relaxed max-w-3xl">
              {summary}
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
                {department.contact?.name}
              </h2>
              <div className="flex flex-col gap-2 mt-4 text-xs font-semibold text-gray-500">
                {department.contact?.phone && (
                  <a href={telHref(department.contact.phone)} className="flex items-center gap-2 hover:text-primary transition-colors">
                    <Phone className="w-4 h-4 text-primary shrink-0" />
                    <span dir="ltr">{department.contact.phone}</span>
                  </a>
                )}
                {department.contact?.email && (
                  <a href={`mailto:${department.contact.email}`} className="flex items-center gap-2 hover:text-primary transition-colors min-w-0">
                    <Mail className="w-4 h-4 text-primary shrink-0" />
                    <span className="truncate">{department.contact.email}</span>
                  </a>
                )}
                {department.contact?.reception && (
                  <span className="flex items-start gap-2">
                    <Clock className="w-4 h-4 text-primary shrink-0 mt-0.5" />
                    <span>{department.contact.reception}</span>
                  </span>
                )}
              </div>
              <div className="mt-6">
                <Link
                  to={facultyTechnology.route}
                  className="inline-flex items-center gap-2 text-xs font-extrabold text-primary hover:text-primary-hover transition-colors"
                >
                  {labels.backToFaculty}
                  <ArrowRight className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`} />
                </Link>
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}

function DepartmentContactCard({ department, labels }) {
  const contact = department.contact || {};

  return (
    <div className="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm flex flex-col md:flex-row items-center md:items-start gap-6 text-start">
      <ImageWithFallback
        src={contact.image}
        fallbackSrc={contact.fallbackImage}
        alt={contact.name}
        className="w-28 h-28 rounded-full object-cover border-4 border-white shadow-md shrink-0"
        initialsClassName="w-28 h-28 rounded-full bg-linear-to-br from-primary to-primary-hover text-white shadow-md flex items-center justify-center font-extrabold text-xl border-4 border-white shrink-0"
      />
      <div className="grow text-center md:text-start min-w-0">
        <p className="text-primary text-[11px] font-extrabold uppercase tracking-wider mb-2">
          {contact.role || labels.headOfDepartment}
        </p>
        <h2 className="text-xl font-extrabold text-navy leading-snug">{contact.name}</h2>
        {contact.title && (
          <p className="text-sm text-gray-500 font-semibold mt-2">{contact.title}</p>
        )}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5 pt-5 border-t border-gray-100 text-xs font-semibold text-gray-500">
          {contact.reception && (
            <span className="flex items-start justify-center md:justify-start gap-2">
              <Clock className="w-4 h-4 text-primary shrink-0 mt-0.5" />
              <span>{contact.reception}</span>
            </span>
          )}
          {contact.phone && (
            <a href={telHref(contact.phone)} className="flex items-center justify-center md:justify-start gap-2 hover:text-primary transition-colors">
              <Phone className="w-4 h-4 text-primary shrink-0" />
              <span dir="ltr">{contact.phone}</span>
            </a>
          )}
          {contact.email && (
            <a href={`mailto:${contact.email}`} className="flex items-center justify-center md:justify-start gap-2 hover:text-primary transition-colors min-w-0">
              <Mail className="w-4 h-4 text-primary shrink-0" />
              <span className="truncate">{contact.email}</span>
            </a>
          )}
        </div>
      </div>
    </div>
  );
}

function DepartmentProgramList({ programs, labels, t }) {
  if (!programs || programs.length === 0) return null;

  const grouped = programs.reduce((acc, item) => {
    const key = item.level || "programs";
    acc[key] = acc[key] || [];
    acc[key].push(item);
    return acc;
  }, {});

  const groupLabels = {
    bachelor: labels.bachelor,
    master: labels.master,
    doctoral: labels.doctoral,
    phd: labels.doctoral,
    programs: labels.programs,
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
      {Object.entries(grouped).map(([level, items]) => (
        <div key={level} className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
          <h3 className="font-extrabold text-navy mb-4 flex items-center gap-2">
            <GraduationCap className="w-4 h-4 text-primary" />
            {groupLabels[level] || level}
          </h3>
          <ul className="flex flex-col gap-3">
            {items.map((item, index) => {
              const programName = item.id ? t(`programs.${item.id}.name`, item.name) : item.name;
              const content = (
                <>
                  <span className="text-[11px] font-extrabold text-primary bg-primary/10 rounded-lg px-2 py-1 shrink-0">
                    {item.code}
                  </span>
                  <span>{programName}</span>
                </>
              );

              return (
                <li key={`${item.code}-${item.name}-${index}`} className="text-sm text-gray-500 font-semibold leading-relaxed">
                  {item.route ? (
                    <Link to={item.route} className="flex gap-3 items-start hover:text-primary transition-colors">
                      {content}
                    </Link>
                  ) : (
                    <span className="flex gap-3 items-start">{content}</span>
                  )}
                </li>
              );
            })}
          </ul>
        </div>
      ))}
    </div>
  );
}

function DepartmentSubjectsAccordion({ subjects, labels }) {
  if (!subjects) return null;

  const groups = Object.entries(subjects).filter(([, items]) => Array.isArray(items) && items.length > 0);
  if (groups.length === 0) return null;

  const groupLabels = {
    bachelor: labels.bachelorSubjects,
    master: labels.masterSubjects,
  };

  return (
    <div className="flex flex-col gap-4">
      {groups.map(([key, items], index) => (
        <details key={key} className="group bg-white border border-gray-100 rounded-3xl shadow-sm overflow-hidden" open={index === 0}>
          <summary className="cursor-pointer list-none p-5 md:p-6 flex items-center justify-between gap-4">
            <span className="font-extrabold text-navy flex items-center gap-2">
              <BookOpen className="w-4 h-4 text-primary" />
              {groupLabels[key] || key}
            </span>
            <span className="text-[11px] font-extrabold text-primary bg-primary/10 rounded-full px-3 py-1">
              {items.length}
            </span>
          </summary>
          <div className="px-5 md:px-6 pb-6 border-t border-gray-100">
            <ul className="grid grid-cols-1 md:grid-cols-2 gap-3 pt-5">
              {items.map((subject, subjectIndex) => (
                <li key={`${subject}-${subjectIndex}`} className="flex items-start gap-2.5 text-xs md:text-sm text-gray-500 font-semibold leading-relaxed">
                  <span className="w-1.5 h-1.5 rounded-full bg-primary mt-2 shrink-0" />
                  <span>{subject}</span>
                </li>
              ))}
            </ul>
          </div>
        </details>
      ))}
    </div>
  );
}

function DepartmentStaffGrid({ staff, labels }) {
  if (!staff || staff.length === 0) return null;

  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
      {staff.map((member, index) => (
        <article key={`${member.name}-${index}`} className="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex gap-3 items-center text-start">
          <ImageWithFallback
            src={member.image}
            fallbackSrc={member.fallbackImage}
            alt={member.name}
            className="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm shrink-0"
            initialsClassName="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm shrink-0"
          />
          <div className="min-w-0">
            <h3 className="text-sm font-extrabold text-navy leading-snug">{member.name}</h3>
            <p className="text-[11px] text-gray-500 font-semibold leading-relaxed mt-1">
              {member.title || member.role || labels.staff}
            </p>
          </div>
        </article>
      ))}
    </div>
  );
}

function DepartmentPublications({ publications, labels }) {
  const groups = normalizePublicationGroups(publications);
  if (groups.length === 0) return null;

  return (
    <div className="flex flex-col gap-4">
      {groups.map((group, index) => (
        <details key={group.key} className="group bg-white border border-gray-100 rounded-3xl shadow-sm overflow-hidden" open={index === 0}>
          <summary className="cursor-pointer list-none p-5 md:p-6 flex items-center justify-between gap-4">
            <span className="font-extrabold text-navy flex items-center gap-2">
              <FileText className="w-4 h-4 text-primary" />
              {labels.publicationGroups[group.key] || group.title}
            </span>
            <span className="text-[11px] font-extrabold text-primary bg-primary/10 rounded-full px-3 py-1">
              {group.items.length}
            </span>
          </summary>
          <div className="px-5 md:px-6 pb-6 border-t border-gray-100">
            <ul className="flex flex-col gap-3 pt-5">
              {group.items.map((item, itemIndex) => (
                <li key={`${item.title}-${itemIndex}`} className="flex items-start gap-3 text-xs md:text-sm text-gray-500 font-semibold leading-relaxed">
                  <span className="w-1.5 h-1.5 rounded-full bg-primary mt-2 shrink-0" />
                  <span>{item.title || item}</span>
                </li>
              ))}
            </ul>
          </div>
        </details>
      ))}
    </div>
  );
}

function DepartmentGallery({ images, labels }) {
  if (!images || images.length === 0) return null;

  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      {images.map((image, index) => (
        <div key={`${image.src}-${index}`} className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm">
          <ImageWithFallback
            src={image.src}
            fallbackSrc={image.fallback}
            alt={image.alt || labels.gallery}
            className="w-full aspect-4/3 object-cover"
            initialsClassName="w-full aspect-4/3 bg-primary-light text-primary flex items-center justify-center"
          />
          {image.caption && (
            <p className="p-4 text-xs font-semibold text-gray-500">{image.caption}</p>
          )}
        </div>
      ))}
    </div>
  );
}

export default function DepartmentPage() {
  const { id } = useParams();
  const { t, language } = useLanguage();
  const isRtl = language === "ar";
  const department = getTechnologyDepartmentByRoute(id);

  const labels = useMemo(() => ({
    home: t("nav.home", "Home"),
    faculties: t("common.faculties", "Faculties"),
    facultyTechnology: getLocalized(facultyTechnology.title, language),
    quickContact: t("common.quickContact", "Quick Contact"),
    backToFaculty: t("common.backToFaculty", "Back to Faculty"),
    headOfDepartment: t("common.headOfDepartment", "Head of Department"),
    history: t("common.departmentHistory", "Kafedra Tarixi / History"),
    preparedSpecialists: t("common.academicPrograms", "Prepared Specialists"),
    subjects: t("common.curriculumSubjects", "Taught Subjects"),
    staff: t("common.departmentStaff", "Professor-Teachers"),
    publications: t("common.publicationsTextbooks", "Textbooks / Manuals / Publications"),
    research: t("common.researchInnovation", "Ongoing Research"),
    cooperation: t("common.internationalCooperation", "Cooperation / International Relations"),
    activities: t("common.departmentActivities", "News / Activities / Prospective Plans"),
    source: t("facultyTechnology.source", "Original department source"),
    bachelor: t("common.bachelorPrograms", "Bachelor's degree"),
    master: t("common.masterPrograms", "Master's degree / Judiciary"),
    doctoral: t("facultyTechnology.doctoral", "Doctoral / PhD"),
    programs: t("common.programs", "Programs"),
    bachelorSubjects: t("facultyTechnology.bachelorSubjects", "Bachelor subjects"),
    masterSubjects: t("facultyTechnology.masterSubjects", "Master subjects"),
    gallery: t("facultyTechnology.gallery", "Gallery"),
    publicationGroups: {
      conferencePapers: t("common.conferenceArticles", "Conference Papers"),
      scopusWebOfScience: t("common.scopusArticles", "Scopus / Web of Science Works"),
      textbooksManuals: t("common.textbooks", "Textbooks and Manuals"),
      textbooksManualsMonographs: t("common.publicationsTextbooks", "Textbooks, Manuals and Monographs"),
      monographs: t("facultyTechnology.monographs", "Monographs"),
      articles: t("facultyTechnology.scientificArticles", "Scientific Articles"),
    },
  }), [language, t]);

  useEffect(() => {
    if (!department) return;
    window.scrollTo(0, 0);
    document.title = `${department.name} | BSTU`;
  }, [department]);

  if (!department) {
    return <DepartmentDetails />;
  }

  const fullHistory = extractRawSection(
    department.rawContent?.original,
    ["Kafedra Tarixi", "Kafedra tarixi"],
    ["Prepared specialists of the department", "PROFESSOR-TEACHERS OF THE DEPARTMENT"]
  ) || department.history;

  const rawPublicationText = extractFirstRawSection(department.rawContent?.original, [
    {
      starts: ["Textbook and articles", "Darslik va o‘quv qo‘llanmalar", "Darslik va o'quv qo'llanmalar"],
      ends: [
        "SCIENTIFIC AND METHODICAL WORK",
        "KAFEDRADA OLIB BORILAYOTGAN",
        "THE DEPARTMENT OFFERS COOPERATION",
        "KAFEDRA XORIJIY",
        "News",
        "KAFEDRANING ISTIQBOLLI REJALARI",
      ],
    },
    {
      starts: ["TEXTBOOKS AND MANUALS", "TEXTBOOKS AND MANUALS", "Monographs created by the department"],
      ends: [
        "SCIENTIFIC AND METHODICAL WORK",
        "KAFEDRADA OLIB BORILAYOTGAN",
        "THE DEPARTMENT OFFERS COOPERATION",
        "KAFEDRA XORIJIY",
        "News",
        "KAFEDRANING ISTIQBOLLI REJALARI",
      ],
    },
  ]);

  const scientificCouncilText = extractRawSection(
    department.rawContent?.original,
    "Department of Oil and Gas Refining Technology Bukhara State Technical University Scientific council",
    "THE DEPARTMENT OFFERS COOPERATION WITH FOREIGN EDUCATIONAL INSTITUTIONS"
  );

  const rawResearchText = extractFirstRawSection(department.rawContent?.original, [
    {
      starts: [
        "SCIENTIFIC AND METHODICAL WORK OF THE DEPARTMENT",
        "SCIENTIFIC AND METHODICAL WORK",
        "KAFEDRADA OLIB BORILAYOTGAN ILMIY-TADQIQOT ISHLARI",
        "KAFEDRADA OLIB BORILAYOTGAN ILMIY-USLUBIY ISHLAR",
      ],
      ends: [
        "THE DEPARTMENT OFFERS COOPERATION",
        "KAFEDRA XORIJIY",
        "KAFEDRANING ISTIQBOLLI REJALARI",
        "JOINT PROGRAMS",
        "News",
        "والصور",
      ],
    },
    {
      starts: ["LEADING SCIENTIFIC WORK AT THE DEPARTMENT", "LEADING SCIENTIFIC WORK AT THE DEPARTMENT:"],
      ends: ["TEXTBOOKS AND MANUALS", "Monographs created by the department", "THE DEPARTMENT OFFERS COOPERATION", "KAFEDRA XORIJIY", "والصور"],
    },
  ]);

  const cooperationText = extractFirstRawSection(department.rawContent?.original, [
    {
      starts: [
        "THE DEPARTMENT OFFERS COOPERATION WITH FOREIGN EDUCATIONAL INSTITUTIONS",
        "KAFEDRA XORIJIY TA’LIM MUASSASALARI BILAN HAMKORLIK",
        "KAFEDRA XORIJIY TA'LIM MUASSASALARI BILAN HAMKORLIK",
      ],
      ends: ["JOINT PROGRAMS", "KAFEDRANING ISTIQBOLLI REJALARI", "News", "والصور"],
    },
    {
      starts: ["JOINT PROGRAMS"],
      ends: ["KAFEDRANING ISTIQBOLLI REJALARI", "News", "والصور"],
    },
  ]);

  const rawActivityText = extractFirstRawSection(department.rawContent?.original, [
    {
      starts: ["KAFEDRANING ISTIQBOLLI REJALARI", "PROSPECTIVE PLANS", "News", "NEWS"],
      ends: ["والصور", "والقسم", "القسم"],
    },
  ]);

  const activityItems = [
    ...(department.activities || []),
    ...(department.news || []),
    ...(department.prospectivePlans || []),
  ];

  const researchItems = [
    ...(department.projects || []),
    ...(department.research || []),
    ...(department.scientificMethodicalWorks || []),
    ...(department.scientificArticleCounts || []),
  ];

  if (scientificCouncilText) {
    researchItems.push(scientificCouncilText);
  }

  const cooperationItems = [
    ...(department.cooperation || []),
    ...(department.jointPrograms || []),
  ];
  const hasPublications = normalizePublicationGroups(department.publications).length > 0 || Boolean(rawPublicationText);

  return (
    <div className="pt-20 bg-white" dir={isRtl ? "rtl" : "ltr"}>
      <DepartmentHero department={department} labels={labels} isRtl={isRtl} />

      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">
          <aside className="lg:col-span-4 flex flex-col gap-6">
            <DepartmentContactCard department={department} labels={labels} />

            <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
              <h2 className="text-lg font-extrabold text-navy mb-4">{labels.facultyTechnology}</h2>
              <div className="flex flex-col gap-2">
                {technologyDepartments.map((item) => (
                  <Link
                    key={item.slug}
                    to={item.route}
                    className={`text-xs font-bold p-3 rounded-xl transition-all ${
                      item.slug === department.slug
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
            <DepartmentSection id="history" icon={Clock} title={labels.history}>
              <TextPanel paragraphs={fullHistory} />
            </DepartmentSection>

            <DepartmentSection id="programs" icon={GraduationCap} title={labels.preparedSpecialists}>
              <DepartmentProgramList programs={department.preparedSpecialists} labels={labels} t={t} />
            </DepartmentSection>

            <DepartmentSection id="subjects" icon={BookOpen} title={labels.subjects}>
              <DepartmentSubjectsAccordion subjects={department.subjects} labels={labels} />
            </DepartmentSection>

            <DepartmentSection id="staff" icon={Users} title={labels.staff}>
              <DepartmentStaffGrid staff={department.staff} labels={labels} />
            </DepartmentSection>

            {hasPublications && (
              <DepartmentSection id="publications" icon={Award} title={labels.publications}>
                <div className="flex flex-col gap-5">
                  <DepartmentPublications publications={department.publications} labels={labels} />
                  {rawPublicationText && <TextPanel paragraphs={rawPublicationText} />}
                </div>
              </DepartmentSection>
            )}

            {(researchItems.length > 0 || rawResearchText) && (
              <DepartmentSection id="research" icon={Microscope} title={labels.research}>
                <div className="flex flex-col gap-5">
                  {researchItems.length > 0 && <ListPanel items={researchItems} marker="number" />}
                  {rawResearchText && <TextPanel paragraphs={rawResearchText} />}
                </div>
              </DepartmentSection>
            )}

            {(cooperationItems.length > 0 || cooperationText) && (
              <DepartmentSection id="cooperation" icon={Globe} title={labels.cooperation}>
                <div className="flex flex-col gap-5">
                  {cooperationText && <TextPanel paragraphs={cooperationText} />}
                  {cooperationItems.length > 0 && <ListPanel items={cooperationItems} />}
                </div>
              </DepartmentSection>
            )}

            {(activityItems.length > 0 || rawActivityText) && (
              <DepartmentSection id="activities" icon={Zap} title={labels.activities}>
                <div className="flex flex-col gap-5">
                  {activityItems.length > 0 && <ListPanel items={activityItems} marker="number" />}
                  {rawActivityText && <TextPanel paragraphs={rawActivityText} />}
                </div>
              </DepartmentSection>
            )}

            {department.gallery?.length > 0 && (
              <DepartmentSection id="gallery" icon={ImageIcon} title={labels.gallery}>
                <DepartmentGallery images={department.gallery} labels={labels} />
              </DepartmentSection>
            )}

            {department.sourceLink && (
              <DepartmentSection id="source" icon={ExternalLink} title={labels.source}>
                <a
                  href={department.sourceLink}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex w-fit items-center gap-2 bg-primary text-white px-5 py-3 rounded-xl text-sm font-extrabold hover:bg-primary-hover transition-colors"
                >
                  {labels.source}
                  <ExternalLink className="w-4 h-4" />
                </a>
              </DepartmentSection>
            )}
          </main>
        </div>
      </div>
    </div>
  );
}
