import React, { useEffect, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../../../context/LanguageContext";
import { useAuth } from "../../../context/AuthContext";
import {
  LayoutDashboard,
  User,
  ClipboardList,
  FileCheck,
  Bell,
  CreditCard,
  MessageSquare,
  Menu as MenuIcon,
  Globe,
  ChevronDown,
  LogOut,
  X,
  Home,
  Clock,
  BellOff,
} from "lucide-react";
import { studentPortalService } from "../../../services/studentPortalService";
import { studentService } from "../../../services/studentService";
import { formatLocalizedDate } from "../../../utils/dateFormat";

export default function StudentLayout({ children }) {
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
  const [notificationsOpen, setNotificationsOpen] = useState(false);
  const [languageDropdownOpen, setLanguageDropdownOpen] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const [notificationsLoading, setNotificationsLoading] = useState(false);
  const [markingAllNotifications, setMarkingAllNotifications] = useState(false);
  const [isTransferStudent, setIsTransferStudent] = useState(false);

  useEffect(() => {
    studentPortalService
      .summary()
      .then((summary) => {
        setIsTransferStudent(
          String(summary?.application?.student_type || "").toLowerCase() ===
            "transfer",
        );
      })
      .catch(() => setIsTransferStudent(false));
  }, []);

  useEffect(() => {
    let active = true;

    setNotificationsLoading(true);
    studentService
      .getNotifications()
      .then((items) => {
        if (active) setNotifications(Array.isArray(items) ? items.slice(0, 5) : []);
      })
      .catch(() => {
        if (active) setNotifications([]);
      })
      .finally(() => {
        if (active) setNotificationsLoading(false);
      });

    return () => {
      active = false;
    };
  }, [location.pathname]);

  const handleLogout = async () => {
    try {
      await logout();
      navigate("/login");
    } catch (err) {
      console.error("Logout failed", err);
    }
  };

  const menuLinks = [
    { path: "/student/dashboard", label: t("student.dashboard.title"), icon: LayoutDashboard },
    { path: "/student/profile", label: t("student.profile.title"), icon: User },
    {
      path: "/student/application",
      label: t("student.nav.applicationOverview"),
      icon: ClipboardList,
    },
    {
      path: "/student/academic-information",
      label: t("student.nav.academicInformation"),
      icon: ClipboardList,
    },
    { path: "/student/documents", label: t("student.nav.requiredDocuments"), icon: FileCheck },
    ...(isTransferStudent
      ? [
          {
            path: "/student/equivalency",
            label: t("student.nav.academicEquivalency"),
            icon: FileCheck,
          },
        ]
      : []),
    { path: "/student/payments", label: t("student.nav.payments"), icon: CreditCard },
    { path: "/student/admission", label: t("student.nav.admission"), icon: FileCheck },
    { path: "/student/enrollment", label: t("student.nav.enrollment"), icon: FileCheck },
    { path: "/student/prikaz", label: t("student.nav.prikaz"), icon: FileCheck },
    { path: "/student/service-fee", label: t("student.nav.serviceFee"), icon: CreditCard },
    { path: "/student/visa", label: t("student.nav.visa"), icon: FileCheck },
    { path: "/student/housing", label: t("student.nav.housing"), icon: ClipboardList },
    { path: "/student/residence", label: t("student.nav.residence"), icon: FileCheck },
    { path: "/student/notifications", label: t("student.notifications"), icon: Bell },
    { path: "/student/support", label: t("student.nav.support"), icon: MessageSquare },
  ];

  const isActive = (path) => {
    if (path === "/student/dashboard" && location.pathname === "/student")
      return true;
    return location.pathname === path;
  };

  const getBreadcrumbLabel = () => {
    const activeLink = menuLinks.find((link) => isActive(link.path));
    return activeLink ? activeLink.label : t("student.dashboard.title");
  };

  const activeLocales = locales.filter((item) => item?.is_active !== false);
  const languages = [...activeLocales]
    .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0))
    .map((item) => ({
      code: item.code,
      label: item.native_name || item.name || item.code.toUpperCase(),
      short: item.code.toUpperCase(),
    }));
  const currentLang = languages.find((item) => item.code === language) || languages[0] || null;
  const activeMenuLink = menuLinks.find((link) => isActive(link.path));
  const HeaderIcon = activeMenuLink?.icon || LayoutDashboard;
  const unreadCount = notifications.filter((item) => !item.is_read).length;
  const dropdownAlign = isRtl ? "left-0" : "right-0";
  const portalLabel = t("student.portal") || "Student Portal";
  const supportLabel = t("student.nav.support") || "Messages";

  const closeHeaderMenus = () => {
    setProfileDropdownOpen(false);
    setNotificationsOpen(false);
    setLanguageDropdownOpen(false);
  };

  const translateMaybe = (value) => {
    if (!value || !String(value).includes(".")) return value;
    return t(value);
  };

  const handleMarkAllNotificationsRead = async () => {
    try {
      setMarkingAllNotifications(true);
      await studentService.markAllNotificationsRead();
      setNotifications((prev) => prev.map((item) => ({ ...item, is_read: true })));
    } catch (err) {
      console.error("Failed to mark all notifications as read", err);
    } finally {
      setMarkingAllNotifications(false);
    }
  };

  const formatNotificationDate = (value) =>
    formatLocalizedDate(value, language, t, {
      day: "numeric",
      month: "short",
    });

  const renderSidebar = () => (
    <div className="flex h-full min-w-0 flex-col border-navy-dark bg-navy text-white select-none md:border-e">
      {/* Brand Header */}
      <div className="flex items-center justify-between gap-3 border-b border-navy-dark px-5 py-5">
        <Link to="/student/dashboard" className="flex min-w-0 items-center gap-2.5">
          {logoSrc ? (
            <img
              src={logoSrc}
              alt={settings.site_name || t("app.name")}
              className="h-8 w-8 shrink-0 rounded-xl bg-white object-contain"
            />
          ) : (
            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary text-base font-black text-white">
              {t("student.initials")}
            </div>
          )}
          <div className="flex min-w-0 flex-col">
            <span className="truncate text-xs font-extrabold uppercase leading-none tracking-wider">
              {settings.site_name || t("app.name")}
            </span>
            <span className="mt-0.5 truncate text-[10px] font-bold uppercase tracking-widest text-gray-400">
              {t("auth.loginTitle")}
            </span>
          </div>
        </Link>
        <button
          onClick={() => setMobileSidebarOpen(false)}
          className="md:hidden text-gray-400 hover:text-white cursor-pointer"
        >
          <X className="w-5 h-5" />
        </button>
      </div>

      {/* Nav List */}
      <div className="student-sidebar-scroll grow space-y-2 overflow-y-auto px-4 py-6">
        <div className="space-y-1">
          {menuLinks.map((link) => {
            const Icon = link.icon;
            const active = isActive(link.path);
            return (
              <Link
                key={link.path}
                to={link.path}
                onClick={() => setMobileSidebarOpen(false)}
                className={`flex min-w-0 items-center gap-2.5 rounded-xl px-3 py-3.5 text-xs font-bold transition-all ${
                  active
                    ? "bg-primary text-white shadow-md shadow-primary/20"
                    : "text-gray-400 hover:text-white hover:bg-navy-dark/40"
                }`}
              >
                <Icon className="w-4.5 h-4.5 shrink-0" />
                <span className="min-w-0 break-words leading-snug">{link.label}</span>
              </Link>
            );
          })}
        </div>

        <div className="border-t border-navy-dark pt-6">
          <Link
            to="/"
            className="flex min-w-0 items-center gap-2.5 rounded-xl px-3 py-3 text-xs font-bold text-gray-400 transition-all hover:bg-navy-dark/40 hover:text-white"
          >
            <Home className="w-4.5 h-4.5 shrink-0" />
            <span className="min-w-0 break-words leading-snug">{t("student.nav.returnHome")}</span>
          </Link>
        </div>
      </div>
    </div>
  );

  return (
    <div className="flex min-h-screen overflow-x-hidden bg-gray-50" dir={isRtl ? "rtl" : "ltr"}>
      {/* Desktop Sidebar */}
      <aside className={`fixed inset-y-0 z-20 hidden w-64 shrink-0 md:block ${isRtl ? "right-0" : "left-0"}`}>
        {renderSidebar()}
      </aside>

      {/* Mobile Sidebar overlay */}
      {mobileSidebarOpen && (
        <div className={`fixed inset-0 z-50 flex md:hidden ${isRtl ? "justify-end" : "justify-start"}`}>
          <div
            className="fixed inset-0 bg-navy/40 backdrop-blur-xs"
            onClick={() => setMobileSidebarOpen(false)}
          />
          <aside
            className={`relative h-full w-72 max-w-[86vw] duration-200 animate-in ${
              isRtl ? "slide-in-from-right" : "slide-in-from-left"
            }`}
          >
            {renderSidebar()}
          </aside>
        </div>
      )}

      {/* Main Workspace */}
      <div className={`flex min-h-screen min-w-0 grow flex-col ${isRtl ? "md:pr-64" : "md:pl-64"}`}>
        <header className="sticky top-0 z-30 min-w-0 border-b border-gray-100/80 bg-white/95 px-3 py-3 shadow-sm shadow-gray-200/40 backdrop-blur-xl sm:px-6">
          <div className="flex min-w-0 items-center justify-between gap-3">
            <div className="flex min-w-0 items-center gap-3">
            <button
              onClick={() => setMobileSidebarOpen(true)}
              className="md:hidden flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white text-navy shadow-2xs transition-all hover:border-primary/30 hover:bg-primary/5 hover:text-primary"
              aria-label="Open student menu"
            >
              <MenuIcon className="w-5 h-5" />
            </button>

              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-primary/10">
                <HeaderIcon className="h-5 w-5" />
              </div>

              <div className="min-w-0 select-none">
                <div className="hidden min-w-0 items-center gap-2 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 sm:flex">
                  <span className="shrink-0">{portalLabel}</span>
                  <span className="h-1 w-1 shrink-0 rounded-full bg-gray-300" />
                  <span className="min-w-0 truncate">
                    {settings.site_name || t("app.name")}
                  </span>
                </div>
                <h1 className="min-w-0 truncate text-sm font-black text-navy sm:text-base">
                  {getBreadcrumbLabel()}
                </h1>
              </div>
            </div>

            {/* Top Bar Actions */}
            <div className="flex min-w-0 shrink-0 items-center gap-1 rounded-2xl border border-gray-100 bg-gray-50/80 p-1 shadow-inner sm:gap-1.5">
            <Link
              to="/student/support"
                className="group inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white text-navy shadow-2xs ring-1 ring-gray-100 transition-all hover:bg-primary hover:text-white hover:ring-primary"
              title={supportLabel}
                aria-label={supportLabel}
            >
                <MessageSquare className="h-4.5 w-4.5 shrink-0 transition-transform group-hover:-translate-y-0.5" />
            </Link>

            <div className="relative">
              <button
                type="button"
                onClick={() => {
                  setNotificationsOpen((open) => !open);
                  setProfileDropdownOpen(false);
                  setLanguageDropdownOpen(false);
                }}
                  className={`relative inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white shadow-2xs ring-1 transition-all ${
                    notificationsOpen
                      ? "text-primary ring-primary/25"
                      : "text-navy ring-gray-100 hover:bg-primary/5 hover:text-primary hover:ring-primary/20"
                  }`}
                aria-expanded={notificationsOpen}
                title={t("student.notifications")}
                  aria-label={t("student.notifications")}
              >
                <Bell className="h-4.5 w-4.5" />
                {unreadCount > 0 && (
                    <span className={`absolute -top-1 flex h-5 min-w-5 items-center justify-center rounded-full border-2 border-white bg-rose-500 px-1 text-[10px] font-black leading-none text-white ${isRtl ? "-left-1" : "-right-1"}`}>
                    {unreadCount > 9 ? "9+" : unreadCount}
                  </span>
                )}
              </button>

              {notificationsOpen && (
                <>
                  <div className="fixed inset-0 z-40" onClick={closeHeaderMenus} />
                  <div
                      className={`absolute ${dropdownAlign} z-50 mt-2 w-[min(18rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl ring-1 ring-black/5 animate-in fade-in slide-in-from-top-2 duration-150`}
                  >
                      <div className="px-3.5 py-3">
                        <div className="flex items-center justify-between gap-3">
                          <div className="min-w-0">
                            <p className="truncate text-xs font-black text-navy">
                          {t("student.notifications")}
                        </p>
                            <p className="mt-0.5 text-[10px] font-bold text-primary">
                          {unreadCount > 0
                                ? `${unreadCount} New`
                            : t("notification.noneRecent")}
                        </p>
                      </div>
                      {unreadCount > 0 && (
                        <button
                          type="button"
                          onClick={handleMarkAllNotificationsRead}
                          disabled={markingAllNotifications}
                              className="shrink-0 rounded-lg px-2 py-1 text-[10px] font-extrabold text-primary transition-all hover:bg-primary/10 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                          {t("button.markAllRead")}
                        </button>
                      )}
                    </div>
                      </div>

                      <div className="student-header-scroll max-h-64 overflow-y-auto border-y border-gray-50 p-1.5">
                      {notificationsLoading ? (
                          <div className="px-3 py-6 text-center text-xs font-bold text-gray-400">
                          {t("notification.loading")}
                        </div>
                      ) : notifications.length > 0 ? (
                        notifications.map((notification) => (
                          <Link
                            key={notification.id}
                            to="/student/notifications"
                            onClick={closeHeaderMenus}
                              className={`group flex min-w-0 items-start gap-2 rounded-xl px-2.5 py-2 transition-all hover:bg-gray-50 ${
                                notification.is_read ? "bg-white" : "bg-primary/5"
                            }`}
                          >
                            <span
                                className={`mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition-colors ${
                                notification.is_read
                                  ? "bg-gray-100 text-gray-400"
                                    : "bg-primary text-white"
                              }`}
                            >
                              <Bell className="h-3.5 w-3.5" />
                            </span>
                            <span className="min-w-0 grow">
                                  <span className="block min-w-0 truncate text-xs font-black text-navy group-hover:text-primary">
                                  {translateMaybe(notification.title)}
                                </span>
                              <span className="mt-1 flex items-center gap-1 text-[10px] font-bold text-gray-400">
                                <Clock className="h-3 w-3" />
                                {formatNotificationDate(notification.created_at)}
                              </span>
                            </span>
                          </Link>
                        ))
                      ) : (
                        <div className="px-3 py-6 text-center">
                          <BellOff className="mx-auto h-7 w-7 text-gray-200" />
                          <p className="mt-2 text-xs font-bold text-gray-400">
                            {t("notification.none")}
                          </p>
                        </div>
                      )}
                    </div>

                      <div className="p-1.5">
                      <Link
                        to="/student/notifications"
                        onClick={closeHeaderMenus}
                          className="flex items-center justify-center rounded-xl px-3 py-2 text-xs font-extrabold text-primary transition-all hover:bg-primary/10"
                      >
                        {t("button.viewAll")}
                      </Link>
                    </div>
                  </div>
                </>
              )}
            </div>

            {currentLang && (
              <div className="relative shrink-0">
                <button
                  type="button"
                  onClick={() => {
                    setLanguageDropdownOpen((open) => !open);
                    setProfileDropdownOpen(false);
                    setNotificationsOpen(false);
                  }}
                  className="flex h-9 cursor-pointer items-center gap-1.5 rounded-xl border border-gray-200/50 bg-primary-light px-3 text-xs font-bold text-navy transition-all hover:text-primary"
                  aria-expanded={languageDropdownOpen}
                  title={currentLang.label}
                  aria-label={currentLang.label}
                >
                  <Globe className="h-3.5 w-3.5 text-primary" />
                  <span>{currentLang.short}</span>
                  <ChevronDown
                    className={`h-3.5 w-3.5 transition-transform duration-300 ${languageDropdownOpen ? "rotate-180" : ""}`}
                  />
                </button>
                <AnimatePresence>
                  {languageDropdownOpen && (
                    <>
                      <div className="fixed inset-0 z-40" onClick={closeHeaderMenus} />
                      <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: 10 }}
                        className={`absolute top-full z-50 mt-2 flex w-36 flex-col gap-0.5 rounded-2xl border border-gray-100 bg-white p-1 py-2 shadow-xl ${dropdownAlign}`}
                      >
                        {languages.map((lang) => (
                          <button
                            key={lang.code}
                            type="button"
                            onClick={() => {
                              changeLanguage(lang.code);
                              setLanguageDropdownOpen(false);
                            }}
                            className={`w-full cursor-pointer rounded-xl px-4 py-2 text-start text-xs font-bold transition-all duration-200 ${
                              language === lang.code
                                ? "bg-primary text-white"
                                : "text-navy hover:bg-primary/5 hover:text-primary"
                            }`}
                          >
                            {lang.label}
                          </button>
                        ))}
                      </motion.div>
                    </>
                  )}
                </AnimatePresence>
              </div>
            )}

            <div className="relative">
              <button
                onClick={() => {
                  setProfileDropdownOpen(!profileDropdownOpen);
                  setNotificationsOpen(false);
                  setLanguageDropdownOpen(false);
                }}
                  className={`flex h-9 items-center gap-2 rounded-xl bg-white px-1.5 pe-2.5 shadow-2xs ring-1 transition-all ${
                    profileDropdownOpen
                      ? "text-primary ring-primary/25"
                      : "text-navy ring-gray-100 hover:bg-primary/5 hover:ring-primary/20"
                  }`}
                  aria-expanded={profileDropdownOpen}
                  aria-label={user?.name || t("student.user")}
              >
                  <div className="w-7 h-7 rounded-xl bg-linear-to-br from-primary to-primary-hover text-white flex items-center justify-center font-black text-xs select-none uppercase shadow-sm">
                  {user?.name?.slice(0, 2) || t("student.initials")}
                </div>
                <span className="hidden max-w-40 truncate text-xs font-bold text-navy select-none lg:inline">
                  {user?.name || t("student.user")}
                </span>
                <ChevronDown className="w-3.5 h-3.5 text-gray-400" />
              </button>

              {profileDropdownOpen && (
                <>
                  <div
                    className="fixed inset-0 z-40"
                    onClick={closeHeaderMenus}
                  />
                    <div className={`absolute mt-3 w-64 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl shadow-navy/10 ring-1 ring-black/5 z-50 animate-in fade-in slide-in-from-top-2 duration-150 ${dropdownAlign}`}>
                      <div className="bg-linear-to-br from-navy to-navy-dark p-4 text-white select-none">
                        <div className="flex items-center gap-3">
                          <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-sm font-black uppercase text-white ring-1 ring-white/15">
                            {user?.name?.slice(0, 2) || t("student.initials")}
                          </div>
                          <div className="min-w-0">
                            <p className="text-xs font-extrabold text-white truncate">
                        {user?.name}
                      </p>
                            <p className="mt-0.5 truncate text-[10px] font-semibold text-white/65">
                        {user?.email}
                      </p>
                    </div>
                        </div>
                      </div>

                    <button
                      onClick={handleLogout}
                        className="m-2 flex w-[calc(100%-1rem)] items-center gap-2 rounded-2xl px-3 py-3 text-start text-xs font-extrabold text-rose-600 transition-all hover:bg-rose-50"
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

        <main className="min-w-0 grow space-y-6 p-4 sm:p-6 md:p-8">{children}</main>
      </div>
    </div>
  );
}
