import React, { useState, useEffect } from "react";
import { Link, useLocation } from "react-router-dom";
import { Search, Calendar, Eye, ArrowRight, Sparkles } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { announcementsData } from "../data/announcementsData";
import { useLanguage } from "../context/LanguageContext";

export default function AnnouncementsPage() {
  const { t, language } = useLanguage();
  const location = useLocation();
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCategory, setSelectedCategory] = useState("all");

  useEffect(() => {
    window.scrollTo(0, 0);

    // Check query params
    const params = new URLSearchParams(location.search);
    const searchParam = params.get("search");
    const categoryParam = params.get("category");

    if (searchParam) {
      setSearchQuery(searchParam);
    }
    if (categoryParam) {
      setSelectedCategory(categoryParam.toLowerCase());
    }
  }, [location.search]);

  // Filter announcements
  const filteredAnnouncements = announcementsData.filter((item) => {
    const title = t(`announcements.items.${item.id}.title`, "").toLowerCase();
    const excerpt = t(
      `announcements.items.${item.id}.excerpt`,
      "",
    ).toLowerCase();
    const matchesSearch =
      title.includes(searchQuery.toLowerCase()) ||
      excerpt.includes(searchQuery.toLowerCase());
    const matchesCategory =
      selectedCategory === "all" ||
      item.category.toLowerCase() === selectedCategory.toLowerCase();
    return matchesSearch && matchesCategory;
  });

  const uniqueCategories = [
    "all",
    ...new Set(announcementsData.map((item) => item.category.toLowerCase())),
  ];
  const categories = uniqueCategories.map((cat) => {
    const name = t(
      `announcements.categories.${cat}`,
      cat.charAt(0).toUpperCase() + cat.slice(1),
    );
    const count =
      cat === "all"
        ? announcementsData.length
        : announcementsData.filter((p) => p.category.toLowerCase() === cat)
            .length;
    return { name, count, value: cat };
  });

  const recentAnnouncements = announcementsData.slice(0, 3);

  // Animation variants
  const containerVariants = {
    hidden: { opacity: 0 },
    show: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1,
      },
    },
  };

  const cardVariants = {
    hidden: { opacity: 0, y: 20 },
    show: { opacity: 1, y: 0, transition: { type: "spring", stiffness: 100 } },
  };

  return (
    <div className="pt-24 min-h-screen bg-slate-50/50">
      {/* Hero Section */}

      {/* Main Content */}
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
          {/* Left: Announcements List */}
          <div className="lg:col-span-8 flex flex-col gap-6">
            <AnimatePresence mode="popLayout">
              {filteredAnnouncements.length > 0 ? (
                <motion.div
                  variants={containerVariants}
                  initial="hidden"
                  animate="show"
                  className="flex flex-col gap-6"
                >
                  {filteredAnnouncements.map((item) => {
                    const title = t(`announcements.items.${item.id}.title`);
                    const excerpt = t(`announcements.items.${item.id}.excerpt`);
                    const translatedCategory = t(
                      `announcements.categories.${item.category.toLowerCase()}`,
                      item.category,
                    );

                    return (
                      <motion.article
                        key={item.id}
                        variants={cardVariants}
                        layout
                        className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col md:flex-row gap-6 p-6 group relative"
                      >
                        {/* Featured Badge */}
                        {item.important && (
                          <div
                            className={`absolute top-4 ${language === "ar" ? "left-4" : "right-4"} z-20 bg-amber-500 text-white text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-md shadow-xs flex items-center gap-1`}
                          >
                            <Sparkles className="w-2.5 h-2.5" />
                            {t("announcements.importantBadge", "IMPORTANT")}
                          </div>
                        )}

                        {/* Image area */}
                        <div className="w-full md:w-65 aspect-16/11 md:aspect-auto overflow-hidden rounded-2xl bg-gray-50 shrink-0 relative">
                          <img
                            src={item.image}
                            alt={title}
                            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                          />
                          <span
                            className={`absolute bottom-3 ${language === "ar" ? "right-3" : "left-3"} bg-navy/80 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg`}
                          >
                            {translatedCategory}
                          </span>
                        </div>

                        {/* Text area */}
                        <div className="flex flex-col justify-between grow py-1 text-start">
                          <div>
                            {/* Meta info */}
                            <div className="flex items-center gap-4 text-xs font-semibold text-gray-400 mb-3">
                              <span className="flex items-center gap-1">
                                <Calendar className="w-3.5 h-3.5 text-primary" />
                                {item.date}
                              </span>
                              <span className="flex items-center gap-1">
                                <Eye className="w-3.5 h-3.5" />
                                {item.views}{" "}
                                {t("announcements.viewsLabel", "views")}
                              </span>
                            </div>

                            {/* Title */}
                            <h2 className="text-lg md:text-xl font-extrabold text-navy group-hover:text-primary transition-colors duration-300 leading-snug mb-3">
                              <Link to={`/announcements/${item.id}`}>
                                {title}
                              </Link>
                            </h2>

                            {/* Excerpt */}
                            <p className="text-gray-500 text-sm leading-relaxed mb-4 line-clamp-2">
                              {excerpt}
                            </p>
                          </div>

                          {/* Read Details Button */}
                          <div>
                            <Link
                              to={`/announcements/${item.id}`}
                              className="inline-flex items-center gap-1.5 text-sm font-extrabold text-navy hover:text-primary transition-colors group-hover:underline"
                            >
                              {t("announcements.readMore", "Read Details")}
                              <ArrowRight
                                className={`w-4 h-4 transition-transform duration-300 ${language === "ar" ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                              />
                            </Link>
                          </div>
                        </div>
                      </motion.article>
                    );
                  })}
                </motion.div>
              ) : (
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  className="text-center py-20 bg-white rounded-3xl border border-gray-100 p-8"
                >
                  <p className="text-gray-500 font-semibold mb-3">
                    {t("announcements.noResults", "No announcements found")}
                  </p>
                  <button
                    onClick={() => {
                      setSearchQuery("");
                      setSelectedCategory("all");
                    }}
                    className="bg-primary/10 text-primary hover:bg-primary/20 text-xs font-extrabold px-5 py-2.5 rounded-xl transition-all"
                  >
                    {t("announcements.clearFilters", "Clear search filters")}
                  </button>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          {/* Right: Sidebar */}
          <div className="lg:col-span-4 flex flex-col gap-8 text-start">
            {/* Search Box */}
            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {t("announcements.searchTitle", "Search Announcements")}
              </h4>
              <div className="flex bg-slate-50 border border-gray-100 rounded-xl overflow-hidden hover:border-gray-200 transition-all">
                <input
                  type="text"
                  placeholder={t(
                    "announcements.searchPlaceholder",
                    "Search announcements...",
                  )}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="grow px-4 py-3 text-sm bg-transparent focus:outline-none"
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center bg-slate-100/50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>

            {/* Categories */}
            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-slate-50 pb-3">
                {t("announcements.categoriesTitle", "Categories")}
              </h4>
              <ul className="flex flex-col gap-2 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <button
                      onClick={() => setSelectedCategory(cat.value)}
                      className={`w-full flex items-center justify-between py-2.5 px-3 rounded-xl transition-all cursor-pointer ${
                        selectedCategory === cat.value
                          ? "bg-primary text-white font-bold"
                          : "text-gray-500 hover:bg-slate-50 hover:text-primary"
                      }`}
                    >
                      <span>{cat.name}</span>
                      <span
                        className={`px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold ${
                          selectedCategory === cat.value
                            ? "bg-white/20 text-white"
                            : "bg-slate-50 text-gray-400"
                        }`}
                      >
                        {cat.count}
                      </span>
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            {/* Recent Announcements Widget */}
            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-slate-50 pb-3">
                {t("announcements.recentTitle", "Recent Announcements")}
              </h4>
              <div className="flex flex-col gap-4">
                {recentAnnouncements.map((ann) => {
                  const title = t(`announcements.items.${ann.id}.title`);
                  return (
                    <div key={ann.id} className="flex gap-3 group">
                      <img
                        src={ann.image}
                        alt={title}
                        className="w-14 h-14 rounded-xl object-cover shrink-0 bg-gray-50 border border-slate-100"
                      />
                      <div className="flex flex-col justify-center text-start">
                        <h5 className="font-extrabold text-xs text-navy group-hover:text-primary transition-colors line-clamp-2 leading-snug">
                          <Link to={`/announcements/${ann.id}`}>{title}</Link>
                        </h5>
                        <span className="text-[10px] text-gray-400 mt-1 font-bold">
                          {ann.date}
                        </span>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
