import React, { useEffect, useState } from "react";
import { ArrowRight } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { aboutService } from "../services/aboutService";

const API_ORIGIN = (import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1").replace(
  /\/api\/v1\/?$/,
  "",
);

const resolveAssetUrl = (path) => {
  if (!path) return "";
  if (path.startsWith("http://") || path.startsWith("https://") || path.startsWith("/")) {
    return path;
  }
  return `${API_ORIGIN}/storage/${path}`;
};

export default function About() {
  const { language } = useLanguage();
  const [aboutPage, setAboutPage] = useState(null);

  useEffect(() => {
    let alive = true;

    aboutService
      .getPage(language)
      .then((page) => {
        if (alive) setAboutPage(page || null);
      })
      .catch(() => {
        if (alive) setAboutPage(null);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  const content = aboutPage?.content || {};
  const hero = content.hero || {};
  const identity = content.identity || {};
  const imageSrc = resolveAssetUrl(aboutPage?.identity_image_url || aboutPage?.identity_image || "");

  if (!aboutPage) {
    return null;
  }

  return (
    <section id="about" className="py-24 bg-white overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          {/* Left Content Card */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.8 }}
            className="bg-primary-light p-8 md:p-12 rounded-3xl flex flex-col justify-center border border-gray-100 text-start"
          >
            <h3 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
              {identity.badge || hero.badge || ""}
            </h3>
            <h2 className="text-2xl md:text-3xl lg:text-4xl font-extrabold text-navy leading-snug mb-6">
              {identity.title || hero.title || ""}
            </h2>
            <p className="text-gray-500 mb-6 leading-relaxed">
              {identity.desc1 || hero.subtitle || ""}
            </p>
            {identity.desc2 && (
              <div className="mb-8 p-4 bg-primary/5 border border-primary/10 rounded-2xl flex items-center gap-3">
                <div className="text-xs text-gray-600 leading-relaxed text-start">
                  {identity.desc2}
                </div>
              </div>
            )}
            <div>
              <a
                href="/about"
                className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-6 py-3.5 rounded-xl text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-primary/30 transition-all duration-300 hover:-translate-y-0.5 group"
              >
                {hero.campusBtn || ""}
                <ArrowRight className={`w-4 h-4 transition-transform duration-300 ${language === 'ar' ? 'rotate-180 group-hover:-translate-x-1' : 'group-hover:translate-x-1'}`} />
              </a>
            </div>
          </motion.div>

          {/* Right Image */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.8 }}
            className="flex items-center justify-center relative"
          >
            <div className="relative rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-shadow duration-300">
              <img
                src={imageSrc}
                alt={identity.title || hero.title || ""}
                className="w-full object-cover transition-transform duration-500 hover:scale-105"
              />
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
