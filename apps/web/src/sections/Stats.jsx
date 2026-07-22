import React, { useEffect, useState } from "react";
import CountUpComponent from "react-countup";
import { useInView } from "react-intersection-observer";
import { Users, GraduationCap, Award, BookOpen, Globe } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { aboutService } from "../services/aboutService";

const CountUp =
  typeof CountUpComponent === "function"
    ? CountUpComponent
    : CountUpComponent.default || CountUpComponent;

export default function Stats() {
  const { language } = useLanguage();
  const [aboutPage, setAboutPage] = useState(null);
  const { ref, inView } = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });

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

  const icons = [Users, Globe, GraduationCap, BookOpen, Award, BookOpen];
  const colors = [
    "text-blue-600 bg-blue-50 border-blue-100",
    "text-purple-600 bg-purple-50 border-purple-100",
    "text-orange-600 bg-orange-50 border-orange-100",
    "text-cyan-600 bg-cyan-50 border-cyan-100",
    "text-green-600 bg-green-50 border-green-100",
    "text-pink-600 bg-pink-50 border-pink-100",
  ];
  const stats = (aboutPage?.content?.stats?.items || []).map((item, index) => {
    const number = String(item.number || "");
    const match = number.match(/^([\d\s,.]+)(.*)$/);

    return {
      value: Number((match?.[1] || "0").replace(/[^\d.]/g, "")) || 0,
      suffix: match?.[2] || "",
      label: item.label || "",
      icon: icons[index] || Users,
      color: colors[index] || colors[0],
    };
  });

  if (!aboutPage || stats.length === 0) {
    return null;
  }

  return (
    <section ref={ref} id="stats" className="py-16 bg-primary-light">
      <div className="container mx-auto px-4 md:px-6 max-w-7xl">
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 md:gap-5">
          {stats.map((stat, index) => {
            const IconComponent = stat.icon;
            return (
              <motion.div
                key={index}
                initial={{ opacity: 0, scale: 0.95 }}
                animate={inView ? { opacity: 1, scale: 1 } : {}}
                transition={{ duration: 0.5, delay: index * 0.08 }}
                className="bg-white border border-gray-100/50 p-4 md:p-5 rounded-2xl shadow-sm flex items-center gap-4"
              >
                <div
                  className={`w-11 h-11 md:w-12 md:h-12 rounded-xl border flex items-center justify-center ${stat.color} shrink-0`}
                >
                  <IconComponent className="w-5.5 h-5.5 md:w-6 md:h-6" />
                </div>
                <div className="flex flex-col text-start min-w-0">
                  <span className="text-xl md:text-2xl font-extrabold text-navy leading-none">
                    {inView ? <CountUp end={stat.value} duration={2} /> : "0"}
                    {stat.suffix}
                  </span>
                  <span className="text-gray-500 font-bold text-[10px] md:text-xs mt-1.5 leading-tight line-clamp-2">
                    {stat.label}
                  </span>
                </div>
              </motion.div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
