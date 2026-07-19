import React from "react";
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
} from "lucide-react";
import { useLanguage } from "../context/LanguageContext";

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
};

export default function Services({ limit }) {
  const { t, language } = useLanguage();

  const interactiveServices = [
    {
      name: t("home.services.items.hemisStudent.name"),
      link: "https://student.bstu.uz/en/dashboard/login",
      description: t("home.services.items.hemisStudent.desc"),
      icon: Contact,
      color: "teal",
      actionText: t("home.services.items.hemisStudent.action"),
    },
    {
      name: t("home.services.items.officeRegistrar.name"),
      link: "https://ro.bstu.uz/student/login",
      description: t("home.services.items.officeRegistrar.desc"),
      icon: PieChart,
      color: "red",
      actionText: t("home.services.items.officeRegistrar.action"),
    },
    {
      name: t("home.services.items.distanceLearning.name"),
      link: "https://mt.bstu.uz/login/index.php",
      description: t("home.services.items.distanceLearning.desc"),
      icon: GraduationCap,
      color: "indigo",
      actionText: t("home.services.items.distanceLearning.action"),
    },
    {
      name: t("home.services.items.kontraktPortal.name"),
      link: "https://kontrakt.edu.uz/login",
      description: t("home.services.items.kontraktPortal.desc"),
      icon: CreditCard,
      color: "cyan",
      actionText: t("home.services.items.kontraktPortal.action"),
    },
    {
      name: t("home.services.items.remotePlatform.name"),
      link: "http://mb.bstu.uz/",
      description: t("home.services.items.remotePlatform.desc"),
      icon: ListTodo,
      color: "pink",
      actionText: t("home.services.items.remotePlatform.action"),
    },
    {
      name: t("home.services.items.stopCorruption.name"),
      link: "https://t.me/BDTU_antikorrupsiyabot",
      description: t("home.services.items.stopCorruption.desc"),
      icon: AlertTriangle,
      color: "red",
      actionText: t("home.services.items.stopCorruption.action"),
    },
    {
      name: t("home.services.items.accommodation.name"),
      link: "https://ttj.bstu.uz/",
      description: t("home.services.items.accommodation.desc"),
      icon: Home,
      color: "teal",
      actionText: t("home.services.items.accommodation.action"),
    },
    {
      name: t("home.services.items.schedule.name"),
      link: "https://timetable.bstu.uz/",
      description: t("home.services.items.schedule.desc"),
      icon: Calendar,
      color: "cyan",
      actionText: t("home.services.items.schedule.action"),
    },
    {
      name: t("home.services.items.paymentSystem.name"),
      link: "https://pay.bstu.uz/",
      description: t("home.services.items.paymentSystem.desc"),
      icon: CreditCard,
      color: "teal",
      actionText: t("home.services.items.paymentSystem.action"),
    },
    {
      name: t("home.services.items.hemisTeachers.name"),
      link: "https://hemis.bstu.uz/",
      description: t("home.services.items.hemisTeachers.desc"),
      icon: UserCheck,
      color: "indigo",
      actionText: t("home.services.items.hemisTeachers.action"),
    },
    {
      name: t("home.services.items.digitalLibrary.name"),
      link: "https://lib.bstu.uz/",
      description: t("home.services.items.digitalLibrary.desc"),
      icon: BookOpen,
      color: "orange",
      actionText: t("home.services.items.digitalLibrary.action"),
    },
    {
      name: t("home.services.items.dissertations.name"),
      link: "https://dissertations.bstu.uz/",
      description: t("home.services.items.dissertations.desc"),
      icon: FileText,
      color: "orange",
      actionText: t("home.services.items.dissertations.action"),
    },
    {
      name: t("home.services.items.conferences.name"),
      link: "https://conferences.bstu.uz/",
      description: t("home.services.items.conferences.desc"),
      icon: Users,
      color: "cyan",
      actionText: t("home.services.items.conferences.action"),
    },
    {
      name: t("home.services.items.virtualTour.name"),
      link: "https://bstu.uz/tour/",
      description: t("home.services.items.virtualTour.desc"),
      icon: Map,
      color: "teal",
      actionText: t("home.services.items.virtualTour.action"),
    },
    {
      name: t("home.services.items.kpiSystem.name"),
      link: "https://kpi.bstu.uz/",
      description: t("home.services.items.kpiSystem.desc"),
      icon: TrendingUp,
      color: "pink",
      actionText: t("home.services.items.kpiSystem.action"),
    },
    {
      name: t("home.services.items.telegramBot.name"),
      link: "https://t.me/bdtu_uz_rasmiy",
      description: t("home.services.items.telegramBot.desc"),
      icon: Send,
      color: "indigo",
      actionText: t("home.services.items.telegramBot.action"),
    },
    {
      name: t("home.services.items.webmail.name"),
      link: "https://webmail.bstu.uz/",
      description: t("home.services.items.webmail.desc"),
      icon: Mail,
      color: "cyan",
      actionText: t("home.services.items.webmail.action"),
    },
  ];

  const displayServices = limit
    ? interactiveServices.slice(0, limit)
    : interactiveServices;

  return (
    <section id="services" className="py-24 bg-primary-light">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {t("home.services.tag")}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {t("home.services.title")}
          </p>
        </div>

        {/* Services Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
          {displayServices.map((service, index) => {
            const Icon = service.icon;
            const colors = colorConfig[service.color] || colorConfig.cyan;

            return (
              <div
                key={index}
                className={`group bg-white border ${colors.border} p-8 rounded-3xl shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 flex flex-col relative overflow-hidden text-start`}
              >
                {/* Icon */}
                <div
                  className={`w-14 h-14 rounded-2xl flex items-center justify-center mb-6 transition-all duration-300 ${colors.icon}`}
                >
                  <Icon className="w-6 h-6" />
                </div>

                {/* Content */}
                <h3 className="text-xl font-bold text-navy mb-4">
                  {service.name}
                </h3>
                <p className="text-gray-500 mb-8 leading-relaxed text-sm">
                  {service.description}
                </p>

                {/* External Link */}
                <div className="mt-auto">
                  <a
                    href={service.link}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-1.5 text-sm font-bold text-primary cursor-pointer"
                  >
                    {service.actionText}
                    <span
                      className={`transition-transform duration-300 ${language === "ar" ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                    >
                      →
                    </span>
                  </a>
                </div>
              </div>
            );
          })}
        </div>

        {/* View All Services Button */}
        {limit && (
          <div className="text-center mt-16">
            <Link
              to="/services"
              className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-8 py-3.5 rounded-xl font-extrabold text-sm transition-all shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5"
            >
              {t("home.services.viewAll")}
              <ArrowRight
                className={`w-4 h-4 transition-transform ${language === "ar" ? "rotate-180" : ""}`}
              />
            </Link>
          </div>
        )}
      </div>
    </section>
  );
}
