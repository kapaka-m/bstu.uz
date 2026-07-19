import React from "react";
import { Award, ClipboardList, Dribbble, Filter, Zap, ShieldCheck } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

export default function AltFeatures() {
  const { t } = useLanguage();

  const features = [
    {
      icon: ShieldCheck,
      title: t("home.altFeatures.feat1.title"),
      description: t("home.altFeatures.feat1.desc")
    },
    {
      icon: ClipboardList,
      title: t("home.altFeatures.feat2.title"),
      description: t("home.altFeatures.feat2.desc")
    },
    {
      icon: Award,
      title: t("home.altFeatures.feat3.title"),
      description: t("home.altFeatures.feat3.desc")
    },
    {
      icon: Zap,
      title: t("home.altFeatures.feat4.title"),
      description: t("home.altFeatures.feat4.desc")
    },
    {
      icon: Dribbble,
      title: t("home.altFeatures.feat5.title"),
      description: t("home.altFeatures.feat5.desc")
    },
    {
      icon: Filter,
      title: t("home.altFeatures.feat6.title"),
      description: t("home.altFeatures.feat6.desc")
    }
  ];

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
            <div className="relative rounded-3xl overflow-hidden shadow-lg border border-gray-100/80 aspect-4/3 w-full max-w-112.5 lg:max-w-none shrink-0 bg-gray-50">
              <img
                src="/assets/img/features/graduation.jpg"
                alt={t("home.altFeatures.imageAlt")}
                className="w-full h-full object-cover transition-transform duration-500 hover:scale-103"
                loading="lazy"
              />
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
