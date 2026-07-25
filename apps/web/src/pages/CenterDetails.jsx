import React, { useEffect, useState } from "react";
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
import { useLanguage } from "../context/LanguageContext";
import { centerService } from "../services/centerService";

export default function CenterDetails() {
  const { id } = useParams();
  const { t, language } = useLanguage();
  
  const [centers, setCenters] = useState([]);
  const [center, setCenter] = useState(null);
  const [settings, setSettings] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;
    setLoading(true);

    Promise.all([
      centerService.getCenters(),
      centerService.getCenter(id),
      centerService.getSettings(),
    ])
      .then(([allCenters, currentCenter, settingsData]) => {
        if (!active) return;
        setCenters(allCenters);
        setCenter(currentCenter);
        setSettings(settingsData);
      })
      .catch((err) => {
        console.error("Failed to fetch center details:", err);
        if (active) {
          setCenter(null);
        }
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [id, language]);

  if (loading) {
    return (
      <div className="pt-24 min-h-screen flex items-center justify-center">
        <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!center) {
    return (
      <div className="pt-24 min-h-screen flex flex-col items-center justify-center gap-4">
        <h2 className="text-2xl font-bold text-navy">
          {t("common.errorOccurred", "Center Not Found")}
        </h2>
        <Link to="/" className="text-primary hover:underline font-semibold">
          {t("common.backToHome", "Back to Home")}
        </Link>
      </div>
    );
  }

  const siblingCenters = centers.map((c) => ({
    id: c.slug,
    name: c.name,
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
                {settings?.sidebar_title || t("nav.centers", "University Centers")}
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
                {settings?.support_title || t("common.centerSupport", "Center Support")}
              </h4>
              <p className="text-white/70 text-sm leading-relaxed mb-4">
                {settings?.support_desc || t(
                  "common.centerSupportDesc",
                  "Have questions about specific courses, research equipment, or facilities? Contact the director.",
                )}
              </p>

              <div className="flex flex-col gap-3 font-bold text-xs mb-6 text-white/95 items-center w-full break-all">
                {center.email && (
                  <span className="flex items-center gap-2">
                    <Mail className="w-4 h-4 text-white/80 shrink-0" />
                    {center.email}
                  </span>
                )}
                {center.phone && (
                  <span className="flex items-center gap-2">
                    <Phone className="w-4 h-4 text-white/80 shrink-0" />
                    <span dir="ltr">{center.phone}</span>
                  </span>
                )}
                {center.officeHours && (
                  <span className="flex items-center gap-2">
                    <Clock className="w-4 h-4 text-white/80 shrink-0" />
                    {center.officeHours}
                  </span>
                )}
              </div>

              <Link
                to="/contact"
                className="bg-white text-primary hover:bg-gray-50 px-6 py-3 rounded-xl text-xs font-bold transition-colors shadow-sm"
              >
                {settings?.contact_btn_label || t("common.contactUniversity", "Contact University")}
              </Link>
            </div>
          </div>

          {/* Right Main Content */}
          <div className="lg:col-span-8 flex flex-col gap-10 lg:pl-8">
            {/* Banner Image */}
            <div className="rounded-3xl overflow-hidden shadow-lg aspect-video bg-gray-100 border border-gray-55 relative flex items-center justify-center">
              {center.image ? (
                <img
                  src={center.image}
                  alt={center.name}
                  className="w-full h-full object-cover"
                />
              ) : (
                <div className="w-full h-full bg-gradient-to-tr from-navy via-navy/95 to-primary flex flex-col items-center justify-center p-8 text-center relative overflow-hidden select-none">
                  <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-white/5 to-transparent opacity-40 pointer-events-none" />
                  <div className="relative z-10 space-y-3">
                    <span className="inline-flex rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-primary-light">
                      {settings?.structure_label || t("common.universityStructure", "University Structure")}
                    </span>
                    <h2 className="text-xl md:text-2xl lg:text-3xl font-black text-white max-w-lg leading-tight">
                      {center.name}
                    </h2>
                  </div>
                </div>
              )}
            </div>

            {/* Center Title */}
            <div>
              <span className="text-xs font-bold uppercase tracking-wider text-primary">
                {settings?.structure_label || t("common.universityStructure", "University Structure")}
              </span>
              <h1 className="text-3xl md:text-4xl font-extrabold text-navy tracking-tight mt-1 mb-4">
                {center.name}
              </h1>
              <div className="w-20 h-1 bg-primary rounded-full" />
            </div>

            {/* About Section */}
            {center.about && (
              <section className="flex flex-col gap-4">
                <h3 className="text-xl font-extrabold text-navy flex items-center gap-2">
                  <BookOpen className="w-5 h-5 text-primary" />
                  {settings?.about_label || t("common.aboutCenter", "About the Center")}
                </h3>
                <p className="text-gray-500 text-sm md:text-base leading-relaxed whitespace-pre-line">
                  {center.about}
                </p>
              </section>
            )}

            {/* Head of Center Profile Card */}
            {center.head && (
              <section className="flex flex-col gap-4">
                <h3 className="text-xl font-extrabold text-navy flex items-center gap-2">
                  <UserCheck className="w-5 h-5 text-primary" />
                  {settings?.staff_label || t("common.centerStructure", "Center Structure & Staff")}
                </h3>
                <div className="bg-gray-50 border border-gray-100 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6">
                  {center.image ? (
                    <img
                      src={center.image}
                      alt={center.head}
                      className="w-20 h-20 rounded-full object-cover border-2 border-white shadow-md shrink-0"
                    />
                  ) : (
                    <div className="w-20 h-20 rounded-full bg-gradient-to-br from-primary/10 to-primary/20 flex items-center justify-center border border-primary/20 shrink-0 shadow-sm text-primary">
                      <UserCheck className="w-8 h-8" />
                    </div>
                  )}
                  <div className="grow text-center md:text-start flex flex-col gap-2">
                    <h4 className="text-lg font-bold text-navy">{center.head}</h4>
                    {center.headTitle && (
                      <p className="text-primary text-xs font-bold uppercase tracking-wider">
                        {center.headTitle}
                      </p>
                    )}
                    <p className="text-gray-500 text-sm leading-relaxed mt-2">
                      {center.headDescription || settings?.default_head_desc || t(
                        "common.headDesc",
                        "Supervises daily operations, instructional standards development, training partnerships, and compliance metrics within the center.",
                      )}
                    </p>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-xs font-semibold text-gray-500 border-t border-gray-200/50 pt-4">
                      {center.email && (
                        <span className="flex items-center justify-center md:justify-start gap-2">
                          <Mail className="w-4 h-4 text-primary shrink-0" />
                          {center.email}
                        </span>
                      )}
                      {center.phone && (
                        <span className="flex items-center justify-center md:justify-start gap-2">
                          <Phone className="w-4 h-4 text-primary shrink-0" />
                          <span dir="ltr">{center.phone}</span>
                        </span>
                      )}
                    </div>
                  </div>
                </div>
              </section>
            )}

            {/* Core Functions */}
            {center.functions && center.functions.length > 0 && (
              <section className="flex flex-col gap-4">
                <h3 className="text-xl font-extrabold text-navy flex items-center gap-2">
                  <ShieldCheck className="w-5 h-5 text-primary" />
                  {settings?.mission_label || t("common.functions", "Functions & Main Activities")}
                </h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {center.functions.map((fn, index) => (
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
                          {settings?.function_badge_label || t("about.goals.missionTitle", "Mission")}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              </section>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
