import React, { useEffect, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { useLanguage } from "../../../context/LanguageContext";
import { hasApanelPermission, useAuth } from "../../../context/AuthContext";
import { apanelService } from "../../../services/apanelService";
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
  KeyRound,
  MailPlus,
  Home,
  X,
  ChevronDown,
  LogOut,
  Clock,
  Check,
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
  const [languageDropdownOpen, setLanguageDropdownOpen] = useState(false);
  const [notificationsDropdownOpen, setNotificationsDropdownOpen] = useState(false);
  const [headerNotifications, setHeaderNotifications] = useState([]);
  const [notificationsLoading, setNotificationsLoading] = useState(false);

  const handleLogout = async () => {
    try {
      await logout();
      navigate("/apanel/login");
    } catch (err) {
      console.error("Logout failed", err);
    }
  };

  useEffect(() => {
    if (!notificationsDropdownOpen || headerNotifications.length > 0) return;

    let mounted = true;
    setNotificationsLoading(true);
    apanelService
      .listPage("notifications", { per_page: 5 })
      .then((page) => {
        if (mounted) setHeaderNotifications(page.items || []);
      })
      .catch(() => {
        if (mounted) setHeaderNotifications([]);
      })
      .finally(() => {
        if (mounted) setNotificationsLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, [headerNotifications.length, notificationsDropdownOpen]);

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
        {
          path: "/apanel/settings",
          label: t("apanel.nav.settings"),
          icon: Settings,
          permission: "manage-settings",
        },
        {
          path: "/apanel/translations",
          label: t("apanel.nav.translationDict"),
          icon: Languages,
          permission: "manage-translations",
        },
        {
          path: "/apanel/audit-logs",
          label: t("apanel.nav.auditLogs"),
          icon: History,
          permission: "view-audit-logs",
        },
      ],
    },
    {
      title: t("apanel.nav.cms"),
      links: [
        {
          path: "/apanel/cms/home",
          label: "Home Page",
          icon: Home,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/about-page",
          label: t("apanel.nav.aboutPage"),
          icon: FileText,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/faculty-page",
          label: "Faculty Page",
          icon: GraduationCap,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/department-page",
          label: "Department Page",
          icon: Building2,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/programs",
          label: "Program Pages",
          icon: GraduationCap,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/apply-page",
          label: "Apply Page",
          icon: ClipboardList,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/contact-page",
          label: t("apanel.nav.contactPage"),
          icon: Mail,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/cms-auth",
          label: "CMS Auth",
          icon: KeyRound,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/header-navbar",
          label: t("apanel.nav.headerNavbar"),
          icon: MenuIcon,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/footer-web",
          label: t("apanel.nav.footerWeb"),
          icon: PanelBottom,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/locales",
          label: t("apanel.nav.locales"),
          icon: Globe,
          permission: "manage-locales",
        },
        {
          path: "/apanel/cms/news-events",
          label: t("apanel.nav.newsEvents"),
          icon: Newspaper,
          permission: "manage-news",
        },
        {
          path: "/apanel/cms/announcements",
          label: t("apanel.nav.announcements"),
          icon: Megaphone,
          permission: "manage-announcements",
        },
        {
          path: "/apanel/cms/blog",
          label: t("apanel.nav.blog"),
          icon: BookOpen,
          permission: "manage-news",
        },
        {
          path: "/apanel/cms/content-publishers",
          label: "Content Publishers",
          icon: Building2,
          permission: "manage-news",
        },
        {
          path: "/apanel/cms/video-bdtu",
          label: t("apanel.nav.videoGallery"),
          icon: Video,
          permission: "manage-news",
        },
        {
          path: "/apanel/cms/green-campus",
          label: t("apanel.nav.greenCampus"),
          icon: Leaf,
          permission: "manage-pages",
        },
        {
          path: "/apanel/cms/administration",
          label: t("apanel.nav.administration"),
          icon: UsersRound,
          permission: "manage-pages",
        },
        {
          path: "/apanel/centres-and-departments",
          label: t("apanel.nav.centresDepartments"),
          icon: Building2,
          permission: "manage-services",
        },
        {
          path: "/apanel/cms/interactive-services",
          label: t("apanel.nav.interactiveServices"),
          icon: Briefcase,
          permission: "manage-services",
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
          permission: "manage-inquiries",
        },
        {
          path: "/apanel/management/contact",
          label: t("apanel.nav.contactMessages"),
          icon: HelpCircle,
          permission: "manage-inquiries",
        },
      ],
    },
    {
      title: t("apanel.nav.academicHub"),
      links: [
        { path: "/apanel/faculties", label: t("apanel.nav.faculties"), icon: GraduationCap, permission: "manage-faculties" },
        { path: "/apanel/departments", label: t("apanel.nav.departments"), icon: Building2, permission: "manage-departments" },
        { path: "/apanel/programs", label: t("apanel.nav.studyPrograms"), icon: BookOpen, permission: "manage-programs" },
        { path: "/apanel/courses", label: t("apanel.nav.courses"), icon: BookOpen, permission: "manage-courses" },
        { path: "/apanel/staff", label: t("apanel.nav.staffProfiles"), icon: Contact, permission: "manage-staff" },
      ],
    },
    {
      title: t("apanel.nav.admissionsStudents"),
      links: [
        { path: "/apanel/students", label: t("apanel.nav.students"), icon: User, permission: "manage-students" },
      ],
    },
    {
      title: t("apanel.nav.applications"),
      links: [
        {
          path: "/apanel/applications",
          label: t("apanel.nav.allApplications"),
          icon: ClipboardList,
          permission: "manage-applications",
        },
        {
          path: "/apanel/applications?stage=documents",
          label: t("apanel.nav.documentsReview"),
          icon: FileCheck,
          permission: "manage-applications",
        },
        {
          path: "/apanel/applications?stage=equivalency",
          label: t("apanel.nav.academicReview"),
          icon: GraduationCap,
          permission: "manage-applications",
        },
        {
          path: "/apanel/applications?stage=payments",
          label: t("apanel.nav.paymentsReview"),
          icon: CreditCard,
          permission: "manage-applications",
        },
        {
          path: "/apanel/applications?stage=final-review",
          label: t("apanel.nav.finalReview"),
          icon: FileCheck,
          permission: "manage-applications",
        },
        {
          path: "/apanel/applications?stage=admissions",
          label: t("apanel.nav.admissions"),
          icon: Shield,
          permission: "manage-applications",
        },
        {
          path: "/apanel/countries",
          label: t("apanel.nav.countries"),
          icon: Globe,
          permission: "manage-applications",
        },
        {
          path: "/apanel/nationalities",
          label: t("apanel.nav.nationalities"),
          icon: Globe,
          permission: "manage-applications",
        },
      ],
    },
    {
      title: t("apanel.nav.studentServices"),
      links: [
        { path: "/apanel/application-documents", label: t("apanel.nav.legacyDocuments"), icon: FileCheck, permission: "manage-documents|manage-applications" },
        { path: "/apanel/payments", label: t("apanel.nav.legacyPayments"), icon: CreditCard, permission: "manage-payments" },
        {
          path: "/apanel/support-tickets",
          label: t("apanel.nav.supportTickets"),
          icon: MessageSquare,
          permission: "manage-support-tickets",
        },
        { path: "/apanel/notifications", label: t("apanel.nav.notifications"), icon: Bell, permission: "manage-notifications" },
        {
          path: "/apanel/application-status-histories",
          label: t("apanel.nav.statusHistory"),
          icon: History,
          permission: "manage-applications",
        },
      ],
    },
    {
      title: t("apanel.nav.commentModeration"),
      links: [
        { path: "/apanel/blog-comments", label: t("apanel.nav.blogComments"), icon: MessageCircle, permission: "manage-comments" },
        { path: "/apanel/video-comments", label: t("apanel.nav.videoComments"), icon: MessageCircle, permission: "manage-comments" },
      ],
    },
    {
      title: t("apanel.nav.accessControl"),
      links: [
        { path: "/apanel/users", label: t("apanel.nav.users"), icon: User, permission: "manage-users" },
        { path: "/apanel/roles", label: t("apanel.nav.roles"), icon: Shield, permission: "manage-roles" },
        { path: "/apanel/permissions", label: t("apanel.nav.permissions"), icon: Shield, permission: "manage-permissions" },
      ],
    },
    {
      title: t("apanel.nav.assetsManager"),
      links: [{ path: "/apanel/media", label: "Media", icon: Image, permission: "manage-media" }],
    },
  ];

  const visibleMenuCategories = menuCategories
    .map((category) => ({
      ...category,
      links: category.links.filter((link) =>
        hasApanelPermission(user, link.permission)
      ),
    }))
    .filter((category) => category.links.length > 0);

  // Helper to determine active link
  const isActive = (path) => {
    if (path === "/apanel/dashboard" && location.pathname === "/apanel")
      return true;
    const [pathName, queryString] = path.split("?");

    if (queryString) {
      return location.pathname === pathName && location.search === `?${queryString}`;
    }

    if (path === "/apanel/applications" && location.search) {
      return false;
    }

    return location.pathname === pathName;
  };

  const activeLink = visibleMenuCategories
    .flatMap((cat) => cat.links)
    .find((link) => isActive(link.path));
  const activePageLabel = activeLink?.label || t("apanel.nav.dashboard");
  const ActivePageIcon = activeLink?.icon || LayoutDashboard;
  const userInitials = user?.name?.trim()?.slice(0, 2) || t("apanel.initials");
  const availableLocales = locales.filter((item) => item?.is_active !== false);
  const unreadNotificationsCount = headerNotifications.filter(
    (notification) => !notification?.is_read
  ).length;
  const localizedText = (value) => {
    if (!value) return "";
    const translated = t(value);
    return translated && translated !== value ? translated : value;
  };
  const formatNotificationDate = (value) => {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";
    return date.toLocaleDateString(language === "uz" ? "uz-UZ" : language, {
      month: "short",
      day: "numeric",
    });
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
        {visibleMenuCategories.map((category) => (
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
      <div className="grow min-w-0 flex flex-col md:pl-64 min-h-screen overflow-x-clip">
        {/* Top Navbar */}
        <header className="sticky top-0 z-40 border-b border-gray-200/70 bg-white/95 px-3 py-2.5 shadow-sm shadow-gray-200/40 backdrop-blur-xl sm:px-5">
          <div className="flex min-w-0 items-center justify-between gap-3">
            <div className="flex min-w-0 items-center gap-3">
              {/* Hamburger for mobile */}
              <button
                onClick={() => setMobileSidebarOpen(true)}
                aria-label={t("apanel.controlPanel")}
                className="md:hidden flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-gray-200 bg-white text-navy shadow-2xs transition-all hover:border-primary/30 hover:bg-primary/5 hover:text-primary"
              >
                <MenuIcon className="w-5 h-5" />
              </button>

              <div className="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary sm:flex">
                <ActivePageIcon className="h-5 w-5" />
              </div>

              <div className="min-w-0 select-none">
                <div className="hidden min-w-0 items-center gap-2 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 lg:flex">
                  <span className="min-w-0 truncate">
                    {settings.site_name || t("app.name")}
                  </span>
                  <span className="h-1 w-1 shrink-0 rounded-full bg-gray-300" />
                  <span className="shrink-0">{t("apanel.breadcrumbRoot")}</span>
                </div>
                <h1 className="min-w-0 truncate text-sm font-black text-navy sm:text-base">
                  {activePageLabel}
                </h1>
              </div>
            </div>

            {/* Right actions: Language, notifications, user menu */}
            <div className="flex min-w-0 shrink-0 items-center gap-1.5 rounded-2xl border border-gray-100 bg-gray-50/80 p-1 shadow-inner sm:gap-2">
              {/* Language switcher */}
              <div className="relative shrink-0">
                <button
                  type="button"
                  onClick={() => {
                    setLanguageDropdownOpen((open) => !open);
                    setNotificationsDropdownOpen(false);
                    setProfileDropdownOpen(false);
                  }}
                  aria-expanded={languageDropdownOpen}
                  className="flex h-9 cursor-pointer items-center gap-1.5 rounded-xl border border-gray-200/60 bg-white px-2.5 text-navy shadow-2xs transition-all hover:border-primary/20 hover:bg-primary/5"
                >
                  <Globe className="h-3.5 w-3.5 shrink-0 text-primary" />
                  <span className="text-xs font-black uppercase">
                    {language.toUpperCase()}
                  </span>
                  <ChevronDown
                    className={`h-3.5 w-3.5 shrink-0 text-gray-400 transition-transform ${
                      languageDropdownOpen ? "rotate-180" : ""
                    }`}
                  />
                </button>

                {languageDropdownOpen && (
                  <>
                    <div
                      className="fixed inset-0 z-40"
                      onClick={() => setLanguageDropdownOpen(false)}
                    />
                    <div className="absolute end-0 z-50 mt-2 w-44 rounded-2xl border border-gray-100 bg-white p-1.5 shadow-xl animate-in fade-in slide-in-from-top-2 duration-150">
                      {availableLocales.map((item) => {
                        const active = item.code === language;
                        return (
                          <button
                            key={item.code}
                            type="button"
                            onClick={() => {
                              changeLanguage(item.code);
                              setLanguageDropdownOpen(false);
                            }}
                            className={`flex w-full min-w-0 items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-start transition-all ${
                              active
                                ? "bg-primary/10 text-primary"
                                : "text-navy hover:bg-gray-50"
                            }`}
                          >
                            <span className="flex min-w-0 items-center gap-2">
                              <span
                                className={`flex h-6 w-8 shrink-0 items-center justify-center rounded-lg text-[10px] font-black uppercase ${
                                  active
                                    ? "bg-primary text-white"
                                    : "bg-gray-100 text-gray-500"
                                }`}
                              >
                                {item.code}
                              </span>
                              <span className="min-w-0 truncate text-xs font-black">
                                {item.native_name || item.name || item.code.toUpperCase()}
                              </span>
                            </span>
                            {active && <Check className="h-3.5 w-3.5 shrink-0" />}
                          </button>
                        );
                      })}
                    </div>
                  </>
                )}
              </div>

              {/* Notifications dropdown */}
              <div className="relative shrink-0">
                <button
                  type="button"
                  onClick={() => {
                    setNotificationsDropdownOpen((open) => !open);
                    setLanguageDropdownOpen(false);
                    setProfileDropdownOpen(false);
                  }}
                  aria-expanded={notificationsDropdownOpen}
                  aria-label={t("apanel.nav.notifications")}
                  className="relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl bg-white text-navy shadow-2xs ring-1 ring-gray-100 transition-all hover:bg-primary/5 hover:text-primary hover:ring-primary/20"
                >
                  <Bell className="h-4 w-4" />
                  {unreadNotificationsCount > 0 && (
                    <span className="absolute -end-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[9px] font-black leading-none text-white ring-2 ring-white">
                      {unreadNotificationsCount > 9 ? "9+" : unreadNotificationsCount}
                    </span>
                  )}
                </button>

                {notificationsDropdownOpen && (
                  <>
                    <div
                      className="fixed inset-0 z-40"
                      onClick={() => setNotificationsDropdownOpen(false)}
                    />
                    <div className="absolute end-0 z-50 mt-2 w-[min(22rem,calc(100vw-1.5rem))] rounded-2xl border border-gray-100 bg-white shadow-xl animate-in fade-in slide-in-from-top-2 duration-150">
                      <div className="flex items-center justify-between gap-3 border-b border-gray-50 px-4 py-3">
                        <div className="min-w-0">
                          <p className="truncate text-xs font-black text-navy">
                            {t("apanel.nav.notifications")}
                          </p>
                          <p className="mt-0.5 text-[10px] font-bold text-gray-400">
                            {unreadNotificationsCount > 0
                              ? t("interface.apanelHeaderUnreadAlerts").replace(
                                  ":count",
                                  unreadNotificationsCount
                                )
                              : t("interface.apanelHeaderLatestAlerts")}
                          </p>
                        </div>
                        <Link
                          to="/apanel/notifications"
                          onClick={() => setNotificationsDropdownOpen(false)}
                          className="shrink-0 rounded-lg bg-primary/10 px-2.5 py-1.5 text-[10px] font-black uppercase text-primary transition-all hover:bg-primary hover:text-white"
                        >
                          {t("interface.apanelHeaderViewAll")}
                        </Link>
                      </div>

                      <div className="max-h-80 overflow-y-auto p-1.5">
                        {notificationsLoading ? (
                          <div className="px-3 py-6 text-center text-xs font-bold text-gray-400">
                            {t("interface.apanelHeaderLoadingNotifications")}
                          </div>
                        ) : headerNotifications.length > 0 ? (
                          headerNotifications.map((notification) => (
                            <Link
                              key={notification.id}
                              to="/apanel/notifications"
                              onClick={() => setNotificationsDropdownOpen(false)}
                              className="group flex min-w-0 items-start gap-3 rounded-xl px-3 py-2.5 transition-all hover:bg-gray-50"
                            >
                              <span
                                className={`mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl ${
                                  notification?.is_read
                                    ? "bg-gray-100 text-gray-400"
                                    : "bg-primary/10 text-primary"
                                }`}
                              >
                                <Bell className="h-4 w-4" />
                              </span>
                              <span className="min-w-0 grow">
                                <span className="block truncate text-xs font-black text-navy group-hover:text-primary">
                                  {localizedText(notification.title) ||
                                    t("apanel.nav.notifications")}
                                </span>
                                {notification.message && (
                                  <span className="mt-1 line-clamp-2 block text-[11px] font-semibold leading-relaxed text-gray-500">
                                    {localizedText(notification.message)}
                                  </span>
                                )}
                                {notification.created_at && (
                                  <span className="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-gray-400">
                                    <Clock className="h-3 w-3" />
                                    {formatNotificationDate(notification.created_at)}
                                  </span>
                                )}
                              </span>
                            </Link>
                          ))
                        ) : (
                          <div className="px-3 py-6 text-center">
                            <div className="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-400">
                              <Bell className="h-5 w-5" />
                            </div>
                            <p className="mt-2 text-xs font-black text-navy">
                              {t("interface.apanelHeaderNoNotifications")}
                            </p>
                            <p className="mt-1 text-[11px] font-semibold text-gray-400">
                              {t("interface.apanelHeaderNotificationsEmptyHint")}
                            </p>
                          </div>
                        )}
                      </div>
                    </div>
                  </>
                )}
              </div>

              {/* User Dropdown */}
              <div className="relative shrink-0">
                <button
                  onClick={() => {
                    setProfileDropdownOpen((open) => !open);
                    setLanguageDropdownOpen(false);
                    setNotificationsDropdownOpen(false);
                  }}
                  aria-expanded={profileDropdownOpen}
                  className="flex h-9 cursor-pointer items-center gap-2 rounded-xl bg-white px-1.5 pe-2.5 text-navy shadow-2xs ring-1 ring-gray-100 transition-all hover:bg-primary/5 hover:ring-primary/20"
                >
                  <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary text-xs font-black uppercase text-white shadow-sm select-none">
                    {userInitials}
                  </div>
                  <span className="hidden max-w-40 truncate text-xs font-bold text-navy select-none lg:inline">
                    {user?.name || t("apanel.user")}
                  </span>
                  <ChevronDown
                    className={`h-3.5 w-3.5 shrink-0 text-gray-400 transition-transform ${
                      profileDropdownOpen ? "rotate-180" : ""
                    }`}
                  />
                </button>

                {profileDropdownOpen && (
                  <>
                    <div
                      className="fixed inset-0 z-40"
                      onClick={() => setProfileDropdownOpen(false)}
                    />
                    <div className="absolute end-0 z-50 mt-2 w-64 rounded-2xl border border-gray-100 bg-white p-2 py-1.5 shadow-xl animate-in fade-in slide-in-from-top-2 duration-150">
                      <div className="px-3.5 py-2 border-b border-gray-50 select-none">
                        <p className="text-xs font-extrabold text-navy truncate">
                          {user?.name || t("apanel.user")}
                        </p>
                        {user?.email && (
                          <p className="text-[10px] text-gray-400 font-semibold truncate mt-0.5">
                            {user.email}
                          </p>
                        )}
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
          </div>
        </header>

        {/* Content body wrapper */}
        <main className="grow min-w-0 max-w-full overflow-x-clip p-6 md:p-8 space-y-6">{children}</main>
      </div>
    </div>
  );
}
