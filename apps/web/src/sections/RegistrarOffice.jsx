import React from "react";
import { ExternalLink, CheckCircle2, Landmark } from "lucide-react";
import { motion } from "framer-motion";
import { useHomeSection } from "../hooks/useHomeSection";
import { useLanguage } from "../context/LanguageContext";
import { publicAssetUrl } from "../lib/api";

export default function RegistrarOffice() {
  const { section, loading } = useHomeSection("registrar_office");
  const { language } = useLanguage();

  if (!section && !loading) {
    return null;
  }

  if (!section) {
    return (
      <section id="registrar-office" aria-busy="true" className="py-20 bg-white overflow-hidden">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div className="relative order-2 lg:order-1">
              <div className="p-4 rounded-[2.5rem] bg-primary-light shadow-xl">
                <div className="aspect-[16/10] rounded-4xl bg-white shadow-lg" />
              </div>
            </div>
            <div className="flex flex-col order-1 lg:order-2">
              <div className="mb-4 h-7 w-36 rounded-full bg-primary/10" />
              <div className="mb-4 h-20 w-full rounded-2xl bg-gray-100" />
              <div className="mb-6 h-24 w-full rounded-2xl bg-gray-50" />
              <div className="mb-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {[1, 2, 3, 4].map((item) => (
                  <div key={item} className="h-8 rounded-xl bg-primary-light" />
                ))}
              </div>
              <div className="h-14 w-44 rounded-xl bg-primary/15" />
            </div>
          </div>
        </div>
      </section>
    );
  }

  const imageSrc = publicAssetUrl(section.settings?.image || "");

  return (
    <section id="registrar-office" className="py-20 bg-white overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          {/* Left Column - Image */}
          <motion.div
            initial={{ opacity: 0, x: -30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="relative order-2 lg:order-1"
          >
            <div className="relative group p-4 bg-linear-to-br from-primary-light to-white rounded-[2.5rem] shadow-xl border border-gray-100/50">
              <div className="overflow-hidden rounded-4xl shadow-lg relative aspect-[16/10] bg-white flex items-center justify-center">
                {imageSrc ? (
                  <img
                    src={imageSrc}
                    alt={section.image_alt || section.title || ""}
                    width="960"
                    height="600"
                    loading="lazy"
                    className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                  />
                ) : (
                  <>
                    <div className="absolute inset-0 bg-[radial-gradient(#0d6efd12_1px,transparent_1px)] bg-size-[18px_18px]" />
                    <div className="relative w-28 h-28 rounded-3xl bg-primary/10 text-primary flex items-center justify-center shadow-sm border border-primary/10">
                      <Landmark className="w-12 h-12" />
                    </div>
                  </>
                )}
              </div>
              {/* Decorative accent */}
              <div className="absolute -top-3 -left-3 w-16 h-16 bg-primary/5 rounded-full blur-xl" />
              <div className="absolute -bottom-3 -right-3 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl" />
            </div>
          </motion.div>

          {/* Right Column - Content */}
          <motion.div
            initial={{ opacity: 0, x: 30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="flex flex-col text-start order-1 lg:order-2"
          >
            <span className="self-start inline-flex items-center gap-1 bg-primary/10 text-primary text-[11px] font-extrabold uppercase tracking-widest px-3.5 py-1.5 rounded-full mb-4">
              {section.eyebrow || ""}
            </span>
            <h2 className="text-3xl md:text-4xl font-extrabold text-navy leading-tight mb-4">
              {section.title || ""}
            </h2>
            <p className="text-gray-500 text-base md:text-lg leading-relaxed mb-6">
              {section.description || ""}
            </p>

            {/* List of Services */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
              {(section.items || []).map((service, index) => (
                <div key={index} className="flex items-start gap-3">
                  <CheckCircle2 className="w-5 h-5 text-primary shrink-0 mt-0.5" />
                  <span className="text-navy font-bold text-sm leading-snug text-start">
                    {service.title}
                  </span>
                </div>
              ))}
            </div>

            {/* CTA Button */}
            <div>
              <a
                href={section.cta_url || "#"}
                target={/^https?:\/\//i.test(section.cta_url || "") ? "_blank" : undefined}
                rel={/^https?:\/\//i.test(section.cta_url || "") ? "noopener noreferrer" : undefined}
                className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-8 py-4 rounded-xl font-extrabold shadow-md shadow-primary/20 hover:shadow-primary/30 transition-all hover:-translate-y-0.5 cursor-pointer"
              >
                {section.cta_label || ""}
                <ExternalLink className={`w-4 h-4 ${language === "ar" ? "-scale-x-100" : ""}`} />
              </a>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
