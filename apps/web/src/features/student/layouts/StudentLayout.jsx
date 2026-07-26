import React, { useEffect, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
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
} from "lucide-react";
import { studentPortalService } from "../../../services/studentPortalService";

export default function StudentLayout({ children }) {
  const { language, changeLanguage } = useLanguage();
  const { user, logout } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();

  const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);
  const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);
  const [isTransferStudent, setIsTransferStudent] = useState(false);

  const isRtl = language === "ar";

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

  const handleLogout = async () => {
    try {
      await logout();
      navigate("/student/login");
    } catch (err) {
      console.error("Logout failed", err);
    }
  };

  const menuLinks = [
    { path: "/student/dashboard", label: "Dashboard", icon: LayoutDashboard },
    { path: "/student/profile", label: "Personal Information", icon: User },
    {
      path: "/student/application",
      label: "Application Overview",
      icon: ClipboardList,
    },
    {
      path: "/student/academic-information",
      label: "Academic Information",
      icon: ClipboardList,
    },
    { path: "/student/documents", label: "Required Documents", icon: FileCheck },
    ...(isTransferStudent
      ? [
          {
            path: "/student/equivalency",
            label: "Academic Equivalency",
            icon: FileCheck,
          },
        ]
      : []),
    { path: "/student/payments", label: "Payments", icon: CreditCard },
    { path: "/student/admission", label: "Admission", icon: FileCheck },
    { path: "/student/enrollment", label: "Enrollment", icon: FileCheck },
    { path: "/student/prikaz", label: "Prikaz", icon: FileCheck },
    { path: "/student/service-fee", label: "Service Fee", icon: CreditCard },
    { path: "/student/visa", label: "Telex & Visa", icon: FileCheck },
    { path: "/student/housing", label: "Housing", icon: ClipboardList },
    { path: "/student/residence", label: "Residence", icon: FileCheck },
    { path: "/student/notifications", label: "Notifications", icon: Bell },
    { path: "/student/support", label: "Support Center", icon: MessageSquare },
  ];

  const isActive = (path) => {
    if (path === "/student/dashboard" && location.pathname === "/student")
      return true;
    return location.pathname === path;
  };

  const getBreadcrumbLabel = () => {
    const activeLink = menuLinks.find((link) => isActive(link.path));
    return activeLink ? activeLink.label : "Dashboard";
  };

  const renderSidebar = () => (
    <div className="flex flex-col h-full bg-navy border-r border-navy-dark text-white select-none">
      {/* Brand Header */}
      <div className="flex items-center justify-between px-6 py-5 border-b border-navy-dark">
        <Link to="/student/dashboard" className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-xl bg-primary flex items-center justify-center font-black text-white text-base">
            S
          </div>
          <div className="flex flex-col">
            <span className="font-extrabold text-xs tracking-wider uppercase leading-none">
              BSTU
            </span>
            <span className="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
              Student Portal
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
      <div className="grow overflow-y-auto px-4 py-6 space-y-2">
        <div className="space-y-1">
          {menuLinks.map((link) => {
            const Icon = link.icon;
            const active = isActive(link.path);
            return (
              <Link
                key={link.path}
                to={link.path}
                onClick={() => setMobileSidebarOpen(false)}
                className={`flex items-center gap-2.5 px-3 py-3.5 rounded-xl text-xs font-bold transition-all ${
                  active
                    ? "bg-primary text-white shadow-md shadow-primary/20"
                    : "text-gray-400 hover:text-white hover:bg-navy-dark/40"
                }`}
              >
                <Icon className="w-4.5 h-4.5 shrink-0" />
                <span>{link.label}</span>
              </Link>
            );
          })}
        </div>

        <div className="pt-6 border-t border-navy-dark">
          <Link
            to="/"
            className="flex items-center gap-2.5 px-3 py-3 text-xs font-bold text-gray-400 hover:text-white hover:bg-navy-dark/40 rounded-xl transition-all"
          >
            <Home className="w-4.5 h-4.5 shrink-0" />
            <span>Return to Homepage</span>
          </Link>
        </div>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-gray-50 flex" dir={isRtl ? "rtl" : "ltr"}>
      {/* Desktop Sidebar */}
      <aside className="hidden md:block w-64 shrink-0 fixed inset-y-0 left-0 z-20">
        {renderSidebar()}
      </aside>

      {/* Mobile Sidebar overlay */}
      {mobileSidebarOpen && (
        <div className="fixed inset-0 z-50 flex md:hidden">
          <div
            className="fixed inset-0 bg-navy/40 backdrop-blur-xs"
            onClick={() => setMobileSidebarOpen(false)}
          />
          <aside className="relative w-64 h-full animate-in slide-in-from-left duration-200">
            {renderSidebar()}
          </aside>
        </div>
      )}

      {/* Main Workspace */}
      <div className="grow flex flex-col md:pl-64 min-h-screen">
        <header className="sticky top-0 bg-white/80 backdrop-blur-md border-b border-gray-100 z-30 px-6 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <button
              onClick={() => setMobileSidebarOpen(true)}
              className="md:hidden text-navy hover:text-primary p-1.5 rounded-lg border border-gray-200 cursor-pointer bg-white"
            >
              <MenuIcon className="w-5 h-5" />
            </button>

            <div className="hidden sm:flex items-center gap-2 text-xs font-semibold text-gray-400 select-none">
              <span>Portal</span>
              <span>/</span>
              <span className="text-navy font-bold">
                {getBreadcrumbLabel()}
              </span>
            </div>
          </div>

          {/* Top Bar Actions */}
          <div className="flex items-center gap-4">
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

            <div className="relative">
              <button
                onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
                className="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-gray-100 hover:border-gray-200 cursor-pointer bg-white transition-all shadow-2xs"
              >
                <div className="w-6 h-6 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs select-none uppercase">
                  {user?.name?.slice(0, 2) || "ST"}
                </div>
                <span className="hidden sm:inline text-xs font-bold text-navy select-none">
                  {user?.name || "Student User"}
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

        <main className="grow p-6 md:p-8 space-y-6">{children}</main>
      </div>
    </div>
  );
}
