import React, { useEffect, useState } from "react";
import {
  Award,
  Target,
  Eye,
  ShieldCheck,
  History,
  Cpu,
  Zap,
  Landmark,
  BookOpen,
  Users,
  GraduationCap,
  Microscope,
  Globe,
  Building2,
  ArrowRight,
  Compass,
  Sparkles,
} from "lucide-react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { useLanguage } from "../context/LanguageContext";
import { administrationService } from "../services/administrationService";

export default function AboutPage() {
  const { t, language } = useLanguage();
  const [rectorProfile, setRectorProfile] = useState(null);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  useEffect(() => {
    let alive = true;
    administrationService
      .getProfile("rector")
      .then((profile) => {
        if (alive) setRectorProfile(profile);
      })
      .catch(() => {
        if (alive) setRectorProfile(null);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  const stats = [
    {
      number: "18,000+",
      label: t("about.stats.activeStudents"),
      desc: t("about.stats.activeStudentsDesc"),
      icon: Users,
      color: "from-blue-500 to-cyan-500",
    },
    {
      number: "730+",
      label: t("about.stats.professors"),
      desc: t("about.stats.professorsDesc"),
      icon: GraduationCap,
      color: "from-purple-500 to-indigo-500",
    },
    {
      number: "4",
      label: t("about.stats.faculties"),
      desc: t("about.stats.facultiesDesc"),
      icon: Building2,
      color: "from-emerald-500 to-teal-500",
    },
    {
      number: "24",
      label: t("about.stats.departments"),
      desc: t("about.stats.departmentsDesc"),
      icon: BookOpen,
      color: "from-amber-500 to-orange-500",
    },
    {
      number: "13",
      label: t("about.stats.research"),
      desc: t("about.stats.researchDesc"),
      icon: Microscope,
      color: "from-rose-500 to-pink-500",
    },
    {
      number: "2",
      label: t("about.stats.techParks"),
      desc: t("about.stats.techParksDesc"),
      icon: Cpu,
      color: "from-indigo-500 to-blue-500",
    },
    {
      number: "250+",
      label: t("about.stats.intStudents"),
      desc: t("about.stats.intStudentsDesc"),
      icon: Globe,
      color: "from-cyan-500 to-teal-500",
    },
    {
      number: "18",
      label: t("about.stats.techSchools"),
      desc: t("about.stats.techSchoolsDesc"),
      icon: Landmark,
      color: "from-violet-500 to-purple-500",
    },
  ];

  const timelineEvents = [
    {
      year: "1959",
      title: t("about.timeline.year1959Title"),
      desc: t("about.timeline.year1959Desc"),
    },
    {
      year: "1978",
      title: t("about.timeline.year1978Title"),
      desc: t("about.timeline.year1978Desc"),
    },
    {
      year: "2011/2013",
      title: t("about.timeline.year2011Title"),
      desc: t("about.timeline.year2011Desc"),
    },
    {
      year: "2025",
      title: t("about.timeline.year2025Title"),
      desc: t("about.timeline.year2025Desc"),
    },
    {
      year: "2026",
      title: t("about.timeline.year2026Title"),
      desc: t("about.timeline.year2026Desc"),
    },
  ];

  const faculties = [
    {
      id: "faculty-of-engineering",
      name: t("about.facultiesList.engineering"),
      dean: t("about.facultiesList.engineeringDean"),
      count: t("about.facultiesList.engineeringCount", "7 Departments"),
      desc: t("about.facultiesList.engineeringDesc"),
      link: "/faculty/faculty-of-engineering",
      color: "border-blue-500/20 hover:border-blue-500",
    },
    {
      id: "faculty-of-technology",
      name: t("about.facultiesList.technology"),
      dean: t("about.facultiesList.technologyDean"),
      count: t("about.facultiesList.technologyCount", "6 Departments"),
      desc: t("about.facultiesList.technologyDesc"),
      link: "/faculty/faculty-of-technology",
      color: "border-purple-500/20 hover:border-purple-500",
    },
    {
      id: "faculty-of-service-and-digitalization",
      name: t("about.facultiesList.service"),
      dean: t("about.facultiesList.serviceDean"),
      count: t("about.facultiesList.serviceCount", "7 Departments"),
      desc: t("about.facultiesList.serviceDesc"),
      link: "/faculty/faculty-of-service-and-digitalization",
      color: "border-emerald-500/20 hover:border-emerald-500",
    },
    {
      id: "faculty-of-natural-resources-management",
      name: t("about.facultiesList.natural"),
      dean: t("about.facultiesList.naturalDean"),
      count: t("about.facultiesList.naturalCount", "6 Departments"),
      desc: t("about.facultiesList.naturalDesc"),
      link: "/faculty/faculty-of-natural-resources-management",
      color: "border-amber-500/20 hover:border-amber-500",
    },
  ];

  const isRtl = language === "ar";

  return (
    <div className={`bg-white min-h-screen ${isRtl ? "text-right" : ""}`}>
      {/* SECTION 1: Immersive Hero Section (No standard breadcrumbs) */}
      <section className="relative pt-32 pb-24 overflow-hidden bg-linear-to-br from-navy-dark via-navy to-navy-dark text-white">
        {/* Glow Effects */}
        <div className="absolute top-0 right-0 w-125 h-125 bg-primary/20 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3 pointer-events-none" />
        <div className="absolute bottom-0 left-0 w-100 h-100 bg-indigo-500/10 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3 pointer-events-none" />
        {/* Grid pattern overlay */}
        <div className="absolute inset-0 bg-[radial-gradient(#ffffff0a_1px,transparent_1px)] bg-size-[16px_16px] pointer-events-none" />

        <div className="container mx-auto px-4 md:px-8 max-w-7xl relative z-10">
          <div
            className={`grid grid-cols-1 lg:grid-cols-12 gap-12 items-center ${isRtl ? "direction-rtl" : ""}`}
          >
            {/* Left Headline */}
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
              className={`lg:col-span-7 flex flex-col gap-6 ${isRtl ? "items-start text-right" : ""}`}
            >
              <div
                className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/10 self-start text-xs font-bold uppercase tracking-widest text-white!"
                style={{ color: "#ffffff" }}
              >
                <Sparkles className="w-3.5 h-3.5 text-white" />
                {t("about.hero.badge")}
              </div>

              <h1
                className="text-4xl md:text-6xl font-black tracking-tight leading-tight uppercase font-heading text-white!"
                style={{ color: "#ffffff" }}
              >
                {t("about.hero.title")}
              </h1>

              <p className="text-white/80 text-sm md:text-base leading-relaxed max-w-2xl font-semibold">
                {t("about.hero.subtitle")}
              </p>

              <div className="flex flex-wrap gap-4 mt-4">
                <Link
                  to="/contact"
                  className="bg-primary hover:bg-primary-hover text-white px-8 py-4 rounded-2xl text-sm font-extrabold transition-all shadow-lg shadow-primary/20 flex items-center gap-2 group"
                >
                  {t("about.hero.admissionsBtn")}
                  <ArrowRight
                    className={`w-4 h-4 transition-transform ${isRtl ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                  />
                </Link>
                <Link
                  to="/video-bdtu"
                  className="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-8 py-4 rounded-2xl text-sm font-extrabold transition-all backdrop-blur-xs flex items-center gap-2"
                >
                  {t("about.hero.campusBtn")}
                </Link>
              </div>
            </motion.div>

            {/* Right Interactive Stats Card */}
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="lg:col-span-5 relative"
            >
              <div className="bg-white/5 border border-white/10 rounded-3xl p-8 backdrop-blur-xl shadow-2xl relative overflow-hidden">
                <div className="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-2xl pointer-events-none" />
                <h3
                  className={`text-xl font-bold mb-6 text-white! flex items-center gap-2 ${isRtl ? "flex-row-reverse" : ""}`}
                  style={{ color: "#ffffff" }}
                >
                  <Compass className="w-5 h-5 text-white" />
                  {t("about.hero.profileTitle")}
                </h3>

                <ul className="flex flex-col gap-4 font-semibold text-xs text-white/90">
                  <li
                    className={`flex items-start gap-3 bg-white/5 p-4 rounded-2xl ${isRtl ? "flex-row-reverse" : ""}`}
                  >
                    <ShieldCheck className="w-5 h-5 text-white shrink-0 mt-0.5" />
                    <div className={isRtl ? "text-right" : ""}>
                      <strong
                        className="block text-white! text-sm mb-0.5"
                        style={{ color: "#ffffff" }}
                      >
                        {t("about.hero.autonomyTitle")}
                      </strong>
                      {t("about.hero.autonomyDesc")}
                    </div>
                  </li>
                  <li
                    className={`flex items-start gap-3 bg-white/5 p-4 rounded-2xl ${isRtl ? "flex-row-reverse" : ""}`}
                  >
                    <Award className="w-5 h-5 text-white shrink-0 mt-0.5" />
                    <div className={isRtl ? "text-right" : ""}>
                      <strong
                        className="block text-white! text-sm mb-0.5"
                        style={{ color: "#ffffff" }}
                      >
                        {t("about.hero.qsTitle")}
                      </strong>
                      {t("about.hero.qsDesc")}
                    </div>
                  </li>
                  <li
                    className={`flex items-start gap-3 bg-white/5 p-4 rounded-2xl ${isRtl ? "flex-row-reverse" : ""}`}
                  >
                    <History className="w-5 h-5 text-white shrink-0 mt-0.5" />
                    <div className={isRtl ? "text-right" : ""}>
                      <strong
                        className="block text-white! text-sm mb-0.5"
                        style={{ color: "#ffffff" }}
                      >
                        {t("about.hero.legacyTitle")}
                      </strong>
                      {t("about.hero.legacyDesc")}
                    </div>
                  </li>
                </ul>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* SECTION 2: Stacked University Core (Identity, Mission & Vision, Core Values) */}
      <section className="py-20 bg-white">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl space-y-24">
          {/* Subsection 1: Our Identity */}
          <div className="bg-primary-light/50 border border-primary-light rounded-4xl p-8 md:p-12 shadow-xs">
            <div
              className={`grid grid-cols-1 lg:grid-cols-12 gap-8 items-center ${isRtl ? "direction-rtl" : ""}`}
            >
              <div
                className={`lg:col-span-7 flex flex-col gap-5 ${isRtl ? "items-start text-right" : ""}`}
              >
                <span className="self-start inline-flex bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full">
                  {t("about.identity.badge")}
                </span>
                <h2 className="text-2xl md:text-3xl font-extrabold text-navy uppercase leading-tight font-heading">
                  {t("about.identity.title")}
                </h2>
                <p className="text-gray-500 text-sm leading-relaxed font-semibold">
                  {t("about.identity.desc1")}
                </p>
                <p className="text-gray-500 text-sm leading-relaxed font-semibold">
                  {t("about.identity.desc2")}
                </p>
              </div>
              <div className="lg:col-span-5 relative">
                <div className="aspect-4/3 rounded-3xl overflow-hidden shadow-lg bg-gray-50 border border-gray-100">
                  <img
                    src="/assets/img/bb30a27931a9.jpg"
                    alt={t("about.identity.title")}
                    width="1200"
                    height="800"
                    className="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Subsection 2: Mission & Vision */}
          <div>
            <div className="text-center max-w-2xl mx-auto mb-12">
              <span className="bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full mb-3 inline-block font-heading">
                {t("about.goals.badge")}
              </span>
              <h2 className="text-3xl font-extrabold text-navy font-heading">
                {t("about.goals.title")}
              </h2>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div
                className={`bg-white border border-gray-100 p-8 rounded-3xl shadow-xs relative overflow-hidden group hover:shadow-md transition-shadow ${isRtl ? "text-right" : ""}`}
              >
                <div className="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-full translate-x-6 -translate-y-6" />
                <div
                  className={`w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-6 ${isRtl ? "mr-0 ml-auto" : ""}`}
                >
                  <Target className="w-6 h-6" />
                </div>
                <h3 className="text-xl font-extrabold text-navy mb-3 font-heading">
                  {t("about.goals.missionTitle")}
                </h3>
                <p className="text-gray-500 text-xs md:text-sm leading-relaxed font-semibold">
                  {t("about.goals.missionDesc")}
                </p>
              </div>

              <div
                className={`bg-white border border-gray-100 p-8 rounded-3xl shadow-xs relative overflow-hidden group hover:shadow-md transition-shadow ${isRtl ? "text-right" : ""}`}
              >
                <div className="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full translate-x-6 -translate-y-6" />
                <div
                  className={`w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center mb-6 ${isRtl ? "mr-0 ml-auto" : ""}`}
                >
                  <Eye className="w-6 h-6" />
                </div>
                <h3 className="text-xl font-extrabold text-navy mb-3 font-heading">
                  {t("about.goals.visionTitle")}
                </h3>
                <p className="text-gray-500 text-xs md:text-sm leading-relaxed font-semibold">
                  {t("about.goals.visionDesc")}
                </p>
              </div>
            </div>
          </div>

          {/* Subsection 3: Core Values */}
          <div>
            <div className="text-center max-w-2xl mx-auto mb-12">
              <span className="bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full mb-3 inline-block font-heading">
                {t("about.values.badge")}
              </span>
              <h2 className="text-3xl font-extrabold text-navy font-heading">
                {t("about.values.title")}
              </h2>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div
                className={`bg-white border border-gray-100 p-6 rounded-2xl shadow-xs hover:shadow-md transition-shadow ${isRtl ? "text-right" : ""}`}
              >
                <div
                  className={`w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-4 ${isRtl ? "mr-0 ml-auto" : ""}`}
                >
                  <ShieldCheck className="w-5 h-5" />
                </div>
                <h4 className="font-extrabold text-navy mb-2 font-heading">
                  {t("about.values.integrityTitle")}
                </h4>
                <p className="text-gray-500 text-xs leading-relaxed font-semibold">
                  {t("about.values.integrityDesc")}
                </p>
              </div>

              <div
                className={`bg-white border border-gray-100 p-6 rounded-2xl shadow-xs hover:shadow-md transition-shadow ${isRtl ? "text-right" : ""}`}
              >
                <div
                  className={`w-10 h-10 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center mb-4 ${isRtl ? "mr-0 ml-auto" : ""}`}
                >
                  <Zap className="w-5 h-5" />
                </div>
                <h4 className="font-extrabold text-navy mb-2 font-heading">
                  {t("about.values.innovationTitle")}
                </h4>
                <p className="text-gray-500 text-xs leading-relaxed font-semibold">
                  {t("about.values.innovationDesc")}
                </p>
              </div>

              <div
                className={`bg-white border border-gray-100 p-6 rounded-2xl shadow-xs hover:shadow-md transition-shadow ${isRtl ? "text-right" : ""}`}
              >
                <div
                  className={`w-10 h-10 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center mb-4 ${isRtl ? "mr-0 ml-auto" : ""}`}
                >
                  <Globe className="w-5 h-5" />
                </div>
                <h4 className="font-extrabold text-navy mb-2 font-heading">
                  {t("about.values.inclusivityTitle")}
                </h4>
                <p className="text-gray-500 text-xs leading-relaxed font-semibold">
                  {t("about.values.inclusivityDesc")}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* SECTION 3: Key University Stats Grid */}
      <section className="py-20 bg-primary-light/30 border-y border-gray-100">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <span className="bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full mb-3 inline-block font-heading">
              {t("about.stats.badge")}
            </span>
            <h2 className="text-3xl font-extrabold text-navy font-heading">
              {t("about.stats.title")}
            </h2>
            <p className="text-gray-500 text-xs md:text-sm font-semibold mt-2">
              {t("about.stats.subtitle")}
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {stats.map((item, idx) => {
              const Icon = item.icon;
              return (
                <motion.div
                  key={idx}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.5, delay: idx * 0.05 }}
                  className={`bg-white border border-gray-100 p-6 rounded-3xl shadow-xs hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group ${isRtl ? "text-right" : ""}`}
                >
                  <div
                    className={`flex justify-between items-start mb-4 ${isRtl ? "flex-row-reverse" : ""}`}
                  >
                    <span className="text-3xl font-black text-navy group-hover:text-primary transition-colors">
                      {item.number}
                    </span>
                    <div className="w-10 h-10 rounded-xl bg-primary-light text-primary flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                      <Icon className="w-5 h-5 text-primary" />
                    </div>
                  </div>
                  <h4 className="text-sm font-extrabold text-navy mb-1 font-heading">
                    {item.label}
                  </h4>
                  <p className="text-gray-400 text-[11px] leading-relaxed font-semibold">
                    {item.desc}
                  </p>
                </motion.div>
              );
            })}
          </div>
        </div>
      </section>

      {/* SECTION 4: Interactive Faculties Showcase */}
      <section className="py-20 bg-white">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <span className="bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full mb-3 inline-block font-heading">
              {t("about.facultiesList.badge")}
            </span>
            <h2 className="text-3xl font-extrabold text-navy font-heading">
              {t("about.facultiesList.title")}
            </h2>
            <p className="text-gray-500 text-xs md:text-sm font-semibold mt-2">
              {t("about.facultiesList.subtitle")}
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {faculties.map((f, idx) => (
              <motion.div
                key={f.id}
                initial={{ opacity: 0, scale: 0.98 }}
                whileInView={{ opacity: 1, scale: 1 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: idx * 0.05 }}
                className={`bg-white border p-8 rounded-3xl shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between group ${isRtl ? "text-right" : ""} ${f.color}`}
              >
                <div>
                  <div
                    className={`flex justify-between items-start mb-6 ${isRtl ? "flex-row-reverse" : ""}`}
                  >
                    <div>
                      <span className="text-[10px] text-primary font-bold uppercase tracking-wider block mb-1">
                        {t("about.facultiesList.facultyBadge")}
                      </span>
                      <h3 className="text-xl font-extrabold text-navy group-hover:text-primary transition-colors duration-300 font-heading">
                        {f.name}
                      </h3>
                    </div>
                    <span className="text-xs font-bold text-gray-400 bg-gray-50 border border-gray-100 px-3 py-1.5 rounded-xl shrink-0">
                      {f.count}
                    </span>
                  </div>

                  <p className="text-gray-500 text-xs md:text-sm leading-relaxed mb-6 font-semibold">
                    {f.desc}
                  </p>

                  <div className="border-t border-gray-100 pt-4 mb-8">
                    <span className="text-xs text-gray-400 font-semibold block mb-0.5">
                      {t("about.facultiesList.deanLabel")}
                    </span>
                    <span className="text-sm font-extrabold text-navy font-heading">
                      {f.dean}
                    </span>
                  </div>
                </div>

                <Link
                  to={f.link}
                  className={`inline-flex items-center gap-2 text-xs font-bold text-primary group-hover:text-primary-hover hover:underline ${isRtl ? "flex-row-reverse" : ""}`}
                >
                  {t("about.facultiesList.exploreBtn")}
                  <ArrowRight
                    className={`w-4 h-4 transition-transform ${isRtl ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                  />
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* SECTION 5: Rector Spotlight Welcome */}
      <section className="py-20 bg-primary-light/20 border-t border-gray-100">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="bg-white border border-gray-100 rounded-4xl p-8 md:p-12 shadow-xs">
            <div
              className={`grid grid-cols-1 lg:grid-cols-12 gap-12 items-center ${isRtl ? "direction-rtl" : ""}`}
            >
              {/* Photo */}
              <div className="lg:col-span-4 flex flex-col items-center">
                <div className="relative w-64 h-64 md:w-72 md:h-72 rounded-3xl overflow-hidden shadow-xl border-4 border-white bg-gray-50">
                  {rectorProfile?.image ? (
                    <img
                      src={rectorProfile.image}
                      alt={rectorProfile.name || ""}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <div className="w-full h-full bg-primary/10" />
                  )}
                  <div className="absolute inset-0 bg-linear-to-t from-navy/60 via-transparent to-transparent" />
                </div>
                <div className="text-center mt-5">
                  <h4 className="text-lg font-extrabold text-navy font-heading">
                    {rectorProfile?.name || t("about.rector.name")}
                  </h4>
                  <p className="text-xs text-primary font-bold uppercase tracking-wider mt-1 font-heading">
                    {rectorProfile?.position || rectorProfile?.title || t("about.rector.name")}
                  </p>
                  <p className="text-[10px] text-gray-400 font-semibold mt-0.5">
                    {rectorProfile?.degree || t("about.rector.degree")}
                  </p>
                </div>
              </div>

              {/* Message */}
              <div
                className={`lg:col-span-8 flex flex-col gap-5 ${isRtl ? "items-start text-right" : ""}`}
              >
                <span className="self-start inline-flex items-center gap-1 bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full font-heading">
                  {t("about.rector.badge")}
                </span>
                <h3 className="text-2xl md:text-3xl font-extrabold text-navy leading-snug font-heading">
                  {t("about.rector.title")}
                </h3>

                <p className="text-gray-500 text-xs md:text-sm leading-relaxed font-semibold italic">
                  "{t("about.rector.quote1")}"
                </p>
                <p className="text-gray-500 text-xs md:text-sm leading-relaxed font-semibold">
                  "{t("about.rector.quote2")}"
                </p>

                <div className="flex gap-4 mt-4 border-t border-gray-100 pt-6">
                  <Link
                    to="/profile/rector"
                    className="bg-navy hover:bg-navy-dark text-white px-6 py-3 rounded-xl text-xs font-bold transition-colors shadow-sm inline-flex items-center gap-2"
                  >
                    {t("about.rector.profileBtn")}
                  </Link>
                </div>
              </div>
            </div>

            {/* Rector's Special Appeal (Awareness & Safety) */}
            <div className="border-t border-gray-100 my-10 pt-10">
              <div className="max-w-3xl mx-auto text-center mb-8">
                <span className="inline-flex items-center gap-1 bg-red-500/10 text-red-500 text-[10px] font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full font-heading mb-3">
                  {t("about.rector.appealBadge")}
                </span>
                <h3 className="text-xl md:text-2xl font-extrabold text-navy font-heading mb-3">
                  {t("about.rector.appealTitle")}
                </h3>
                <p className="text-gray-500 text-xs md:text-sm leading-relaxed font-semibold italic">
                  "{t("about.rector.appealQuote")}"
                </p>
              </div>

              {/* 3 Audience Cards */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {/* To Students */}
                <div
                  className={`bg-blue-50/30 border border-blue-100 rounded-3xl p-6 hover:shadow-md transition-all duration-300 group ${isRtl ? "text-right" : ""}`}
                >
                  <div
                    className={`w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform ${isRtl ? "mr-0 ml-auto" : ""}`}
                  >
                    <Users className="w-5 h-5" />
                  </div>
                  <h4 className="text-md font-bold text-navy mb-2 font-heading">
                    {t("about.rector.studentsTitle")}
                  </h4>
                  <p className="text-gray-500 text-xs leading-relaxed font-semibold">
                    {t("about.rector.studentsDesc")}
                  </p>
                </div>

                {/* To Parents */}
                <div
                  className={`bg-emerald-50/30 border border-emerald-100 rounded-3xl p-6 hover:shadow-md transition-all duration-300 group ${isRtl ? "text-right" : ""}`}
                >
                  <div
                    className={`w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform ${isRtl ? "mr-0 ml-auto" : ""}`}
                  >
                    <Compass className="w-5 h-5" />
                  </div>
                  <h4 className="text-md font-bold text-navy mb-2 font-heading">
                    {t("about.rector.parentsTitle")}
                  </h4>
                  <p className="text-gray-500 text-xs leading-relaxed font-semibold">
                    {t("about.rector.parentsDesc")}
                  </p>
                </div>

                {/* To Teachers */}
                <div
                  className={`bg-violet-50/30 border border-violet-100 rounded-3xl p-6 hover:shadow-md transition-all duration-300 group ${isRtl ? "text-right" : ""}`}
                >
                  <div
                    className={`w-10 h-10 rounded-xl bg-violet-500/10 text-violet-500 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform ${isRtl ? "mr-0 ml-auto" : ""}`}
                  >
                    <GraduationCap className="w-5 h-5" />
                  </div>
                  <h4 className="text-md font-bold text-navy mb-2 font-heading">
                    {t("about.rector.teachersTitle")}
                  </h4>
                  <p className="text-gray-500 text-xs leading-relaxed font-semibold">
                    {t("about.rector.teachersDesc")}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* SECTION 6: Detailed Historical Timeline */}
      <section className="py-20 bg-white">
        <div className="container mx-auto px-4 md:px-8 max-w-5xl">
          <div className="text-center max-w-2xl mx-auto mb-20">
            <span className="inline-flex items-center gap-1.5 bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full mb-3 font-heading">
              <History className="w-3.5 h-3.5" /> {t("about.timeline.badge")}
            </span>
            <h2 className="text-3xl font-extrabold text-navy font-heading">
              {t("about.timeline.title")}
            </h2>
            <p className="text-gray-500 text-xs md:text-sm font-semibold mt-2">
              {t("about.timeline.subtitle")}
            </p>
          </div>

          {/* Timeline - clean flex layout */}
          <div className="relative flex flex-col gap-10">
            {/* Vertical line */}
            <div
              className={`absolute top-0 bottom-0 w-0.5 bg-primary/20 ${isRtl ? "right-30 md:right-38" : "left-30 md:left-38"}`}
            />

            {timelineEvents.map((evt, idx) => (
              <div key={idx} className="flex items-start gap-0 relative group">
                {/* Year column */}
                <div className="w-28 md:w-36 shrink-0 pt-5 text-end pe-4">
                  <span className="text-xl md:text-2xl font-black text-navy group-hover:text-primary transition-colors duration-300 font-heading block">
                    {evt.year}
                  </span>
                </div>

                {/* Dot — centered on the line */}
                <div className="relative shrink-0 flex items-start justify-center w-4 pt-4.5">
                  <div className="w-4 h-4 rounded-full bg-white border-4 border-primary transition-all group-hover:bg-primary group-hover:scale-110 z-10 shrink-0" />
                </div>

                {/* Card */}
                <div className="flex-1 ps-4 md:ps-6 pb-2">
                  <div
                    className={`bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-xs group-hover:shadow-md transition-all duration-300 ${isRtl ? "text-right" : ""}`}
                  >
                    <h3 className="font-extrabold text-navy text-lg mb-2 group-hover:text-primary transition-colors font-heading">
                      {evt.title}
                    </h3>
                    <p className="text-gray-500 text-xs md:text-sm leading-relaxed font-semibold">
                      {evt.desc}
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
