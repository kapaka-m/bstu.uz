import React, { useEffect, useRef, useState } from "react";
import { Link, useLocation } from "react-router-dom";
import { Menu, X, ChevronDown, LogIn, Globe } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

function sortedActive(items = []) {
  const list = Array.isArray(items)
    ? items
    : Array.isArray(items?.data)
      ? items.data
      : Array.isArray(items?.items)
        ? items.items
        : Array.isArray(items?.data?.items)
          ? items.data.items
          : [];

  return [...list]
    .filter((item) => item?.is_active !== false)
    .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0));
}

function dropdownKey(item, fallback) {
  return `${item?.id || item?.route_name || fallback}`;
}

export default function Header() {
  const [isSticky, setIsSticky] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [openDropdowns, setOpenDropdowns] = useState({});
  const [isLangDropdownOpen, setIsLangDropdownOpen] = useState(false);

  const { language, changeLanguage, t, logoSrc, headerMenu, locales, isRtl } =
    useLanguage();
  const location = useLocation();
  const langDropdownRef = useRef(null);

  useEffect(() => {
    const handleScroll = () => setIsSticky(window.scrollY > 80);
    const handleClickOutside = (event) => {
      if (
        langDropdownRef.current &&
        !langDropdownRef.current.contains(event.target)
      ) {
        setIsLangDropdownOpen(false);
      }
    };

    window.addEventListener("scroll", handleScroll);
    document.addEventListener("mousedown", handleClickOutside);

    return () => {
      window.removeEventListener("scroll", handleScroll);
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const navItems = sortedActive(headerMenu || []);
  const menuItems = navItems.filter((item) => item.route_name !== "action");
  const actionItems = navItems.filter((item) => item.route_name === "action");
  const loginAction = actionItems.find(
    (item) => item.icon === "login" || item.url === "/login",
  );
  const languages = React.useMemo(() => {
    return (locales || [])
      .filter((l) => l.is_active !== false)
      .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0))
      .map((l) => ({
        code: l.code,
        label: l.native_name || l.name,
        direction: l.direction,
        short: l.code.toUpperCase(),
      }));
  }, [locales]);

  const currentLang = languages.find((l) => l.code === language) ||
    languages[0] ||
    null;

  const handleLinkClick = () => {
    setIsMobileMenuOpen(false);
    setOpenDropdowns({});
  };

  const toggleDropdown = (key) => {
    setOpenDropdowns((prev) => ({ ...prev, [key]: !prev[key] }));
  };

  const renderDesktopLink = (item, className = "") => (
    <Link
      key={item.id || item.url}
      to={item.url || "/"}
      className={`${className} transition-colors duration-300 hover:text-primary ${
        location.pathname === item.url ? "text-primary font-bold" : "text-navy"
      }`}
    >
      {item.label}
    </Link>
  );

  const renderMediaDropdown = (item) => (
    <div className="absolute top-full left-1/2 z-50 mt-2 flex w-52 -translate-x-1/2 flex-col gap-1 rounded-2xl border border-gray-100 bg-white p-3 text-start shadow-xl opacity-0 invisible transition-all duration-300 group-hover:visible group-hover:opacity-100">
      {sortedActive(item.children).map((child) => (
        <Link
          key={child.id || child.url}
          to={child.url || "/"}
          className="rounded-xl px-3 py-2 text-xs font-semibold text-navy transition-colors hover:bg-primary/5 hover:text-primary"
        >
          {child.label}
        </Link>
      ))}
    </div>
  );

  const renderFacultiesDropdown = (item) => (
    <div className="absolute top-full left-1/2 z-50 mt-2 grid w-245 -translate-x-1/2 grid-cols-4 gap-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-xl opacity-0 invisible transition-all duration-300 group-hover:visible group-hover:opacity-100">
      {sortedActive(item.children).map((faculty) => (
        <div key={faculty.id || faculty.url} className="flex flex-col gap-2.5">
          <Link
            to={faculty.url || "#"}
            className="text-start text-xs font-extrabold uppercase tracking-wider text-primary hover:underline"
          >
            {faculty.label}
          </Link>
          <div className="flex flex-col gap-1.5 border-t border-gray-50 pt-2 text-start">
            {sortedActive(faculty.children).map((department) => (
              <Link
                key={department.id || department.url}
                to={department.url || "#"}
                className={`${isRtl ? "text-xs" : "text-[11px]"} leading-snug text-navy transition-colors hover:text-primary`}
              >
                {department.label}
              </Link>
            ))}
          </div>
        </div>
      ))}
    </div>
  );

  const renderStructureDropdown = (item) => (
    <div className="absolute top-full left-1/2 z-50 mt-2 flex w-160 -translate-x-1/2 flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-xl opacity-0 invisible transition-all duration-300 group-hover:visible group-hover:opacity-100">
      <div className="border-b border-gray-100 pb-2 text-start">
        <span className="text-xs font-extrabold uppercase tracking-widest text-navy/50">
          {item.label}
        </span>
      </div>
      <div className="grid grid-cols-2 gap-6 text-start">
        {sortedActive(item.children).map((group, groupIndex) => (
          <div key={group.id || group.label} className="flex flex-col gap-2.5">
            <span className="text-xs font-extrabold uppercase tracking-wider text-primary">
              {group.label}
            </span>
            <div
              className={`flex flex-col gap-1.5 border-t border-gray-50 pt-2 pe-1.5 text-[11px] font-semibold leading-snug text-navy ${
                groupIndex === 0
                  ? ""
                  : "max-h-75 overflow-y-auto scrollbar-thin"
              }`}
            >
              {sortedActive(group.children).map((child) => (
                <Link
                  key={child.id || child.url}
                  to={child.url || "#"}
                  className={`py-0.5 transition-colors hover:text-primary ${
                    isRtl
                      ? "text-xs leading-normal font-semibold"
                      : "leading-tight font-medium"
                  }`}
                >
                  {child.label}
                </Link>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderDesktopDropdown = (item) => {
    if (item.route_name === "media") return renderMediaDropdown(item);
    if (item.route_name === "faculties") return renderFacultiesDropdown(item);
    if (item.route_name === "structure") return renderStructureDropdown(item);
    return renderMediaDropdown(item);
  };

  const renderDesktopItem = (item) => {
    if ((item.route_name || "link") === "link") {
      return renderDesktopLink(item);
    }

    return (
      <div key={item.id || item.route_name} className="group relative">
        <button
          type="button"
          className="flex cursor-pointer items-center gap-1 py-2 font-semibold text-navy transition-colors hover:text-primary"
        >
          {item.label}
          <ChevronDown className="h-4 w-4" />
        </button>
        {renderDesktopDropdown(item)}
      </div>
    );
  };

  const renderMobileItem = (item, level = 0) => {
    const children = sortedActive(item.children);
    const key = dropdownKey(item, `level-${level}`);
    const hasChildren = children.length > 0;
    const paddingClass = level === 0 ? "py-2 text-base" : "py-1.5 text-sm";

    if (!hasChildren && (item.route_name || "link") === "link") {
      return (
        <Link
          key={item.id || item.url}
          to={item.url || "/"}
          onClick={handleLinkClick}
          className={`${paddingClass} text-start transition-colors hover:text-primary ${
            location.pathname === item.url ? "text-primary font-bold" : ""
          } ${level > 0 ? "text-gray-500" : "text-navy"}`}
        >
          {item.label}
        </Link>
      );
    }

    return (
      <div
        key={item.id || key}
        className={`${level === 0 ? "border-t border-gray-50 pt-2" : ""} flex flex-col`}
      >
        <button
          type="button"
          onClick={() => toggleDropdown(key)}
          className={`flex cursor-pointer items-center justify-between ${paddingClass} text-navy`}
        >
          <span
            className={`${level > 0 ? "text-xs font-extrabold uppercase tracking-wider text-primary" : ""}`}
          >
            {item.label}
          </span>
          <ChevronDown
            className={`h-4 w-4 transition-transform duration-300 ${openDropdowns[key] ? "rotate-180" : ""}`}
          />
        </button>
        {openDropdowns[key] && (
          <div
            className={`${level === 0 ? "ms-2 border-s-2 ps-4" : "ms-1 border-s ps-2"} flex max-h-[50vh] flex-col gap-2 overflow-y-auto pt-2 text-start scrollbar-thin`}
          >
            {children.map((child) => renderMobileItem(child, level + 1))}
          </div>
        )}
      </div>
    );
  };

  return (
    <header
      className={`fixed top-0 left-0 z-50 w-full transition-all duration-300 ${
        isSticky || location.pathname !== "/"
          ? "bg-white py-4 shadow-md"
          : "bg-transparent py-6"
      }`}
    >
      <div className="container mx-auto flex max-w-7xl items-center justify-between px-4 md:px-8">
        <Link to="/" className="group flex shrink-0 items-center gap-3">
          <img
            src={logoSrc}
            alt={t("common.logoAlt")}
            className="h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-105 md:h-12"
            width="108"
            height="48"
          />
        </Link>

        <nav className="hidden items-center gap-8 text-sm font-semibold xl:flex">
          {menuItems.map(renderDesktopItem)}
        </nav>

        <div className="flex items-center gap-3 md:gap-4">
          {currentLang && (
            <div className="relative shrink-0" ref={langDropdownRef}>
              <button
                type="button"
                onClick={() => setIsLangDropdownOpen(!isLangDropdownOpen)}
                className="flex cursor-pointer items-center gap-1.5 rounded-xl border border-gray-200/50 bg-primary-light px-3 py-2 text-xs font-bold text-navy transition-all hover:text-primary"
              >
                <Globe className="h-3.5 w-3.5 text-primary" />
                <span>{currentLang.short}</span>
                <ChevronDown
                  className={`h-3.5 w-3.5 transition-transform duration-300 ${isLangDropdownOpen ? "rotate-180" : ""}`}
                />
              </button>
              <AnimatePresence>
                {isLangDropdownOpen && (
                  <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 10 }}
                    className={`absolute top-full z-50 mt-2 flex w-36 flex-col gap-0.5 rounded-2xl border border-gray-100 bg-white p-1 py-2 shadow-xl ${isRtl ? "left-0" : "right-0"}`}
                  >
                    {languages.map((lang) => (
                      <button
                        key={lang.code}
                        type="button"
                        onClick={() => {
                          changeLanguage(lang.code);
                          setIsLangDropdownOpen(false);
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
                )}
              </AnimatePresence>
            </div>
          )}

          {loginAction && (
            <Link
              to={loginAction.url}
              className="hidden items-center gap-1.5 text-sm font-bold text-navy transition-colors hover:text-primary sm:inline-flex"
            >
              <LogIn className="h-4 w-4" />
              {loginAction.label}
            </Link>
          )}

          <button
            type="button"
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            aria-label={t("common.toggleMobileMenu")}
            className="cursor-pointer p-2 text-navy transition-colors hover:text-primary xl:hidden"
          >
            {isMobileMenuOpen ? (
              <X className="h-6 w-6" />
            ) : (
              <Menu className="h-6 w-6" />
            )}
          </button>
        </div>
      </div>

      <AnimatePresence>
        {isMobileMenuOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            className="overflow-hidden border-t border-gray-100 bg-white xl:hidden"
          >
            <div
              className={`flex max-h-[80vh] flex-col gap-4 overflow-y-auto px-6 py-4 font-semibold text-navy ${isRtl ? "text-right" : ""}`}
            >
              {menuItems.map((item) => renderMobileItem(item))}

              {loginAction && (
                <div className="flex flex-col gap-3 border-t border-gray-100 pt-4">
                  <Link
                    to={loginAction.url}
                    onClick={handleLinkClick}
                    className="w-full rounded-xl border border-gray-200 py-2.5 text-center text-sm font-bold transition-colors hover:bg-gray-50"
                  >
                    {loginAction.label}
                  </Link>
                </div>
              )}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </header>
  );
}
