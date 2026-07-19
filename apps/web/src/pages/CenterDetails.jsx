import React, { useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import {
  BookOpen,
  UserCheck,
  HelpCircle,
  Phone,
  Mail,
  Clock,
  ShieldCheck,
} from "lucide-react";
import { visibleCentersData } from "../data/universityData";
import { useLanguage } from "../context/LanguageContext";

export default function CenterDetails() {
  const { id } = useParams();
  const { t } = useLanguage();

  // Find center, default to inclusive-it-center if not found
  const visibleCenterIds = Object.keys(visibleCentersData);
  const center =
    visibleCentersData[id] || visibleCentersData[visibleCenterIds[0]];

  const centerName = t(`centers.${center.id}.name`, center.name);
  const centerHeadTitle = t(`centers.${center.id}.headTitle`, center.headTitle);
  const centerOfficeHours = t(
    `centers.${center.id}.officeHours`,
    center.officeHours,
  );
  const centerAbout = t(`centers.${center.id}.about`, center.about);
  const localizedFunctions = t(
    `centers.${center.id}.functions`,
    center.functions,
  );
  const centerFunctions = Array.isArray(localizedFunctions)
    ? localizedFunctions
    : center.functions;

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  const siblingCenters = visibleCenterIds.map((key) => ({
    id: key,
    name: t(`centers.${key}.name`, visibleCentersData[key].name),
  }));

  return (
    <div className="pt-24 bg-white">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          {/* Left Sidebar */}
          <div className="lg:col-span-4 flex flex-col gap-6">
            {/* Sibling switcher */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-lg font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {t("nav.centers", "University Centers")}
              </h4>
              <ul className="flex flex-col gap-3 font-semibold">
                {siblingCenters.map((sib) => {
                  const isActive = sib.id === id;
                  return (
                    <li key={sib.id}>
                      <Link
                        to={`/center/${sib.id}`}
                        className={`block px-4 py-3 rounded-xl text-xs md:text-sm transition-all duration-300 ${
                          isActive
                            ? "bg-primary text-white shadow-md shadow-primary/20 font-bold"
                            : "bg-white text-gray-500 hover:bg-gray-100 hover:text-navy"
                        }`}
                      >
                        {sib.name}
                      </Link>
                    </li>
                  );
                })}
              </ul>
            </div>

            {/* Help Card */}
            <div className="bg-primary text-white p-8 rounded-3xl flex flex-col items-center text-center shadow-lg shadow-primary/20 relative overflow-hidden">
              <div className="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full translate-x-10 -translate-y-10" />
              <HelpCircle className="w-10 h-10 mb-4 text-white/80" />
              <h4 className="text-xl font-bold mb-2">
                {t("common.centerSupport", "Center Support")}
              </h4>
              <p className="text-white/70 text-sm leading-relaxed mb-4">
                {t(
                  "common.centerSupportDesc",
                  "Have questions about specific courses, research equipment, or facilities? Contact the director.",
                )}
              </p>

              <div className="flex flex-col gap-3 font-bold text-xs mb-6 text-white/95 items-center w-full break-all">
                <span className="flex items-center gap-2">
                  <Mail className="w-4 h-4 text-white/80 shrink-0" />
                  {center.email}
                </span>
                <span className="flex items-center gap-2">
                  <Phone className="w-4 h-4 text-white/80 shrink-0" />
                  {center.phone}
                </span>
                <span className="flex items-center gap-2">
                  <Clock className="w-4 h-4 text-white/80 shrink-0" />
                  {centerOfficeHours}
                </span>
              </div>

              <Link
                to="/contact"
                className="bg-white text-primary hover:bg-gray-50 px-6 py-3 rounded-xl text-xs font-bold transition-colors shadow-sm"
              >
                {t("common.contactUniversity", "Contact University")}
              </Link>
            </div>
          </div>

          {/* Right Main Content */}
          <div className="lg:col-span-8 flex flex-col gap-10 lg:pl-8">
            {/* Banner Image */}
            <div className="rounded-3xl overflow-hidden shadow-lg aspect-video bg-gray-100 border border-gray-50">
              <img
                src={center.image}
                alt={centerName}
                className="w-full h-full object-cover"
              />
            </div>

            {/* Center Title */}
            <div>
              <span className="text-xs font-bold uppercase tracking-wider text-primary">
                {t("common.universityStructure", "University Structure")}
              </span>
              <h1 className="text-3xl md:text-4xl font-extrabold text-navy tracking-tight mt-1 mb-4">
                {centerName}
              </h1>
              <div className="w-20 h-1 bg-primary rounded-full" />
            </div>

            {/* About Section */}
            <section className="flex flex-col gap-4">
              <h3 className="text-xl font-extrabold text-navy flex items-center gap-2">
                <BookOpen className="w-5 h-5 text-primary" />
                {t("common.aboutCenter", "About the Center")}
              </h3>
              <p className="text-gray-500 text-sm md:text-base leading-relaxed">
                {centerAbout}
              </p>
            </section>

            {/* Head of Center Profile Card */}
            <section className="flex flex-col gap-4">
              <h3 className="text-xl font-extrabold text-navy flex items-center gap-2">
                <UserCheck className="w-5 h-5 text-primary" />
                {t("common.centerStructure", "Center Structure & Staff")}
              </h3>
              <div className="bg-gray-50 border border-gray-100 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6">
                <img
                  src={center.image}
                  alt={center.head}
                  className="w-20 h-20 rounded-full object-cover border-2 border-white shadow-md shrink-0"
                />
                <div className="grow text-center md:text-left flex flex-col gap-2">
                  <h4 className="text-lg font-bold text-navy">{center.head}</h4>
                  <p className="text-primary text-xs font-bold uppercase tracking-wider">
                    {centerHeadTitle}
                  </p>
                  <p className="text-gray-500 text-sm leading-relaxed mt-2">
                    {t(
                      "common.headDesc",
                      "Supervises daily operations, instructional standards development, training partnerships, and compliance metrics within the center.",
                    )}
                  </p>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-xs font-semibold text-gray-500 border-t border-gray-200/50 pt-4">
                    <span className="flex items-center justify-center md:justify-start gap-2">
                      <Mail className="w-4 h-4 text-primary shrink-0" />
                      {center.email}
                    </span>
                    <span className="flex items-center justify-center md:justify-start gap-2">
                      <Phone className="w-4 h-4 text-primary shrink-0" />
                      {center.phone}
                    </span>
                  </div>
                </div>
              </div>
            </section>

            {/* Core Functions */}
            <section className="flex flex-col gap-4">
              <h3 className="text-xl font-extrabold text-navy flex items-center gap-2">
                <ShieldCheck className="w-5 h-5 text-primary" />
                {t("common.functions", "Functions & Main Activities")}
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {centerFunctions.map((fn, index) => (
                  <div
                    key={index}
                    className="flex gap-3 items-start bg-white p-4 border border-gray-100 rounded-2xl shadow-sm hover:shadow-md transition-shadow"
                  >
                    <div className="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 text-primary mt-0.5 font-bold text-sm">
                      {index + 1}
                    </div>
                    <div>
                      <p className="text-sm font-semibold text-navy leading-snug">
                        {fn}
                      </p>
                      <span className="text-[10px] text-primary font-bold uppercase tracking-wider">
                        {t("about.goals.missionTitle", "Mission")}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  );
}
