import React, { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import {
  ArrowRight,
  Contact,
  UserCheck,
  BookOpen,
  PieChart,
  CreditCard,
  Calendar,
  GraduationCap,
  ListTodo,
  FileText,
  Users,
  Map,
  TrendingUp,
  Home,
  Send,
  AlertTriangle,
  Mail,
  Loader2,
} from "lucide-react";
import { Swiper, SwiperSlide } from "swiper/react";
import { Autoplay } from "swiper/modules";
import { useLanguage } from "../context/LanguageContext";
import { serviceService } from "../services/serviceService";
import "swiper/css";

const colorConfig = {
  cyan: {
    border: "border-cyan-100/80 hover:border-cyan-500",
    icon: "text-cyan-600 bg-cyan-50/80",
  },
  orange: {
    border: "border-orange-100/80 hover:border-orange-500",
    icon: "text-orange-600 bg-orange-50/80",
  },
  teal: {
    border: "border-teal-100/80 hover:border-teal-500",
    icon: "text-teal-600 bg-teal-50/80",
  },
  red: {
    border: "border-red-100/80 hover:border-red-500",
    icon: "text-red-600 bg-red-50/80",
  },
  indigo: {
    border: "border-indigo-100/80 hover:border-indigo-500",
    icon: "text-indigo-600 bg-indigo-50/80",
  },
  pink: {
    border: "border-pink-100/80 hover:border-pink-500",
    icon: "text-pink-600 bg-pink-50/80",
  },
  blue: {
    border: "border-blue-100/80 hover:border-blue-500",
    icon: "text-blue-600 bg-blue-50/80",
  },
  emerald: {
    border: "border-emerald-100/80 hover:border-emerald-500",
    icon: "text-emerald-600 bg-emerald-50/80",
  },
  violet: {
    border: "border-violet-100/80 hover:border-violet-500",
    icon: "text-violet-600 bg-violet-50/80",
  },
  amber: {
    border: "border-amber-100/80 hover:border-amber-500",
    icon: "text-amber-600 bg-amber-50/80",
  },
  rose: {
    border: "border-rose-100/80 hover:border-rose-500",
    icon: "text-rose-600 bg-rose-50/80",
  },
};

const iconMap = {
  contact: Contact,
  "user-check": UserCheck,
  "book-open": BookOpen,
  "pie-chart": PieChart,
  "credit-card": CreditCard,
  calendar: Calendar,
  "graduation-cap": GraduationCap,
  "list-todo": ListTodo,
  "file-text": FileText,
  users: Users,
  map: Map,
  "trending-up": TrendingUp,
  home: Home,
  send: Send,
  "alert-triangle": AlertTriangle,
  mail: Mail,
};

export default function Services({ limit }) {
  const { language, isRtl } = useLanguage();
  const [settings, setSettings] = useState({});
  const [services, setServices] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let active = true;
    setLoading(true);

    Promise.all([
      serviceService.getSettings().catch(() => ({})),
      limit
        ? serviceService.getHomeServices(limit).catch(() => [])
        : serviceService.getServices().catch(() => []),
    ])
      .then(([nextSettings, nextServices]) => {
        if (!active) return;
        const homeLimit = Number(nextSettings?.home_limit || limit || 0);
        setSettings(nextSettings || {});
        setServices(limit && homeLimit ? nextServices.slice(0, homeLimit) : nextServices);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [language, limit]);

  const displayServices = useMemo(() => services, [services]);

  const renderServiceCard = (service, index) => {
    const Icon = iconMap[service.icon] || Contact;
    const colors = colorConfig[service.color] || colorConfig.cyan;

    return (
      <div
        key={service.slug || index}
        className={`group bg-white border ${colors.border} p-8 rounded-3xl shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 flex flex-col relative overflow-hidden text-start h-full min-h-80`}
      >
        <div
          className={`w-14 h-14 rounded-2xl flex items-center justify-center mb-6 transition-all duration-300 ${colors.icon}`}
        >
          <Icon className="w-6 h-6" />
        </div>

        <h3 className="text-xl font-bold text-navy mb-4 min-h-14 line-clamp-2">
          {service.title}
        </h3>
        <p className="text-gray-500 mb-8 leading-relaxed text-sm min-h-24 line-clamp-4">
          {service.description}
        </p>

        <div className="mt-auto">
          <a
            href={service.url}
            target={service.opens_new_tab ? "_blank" : undefined}
            rel={service.opens_new_tab ? "noopener noreferrer" : undefined}
            className="inline-flex items-center gap-1.5 text-sm font-bold text-primary cursor-pointer"
          >
            {service.action_label}
            <span
              className={`transition-transform duration-300 ${isRtl ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
            >
              →
            </span>
          </a>
        </div>
      </div>
    );
  };

  return (
    <section id="services" className="py-24 bg-primary-light">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {settings.home_tag || ""}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {settings.home_title || ""}
          </p>
        </div>

        {loading && (
          <div className="flex items-center justify-center py-12 text-primary">
            <Loader2 className="h-6 w-6 animate-spin" />
            <span className="ms-3 text-sm font-bold">{settings.loading_label || ""}</span>
          </div>
        )}

        {!loading && displayServices.length === 0 && (
          <div className="rounded-3xl border border-gray-100 bg-white p-10 text-center text-sm font-bold text-gray-500">
            {settings.no_results_label || ""}
          </div>
        )}

        {/* Services Grid */}
        {!loading && displayServices.length > 0 && (
          limit ? (
            <Swiper
              key={language}
              dir={isRtl ? "rtl" : "ltr"}
              modules={[Autoplay]}
              spaceBetween={28}
              slidesPerView={1}
              autoplay={{ delay: 5000, disableOnInteraction: false }}
              breakpoints={{
                768: { slidesPerView: 2 },
                1024: { slidesPerView: 3 },
                1280: { slidesPerView: 4 },
              }}
              className="pt-3 pb-6 overflow-visible! [&_.swiper-wrapper]:items-stretch"
            >
              {displayServices.map((service, index) => (
                <SwiperSlide key={service.slug || index} className="h-auto flex">
                  {renderServiceCard(service, index)}
                </SwiperSlide>
              ))}
            </Swiper>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
              {displayServices.map((service, index) => renderServiceCard(service, index))}
            </div>
          )
        )}

        {/* View All Services Button */}
        {limit && (
          <div className="text-center mt-16">
            <Link
              to="/services"
              className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-8 py-3.5 rounded-xl font-extrabold text-sm transition-all shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5"
            >
              {settings.view_all_label || ""}
              <ArrowRight
                className={`w-4 h-4 transition-transform ${isRtl ? "rotate-180" : ""}`}
              />
            </Link>
          </div>
        )}
      </div>
    </section>
  );
}
