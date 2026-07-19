import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ArrowRight,
  BookOpen,
  BriefcaseBusiness,
  Building2,
  ChevronRight,
  Clock,
  GraduationCap,
  Mail,
  Phone,
  ShieldCheck,
  UserCheck,
  Users,
} from "lucide-react";
import { facultyTechnology, technologyDepartments } from "../data/facultyTechnology";
import { useLanguage } from "../context/LanguageContext";

const getLocalized = (value, language) => {
  if (!value || typeof value !== "object") return value;
  return value[language] || value.en || Object.values(value).find(Boolean) || "";
};

const getInitials = (name) => {
  if (!name) return "";
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
};

const telHref = (phone) => `tel:${phone.replace(/[^\d+]/g, "")}`;

const shortText = (text, max = 170) => {
  if (!text) return "";
  return text.length > max ? `${text.slice(0, max).trim()}...` : text;
};

function ImageWithFallback({ src, fallbackSrc, alt, className, initialsClassName }) {
  const [currentSrc, setCurrentSrc] = useState(src);
  const [failed, setFailed] = useState(false);

  if (failed || !currentSrc) {
    return (
      <div className={initialsClassName}>
        {getInitials(alt)}
      </div>
    );
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
        const programName = program.id ? t(`programs.${program.id}.name`, program.name) : program.name;
        const content = (
          <>
            <div className="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
              <Icon className="w-5 h-5" />
            </div>
            <div className="min-w-0">
              <span className="text-[11px] font-extrabold text-primary uppercase tracking-wider">
                {program.code}
              </span>
              <h3 className="text-sm md:text-base font-bold text-navy leading-snug mt-1">
                {programName}
              </h3>
              <p className="text-xs font-semibold text-gray-400 mt-2">{label}</p>
            </div>
          </>
        );

        return program.route ? (
          <Link
            key={`${program.code}-${program.name}-${index}`}
            to={program.route}
            className="bg-white border border-gray-100 hover:border-primary/25 rounded-2xl p-5 shadow-sm flex gap-4 items-start transition-colors text-start"
          >
            {content}
          </Link>
        ) : (
          <div
            key={`${program.code}-${program.name}-${index}`}
            className="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex gap-4 items-start text-start"
          >
            {content}
          </div>
        );
      })}
    </div>
  );
}

export default function FacultyTechnologyPage() {
  const { t, language } = useLanguage();
  const isRtl = language === "ar";

  const labels = {
    home: t("nav.home", "Home"),
    faculties: t("common.faculties", "Faculties"),
    departments: t("common.departments", "Departments"),
    bachelorPrograms: t("common.bachelorPrograms", "Bachelor Programs"),
    masterSpecializations: t("facultyTechnology.masterSpecializations", "Master Specializations"),
    contact: t("common.contact", "Contact"),
    overview: t("common.aboutFaculty", "Faculty Overview"),
    leadership: t("common.managementDean", "Faculty Leadership"),
    learnMore: t("common.learnMore", "Learn more"),
    head: t("common.headOfDepartment", "Head of Department"),
    reception: t("facultyTechnology.reception", "Reception"),
    phone: t("common.phone", "Phone"),
    email: t("common.email", "Email"),
    quickDepartmentLinks: t("facultyTechnology.quickDepartmentLinks", "Quick Department Links"),
    deanContact: t("facultyTechnology.deanContact", "Dean Contact"),
    deputyDeanContacts: t("facultyTechnology.deputyDeanContacts", "Deputy Dean Contacts"),
    industryCooperation: t("facultyTechnology.industryCooperation", "Industry Cooperation"),
    academicPathways: t("facultyTechnology.academicPathways", "Academic Pathways"),
  };

  const title = getLocalized(facultyTechnology.title, language);
  const overview = getLocalized(facultyTechnology.overview, language);
  const overviewParagraphs = overview.split(/(?<=\.)\s+/).filter(Boolean);

  useEffect(() => {
    window.scrollTo(0, 0);
    document.title = "Faculty of Technology | BSTU";

    const description =
      "Faculty of Technology at Bukhara State Technical University, including departments, bachelor programs, master specializations, and industry cooperation.";
    let metaDescription = document.querySelector('meta[name="description"]');
    if (!metaDescription) {
      metaDescription = document.createElement("meta");
      metaDescription.setAttribute("name", "description");
      document.head.appendChild(metaDescription);
    }
    metaDescription.setAttribute("content", description);
  }, []);

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
                <span className="text-gray-400 font-bold">{title}</span>
              </nav>

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
                    <ArrowRight className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`} />
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
                  {technologyDepartments.length}
                </p>
                <p className="text-xs font-extrabold uppercase tracking-wider text-primary mt-1">
                  {labels.departments}
                </p>
                <div className="mt-6 grid grid-cols-2 gap-3">
                  <div className="bg-primary-light rounded-2xl p-4">
                    <p className="text-xl font-extrabold text-navy">
                      {facultyTechnology.bachelorPrograms.length}
                    </p>
                    <p className="text-[10px] font-bold text-gray-500 mt-1">
                      {labels.bachelorPrograms}
                    </p>
                  </div>
                  <div className="bg-primary-light rounded-2xl p-4">
                    <p className="text-xl font-extrabold text-navy">
                      {facultyTechnology.masterSpecializations.length}
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

      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 flex flex-col gap-16">
        <section id="overview" className="grid grid-cols-1 lg:grid-cols-12 gap-8">
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

        <section id="leadership" className="flex flex-col gap-8">
          <SectionTitle
            icon={UserCheck}
            eyebrow={labels.leadership}
            title={labels.leadership}
            description={t("facultyTechnology.leadershipDesc", "Faculty leadership and contact details for academic, youth, and administrative affairs.")}
          />
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {facultyTechnology.leadership.map((member) => (
              <article
                key={member.email}
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
                  <span className="flex items-start gap-2">
                    <Clock className="w-4 h-4 text-primary shrink-0 mt-0.5" />
                    <span>{member.reception}</span>
                  </span>
                  <a href={telHref(member.phone)} className="flex items-center gap-2 hover:text-primary transition-colors">
                    <Phone className="w-4 h-4 text-primary shrink-0" />
                    <span dir="ltr">{member.phone}</span>
                  </a>
                  <a href={`mailto:${member.email}`} className="flex items-center gap-2 hover:text-primary transition-colors min-w-0">
                    <Mail className="w-4 h-4 text-primary shrink-0" />
                    <span className="truncate">{member.email}</span>
                  </a>
                </div>
              </article>
            ))}
          </div>
        </section>

        <section id="departments" className="flex flex-col gap-8">
          <SectionTitle
            icon={ShieldCheck}
            eyebrow={labels.departments}
            title={labels.departments}
            description={t("facultyTechnology.departmentsDesc", "Six specialized departments connect academic training with industrial practice and applied research.")}
          />
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            {technologyDepartments.map((department, index) => {
              const summary = shortText(department.history?.[0] || department.rawContent?.original || "", 160);
              return (
                <Link
                  key={department.slug}
                  to={department.route}
                  className="group bg-white border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all flex flex-col gap-5 text-start"
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
                      {department.name}
                    </h3>
                    {summary && (
                      <p className="text-xs md:text-sm text-gray-500 font-medium leading-relaxed mt-3">
                        {summary}
                      </p>
                    )}
                  </div>
                  {department.contact?.name && (
                    <div className="border-t border-gray-100 pt-4 text-xs font-semibold text-gray-500 flex items-start gap-2">
                      <Users className="w-4 h-4 text-primary shrink-0 mt-0.5" />
                      <span>
                        <span className="text-navy font-bold">{labels.head}: </span>
                        {department.contact.name}
                      </span>
                    </div>
                  )}
                  <span className="inline-flex items-center gap-2 text-xs font-extrabold text-primary mt-auto">
                    {labels.learnMore}
                    <ArrowRight className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`} />
                  </span>
                </Link>
              );
            })}
          </div>
        </section>

        <section id="bachelor-programs" className="flex flex-col gap-8">
          <SectionTitle
            icon={GraduationCap}
            eyebrow={labels.academicPathways}
            title={labels.bachelorPrograms}
            description={t("facultyTechnology.bachelorDesc", "Bachelor degree programs offered by the Faculty of Technology.")}
          />
          <ProgramGrid programs={facultyTechnology.bachelorPrograms} label={labels.bachelorPrograms} icon={GraduationCap} t={t} />
        </section>

        <section id="master-specializations" className="flex flex-col gap-8">
          <SectionTitle
            icon={BookOpen}
            eyebrow={labels.academicPathways}
            title={labels.masterSpecializations}
            description={t("facultyTechnology.masterDesc", "Master degree specializations available through the Faculty of Technology.")}
          />
          <ProgramGrid programs={facultyTechnology.masterSpecializations} label={labels.masterSpecializations} icon={BookOpen} t={t} />
        </section>

        <section id="contact" className="grid grid-cols-1 lg:grid-cols-12 gap-8">
          <div className="lg:col-span-4">
            <SectionTitle
              icon={Mail}
              eyebrow={labels.contact}
              title={labels.contact}
              description={t("facultyTechnology.contactDesc", "Faculty and department contact paths for students, applicants, and partners.")}
            />
          </div>
          <div className="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div className="bg-primary-light border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
              <h3 className="text-lg font-extrabold text-navy mb-4">{labels.deanContact}</h3>
              <div className="flex flex-col gap-3 text-sm font-semibold text-gray-500">
                {facultyTechnology.leadership.slice(0, 1).map((member) => (
                  <React.Fragment key={member.email}>
                    <p className="text-navy font-extrabold">{member.name}</p>
                    <a href={telHref(member.phone)} className="flex items-center gap-2 hover:text-primary transition-colors">
                      <Phone className="w-4 h-4 text-primary shrink-0" />
                      <span dir="ltr">{member.phone}</span>
                    </a>
                    <a href={`mailto:${member.email}`} className="flex items-center gap-2 hover:text-primary transition-colors min-w-0">
                      <Mail className="w-4 h-4 text-primary shrink-0" />
                      <span className="truncate">{member.email}</span>
                    </a>
                  </React.Fragment>
                ))}
              </div>
            </div>

            <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
              <h3 className="text-lg font-extrabold text-navy mb-4">{labels.deputyDeanContacts}</h3>
              <div className="flex flex-col gap-4">
                {facultyTechnology.leadership.slice(1).map((member) => (
                  <div key={member.email} className="border-b border-gray-100 last:border-b-0 pb-4 last:pb-0">
                    <p className="text-sm font-extrabold text-navy">{member.name}</p>
                    <p className="text-[11px] font-bold text-primary uppercase tracking-wider mt-1">{member.role}</p>
                    <div className="flex flex-col gap-1.5 mt-3 text-xs font-semibold text-gray-500">
                      <a href={telHref(member.phone)} className="hover:text-primary transition-colors" dir="ltr">
                        {member.phone}
                      </a>
                      <a href={`mailto:${member.email}`} className="hover:text-primary transition-colors truncate">
                        {member.email}
                      </a>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div className="md:col-span-2 bg-white border border-gray-100 rounded-3xl p-6 shadow-sm text-start">
              <h3 className="text-lg font-extrabold text-navy mb-5">{labels.quickDepartmentLinks}</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                {technologyDepartments.map((department) => (
                  <Link
                    key={department.slug}
                    to={department.route}
                    className="flex items-center justify-between gap-3 border border-gray-100 rounded-2xl px-4 py-3 text-sm font-bold text-gray-500 hover:text-primary hover:border-primary/20 transition-colors"
                  >
                    <span>{department.name}</span>
                    <ArrowRight className={`w-4 h-4 shrink-0 ${isRtl ? "rotate-180" : ""}`} />
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
