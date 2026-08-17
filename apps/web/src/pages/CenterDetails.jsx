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
import { centerService, profileSlugFromName } from "../services/centerService";

const mailHref = (email) => `mailto:${String(email || "").trim()}`;
const splitPhone = (phone = "") => {
  const value = String(phone).trim();
  const match = value.match(/^([^()]+?)\s*(\(.+\))\s*$/);
  const main = (match?.[1] || value).trim();

  return {
    main,
    extension: (match?.[2] || "").trim(),
    href: `tel:${main.replace(/[^\d+]/g, "")}`,
  };
};

export default function CenterDetails() {
  const { id } = useParams();
  const { t, language } = useLanguage();
  
  const [centers, setCenters] = useState([]);
  const [center, setCenter] = useState(null);
  const [settings, setSettings] = useState(null);
  const [loading, setLoading] = useState(true);
  const [imageFailed, setImageFailed] = useState(false);

  useEffect(() => {
    window.scrollTo(0, 0);
    setImageFailed(false);
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
      .catch(() => {
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
      <div className="pt-24 bg-white min-h-screen overflow-x-hidden">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <div className="order-2 lg:order-1 lg:col-span-4 space-y-6">
              <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
                <div className="h-6 w-40 bg-gray-200 rounded-full animate-pulse mb-6" />
                <div className="space-y-3">
                  {[1, 2, 3, 4, 5].map((item) => (
                    <div key={item} className="h-11 bg-white rounded-xl animate-pulse" />
                  ))}
                </div>
              </div>
              <div className="bg-primary/90 p-8 rounded-3xl">
                <div className="w-10 h-10 bg-white/20 rounded-full mx-auto animate-pulse" />
                <div className="h-6 w-40 bg-white/20 rounded-full mx-auto mt-4 animate-pulse" />
                <div className="h-4 w-full bg-white/15 rounded-full mt-4 animate-pulse" />
              </div>
            </div>
            <div className="order-1 lg:order-2 lg:col-span-8 space-y-8">
              <div className="aspect-video bg-gray-100 border border-gray-100 rounded-3xl animate-pulse" />
              <div className="space-y-4">
                <div className="h-4 w-28 bg-gray-200 rounded-full animate-pulse" />
                <div className="h-10 w-3/4 bg-gray-200 rounded-full animate-pulse" />
                <div className="h-1 w-20 bg-gray-200 rounded-full animate-pulse" />
              </div>
              <div className="space-y-3">
                <div className="h-5 w-44 bg-gray-200 rounded-full animate-pulse" />
                <div className="h-4 w-full bg-gray-100 rounded-full animate-pulse" />
                <div className="h-4 w-11/12 bg-gray-100 rounded-full animate-pulse" />
                <div className="h-4 w-4/5 bg-gray-100 rounded-full animate-pulse" />
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (!center) {
    return (
      <div className="pt-24 min-h-screen flex flex-col items-center justify-center gap-4 px-4 text-center overflow-x-hidden">
        <h2 className="text-2xl font-bold text-navy">
          {t("common.errorOccurred")}
        </h2>
        <Link to="/" className="text-primary hover:underline font-semibold">
          {t("common.backToHome")}
        </Link>
      </div>
    );
  }

  const siblingCenters = centers.map((c) => ({
    id: c.slug,
    name: c.name,
  }));
  const email = String(center.email || "").trim();
  const phone = String(center.phone || "").trim();
  const phoneParts = splitPhone(phone);
  const headProfileSlug = center.headProfileSlug || profileSlugFromName(center.head);

  return (
    <div className="pt-24 bg-white overflow-x-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 min-w-0">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start min-w-0">
          {/* Left Sidebar */}
          <div className="order-2 lg:order-1 lg:col-span-4 flex flex-col gap-6 min-w-0 lg:sticky lg:top-28">
            {/* Sibling switcher */}
            <div className="bg-primary-light border border-gray-100 p-5 sm:p-6 md:p-8 rounded-2xl lg:rounded-3xl min-w-0">
              <h4 className="text-lg font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {settings?.sidebar_title || t("nav.centers")}
              </h4>
              <ul className="flex flex-col gap-3 font-semibold">
                {siblingCenters.map((sib) => {
                  const isActive = sib.id === id;
                  return (
                    <li key={sib.id}>
                      <Link
                        to={`/center/${sib.id}`}
                        className={`block px-4 py-3 rounded-xl text-xs md:text-sm transition-all duration-300 break-words ${
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
            <div className="bg-primary text-white p-5 sm:p-6 md:p-8 rounded-2xl lg:rounded-3xl flex flex-col items-center text-center shadow-lg shadow-primary/20 relative overflow-hidden min-w-0">
              <div className="absolute top-0 end-0 w-24 h-24 bg-white/5 rounded-full translate-x-10 rtl:-translate-x-10 -translate-y-10" />
              <HelpCircle className="w-10 h-10 mb-4 text-white/80" />
              <h4 className="text-xl font-bold mb-2 break-words">
                {settings?.support_title || t("common.centerSupport")}
              </h4>
              <p className="text-white/70 text-sm leading-relaxed mb-4 break-words">
                {settings?.support_desc || t("common.centerSupportDesc")}
              </p>

              <div className="flex flex-col gap-3 font-bold text-xs mb-6 text-white/95 items-center w-full min-w-0 break-all">
                {email && (
                  <a
                    href={mailHref(email)}
                    className="flex items-center gap-2 hover:text-white transition-colors"
                  >
                    <Mail className="w-4 h-4 text-white/80 shrink-0" />
                    {email}
                  </a>
                )}
                {phone && (
                  <span className="flex items-center justify-center gap-2 min-w-0">
                    <Phone className="w-4 h-4 text-white/80 shrink-0" />
                    <span dir="ltr" className="inline-flex flex-wrap justify-center gap-x-1">
                      <a href={phoneParts.href} className="hover:text-white transition-colors">
                        {phoneParts.main}
                      </a>
                      {phoneParts.extension && <span>{phoneParts.extension}</span>}
                    </span>
                  </span>
                )}
                {center.officeHours && (
                  <span className="flex items-center justify-center gap-2 min-w-0">
                    <Clock className="w-4 h-4 text-white/80 shrink-0" />
                    <span className="break-words">{center.officeHours}</span>
                  </span>
                )}
              </div>

              <Link
                to="/contact"
                className="bg-white text-primary hover:bg-gray-50 px-6 py-3 rounded-xl text-xs font-bold transition-colors shadow-sm"
              >
                {settings?.contact_btn_label || t("common.contactUniversity")}
              </Link>
            </div>
          </div>

          {/* Right Main Content */}
          <div className="order-1 lg:order-2 lg:col-span-8 flex flex-col gap-10 lg:ps-8 min-w-0">
            {/* Banner Image */}
            <div className="rounded-3xl overflow-hidden shadow-lg aspect-video bg-gray-100 border border-gray-55 relative flex items-center justify-center min-w-0">
              {center.image && !imageFailed ? (
                <img
                  src={center.image}
                  alt={center.name}
                  className="w-full h-full object-cover"
                  onError={() => setImageFailed(true)}
                />
              ) : (
                <div className="w-full h-full bg-linear-to-tr from-navy via-navy/95 to-primary flex flex-col items-center justify-center p-8 text-center relative overflow-hidden select-none">
                  <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,var(--tw-gradient-stops))] from-white/5 to-transparent opacity-40 pointer-events-none" />
                  <div className="relative z-10 space-y-3">
                    <span className="inline-flex rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-primary-light">
                      {settings?.structure_label || t("common.universityStructure")}
                    </span>
                    <h2 className="text-xl md:text-2xl lg:text-3xl font-black text-white max-w-lg leading-tight break-words">
                      {center.name}
                    </h2>
                  </div>
                </div>
              )}
            </div>

            {/* Center Title */}
            <div className="min-w-0">
              <span className="text-xs font-bold uppercase tracking-wider text-primary">
                {settings?.structure_label || t("common.universityStructure")}
              </span>
              <h1 className="text-3xl md:text-4xl font-extrabold text-navy tracking-tight mt-1 mb-4 break-words">
                {center.name}
              </h1>
              <div className="w-20 h-1 bg-primary rounded-full" />
            </div>

            {/* About Section */}
            {center.about && (
              <section className="flex flex-col gap-4">
                <h3 className="text-xl font-extrabold text-navy flex items-center gap-2 min-w-0">
                  <BookOpen className="w-5 h-5 text-primary" />
                  <span className="break-words">{settings?.about_label || t("common.aboutCenter")}</span>
                </h3>
                <p className="text-gray-500 text-sm md:text-base leading-relaxed whitespace-pre-line break-words">
                  {center.about}
                </p>
              </section>
            )}

            {/* Head of Center Profile Card */}
            {center.head && (
              <section className="flex flex-col gap-4">
                <h3 className="text-xl font-extrabold text-navy flex items-center gap-2 min-w-0">
                  <UserCheck className="w-5 h-5 text-primary" />
                  <span className="break-words">{settings?.staff_label || t("common.centerStructure")}</span>
                </h3>
                <div className="bg-gray-50 border border-gray-100 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 min-w-0">
                  {center.image && !imageFailed ? (
                    <img
                      src={center.image}
                      alt={center.head}
                      className="w-20 h-20 rounded-full object-cover border-2 border-white shadow-md shrink-0"
                      onError={() => setImageFailed(true)}
                    />
                  ) : (
                    <div className="w-20 h-20 rounded-full bg-linear-to-br from-primary/10 to-primary/20 flex items-center justify-center border border-primary/20 shrink-0 shadow-sm text-primary">
                      <UserCheck className="w-8 h-8" />
                    </div>
                  )}
                  <div className="grow text-center md:text-start flex flex-col gap-2 min-w-0">
                    {headProfileSlug ? (
                      <Link
                        to={`/profile/${headProfileSlug}`}
                        className="text-lg font-bold text-navy hover:text-primary transition-colors break-words"
                      >
                        {center.head}
                      </Link>
                    ) : (
                      <h4 className="text-lg font-bold text-navy break-words">{center.head}</h4>
                    )}
                    {center.headTitle && (
                      <p className="text-primary text-xs font-bold uppercase tracking-wider break-words">
                        {center.headTitle}
                      </p>
                    )}
                    <p className="text-gray-500 text-sm leading-relaxed mt-2 break-words">
                      {center.headDescription || settings?.default_head_desc || t("common.headDesc")}
                    </p>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-xs font-semibold text-gray-500 border-t border-gray-200/50 pt-4">
                      {email && (
                        <a
                          href={mailHref(email)}
                          className="flex items-center justify-center md:justify-start gap-2 hover:text-primary transition-colors min-w-0"
                        >
                          <Mail className="w-4 h-4 text-primary shrink-0" />
                          <span className="break-all">{email}</span>
                        </a>
                      )}
                      {phone && (
                        <span className="flex items-center justify-center md:justify-start gap-2 min-w-0">
                          <Phone className="w-4 h-4 text-primary shrink-0" />
                          <span dir="ltr" className="inline-flex flex-wrap justify-center md:justify-start gap-x-1">
                            <a href={phoneParts.href} className="hover:text-primary transition-colors">
                              {phoneParts.main}
                            </a>
                            {phoneParts.extension && <span>{phoneParts.extension}</span>}
                          </span>
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
                <h3 className="text-xl font-extrabold text-navy flex items-center gap-2 min-w-0">
                  <ShieldCheck className="w-5 h-5 text-primary" />
                  <span className="break-words">{settings?.mission_label || t("common.functions")}</span>
                </h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {center.functions.map((fn, index) => (
                    <div
                      key={index}
                      className="flex gap-3 items-start bg-white p-4 border border-gray-100 rounded-2xl shadow-sm hover:shadow-md transition-shadow min-w-0"
                    >
                      <div className="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0 text-primary mt-0.5 font-bold text-sm">
                        {index + 1}
                      </div>
                      <div className="min-w-0">
                        <p className="text-sm font-semibold text-navy leading-snug break-words">
                          {fn}
                        </p>
                        <span className="text-[10px] text-primary font-bold uppercase tracking-wider break-words">
                          {settings?.function_badge_label || t("common.functions")}
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
