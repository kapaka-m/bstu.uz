import React, { useState, useEffect } from "react";
import { apanelService } from "../../../services/apanelService";
import {
  Users,
  ClipboardList,
  BookOpen,
  Building2,
  Newspaper,
  Image,
  FileCheck,
  MessageSquare,
  Loader2,
  History,
  ArrowRight,
  Activity,
  MailPlus,
  Video,
  Leaf,
  Megaphone,
  UsersRound,
  MousePointerClick,
} from "lucide-react";
import { Link } from "react-router-dom";
import { useLanguage } from "../../../context/LanguageContext";

export default function ApanelDashboard() {
  const { t } = useLanguage();
  const [stats, setStats] = useState({
    users: 0,
    applications: 0,
    programs: 0,
    faculties: 0,
    departments: 0,
    newsEvents: 0,
    announcements: 0,
    blogs: 0,
    videos: 0,
    interactiveServices: 0,
    newsletterSubscriptions: 0,
    greenCampusArticles: 0,
    greenCampusStats: 0,
    administrationProfiles: 0,
    contactPage: 0,
    media: 0,
    pendingDocuments: 0,
    supportTickets: 0,
    inquiries: 0,
  });
  const [recentLogs, setRecentLogs] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        setLoading(true);
        const res = await apanelService.dashboard();
        setStats(res.stats);
        setRecentLogs(res.recentLogs);
      } catch (err) {
        console.error("Failed to load dashboard stats", err);
      } finally {
        setLoading(false);
      }
    };
    fetchStats();
  }, []);

  const cardConfig = [
    {
      label: t("apanel.dashboard.totalUsers"),
      val: stats.users,
      icon: Users,
      color: "text-blue-600 bg-blue-50 border-blue-100",
      path: "/apanel/users",
    },
    {
      label: t("apanel.dashboard.programApplications"),
      val: stats.applications,
      icon: ClipboardList,
      color: "text-emerald-600 bg-emerald-50 border-emerald-100",
      path: "/apanel/applications",
    },
    {
      label: t("apanel.dashboard.studyPrograms"),
      val: stats.programs,
      icon: BookOpen,
      color: "text-purple-600 bg-purple-50 border-purple-100",
      path: "/apanel/programs",
    },
    {
      label: t("apanel.dashboard.faculties"),
      val: stats.faculties,
      icon: Building2,
      color: "text-cyan-600 bg-cyan-50 border-cyan-100",
      path: "/apanel/faculties",
    },
    {
      label: t("apanel.dashboard.departments"),
      val: stats.departments,
      icon: Building2,
      color: "text-indigo-600 bg-indigo-50 border-indigo-100",
      path: "/apanel/departments",
    },
    {
      label: t("apanel.dashboard.newsEvents"),
      val: stats.newsEvents,
      icon: Newspaper,
      color: "text-rose-600 bg-rose-50 border-rose-100",
      path: "/apanel/cms/news-events",
    },
    {
      label: t("apanel.dashboard.announcements"),
      val: stats.announcements,
      icon: Megaphone,
      color: "text-amber-600 bg-amber-50 border-amber-100",
      path: "/apanel/cms/announcements",
    },
    {
      label: t("apanel.dashboard.blog"),
      val: stats.blogs,
      icon: BookOpen,
      color: "text-violet-600 bg-violet-50 border-violet-100",
      path: "/apanel/cms/blog",
    },
    {
      label: t("apanel.dashboard.videoGallery"),
      val: stats.videos,
      icon: Video,
      color: "text-red-600 bg-red-50 border-red-100",
      path: "/apanel/cms/video-bdtu",
    },
    {
      label: t("apanel.dashboard.interactiveServices"),
      val: stats.interactiveServices,
      icon: MousePointerClick,
      color: "text-cyan-700 bg-cyan-50 border-cyan-100",
      path: "/apanel/cms/interactive-services",
    },
    {
      label: t("apanel.dashboard.greenCampus"),
      val: stats.greenCampusArticles,
      icon: Leaf,
      color: "text-emerald-700 bg-emerald-50 border-emerald-100",
      path: "/apanel/cms/green-campus",
    },
    {
      label: t("apanel.dashboard.administration"),
      val: stats.administrationProfiles,
      icon: UsersRound,
      color: "text-slate-700 bg-slate-50 border-slate-100",
      path: "/apanel/cms/administration",
    },
    {
      label: t("apanel.dashboard.contactPage"),
      val: stats.contactPage,
      icon: MessageSquare,
      color: "text-blue-700 bg-blue-50 border-blue-100",
      path: "/apanel/cms/contact-page",
    },
    {
      label: t("apanel.dashboard.newsletterSubscriptions"),
      val: stats.newsletterSubscriptions,
      icon: MailPlus,
      color: "text-fuchsia-600 bg-fuchsia-50 border-fuchsia-100",
      path: "/apanel/newsletter/subscriptions",
    },
    {
      label: t("apanel.dashboard.mediaAssets"),
      val: stats.media,
      icon: Image,
      color: "text-sky-600 bg-sky-50 border-sky-100",
      path: "/apanel/media",
    },
    {
      label: t("apanel.dashboard.pendingDocuments"),
      val: stats.pendingDocuments,
      icon: FileCheck,
      color: "text-orange-600 bg-orange-50 border-orange-100",
      path: "/apanel/application-documents",
    },
    {
      label: t("apanel.dashboard.supportTickets"),
      val: stats.supportTickets,
      icon: MessageSquare,
      color: "text-teal-600 bg-teal-50 border-teal-100",
      path: "/apanel/support-tickets",
    },
    {
      label: t("apanel.dashboard.contactMessages"),
      val: stats.inquiries,
      icon: MessageSquare,
      color: "text-amber-600 bg-amber-50 border-amber-100",
      path: "/apanel/management/contact",
    },
  ];
  const publicContentTotal =
    stats.newsEvents +
    stats.announcements +
    stats.blogs +
    stats.videos +
    stats.greenCampusArticles +
    stats.interactiveServices +
    stats.administrationProfiles;
  const operationsTotal =
    stats.applications + stats.pendingDocuments + stats.supportTickets + stats.inquiries;
  const structureTotal = stats.faculties + stats.departments + stats.programs;

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-100">
        <Loader2 className="w-8 h-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-in fade-in duration-200">
      {/* Page Title */}
      <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <div className="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
          <div>
            <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
              {t("apanel.dashboard.title")}
            </h1>
            <p className="text-gray-400 text-xs font-semibold mt-1">
              {t("apanel.dashboard.subtitle")}
            </p>
          </div>
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-3 xl:min-w-150">
            {[
              ["Public CMS", publicContentTotal],
              ["Student Ops", operationsTotal],
              ["Academic Structure", structureTotal],
            ].map(([label, value]) => (
              <div key={label} className="rounded-2xl border border-gray-100 bg-gray-50/70 px-4 py-3">
                <p className="text-[10px] font-black uppercase tracking-widest text-gray-400">{label}</p>
                <p className="mt-1 text-2xl font-black leading-none text-navy">{value}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Grid Cards */}
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {cardConfig.map((card) => {
          const Icon = card.icon;
          return (
            <Link
              key={card.label}
              to={card.path}
              className="group flex min-h-35 flex-col justify-between rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-primary/20 hover:shadow-md"
            >
              <div className="flex items-start justify-between gap-4">
                <div className="min-w-0 space-y-2">
                  <span className="block truncate text-[10px] font-black uppercase tracking-widest text-gray-400">
                    {card.label}
                  </span>
                  <span className="block text-3xl font-black leading-none text-navy">
                    {card.val}
                  </span>
                </div>
                <div
                  className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border ${card.color} transition-all group-hover:scale-105`}
                >
                  <Icon className="h-5 w-5" />
                </div>
              </div>
              <div className="mt-5 flex items-center justify-between border-t border-gray-50 pt-3 text-[11px] font-extrabold uppercase tracking-widest text-gray-400">
                <span>Open</span>
                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5 group-hover:text-primary" />
              </div>
            </Link>
          );
        })}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left: Recent Activity Audit Logs */}
        <div className="lg:col-span-2 bg-white border border-gray-100 rounded-3xl p-6 shadow-xs flex flex-col justify-between">
          <div className="space-y-4">
            <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
              <History className="w-5 h-5 text-navy" />
              <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
                {t("apanel.dashboard.auditLogs")}
              </h3>
            </div>

            <div className="divide-y divide-gray-50 overflow-y-auto max-h-87.5 pr-2 scrollbar-thin">
              {recentLogs.length === 0 ? (
                <p className="text-xs font-semibold text-gray-400 py-6 text-center">
                  {t("apanel.dashboard.noRecentActions")}
                </p>
              ) : (
                recentLogs.map((log) => (
                  <div
                    key={log.id}
                    className="py-3 flex justify-between items-start gap-4 text-xs font-semibold"
                  >
                    <div className="space-y-1">
                      <div className="flex items-center gap-1.5 flex-wrap">
                        <span className="font-extrabold text-navy capitalize">
                          {log.action}
                        </span>
                        <span className="text-gray-300">|</span>
                        <span className="text-gray-400 font-bold uppercase text-[9px] tracking-wider truncate max-w-37.5">
                          {log.model_type?.split("\\").pop()}
                        </span>
                        <span className="text-gray-300">|</span>
                        <span className="text-[10px] text-gray-400">
                          {t("apanel.dashboard.recordId")} {log.model_id}
                        </span>
                      </div>
                      <p className="text-[10px] text-gray-400 font-semibold">
                        {log.ip_address} ({log.user_agent?.slice(0, 50)}...)
                      </p>
                    </div>
                    <span className="text-[10px] text-gray-400 font-bold shrink-0">
                      {new Date(log.created_at).toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                      })}
                    </span>
                  </div>
                ))
              )}
            </div>
          </div>

          <Link
            to="/apanel/audit-logs"
            className="flex items-center justify-center gap-1.5 text-xs font-extrabold text-primary hover:text-primary-hover pt-4 border-t border-gray-50 mt-4"
          >
            {t("apanel.dashboard.viewAllAuditLogs")}
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>

        {/* Right: Quick Links */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
            <Activity className="w-5 h-5 text-navy" />
            <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
              {t("apanel.dashboard.quickActions")}
            </h3>
          </div>

          <div className="grid grid-cols-1 gap-2.5">
            <Link
              to="/apanel/translations"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.manageTranslations")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/applications"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.reviewApplications")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/cms/news-events"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.manageNewsEvents")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/cms/blog"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.manageBlog")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/cms/interactive-services"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.manageInteractiveServices")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/cms/contact-page"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.manageContactPage")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/management/contact"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.reviewContactMessages")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/media"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.uploadMediaAssets")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/settings"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>{t("apanel.dashboard.configureSettings")}</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
