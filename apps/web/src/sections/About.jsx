import React, { useEffect, useState } from "react";
import { ArrowRight } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { aboutService } from "../services/aboutService";
import { publicAssetUrl } from "../lib/api";
import { useHomeSection } from "../hooks/useHomeSection";

export default function About() {
  const { language, isRtl } = useLanguage();
  const [aboutPage, setAboutPage] = useState(null);
  const { section } = useHomeSection("identity");

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

  const imageSrc = publicAssetUrl(
    section?.settings?.image || aboutPage?.identity_image_url || aboutPage?.identity_image || "",
  );

  if (!section) {
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
              {section.eyebrow || ""}
            </h3>
            <h2 className="text-2xl md:text-3xl lg:text-4xl font-extrabold text-navy leading-snug mb-6">
              {section.title || ""}
            </h2>
            <p className="text-gray-500 mb-6 leading-relaxed">
              {section.description || ""}
            </p>
            {section.secondary_description && (
              <div className="mb-8 p-4 bg-primary/5 border border-primary/10 rounded-2xl flex items-center gap-3">
                <div className="text-xs text-gray-600 leading-relaxed text-start">
                  {section.secondary_description}
                </div>
              </div>
            )}
            <div>
              <a
                href={section.cta_url || "/about"}
                className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-6 py-3.5 rounded-xl text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-primary/30 transition-all duration-300 hover:-translate-y-0.5 group"
              >
                {section.cta_label || ""}
                <ArrowRight className={`w-4 h-4 transition-transform duration-300 ${isRtl ? 'rotate-180 group-hover:-translate-x-1' : 'group-hover:translate-x-1'}`} />
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
                alt={section.image_alt || section.title || ""}
                className="w-full object-cover transition-transform duration-500 hover:scale-105"
              />
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
