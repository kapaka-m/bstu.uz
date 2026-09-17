import React from "react";
import { motion } from "framer-motion";
import { ShieldCheck, Zap, Globe } from "lucide-react";
import { useHomeSection } from "../hooks/useHomeSection";
import { publicAssetUrl } from "../lib/api";

export default function Values() {
  const { section, loading } = useHomeSection("core_values");

  const icons = [ShieldCheck, Zap, Globe];
  const values = (section?.items || [])
    .map((item, index) => ({
      icon: icons[index] || ShieldCheck,
      title: item.title,
      description: item.description,
      image: publicAssetUrl(item.settings?.image || ""),
    }))
    .filter((item) => item.title || item.description);

  if ((!section || values.length === 0) && !loading) {
    return null;
  }

  if (!section) {
    return (
      <section id="values" aria-busy="true" className="py-24 bg-white border-t border-gray-50">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="mx-auto mb-3 h-4 w-36 rounded-full bg-primary/20" />
            <div className="mx-auto h-10 w-4/5 rounded-full bg-gray-100" />
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {[1, 2, 3].map((item) => (
              <div key={item} className="min-h-90 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div className="mb-6 aspect-video rounded-2xl bg-primary/5" />
                <div className="mx-auto mb-4 h-6 w-3/4 rounded-full bg-gray-100" />
                <div className="mx-auto h-20 w-full rounded-2xl bg-gray-50" />
              </div>
            ))}
          </div>
        </div>
      </section>
    );
  }

  return (
    <section id="values" className="py-24 bg-white border-t border-gray-50">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <p className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {section.eyebrow || ""}
          </p>
          <h2 className="text-3xl md:text-4xl font-extrabold text-navy">
            {section.title || ""}
          </h2>
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
                      width="640"
                      height="360"
                      loading="lazy"
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
