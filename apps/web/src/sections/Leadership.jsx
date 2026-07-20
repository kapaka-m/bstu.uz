import React, { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { Phone, Mail, Clock, Send, Shield, UsersRound } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { administrationService } from "../services/administrationService";

export default function Leadership() {
  const { t, language } = useLanguage();
  const [settings, setSettings] = useState(null);
  const [leaders, setLeaders] = useState([]);

  useEffect(() => {
    let alive = true;

    Promise.all([
      administrationService.getSettings().catch(() => null),
      administrationService.getProfiles().catch(() => []),
    ]).then(([nextSettings, nextLeaders]) => {
      if (!alive) return;
      setSettings(nextSettings || null);
      setLeaders(nextLeaders || []);
    });

    return () => {
      alive = false;
    };
  }, [language]);

  const { rector, viceRectors } = useMemo(() => {
    const sorted = [...leaders].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
    const rectorProfile = sorted.find((leader) => leader.is_rector) || sorted[0] || null;
    return {
      rector: rectorProfile,
      viceRectors: sorted.filter((leader) => leader.slug !== rectorProfile?.slug),
    };
  }, [leaders]);

  if (!rector && leaders.length === 0) {
    return null;
  }

  return (
    <section id="leadership" className="py-24 bg-white relative overflow-hidden">
      <div className="absolute top-0 left-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2" />
      <div className="absolute bottom-0 right-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl translate-x-1/2 translate-y-1/2" />

      <div className="container mx-auto px-4 md:px-8 max-w-7xl relative z-10">
        <div className="text-center max-w-3xl mx-auto mb-20">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3 inline-flex items-center justify-center gap-2">
            <UsersRound className="w-4 h-4" />
            {settings?.home_tag || t("home.leadership.tag")}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy leading-tight">
            {settings?.home_title || t("home.leadership.title")}
          </p>
          <div className="w-16 h-1 bg-primary mx-auto mt-4 rounded-full" />
        </div>

        <div className="space-y-12">
          {rector && (
            <div className="flex justify-center">
              <LeaderCard leader={rector} settings={settings} isRector />
            </div>
          )}

          <div className="hidden sm:grid grid-cols-1 md:grid-cols-6 lg:grid-cols-5 gap-6 md:gap-8 max-w-7xl mx-auto items-stretch">
            {viceRectors.map((leader, index) => {
              const colSpanClass = index < 2
                ? "col-span-1 md:col-span-3 lg:col-span-1"
                : "col-span-1 md:col-span-2 lg:col-span-1";
              return (
                <div key={leader.slug || leader.id} className={`${colSpanClass} flex`}>
                  <LeaderCard leader={leader} settings={settings} isRector={false} />
                </div>
              );
            })}
          </div>

          <div className="sm:hidden flex gap-4 overflow-x-auto snap-x snap-mandatory pb-4 -mx-4 px-4">
            {viceRectors.map((leader) => (
              <div key={leader.slug || leader.id} className="min-w-[17rem] snap-center flex">
                <LeaderCard leader={leader} settings={settings} isRector={false} />
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}

function LeaderCard({ leader, settings, isRector }) {
  const { t } = useLanguage();

  return (
    <div className={`group bg-primary-light/50 border border-gray-100 hover:border-primary/20 p-3 sm:p-4 rounded-[1.75rem] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1.5 flex flex-col overflow-hidden w-full mx-auto ${
      isRector ? "max-w-72 sm:max-w-76" : "max-w-72 lg:max-w-52.5 xl:max-w-none"
    }`}>
      <Link to={leader.path} className="block relative aspect-4/5 w-full rounded-2xl overflow-hidden mb-3.5 bg-gray-100 border border-gray-200/50 shadow-inner group/photo">
        <img
          src={leader.photo}
          alt={leader.name}
          className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
          loading="lazy"
        />
        <div className="absolute inset-0 bg-linear-to-t from-navy/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
      </Link>

      <div className="flex flex-col gap-1.5 grow">
        <h3 className={`font-extrabold text-navy tracking-tight group-hover:text-primary transition-colors duration-300 min-h-10 flex items-center text-start leading-tight ${
          isRector ? "text-base md:text-lg" : "text-xs sm:text-sm"
        }`}>
          <Link to={leader.path} className="hover:underline">
            {leader.name}
          </Link>
        </h3>
        <span className="self-start inline-flex items-center gap-1 bg-primary/10 text-primary text-[8px] font-extrabold uppercase tracking-wider px-2.5 py-1.5 rounded-full mb-2">
          <Shield className="w-3 h-3 shrink-0" />
          {leader.role}
        </span>

        <div className="space-y-2.5 pt-2.5 border-t border-gray-100 text-[10px] sm:text-xs font-semibold text-gray-600 text-start">
          <div className="flex items-start gap-1.5">
            <Clock className="w-3 h-3 text-primary shrink-0 mt-0.5" />
            <div>
              <span className="text-gray-400 block text-[8px] uppercase font-bold tracking-wider mb-0.5">
                {settings?.reception_label || t("home.leadership.receptionLabel")}
              </span>
              <span className="text-gray-700 leading-tight block">{leader.reception}</span>
            </div>
          </div>

          <div className="flex items-start gap-1.5">
            <Phone className="w-3 h-3 text-primary shrink-0 mt-0.5" />
            <div>
              <span className="text-gray-400 block text-[8px] uppercase font-bold tracking-wider mb-0.5">
                {settings?.phone_label || t("home.leadership.phoneLabel")}
              </span>
              <a href={`tel:${leader.phone.replace(/\s+/g, "")}`} className="text-gray-700 hover:text-primary transition-colors font-bold block leading-tight">
                {leader.phone}
              </a>
            </div>
          </div>

          {leader.email && (
            <div className="flex items-start gap-1.5">
              <Mail className="w-3 h-3 text-primary shrink-0 mt-0.5" />
              <div>
                <span className="text-gray-400 block text-[8px] uppercase font-bold tracking-wider mb-0.5">
                  {settings?.email_label || t("home.leadership.emailLabel")}
                </span>
                <a href={`mailto:${leader.email}`} className="text-gray-700 hover:text-primary transition-colors break-all font-bold block leading-tight">
                  {leader.email}
                </a>
              </div>
            </div>
          )}

          {leader.telegram && (
            <div className="flex items-start gap-1.5">
              <Send className="w-3 h-3 text-primary shrink-0 mt-0.5" />
              <div>
                <span className="text-gray-400 block text-[8px] uppercase font-bold tracking-wider mb-0.5">
                  {settings?.telegram_label || t("home.leadership.telegramLabel")}
                </span>
                <a
                  href={leader.telegram}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-primary hover:underline transition-all font-bold block leading-tight"
                >
                  {settings?.rector_bot_label || t("home.leadership.rectorBot")}
                </a>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
