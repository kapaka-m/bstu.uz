import React, { useEffect, useMemo, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ArrowRight,
  Award,
  BookOpen,
  Building2,
  Clock,
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
import { useLanguage } from "../context/LanguageContext";
import { departmentService } from "../services/departmentService";
import { departmentPageCmsService } from "../services/departmentPageCmsService";

const sectionItems = (sections, key) => {
  const section = (sections || []).find((item) => item.key === key);
  if (!section) return [];
  if (Array.isArray(section.items)) return section.items;
  if (typeof section.items === "string") return splitParagraphs(section.items);
  return [];
};

const textSection = (sections, key) => {
  const items = sectionItems(sections, key);
  return items.map((item) => (typeof item === "string" ? item : item.title || item.name || "")).filter(Boolean).join("\n\n");
};

const meaningfulText = (value) => {
  const text = normalizeText(value);
  if (!text || text.length < 12) return "";
  return text;
};

const splitNumberedItems = (value) => {
  const text = normalizeText(value)
    .replace(/\bBachelor'?s?\s+Degree\s*:/gi, "")
    .replace(/\bBachelor\s+subjects\s*:?\s*/gi, "")
    .replace(/\bMaster'?s?\s+Degree\s*:/gi, "")
    .replace(/\bMaster\s+subjects\s*:?\s*/gi, "")
    .replace(/\bJudiciary\s*:?\s*/gi, "");

  if (!text) return [];

  return text
    .replace(/\s+/g, " ")
    .split(/(?=\b\d+\.\s+)/)
    .map((item) => item.replace(/^\d+\.\s*/, "").trim())
    .filter((item) => item.length > 2);
};

const cleanSubjectItem = (value) =>
  normalizeText(typeof value === "string" ? value : value?.title || value?.name || "")
    .replace(/\bBachelor'?s?\s+Degree\s*:/gi, "")
    .replace(/\bBachelor\s+subjects\s*:?\s*/gi, "")
    .replace(/\bMaster'?s?\s+Degree\s*:/gi, "")
    .replace(/\bMaster\s+subjects\s*:?\s*/gi, "")
    .replace(/\bJudiciary\s*:?\s*/gi, "")
    .trim();

const subjectGroupsFromSections = (sections) => {
  const section = (sections || []).find((item) => item.key === "subjects");
  if (section && (Array.isArray(section.bachelor) || Array.isArray(section.master))) {
    const groups = {
      bachelor: (section.bachelor || []).map(cleanSubjectItem).filter(Boolean),
      master: (section.master || []).map(cleanSubjectItem).filter(Boolean),
    };

    return Object.fromEntries(Object.entries(groups).filter(([, items]) => items.length > 0));
  }

  const subjectItems = sectionItems(sections, "subjects")
    .map((item) => normalizeText(typeof item === "string" ? item : item?.title || item?.name || ""))
    .filter(Boolean)
    .filter((item) => item.length > 2);

  if (subjectItems.length === 0) return {};

  const subjectText = subjectItems.join("\n\n");

  if (subjectItems.length > 1 && !/\b(Judiciary|Master'?s?\s+Degree|Master\s+subjects)\b/i.test(subjectText)) {
    return { bachelor: subjectItems };
  }

  const normalized = normalizeText(subjectText);
  const masterMatch = normalized.match(/\b(Judiciary|Master'?s?\s+Degree|Master\s+subjects)\b/i);
  const bachelorText = masterMatch ? normalized.slice(0, masterMatch.index) : normalized;
  const masterText = masterMatch ? normalized.slice(masterMatch.index) : "";

  const groups = {
    bachelor: splitNumberedItems(bachelorText),
    master: splitNumberedItems(masterText),
  };

  return Object.fromEntries(Object.entries(groups).filter(([, items]) => items.length > 0));
};

const isEmailAddress = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || "").trim());

const personKey = (value) =>
  String(value || "")
    .replace(/[‘’`ʼ]/g, "'")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "");

const normalizeApiDepartment = (data) => {
  if (!data) return null;
  const sections = data.content_sections || [];
  const staff = data.staff || [];
  const head = staff.find((member) => member.slug === data.head_profile_slug)
    || staff.find((member) => personKey(member.full_name || member.name) === personKey(data.head_name))
    || staff.find((member) => data.email && member.email === data.email)
    || staff[0]
    || null;
  const description = meaningfulText(data.description) || meaningfulText(textSection(sections, "history")) || meaningfulText(textSection(sections, "overview"));

  const preparedSpecialists = (data.programs || []).map((program) => ({
    id: program.slug,
    code: program.display_code || program.official_code || program.code || "",
    name: program.name || "",
    level: String(program.degree || "programs").toLowerCase(),
    route: `/programs/${program.slug}`,
  }));

  return {
    ...data,
    id: data.slug,
    slug: data.slug,
    name: data.name || "",
    route: `/department/${data.slug}`,
    facultyRoute: data.faculty?.slug ? `/faculty/${data.faculty.slug}` : "/",
    facultyName: data.faculty?.name || "",
    history: description,
    rawContent: { original: description },
    contact: {
      name: data.head_name || head?.full_name || "",
      route: (data.head_profile_slug || head?.slug) ? `/profile/${data.head_profile_slug || head?.slug}` : "",
      role: head?.position || "",
      reception: data.reception_time || head?.office || "",
      phone: data.phone || head?.phone || "",
      email: data.email || head?.email || "",
      image: data.head_profile_photo_url || data.head_profile_photo || head?.photo_url || head?.photo || "",
      fallbackImage: null,
    },
    preparedSpecialists,
    subjects: subjectGroupsFromSections(sections),
    staff: (data.staff || []).map((member) => ({
      slug: member.slug,
      route: member.slug ? `/profile/${member.slug}` : "",
      name: member.full_name || member.name || "",
      title: member.position || "",
      image: member.photo_url || member.photo || "",
      fallbackImage: null,
    })),
    publications: {
      textbooksManualsMonographs: sectionItems(sections, "publications"),
    },
    research: sectionItems(sections, "research"),
    cooperation: sectionItems(sections, "cooperation"),
    prospectivePlans: sectionItems(sections, "plans"),
    activities: [],
    news: [],
    gallery: [],
    dynamicSections: sections,
    rawPreparedSpecialistsText: preparedSpecialists.length > 0 ? [] : textSection(sections, "prepared_specialists"),
    rawPublicationText: textSection(sections, "publications"),
    rawResearchText: textSection(sections, "research"),
    cooperationText: textSection(sections, "cooperation"),
    rawActivityText: textSection(sections, "plans"),
  };
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

const normalizeText = (text) =>
  String(text || "")
    .replace(/\\/g, "/")
    .replace(/\n{3,}/g, "\n\n")
    .trim();

const splitParagraphs = (value) => {
  if (Array.isArray(value)) return value.filter(Boolean);
  return normalizeText(value)
    .split(/\n{2,}|(?<=\.)\s+(?=[A-ZА-ЯЁЎҚҒҲO‘G‘])/)
    .map((item) => item.trim())
    .filter(Boolean);
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

  useEffect(() => {
    setCurrentSrc(src || fallbackSrc || "");
    setFailed(!src && !fallbackSrc);
  }, [src, fallbackSrc]);

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
    <section id={id} className="flex flex-col gap-5 text-start min-w-0">
      <div className="flex flex-col gap-2">
        <h2 className="text-xl md:text-2xl font-extrabold text-navy flex items-start gap-2 min-w-0 break-words">
          <Icon className="w-5 h-5 text-primary shrink-0" />
          <span className="min-w-0 break-words">{title}</span>
        </h2>
        {description && (
          <p className="text-sm text-gray-500 leading-relaxed max-w-3xl break-words">
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
    <div className="bg-gray-50 border border-gray-100 rounded-3xl p-6 md:p-8 text-gray-500 text-sm md:text-base leading-relaxed flex flex-col gap-4 min-w-0">
      {content.map((paragraph, index) => (
        <p key={index} className="whitespace-pre-line break-words">{paragraph}</p>
      ))}
    </div>
  );
}

function ListPanel({ items, marker = "dot" }) {
  if (!items || items.length === 0) return null;

  return (
    <ul className="flex flex-col gap-3 bg-gray-50 border border-gray-100 rounded-3xl p-6 md:p-8">
      {items.map((item, index) => (
        <li key={`${String(item.name || item.title || item)}-${index}`} className="flex items-start gap-3 text-sm text-gray-500 font-semibold leading-relaxed min-w-0">
          <span className={`${marker === "number" ? "w-7 h-7 text-[11px]" : "w-2 h-2 mt-2"} rounded-full bg-primary text-white shrink-0 flex items-center justify-center font-extrabold`}>
            {marker === "number" ? index + 1 : ""}
          </span>
          <span className="min-w-0 break-words">
            {typeof item === "string" ? item : item.title || item.name}
          </span>
        </li>
      ))}
    </ul>
  );
}

function DepartmentHero({ department, labels, isRtl }) {
  const summary = shortText(
    department.history ||
      department.rawContent?.original ||
      department.name,
    360
  );

  return (
    <section className="bg-primary-light border-b border-gray-100 overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-14 md:py-20">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center min-w-0">
          <motion.div
            initial={{ opacity: 0, y: 18 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.45 }}
            className="lg:col-span-8 text-start min-w-0"
          >
            <span className="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider text-primary bg-white/70 border border-white px-3 py-1.5 rounded-full mb-5">
              <Building2 className="w-4 h-4" />
              {labels.facultyTechnology}
            </span>
            <h1 className="text-3xl md:text-5xl font-extrabold text-navy leading-tight tracking-tight mb-5 break-words">
              {department.name}
            </h1>
            <p className="text-gray-500 text-sm md:text-lg leading-relaxed max-w-3xl break-words">
              {summary}
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 18 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.45, delay: 0.08 }}
            className="lg:col-span-4"
          >
            <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start min-w-0">
              <div className="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-5">
                <UserCheck className="w-7 h-7" />
              </div>
              <p className="text-[11px] font-extrabold text-primary uppercase tracking-wider mb-2">
                {labels.quickContact}
              </p>
              <h2 className="text-lg font-extrabold text-navy leading-snug break-words">
                {department.contact?.route ? (
                  <Link to={department.contact.route} className="hover:text-primary transition-colors">
                    {department.contact.name}
                  </Link>
                ) : (
                  department.contact?.name
                )}
              </h2>
              <div className="flex flex-col gap-2 mt-4 text-xs font-semibold text-gray-500">
                {department.contact?.phone && (
                  <a href={telHref(department.contact.phone)} className="flex items-center gap-2 hover:text-primary transition-colors">
                    <Phone className="w-4 h-4 text-primary shrink-0" />
                    <span dir="ltr">{department.contact.phone}</span>
                  </a>
                )}
                {isEmailAddress(department.contact?.email) && (
                  <a href={`mailto:${department.contact.email}`} className="flex items-center gap-2 hover:text-primary transition-colors min-w-0">
                    <Mail className="w-4 h-4 text-primary shrink-0" />
                    <span className="break-all" dir="ltr">{department.contact.email}</span>
                  </a>
                )}
                {department.contact?.reception && (
                  <span className="flex items-start gap-2">
                    <Clock className="w-4 h-4 text-primary shrink-0 mt-0.5" />
                    <span className="break-words">{department.contact.reception}</span>
                  </span>
                )}
              </div>
              <div className="mt-6">
                <Link
                  to={department.facultyRoute}
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
  const role = contact.role && contact.role !== labels.headOfDepartment ? contact.role : "";

  return (
    <div className="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm text-start min-w-0">
      <div className="flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <ImageWithFallback
          src={contact.image}
          fallbackSrc={contact.fallbackImage}
          alt={contact.name}
          className="w-20 h-20 rounded-2xl object-cover border border-gray-100 shadow-sm shrink-0"
          initialsClassName="w-20 h-20 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-extrabold text-lg shrink-0"
        />
        <div className="grow text-center sm:text-start min-w-0">
          <p className="text-primary text-[11px] font-extrabold uppercase tracking-wider mb-2">
            {labels.headOfDepartment}
          </p>
          <h2 className="text-xl md:text-2xl font-extrabold text-navy leading-snug break-words">
            {contact.route ? (
              <Link to={contact.route} className="hover:text-primary transition-colors">
                {contact.name}
              </Link>
            ) : (
              contact.name
            )}
          </h2>
          {role && (
            <p className="text-sm text-gray-500 font-semibold mt-2 break-words">{role}</p>
          )}
        </div>
      </div>

      <div className="flex flex-col gap-3 mt-6 pt-5 border-t border-gray-100 text-xs md:text-sm font-semibold text-gray-500">
          {contact.phone && (
            <a href={telHref(contact.phone)} className="flex items-center gap-2 rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3 hover:text-primary transition-colors min-w-0">
              <Phone className="w-4 h-4 text-primary shrink-0" />
              <span dir="ltr" className="whitespace-nowrap max-w-full overflow-hidden text-ellipsis">{contact.phone}</span>
            </a>
          )}
          {isEmailAddress(contact.email) && (
            <a href={`mailto:${contact.email}`} className="flex items-center gap-2 rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3 hover:text-primary transition-colors min-w-0">
              <Mail className="w-4 h-4 text-primary shrink-0" />
              <span className="min-w-0 break-all leading-relaxed" dir="ltr">{contact.email}</span>
            </a>
          )}
          {contact.reception && (
            <span className="flex items-start gap-2 rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3 min-w-0">
              <Clock className="w-4 h-4 text-primary shrink-0 mt-0.5" />
              <span className="leading-relaxed break-words">{contact.reception}</span>
            </span>
          )}
      </div>
    </div>
  );
}

function DepartmentProgramList({ programs, labels }) {
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
    <div className="flex flex-col gap-6">
      {Object.entries(grouped).map(([level, items]) => (
        <div key={level} className="flex flex-col gap-4 min-w-0">
          <h3 className="font-extrabold text-navy flex items-center gap-2 min-w-0">
            <GraduationCap className="w-4 h-4 text-primary" />
            <span className="min-w-0 break-words">{groupLabels[level] || level}</span>
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {items.map((item, index) => {
              const programName = item.name || "";
              const content = (
                <>
                  <div className="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <GraduationCap className="w-5 h-5" />
                  </div>
                  <div className="min-w-0">
                    {item.code && (
                      <span className="text-[11px] font-extrabold text-primary uppercase tracking-wider">
                        {item.code}
                      </span>
                    )}
                    <h4 className="text-sm md:text-base font-bold text-navy leading-snug mt-1 break-words">
                      {programName}
                    </h4>
                    <p className="text-xs font-semibold text-gray-400 mt-2">
                      {groupLabels[level] || labels.programs}
                    </p>
                  </div>
                </>
              );

              return item.route ? (
                <Link
                  key={`${item.code}-${item.name}-${index}`}
                  to={item.route}
                  className="bg-white border border-gray-100 hover:border-primary/25 rounded-2xl p-5 shadow-sm flex gap-4 items-start transition-colors text-start w-full min-w-0"
                >
                  {content}
                </Link>
              ) : (
                <article
                  key={`${item.code}-${item.name}-${index}`}
                  className="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex gap-4 items-start text-start w-full min-w-0"
                >
                  {content}
                </article>
              );
            })}
          </div>
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
          <summary className="cursor-pointer list-none p-5 md:p-6 flex items-center justify-between gap-4 min-w-0">
            <span className="font-extrabold text-navy flex items-center gap-2 min-w-0">
              <BookOpen className="w-4 h-4 text-primary" />
              <span className="min-w-0 break-words">{groupLabels[key] || key}</span>
            </span>
            <span className="text-[11px] font-extrabold text-primary bg-primary/10 rounded-full px-3 py-1">
              {items.length}
            </span>
          </summary>
          <div className="px-5 md:px-6 pb-6 border-t border-gray-100">
            <ul className="grid grid-cols-1 md:grid-cols-2 gap-3 pt-5">
              {items.map((subject, subjectIndex) => (
                <li key={`${subject}-${subjectIndex}`} className="flex items-start gap-2.5 text-xs md:text-sm text-gray-500 font-semibold leading-relaxed min-w-0">
                  <span className="w-1.5 h-1.5 rounded-full bg-primary mt-2 shrink-0" />
                  <span className="min-w-0 break-words">{subject}</span>
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
        <article key={`${member.name}-${index}`} className="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex gap-3 items-center text-start min-w-0">
          <ImageWithFallback
            src={member.image}
            fallbackSrc={member.fallbackImage}
            alt={member.name}
            className="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm shrink-0"
            initialsClassName="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-extrabold text-sm shrink-0"
          />
          <div className="min-w-0">
            <h3 className="text-sm font-extrabold text-navy leading-snug break-words">
              {member.route ? (
                <Link to={member.route} className="hover:text-primary transition-colors">
                  {member.name}
                </Link>
              ) : (
                member.name
              )}
            </h3>
            <p className="text-[11px] text-gray-500 font-semibold leading-relaxed mt-1 break-words">
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

  if (groups.length === 1) {
    const [group] = groups;

    return (
      <div className="bg-white border border-gray-100 rounded-3xl shadow-sm overflow-hidden">
        <div className="px-5 md:px-6 py-4 border-b border-gray-100 flex justify-end">
          <span className="text-[11px] font-extrabold text-primary bg-primary/10 rounded-full px-3 py-1">
            {group.items.length}
          </span>
        </div>
        <ul className="flex flex-col gap-3 p-5 md:p-6">
          {group.items.map((item, itemIndex) => (
            <li
              key={`${item.title || item}-${itemIndex}`}
              className="flex items-start gap-3 rounded-2xl border border-gray-100 bg-gray-50/70 p-3 md:p-4 text-xs md:text-sm text-gray-600 font-semibold leading-relaxed min-w-0"
            >
              <span className="w-7 h-7 rounded-full bg-primary text-white shrink-0 flex items-center justify-center text-[11px] font-extrabold">
                {itemIndex + 1}
              </span>
              <span className="min-w-0 break-words whitespace-normal">{item.title || item}</span>
            </li>
          ))}
        </ul>
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      {groups.map((group, index) => (
        <details key={group.key} className="group bg-white border border-gray-100 rounded-3xl shadow-sm overflow-hidden" open={index === 0}>
          <summary className="cursor-pointer list-none p-5 md:p-6 flex items-center justify-between gap-4 min-w-0">
            <span className="font-extrabold text-navy flex items-center gap-2 min-w-0">
              <FileText className="w-4 h-4 text-primary" />
              <span className="min-w-0 break-words">{labels.publicationGroups[group.key] || group.title}</span>
            </span>
            <span className="text-[11px] font-extrabold text-primary bg-primary/10 rounded-full px-3 py-1">
              {group.items.length}
            </span>
          </summary>
          <div className="px-5 md:px-6 pb-6 border-t border-gray-100">
            <ul className="flex flex-col gap-3 pt-5">
              {group.items.map((item, itemIndex) => (
                <li
                  key={`${item.title || item}-${itemIndex}`}
                  className="flex items-start gap-3 rounded-2xl border border-gray-100 bg-gray-50/70 p-3 md:p-4 text-xs md:text-sm text-gray-600 font-semibold leading-relaxed min-w-0"
                >
                  <span className="w-7 h-7 rounded-full bg-primary text-white shrink-0 flex items-center justify-center text-[11px] font-extrabold">
                    {itemIndex + 1}
                  </span>
                  <span className="min-w-0 break-words whitespace-normal">{item.title || item}</span>
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
            <p className="p-4 text-xs font-semibold text-gray-500 break-words">{image.caption}</p>
          )}
        </div>
      ))}
    </div>
  );
}

function DepartmentLoadingSkeleton() {
  return (
    <div className="pt-20 bg-white overflow-x-hidden">
      <section className="bg-primary-light border-b border-gray-100">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-14 md:py-20">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div className="lg:col-span-8 space-y-5">
              <div className="h-8 w-44 max-w-full rounded-full bg-white/80 animate-pulse" />
              <div className="h-10 md:h-14 w-full max-w-2xl rounded-2xl bg-white/80 animate-pulse" />
              <div className="space-y-3 max-w-3xl">
                <div className="h-4 rounded-full bg-white/80 animate-pulse" />
                <div className="h-4 w-5/6 rounded-full bg-white/80 animate-pulse" />
                <div className="h-4 w-3/4 rounded-full bg-white/80 animate-pulse" />
              </div>
            </div>
            <div className="lg:col-span-4">
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm space-y-4">
                <div className="h-14 w-14 rounded-2xl bg-primary/10 animate-pulse" />
                <div className="h-4 w-32 rounded-full bg-gray-100 animate-pulse" />
                <div className="h-6 w-56 max-w-full rounded-full bg-gray-100 animate-pulse" />
                <div className="h-10 rounded-2xl bg-gray-50 animate-pulse" />
              </div>
            </div>
          </div>
        </div>
      </section>
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">
          <div className="lg:col-span-4 space-y-6">
            <div className="h-72 rounded-3xl bg-gray-50 border border-gray-100 animate-pulse" />
            <div className="h-64 rounded-3xl bg-gray-50 border border-gray-100 animate-pulse" />
          </div>
          <div className="lg:col-span-8 space-y-6">
            <div className="h-56 rounded-3xl bg-gray-50 border border-gray-100 animate-pulse" />
            <div className="h-80 rounded-3xl bg-gray-50 border border-gray-100 animate-pulse" />
          </div>
        </div>
      </div>
    </div>
  );
}

export default function DepartmentPage() {
  const { id } = useParams();
  const { language, isRtl } = useLanguage();

  const [department, setDepartment] = useState(null);
  const [departmentList, setDepartmentList] = useState([]);
  const [departmentLoading, setDepartmentLoading] = useState(true);
  const [labelsLoading, setLabelsLoading] = useState(true);
  const [departmentMissing, setDepartmentMissing] = useState(false);
  const [departmentPageLabels, setDepartmentPageLabels] = useState({});

  const labels = useMemo(() => ({
    home: departmentPageLabels.home || "",
    faculties: departmentPageLabels.faculties || "",
    facultyTechnology: department?.facultyName || departmentPageLabels.faculties || "",
    quickContact: departmentPageLabels.quick_contact || "",
    backToFaculty: departmentPageLabels.back_to_faculty || "",
    headOfDepartment: departmentPageLabels.head_of_department || "",
    history: departmentPageLabels.history || "",
    preparedSpecialists: departmentPageLabels.prepared_specialists || "",
    subjects: departmentPageLabels.subjects || "",
    staff: departmentPageLabels.staff || "",
    publications: departmentPageLabels.publications || "",
    research: departmentPageLabels.research || "",
    cooperation: departmentPageLabels.cooperation || "",
    activities: departmentPageLabels.activities || "",
    bachelor: departmentPageLabels.bachelor || "",
    master: departmentPageLabels.master || "",
    doctoral: departmentPageLabels.doctoral || "",
    programs: departmentPageLabels.programs || "",
    bachelorSubjects: departmentPageLabels.bachelor_subjects || "",
    masterSubjects: departmentPageLabels.master_subjects || "",
    gallery: departmentPageLabels.gallery || "",
    notFoundTitle: departmentPageLabels.not_found_title || "",
    notFoundDescription: departmentPageLabels.not_found_description || "",
    publicationGroups: {
      conferencePapers: departmentPageLabels.conference_papers || "",
      scopusWebOfScience: departmentPageLabels.scopus_web_of_science || "",
      textbooksManuals: departmentPageLabels.textbooks_manuals || "",
      textbooksManualsMonographs: departmentPageLabels.textbooks_manuals_monographs || "",
      monographs: departmentPageLabels.monographs || "",
      articles: departmentPageLabels.articles || "",
    },
  }), [department?.facultyName, departmentPageLabels]);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;
    setLabelsLoading(true);

    departmentPageCmsService
      .get(language)
      .then((payload) => {
        if (!active) return;
        setDepartmentPageLabels(payload?.labels || {});
      })
      .catch(() => {
        if (!active) return;
        setDepartmentPageLabels({});
      })
      .finally(() => {
        if (active) setLabelsLoading(false);
      });

    return () => {
      active = false;
    };
  }, [language]);

  useEffect(() => {
    let active = true;
    setDepartmentLoading(true);
    setDepartmentMissing(false);

    Promise.all([
      departmentService.getDepartment(id),
      departmentService.getDepartments(),
    ])
      .then(([departmentData, departmentsData]) => {
        if (!active) return;
        const normalized = normalizeApiDepartment(departmentData);
        setDepartment(normalized);
        setDepartmentList((departmentsData || []).filter((item) => item.faculty_id === normalized?.faculty_id));
      })
      .catch(() => {
        if (!active) return;
        setDepartment(null);
        setDepartmentList([]);
        setDepartmentMissing(true);
      })
      .finally(() => {
        if (active) setDepartmentLoading(false);
      });

    return () => {
      active = false;
    };
  }, [id, language]);

  if (departmentLoading || labelsLoading) {
    return <DepartmentLoadingSkeleton />;
  }

  if (!department || departmentMissing) {
    return (
      <div className="pt-20 min-h-screen bg-primary-light flex flex-col items-center justify-center text-center p-8 overflow-x-hidden">
        <h1 className="text-3xl font-extrabold text-navy mb-2 break-words">{labels.notFoundTitle}</h1>
        <p className="text-gray-500 max-w-md mb-8">
          {labels.notFoundDescription}
        </p>
        <Link
          to="/"
          className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md"
        >
          {labels.home}
        </Link>
      </div>
    );
  }

  const fullHistory = department.history;
  const rawPreparedSpecialistsText = department.rawPreparedSpecialistsText;
  const rawPublicationText = department.rawPublicationText;
  const rawResearchText = department.rawResearchText;
  const cooperationText = department.cooperationText;
  const rawActivityText = department.rawActivityText;

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

  const cooperationItems = [
    ...(department.cooperation || []),
    ...(department.jointPrograms || []),
  ];
  const publicationGroups = normalizePublicationGroups(department.publications);
  const hasPublications = publicationGroups.length > 0 || Boolean(rawPublicationText);

  return (
    <div className="pt-20 bg-white overflow-x-hidden" dir={isRtl ? "rtl" : "ltr"}>
      <DepartmentHero department={department} labels={labels} isRtl={isRtl} />

      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 min-w-0">
          <aside className="lg:col-span-4 flex flex-col gap-6 min-w-0">
            <DepartmentContactCard department={department} labels={labels} />

            <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start min-w-0">
              <h2 className="text-lg font-extrabold text-navy mb-4 break-words">{labels.facultyTechnology}</h2>
              <div className="flex flex-col gap-2">
                {departmentList.map((item) => (
                  <Link
                    key={item.slug}
                    to={`/department/${item.slug}`}
                    className={`text-xs font-bold p-3 rounded-xl transition-all min-w-0 break-words ${
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

          <main className="lg:col-span-8 flex flex-col gap-12 min-w-0">
            <DepartmentSection id="history" icon={Clock} title={labels.history}>
              <TextPanel paragraphs={fullHistory} />
            </DepartmentSection>

            <DepartmentSection id="programs" icon={GraduationCap} title={labels.preparedSpecialists}>
              <div className="flex flex-col gap-5">
                <DepartmentProgramList programs={department.preparedSpecialists} labels={labels} />
                {rawPreparedSpecialistsText && <TextPanel paragraphs={rawPreparedSpecialistsText} />}
              </div>
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
                  {publicationGroups.length === 0 && rawPublicationText && <TextPanel paragraphs={rawPublicationText} />}
                </div>
              </DepartmentSection>
            )}

            {(researchItems.length > 0 || rawResearchText) && (
              <DepartmentSection id="research" icon={Microscope} title={labels.research}>
                <div className="flex flex-col gap-5">
                  {researchItems.length > 0 && <ListPanel items={researchItems} marker="number" />}
                  {researchItems.length === 0 && rawResearchText && <TextPanel paragraphs={rawResearchText} />}
                </div>
              </DepartmentSection>
            )}

            {(cooperationItems.length > 0 || cooperationText) && (
              <DepartmentSection id="cooperation" icon={Globe} title={labels.cooperation}>
                <div className="flex flex-col gap-5">
                  {cooperationItems.length > 0 && <ListPanel items={cooperationItems} />}
                  {cooperationItems.length === 0 && cooperationText && <TextPanel paragraphs={cooperationText} />}
                </div>
              </DepartmentSection>
            )}

            {(activityItems.length > 0 || rawActivityText) && (
              <DepartmentSection id="activities" icon={Zap} title={labels.activities}>
                <div className="flex flex-col gap-5">
                  {activityItems.length > 0 && <ListPanel items={activityItems} marker="number" />}
                  {activityItems.length === 0 && rawActivityText && <TextPanel paragraphs={rawActivityText} />}
                </div>
              </DepartmentSection>
            )}

            {department.gallery?.length > 0 && (
              <DepartmentSection id="gallery" icon={ImageIcon} title={labels.gallery}>
                <DepartmentGallery images={department.gallery} labels={labels} />
              </DepartmentSection>
            )}

          </main>
        </div>
      </div>
    </div>
  );
}
