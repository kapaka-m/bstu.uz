import React, { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Building2, Clock, GraduationCap, Hash, Search, X } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { programService } from "../services/programService";
import { facultyService } from "../services/facultyService";
import { useHomeSection } from "../hooks/useHomeSection";

const colorConfig = {
  cyan: {
    border: "border-cyan-100/80 hover:border-cyan-500",
    icon: "text-cyan-600 bg-cyan-50/80",
    badge: "bg-cyan-50 text-cyan-700 border-cyan-100"
  },
  indigo: {
    border: "border-indigo-100/80 hover:border-indigo-500",
    icon: "text-indigo-600 bg-indigo-50/80",
    badge: "bg-indigo-50 text-indigo-700 border-indigo-100"
  },
  orange: {
    border: "border-orange-100/80 hover:border-orange-500",
    icon: "text-orange-600 bg-orange-50/80",
    badge: "bg-orange-50 text-orange-700 border-orange-100"
  },
  pink: {
    border: "border-pink-100/80 hover:border-pink-500",
    icon: "text-pink-600 bg-pink-50/80",
    badge: "bg-pink-50 text-pink-700 border-pink-100"
  },
  teal: {
    border: "border-teal-100/80 hover:border-teal-500",
    icon: "text-teal-600 bg-teal-50/80",
    badge: "bg-teal-50 text-teal-700 border-teal-100"
  },
  red: {
    border: "border-red-100/80 hover:border-red-500",
    icon: "text-red-600 bg-red-50/80",
    badge: "bg-red-50 text-red-700 border-red-100"
  },
  yellow: {
    border: "border-yellow-100/80 hover:border-yellow-500",
    icon: "text-yellow-600 bg-yellow-50/80",
    badge: "bg-yellow-50 text-yellow-700 border-yellow-100"
  },
  green: {
    border: "border-green-100/80 hover:border-green-500",
    icon: "text-green-600 bg-green-50/80",
    badge: "bg-green-50 text-green-700 border-green-100"
  },
  blue: {
    border: "border-blue-100/80 hover:border-blue-500",
    icon: "text-blue-600 bg-blue-50/80",
    badge: "bg-blue-50 text-blue-700 border-blue-100"
  },
  purple: {
    border: "border-purple-100/80 hover:border-purple-500",
    icon: "text-purple-600 bg-purple-50/80",
    badge: "bg-purple-50 text-purple-700 border-purple-100"
  },
  gray: {
    border: "border-gray-100/80 hover:border-gray-500",
    icon: "text-gray-600 bg-gray-50/80",
    badge: "bg-gray-50 text-gray-700 border-gray-100"
  }
};

const paletteKeys = Object.keys(colorConfig);

const normalizeDegree = (degree) => String(degree || "").toLowerCase();
const normalizeProgram = (program, index) => ({
  ...program,
  id: program.slug || program.id,
  code: program.display_code || program.official_code || program.code || "",
  name: program.name || "",
  description: program.description || "",
  degree: normalizeDegree(program.degree),
  duration: program.duration || "",
  durationYears: program.duration_years,
  facultyId: program.faculty?.slug || program.faculty_slug || program.faculty_id,
  departmentId: program.department?.slug || program.department_slug || program.department_id,
  facultyName: program.faculty?.short_name || program.faculty?.name || "",
  departmentName: program.department?.short_name || program.department?.name || "",
  color: program.color || paletteKeys[index % paletteKeys.length],
  icon: GraduationCap,
});

export default function Programs({ limit, showRemaining, featured = false }) {
  const { t, language, isRtl } = useLanguage();
  const { section: homeSection, loading: homeSectionLoading } = useHomeSection("programs");
  const [selectedFaculty, setSelectedFaculty] = useState("all");
  const [selectedDegree, setSelectedDegree] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [programs, setPrograms] = useState([]);
  const [faculties, setFaculties] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let active = true;
    setLoading(true);

    Promise.all([
      programService.getPrograms(featured ? { featured: "home" } : {}),
      facultyService.getFaculties(),
    ])
      .then(([programItems, facultyItems]) => {
        if (!active) return;
        setPrograms((programItems || []).map(normalizeProgram));
        setFaculties(facultyItems || []);
      })
      .catch(() => {
        if (!active) return;
        setPrograms([]);
        setFaculties([]);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [featured, language]);

  const getProgramName = (program) => program.name;
  const getProgramDescription = (program) => program.description;
  
  let displayPrograms = programs;
  if (limit) {
    displayPrograms = programs.slice(0, Number(limit || 6));
  } else if (showRemaining) {
    displayPrograms = programs.slice(Number(limit || 6));
  }

  // Filter full catalog dynamically
  if (!limit) {
    if (selectedFaculty !== "all") {
      displayPrograms = displayPrograms.filter(p => p.facultyId === selectedFaculty);
    }
    if (selectedDegree !== "all") {
      displayPrograms = displayPrograms.filter(p => normalizeDegree(p.degree) === selectedDegree.toLowerCase());
    }
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      displayPrograms = displayPrograms.filter(p =>
        getProgramName(p).toLowerCase().includes(q) ||
        getProgramDescription(p).toLowerCase().includes(q) ||
        p.name.toLowerCase().includes(q) ||
        (p.description && p.description.toLowerCase().includes(q)) ||
        (p.code && p.code.toLowerCase().includes(q)) ||
        (p.departmentId && p.departmentId.toLowerCase().replace(/-/g, " ").includes(q))
      );
    }
  }

  const facultyFilters = useMemo(() => [
    { id: "all", label: t("common.allFaculties") },
    ...faculties.map((faculty) => ({
      id: faculty.slug || faculty.id,
      label: faculty.short_name || faculty.name || faculty.slug,
    })),
  ], [faculties, t]);

  const homeMode = Boolean(featured || limit);

  if (loading || (homeMode && homeSectionLoading)) {
    return null;
  }

  const sectionEyebrow = homeMode ? homeSection?.eyebrow || "" : t("home.programs.tag");
  const sectionTitle = homeMode ? homeSection?.title || "" : t("home.programs.title");
  const viewAllLabel = homeMode ? homeSection?.cta_label || "" : t("home.programs.viewAll");
  const homeLabel = (key, fallback = "") =>
    homeSection?.items?.find((item) => item.item_key === key)?.label || fallback;
  const exploreLabel = homeMode ? homeLabel("explore_label") : t("home.programs.explore");
  const hasActiveFilters = searchQuery || selectedFaculty !== "all" || selectedDegree !== "all";

  return (
    <section id="programs" className="bg-primary-light/50 py-16 border-t border-gray-100 sm:py-20 lg:py-24">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        
        {/* Section Header */}
        <div className="mx-auto mb-10 max-w-2xl text-center sm:mb-14">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {sectionEyebrow}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {sectionTitle}
          </p>
          <div className="w-16 h-1 bg-primary mx-auto mt-4 rounded-full" />
        </div>

        {/* Filters Bar - Only visible on full programs catalog page */}
        {!limit && (
          <div className="mb-10 flex min-w-0 flex-col gap-4 rounded-3xl border border-gray-100 bg-white p-3 shadow-sm sm:p-5">
            {/* Search Bar */}
            <div className="relative">
              <Search
                className={`pointer-events-none absolute top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 ${
                  isRtl ? "right-4" : "left-4"
                }`}
              />
              <input
                type="text"
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                placeholder={t("programs.searchPlaceholder")}
                className={`w-full rounded-2xl border border-gray-200 bg-gray-50 px-11 py-3.5 text-sm font-semibold text-navy shadow-inner outline-none transition-all placeholder:text-gray-400 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/15 ${
                  isRtl ? "text-right" : "text-left"
                }`}
              />
              {searchQuery && (
                <button
                  onClick={() => setSearchQuery("")}
                  className={`absolute top-1/2 -translate-y-1/2 rounded-lg p-1 text-gray-400 transition-colors hover:bg-white hover:text-navy ${
                    isRtl ? "left-3" : "right-3"
                  }`}
                >
                  <X className="w-4 h-4" />
                </button>
              )}
            </div>

            {/* Faculty & Degree Filters */}
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              {/* Faculty Filters */}
              <div className="student-header-scroll flex max-h-32 flex-wrap gap-2 overflow-y-auto text-start sm:max-h-none lg:justify-start">
                {facultyFilters.map(fac => (
                  <button
                    key={fac.id}
                    onClick={() => setSelectedFaculty(fac.id)}
                    className={`text-xs font-extrabold px-4 py-2.5 rounded-xl transition-all cursor-pointer ${
                      selectedFaculty === fac.id
                        ? "bg-primary text-white shadow-md shadow-primary/10"
                        : "bg-gray-50 text-gray-500 hover:bg-gray-100"
                    }`}
                  >
                    {fac.label}
                  </button>
                ))}
              </div>

              {/* Degree Filters */}
              <div className="grid grid-cols-2 gap-2 border-t border-gray-100 pt-4 sm:flex lg:shrink-0 lg:border-t-0 lg:pt-0">
                {[
                  { id: "all", label: t("common.allDegrees") },
                  { id: "bachelor", label: t("home.programs.degrees.bachelor") },
                  { id: "master", label: t("home.programs.degrees.master") },
                  { id: "phd", label: t("home.programs.degrees.phd") }
                ].map(deg => (
                  <button
                    key={deg.id}
                    onClick={() => setSelectedDegree(deg.id)}
                    className={`text-xs font-extrabold px-4 py-2.5 rounded-xl transition-all cursor-pointer whitespace-nowrap ${
                      selectedDegree === deg.id
                        ? "bg-primary text-white shadow-md shadow-primary/10"
                        : "bg-gray-50 text-gray-500 hover:bg-gray-100"
                    }`}
                  >
                    {deg.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Results count */}
            <div className="flex flex-col gap-2 border-t border-gray-50 pt-3 text-xs font-bold text-gray-400 sm:flex-row sm:items-center sm:justify-between">
              <p>
                <span className="text-navy">{displayPrograms.length}</span> {t("programs.resultsFound")}
              </p>
              {hasActiveFilters && (
                <button
                  type="button"
                  onClick={() => {
                    setSelectedFaculty("all");
                    setSelectedDegree("all");
                    setSearchQuery("");
                  }}
                  className="inline-flex w-fit items-center gap-1.5 rounded-xl bg-gray-50 px-3 py-2 text-[11px] font-extrabold text-primary transition-all hover:bg-primary/10"
                >
                  <X className="h-3.5 w-3.5" />
                  {t("common.clearFilters")}
                </button>
              )}
            </div>
          </div>
        )}

        {/* Programs Grid */}
        {displayPrograms.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-xs max-w-lg mx-auto">
            <GraduationCap className="w-12 h-12 text-gray-300 mx-auto mb-4 animate-bounce" />
            <p className="text-lg font-extrabold text-navy mb-1">{t("programs.noResults.title")}</p>
            <p className="text-sm text-gray-500">{t("programs.noResults.desc")}</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            {displayPrograms.map((program) => {
            const Icon = program.icon || GraduationCap;
            const colors = colorConfig[program.color] || colorConfig.cyan;
            
            const pName = getProgramName(program);
            const pDesc = getProgramDescription(program);
            
            const degreeKey = program.degree.toLowerCase();
            const degreeTranslationKey = degreeKey ? `home.programs.degrees.${degreeKey}` : "";
            const degreeTranslation = homeMode
              ? homeLabel(`degree_${degreeKey}`)
              : degreeTranslationKey
                ? t(degreeTranslationKey)
                : "";
            const pDegree = degreeTranslation === degreeTranslationKey ? program.degree : degreeTranslation;
            
            const durationKey = program.durationYears ? `years${program.durationYears}` : "";
            const durationTranslationKey = durationKey ? `home.programs.durations.${durationKey}` : "";
            const durationTranslation = homeMode
              ? homeLabel(`duration_${durationKey}`)
              : durationTranslationKey
                ? t(durationTranslationKey)
                : "";
            const pDuration =
              program.duration ||
              (durationTranslation === durationTranslationKey ? "" : durationTranslation);

            return (
              <div
                key={program.id}
                className={`group flex min-w-0 flex-col rounded-3xl border ${colors.border} bg-white p-5 text-start shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:p-6`}
              >
                {/* Header: Icon & Badges */}
                <div className="mb-5 flex items-start justify-between gap-3">
                  <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl transition-colors duration-300 ${colors.icon}`}>
                    <Icon className="w-5 h-5" />
                  </div>
                  <div className="flex min-w-0 flex-wrap justify-end gap-2">
                    {pDegree && (
                      <span className={`inline-flex min-w-0 items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-extrabold uppercase ${colors.badge}`}>
                        <GraduationCap className="w-3.5 h-3.5 shrink-0" />
                        <span className="truncate">{pDegree}</span>
                      </span>
                    )}
                    {pDuration && (
                      <span className="inline-flex items-center gap-1 rounded-full border border-gray-100 bg-gray-50 px-2.5 py-1 text-[10px] font-bold text-gray-500">
                        <Clock className="w-3.5 h-3.5 shrink-0" />
                        {pDuration}
                      </span>
                    )}
                  </div>
                </div>

                {/* Content */}
                {program.code && (
                  <div className="mb-3 inline-flex w-fit items-center gap-1.5 rounded-xl bg-gray-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-gray-500">
                    <Hash className="h-3.5 w-3.5 text-primary" />
                    {program.code}
                  </div>
                )}
                <h3 className="mb-3 line-clamp-2 min-h-14 text-lg font-extrabold leading-snug text-navy transition-colors group-hover:text-primary sm:text-xl">
                  {pName}
                </h3>
                <p className="mb-5 line-clamp-4 grow text-sm font-semibold leading-relaxed text-gray-500">
                  {pDesc}
                </p>
                {(program.facultyName || program.departmentName) && (
                  <div className="mb-5 space-y-2 rounded-2xl bg-gray-50 p-3 text-[11px] font-bold text-gray-500">
                    {program.facultyName && (
                      <div className="flex min-w-0 items-center gap-2">
                        <Building2 className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">{program.facultyName}</span>
                      </div>
                    )}
                    {program.departmentName && (
                      <div className="flex min-w-0 items-center gap-2">
                        <GraduationCap className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">{program.departmentName}</span>
                      </div>
                    )}
                  </div>
                )}

                {/* Footer Link */}
                <div className="border-t border-gray-50 pt-4">
                  <Link
                    to={`/programs/${program.id}`}
                    className="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-primary"
                  >
                    {exploreLabel}
                    <ArrowRight className={`h-4 w-4 transition-transform duration-300 ${isRtl ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`} />
                  </Link>
                </div>
              </div>
            );
          })}
          </div>
        )}

        {/* View All Programs Button */}
        {limit && (
          <div className="text-center mt-16">
            <Link
              to="/programs"
              className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-8 py-4 rounded-xl font-extrabold text-sm transition-all shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5"
            >
              {viewAllLabel}
              <ArrowRight className={`w-4 h-4 transition-transform ${isRtl ? 'rotate-180' : ''}`} />
            </Link>
          </div>
        )}

      </div>
    </section>
  );
}
