import React, { useEffect, useMemo, useState } from "react";
import { Link, useLocation } from "react-router-dom";
import { Search, Calendar, Eye, ArrowRight, Sparkles, User, AlertCircle, Megaphone } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { announcementService } from "../services/announcementService";
import { formatLocalizedDate } from "../utils/dateFormat";

export default function AnnouncementsPage() {
  const { language, isRtl, t } = useLanguage();
  const location = useLocation();
  const [announcements, setAnnouncements] = useState([]);
  const [settings, setSettings] = useState(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCategory, setSelectedCategory] = useState("all");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    window.scrollTo(0, 0);

    const params = new URLSearchParams(location.search);
    setSearchQuery(params.get("search") || "");
    setSelectedCategory((params.get("category") || "all").toLowerCase());
  }, [location.search]);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    setError("");
    Promise.all([
      announcementService.getSettings().catch(() => null),
      announcementService
        .getAnnouncements({ per_page: 100 })
        .catch(() => ({ items: [] })),
    ])
      .then(([settingsData, listData]) => {
        if (!mounted) return;
        setSettings(settingsData);
        setAnnouncements(listData.items || []);
      })
      .catch(() => {
        if (!mounted) return;
        setSettings(null);
        setAnnouncements([]);
        setError(t("common.loadError"));
      })
      .finally(() => {
        if (mounted) setLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, [language, t]);

  const formatDate = (value) => {
    return formatLocalizedDate(value, language, t, {
      month: "short",
      day: "2-digit",
    });
  };

  const filteredAnnouncements = useMemo(() => {
    const query = searchQuery.trim().toLowerCase();
    return announcements.filter((item) => {
      const matchesSearch =
        !query ||
        String(item.title || "").toLowerCase().includes(query) ||
        String(item.excerpt || "").toLowerCase().includes(query) ||
        String(item.category_label || "").toLowerCase().includes(query);
      const matchesCategory =
        selectedCategory === "all" ||
        String(item.category || "").toLowerCase() === selectedCategory.toLowerCase();
      return matchesSearch && matchesCategory;
    });
  }, [announcements, searchQuery, selectedCategory]);

  const categories = useMemo(() => {
    const grouped = announcements.reduce(
      (acc, item) => {
        const value = String(item.category || "general").toLowerCase();
        if (!acc[value]) {
          acc[value] = { name: item.category_label, count: 0, value };
        }
        acc[value].count += 1;
        return acc;
      },
      {
        all: {
          name: settings?.all_label || "",
          count: announcements.length,
          value: "all",
        },
      },
    );
    return Object.values(grouped);
  }, [announcements, settings?.all_label]);

  const recentAnnouncements = announcements.slice(0, Number(settings?.recent_limit || 3));
  const importantSlugs = new Set(
    announcements
      .filter((item) => item.important)
      .slice(0, Number(settings?.important_limit || 3))
      .map((item) => item.slug),
  );

  const containerVariants = {
    hidden: { opacity: 0 },
    show: { opacity: 1, transition: { staggerChildren: 0.1 } },
  };

  const cardVariants = {
    hidden: { opacity: 0, y: 20 },
    show: { opacity: 1, y: 0, transition: { type: "spring", stiffness: 100 } },
  };

  if (loading) {
    return null;
  }

  if (error) {
    return (
      <div className="pt-24 min-h-screen bg-slate-50/50 flex items-center justify-center px-4">
        <div className="flex items-center gap-3 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-bold text-red-600">
          <AlertCircle className="w-5 h-5 shrink-0" />
          <span>{error}</span>
        </div>
      </div>
    );
  }

  return (
    <div className="pt-24 min-h-screen bg-slate-50/50 overflow-x-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 min-w-0">
        <div className="grid min-w-0 grid-cols-1 lg:grid-cols-12 gap-10 items-start">
          <div className="order-1 lg:hidden">
            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs text-start">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {settings?.search_title || ""}
              </h4>
              <div className="flex min-w-0 bg-slate-50 border border-gray-100 rounded-xl overflow-hidden hover:border-gray-200 transition-all">
                <input
                  id="announcements-search-mobile"
                  name="announcements_search_mobile"
                  type="text"
                  placeholder={settings?.search_placeholder || ""}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="min-w-0 grow px-4 py-3 text-sm bg-transparent focus:outline-none"
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center bg-slate-100/50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>
          </div>

          <div className="order-2 lg:order-1 lg:col-span-8 flex min-w-0 flex-col gap-6">
            <AnimatePresence mode="popLayout">
              {filteredAnnouncements.length > 0 ? (
                <motion.div
                  variants={containerVariants}
                  initial="hidden"
                  animate="show"
                  className="flex flex-col gap-6"
                >
                  {filteredAnnouncements.map((item) => (
                    <motion.article
                      key={item.slug}
                      variants={cardVariants}
                      layout
                      className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col md:flex-row gap-6 p-5 sm:p-6 group relative min-w-0"
                    >
                      {importantSlugs.has(item.slug) && (
                        <div
                          className={`absolute top-4 ${isRtl ? "left-4" : "right-4"} z-20 bg-amber-500 text-white text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-md shadow-xs flex items-center gap-1`}
                        >
                          <Sparkles className="w-2.5 h-2.5" />
                          {settings?.important_label || ""}
                        </div>
                      )}

                      <div className="w-full md:w-65 aspect-16/11 md:aspect-auto overflow-hidden rounded-2xl bg-primary/10 shrink-0 relative">
                        {item.image ? (
                          <img
                            src={item.image}
                            alt={item.title}
                            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                          />
                        ) : (
                          <div className="w-full h-full flex items-center justify-center text-primary">
                            <Megaphone className="w-10 h-10" />
                          </div>
                        )}
                        <span
                          className={`absolute bottom-3 ${isRtl ? "right-3" : "left-3"} bg-navy/80 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg`}
                        >
                          {item.category_label}
                        </span>
                      </div>

                      <div className="flex min-w-0 flex-col justify-between grow py-1 text-start">
                        <div>
                          <div className="flex min-w-0 flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold text-gray-400 mb-3">
                            <span className="flex min-w-0 items-center gap-1">
                              <Calendar className="w-3.5 h-3.5 text-primary" />
                              <span className="truncate">{formatDate(item.date)}</span>
                            </span>
                            <span className="flex items-center gap-1">
                              <Eye className="w-3.5 h-3.5" />
                          {item.views} {settings?.views_label || ""}
                            </span>
                            {item.publisher?.name && (
                              <span className="flex min-w-0 items-center gap-1">
                                <User className="w-3.5 h-3.5 text-primary" />
                                {item.publisher.slug ? (
                                  <Link to={`/publishers/${item.publisher.slug}`} className="min-w-0 truncate hover:text-primary transition-colors">
                                    {item.publisher.name}
                                  </Link>
                                ) : (
                                  item.publisher.name
                                )}
                              </span>
                            )}
                          </div>

                          <h2 className="text-lg md:text-xl font-extrabold text-navy group-hover:text-primary transition-colors duration-300 leading-snug mb-3 break-words">
                            <Link to={`/announcements/${item.slug}`}>{item.title}</Link>
                          </h2>

                          <p className="text-gray-500 text-sm leading-relaxed mb-4 line-clamp-2 break-words">
                            {item.excerpt}
                          </p>
                        </div>

                        <div>
                          <Link
                            to={`/announcements/${item.slug}`}
                            className="inline-flex items-center gap-1.5 text-sm font-extrabold text-navy hover:text-primary transition-colors group-hover:underline"
                          >
                            {settings?.read_details_label || ""}
                            <ArrowRight
                              className={`w-4 h-4 transition-transform duration-300 ${isRtl ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                            />
                          </Link>
                        </div>
                      </div>
                    </motion.article>
                  ))}
                </motion.div>
              ) : (
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  className="text-center py-20 bg-white rounded-3xl border border-gray-100 p-8"
                >
                  <p className="text-gray-500 font-semibold mb-3">
                    {settings?.no_results_label || ""}
                  </p>
                  <button
                    onClick={() => {
                      setSearchQuery("");
                      setSelectedCategory("all");
                    }}
                    className="bg-primary/10 text-primary hover:bg-primary/20 text-xs font-extrabold px-5 py-2.5 rounded-xl transition-all"
                  >
                    {settings?.clear_filters_label || ""}
                  </button>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          <div className="order-3 lg:order-2 lg:col-span-4 flex min-w-0 flex-col gap-8 text-start">
            <div className="hidden lg:block bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {settings?.search_title || ""}
              </h4>
              <div className="flex min-w-0 bg-slate-50 border border-gray-100 rounded-xl overflow-hidden hover:border-gray-200 transition-all">
                <input
                  id="announcements-search"
                  name="announcements_search"
                  type="text"
                  placeholder={settings?.search_placeholder || ""}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="min-w-0 grow px-4 py-3 text-sm bg-transparent focus:outline-none"
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center bg-slate-100/50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>

            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-slate-50 pb-3">
                {settings?.categories_title || ""}
              </h4>
              <ul className="flex flex-col gap-2 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <button
                      onClick={() => setSelectedCategory(cat.value)}
                      className={`w-full flex items-center justify-between gap-3 py-2.5 px-3 rounded-xl transition-all cursor-pointer text-start ${
                        selectedCategory === cat.value
                          ? "bg-primary text-white font-bold"
                          : "text-gray-500 hover:bg-slate-50 hover:text-primary"
                      }`}
                    >
                      <span className="min-w-0 break-words">{cat.name}</span>
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

            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-slate-50 pb-3">
                {settings?.recent_title || ""}
              </h4>
              <div className="flex flex-col gap-4">
                {recentAnnouncements.map((ann) => (
                  <div key={ann.slug} className="flex min-w-0 gap-3 group">
                    {ann.image ? (
                      <img
                        src={ann.image}
                        alt={ann.title}
                        className="w-14 h-14 rounded-xl object-cover shrink-0 bg-gray-50 border border-slate-100"
                      />
                    ) : (
                      <div className="w-14 h-14 rounded-xl shrink-0 bg-primary/10 border border-primary/10 flex items-center justify-center text-primary">
                        <Megaphone className="w-5 h-5" />
                      </div>
                    )}
                    <div className="flex flex-col justify-center text-start min-w-0">
                      <h5 className="font-extrabold text-xs text-navy group-hover:text-primary transition-colors line-clamp-2 leading-snug">
                        <Link to={`/announcements/${ann.slug}`}>{ann.title}</Link>
                      </h5>
                      <span className="text-[10px] text-gray-400 mt-1 font-bold">
                        {formatDate(ann.date)}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
