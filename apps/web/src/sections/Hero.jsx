import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Play, X } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { videoService } from "../services/videoService";

const API_ORIGIN = (import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1").replace(
  /\/api\/v1\/?$/,
  "",
);

const resolveAssetUrl = (path) => {
  if (!path) return "";
  if (path.startsWith("http://") || path.startsWith("https://") || path.startsWith("/")) {
    return path;
  }
  if (path.startsWith("assets/")) {
    return `/${path}`;
  }
  return `${API_ORIGIN}/storage/${path}`;
};

export default function Hero() {
  const [isVideoOpen, setIsVideoOpen] = useState(false);
  const [heroVideo, setHeroVideo] = useState(null);
  const { t, language } = useLanguage();

  useEffect(() => {
    let alive = true;

    videoService
      .getVideos()
      .then((videos) => {
        if (alive) setHeroVideo((videos || [])[0] || null);
      })
      .catch(() => {
        if (alive) setHeroVideo(null);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  return (
    <section
      id="hero"
      className="relative min-h-screen pt-32 pb-20 flex items-center bg-no-repeat bg-top-right overflow-hidden"
      style={{ backgroundImage: "url('/assets/img/hero-bg.png')" }}
    >
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          {/* Left Column (Text) */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, ease: "easeOut" }}
            className="flex flex-col justify-center text-center lg:text-left"
          >
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-navy leading-tight mb-4">
              {t("home.hero.title")}
            </h1>
            <p className="text-navy-light text-lg md:text-xl font-medium mb-8 max-w-xl mx-auto lg:mx-0">
              {t("home.hero.subtitle")}
            </p>
            <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
              <Link
                to="/apply"
                className="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all duration-300 hover:-translate-y-0.5 group cursor-pointer"
              >
                {t("common.applyNow")}
                <ArrowRight className={`w-4 h-4 transition-transform duration-300 ${language === "ar" ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`} />
              </Link>
              {heroVideo && (
                <button
                  onClick={() => setIsVideoOpen(true)}
                  className="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 text-navy hover:text-primary transition-colors py-3 px-6 rounded-xl font-bold group cursor-pointer"
                >
                  <span className="w-12 h-12 rounded-full border-2 border-primary/20 flex items-center justify-center bg-white transition-all duration-300 group-hover:bg-primary group-hover:text-white group-hover:border-primary shadow-md">
                    <Play className="w-4 h-4 fill-current ml-0.5" />
                  </span>
                  {t("home.hero.watchVideo")}
                </button>
              )}
            </div>
          </motion.div>

          {/* Right Column (University Image with Premium Dashboard Styling) */}
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
            className="flex justify-center items-center relative"
          >
            {/* Main Image Container */}
            <div className="relative p-3 bg-linear-to-br from-primary-light to-white rounded-[2.5rem] shadow-2xl max-w-lg lg:max-w-none group">
              <div className="overflow-hidden rounded-4xl border-4 border-white shadow-md relative">
                <img
                  src="/assets/img/hero-university.jpg"
                  alt={t("home.hero.imageAlt")}
                  className="w-full h-auto aspect-4/3 object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-linear-to-t from-navy/35 to-transparent mix-blend-multiply" />
              </div>

              {/* Floating Card 1: Students Count */}
              <div className="absolute -bottom-6 -left-6 bg-white/90 backdrop-blur-md border border-gray-100 p-4 rounded-2xl shadow-xl flex items-center gap-3 animate-float-slow">
                <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                  <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </div>
                <div>
                  <div className="text-navy font-extrabold text-sm leading-none">15,000+</div>
                  <div className="text-gray-500 text-[10px] font-bold mt-1 uppercase tracking-wider">{t("home.hero.activeStudents")}</div>
                </div>
              </div>

              {/* Floating Card 2: Programs */}
              <div className="absolute -top-6 -right-6 bg-white/90 backdrop-blur-md border border-gray-100 p-4 rounded-2xl shadow-xl flex items-center gap-3 animate-float-slow" style={{ animationDelay: "2s" }}>
                <div className="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                  <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                  </svg>
                </div>
                <div>
                  <div className="text-navy font-extrabold text-sm leading-none">{t("home.hero.accredited")}</div>
                  <div className="text-gray-500 text-[10px] font-bold mt-1 uppercase tracking-wider">{t("home.hero.statePrograms")}</div>
                </div>
              </div>
            </div>
          </motion.div>
        </div>
      </div>

      {/* Video Modal */}
      <AnimatePresence>
        {isVideoOpen && heroVideo && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 backdrop-blur-md"
          >
            <div className="relative w-full max-w-4xl aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl border border-white/10">
              <button
                onClick={() => setIsVideoOpen(false)}
                aria-label={t("common.close")}
                className="absolute top-4 right-4 z-25 p-2.5 rounded-full bg-black/40 hover:bg-black/60 text-white transition-all cursor-pointer shadow-md border border-white/10 hover:scale-105"
              >
                <X className="w-5 h-5" />
              </button>
              {heroVideo.youtubeId ? (
                <iframe
                  src={`https://www.youtube.com/embed/${heroVideo.youtubeId}?autoplay=1`}
                  title={heroVideo.title || ""}
                  className="w-full h-full"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                />
              ) : (
                <video
                  src={resolveAssetUrl(heroVideo.videoUrl)}
                  className="w-full h-full object-cover"
                  controls
                  autoPlay
                  playsInline
                />
              )}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </section>
  );
}
