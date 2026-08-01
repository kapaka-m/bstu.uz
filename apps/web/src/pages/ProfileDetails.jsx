import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useLanguage } from "../context/LanguageContext";
import { Mail, Phone, Clock, Award, Briefcase, FileText, CheckCircle, GraduationCap } from "lucide-react";
import { administrationService } from "../services/administrationService";
import { staffService } from "../services/staffService";
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

export default function ProfileDetails() {
  const { id } = useParams();
  const { t, language } = useLanguage();
  const navigate = useNavigate();
  const [adminPerson, setAdminPerson] = useState(null);
  const [adminSettings, setAdminSettings] = useState(null);
  const [adminLoading, setAdminLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let alive = true;
    setAdminLoading(true);
    setAdminPerson(null);

    Promise.all([
      administrationService
        .getProfile(id, language)
        .then((profile) => ({ profile, source: "administration" }))
        .catch(() => staffService.getStaffProfile(id).then((profile) => ({ profile, source: "staff" })))
        .catch(() =>
          centerService.getCenters().then((centers) => {
            const matchedCenter = centers.find((item) => (item.headProfileSlug || profileSlugFromName(item.head)) === id);

            if (!matchedCenter) {
              throw new Error(`Profile '${id}' not found`);
            }

            return Promise.all([
              centerService.getCenter(matchedCenter.slug).catch(() => matchedCenter),
              centerService.getSettings().catch(() => null),
            ]).then(([center, centerSettings]) => ({
              source: "center",
              profile: {
                name: center.head,
                full_name: center.head,
                position: center.headTitle,
                title: center.headTitle,
                degree: center.name,
                image: center.image,
                photo: center.image,
                email: center.email,
                phone: center.phone,
                officeHours: center.officeHours,
                about: center.headDescription || centerSettings?.default_head_desc || "",
                details: "",
                achievements: [],
                slug: id,
              },
            }));
          })
        ),
      administrationService.getSettings(language).catch(() => null),
    ])
      .then(([profileResult, settings]) => {
        if (!alive) return;
        const { profile, source } = profileResult;
        setAdminPerson({
          name: profile.name || profile.full_name,
          title: profile.position,
          degree: profile.degree,
          image: profile.image || profile.photo_url || profile.photo,
          email: profile.email,
          phone: profile.phone,
          officeHours: profile.officeHours || profile.office || profile.office_hours,
          about: profile.about || profile.bio,
          details: profile.details,
          achievements: profile.achievements,
          slug: profile.slug,
          profileType: source,
        });
        setAdminSettings(settings);
      })
      .catch(() => {
        if (alive) {
          setAdminPerson(null);
          setAdminSettings(null);
        }
      })
      .finally(() => {
        if (alive) setAdminLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [id, language]);

  let person = null;
  let category = "";

  if (adminPerson) {
    person = adminPerson;
    if (adminPerson.profileType === "administration") {
      category = adminSettings?.profile_category_label || t("common.administration");
    }
  }

  if (!person && adminLoading) {
    return null;
  }

  if (!person) {
    return (
      <div className="pt-24 min-h-[70vh] bg-white flex flex-col items-center justify-center p-6 text-center">
        <h2 className="text-2xl font-extrabold text-navy mb-4">{t("common.notFound")}</h2>
        <button onClick={() => navigate(-1)} className="bg-primary text-white px-6 py-2.5 rounded-xl font-bold text-sm">
          {t("common.goBack")}
        </button>
      </div>
    );
  }

  const getInitials = (name) => {
    const parts = name.replace(/^(Dr\.|Prof\.)\s+/i, "").trim().split(/\s+/);
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[1][0]).toUpperCase();
  };

  const isAdministrationProfile = adminPerson?.profileType === "administration";
  const labels = isAdministrationProfile ? {
    email: adminSettings?.email_address_label || adminSettings?.email_label || t("common.email"),
    phone: adminSettings?.phone_number_label || adminSettings?.phone_label || t("common.phone"),
    officeHours: adminSettings?.office_hours_label || adminSettings?.reception_label || t("common.officeHours"),
    academicRank: adminSettings?.academic_rank_label || t("common.academicRank"),
    biography: adminSettings?.biography_label || t("common.biography"),
    duties: adminSettings?.duties_label || t("common.dutiesResponsibilities"),
    achievements: adminSettings?.achievements_label || t("common.keyAchievements"),
  } : {
    email: t("common.email"),
    phone: t("common.phone"),
    officeHours: t("common.officeHours"),
    academicRank: t("common.academicRank"),
    biography: t("common.biography"),
    duties: t("common.dutiesResponsibilities"),
    achievements: t("common.keyAchievements"),
  };

  const email = String(person.email || "").trim();
  const phone = String(person.phone || "").trim();
  const phoneParts = splitPhone(phone);

  return (
    <div className="pt-24 bg-white min-h-screen text-start">
      <div className="container mx-auto px-4 md:px-8 max-w-6xl py-12">

        {/* Main Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          {/* Left Column: Portrait Card */}
          <div className="lg:col-span-4 bg-gray-50 border border-gray-100 rounded-3xl p-6 text-center shadow-sm flex flex-col items-center gap-6">
            {person.image ? (
              <img
                src={person.image}
                alt={person.name}
                className="w-48 h-48 rounded-2xl object-cover border-4 border-white shadow-md"
                onError={(e) => {
                  e.target.onerror = null;
                  e.target.style.display = "none";
                  e.target.nextSibling.style.display = "flex";
                }}
              />
            ) : null}
            <div
              className="w-48 h-48 rounded-2xl bg-linear-to-br from-primary to-primary-hover text-white shadow-md flex items-center justify-center font-extrabold text-4xl border-4 border-white"
              style={{ display: person.image ? "none" : "flex" }}
            >
              {getInitials(person.name)}
            </div>

            <div className="flex flex-col gap-2">
              {category && (
                <span className="inline-block text-[10px] font-extrabold uppercase tracking-wider text-primary bg-primary/10 px-3 py-1 rounded-full w-fit mx-auto">
                  {category}
                </span>
              )}
              <h1 className="text-xl font-extrabold text-navy leading-snug mt-2">{person.name}</h1>
              <p className="text-xs font-semibold text-gray-500">{person.title}</p>
            </div>

            {/* Quick Contact Info */}
            <div className="w-full border-t border-gray-200/65 pt-6 flex flex-col gap-4 text-xs font-semibold text-gray-600 text-start">
              {email && (
                <div className="flex items-center gap-3">
                  <Mail className="w-5 h-5 text-primary shrink-0" />
                  <div className="min-w-0">
                    <p className="text-[10px] text-gray-400 font-extrabold uppercase tracking-wider">{labels.email}</p>
                    <a
                      href={mailHref(email)}
                      className="text-navy break-all font-bold hover:text-primary transition-colors"
                    >
                      {email}
                    </a>
                  </div>
                </div>
              )}
              {phone && (
                <div className="flex items-center gap-3">
                  <Phone className="w-5 h-5 text-primary shrink-0" />
                  <div className="min-w-0">
                    <p className="text-[10px] text-gray-400 font-extrabold uppercase tracking-wider">{labels.phone}</p>
                    <span
                      className="text-navy font-bold text-left [unicode-bidi:isolate] inline-flex flex-wrap gap-x-1"
                      dir="ltr"
                    >
                      <a href={phoneParts.href} className="hover:text-primary transition-colors">
                        {phoneParts.main}
                      </a>
                      {phoneParts.extension && <span>{phoneParts.extension}</span>}
                    </span>
                  </div>
                </div>
              )}
              <div className="flex items-center gap-3">
                <Clock className="w-5 h-5 text-primary shrink-0" />
                <div>
                  <p className="text-[10px] text-gray-400 font-extrabold uppercase tracking-wider">{labels.officeHours}</p>
                  <p className="text-navy font-bold">{person.officeHours}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Right Column: Bio & Professional details */}
          <div className="lg:col-span-8 flex flex-col gap-8">
            {/* Degree / Academic Rank banner */}
            {person.degree && (
              <div className="bg-primary/5 border border-primary/10 rounded-2xl p-4 flex items-center gap-3">
                <GraduationCap className="w-6 h-6 text-primary shrink-0" />
                <div>
                  <h4 className="text-xs font-extrabold text-navy uppercase tracking-wider">{labels.academicRank}</h4>
                  <p className="text-sm font-bold text-primary leading-snug">{person.degree}</p>
                </div>
              </div>
            )}

            {/* About / Bio */}
            <section className="flex flex-col gap-4">
              <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                <Briefcase className="w-5 h-5 text-primary shrink-0" />
                {labels.biography}
              </h3>
              <p className="text-gray-500 text-sm md:text-base leading-relaxed whitespace-pre-line">
                {person.about}
              </p>
            </section>

            {/* Detailed Duties / Responsibilities (Rectorate only) */}
            {person.details && (
              <section className="flex flex-col gap-4">
                <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                  <FileText className="w-5 h-5 text-primary shrink-0" />
                  {labels.duties}
                </h3>
                <div className="grid grid-cols-1 gap-3">
                  {person.details.split(/[;؛]/).map((duty, idx) => {
                    const cleanDuty = duty.trim();
                    if (!cleanDuty) return null;
                    return (
                      <div key={idx} className="flex gap-3 items-start bg-gray-50 p-4 border border-gray-100 rounded-2xl">
                        <CheckCircle className="w-5 h-5 text-primary shrink-0 mt-0.5" />
                        <p className="text-xs text-navy font-semibold leading-relaxed">{cleanDuty}</p>
                      </div>
                    );
                  })}
                </div>
              </section>
            )}

            {/* Achievements (Rectorate only) */}
            {person.achievements && person.achievements.length > 0 && (
              <section className="flex flex-col gap-4">
                <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                  <Award className="w-5 h-5 text-primary shrink-0" />
                  {labels.achievements}
                </h3>
                <ul className="flex flex-col gap-3">
                  {person.achievements.map((ach, idx) => (
                    <li key={idx} className="flex items-start gap-3 text-sm text-gray-500 font-semibold leading-relaxed">
                      <div className="w-2 h-2 rounded-full bg-primary mt-2 shrink-0" />
                      <span>{ach}</span>
                    </li>
                  ))}
                </ul>
              </section>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
