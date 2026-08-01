import React, { useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { useLanguage } from "../../../context/LanguageContext";
import { useAuth } from "../../../context/AuthContext";
import {
  LayoutDashboard,
  Settings,
  Globe,
  Languages,
  History,
  Menu as MenuIcon,
  FileText,
  Newspaper,
  Megaphone,
  Briefcase,
  Video,
  GraduationCap,
  Building2,
  BookOpen,
  User,
  Contact,
  ClipboardList,
  FileCheck,
  CreditCard,
  HelpCircle,
  MessageSquare,
  MessageCircle,
  Bell,
  Image,
  Leaf,
  Shield,
  UsersRound,
  PanelBottom,
  Mail,
  MailPlus,
  X,
  ChevronDown,
  LogOut,
} from "lucide-react";

export default function ApanelLayout({ children }) {
  const {
    t,
    language,
    changeLanguage,
    isRtl,
    locales = [],
    settings = {},
    logoSrc,
  } = useLanguage();
  const { user, logout } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();

  const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);
  const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);

  const handleLogout = async () => {
    try {
      await logout();
      navigate("/apanel/login");
    } catch (err) {
      console.error("Logout failed", err);
    }
  };

  // Nav categories & links configuration
  const menuCategories = [
    {
      title: t("apanel.nav.coreAdmin"),
      links: [
        {
          path: "/apanel/dashboard",
          label: t("apanel.nav.dashboard"),
          icon: LayoutDashboard,
        },
        { path: "/apanel/settings", label: t("apanel.nav.settings"), icon: Settings },
        {
          path: "/apanel/translations",
          label: t("apanel.nav.translationDict"),
          icon: Languages,
        },
        { path: "/apanel/audit-logs", label: t("apanel.nav.auditLogs"), icon: History },
      ],
    },
    {
      title: t("apanel.nav.cms"),
      links: [
        {
          path: "/apanel/cms/about-page",
          label: t("apanel.nav.aboutPage"),
          icon: FileText,
        },
        {
          path: "/apanel/cms/contact-page",
          label: t("apanel.nav.contactPage"),
          icon: Mail,
        },
        {
          path: "/apanel/cms/header-navbar",
          label: t("apanel.nav.headerNavbar"),
          icon: MenuIcon,
        },
        {
          path: "/apanel/cms/footer-web",
          label: t("apanel.nav.footerWeb"),
          icon: PanelBottom,
        },
        {
          path: "/apanel/cms/locales",
          label: t("apanel.nav.locales"),
          icon: Globe,
        },
        {
          path: "/apanel/cms/news-events",
          label: t("apanel.nav.newsEvents"),
          icon: Newspaper,
        },
        {
          path: "/apanel/cms/announcements",
          label: t("apanel.nav.announcements"),
          icon: Megaphone,
        },
        {
          path: "/apanel/cms/blog",
          label: t("apanel.nav.blog"),
          icon: BookOpen,
        },
        {
          path: "/apanel/cms/video-bdtu",
          label: t("apanel.nav.videoGallery"),
          icon: Video,
        },
        {
          path: "/apanel/cms/green-campus",
          label: t("apanel.nav.greenCampus"),
          icon: Leaf,
        },
        {
          path: "/apanel/cms/administration",
          label: t("apanel.nav.administration"),
          icon: UsersRound,
        },
        {
          path: "/apanel/centres-and-departments",
          label: t("apanel.nav.centresDepartments"),
          icon: Building2,
        },
        {
          path: "/apanel/cms/interactive-services",
          label: t("apanel.nav.interactiveServices"),
          icon: Briefcase,
        },
      ],
    },
    {
      title: t("apanel.nav.marketing"),
      links: [
        {
          path: "/apanel/newsletter/subscriptions",
          label: t("apanel.nav.newsletterSubscriptions"),
          icon: MailPlus,
        },
        {
          path: "/apanel/management/contact",
          label: t("apanel.nav.contactMessages"),
          icon: HelpCircle,
        },
      ],
    },
    {
      title: t("apanel.nav.academicHub"),
      links: [
        { path: "/apanel/faculties", label: t("apanel.nav.faculties"), icon: GraduationCap },
        { path: "/apanel/departments", label: t("apanel.nav.departments"), icon: Building2 },
        { path: "/apanel/programs", label: t("apanel.nav.studyPrograms"), icon: BookOpen },
        { path: "/apanel/courses", label: t("apanel.nav.courses"), icon: BookOpen },
        { path: "/apanel/staff", label: t("apanel.nav.staffProfiles"), icon: Contact },
      ],
    },
    {
      title: t("apanel.nav.admissionsStudents"),
      links: [
        { path: "/apanel/students", label: t("apanel.nav.students"), icon: User },
      ],
    },
    {
      title: t("apanel.nav.applications"),
      links: [
        {
          path: "/apanel/applications",
          label: t("apanel.nav.allApplications"),
          icon: ClipboardList,
        },
        {
          path: "/apanel/applications?stage=documents",
          label: t("apanel.nav.documentsReview"),
          icon: FileCheck,
        },
        {
          path: "/apanel/applications?stage=equivalency",
          label: t("apanel.nav.academicReview"),
          icon: GraduationCap,
        },
        {
          path: "/apanel/applications?stage=payments",
          label: t("apanel.nav.paymentsReview"),
          icon: CreditCard,
        },
        {
          path: "/apanel/applications?stage=final-review",
          label: t("apanel.nav.finalReview"),
          icon: FileCheck,
        },
        {
          path: "/apanel/applications?stage=admissions",
          label: t("apanel.nav.admissions"),
          icon: Shield,
        },
        {
          path: "/apanel/countries",
          label: t("apanel.nav.countries"),
          icon: Globe,
        },
        {
          path: "/apanel/nationalities",
          label: t("apanel.nav.nationalities"),
          icon: Globe,
        },
      ],
    },
    {
      title: t("apanel.nav.studentServices"),
      links: [
        { path: "/apanel/application-documents", label: t("apanel.nav.legacyDocuments"), icon: FileCheck },
        { path: "/apanel/payments", label: t("apanel.nav.legacyPayments"), icon: CreditCard },
        {
          path: "/apanel/support-tickets",
          label: t("apanel.nav.supportTickets"),
          icon: MessageSquare,
        },
        { path: "/apanel/comments", label: t("apanel.nav.comments"), icon: MessageCircle },
        { path: "/apanel/notifications", label: t("apanel.nav.notifications"), icon: Bell },
        {
          path: "/apanel/application-status-histories",
          label: t("apanel.nav.statusHistory"),
          icon: History,
        },
      ],
    },
    {
      title: t("apanel.nav.accessControl"),
      links: [
        { path: "/apanel/users", label: t("apanel.nav.users"), icon: User },
        { path: "/apanel/roles", label: t("apanel.nav.roles"), icon: Shield },
        { path: "/apanel/permissions", label: t("apanel.nav.permissions"), icon: Shield },
      ],
    },
    {
      title: t("apanel.nav.assetsManager"),
      links: [{ path: "/apanel/media", label: t("apanel.nav.mediaLibrary"), icon: Image }],
    },
  ];

  // Helper to determine active link
  const isActive = (path) => {
    if (path === "/apanel/dashboard" && location.pathname === "/apanel")
      return true;
    return location.pathname === path;
  };

  // Get current section label for breadcrumbs
  const getBreadcrumbLabel = () => {
    const activeLink = menuCategories
      .flatMap((cat) => cat.links)
      .find((link) => isActive(link.path));
    return activeLink ? activeLink.label : t("apanel.nav.dashboard");
  };

  const renderSidebarContent = () => (
    <div className="flex flex-col h-full bg-navy border-r border-navy-dark text-white select-none">
      {/* Brand Logo header */}
      <div className="flex items-center justify-between px-6 py-5 border-b border-navy-dark">
        <Link to="/apanel" className="flex items-center gap-2.5">
          {logoSrc ? (
            <img
              src={logoSrc}
              alt={settings.site_name || t("app.name")}
              className="w-8 h-8 rounded-xl object-contain bg-white"
            />
          ) : (
            <div className="w-8 h-8 rounded-xl bg-primary flex items-center justify-center font-black text-white text-base">
              {t("apanel.initials")}
            </div>
          )}
          <div className="flex flex-col">
            <span className="font-extrabold text-xs tracking-wider uppercase leading-none">
              {settings.site_name || t("app.name")}
            </span>
            <span className="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
              {t("apanel.controlPanel")}
            </span>
          </div>
        </Link>
        {/* Mobile close button */}
        <button
          onClick={() => setMobileSidebarOpen(false)}
          className="md:hidden text-gray-400 hover:text-white cursor-pointer"
        >
          <X className="w-5 h-5" />
        </button>
      </div>

      {/* Nav List */}
      <div className="grow overflow-y-auto px-4 py-6 space-y-7 scrollbar-thin scrollbar-thumb-navy-dark scrollbar-track-transparent">
        {menuCategories.map((category) => (
          <div key={category.title} className="space-y-2">
            <span className="text-[9px] uppercase font-black text-navy-light/70 tracking-widest px-3 block">
              {category.title}
            </span>
            <div className="space-y-0.5">
              {category.links.map((link) => {
                const Icon = link.icon;
                const active = isActive(link.path);
                return (
                  <Link
                    key={link.path}
                    to={link.path}
                    onClick={() => setMobileSidebarOpen(false)}
                    className={`flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-all ${
                      active
                        ? "bg-primary text-white shadow-md shadow-primary/20"
                        : "text-gray-400 hover:text-white hover:bg-navy-dark/40"
                    }`}
                  >
                    <Icon className="w-4 h-4 shrink-0" />
                    <span>{link.label}</span>
                  </Link>
                );
              })}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-gray-50 flex" dir={isRtl ? "rtl" : "ltr"}>
      {/* 1. Desktop Sidebar */}
      <aside className="hidden md:block w-64 shrink-0 fixed inset-y-0 left-0 z-20">
        {renderSidebarContent()}
      </aside>

      {/* 2. Mobile Sidebar Overlay drawer */}
      {mobileSidebarOpen && (
        <div className="fixed inset-0 z-50 flex md:hidden">
          <div
            className="fixed inset-0 bg-navy/40 backdrop-blur-xs"
            onClick={() => setMobileSidebarOpen(false)}
          />
          <aside className="relative w-64 h-full animate-in slide-in-from-left duration-200">
            {renderSidebarContent()}
          </aside>
        </div>
      )}

      {/* 3. Main Workspace Container */}
      <div className="grow min-w-0 flex flex-col md:pl-64 min-h-screen overflow-x-hidden">
        {/* Top Navbar */}
        <header className="sticky top-0 bg-white/80 backdrop-blur-md border-b border-gray-100 z-30 px-6 py-4 flex min-w-0 items-center justify-between">
          <div className="flex items-center gap-3">
            {/* Hamburger for mobile */}
            <button
              onClick={() => setMobileSidebarOpen(true)}
              className="md:hidden text-navy hover:text-primary p-1.5 rounded-lg border border-gray-200 cursor-pointer bg-white"
            >
              <MenuIcon className="w-5 h-5" />
            </button>

            {/* Breadcrumbs */}
            <div className="hidden sm:flex items-center gap-2 text-xs font-semibold text-gray-400 select-none">
              <span>{t("apanel.breadcrumbRoot")}</span>
              <span>/</span>
              <span className="text-navy font-bold">
                {getBreadcrumbLabel()}
              </span>
            </div>
          </div>

          {/* Right actions: Language Switcher, User Dropdown */}
          <div className="flex items-center gap-4">
            {/* Language switcher */}
            <div className="flex items-center gap-1 border border-gray-250 rounded-xl px-2.5 py-1.5 bg-white shadow-2xs">
              <Globe className="w-3.5 h-3.5 text-gray-400 shrink-0" />
              <select
                value={language}
                onChange={(e) => changeLanguage(e.target.value)}
                className="text-xs font-bold text-navy outline-none bg-transparent cursor-pointer"
              >
                {locales
                  .filter((item) => item?.is_active !== false)
                  .map((item) => (
                    <option key={item.code} value={item.code}>
                      {item.native_name || item.name || item.code.toUpperCase()}
                    </option>
                  ))}
              </select>
            </div>

            {/* User Dropdown */}
            <div className="relative">
              <button
                onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
                className="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-gray-100 hover:border-gray-200 cursor-pointer bg-white transition-all shadow-2xs"
              >
                <div className="w-6 h-6 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs select-none uppercase">
                  {user?.name?.slice(0, 2) || t("apanel.initials")}
                </div>
                <span className="hidden sm:inline text-xs font-bold text-navy select-none">
                  {user?.name || t("apanel.user")}
                </span>
                <ChevronDown className="w-3.5 h-3.5 text-gray-400" />
              </button>

              {profileDropdownOpen && (
                <>
                  <div
                    className="fixed inset-0 z-40"
                    onClick={() => setProfileDropdownOpen(false)}
                  />
                  <div className="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 p-2 py-1.5 animate-in fade-in slide-in-from-top-2 duration-150">
                    <div className="px-3.5 py-2 border-b border-gray-50 select-none">
                      <p className="text-xs font-extrabold text-navy truncate">
                        {user?.name}
                      </p>
                      <p className="text-[10px] text-gray-400 font-semibold truncate mt-0.5">
                        {user?.email}
                      </p>
                    </div>

                    <button
                      onClick={handleLogout}
                      className="w-full text-start flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 cursor-pointer transition-all mt-1"
                    >
                      <LogOut className="w-4 h-4 shrink-0" />
                      {t("auth.logout")}
                    </button>
                  </div>
                </>
              )}
            </div>
          </div>
        </header>

        {/* Content body wrapper */}
        <main className="grow min-w-0 max-w-full overflow-x-hidden p-6 md:p-8 space-y-6">{children}</main>
      </div>
    </div>
  );
}
