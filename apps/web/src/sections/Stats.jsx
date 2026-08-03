import React from "react";
import CountUpComponent from "react-countup";
import { useInView } from "react-intersection-observer";
import { Users, GraduationCap, Award, BookOpen, Globe } from "lucide-react";
import { motion } from "framer-motion";
import { Swiper, SwiperSlide } from "swiper/react";
import { Autoplay } from "swiper/modules";
import { useHomeSection } from "../hooks/useHomeSection";
import "swiper/css";

const CountUp =
  typeof CountUpComponent === "function"
    ? CountUpComponent
    : CountUpComponent.default || CountUpComponent;

export default function Stats() {
  const { section } = useHomeSection("stats");
  const { ref, inView } = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });

  const icons = [Users, Globe, GraduationCap, BookOpen, Award, BookOpen, Users, Award];
  const colors = [
    "text-blue-600 bg-blue-50 border-blue-100",
    "text-purple-600 bg-purple-50 border-purple-100",
    "text-orange-600 bg-orange-50 border-orange-100",
    "text-cyan-600 bg-cyan-50 border-cyan-100",
    "text-green-600 bg-green-50 border-green-100",
    "text-pink-600 bg-pink-50 border-pink-100",
  ];
  const stats = (section?.items || []).map((item, index) => {
    const value = String(item.value || "0");

    return {
      value: Number(value.replace(/[^\d.]/g, "")) || 0,
      suffix: item.suffix || "",
      label: item.label || item.title || "",
      icon: icons[index] || Users,
      color: colors[index] || colors[0],
    };
  });

  if (!section || stats.length === 0) {
    return null;
  }

  const renderStatCard = (stat, index, keyPrefix = "stat") => {
    const IconComponent = stat.icon;

    return (
      <motion.div
        key={`${keyPrefix}-${index}`}
        initial={{ opacity: 0, scale: 0.95 }}
        animate={inView ? { opacity: 1, scale: 1 } : {}}
        transition={{ duration: 0.5, delay: index * 0.08 }}
        className="bg-white border border-gray-100/50 p-4 md:p-5 rounded-2xl shadow-sm flex items-center gap-4 h-full min-h-24"
      >
        <div
          className={`w-11 h-11 md:w-12 md:h-12 rounded-xl border flex items-center justify-center ${stat.color} shrink-0`}
        >
          <IconComponent className="w-5.5 h-5.5 md:w-6 md:h-6" />
        </div>
        <div className="flex flex-col text-start min-w-0">
          <span className="text-xl md:text-2xl font-extrabold text-navy leading-none">
            {inView ? <CountUp end={stat.value} duration={2} separator="," /> : "0"}
            {stat.suffix}
          </span>
          <span className="text-gray-500 font-bold text-[10px] md:text-xs mt-1.5 leading-tight line-clamp-2">
            {stat.label}
          </span>
        </div>
      </motion.div>
    );
  };

  return (
    <section ref={ref} id="stats" className="py-16 bg-primary-light">
      <div className="container mx-auto px-4 md:px-6 max-w-7xl">
        <div className="md:hidden">
          <Swiper
            modules={[Autoplay]}
            slidesPerView={1.12}
            spaceBetween={14}
            loop={stats.length > 2}
            speed={4500}
            autoplay={{
              delay: 0,
              disableOnInteraction: false,
              pauseOnMouseEnter: true,
            }}
            className="stats-mobile-swiper overflow-visible! [&_.swiper-wrapper]:items-stretch [&_.swiper-wrapper]:ease-linear"
          >
            {stats.map((stat, index) => (
              <SwiperSlide key={`mobile-${index}`} className="h-auto">
                {renderStatCard(stat, index, "mobile-stat")}
              </SwiperSlide>
            ))}
          </Swiper>
        </div>

        <div className="hidden md:grid md:grid-cols-4 gap-4 md:gap-5">
          {stats.map((stat, index) => renderStatCard(stat, index))}
        </div>
      </div>
    </section>
  );
}
