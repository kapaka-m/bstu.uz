import React from "react";
import CountUpComponent from "react-countup";
import { useInView } from "react-intersection-observer";
import { Users, GraduationCap, Award, BookOpen, Globe } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

const CountUp =
  typeof CountUpComponent === "function"
    ? CountUpComponent
    : CountUpComponent.default || CountUpComponent;

export default function Stats() {
  const { t } = useLanguage();
  const { ref, inView } = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });

  const stats = [
    {
      value: 18000,
      suffix: "+",
      label: t("about.stats.activeStudents"),
      icon: Users,
      color: "text-blue-600 bg-blue-50 border-blue-100",
    },
    {
      value: 250,
      suffix: "+",
      label: t("about.stats.intStudents"),
      icon: Globe,
      color: "text-purple-600 bg-purple-50 border-purple-100",
    },
    {
      value: 700,
      suffix: "+",
      label: t("about.stats.professors"),
      icon: GraduationCap,
      color: "text-orange-600 bg-orange-50 border-orange-100",
    },
    {
      value: 80,
      suffix: "+",
      label: t("common.academicPrograms"),
      icon: BookOpen,
      color: "text-cyan-600 bg-cyan-50 border-cyan-100",
    },
    {
      value: 4,
      suffix: "",
      label: t("about.stats.faculties"),
      icon: Award,
      color: "text-green-600 bg-green-50 border-green-100",
    },
    {
      value: 24,
      suffix: "",
      label: t("about.stats.departments"),
      icon: BookOpen,
      color: "text-pink-600 bg-pink-50 border-pink-100",
    },
  ];

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
