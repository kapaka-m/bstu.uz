import React from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Calendar, Eye, Megaphone } from "lucide-react";
import { Swiper, SwiperSlide } from "swiper/react";
import { Autoplay } from "swiper/modules";
import { announcementsData } from "../data/announcementsData";
import { useLanguage } from "../context/LanguageContext";
import { motion } from "framer-motion";

// Import Swiper styles
import "swiper/css";

export default function Announcements() {
  const { t, language } = useLanguage();

  // Sort by date descending and get the latest 4 announcements
  const latestAnnouncements = [...announcementsData]
    .sort((a, b) => new Date(b.date) - new Date(a.date))
    .slice(0, 4);

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
            {t("announcements.tag", "Announcements")}
          </motion.div>

          <motion.h2
            initial={{ opacity: 0, y: -20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="text-3xl md:text-4xl font-extrabold text-navy leading-tight mb-4"
          >
            {t("announcements.title", "Latest Announcements")}
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
            className="pb-2"
          >
            {latestAnnouncements.map((item) => {
              const title = t(`announcements.items.${item.id}.title`);
              const excerpt = t(`announcements.items.${item.id}.excerpt`);
              const categoryTranslated = t(
                `announcements.categories.${item.category.toLowerCase()}`,
                item.category,
              );

              return (
                <SwiperSlide key={item.id} className="h-auto">
                  <article className="bg-white border border-gray-100/70 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col h-full group text-start">
                    {/* Image Container */}
                    <div className="aspect-16/10 overflow-hidden bg-gray-100 relative">
                      <img
                        src={item.image}
                        alt={title}
                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                      />
                      <span
                        className={`absolute bottom-3 ${language === "ar" ? "right-3" : "left-3"} bg-navy/80 backdrop-blur-xs text-white text-[10px] font-extrabold uppercase px-3 py-1 rounded-lg`}
                      >
                        {categoryTranslated}
                      </span>
                    </div>

                    {/* Card Body */}
                    <div className="p-6 flex flex-col grow">
                      {/* Meta details */}
                      <div className="flex items-center gap-3 text-xs font-semibold text-gray-400 mb-3">
                        <span className="flex items-center gap-1.5">
                          <Calendar className="w-3.5 h-3.5 text-primary" />
                          {item.date}
                        </span>
                        <span
                          className={`flex items-center gap-1.5 ${language === "ar" ? "mr-auto" : "ml-auto"}`}
                        >
                          <Eye className="w-3.5 h-3.5" />
                          {item.views} {t("announcements.viewsLabel", "views")}
                        </span>
                      </div>

                      {/* Title */}
                      <h3 className="text-base font-extrabold text-navy group-hover:text-primary transition-colors duration-300 line-clamp-2 mb-3 leading-snug">
                        <Link to={`/announcements/${item.id}`}>{title}</Link>
                      </h3>

                      {/* Excerpt */}
                      <p className="text-gray-500 text-xs leading-relaxed mb-6 line-clamp-3">
                        {excerpt}
                      </p>

                      {/* Read Details */}
                      <div className="mt-auto pt-3 border-t border-gray-50">
                        <Link
                          to={`/announcements/${item.id}`}
                          className="inline-flex items-center gap-1.5 text-xs font-extrabold text-navy hover:text-primary transition-colors group"
                        >
                          {t("announcements.readMore", "Read Details")}
                          <ArrowRight
                            className={`w-3.5 h-3.5 transition-transform duration-300 ${language === "ar" ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                          />
                        </Link>
                      </div>
                    </div>
                  </article>
                </SwiperSlide>
              );
            })}
          </Swiper>
        </motion.div>

        {/* View All Button */}
        <div className="text-center mt-12 relative z-10">
          <Link
            to="/announcements"
            className="inline-flex items-center gap-2 bg-navy hover:bg-primary text-white font-extrabold px-8 py-3.5 rounded-full transition-all duration-300 shadow-md shadow-navy/10 hover:shadow-primary/20 hover:-translate-y-0.5"
          >
            {t("announcements.viewAll", "All Announcements")}
            <ArrowRight
              className={`w-4 h-4 ${language === "ar" ? "rotate-180" : ""}`}
            />
          </Link>
        </div>
      </div>
    </section>
  );
}
