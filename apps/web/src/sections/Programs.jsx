import React, { useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Clock, GraduationCap, Search, X } from "lucide-react";
import { programsData } from "../data/programsData";
import { useLanguage } from "../context/LanguageContext";

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

const keyMap = {
  "software-engineering": "softwareEngineering",
  "mechanical-engineering": "mechanicalEngineering",
  "architecture": "architecture",
  "economics": "economics",
  "oil-gas-engineering": "oilGasEngineering",
  "power-engineering": "powerEngineering",
  "construction": "construction",
  "metallurgy": "metallurgy",
  "automotive-engineering": "automotiveEngineering",
  "cybersecurity": "cybersecurity",
  "food-technology": "foodTechnology",
  "food-technology-60720100": "foodTechnology",
  "textile-engineering": "textileEngineering"
};

export default function Programs({ limit, showRemaining }) {
  const { t, language } = useLanguage();
  const [selectedFaculty, setSelectedFaculty] = useState("all");
  const [selectedDegree, setSelectedDegree] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");

  const getProgramKey = (program) => keyMap[program.id] || program.id;
  const getProgramName = (program) => t(`programs.${program.id}.name`, t(`home.programs.list.${getProgramKey(program)}.name`, program.name));
  const getProgramDescription = (program) => t(`programs.${program.id}.description`, t(`home.programs.list.${getProgramKey(program)}.desc`, program.description));
  
  const featuredIds = [
    "power-engineering",
    "mechanical-engineering",
    "oil-gas-engineering",
    "automotive-engineering",
    "software-engineering",
    "cybersecurity"
  ];
  
  let displayPrograms = programsData;
  if (limit) {
    displayPrograms = programsData.filter(p => featuredIds.includes(p.id));
  } else if (showRemaining) {
    displayPrograms = programsData.filter(p => !featuredIds.includes(p.id));
  }

  // Filter full catalog dynamically
  if (!limit) {
    if (selectedFaculty !== "all") {
      displayPrograms = displayPrograms.filter(p => p.facultyId === selectedFaculty);
    }
    if (selectedDegree !== "all") {
      displayPrograms = displayPrograms.filter(p => p.degree.toLowerCase() === selectedDegree.toLowerCase());
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

  return (
    <section id="programs" className="py-24 bg-primary-light/50 border-t border-gray-100">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {t("home.programs.tag")}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {t("home.programs.title")}
          </p>
          <div className="w-16 h-1 bg-primary mx-auto mt-4 rounded-full" />
        </div>

        {/* Filters Bar - Only visible on full programs catalog page */}
        {!limit && (
          <div className="flex flex-col gap-4 mb-12">
            {/* Search Bar */}
            <div className="relative">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
              <input
                type="text"
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                placeholder={t("programs.searchPlaceholder", "Search programs by name, code, or field...")}
                className="w-full pl-11 pr-11 py-3.5 bg-white border border-gray-200 rounded-2xl text-sm font-semibold text-navy placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
              />
              {searchQuery && (
                <button
                  onClick={() => setSearchQuery("")}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-navy transition-colors"
                >
                  <X className="w-4 h-4" />
                </button>
              )}
            </div>

            {/* Faculty & Degree Filters */}
            <div className="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4 bg-white p-5 rounded-3xl border border-gray-100 shadow-sm">
              {/* Faculty Filters */}
              <div className="flex flex-wrap gap-2 text-start justify-center lg:justify-start">
                {[
                  { id: "all", label: t("common.allFaculties", "All Faculties") },
                  { id: "faculty-of-technology", label: t("faculties.faculty-of-technology.short", "Technology") },
                  { id: "faculty-of-engineering", label: t("faculties.faculty-of-engineering.short", "Engineering") },
                  { id: "faculty-of-service-and-digitalization", label: t("faculties.faculty-of-service-and-digitalization.short", "Service & Digitalization") },
                  { id: "faculty-of-natural-resources-management", label: t("faculties.faculty-of-natural-resources-management.short", "Natural Resources") }
                ].map(fac => (
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
              <div className="flex gap-2 justify-center lg:justify-end border-t pt-4 border-gray-100 lg:border-t-0 lg:pt-0">
                {[
                  { id: "all", label: t("common.allDegrees", "All Degrees") },
                  { id: "bachelor", label: t("home.programs.degrees.bachelor", "Bachelor") },
                  { id: "master", label: t("home.programs.degrees.master", "Master") },
                  { id: "phd", label: t("home.programs.degrees.phd", "PhD") }
                ].map(deg => (
                  <button
                    key={deg.id}
                    onClick={() => setSelectedDegree(deg.id)}
                    className={`text-xs font-extrabold px-4 py-2.5 rounded-xl transition-all cursor-pointer ${
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
            {(searchQuery || selectedFaculty !== "all" || selectedDegree !== "all") && (
              <p className="text-xs font-bold text-gray-400 text-center">
                {displayPrograms.length} {t("programs.resultsFound", "programs found")}
              </p>
            )}
          </div>
        )}

        {/* Programs Grid */}
        {displayPrograms.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-xs max-w-lg mx-auto">
            <GraduationCap className="w-12 h-12 text-gray-300 mx-auto mb-4 animate-bounce" />
            <p className="text-lg font-extrabold text-navy mb-1">{t("programs.noResults.title", "No programs found")}</p>
            <p className="text-sm text-gray-500">{t("programs.noResults.desc", "Try adjusting your filters to find suitable programs.")}</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {displayPrograms.map((program) => {
            const Icon = program.icon;
            const colors = colorConfig[program.color] || colorConfig.cyan;
            
            const pName = getProgramName(program);
            const pDesc = getProgramDescription(program);
            
            const degreeKey = program.degree.toLowerCase(); // bachelor, master
            const pDegree = t(`home.programs.degrees.${degreeKey}`, program.degree);
            
            const durationKey = program.duration.includes("4") ? "years4" : program.duration.includes("5") ? "years5" : "years2";
            const pDuration = t(`home.programs.durations.${durationKey}`, program.duration);

            return (
              <div
                key={program.id}
                className={`group bg-white border ${colors.border} p-8 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1.5 flex flex-col text-start`}
              >
                {/* Header: Icon & Badges */}
                <div className="flex items-center justify-between mb-6">
                  <div className={`w-12 h-12 rounded-xl flex items-center justify-center transition-colors duration-300 ${colors.icon}`}>
                    <Icon className="w-5 h-5" />
                  </div>
                  <div className="flex gap-2">
                    <span className={`inline-flex items-center gap-1 text-[10px] font-extrabold uppercase border px-2.5 py-1 rounded-full ${colors.badge}`}>
                      <GraduationCap className="w-3.5 h-3.5 shrink-0" />
                      {pDegree}
                    </span>
                    <span className="inline-flex items-center gap-1 text-[10px] font-bold text-gray-500 bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-full">
                      <Clock className="w-3.5 h-3.5 shrink-0" />
                      {pDuration}
                    </span>
                  </div>
                </div>

                {/* Content */}
                <h3 className="text-xl font-bold text-navy mb-3 group-hover:text-primary transition-colors">
                  {pName}
                </h3>
                <p className="text-gray-500 text-sm leading-relaxed mb-6 grow">
                  {pDesc}
                </p>

                {/* Footer Link */}
                <div className="pt-4 border-t border-gray-50">
                  <Link
                    to={`/programs/${program.id}`}
                    className="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-primary group-hover:underline"
                  >
                    {t("home.programs.explore")}
                    <span className={`transition-transform duration-300 ${language === 'ar' ? 'rotate-180 group-hover:-translate-x-1' : 'group-hover:translate-x-1'}`}>→</span>
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
              {t("home.programs.viewAll")}
              <ArrowRight className={`w-4 h-4 transition-transform ${language === 'ar' ? 'rotate-180' : ''}`} />
            </Link>
          </div>
        )}

      </div>
    </section>
  );
}
