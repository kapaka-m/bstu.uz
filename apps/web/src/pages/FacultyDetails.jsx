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
import { useLanguage } from "../context/LanguageContext";
import { facultyService } from "../services/facultyService";

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
  const { t, language, isRtl } = useLanguage();
  const [faculty, setFaculty] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");


  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;
    setLoading(true);
    setError("");

    facultyService
      .getFaculty(id)
      .then((data) => {
        if (!active) return;
        setFaculty(data || null);
        if (data?.name) {
          document.title = data.name;
        }
      })
      .catch(() => {
        if (!active) return;
        setFaculty(null);
        setError(t("common.notFoundDesc"));
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [id, language, t]);

  const labels = {
    home: t("nav.home"),
    faculties: t("common.faculties"),
    departments: t("common.departments"),
    bachelorPrograms: t("common.bachelorPrograms"),
    masterSpecializations: t("facultyTechnology.masterSpecializations"),
    contact: t("common.contact"),
    overview: t("common.aboutFaculty"),
    leadership: t("common.managementDean"),
    learnMore: t("common.learnMore"),
    head: t("common.headOfDepartment"),
    phone: t("common.phone"),
    email: t("common.email"),
    quickDepartmentLinks: t("facultyTechnology.quickDepartmentLinks"),
    deanContact: t("facultyTechnology.deanContact"),
    deputyDeanContacts: t("facultyTechnology.deputyDeanContacts"),
    industryCooperation: t("facultyTechnology.industryCooperation"),
    academicPathways: t("facultyTechnology.academicPathways"),
  };

  if (loading) {
    return (
      <div className="pt-20 min-h-screen bg-white flex items-center justify-center">
        <div className="w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin" />
      </div>
    );
  }

  if (!faculty || error) {
    return (
      <div className="pt-20 min-h-screen bg-primary-light flex flex-col items-center justify-center text-center p-8">
        <h1 className="text-3xl font-extrabold text-navy mb-2">{t("common.notFound")}</h1>
        <p className="text-gray-500 max-w-md mb-8">
          {error || t("common.notFoundDesc")}
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
  const overview = faculty.description || "";
  const overviewParagraphs = overview.split(/(?<=\.)\s+/).filter(Boolean);

  const facultyDepartmentsList = (faculty.departments || []).map((dept) => ({
    slug: dept.slug,
    name: dept.name,
    route: `/department/${dept.slug}`,
    about: dept.description || "",
    contact: dept.head_name
      ? {
          name: dept.head_name,
          route: dept.head_profile_slug ? `/profile/${dept.head_profile_slug}` : "",
          role: labels.head,
          phone: dept.phone,
          email: dept.email,
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
            description={t("facultyTechnology.leadershipDesc")}
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
            description={t("facultyTechnology.departmentsDesc")}
          />
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            {facultyDepartmentsList.map((department, index) => {
              const summary = shortText(
                department.about || t(`departments.${department.slug}.about`),
                160,
              );
              return (
                <article
                  key={department.slug}
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
                    <h3 className="text-base font-extrabold text-navy leading-snug">
                      <Link
                        to={department.route}
                        className="hover:text-primary transition-colors"
                      >
                        {t(
                          `departments.${department.slug}.name`,
                          department.name,
                        )}
                      </Link>
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
                        {department.contact.route ? (
                          <Link to={department.contact.route} className="hover:text-primary transition-colors">
                            {department.contact.name}
                          </Link>
                        ) : (
                          department.contact.name
                        )}
                      </span>
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
              description={t("facultyTechnology.bachelorDesc")}
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
              description={t("facultyTechnology.masterDesc")}
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
              description={t("facultyTechnology.contactDesc")}
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
