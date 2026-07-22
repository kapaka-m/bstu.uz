import React, { useState, useEffect, useRef } from "react";
import { Link, useLocation } from "react-router-dom";
import { Menu, X, ChevronDown, LogIn, Globe } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { visibleCentersData } from "../data/universityData";
import { administrationService } from "../services/administrationService";

export default function Header() {
  const [isSticky, setIsSticky] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [openDropdowns, setOpenDropdowns] = useState({});
  const [isLangDropdownOpen, setIsLangDropdownOpen] = useState(false);
  const [administrationLinks, setAdministrationLinks] = useState([]);
  const [administrationSettings, setAdministrationSettings] = useState(null);

  const { language, changeLanguage, t, logoSrc } = useLanguage();
  const location = useLocation();
  const langDropdownRef = useRef(null);

  useEffect(() => {
    const handleScroll = () => {
      setIsSticky(window.scrollY > 80);
    };

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

  useEffect(() => {
    let alive = true;

    Promise.all([
      administrationService.getSettings(language).catch(() => null),
      administrationService.getProfiles({}, language).catch(() => []),
    ]).then(([settings, profiles]) => {
      if (!alive) return;
      setAdministrationSettings(settings || null);
      setAdministrationLinks(profiles || []);
    });

    return () => {
      alive = false;
    };
  }, [language]);

  const handleLinkClick = () => {
    setIsMobileMenuOpen(false);
  };

  const toggleDropdown = (name) => {
    setOpenDropdowns((prev) => ({
      ...prev,
      [name]: !prev[name],
    }));
  };

  const navLinks = [
    { label: t("nav.home"), path: "/" },
    { label: t("nav.aboutUs"), path: "/about" },
    { label: t("nav.services"), path: "/services" },
  ];

  const languages = [
    { code: "en", label: "English", short: "EN" },
    { code: "uz", label: "O'zbek", short: "UZ" },
    { code: "ru", label: "Русский", short: "RU" },
    { code: "ar", label: "العربية", short: "AR" },
  ];

  const currentLang =
    languages.find((l) => l.code === language) || languages[0];

  const facultyMenuData = [
    {
      id: "faculty-of-engineering",
      titleKey: "faculties.faculty-of-engineering.name",
      name: "Faculty of Engineering",
      slug: "faculty-of-engineering",
      departments: [
        {
          key: "electrical-power-engineering",
          label: "Department of Electrical and Power Engineering",
          to: "/department/electrical-power-engineering",
        },
        {
          key: "architecture",
          label: "Department of Architecture",
          to: "/department/architecture",
        },
        {
          key: "civil-engineering",
          label: "Department of Civil Engineering",
          to: "/department/civil-engineering",
        },
        {
          key: "light-industry-engineering-and-design",
          label: "Department of Light Industry Engineering and Design",
          to: "/department/light-industry-engineering-and-design",
        },
        {
          key: "mechanics-engineering-graphics",
          translationKey: "mechanics-and-engineering-graphics",
          label: "Department of Mechanics and Engineering Graphics",
          to: "/department/mechanics-engineering-graphics",
        },
        {
          key: "technological-machines-equipment",
          translationKey: "technological-machines-and-equipment",
          label: "Department of Technological Machines and Equipment",
          to: "/department/technological-machines-equipment",
        },
      ],
    },
    {
      id: "faculty-of-technology",
      titleKey: "faculties.faculty-of-technology.name",
      name: "Faculty of Technology",
      slug: "faculty-of-technology",
      departments: [
        {
          key: "oil-gas-refining-technology",
          translationKey: "oil-and-gas-refining-technology",
          label: "Department of Oil and Gas Refining Technology",
          to: "/department/oil-gas-refining-technology",
        },
        {
          key: "food-technology-service",
          translationKey: "food-technology-and-service",
          label: "Department of Food Technology and Service",
          to: "/department/food-technology-service",
        },
        {
          key: "chemical-technology",
          label: "Department of Chemical Technology",
          to: "/department/chemical-technology",
        },
        {
          key: "agricultural-products-storage-oil-fat-technology",
          label:
            "Department of Agricultural Products Storage & Oil-Fat Technology",
          to: "/department/agricultural-products-storage-oil-fat-technology",
        },
        {
          key: "oil-gas-engineering-upstream-downstream",
          translationKey: "oil-and-gas-engineering-upstream-downstream",
          label:
            "Department of Oil and Gas Engineering (Upstream & Downstream)",
          to: "/department/oil-gas-engineering-upstream-downstream",
        },
        {
          key: "metrology-standardization-quality-control",
          translationKey: "metrology-standardization-and-quality-control",
          label:
            "Department of Metrology, Standardization, and Quality Control",
          to: "/department/metrology-standardization-quality-control",
        },
      ],
    },
    {
      id: "faculty-of-natural-resources-management",
      titleKey: "faculties.faculty-of-natural-resources-management.name",
      name: "Faculty of Natural Resources Management",
      slug: "faculty-of-natural-resources-management",
      departments: [
        {
          key: "irrigation-melioration",
          label: "Department of Irrigation and Melioration",
          to: "/department/irrigation-melioration",
        },
        {
          key: "hydrotechnical-structures-pump-stations",
          label: "Department of Hydrotechnical Structures and Pump Stations",
          to: "/department/hydrotechnical-structures-pump-stations",
        },
        {
          key: "agricultural-water-resources-engineering-technologies",
          translationKey:
            "agricultural-and-water-resources-engineering-technologies",
          label:
            "Department of Agricultural and Water Resources Engineering-Technologies",
          to: "/department/agricultural-water-resources-engineering-technologies",
        },
        {
          key: "land-resources-management-state-land-cadastres",
          label:
            "Department of Land Resources Management and State Land Cadastres",
          to: "/department/land-resources-management-state-land-cadastres",
        },
        {
          key: "industrial-ecology-hydrogeology",
          label: "Department of Industrial Ecology and Hydrogeology",
          to: "/department/industrial-ecology-hydrogeology",
        },
        {
          key: "vehicle-engineering-automotive-transport-systems",
          label:
            "Department of Vehicle Engineering (Automotive & Transport Systems)",
          to: "/department/vehicle-engineering-automotive-transport-systems",
        },
      ],
    },
    {
      id: "faculty-of-service-and-digitalization",
      titleKey: "faculties.faculty-of-service-and-digitalization.name",
      name: "Faculty of Service and Digitalization",
      slug: "faculty-of-service-and-digitalization",
      departments: [
        {
          key: "technological-processes-production-automation",
          label:
            "Department of Technological Processes and Production Automation",
          to: "/department/technological-processes-production-automation",
        },
        {
          key: "information-and-communication-technologies",
          label: "Department of Information and Communication Technologies",
          to: "/department/information-and-communication-technologies",
        },
        {
          key: "economics-and-management",
          label: "Department of Economics and Management",
          to: "/department/economics-and-management",
        },
        {
          key: "artificial-intelligence-digitalization",
          translationKey: "artificial-intelligence-and-digitalization",
          label: "Department of Artificial Intelligence and Digitalization",
          to: "/department/artificial-intelligence-digitalization",
        },
        {
          key: "social-sciences-physical-culture",
          translationKey: "social-sciences-and-physical-culture",
          label: "Department of Social Sciences and Physical Culture",
          to: "/department/social-sciences-physical-culture",
        },
        {
          key: "exact-sciences",
          label: "Department of Exact Sciences",
          to: "/department/exact-sciences",
        },
      ],
    },
  ];

  return (
    <header
      className={`fixed top-0 left-0 w-full z-50 transition-all duration-300 ${
        isSticky || location.pathname !== "/"
          ? "bg-white shadow-md py-4"
          : "bg-transparent py-6"
      }`}
    >
      <div className="container mx-auto px-4 md:px-8 max-w-7xl flex items-center justify-between">
        {/* Logo */}
        <Link to="/" className="flex items-center gap-3 group shrink-0">
          <img
            src={logoSrc}
            alt={t("common.logoAlt")}
            className="h-10 md:h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
            width="108"
            height="48"
          />
        </Link>

        {/* Desktop Nav */}
        <nav
          className={`hidden xl:flex items-center gap-8 font-semibold text-sm ${language === "ar" ? "flex-row-reverse" : ""}`}
        >
          {navLinks.map((link) => {
            const isActive = location.pathname === link.path;

            return (
              <Link
                key={link.label}
                to={link.path}
                className={`transition-colors duration-300 hover:text-primary ${
                  isActive ? "text-primary font-bold" : "text-navy"
                }`}
              >
                {link.label}
              </Link>
            );
          })}

          {/* Listing Dropdown */}
          <div className="relative group">
            <button className="flex items-center gap-1 text-navy hover:text-primary transition-colors py-2 cursor-pointer font-semibold">
              {t("nav.faculties")} <ChevronDown className="w-4 h-4" />
            </button>
            <div className="absolute top-full mt-2 w-245 bg-white border border-gray-100 rounded-2xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 p-6 grid grid-cols-4 gap-6 z-50 left-1/2 -translate-x-1/2">
              {facultyMenuData.map((faculty) => (
                <div key={faculty.id} className="flex flex-col gap-2.5">
                  <Link
                    to={`/faculty/${faculty.slug}`}
                    className="text-xs font-extrabold uppercase tracking-wider text-primary hover:underline text-start"
                  >
                    {t(faculty.titleKey, faculty.name)}
                  </Link>
                  <div className="flex flex-col gap-1.5 border-t border-gray-50 pt-2 text-start">
                    {faculty.departments.map((dept, index) => {
                      return (
                        <Link
                          key={index}
                          to={dept.to}
                          className={`${language === "ar" ? "text-xs" : "text-[11px]"} leading-snug text-navy hover:text-primary transition-colors`}
                        >
                          {t(
                            `departments.${dept.translationKey || dept.key}.name`,
                            dept.label,
                          )}
                        </Link>
                      );
                    })}
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Media Center Dropdown */}
          <div className="relative group">
            <button className="flex items-center gap-1 text-navy hover:text-primary transition-colors py-2 cursor-pointer font-semibold">
              {t("nav.media", "Media Center")}{" "}
              <ChevronDown className="w-4 h-4 text-primary" />
            </button>
            <div
              className={`absolute top-full mt-2 w-48 bg-white border border-gray-100 rounded-2xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 p-3 z-50 flex flex-col gap-1 left-1/2 -translate-x-1/2 text-start`}
            >
              <Link
                to="/announcements"
                className="hover:bg-primary/5 hover:text-primary text-navy transition-colors px-3 py-2 rounded-xl text-xs font-semibold"
              >
                {t("nav.announcements", "Announcements")}
              </Link>
              <Link
                to="/news"
                className="hover:bg-primary/5 hover:text-primary text-navy transition-colors px-3 py-2 rounded-xl text-xs font-semibold"
              >
                {t("nav.news", "News")}
              </Link>
              <Link
                to="/blog"
                className="hover:bg-primary/5 hover:text-primary text-navy transition-colors px-3 py-2 rounded-xl text-xs font-semibold"
              >
                {t("nav.blog", "Blog")}
              </Link>
              <Link
                to="/video-bdtu"
                className="hover:bg-primary/5 hover:text-primary text-navy transition-colors px-3 py-2 rounded-xl text-xs font-semibold"
              >
                {t("nav.videoBdtu", "Video BDTU")}
              </Link>
              <Link
                to="/green-campus"
                className="hover:bg-primary/5 hover:text-primary text-navy transition-colors px-3 py-2 rounded-xl text-xs font-semibold"
              >
                {t("nav.greenCampus", "Green Campus")}
              </Link>
            </div>
          </div>

          {/* University Dropdown */}
          <div className="relative group">
            <button className="flex items-center gap-1 text-navy hover:text-primary transition-colors py-2 cursor-pointer font-semibold">
              {t("nav.structure", "Structure")}{" "}
              <ChevronDown className="w-4 h-4" />
            </button>
            <div className="absolute top-full mt-2 w-160 bg-white border border-gray-100 rounded-2xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 p-6 z-50 flex flex-col gap-4 left-1/2 -translate-x-1/2">
              <div className="border-b border-gray-100 pb-2 text-start">
                <span className="text-xs font-extrabold uppercase tracking-widest text-navy/50">
                  {t("common.structure", "Structure")}
                </span>
              </div>
              <div className="grid grid-cols-2 gap-6 text-start">
                {/* Column 1: Administration */}
                <div className="flex flex-col gap-2.5">
                  <span className="text-xs font-extrabold uppercase tracking-wider text-primary">
                    {administrationSettings?.structure_title ||
                      t("common.administration", "Administration")}
                  </span>
                  <div className="flex flex-col gap-1.5 border-t border-gray-50 pt-2 text-[11px] leading-snug text-navy font-semibold">
                    {administrationLinks.map((item) => (
                      <Link
                        key={item.slug}
                        to={item.path}
                        className={`hover:text-primary transition-colors py-0.5 ${language === "ar" ? "text-xs leading-normal font-semibold" : "leading-tight font-medium"}`}
                      >
                        {item.position || item.title || item.name}
                      </Link>
                    ))}
                  </div>
                </div>

                {/* Column 2: Centres and departments */}
                <div className="flex flex-col gap-2.5">
                  <span className="text-xs font-extrabold uppercase tracking-wider text-primary">
                    {t("nav.centers")}
                  </span>
                  <div className="flex flex-col gap-1.5 border-t border-gray-50 pt-2 text-[11px] leading-snug text-navy font-semibold max-h-75 overflow-y-auto pr-1.5 scrollbar-thin">
                    {Object.keys(visibleCentersData).map((centerId) => (
                      <Link
                        key={centerId}
                        to={`/center/${centerId}`}
                        className="hover:text-primary transition-colors py-0.5"
                      >
                        {t(
                          `centers.${centerId}.name`,
                          visibleCentersData[centerId].name,
                        )}
                      </Link>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <Link
            to="/contact"
            className={`transition-colors duration-300 hover:text-primary ${
              location.pathname === "/contact"
                ? "text-primary font-bold"
                : "text-navy"
            }`}
          >
            {t("nav.contact")}
          </Link>
        </nav>

        {/* Action Buttons, Lang Switcher & Mobile Toggle */}
        <div
          className={`flex items-center gap-3 md:gap-4 ${language === "ar" ? "flex-row-reverse" : ""}`}
        >
          {/* Custom Language Dropdown Switcher */}
          <div className="relative shrink-0" ref={langDropdownRef}>
            <button
              onClick={() => setIsLangDropdownOpen(!isLangDropdownOpen)}
              className="flex items-center gap-1.5 px-3 py-2 bg-primary-light border border-gray-200/50 text-navy hover:text-primary transition-all rounded-xl text-xs font-bold cursor-pointer"
            >
              <Globe className="w-3.5 h-3.5 text-primary" />
              <span>{currentLang.short}</span>
              <ChevronDown
                className={`w-3.5 h-3.5 transition-transform duration-300 ${isLangDropdownOpen ? "rotate-180" : ""}`}
              />
            </button>
            <AnimatePresence>
              {isLangDropdownOpen && (
                <motion.div
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: 10 }}
                  className={`absolute top-full mt-2 w-36 bg-white border border-gray-100 rounded-2xl shadow-xl py-2 z-50 flex flex-col gap-0.5 p-1 ${language === "ar" ? "left-0" : "right-0"}`}
                >
                  {languages.map((lang) => (
                    <button
                      key={lang.code}
                      onClick={() => {
                        changeLanguage(lang.code);
                        setIsLangDropdownOpen(false);
                      }}
                      className={`w-full text-start px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200 cursor-pointer ${
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

          <Link
            to="/login"
            className="hidden sm:inline-flex items-center gap-1.5 text-sm font-bold text-navy hover:text-primary transition-colors"
          >
            <LogIn className="w-4 h-4" />
            {t("nav.login")}
          </Link>

          <Link
            to="/register"
            className="hidden sm:inline-flex items-center justify-center bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-300 shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5"
          >
            {t("nav.register")}
          </Link>

          <button
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            aria-label={t("common.toggleMobileMenu")}
            className="xl:hidden p-2 text-navy hover:text-primary transition-colors cursor-pointer"
          >
            {isMobileMenuOpen ? (
              <X className="w-6 h-6" />
            ) : (
              <Menu className="w-6 h-6" />
            )}
          </button>
        </div>
      </div>

      {/* Mobile Nav Menu */}
      <AnimatePresence>
        {isMobileMenuOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            className="xl:hidden bg-white border-t border-gray-100 overflow-hidden"
          >
            <div
              className={`px-6 py-4 flex flex-col gap-4 max-h-[80vh] overflow-y-auto font-semibold text-navy ${language === "ar" ? "text-right" : ""}`}
            >
              {navLinks.map((link) => {
                const isActive = location.pathname === link.path;
                return (
                  <Link
                    key={link.label}
                    to={link.path}
                    onClick={handleLinkClick}
                    className={`py-2 text-base transition-colors ${
                      isActive ? "text-primary font-bold" : ""
                    }`}
                  >
                    {link.label}
                  </Link>
                );
              })}

              {/* Mobile Media Dropdown */}
              <div className="border-t border-gray-50 pt-2 flex flex-col">
                <button
                  onClick={() => toggleDropdown("media")}
                  className={`flex items-center justify-between py-2 text-base text-navy cursor-pointer ${language === "ar" ? "flex-row-reverse" : ""}`}
                >
                  <span>{t("nav.media", "Media Center")}</span>
                  <ChevronDown
                    className={`w-4 h-4 transition-transform duration-300 ${openDropdowns.media ? "rotate-180" : ""}`}
                  />
                </button>
                {openDropdowns.media && (
                  <div className="flex flex-col pt-2 gap-2 border-s-2 ps-4 ms-2 text-start">
                    <Link
                      to="/announcements"
                      onClick={handleLinkClick}
                      className="py-1.5 text-sm text-navy hover:text-primary transition-colors"
                    >
                      {t("nav.announcements", "Announcements")}
                    </Link>
                    <Link
                      to="/news"
                      onClick={handleLinkClick}
                      className="py-1.5 text-sm text-navy hover:text-primary transition-colors"
                    >
                      {t("nav.news", "News")}
                    </Link>
                    <Link
                      to="/blog"
                      onClick={handleLinkClick}
                      className="py-1.5 text-sm text-navy hover:text-primary transition-colors"
                    >
                      {t("nav.blog", "Blog")}
                    </Link>
                    <Link
                      to="/video-bdtu"
                      onClick={handleLinkClick}
                      className="py-1.5 text-sm text-navy hover:text-primary transition-colors"
                    >
                      {t("nav.videoBdtu", "Video BDTU")}
                    </Link>
                    <Link
                      to="/green-campus"
                      onClick={handleLinkClick}
                      className="py-1.5 text-sm text-navy hover:text-primary transition-colors"
                    >
                      {t("nav.greenCampus", "Green Campus")}
                    </Link>
                  </div>
                )}
              </div>

              {/* Mobile Listing Dropdown */}
              <div className="border-t border-gray-50 pt-2 flex flex-col">
                <button
                  onClick={() => toggleDropdown("listing")}
                  className={`flex items-center justify-between py-2 text-base text-navy cursor-pointer ${language === "ar" ? "flex-row-reverse" : ""}`}
                >
                  <span>{t("nav.faculties")}</span>
                  <ChevronDown
                    className={`w-4 h-4 transition-transform duration-300 ${openDropdowns.listing ? "rotate-180" : ""}`}
                  />
                </button>
                {openDropdowns.listing && (
                  <div className="flex flex-col pt-2 gap-3 max-h-[50vh] overflow-y-auto border-s-2 ps-4 ms-2">
                    {facultyMenuData.map((faculty) => (
                      <div key={faculty.id} className="flex flex-col gap-2">
                        <Link
                          to={`/faculty/${faculty.slug}`}
                          onClick={handleLinkClick}
                          className="text-xs font-extrabold text-primary py-1 uppercase tracking-wider text-start"
                        >
                          {t(faculty.titleKey, faculty.name)}
                        </Link>
                        <div className="flex flex-col gap-1.5 border-s ps-2 ms-1 text-start">
                          {faculty.departments.map((dept, index) => {
                            return (
                              <Link
                                key={index}
                                to={dept.to}
                                onClick={handleLinkClick}
                                className={`py-1 ${language === "ar" ? "text-xs" : "text-[11px]"} text-gray-500 hover:text-primary transition-colors leading-tight`}
                              >
                                {t(
                                  `departments.${dept.translationKey || dept.key}.name`,
                                  dept.label,
                                )}
                              </Link>
                            );
                          })}
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Mobile University Dropdown */}
              <div className="flex flex-col">
                <button
                  onClick={() => toggleDropdown("university")}
                  className={`flex items-center justify-between py-2 text-base text-navy cursor-pointer ${language === "ar" ? "flex-row-reverse" : ""}`}
                >
                  <span>{t("nav.structure", "Structure")}</span>
                  <ChevronDown
                    className={`w-4 h-4 transition-transform duration-300 ${openDropdowns.university ? "rotate-180" : ""}`}
                  />
                </button>
                {openDropdowns.university && (
                  <div className="flex flex-col pt-2 gap-3 max-h-[50vh] overflow-y-auto border-s-2 ps-4 ms-2">
                    {/* Structure Heading */}
                    <div className="text-[10px] font-extrabold uppercase tracking-widest text-navy/50 border-b border-gray-50 pb-1 text-start">
                      {t("common.structure", "Structure")}
                    </div>
                    {/* Administration */}
                    <div className="flex flex-col gap-1">
                      <span className="text-xs font-extrabold text-primary py-1 uppercase tracking-wider text-start">
                        {administrationSettings?.structure_title ||
                          t("common.administration", "Administration")}
                      </span>
                      <div className="flex flex-col gap-1.5 border-s ps-2 ms-1 text-start text-xs text-gray-500 font-bold">
                        {administrationLinks.map((item) => (
                          <Link
                            key={item.slug}
                            to={item.path}
                            onClick={handleLinkClick}
                            className={`hover:text-primary py-0.5 ${language === "ar" ? "text-sm leading-normal font-semibold" : "leading-tight font-medium"}`}
                          >
                            {item.position || item.title || item.name}
                          </Link>
                        ))}
                      </div>
                    </div>

                    {/* Centres and departments */}
                    <div className="flex flex-col gap-1">
                      <span className="text-xs font-extrabold text-primary py-1 uppercase tracking-wider text-start">
                        {t("nav.centers")}
                      </span>
                      <div className="flex flex-col gap-1.5 border-s ps-2 pe-1 ms-1 text-start text-xs text-gray-500 font-bold max-h-75 overflow-y-auto scrollbar-thin">
                        <Link
                          to="/center/inclusive-it-center"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.inclusive-it-center.name")}
                        </Link>
                        <Link
                          to="/center/professional-development-center"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.professional-development-center.name")}
                        </Link>
                        <Link
                          to="/center/digital-educational-technologies"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.digital-educational-technologies.name")}
                        </Link>
                        <Link
                          to="/center/information-resource-center"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.information-resource-center.name")}
                        </Link>
                        <Link
                          to="/center/office-archive"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.office-archive.name")}
                        </Link>
                        <Link
                          to="/center/information-service"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.information-service.name")}
                        </Link>
                        <Link
                          to="/center/monitoring-internal-control"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.monitoring-internal-control.name")}
                        </Link>
                        <Link
                          to="/center/personnel-department"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.personnel-department.name")}
                        </Link>
                        <Link
                          to="/center/educational-quality-control"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.educational-quality-control.name")}
                        </Link>
                        <Link
                          to="/center/trade-union-committee"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.trade-union-committee.name")}
                        </Link>
                        <Link
                          to="/center/academic-affairs-management"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.academic-affairs-management.name")}
                        </Link>
                        <Link
                          to="/center/masters-department"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.masters-department.name")}
                        </Link>
                        <Link
                          to="/center/vocational-education-coordination"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.vocational-education-coordination.name")}
                        </Link>
                        <Link
                          to="/center/youth-spirituality-enlightenment"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.youth-spirituality-enlightenment.name")}
                        </Link>
                        <Link
                          to="/center/labor-protection-safety"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.labor-protection-safety.name")}
                        </Link>
                        <Link
                          to="/center/gifted-students-research"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.gifted-students-research.name")}
                        </Link>
                        <Link
                          to="/center/international-cooperation-department"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t(
                            "centers.international-cooperation-department.name",
                          )}
                        </Link>
                        <Link
                          to="/center/anti-corruption-compliance"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.anti-corruption-compliance.name")}
                        </Link>
                        <Link
                          to="/center/youth-union-organization"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.youth-union-organization.name")}
                        </Link>
                        <Link
                          to="/center/civil-and-labor-protection"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.civil-and-labor-protection.name")}
                        </Link>
                        <Link
                          to="/center/legal-service"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("centers.legal-service.name")}
                        </Link>
                      </div>
                    </div>

                    {/* Faculties */}
                    <div className="flex flex-col gap-1">
                      <span className="text-xs font-extrabold text-primary py-1 uppercase tracking-wider text-start">
                        {t("nav.faculties")}
                      </span>
                      <div className="flex flex-col gap-1.5 border-s ps-2 ms-1 text-start text-xs text-gray-500 font-bold">
                        <Link
                          to="/faculty/faculty-of-engineering"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("faculties.faculty-of-engineering.name")}
                        </Link>
                        <Link
                          to="/faculty/faculty-of-technology"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t("faculties.faculty-of-technology.name")}
                        </Link>
                        <Link
                          to="/faculty/faculty-of-natural-resources-management"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t(
                            "faculties.faculty-of-natural-resources-management.name",
                          )}
                        </Link>
                        <Link
                          to="/faculty/faculty-of-service-and-digitalization"
                          onClick={handleLinkClick}
                          className="hover:text-primary py-0.5"
                        >
                          {t(
                            "faculties.faculty-of-service-and-digitalization.name",
                          )}
                        </Link>
                      </div>
                    </div>
                  </div>
                )}
              </div>

              <Link
                to="/contact"
                onClick={handleLinkClick}
                className={`py-2 text-base transition-colors ${
                  location.pathname === "/contact"
                    ? "text-primary font-bold"
                    : ""
                }`}
              >
                {t("nav.contact")}
              </Link>

              {/* Mobile Auth Links */}
              <div className="border-t border-gray-100 pt-4 flex flex-col gap-3">
                <Link
                  to="/login"
                  onClick={handleLinkClick}
                  className="w-full text-center py-2.5 border border-gray-200 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors"
                >
                  {t("nav.login")}
                </Link>
                <Link
                  to="/register"
                  onClick={handleLinkClick}
                  className="w-full text-center py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary-hover transition-colors"
                >
                  {t("nav.register")}
                </Link>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </header>
  );
}
