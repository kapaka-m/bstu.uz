import React, { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Calendar, Eye, Megaphone } from "lucide-react";
import { Swiper, SwiperSlide } from "swiper/react";
import { Autoplay } from "swiper/modules";
import { useLanguage } from "../context/LanguageContext";
import { motion } from "framer-motion";
import { announcementService } from "../services/announcementService";

// Import Swiper styles
import "swiper/css";

export default function Announcements() {
  const { language } = useLanguage();
  const [announcements, setAnnouncements] = useState([]);
  const [settings, setSettings] = useState(null);

  useEffect(() => {
    let mounted = true;
    Promise.all([
      announcementService.getSettings().catch(() => null),
      announcementService
        .getAnnouncements({ per_page: 12 })
        .catch(() => ({ items: [] })),
    ]).then(([settingsData, listData]) => {
      if (!mounted) return;
      setSettings(settingsData);
      setAnnouncements(listData.items || []);
    });

    return () => {
      mounted = false;
    };
  }, [language]);

  const latestAnnouncements = useMemo(
    () => announcements.slice(0, Number(settings?.home_limit || 4)),
    [announcements, settings?.home_limit],
  );

  const formatDate = (value) => {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    const localeMap = { ar: "ar", uz: "uz-Latn-UZ", ru: "ru-RU", en: "en-US" };
    return new Intl.DateTimeFormat(localeMap[language] || "en-US", {
      year: "numeric",
      month: "short",
      day: "2-digit",
    }).format(date);
  };

  if (!latestAnnouncements.length) {
    return null;
  }

  return (
    <section
      id="announcements"
      className="py-24 bg-slate-50/50 border-t border-gray-100/85 overflow-hidden"
    >
      <div className="container mx-auto px-4 md:px-8 max-w-7xl relative">
        {/* Decorative Background Elements */}
        <div className="absolute top-0 left-1/3 w-72 h-72 bg-primary/5 rounded-full blur-3xl pointer-events-none" />
        <div className="absolute bottom-10 right-1/4 w-96 h-96 bg-[#1990cf]/5 rounded-full blur-3xl pointer-events-none" />

        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16 relative z-10">
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            className="inline-flex items-center gap-2 px-3.5 py-1 bg-primary/10 text-primary rounded-full text-[11px] font-extrabold uppercase tracking-wider mb-4"
          >
            <Megaphone className="w-3.5 h-3.5" />
            {settings?.home_tag || ""}
          </motion.div>

          <motion.h2
            initial={{ opacity: 0, y: -20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="text-3xl md:text-4xl font-extrabold text-navy leading-tight mb-4"
          >
            {settings?.home_title || ""}
          </motion.h2>
        </div>

        {/* Swiper Slider Wrapper */}
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.8 }}
          className="relative z-10"
        >
          <Swiper
            key={language} // Force re-initialization on language change to adapt RTL/LTR native layout
            dir={language === "ar" ? "rtl" : "ltr"}
            modules={[Autoplay]}
            spaceBetween={28}
            slidesPerView={1}
            autoplay={{ delay: 5000, disableOnInteraction: false }}
            breakpoints={{
              640: { slidesPerView: 2 },
              1024: { slidesPerView: 3 },
              1280: { slidesPerView: 4 },
            }}
            className="pt-3 pb-6 overflow-visible! [&_.swiper-wrapper]:items-stretch"
          >
            {latestAnnouncements.map((item) => (
                <SwiperSlide key={item.id} className="h-auto flex">
                  <article className="bg-white border border-gray-100/70 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col min-h-115 sm:min-h-112.5 w-full group text-start">
                    {/* Image Container */}
                    <div className="aspect-16/10 overflow-hidden bg-gray-100 relative">
                      <img
                        src={item.image}
                        alt={item.title}
                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                      />
                      <span
                        className={`absolute bottom-3 ${language === "ar" ? "right-3" : "left-3"} bg-navy/80 backdrop-blur-xs text-white text-[10px] font-extrabold uppercase px-3 py-1 rounded-lg`}
                      >
                        {item.category_label}
                      </span>
                    </div>

                    {/* Card Body */}
                    <div className="p-6 flex flex-col grow min-h-0">
                      {/* Meta details */}
                      <div className="flex items-center gap-3 text-xs font-semibold text-gray-400 mb-3 min-h-9">
                        <span className="flex items-center gap-1.5 min-w-0">
                          <Calendar className="w-3.5 h-3.5 text-primary" />
                          <span className="truncate">{formatDate(item.date)}</span>
                        </span>
                        <span
                          className={`flex items-center gap-1.5 shrink-0 ${language === "ar" ? "mr-auto" : "ml-auto"}`}
                        >
                          <Eye className="w-3.5 h-3.5" />
                          {item.views} {settings?.views_label || ""}
                        </span>
                      </div>

                      {/* Title */}
                      <h3 className="text-base font-extrabold text-navy group-hover:text-primary transition-colors duration-300 line-clamp-2 mb-3 leading-snug min-h-12">
                        <Link to={`/announcements/${item.slug}`}>{item.title}</Link>
                      </h3>

                      {/* Excerpt */}
                      <p className="text-gray-500 text-xs leading-relaxed mb-6 line-clamp-3 min-h-15">
                        {item.excerpt}
                      </p>

                      {/* Read Details */}
                      <div className="mt-auto pt-3 border-t border-gray-50">
                        <Link
                          to={`/announcements/${item.slug}`}
                          className="inline-flex items-center gap-1.5 text-xs font-extrabold text-navy hover:text-primary transition-colors group"
                        >
                          {settings?.read_details_label || ""}
                          <ArrowRight
                            className={`w-3.5 h-3.5 transition-transform duration-300 ${language === "ar" ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                          />
                        </Link>
                      </div>
                    </div>
                  </article>
                </SwiperSlide>
              ))}
          </Swiper>
        </motion.div>

        {/* View All Button */}
        <div className="text-center mt-12 relative z-10">
          <Link
            to="/announcements"
            className="inline-flex items-center gap-2 bg-navy hover:bg-primary text-white font-extrabold px-8 py-3.5 rounded-full transition-all duration-300 shadow-md shadow-navy/10 hover:shadow-primary/20 hover:-translate-y-0.5"
          >
            {settings?.view_all_label || ""}
            <ArrowRight
              className={`w-4 h-4 ${language === "ar" ? "rotate-180" : ""}`}
            />
          </Link>
        </div>
      </div>
    </section>
  );
}
