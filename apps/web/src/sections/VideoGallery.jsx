import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { Play, Eye, Calendar, ArrowRight, X, Film } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { Swiper, SwiperSlide } from "swiper/react";
import { Autoplay, Pagination, Navigation } from "swiper/modules";
import { useLanguage } from "../context/LanguageContext";
import { videoService } from "../services/videoService";

// Import Swiper styles
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/navigation";

export default function VideoGallery() {
  const [activeVideo, setActiveVideo] = useState(null);
  const [videos, setVideos] = useState([]);
  const [settings, setSettings] = useState({});
  const [now] = useState(() => Date.now());
  const { t, language } = useLanguage();

  useEffect(() => {
    let active = true;

    Promise.all([videoService.getSettings(), videoService.getVideos()])
      .then(([settingsData, videoItems]) => {
        if (!active) return;
        setSettings(settingsData);
        setVideos(videoItems);
      })
      .catch(() => {
        if (!active) return;
        setSettings({});
        setVideos([]);
      });

    return () => {
      active = false;
    };
  }, [language]);

  const formatViews = (viewsCount) => {
    const numericValue = Number(viewsCount || 0);

    const localeByLang = {
      en: "en-US",
      uz: "uz-UZ",
      ru: "ru-RU",
      ar: "ar",
    };

    const locale = localeByLang[language] || "en-US";
    const formattedNumber = new Intl.NumberFormat(locale, {
      notation: numericValue >= 1000 ? "compact" : "standard",
      maximumFractionDigits: 1,
    }).format(Math.round(numericValue));

    return `${formattedNumber} ${settings.views_label || ""}`.trim();
  };

  const formatDate = (dateText) => {
    if (!dateText) return "";
    const published = new Date(dateText);
    if (Number.isNaN(published.getTime())) return String(dateText);
    const days = Math.max(1, Math.round((now - published.getTime()) / 86400000));
    const unit = days < 30 ? "day" : days < 365 ? "month" : "year";
    const count = days < 30 ? days : days < 365 ? Math.round(days / 30) : Math.round(days / 365);
    const value = -count;
    const localeByLang = { en: "en-US", uz: "uz-UZ", ru: "ru-RU", ar: "ar" };

    if (language === "uz") {
      const unitLabels = {
        day: "kun",
        month: "oy",
        year: "yil",
      };
      return `${count} ${unitLabels[unit]} oldin`;
    }

    return new Intl.RelativeTimeFormat(localeByLang[language] || language, {
      numeric: "auto",
    }).format(value, unit);
  };

  const categoryLabels = settings.category_labels || {};
  const labelForCategory = (category) => categoryLabels[category] || category;

  if (videos.length === 0) {
    return null;
  }

  return (
    <section id="video-gallery" className="py-24 bg-primary-light/50 border-t border-gray-100 overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <span className="inline-flex items-center gap-1 bg-primary/10 text-primary text-[11px] font-extrabold uppercase tracking-widest px-3.5 py-1.5 rounded-full mb-3">
            <Film className="w-3.5 h-3.5" /> {settings.home_tag || ""}
          </span>
          <h2 className="text-3xl md:text-4xl font-extrabold text-navy">
            {settings.home_title || ""}
          </h2>
          <p className="text-gray-500 text-sm md:text-base font-semibold mt-2">
            {settings.home_subtitle || ""}
          </p>
        </div>

        {/* Swiper Slider */}
        <div className="relative px-2">
          <Swiper
            modules={[Autoplay, Pagination, Navigation]}
            spaceBetween={30}
            slidesPerView={1}
            autoplay={{
              delay: 4000,
              disableOnInteraction: false,
              pauseOnMouseEnter: true,
            }}
            pagination={{ clickable: true, el: ".video-swiper-pagination" }}
            navigation={{
              nextEl: ".video-swiper-button-next",
              prevEl: ".video-swiper-button-prev",
            }}
            breakpoints={{
              640: {
                slidesPerView: 1,
              },
              768: {
                slidesPerView: 2,
              },
              1024: {
                slidesPerView: 3,
              },
              1280: {
                slidesPerView: 4,
              },
            }}
            className="pb-16"
          >
            {videos.slice(0, Number(settings.home_limit || 4)).map((video) => (
              <SwiperSlide key={video.id}>
                <div
                  onClick={() => setActiveVideo(video)}
                  className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 flex flex-col group cursor-pointer h-full"
                >
                  {/* Thumbnail Frame */}
                  <div className="aspect-video overflow-hidden bg-gray-150 relative">
                    {video.poster ? (
                      <img
                        src={video.poster}
                        alt={video.title}
                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                      />
                    ) : (
                      <div className="w-full h-full bg-gray-100" />
                    )}
                    {/* Play Button Overlay */}
                    <div className="absolute inset-0 bg-navy/20 group-hover:bg-navy/40 transition-colors duration-300 flex items-center justify-center">
                      <span className="w-12 h-12 rounded-full bg-white/95 text-primary flex items-center justify-center transition-all duration-300 transform scale-90 group-hover:scale-100 shadow-md group-hover:bg-primary group-hover:text-white">
                        <Play className="w-5 h-5 fill-current ml-0.5" />
                      </span>
                    </div>
                    {/* Duration Badge */}
                    <span className="absolute bottom-3 right-3 bg-black/75 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-sm">
                      {video.duration}
                    </span>
                    {/* Category */}
                    <span className="absolute top-3 left-3 rtl:left-auto rtl:right-3 bg-primary text-white text-[9px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full">
                      {labelForCategory(video.category)}
                    </span>
                  </div>

                  {/* Card Content */}
                  <div className="p-5 flex flex-col grow text-start">
                    {/* Meta stats */}
                    <div className="flex items-center gap-3 text-[11px] font-bold text-gray-400 mb-2.5">
                      <span className="flex items-center gap-1">
                        <Eye className="w-3.5 h-3.5 text-primary" />
                        {formatViews(video.viewsCount)}
                      </span>
                      <span className="flex items-center gap-1">
                        <Calendar className="w-3.5 h-3.5" />
                        {formatDate(video.publishedAt)}
                      </span>
                    </div>
                    {/* Title */}
                    <h3 className="text-sm font-extrabold text-navy group-hover:text-primary transition-colors duration-300 line-clamp-2 leading-snug min-h-[2.8rem]">
                      {video.title}
                    </h3>
                  </div>
                </div>
              </SwiperSlide>
            ))}
          </Swiper>

          {/* Custom Navigation buttons (hidden on mobile, shown on desktop) */}
          <button aria-label={t("common.previousSlide")} className="video-swiper-button-prev hidden md:flex absolute top-1/3 -inset-s-4 z-10 w-11 h-11 rounded-full bg-white border border-gray-100 text-navy hover:text-primary hover:border-primary transition-all items-center justify-center shadow-md hover:-translate-x-0.5 rtl:hover:translate-x-0.5 cursor-pointer">
            <svg className="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button aria-label={t("common.nextSlide")} className="video-swiper-button-next hidden md:flex absolute top-1/3 -inset-e-4 z-10 w-11 h-11 rounded-full bg-white border border-gray-100 text-navy hover:text-primary hover:border-primary transition-all items-center justify-center shadow-md hover:translate-x-0.5 rtl:hover:-translate-x-0.5 cursor-pointer">
            <svg className="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          {/* Paginationbullets */}
          <div className="video-swiper-pagination flex justify-center gap-2 mt-4" />
        </div>

        {/* View All / BDTU Video Page Button */}
        <div className="text-center mt-12">
          <Link
            to="/video-bdtu"
            className="inline-flex items-center gap-2 bg-navy hover:bg-primary text-white font-extrabold px-8 py-3.5 rounded-full transition-all duration-300 shadow-md shadow-navy/10 hover:shadow-primary/20 hover:-translate-y-0.5 cursor-pointer"
          >
            {settings.view_all_label || settings.home_tag || ""}
            <ArrowRight className={`w-4 h-4 transition-transform ${language === 'ar' ? 'rotate-180' : ''}`} />
          </Link>
        </div>
      </div>

      {/* Video Modal Player */}
      <AnimatePresence>
        {activeVideo && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 backdrop-blur-md"
          >
            <div className="relative w-full max-w-4xl aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl border border-white/10">
              <button
                onClick={() => setActiveVideo(null)}
                aria-label={t("common.closeVideoModal")}
                className="absolute top-4 right-4 rtl:right-auto rtl:left-4 z-20 p-2.5 rounded-full bg-black/40 hover:bg-black/60 text-white transition-all cursor-pointer shadow-md border border-white/10 hover:scale-105"
              >
                <X className="w-5 h-5" />
              </button>
              {activeVideo.isLocal ? (
                <video
                  src={activeVideo.videoUrl}
                  controls
                  autoPlay
                  className="w-full h-full object-contain absolute inset-0 bg-black"
                />
              ) : (
                <iframe
                  title={t("common.youtubeVideoPlayer")}
                  src={`https://www.youtube.com/embed/${activeVideo.youtubeId}?autoplay=1&rel=0`}
                  className="w-full h-full border-0 absolute inset-0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                ></iframe>
              )}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </section>
  );
}
