import React from "react";
import { motion } from "framer-motion";
import { ShieldCheck, Zap, Globe } from "lucide-react";
import { useHomeSection } from "../hooks/useHomeSection";
import { publicAssetUrl } from "../lib/api";

export default function Values() {
  const { section } = useHomeSection("core_values");

  const icons = [ShieldCheck, Zap, Globe];
  const values = (section?.items || [])
    .map((item, index) => ({
      icon: icons[index] || ShieldCheck,
      title: item.title,
      description: item.description,
      image: publicAssetUrl(item.settings?.image || ""),
    }))
    .filter((item) => item.title || item.description);

  if (!section || values.length === 0) {
    return null;
  }

  return (
    <section id="values" className="py-24 bg-white border-t border-gray-50">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {section.eyebrow || ""}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {section.title || ""}
          </p>
        </div>

        {/* Cards Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {values.map((val, index) => {
            const Icon = val.icon;

            return (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 flex flex-col items-center text-center group"
              >
                <div className="mb-6 overflow-hidden rounded-2xl w-full aspect-video border border-gray-100/80 shadow-inner shrink-0 bg-primary/5 flex items-center justify-center">
                  {val.image ? (
                    <img
                      src={val.image}
                      alt={val.title || ""}
                      className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                  ) : (
                    <Icon className="w-12 h-12 text-primary" />
                  )}
                </div>
                <h3 className="text-xl font-bold text-navy mb-4 group-hover:text-primary transition-colors">
                  {val.title}
                </h3>
                <p className="text-gray-500 text-sm leading-relaxed">
                  {val.description}
                </p>
              </motion.div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
