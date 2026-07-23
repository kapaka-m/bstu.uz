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

export default function ApanelDashboard() {
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
      label: "Total Users",
      val: stats.users,
      icon: Users,
      color: "text-blue-600 bg-blue-50 border-blue-100",
      path: "/apanel/users",
    },
    {
      label: "Program Applications",
      val: stats.applications,
      icon: ClipboardList,
      color: "text-emerald-600 bg-emerald-50 border-emerald-100",
      path: "/apanel/applications",
    },
    {
      label: "Study Programs",
      val: stats.programs,
      icon: BookOpen,
      color: "text-purple-600 bg-purple-50 border-purple-100",
      path: "/apanel/programs",
    },
    {
      label: "Faculties",
      val: stats.faculties,
      icon: Building2,
      color: "text-cyan-600 bg-cyan-50 border-cyan-100",
      path: "/apanel/faculties",
    },
    {
      label: "Departments",
      val: stats.departments,
      icon: Building2,
      color: "text-indigo-600 bg-indigo-50 border-indigo-100",
      path: "/apanel/departments",
    },
    {
      label: "News & Events",
      val: stats.newsEvents,
      icon: Newspaper,
      color: "text-rose-600 bg-rose-50 border-rose-100",
      path: "/apanel/cms/news-events",
    },
    {
      label: "Announcements",
      val: stats.announcements,
      icon: Megaphone,
      color: "text-amber-600 bg-amber-50 border-amber-100",
      path: "/apanel/cms/announcements",
    },
    {
      label: "Blog",
      val: stats.blogs,
      icon: BookOpen,
      color: "text-violet-600 bg-violet-50 border-violet-100",
      path: "/apanel/cms/blog",
    },
    {
      label: "Video Gallery",
      val: stats.videos,
      icon: Video,
      color: "text-red-600 bg-red-50 border-red-100",
      path: "/apanel/cms/video-bdtu",
    },
    {
      label: "Interactive Services",
      val: stats.interactiveServices,
      icon: MousePointerClick,
      color: "text-cyan-700 bg-cyan-50 border-cyan-100",
      path: "/apanel/cms/interactive-services",
    },
    {
      label: "Green Campus",
      val: stats.greenCampusArticles,
      icon: Leaf,
      color: "text-emerald-700 bg-emerald-50 border-emerald-100",
      path: "/apanel/cms/green-campus",
    },
    {
      label: "Administration",
      val: stats.administrationProfiles,
      icon: UsersRound,
      color: "text-slate-700 bg-slate-50 border-slate-100",
      path: "/apanel/cms/administration",
    },
    {
      label: "Contact Page",
      val: stats.contactPage,
      icon: MessageSquare,
      color: "text-blue-700 bg-blue-50 border-blue-100",
      path: "/apanel/cms/contact-page",
    },
    {
      label: "Newsletter Subscriptions",
      val: stats.newsletterSubscriptions,
      icon: MailPlus,
      color: "text-fuchsia-600 bg-fuchsia-50 border-fuchsia-100",
      path: "/apanel/newsletter/subscriptions",
    },
    {
      label: "Media Assets",
      val: stats.media,
      icon: Image,
      color: "text-sky-600 bg-sky-50 border-sky-100",
      path: "/apanel/media",
    },
    {
      label: "Pending Documents",
      val: stats.pendingDocuments,
      icon: FileCheck,
      color: "text-orange-600 bg-orange-50 border-orange-100",
      path: "/apanel/application-documents",
    },
    {
      label: "Support Tickets",
      val: stats.supportTickets,
      icon: MessageSquare,
      color: "text-teal-600 bg-teal-50 border-teal-100",
      path: "/apanel/support-tickets",
    },
    {
      label: "Contact Messages",
      val: stats.inquiries,
      icon: MessageSquare,
      color: "text-amber-600 bg-amber-50 border-amber-100",
      path: "/apanel/management/contact",
    },
  ];

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
      <div>
        <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
          Dashboard Overview
        </h1>
        <p className="text-gray-400 text-xs font-semibold mt-1">
          International Admissions & Content Management Stats
        </p>
      </div>

      {/* Grid Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        {cardConfig.map((card) => {
          const Icon = card.icon;
          return (
            <Link
              key={card.label}
              to={card.path}
              className="bg-white border border-gray-100 p-6 rounded-3xl hover:shadow-md transition-all flex items-center justify-between group cursor-pointer"
            >
              <div className="space-y-2">
                <span className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">
                  {card.label}
                </span>
                <span className="text-3xl font-black text-navy block leading-none">
                  {card.val}
                </span>
              </div>
              <div
                className={`w-12 h-12 rounded-2xl flex items-center justify-center border ${card.color} group-hover:scale-105 transition-all`}
              >
                <Icon className="w-5 h-5" />
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
                System Audit Logs
              </h3>
            </div>

            <div className="divide-y divide-gray-50 overflow-y-auto max-h-87.5 pr-2 scrollbar-thin">
              {recentLogs.length === 0 ? (
                <p className="text-xs font-semibold text-gray-400 py-6 text-center">
                  No recent actions recorded.
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
                          ID: {log.model_id}
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
            View all audit logs
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>

        {/* Right: Quick Links */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
            <Activity className="w-5 h-5 text-navy" />
            <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
              Quick Actions
            </h3>
          </div>

          <div className="grid grid-cols-1 gap-2.5">
            <Link
              to="/apanel/translations"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Manage Translation Dictionary</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/applications"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Review Admissions Applications</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/cms/news-events"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Manage News & Events CMS</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/cms/blog"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Manage Blog CMS</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/cms/interactive-services"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Manage Interactive Services</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/cms/contact-page"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Manage Contact Page</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/management/contact"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Review Contact Messages</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/media"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Upload Media Assets</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
            <Link
              to="/apanel/settings"
              className="p-3 bg-gray-50/50 hover:bg-primary-light border border-gray-100 hover:border-primary-hover rounded-2xl flex items-center justify-between text-xs font-bold text-navy transition-all group"
            >
              <span>Configure Global Settings</span>
              <ArrowRight className="w-4 h-4 text-gray-400 group-hover:text-primary transition-all" />
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
