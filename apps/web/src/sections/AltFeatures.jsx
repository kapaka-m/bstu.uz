import React, { useEffect, useState } from "react";
import { Award, ClipboardList, Dribbble, Filter, Zap, ShieldCheck } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { aboutService } from "../services/aboutService";

export default function AltFeatures() {
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
  const values = content.values || {};
  const stats = content.stats?.items || [];

  const features = [
    {
      icon: ShieldCheck,
      title: values.integrityTitle,
      description: values.integrityDesc,
    },
    {
      icon: ClipboardList,
      title: values.innovationTitle,
      description: values.innovationDesc,
    },
    {
      icon: Award,
      title: values.inclusivityTitle,
      description: values.inclusivityDesc,
    },
    ...stats.slice(0, 3).map((item, index) => ({
      icon: [Zap, Dribbble, Filter][index] || Zap,
      title: item.label,
      description: item.desc,
    })),
  ].filter((item) => item.title || item.description);

  if (!aboutPage || features.length === 0) {
    return null;
  }

  return (
    <section id="alt-features" className="py-24 bg-white border-t border-gray-50 overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          {/* Left Column (Grid Items) */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="lg:col-span-7 order-2 lg:order-1"
          >
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-10">
              {features.map((feat, index) => {
                const IconComponent = feat.icon;
                return (
                  <motion.div
                    key={index}
                    initial={{ opacity: 0, y: 20 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.5, delay: index * 0.1 }}
                    className="flex gap-4 group"
                  >
                    <div className="w-12 h-12 rounded-xl bg-primary-light text-primary flex items-center justify-center shrink-0 group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                      <IconComponent className="w-5 h-5" />
                    </div>
                    <div className="text-start">
                      <h4 className="text-lg font-bold text-navy mb-2 group-hover:text-primary transition-colors">
                        {feat.title}
                      </h4>
                      <p className="text-gray-500 text-sm leading-relaxed">
                        {feat.description}
                      </p>
                    </div>
                  </motion.div>
                );
              })}
            </div>
          </motion.div>

          {/* Right Column (Image) */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="lg:col-span-5 order-1 lg:order-2 flex justify-center"
          >
            <div className="relative rounded-3xl overflow-hidden shadow-lg border border-gray-100/80 aspect-4/3 w-full max-w-112.5 lg:max-w-none shrink-0 bg-white flex items-center justify-center">
              <div className="absolute inset-0 bg-[radial-gradient(#0d6efd12_1px,transparent_1px)] bg-size-[18px_18px]" />
              <div className="relative w-32 h-32 rounded-3xl bg-primary/10 text-primary flex items-center justify-center shadow-sm border border-primary/10">
                <Award className="w-14 h-14" />
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
