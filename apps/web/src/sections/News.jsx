import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Calendar, CalendarDays, Megaphone, Newspaper } from "lucide-react";
import { motion } from "framer-motion";
import { Swiper, SwiperSlide } from "swiper/react";
import { Autoplay } from "swiper/modules";
import { useLanguage } from "../context/LanguageContext";
import { newsService } from "../services/newsService";
import "swiper/css";

const HOME_ICON_MAP = {
  newspaper: Newspaper,
  "calendar-days": CalendarDays,
  megaphone: Megaphone,
};

export default function News() {
  const { language } = useLanguage();
  const [homeNews, setHomeNews] = useState([]);
  const [settings, setSettings] = useState(null);

  useEffect(() => {
    let active = true;

    Promise.all([
      newsService.getSettings(),
      newsService.getNews({ per_page: 12 }),
    ])
      .then(([data, payload]) => {
        if (!active) return;
        setSettings(data);
        setHomeNews((payload.items || []).slice(0, Number(data.home_limit || 4)));
      })
      .catch(() => {
        if (active) {
          setSettings(null);
          setHomeNews([]);
        }
      });

    return () => {
      active = false;
    };
  }, [language]);

  const formatNewsDate = (dateValue) => {
    if (!dateValue) return "";
    const parsed = new Date(dateValue);
    if (Number.isNaN(parsed.getTime())) return dateValue;

    if (language === "uz") {
      const months = [
        "yanvar",
        "fevral",
        "mart",
        "aprel",
        "may",
        "iyun",
        "iyul",
        "avgust",
        "sentabr",
        "oktabr",
        "noyabr",
        "dekabr",
      ];
      return `${parsed.getFullYear()}-yil ${String(parsed.getDate()).padStart(2, "0")}-${months[parsed.getMonth()]}`;
    }

    return new Intl.DateTimeFormat(language, {
      day: "2-digit",
      month: "long",
      year: "numeric",
    }).format(parsed);
  };

  const categoryLabel = (category) =>
    category?.toLowerCase() === "events"
      ? settings?.events_label || category
      : settings?.news_label || category;
  const HomeIcon = HOME_ICON_MAP[settings?.home_icon] || Newspaper;
  const renderNewsCard = (item, index) => (
    <motion.div
      key={item.id}
      initial={{ opacity: 0, y: 30 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ duration: 0.5, delay: index * 0.1 }}
      className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 flex flex-col group text-start h-full"
    >
      <div className="aspect-16/10 overflow-hidden bg-gray-50 relative">
        <Link to={`/news/${item.id}`}>
          <img
            src={item.img}
            alt={item.title}
            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            loading="lazy"
            onError={(e) => {
              e.currentTarget.style.display = "none";
            }}
          />
        </Link>
        <span className="absolute top-4 left-4 rtl:left-auto rtl:right-4 bg-primary text-white text-[10px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
          {categoryLabel(item.category)}
        </span>
      </div>

      <div className="p-6 flex flex-col grow">
        <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 mb-3">
          <Calendar className="w-3.5 h-3.5 text-primary" />
          {formatNewsDate(item.date)}
        </div>

        <h3 className="text-base font-extrabold text-navy group-hover:text-primary transition-colors duration-300 line-clamp-2 mb-3 leading-snug">
          <Link to={`/news/${item.id}`}>{item.title}</Link>
        </h3>

        <p className="text-gray-500 text-xs leading-relaxed mb-5 line-clamp-3">
          {item.description}
        </p>

        <div className="mt-auto">
          <Link
            to={`/news/${item.id}`}
            className="inline-flex items-center gap-1.5 text-xs font-extrabold text-navy hover:text-primary transition-colors group-hover:underline"
          >
            {settings?.read_details_label || ""}
            <ArrowRight
              className={`w-3.5 h-3.5 transition-transform duration-300 ${language === "ar" ? "rotate-180 group-hover:-translate-x-0.5" : "group-hover:translate-x-0.5"}`}
            />
          </Link>
        </div>
      </div>
    </motion.div>
  );

  return (
    <section
      id="news-section"
      className="py-24 bg-white border-t border-gray-100"
    >
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <span className="bg-primary/10 text-primary text-xs font-extrabold uppercase tracking-widest px-3 py-1.5 rounded-full mb-3 inline-flex items-center gap-2">
            <HomeIcon className="w-3.5 h-3.5" />
            {settings?.home_tag || ""}
          </span>
          <h2 className="text-3xl md:text-4xl font-extrabold text-navy">
            {settings?.home_title || ""}
          </h2>
          <p className="text-gray-500 text-sm md:text-base font-semibold mt-2">
            {settings?.home_subtitle || ""}
          </p>
        </div>

        <div className="lg:hidden">
          <Swiper
            key={language}
            dir={language === "ar" ? "rtl" : "ltr"}
            modules={[Autoplay]}
            spaceBetween={24}
            slidesPerView={1}
            autoplay={{ delay: 4500, disableOnInteraction: false, pauseOnMouseEnter: true }}
            breakpoints={{ 768: { slidesPerView: 2 } }}
            className="pt-3 pb-6 !overflow-visible [&_.swiper-wrapper]:items-stretch"
          >
            {homeNews.map((item, index) => (
              <SwiperSlide key={item.id} className="h-auto">
                {renderNewsCard(item, index)}
              </SwiperSlide>
            ))}
          </Swiper>
        </div>

        <div className="hidden lg:grid lg:grid-cols-4 gap-8">
          {homeNews.map((item, index) => renderNewsCard(item, index))}
        </div>

        <div className="text-center mt-14">
          <Link
            to="/news"
            className="inline-flex items-center gap-2 bg-navy hover:bg-primary text-white font-extrabold px-8 py-3.5 rounded-full transition-all duration-300 shadow-md shadow-navy/10 hover:shadow-primary/20 hover:-translate-y-0.5 cursor-pointer"
          >
            {settings?.view_all_label || ""}
            <ArrowRight
              className={`w-4 h-4 transition-transform ${language === "ar" ? "rotate-180" : ""}`}
            />
          </Link>
        </div>
      </div>
    </section>
  );
}
