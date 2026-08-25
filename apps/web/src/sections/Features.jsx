import React from "react";
import { ArrowRight, Cpu, Globe, Briefcase, Zap, Target } from "lucide-react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { useLanguage } from "../context/LanguageContext";
import { publicAssetUrl } from "../lib/api";
import { useHomeSection } from "../hooks/useHomeSection";

export default function Features() {
  const { isRtl } = useLanguage();
  const { section } = useHomeSection("strategic_goals");

  const imageSrc = publicAssetUrl(section?.settings?.image || "");
  const icons = [Cpu, Globe, Briefcase, Zap];
  const bstuBullets = (section?.items || [])
    .map((item, index) => ({ text: item.title || item.label, icon: icons[index] || Cpu }))
    .filter((item) => item.text);

  if (!section) {
    return null;
  }

  return (
    <section id="features" className="py-24 bg-white overflow-hidden border-t border-gray-50">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-20">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {section.eyebrow || ""}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {section.title || ""}
          </p>
          <div className="w-16 h-1 bg-primary mx-auto mt-4 rounded-full" />
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center mb-20">
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="lg:col-span-6 flex flex-col gap-6 text-start"
          >
            <span className="self-start inline-flex items-center bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest px-3.5 py-1.5 rounded-full">
              {section.subtitle || ""}
            </span>
            <h3 className="text-2xl md:text-3xl font-extrabold text-navy leading-tight">
              {section.secondary_title || ""}
            </h3>
            <p className="text-gray-500 text-sm md:text-base leading-relaxed">
              {section.description || section.secondary_description || ""}
            </p>

            {/* Bullet Points */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 my-2">
              {bstuBullets.map((bullet, index) => {
                const Icon = bullet.icon;
                return (
                  <div key={index} className="flex items-center gap-3 group">
                    <div className="w-9 h-9 rounded-xl bg-primary-light text-primary flex items-center justify-center shrink-0 group-hover:bg-primary group-hover:text-white transition-colors duration-300 shadow-sm border border-gray-100">
                      <Icon className="w-4 h-4" />
                    </div>
                    <span className="font-bold text-navy text-sm group-hover:text-primary transition-colors text-start">
                      {bullet.text}
                    </span>
                  </div>
                );
              })}
            </div>

            {/* Read More Button */}
            <div className="pt-2">
              <Link
                to={section.cta_url || "/about"}
                className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-7 py-3 rounded-xl font-extrabold text-xs transition-all shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5"
              >
                {section.cta_label || ""}
                <ArrowRight className={`w-3.5 h-3.5 transition-transform duration-300 ${isRtl ? 'rotate-180' : ''}`} />
              </Link>
            </div>
          </motion.div>

          {/* Right Column (University Entrance Image) */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="lg:col-span-6 flex justify-center"
          >
            <div className="relative group rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 bg-primary/5">
              {imageSrc ? (
                <img
                  src={imageSrc}
                  alt={section.image_alt || section.secondary_title || section.title || ""}
                  className="w-full max-w-150 object-cover transition-transform duration-750 group-hover:scale-105"
                />
              ) : (
                <div className="flex aspect-4/3 w-full max-w-150 min-w-80 items-center justify-center text-primary">
                  <Target className="h-16 w-16" />
                </div>
              )}
              <div className="absolute inset-0 bg-linear-to-t from-navy/20 via-transparent to-transparent" />
            </div>
          </motion.div>
        </div>

       

      </div>
    </section>
  );
}
