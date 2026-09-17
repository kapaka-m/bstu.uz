import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Play, X } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { videoService } from "../services/videoService";
import { publicAssetUrl } from "../lib/api";
import { useHomeSection } from "../hooks/useHomeSection";

const getYoutubeId = (value) => {
  const url = String(value || "").trim();
  if (!url) return "";

  const patterns = [
    /youtu\.be\/([^?&/]+)/i,
    /youtube\.com\/watch\?[^#]*v=([^?&#]+)/i,
    /youtube\.com\/embed\/([^?&#/]+)/i,
  ];

  for (const pattern of patterns) {
    const match = url.match(pattern);
    if (match?.[1]) return match[1];
  }

  return /^[a-zA-Z0-9_-]{8,}$/.test(url) && !url.includes(".") && !url.includes("/")
    ? url
    : "";
};

export default function Hero() {
  const [isVideoOpen, setIsVideoOpen] = useState(false);
  const [heroVideo, setHeroVideo] = useState(null);
  const { t, language, isRtl } = useLanguage();
  const { section } = useHomeSection("hero");
  const heroBackgroundImage = publicAssetUrl(section?.settings?.background_image || "");
  const heroMainImage = publicAssetUrl(section?.settings?.image || "");
  const heroStudentCard = section?.items?.find((item) => item.item_key === "student_count");
  const heroAccreditationCard = section?.items?.find((item) => item.item_key === "accreditation");
  const heroStudentCount = heroStudentCard
    ? `${heroStudentCard.value || ""}${heroStudentCard.suffix || ""}`
    : "";
  const heroStudentLabel = heroStudentCard?.label || "";
  const heroAccreditedTitle = heroAccreditationCard?.title || "";
  const heroAccreditedLabel = heroAccreditationCard?.label || "";
  const configuredVideoUrl = String(section?.settings?.video_url || "").trim();
  const configuredYoutubeId = getYoutubeId(configuredVideoUrl);
  const activeHeroVideo = configuredVideoUrl
    ? {
        title: section?.secondary_title || "",
        youtubeId: configuredYoutubeId,
        videoUrl: configuredYoutubeId ? "" : configuredVideoUrl,
      }
    : heroVideo;

  useEffect(() => {
    if (configuredVideoUrl) return undefined;
    let alive = true;
    const loadVideo = () => {
      videoService
        .getVideos()
        .then((videos) => {
          if (alive) setHeroVideo((videos || [])[0] || null);
        })
        .catch(() => {
          if (alive) setHeroVideo(null);
        });
    };

    const idleId = window.requestIdleCallback
      ? window.requestIdleCallback(loadVideo, { timeout: 3000 })
      : window.setTimeout(loadVideo, 1500);

    return () => {
      alive = false;
      if (window.cancelIdleCallback && typeof idleId === "number") {
        window.cancelIdleCallback(idleId);
      } else {
        window.clearTimeout(idleId);
      }
    };
  }, [configuredVideoUrl, language]);

  if (!section) {
    return (
      <section
        id="hero"
        aria-busy="true"
        className="relative min-h-screen pt-28 pb-16 md:pt-32 md:pb-20 flex items-center bg-white overflow-hidden"
      >
        <div className="container mx-auto w-full max-w-full px-4 md:max-w-7xl md:px-8">
          <div className="grid w-full min-w-0 grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-12">
            <div className="flex w-full min-w-0 max-w-full flex-col justify-center text-center lg:text-left">
              <div className="mx-auto mb-4 h-20 w-full max-w-xl rounded-3xl bg-primary/10 lg:mx-0" />
              <div className="mx-auto mb-8 h-16 w-full max-w-xl rounded-2xl bg-gray-100 lg:mx-0" />
              <div className="mx-auto flex w-full max-w-xl flex-col items-center gap-3 sm:flex-row lg:mx-0">
                <div className="h-14 w-full rounded-xl bg-primary/15 sm:w-44" />
                <div className="h-14 w-full rounded-xl bg-gray-100 sm:w-44" />
              </div>
            </div>
            <div className="flex w-full min-w-0 max-w-full justify-center items-center relative overflow-visible py-2 sm:px-8 sm:py-10 lg:px-9">
              <div className="relative mx-auto w-full max-w-[calc(100vw-2rem)] p-2.5 sm:max-w-[32rem] sm:p-3 rounded-[1.75rem] sm:rounded-[2.5rem] bg-primary-light shadow-2xl lg:max-w-none">
                <div className="aspect-[4/3] rounded-4xl border-4 border-white bg-gray-100 shadow-md" />
              </div>
            </div>
          </div>
        </div>
      </section>
    );
  }

  return (
    <section
      id="hero"
      className="relative min-h-screen pt-28 pb-16 md:pt-32 md:pb-20 flex items-center bg-no-repeat bg-top-right overflow-hidden"
      style={heroBackgroundImage ? { backgroundImage: `url('${heroBackgroundImage}')` } : undefined}
    >
      <div className="container mx-auto w-full max-w-full px-4 md:max-w-7xl md:px-8">
        <div className="grid w-full min-w-0 grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-12">
          {/* Left Column (Text) */}
          <div
            className={`flex w-full min-w-0 max-w-full flex-col justify-center text-center ${isRtl ? "lg:text-right" : "lg:text-left"}`}
          >
            <h1
              className="mx-auto max-w-[14ch] text-2xl sm:max-w-xl sm:text-4xl md:text-5xl lg:mx-0 lg:max-w-none lg:text-6xl font-extrabold tracking-tight text-navy leading-tight mb-4 break-words"
              style={{ overflowWrap: "anywhere" }}
            >
              {section.title || ""}
            </h1>
            <p className={`mx-auto max-w-[calc(100vw-2rem)] text-navy-light text-base sm:max-w-xl sm:text-lg md:text-xl font-medium mb-8 break-words ${isRtl ? "lg:mr-0 lg:ml-auto" : "lg:mx-0"}`}>
              {section.subtitle || ""}
            </p>
            <div className="flex w-full min-w-0 flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 sm:gap-4">
              <Link
                to={section?.settings?.cta_url || section?.cta_url || "/apply"}
                className="w-full max-w-[calc(100vw-2rem)] sm:w-auto sm:max-w-none inline-flex min-w-0 items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white px-6 sm:px-8 py-4 rounded-xl font-bold shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all duration-300 hover:-translate-y-0.5 group cursor-pointer text-center"
              >
                {section.cta_label || ""}
                <ArrowRight className={`w-4 h-4 transition-transform duration-300 ${isRtl ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`} />
              </Link>
              {activeHeroVideo && (
                <button
                  onClick={() => setIsVideoOpen(true)}
                  className="w-full max-w-[calc(100vw-2rem)] sm:w-auto sm:max-w-none inline-flex min-w-0 items-center justify-center gap-2.5 text-navy hover:text-primary transition-colors py-3 px-4 sm:px-6 rounded-xl font-bold group cursor-pointer text-center"
                >
                  <span className="w-12 h-12 rounded-full border-2 border-primary/20 flex items-center justify-center bg-white transition-all duration-300 group-hover:bg-primary group-hover:text-white group-hover:border-primary shadow-md">
                    <Play className="w-4 h-4 fill-current ml-0.5" />
                  </span>
                  {section.secondary_title || ""}
                </button>
              )}
            </div>
          </div>

          {/* Right Column (University Image with Premium Dashboard Styling) */}
          <div
            className="flex w-full min-w-0 max-w-full justify-center items-center relative overflow-visible py-2 sm:px-8 sm:py-10 lg:px-9"
          >
            {/* Main Image Container */}
            <div className="relative mx-auto w-full max-w-[calc(100vw-2rem)] p-2.5 sm:max-w-[32rem] sm:p-3 bg-linear-to-br from-primary-light to-white rounded-[1.75rem] sm:rounded-[2.5rem] shadow-2xl lg:max-w-none group">
              <div className="overflow-hidden rounded-4xl border-4 border-white shadow-md relative">
                {heroMainImage && (
                  <img
                    src={heroMainImage}
                    alt={section.image_alt || section.title || ""}
                    width="1200"
                    height="900"
                    loading="eager"
                    fetchPriority="high"
                    className="w-full h-auto aspect-[4/3] object-cover transition-transform duration-700 group-hover:scale-105"
                  />
                )}
                <div className="absolute inset-0 bg-linear-to-t from-navy/35 to-transparent mix-blend-multiply" />
              </div>

              {/* Floating Card 1: Students Count */}
              {heroStudentCount && (
                <div className="absolute z-10 bottom-3 left-3 sm:-bottom-6 sm:-left-6 bg-white/90 backdrop-blur-md border border-gray-100 p-3 sm:p-4 rounded-2xl shadow-xl flex max-w-[calc(100%-1.5rem)] items-center gap-3 sm:animate-float-slow">
                  <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                  </div>
                  <div>
                    <div className="text-navy font-extrabold text-sm leading-none">{heroStudentCount}</div>
                    <div className="text-gray-500 text-[10px] font-bold mt-1 uppercase tracking-wider break-words">{heroStudentLabel}</div>
                  </div>
                </div>
              )}

              {/* Floating Card 2: Programs */}
              {(heroAccreditedTitle || heroAccreditedLabel) && (
                <div className="absolute z-10 right-3 top-3 sm:-top-6 sm:-right-6 bg-white/90 backdrop-blur-md border border-gray-100 p-3 sm:p-4 rounded-2xl shadow-xl flex max-w-[calc(100%-1.5rem)] items-center gap-3 sm:animate-float-slow" style={{ animationDelay: "2s" }}>
                  <div className="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                    </svg>
                  </div>
                  <div>
                    <div className="text-navy font-extrabold text-sm leading-none">{heroAccreditedTitle}</div>
                    <div className="text-gray-500 text-[10px] font-bold mt-1 uppercase tracking-wider break-words">{heroAccreditedLabel}</div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Video Modal */}
      {isVideoOpen && activeHeroVideo && (
          <div className="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 backdrop-blur-md">
            <div className="relative w-full max-w-4xl aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl border border-white/10">
              <button
                onClick={() => setIsVideoOpen(false)}
                aria-label={t("common.close")}
                className="absolute top-4 right-4 z-25 p-2.5 rounded-full bg-black/40 hover:bg-black/60 text-white transition-all cursor-pointer shadow-md border border-white/10 hover:scale-105"
              >
                <X className="w-5 h-5" />
              </button>
              {activeHeroVideo.youtubeId ? (
                <iframe
                  src={`https://www.youtube.com/embed/${activeHeroVideo.youtubeId}?autoplay=1`}
                  title={activeHeroVideo.title || ""}
                  className="w-full h-full"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                />
              ) : (
                <video
                  src={publicAssetUrl(activeHeroVideo.videoUrl)}
                  className="w-full h-full object-cover"
                  controls
                  autoPlay
                  playsInline
                />
              )}
            </div>
          </div>
        )}
    </section>
  );
}
