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
  const { language, changeLanguage, isRtl } = useLanguage();
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
      title: "Core Admin",
      links: [
        {
          path: "/apanel/dashboard",
          label: "Dashboard",
          icon: LayoutDashboard,
        },
        { path: "/apanel/settings", label: "Settings", icon: Settings },
        {
          path: "/apanel/translations",
          label: "Translation Dict",
          icon: Languages,
        },
        { path: "/apanel/audit-logs", label: "Audit Logs", icon: History },
      ],
    },
    {
      title: "CMS",
      links: [
        {
          path: "/apanel/cms/about-page",
          label: "About Page",
          icon: FileText,
        },
        {
          path: "/apanel/cms/contact-page",
          label: "Contact Page",
          icon: Mail,
        },
        {
          path: "/apanel/cms/header-navbar",
          label: "Header Navbar",
          icon: MenuIcon,
        },
        {
          path: "/apanel/cms/footer-web",
          label: "Footer Web",
          icon: PanelBottom,
        },
        {
          path: "/apanel/cms/locales",
          label: "Locales",
          icon: Globe,
        },
        {
          path: "/apanel/cms/news-events",
          label: "News & Events",
          icon: Newspaper,
        },
        {
          path: "/apanel/cms/announcements",
          label: "Announcements",
          icon: Megaphone,
        },
        {
          path: "/apanel/cms/blog",
          label: "Blog",
          icon: BookOpen,
        },
        {
          path: "/apanel/cms/video-bdtu",
          label: "Video Gallery",
          icon: Video,
        },
        {
          path: "/apanel/cms/green-campus",
          label: "Green Campus",
          icon: Leaf,
        },
        {
          path: "/apanel/cms/administration",
          label: "Administration",
          icon: UsersRound,
        },
        {
          path: "/apanel/centres-and-departments",
          label: "Centres & Depts",
          icon: Building2,
        },
        {
          path: "/apanel/cms/interactive-services",
          label: "Interactive Services",
          icon: Briefcase,
        },
      ],
    },
    {
      title: "Marketing",
      links: [
        {
          path: "/apanel/newsletter/subscriptions",
          label: "Newsletter Subscriptions",
          icon: MailPlus,
        },
        {
          path: "/apanel/management/contact",
          label: "Contact Messages",
          icon: HelpCircle,
        },
      ],
    },
    {
      title: "Academic Hub",
      links: [
        { path: "/apanel/faculties", label: "Faculties", icon: GraduationCap },
        { path: "/apanel/departments", label: "Departments", icon: Building2 },
        { path: "/apanel/programs", label: "Study Programs", icon: BookOpen },
        { path: "/apanel/courses", label: "Courses", icon: BookOpen },
        { path: "/apanel/staff", label: "Staff Profiles", icon: Contact },
      ],
    },
    {
      title: "Admissions & Students",
      links: [
        { path: "/apanel/students", label: "Students", icon: User },
      ],
    },
    {
      title: "Applications",
      links: [
        {
          path: "/apanel/applications",
          label: "All Applications",
          icon: ClipboardList,
        },
        {
          path: "/apanel/applications?stage=documents",
          label: "Documents Review",
          icon: FileCheck,
        },
        {
          path: "/apanel/applications?stage=equivalency",
          label: "Academic Review",
          icon: GraduationCap,
        },
        {
          path: "/apanel/applications?stage=payments",
          label: "Payments Review",
          icon: CreditCard,
        },
        {
          path: "/apanel/applications?stage=final-review",
          label: "Final Review",
          icon: FileCheck,
        },
        {
          path: "/apanel/applications?stage=admissions",
          label: "Admissions",
          icon: Shield,
        },
        {
          path: "/apanel/countries",
          label: "Countries",
          icon: Globe,
        },
        {
          path: "/apanel/nationalities",
          label: "Nationalities",
          icon: Globe,
        },
      ],
    },
    {
      title: "Student Services",
      links: [
        { path: "/apanel/application-documents", label: "Legacy Documents", icon: FileCheck },
        { path: "/apanel/payments", label: "Legacy Payments", icon: CreditCard },
        {
          path: "/apanel/support-tickets",
          label: "Support Tickets",
          icon: MessageSquare,
        },
        { path: "/apanel/comments", label: "Comments", icon: MessageCircle },
        { path: "/apanel/notifications", label: "Notifications", icon: Bell },
        {
          path: "/apanel/application-status-histories",
          label: "Status History",
          icon: History,
        },
      ],
    },
    {
      title: "Access Control",
      links: [
        { path: "/apanel/users", label: "Users", icon: User },
        { path: "/apanel/roles", label: "Roles", icon: Shield },
        { path: "/apanel/permissions", label: "Permissions", icon: Shield },
      ],
    },
    {
      title: "Assets Manager",
      links: [{ path: "/apanel/media", label: "Media Library", icon: Image }],
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
    return activeLink ? activeLink.label : "Dashboard";
  };

  const renderSidebarContent = () => (
    <div className="flex flex-col h-full bg-navy border-r border-navy-dark text-white select-none">
      {/* Brand Logo header */}
      <div className="flex items-center justify-between px-6 py-5 border-b border-navy-dark">
        <Link to="/apanel" className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-xl bg-primary flex items-center justify-center font-black text-white text-base">
            B
          </div>
          <div className="flex flex-col">
            <span className="font-extrabold text-xs tracking-wider uppercase leading-none">
              BSTU
            </span>
            <span className="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
              Control Panel
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
      <div className="grow flex flex-col md:pl-64 min-h-screen">
        {/* Top Navbar */}
        <header className="sticky top-0 bg-white/80 backdrop-blur-md border-b border-gray-100 z-30 px-6 py-4 flex items-center justify-between">
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
              <span>Admin</span>
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
                <option value="en">EN</option>
                <option value="uz">UZ</option>
                <option value="ru">RU</option>
                <option value="ar">AR</option>
              </select>
            </div>

            {/* User Dropdown */}
            <div className="relative">
              <button
                onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
                className="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-gray-100 hover:border-gray-200 cursor-pointer bg-white transition-all shadow-2xs"
              >
                <div className="w-6 h-6 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs select-none uppercase">
                  {user?.name?.slice(0, 2) || "AP"}
                </div>
                <span className="hidden sm:inline text-xs font-bold text-navy select-none">
                  {user?.name || "Apanel User"}
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
                      Log Out
                    </button>
                  </div>
                </>
              )}
            </div>
          </div>
        </header>

        {/* Content body wrapper */}
        <main className="grow p-6 md:p-8 space-y-6">{children}</main>
      </div>
    </div>
  );
}
