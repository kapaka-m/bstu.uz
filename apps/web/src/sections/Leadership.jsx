import React from "react";
import { Link } from "react-router-dom";
import { Phone, Mail, Clock, Send, Shield } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";

export default function Leadership() {
  const { t } = useLanguage();

  const leaders = [
    {
      name: "Sadoqat Gafforovna Siddiqova",
      role: t("home.leadership.roles.rector"),
      photo: "/assets/img/administrator/Siddikova%20Sadokat%20Ghaforovna.jpg",
      reception: t("home.leadership.receptions.friday"),
      phone: "+998 65 223 78 84",
      telegram: "https://t.me/bdtu_rektor_bot",
      email: "rektor@bstu.uz",
      path: "/profile/rector"
    },
    {
      name: "Marhabo Davlatovna Pardayeva",
      role: t("home.leadership.roles.youth"),
      photo: "/assets/img/administrator/Pardayeva%20Marhabo%20Davlatovna.jpg",
      reception: t("home.leadership.receptions.mon_sat"),
      phone: "+998 90 711 78 73",
      email: "info@bstu.uz",
      path: "/profile/vice-rector-youth"
    },
    {
      name: "Aslitdin Badreddinovich Nizamov",
      role: t("home.leadership.roles.research"),
      photo: "/assets/img/administrator/Aslitdin%20Badreddinovich%20Nizamov.jpg",
      reception: t("home.leadership.receptions.mon_sat"),
      phone: "+998 93 383 18 38",
      email: "buhibkol@mail.ru",
      path: "/profile/vice-rector-research"
    },
    {
      name: "Alisher Xolmurodovich Gafforov",
      role: t("home.leadership.roles.academic"),
      photo: "/assets/img/administrator/Gafforov%20Alisher%20Xolmurodovich.jpg",
      reception: t("home.leadership.receptions.thu_sat"),
      phone: "+998 65 224 64 35",
      email: "info@bstu.uz",
      path: "/profile/vice-rector-academic"
    },
    {
      name: "Sobir Bahronovich Saidov",
      role: t("home.leadership.roles.finance"),
      photo: "/assets/img/administrator/Saidov%20Sobir%20Bahronovich.jpg",
      reception: t("home.leadership.receptions.sat"),
      phone: "+998 97 307 76 66",
      email: "sobirsaidov886@mail.ru",
      path: "/profile/vice-rector-finance"
    },
    {
      name: "Murodjon Ulugbekovich Djurayev",
      role: t("home.leadership.roles.international"),
      photo: "/assets/img/administrator/Murodjon%20Ulugbekovich%20Djurayev.jpg",
      reception: t("home.leadership.receptions.wed_fri"),
      phone: "+998 91 824 94 94",
      email: "prof.mjon@gmail.com",
      path: "/profile/vice-rector-international"
    }
  ];

  const rector = leaders[0];
  const viceRectors = [leaders[1], leaders[2], leaders[3], leaders[4], leaders[5]];

  return (
    <section id="leadership" className="py-24 bg-white relative overflow-hidden">
      {/* Decorative background shapes */}
      <div className="absolute top-0 left-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2" />
      <div className="absolute bottom-0 right-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl translate-x-1/2 translate-y-1/2" />

      <div className="container mx-auto px-4 md:px-8 max-w-7xl relative z-10">
        {/* Section Header */}
        <div className="text-center max-w-3xl mx-auto mb-20">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {t("home.leadership.tag")}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy leading-tight">
            {t("home.leadership.title")}
          </p>
          <div className="w-16 h-1 bg-primary mx-auto mt-4 rounded-full" />
        </div>

        {/* Pyramid Structure */}
        <div className="space-y-12">
          {/* Row 1: Rector (1 centered card) */}
          <div className="flex justify-center">
            <LeaderCard leader={rector} isRector={true} />
          </div>

          {/* Row 2: 5 Vice Rectors */}
          <div className="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-5 gap-6 md:gap-8 max-w-7xl mx-auto items-stretch">
            {viceRectors.map((leader, index) => {
              // Custom span for 2-then-3 layout on iPad (md screen)
              // First 2 items (index 0, 1) take 3 columns each (fill the first row: 3 + 3 = 6)
              // Next 3 items (index 2, 3, 4) take 2 columns each (fill the second row: 2 + 2 + 2 = 6)
              // On large screens (lg), all items take 1 column of 5 (lg:col-span-1)
              const colSpanClass = index < 2 
                ? "col-span-1 md:col-span-3 lg:col-span-1" 
                : "col-span-1 md:col-span-2 lg:col-span-1";
              return (
                <div key={index} className={`${colSpanClass} flex`}>
                  <LeaderCard leader={leader} isRector={false} />
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </section>
  );
}

function LeaderCard({ leader, isRector }) {
  const { t } = useLanguage();

  return (
    <div className={`group bg-primary-light/50 border border-gray-100 hover:border-primary/20 p-3 sm:p-4 rounded-[1.75rem] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1.5 flex flex-col overflow-hidden w-full mx-auto ${
      isRector ? "max-w-72 sm:max-w-76" : "max-w-72 lg:max-w-52.5 xl:max-w-none"
    }`}>
      {/* Photo Frame */}
      <Link to={leader.path} className="block relative aspect-4/5 w-full rounded-2xl overflow-hidden mb-3.5 bg-gray-100 border border-gray-200/50 shadow-inner group/photo">
        <img
          src={leader.photo}
          alt={leader.name}
          className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
          loading="lazy"
        />
        <div className="absolute inset-0 bg-linear-to-t from-navy/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
      </Link>

      {/* Title & Role */}
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

        {/* Details List */}
        <div className="space-y-2.5 pt-2.5 border-t border-gray-100 text-[10px] sm:text-xs font-semibold text-gray-600 text-start">
          {/* Reception */}
          <div className="flex items-start gap-1.5">
            <Clock className="w-3 h-3 text-primary shrink-0 mt-0.5" />
            <div>
              <span className="text-gray-400 block text-[8px] uppercase font-bold tracking-wider mb-0.5">
                {t("home.leadership.receptionLabel")}
              </span>
              <span className="text-gray-700 leading-tight block">{leader.reception}</span>
            </div>
          </div>

          {/* Phone */}
          <div className="flex items-start gap-1.5">
            <Phone className="w-3 h-3 text-primary shrink-0 mt-0.5" />
            <div>
              <span className="text-gray-400 block text-[8px] uppercase font-bold tracking-wider mb-0.5">
                {t("home.leadership.phoneLabel")}
              </span>
              <a href={`tel:${leader.phone.replace(/\s+/g, '')}`} className="text-gray-700 hover:text-primary transition-colors font-bold block leading-tight">
                {leader.phone}
              </a>
            </div>
          </div>

          {/* Email */}
          {leader.email && (
            <div className="flex items-start gap-1.5">
              <Mail className="w-3 h-3 text-primary shrink-0 mt-0.5" />
              <div>
                <span className="text-gray-400 block text-[8px] uppercase font-bold tracking-wider mb-0.5">
                  {t("home.leadership.emailLabel")}
                </span>
                <a href={`mailto:${leader.email}`} className="text-gray-700 hover:text-primary transition-colors break-all font-bold block leading-tight">
                  {leader.email}
                </a>
              </div>
            </div>
          )}

          {/* Telegram Bot for Rector */}
          {leader.telegram && (
            <div className="flex items-start gap-1.5">
              <Send className="w-3 h-3 text-primary shrink-0 mt-0.5" />
              <div>
                <span className="text-gray-400 block text-[8px] uppercase font-bold tracking-wider mb-0.5">
                  {t("home.leadership.telegramLabel")}
                </span>
                <a
                  href={leader.telegram}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-primary hover:underline transition-all font-bold block leading-tight"
                >
                  {t("home.leadership.rectorBot")}
                </a>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}