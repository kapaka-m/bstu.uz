import React, { lazy, Suspense, useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { Award, Briefcase, CheckCircle, Clock, FileText, GraduationCap, Mail, Phone } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { administrationService } from "../services/administrationService";

const ProfileDetails = lazy(() => import("./ProfileDetails"));

export default function AdministrationDetails() {
  const { id } = useParams();
  const { t, language } = useLanguage();
  const navigate = useNavigate();
  const [person, setPerson] = useState(null);
  const [notAdministration, setNotAdministration] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setNotAdministration(false);
    setPerson(null);

    administrationService
      .getProfile(id)
      .then((profile) => {
        if (!alive) return;
        setPerson({
          name: profile.name,
          title: profile.position,
          degree: profile.degree,
          image: profile.image,
          email: profile.email,
          phone: profile.phone,
          officeHours: profile.officeHours,
          about: profile.about,
          details: profile.details,
          achievements: profile.achievements,
        });
      })
      .catch(() => {
        if (alive) setNotAdministration(true);
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [id, language]);

  if (notAdministration) {
    return (
      <Suspense fallback={<div className="pt-24 min-h-[70vh] bg-white" />}>
        <ProfileDetails />
      </Suspense>
    );
  }

  if (loading) {
    return <div className="pt-24 min-h-[70vh] bg-white" />;
  }

  if (!person) {
    return (
      <div className="pt-24 min-h-[70vh] bg-white flex flex-col items-center justify-center p-6 text-center">
        <h2 className="text-2xl font-extrabold text-navy mb-4">{t("common.notFound")}</h2>
        <button onClick={() => navigate(-1)} className="bg-primary text-white px-6 py-2.5 rounded-xl font-bold text-sm">
          {t("common.goBack", "Go Back")}
        </button>
      </div>
    );
  }

  const initials = getInitials(person.name);
  const category = t("common.administration", t("nav.administration", "Administration"));

  return (
    <div className="pt-24 bg-white min-h-screen text-start">
      <div className="container mx-auto px-4 md:px-8 max-w-6xl py-12">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          <div className="lg:col-span-4 bg-gray-50 border border-gray-100 rounded-3xl p-6 text-center shadow-sm flex flex-col items-center gap-6">
            {person.image ? (
              <img
                src={person.image}
                alt={person.name}
                className="w-48 h-48 rounded-2xl object-cover border-4 border-white shadow-md"
                onError={(event) => {
                  event.currentTarget.style.display = "none";
                }}
              />
            ) : (
              <div className="w-48 h-48 rounded-2xl bg-linear-to-br from-primary to-primary-hover text-white shadow-md flex items-center justify-center font-extrabold text-4xl border-4 border-white">
                {initials}
              </div>
            )}

            <div className="flex flex-col gap-2">
              <span className="inline-block text-[10px] font-extrabold uppercase tracking-wider text-primary bg-primary/10 px-3 py-1 rounded-full w-fit mx-auto">
                {category}
              </span>
              <h1 className="text-xl font-extrabold text-navy leading-snug mt-2">{person.name}</h1>
              <p className="text-xs font-semibold text-gray-500">{person.title}</p>
            </div>

            <div className="w-full border-t border-gray-200/65 pt-6 flex flex-col gap-4 text-xs font-semibold text-gray-600 text-start">
              <ContactLine icon={Mail} label={t("common.email", "Email")} value={person.email} />
              <ContactLine icon={Phone} label={t("common.phone", "Phone")} value={person.phone} />
              <ContactLine icon={Clock} label={t("common.officeHours", "Office Hours")} value={person.officeHours} />
            </div>
          </div>

          <div className="lg:col-span-8 flex flex-col gap-8">
            {person.degree && (
              <div className="bg-primary/5 border border-primary/10 rounded-2xl p-4 flex items-center gap-3">
                <GraduationCap className="w-6 h-6 text-primary shrink-0" />
                <div>
                  <h4 className="text-xs font-extrabold text-navy uppercase tracking-wider">{t("common.academicRank", "Academic Rank & Position")}</h4>
                  <p className="text-sm font-bold text-primary leading-snug">{person.degree}</p>
                </div>
              </div>
            )}

            {person.about && (
              <section className="flex flex-col gap-4">
                <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                  <Briefcase className="w-5 h-5 text-primary shrink-0" />
                  {t("common.biography", "Professional Biography")}
                </h3>
                <p className="text-gray-500 text-sm md:text-base leading-relaxed whitespace-pre-line">{person.about}</p>
              </section>
            )}

            {person.details && (
              <section className="flex flex-col gap-4">
                <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                  <FileText className="w-5 h-5 text-primary shrink-0" />
                  {t("common.dutiesResponsibilities", "Duties & Responsibilities")}
                </h3>
                <div className="grid grid-cols-1 gap-3">
                  {person.details.split(/[;؛]/).map((duty) => {
                    const cleanDuty = duty.trim();
                    if (!cleanDuty) return null;
                    return (
                      <div key={cleanDuty} className="flex gap-3 items-start bg-gray-50 p-4 border border-gray-100 rounded-2xl">
                        <CheckCircle className="w-5 h-5 text-primary shrink-0 mt-0.5" />
                        <p className="text-xs text-navy font-semibold leading-relaxed">{cleanDuty}</p>
                      </div>
                    );
                  })}
                </div>
              </section>
            )}

            {person.achievements?.length > 0 && (
              <section className="flex flex-col gap-4">
                <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                  <Award className="w-5 h-5 text-primary shrink-0" />
                  {t("common.keyAchievements", "Key Achievements & Milestones")}
                </h3>
                <ul className="flex flex-col gap-3">
                  {person.achievements.map((achievement) => (
                    <li key={achievement} className="flex items-start gap-3 text-sm text-gray-500 font-semibold leading-relaxed">
                      <div className="w-2 h-2 rounded-full bg-primary mt-2 shrink-0" />
                      <span>{achievement}</span>
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

function ContactLine({ icon: Icon, label, value }) {
  if (!value) return null;

  return (
    <div className="flex items-center gap-3">
      <Icon className="w-5 h-5 text-primary shrink-0" />
      <div>
        <p className="text-[10px] text-gray-400 font-extrabold uppercase tracking-wider">{label}</p>
        <p className="text-navy break-all font-bold">{value}</p>
      </div>
    </div>
  );
}

function getInitials(name = "") {
  const parts = name.replace(/^(Dr\.|Prof\.)\s+/i, "").trim().split(/\s+/);
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return `${parts[0]?.[0] || ""}${parts[1]?.[0] || ""}`.toUpperCase();
}
