import React, { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ArrowRight,
  BookOpen,
  BriefcaseBusiness,
  Building2,
  Clock,
  FileQuestion,
  GraduationCap,
  Mail,
  Phone,
  ShieldCheck,
  UserCheck,
} from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { facultyService } from "../services/facultyService";
import { facultyPageCmsService } from "../services/facultyPageCmsService";

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

const formatPhoneDisplay = (phone) => {
  const value = String(phone || "").trim();
  return value.replace(/^\(\+998\s*(\d{2})\)/, "+998 ($1)");
};

const shortText = (text, max = 170) => {
  if (!text) return "";
  return text.length > max ? `${text.slice(0, max).trim()}...` : text;
};

const displayText = (...values) => values.find((value) => {
  if (typeof value !== "string") return false;
  const trimmed = value.trim();
  return trimmed && !/^[\w.-]+(\.[\w.-]+)+$/.test(trimmed);
})?.trim() || "";

const normalizeDegree = (degree) => String(degree || "").toLowerCase();

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
    <div className="flex flex-col gap-3 text-start min-w-0">
      {eyebrow && (
        <span className="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider text-primary">
          <Icon className="w-4 h-4" />
          {eyebrow}
        </span>
      )}
      <h2 className="text-2xl md:text-3xl font-extrabold text-navy leading-tight break-words">
        {title}
      </h2>
      {description && (
        <p className="text-sm md:text-base text-gray-500 leading-relaxed max-w-3xl break-words">
          {description}
        </p>
      )}
    </div>
  );
}

function ProgramGrid({ programs, label, icon: Icon }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
      {programs.map((program, index) => {
        const displayName = program.name;

        const content = (
          <>
            <div className="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
              <Icon className="w-5 h-5" />
            </div>
            <div className="min-w-0">
              {program.code && (
                <span className="text-[11px] font-extrabold text-primary uppercase tracking-wider break-words">
                  {program.code}
                </span>
              )}
              <h3 className="text-sm md:text-base font-bold text-navy leading-snug mt-1 break-words">
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
            className="bg-white border border-gray-100 hover:border-primary/25 rounded-2xl p-5 shadow-sm flex gap-4 items-start transition-colors text-start w-full min-w-0"
          >
            {content}
          </Link>
        ) : (
          <div
            key={`${program.code}-${program.name}-${index}`}
            className="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex gap-4 items-start text-start w-full min-w-0"
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
  const { language, isRtl } = useLanguage();
  const [faculty, setFaculty] = useState(null);
  const [facultyPageLabels, setFacultyPageLabels] = useState({});
  const [facultyLoading, setFacultyLoading] = useState(true);
  const [labelsLoading, setLabelsLoading] = useState(true);
  const [error, setError] = useState(false);


  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;
    setFacultyLoading(true);
    setError(false);

    facultyService
      .getFaculty(id)
      .then((data) => {
        if (!active) return;
        setFaculty(data || null);
      })
      .catch(() => {
        if (!active) return;
        setFaculty(null);
        setError(true);
      })
      .finally(() => {
        if (active) setFacultyLoading(false);
      });

    return () => {
      active = false;
    };
  }, [id, language]);

  useEffect(() => {
    let active = true;
    setLabelsLoading(true);

    facultyPageCmsService
      .get(language)
      .then((payload) => {
        if (active) setFacultyPageLabels(payload?.labels || {});
      })
      .catch(() => {
        if (active) setFacultyPageLabels({});
      })
      .finally(() => {
        if (active) setLabelsLoading(false);
      });

    return () => {
      active = false;
    };
  }, [language]);

  const cmsLabel = (key) => facultyPageLabels[key] || "";

  const labels = {
    home: cmsLabel("home"),
    faculties: cmsLabel("faculties"),
    departments: cmsLabel("departments"),
    bachelorPrograms: cmsLabel("bachelor_programs"),
    masterSpecializations: cmsLabel("master_specializations"),
    contact: cmsLabel("contact"),
    overview: cmsLabel("overview"),
    leadership: cmsLabel("leadership"),
    learnMore: cmsLabel("learn_more"),
    head: cmsLabel("head_of_department"),
    phone: cmsLabel("phone"),
    email: cmsLabel("email"),
    quickDepartmentLinks: cmsLabel("quick_department_links"),
    deanContact: cmsLabel("dean_contact"),
    deputyDeanContacts: cmsLabel("deputy_dean_contacts"),
    industryCooperation: cmsLabel("industry_cooperation"),
    academicPathways: cmsLabel("academic_pathways"),
    leadershipDesc: cmsLabel("leadership_description"),
    departmentsDesc: cmsLabel("departments_description"),
    bachelorDesc: cmsLabel("bachelor_description"),
    masterDesc: cmsLabel("master_description"),
    contactDesc: cmsLabel("contact_description"),
    notFoundTitle: cmsLabel("not_found_title"),
    notFoundDesc: cmsLabel("not_found_description"),
  };

  if (facultyLoading || labelsLoading) {
    return (
      <div className="pt-20 bg-white min-h-screen overflow-x-hidden">
        <section className="bg-primary-light border-b border-gray-100">
          <div className="container mx-auto px-4 md:px-8 max-w-7xl py-14 md:py-20">
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
              <div className="lg:col-span-8">
                <div className="h-8 w-40 rounded-full bg-white/80 animate-pulse mb-5" />
                <div className="h-12 w-full max-w-2xl rounded-full bg-white/80 animate-pulse mb-4" />
                <div className="h-5 w-full max-w-3xl rounded-full bg-white/80 animate-pulse" />
              </div>
              <div className="lg:col-span-4">
                <div className="h-56 rounded-3xl bg-white/80 animate-pulse" />
              </div>
            </div>
          </div>
        </section>
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 flex flex-col gap-10">
          {[1, 2, 3].map((item) => (
            <div key={item} className="h-44 rounded-3xl bg-gray-100 animate-pulse" />
          ))}
        </div>
      </div>
    );
  }

  if (!faculty || error) {
    return (
      <div className="pt-20 min-h-screen bg-primary-light flex flex-col items-center justify-center text-center p-8">
        <FileQuestion className="w-12 h-12 text-primary mb-4" />
        <h1 className="text-3xl font-extrabold text-navy mb-2">{labels.notFoundTitle}</h1>
        <p className="text-gray-500 max-w-md mb-8">
          {labels.notFoundDesc}
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

  const title = faculty.name || "";
  const heroDescription = faculty.meta_description || faculty.description || "";
  const overview = faculty.description || "";
  const overviewParagraphs = overview.split(/(?<=\.)\s+/).filter(Boolean);

  const facultyDepartmentsList = (faculty.departments || []).map((dept) => ({
    slug: dept.slug,
    name: displayText(dept.name, dept.short_name, dept.slug),
    route: `/department/${dept.slug}`,
    about: dept.description || "",
    contact: dept.head_name
      ? {
          name: dept.head_name,
          route: dept.head_profile_slug ? `/profile/${dept.head_profile_slug}` : "",
          role: labels.head,
          phone: dept.phone,
          email: dept.email,
          image: dept.head_profile_photo_url || dept.head_profile_photo,
        }
      : null,
  }));

  const toProgramCard = (program) => ({
    code: program.display_code || program.official_code || program.code || "",
    name: program.name || "",
    id: program.slug,
    route: `/programs/${program.slug}`,
  });
  const bachelorPrograms = (faculty.programs || [])
    .filter((program) => normalizeDegree(program.degree) === "bachelor")
    .map(toProgramCard);
  const masterPrograms = (faculty.programs || [])
    .filter((program) => ["master", "phd", "doctoral"].includes(normalizeDegree(program.degree)))
    .map(toProgramCard);
  const sectionLinks = [
    [labels.departments, "#departments"],
    ...(bachelorPrograms.length > 0
      ? [[labels.bachelorPrograms, "#bachelor-programs"]]
      : []),
    ...(masterPrograms.length > 0
      ? [[labels.masterSpecializations, "#master-specializations"]]
      : []),
    [labels.contact, "#contact"],
  ];

  const leadership = (faculty.leadership || faculty.staff || [])
    .filter((member) => !member.department_id)
    .map((member) => ({
      slug: member.slug,
      route: member.slug ? `/profile/${member.slug}` : "",
      name: member.full_name || member.name || "",
      role: member.position || "",
      reception: member.office || "",
      phone: member.phone,
      email: member.email,
      image: member.photo_url || member.photo,
      fallbackImage: null,
    }));

  return (
    <div className="pt-20 bg-white overflow-x-hidden" dir={isRtl ? "rtl" : "ltr"}>
      {/* Top Banner/Hero */}
      <section className="bg-primary-light border-b border-gray-100">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-14 md:py-20">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <motion.div
              initial={{ opacity: 0, y: 18 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.45 }}
              className="lg:col-span-8 text-start min-w-0"
            >
              <span className="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider text-primary bg-white/70 border border-white px-3 py-1.5 rounded-full mb-5">
                <BriefcaseBusiness className="w-4 h-4" />
                {labels.industryCooperation}
              </span>
              <h1 className="text-3xl md:text-5xl font-extrabold text-navy leading-tight tracking-tight mb-5 break-words">
                {title}
              </h1>
              <p className="text-gray-500 text-sm md:text-lg leading-relaxed max-w-3xl break-words">
                {shortText(heroDescription, 330)}
              </p>

              <div className="flex flex-wrap gap-3 mt-8">
                {sectionLinks.map(([label, href]) => (
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
              className="lg:col-span-4 min-w-0"
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
                <div className="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div className="bg-primary-light rounded-2xl p-4">
                    <p className="text-xl font-extrabold text-navy">
                      {bachelorPrograms.length}
                    </p>
                    <p className="text-[10px] font-bold text-gray-500 mt-1">
                      {labels.bachelorPrograms}
                    </p>
                  </div>
                  {masterPrograms.length > 0 && (
                    <div className="bg-primary-light rounded-2xl p-4">
                      <p className="text-xl font-extrabold text-navy">
                        {masterPrograms.length}
                      </p>
                      <p className="text-[10px] font-bold text-gray-500 mt-1">
                        {labels.masterSpecializations}
                      </p>
                    </div>
                  )}
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
            />
          </div>
          <div className="lg:col-span-8 bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-sm min-w-0">
            <div className="flex flex-col gap-4 text-gray-500 text-sm md:text-base leading-relaxed text-start break-words">
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
            description={labels.leadershipDesc}
          />
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {leadership.map((member, idx) => (
              <article
                key={member.email || idx}
                className="bg-gray-50 border border-gray-100 rounded-3xl p-6 shadow-sm text-center flex flex-col items-center min-w-0"
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
                <h3 className="text-base font-extrabold text-navy leading-snug break-words">
                  {member.route ? (
                    <Link to={member.route} className="hover:text-primary transition-colors">
                      {member.name}
                    </Link>
                  ) : (
                    member.name
                  )}
                </h3>
                <div className="w-full border-t border-gray-200/60 mt-5 pt-4 flex flex-col gap-2 text-xs font-semibold text-gray-500 text-start">
                  {member.reception && (
                    <span className="flex items-start gap-2">
                      <Clock className="w-4 h-4 text-primary shrink-0 mt-0.5" />
                      <span className="break-words">{member.reception}</span>
                    </span>
                  )}
                  {member.phone && (
                    <a
                      href={telHref(member.phone)}
                      className="flex items-center gap-2 hover:text-primary transition-colors"
                    >
                      <Phone className="w-4 h-4 text-primary shrink-0" />
                      <span dir="ltr">{formatPhoneDisplay(member.phone)}</span>
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
            description={labels.departmentsDesc}
          />
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            {facultyDepartmentsList.map((department, index) => {
              const summary = shortText(
                department.about,
                160,
              );
              return (
                <article
                  key={department.slug}
                className="group bg-white border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all flex flex-col gap-5 text-start w-full min-w-0"
                >
                  <div className="flex items-start justify-between gap-4">
                    <div className="flex items-start gap-4 min-w-0">
                      <div className="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <Building2 className="w-6 h-6" />
                      </div>
                      <h3 className="text-base font-extrabold text-navy leading-snug min-w-0 pt-1 break-words">
                        <Link
                          to={department.route}
                          className="hover:text-primary transition-colors"
                        >
                          {department.name}
                        </Link>
                      </h3>
                    </div>
                    <span className="text-[11px] font-extrabold text-gray-300">
                      {String(index + 1).padStart(2, "0")}
                    </span>
                  </div>
                  <div>
                    {summary && (
                      <p className="text-xs md:text-sm text-gray-500 font-medium leading-relaxed break-words">
                        {summary}
                      </p>
                    )}
                  </div>
                  {department.contact?.name && (
                    <div className="border-t border-gray-100 pt-4 text-xs font-semibold text-gray-500 flex items-start gap-3 mt-auto min-w-0">
                      <ImageWithFallback
                        src={department.contact.image}
                        alt={department.contact.name}
                        className="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm shrink-0"
                        initialsClassName="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-extrabold text-xs shrink-0"
                      />
                      <div className="min-w-0">
                        <span className="text-navy font-bold">
                          {labels.head}:{" "}
                        </span>
                        {department.contact.route ? (
                          <Link to={department.contact.route} className="hover:text-primary transition-colors break-words">
                            {department.contact.name}
                          </Link>
                        ) : (
                          <span className="break-words">{department.contact.name}</span>
                        )}
                      </div>
                    </div>
                  )}
                  <Link
                    to={department.route}
                    className="inline-flex items-center gap-2 text-xs font-extrabold text-primary mt-auto hover:text-primary-hover transition-colors"
                  >
                    {labels.learnMore}
                    <ArrowRight
                      className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`}
                    />
                  </Link>
                </article>
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
              description={labels.bachelorDesc}
            />
            <ProgramGrid
              programs={bachelorPrograms}
              label={labels.bachelorPrograms}
              icon={GraduationCap}
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
              description={labels.masterDesc}
            />
            <ProgramGrid
              programs={masterPrograms}
              label={labels.masterSpecializations}
              icon={BookOpen}
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
              description={labels.contactDesc}
            />
          </div>
          <div className="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-5 min-w-0">
            {/* Dean Contact Card */}
            <div className="bg-primary-light border border-gray-100 rounded-3xl p-6 shadow-sm text-start min-w-0">
              <h3 className="text-lg font-extrabold text-navy mb-4">
                {labels.deanContact}
              </h3>
              <div className="flex flex-col gap-3 text-sm font-semibold text-gray-500">
                {leadership.slice(0, 1).map((member, idx) => (
                  <React.Fragment key={idx}>
                    {member.route ? (
                      <Link to={member.route} className="text-navy font-extrabold hover:text-primary transition-colors">
                        {member.name}
                      </Link>
                    ) : (
                      <p className="text-navy font-extrabold">{member.name}</p>
                    )}
                    {member.phone && (
                      <a
                        href={telHref(member.phone)}
                        className="flex items-center gap-2 hover:text-primary transition-colors"
                      >
                        <Phone className="w-4 h-4 text-primary shrink-0" />
                        <span dir="ltr">{formatPhoneDisplay(member.phone)}</span>
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

            <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start min-w-0">
              <h3 className="text-lg font-extrabold text-navy mb-4">
                {labels.deputyDeanContacts}
              </h3>
              <div className="flex flex-col gap-4">
                {leadership.slice(1).map((member, idx) => (
                  <div
                    key={idx}
                    className="border-b border-gray-100 last:border-b-0 pb-4 last:pb-0"
                  >
                    <p className="text-sm font-extrabold text-navy break-words">
                      {member.route ? (
                        <Link to={member.route} className="hover:text-primary transition-colors">
                          {member.name}
                        </Link>
                      ) : (
                        member.name
                      )}
                    </p>
                    <p className="text-[11px] font-bold text-primary uppercase tracking-wider mt-1">
                      {member.role}
                    </p>
                    <div className="flex flex-col gap-1.5 mt-3 text-xs font-semibold text-gray-500">
                      {member.phone && (
                        <a
                          href={telHref(member.phone)}
                          className={`block w-full hover:text-primary transition-colors ${isRtl ? "text-right" : "text-left"}`}
                        >
                          <span dir="ltr" className="inline-block">
                            {formatPhoneDisplay(member.phone)}
                          </span>
                        </a>
                      )}
                      {member.email && (
                        <a
                          href={`mailto:${member.email}`}
                          className={`block w-full hover:text-primary transition-colors ${isRtl ? "text-right" : "text-left"}`}
                        >
                          <span dir="ltr" className="inline-block max-w-full truncate align-top">
                            {member.email}
                          </span>
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
                    className="flex items-center justify-between gap-3 border border-gray-100 rounded-2xl px-4 py-3 text-sm font-bold text-gray-500 hover:text-primary hover:border-primary/20 transition-colors min-w-0"
                  >
                    <span className="min-w-0 break-words">
                      {department.name}
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
