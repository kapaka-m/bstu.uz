import React from "react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

export default function Values() {
  const { t } = useLanguage();

  const values = [
    {
      image: "/assets/img/values/values-academic.jpg",
      title: t("home.values.academic.title"),
      description: t("home.values.academic.desc")
    },
    {
      image: "/assets/img/values/values-scientific.jpg",
      title: t("home.values.scientific.title"),
      description: t("home.values.scientific.desc")
    },
    {
      image: "/assets/img/values/values-global.jpg",
      title: t("home.values.global.title"),
      description: t("home.values.global.desc")
    }
  ];

  return (
    <section id="values" className="py-24 bg-white border-t border-gray-50">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {t("home.values.tag")}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {t("home.values.title")}
          </p>
        </div>

        {/* Cards Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {values.map((val, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: index * 0.1 }}
              className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 flex flex-col items-center text-center group"
            >
              <div className="mb-6 overflow-hidden rounded-2xl w-full aspect-video border border-gray-100/80 shadow-inner shrink-0">
                <img
                  src={val.image}
                  alt={val.title}
                  className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                  loading="lazy"
                />
              </div>
              <h3 className="text-xl font-bold text-navy mb-4 group-hover:text-primary transition-colors">
                {val.title}
              </h3>
              <p className="text-gray-500 text-sm leading-relaxed">
                {val.description}
              </p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}