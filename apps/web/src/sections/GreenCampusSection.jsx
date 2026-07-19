import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import { ArrowRight, Leaf } from "lucide-react";
import { greenCampusService } from "../services/greenCampusService";
import { useLanguage } from "../context/LanguageContext";

export default function GreenCampusSection() {
  const { language } = useLanguage();
  const [settings, setSettings] = useState({});
  const [articles, setArticles] = useState([]);

  useEffect(() => {
    let active = true;

    Promise.all([
      greenCampusService.getSettings(),
      greenCampusService.getArticles(),
    ])
      .then(([nextSettings, nextArticles]) => {
        if (!active) return;
        setSettings(nextSettings);
        setArticles(nextArticles.slice(0, Number(nextSettings.home_limit || 3)));
      })
      .catch(() => {
        if (!active) return;
        setSettings({});
        setArticles([]);
      });

    return () => {
      active = false;
    };
  }, [language]);

  if (articles.length === 0) {
    return null;
  }

  return (
    <section
      id="green-campus-preview"
      className="py-24 bg-linear-to-b from-gray-50 to-white relative overflow-hidden"
    >
      {/* Decorative leafy background elements */}
      <div className="absolute top-0 right-0 w-80 h-80 bg-emerald-500/5 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3 pointer-events-none" />
      <div className="absolute bottom-0 left-0 w-80 h-80 bg-emerald-500/5 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3 pointer-events-none" />

      <div className="container mx-auto px-4 md:px-8 max-w-7xl relative z-10">
        {/* Section Header */}
        <div className="text-center max-w-3xl mx-auto mb-16">
          <div className="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 text-xs font-bold uppercase tracking-wider px-3.5 py-1.5 rounded-full mb-3">
            <Leaf className="w-3.5 h-3.5 shrink-0" />
            {settings.home_tag || ""}
          </div>
          <p className="text-3xl md:text-4xl font-extrabold text-navy leading-tight">
            {settings.home_title || ""}
          </p>
          <div className="w-16 h-1 bg-emerald-500 mx-auto mt-4 rounded-full" />
        </div>

        {/* Articles Preview Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
          {articles.map((item, index) => {
            return (
              <motion.div
                key={item.id}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                className="bg-white border border-gray-100/80 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 flex flex-col text-start relative group"
              >
                <div className="w-full aspect-video rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 mb-6">
                  <img
                    src={item.image}
                    alt={item.title}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                    onError={(e) => {
                      e.currentTarget.style.display = "none";
                    }}
                  />
                </div>

                <h3 className="text-lg font-extrabold text-navy mb-3 group-hover:text-emerald-600 transition-colors">
                  {item.title}
                </h3>

                <p className="text-gray-500 text-xs font-semibold leading-relaxed mb-6 grow">
                  {item.excerpt}
                </p>

                <div className="pt-4 border-t border-gray-50 flex items-center justify-between text-xs font-extrabold text-emerald-600 group-hover:text-emerald-700">
                  <span>{settings.read_more_label || ""}</span>
                  <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1 rtl:rotate-180" />
                </div>

                {/* Direct link wrap to full page */}
                <Link
                  to={`/green-campus/${item.id}`}
                  className="absolute inset-0"
                />
              </motion.div>
            );
          })}
        </div>

        {/* CTA Button */}
        <div className="text-center">
          <Link
            to="/green-campus"
            className="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm px-8 py-4 rounded-2xl shadow-lg shadow-emerald-600/10 hover:shadow-emerald-600/20 transition-all hover:-translate-y-0.5"
          >
            {settings.view_all_label || ""}
            <ArrowRight className="w-4 h-4 rtl:rotate-180" />
          </Link>
        </div>
      </div>
    </section>
  );
}
