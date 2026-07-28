import React, { useState, useEffect } from "react";
import { Link, useLocation } from "react-router-dom";
import {
  Search,
  Calendar,
  User,
  ArrowRight,
  Leaf,
  Mail,
  Award,
  Globe,
  TreePine,
  Users,
  Eye,
} from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { greenCampusService } from "../services/greenCampusService";

const ICON_MAP = {
  award: Award,
  tree: TreePine,
  leaf: Leaf,
  globe: Globe,
  users: Users,
};

export default function GreenCampusPage() {
  const { language } = useLanguage();
  const location = useLocation();
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCategory, setSelectedCategory] = useState("all");
  const [settings, setSettings] = useState({});
  const [stats, setStats] = useState([]);
  const [articles, setArticles] = useState([]);
  const [loading, setLoading] = useState(true);

  const formatArticleDate = (dateValue) => {
    const parsed = new Date(dateValue);
    if (Number.isNaN(parsed.getTime())) return dateValue;

    return new Intl.DateTimeFormat(language || undefined, {
      day: "numeric",
      month: "short",
      year: "numeric",
    }).format(parsed);
  };

  useEffect(() => {
    window.scrollTo(0, 0);
    const params = new URLSearchParams(location.search);
    const searchParam = params.get("search");
    const categoryParam = params.get("category");
    if (searchParam) setSearchQuery(searchParam);
    if (categoryParam) setSelectedCategory(categoryParam.toLowerCase());
  }, [location.search]);

  useEffect(() => {
    let active = true;
    setLoading(true);

    Promise.all([
      greenCampusService.getSettings(),
      greenCampusService.getStats(),
      greenCampusService.getArticles(),
    ])
      .then(([nextSettings, nextStats, nextArticles]) => {
        if (!active) return;
        setSettings(nextSettings);
        setStats(nextStats);
        setArticles(nextArticles);
      })
      .catch(() => {
        if (!active) return;
        setSettings({});
        setStats([]);
        setArticles([]);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [language]);

  const categoryLabels = settings.category_labels || {};
  const categoryLabel = (category) => categoryLabels[category] || category;

  const filteredArticles = articles.filter((article) => {
    const titleTrans = (article.title || "").toLowerCase();
    const excerptTrans = (article.excerpt || "").toLowerCase();
    const matchesSearch =
      titleTrans.includes(searchQuery.toLowerCase()) ||
      excerptTrans.includes(searchQuery.toLowerCase());
    const matchesCategory =
      selectedCategory === "all" ||
      article.category.toLowerCase() === selectedCategory.toLowerCase();
    return matchesSearch && matchesCategory;
  });

  const uniqueCategories = [
    "all",
    ...new Set(articles.map((a) => a.category.toLowerCase())),
  ];
  const categories = uniqueCategories.map((cat) => {
    const name = cat === "all" ? settings.all_label || "" : categoryLabel(cat);
    const count =
      cat === "all"
        ? articles.length
        : articles.filter(
            (a) => a.category.toLowerCase() === cat,
          ).length;
    return { name, count, value: cat };
  });

  const recentArticles = articles.slice(0, Number(settings.recent_limit || 4));

  if (loading) {
    return (
      <div className="pt-24 bg-white min-h-screen flex items-center justify-center">
        <Leaf className="w-8 h-8 text-emerald-600 animate-pulse" />
      </div>
    );
  }

  return (
    <div className="pt-20 bg-white">
      {/* Stats Banner */}
      <div className="bg-linear-to-r from-emerald-600 to-green-700 py-10">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {stats.map((stat, idx) => {
              const Icon = ICON_MAP[stat.icon] || Leaf;
              return (
                <div
                  key={idx}
                  className="flex flex-col items-center text-center text-white gap-2"
                >
                  <Icon className="w-8 h-8 text-emerald-200 mb-1" />
                  <span className="text-2xl md:text-3xl font-black tracking-tight">
                    {stat.value}
                  </span>
                  <span className="text-emerald-100 text-xs font-semibold leading-snug">
                    {stat.label}
                  </span>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-16 md:py-24 text-start">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          <div className="order-1 lg:hidden">
            <div className="bg-gray-50 border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-gray-200/50 pb-2">
                {settings.search_title || ""}
              </h4>
              <div className="relative">
                <input
                  id="green-campus-search-input-mobile"
                  name="green_campus_search_mobile"
                  type="text"
                  autoComplete="off"
                  placeholder={settings.search_placeholder || ""}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-3 rounded-xl bg-white border border-gray-200 focus:outline-none focus:border-emerald-500 text-sm font-semibold transition-colors"
                />
                <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" />
              </div>
            </div>
          </div>

          {/* Left: Green Campus Articles List */}
          <div className="order-2 lg:order-1 lg:col-span-8 flex flex-col gap-10">
            {filteredArticles.length > 0 ? (
              filteredArticles.map((article) => (
                <article
                  key={article.id}
                  className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col md:flex-row gap-0 group text-start"
                >
                  {/* Photo area */}
                  <Link
                    to={`/green-campus/${article.id}`}
                    className="w-full md:w-72 shrink-0 overflow-hidden block bg-gray-50"
                  >
                    <img
                      src={article.image}
                      alt={article.title}
                      className="w-full h-52 md:h-full object-cover transition-transform duration-500 group-hover:scale-105"
                      loading="lazy"
                      onError={(e) => {
                        e.currentTarget.style.display = "none";
                      }}
                    />
                  </Link>

                  {/* Content area */}
                  <div className="flex flex-col gap-3 grow justify-between p-6">
                    <div className="flex flex-col gap-3">
                      {/* Meta info */}
                      <div className="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold text-gray-400">
                        <span className="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                          {categoryLabel(article.category)}
                        </span>
                        <span className="flex items-center gap-1.5">
                          <Calendar className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                          {formatArticleDate(article.date)}
                        </span>
                        <span className="flex items-center gap-1.5">
                          <User className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                          {article.author}
                        </span>
                        {article.views && (
                          <span className="flex items-center gap-1.5">
                            <Eye className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                            {article.views.toLocaleString(language)}
                          </span>
                        )}
                      </div>

                      {/* Title */}
                      <h2 className="text-xl font-extrabold text-navy group-hover:text-emerald-600 transition-colors leading-tight">
                        <Link to={`/green-campus/${article.id}`}>
                          {article.title}
                        </Link>
                      </h2>

                      {/* Excerpt */}
                      <p className="text-gray-500 text-xs font-semibold leading-relaxed line-clamp-3">
                        {article.excerpt}
                      </p>

                      {/* Gallery preview */}
                      {article.gallery && article.gallery.length > 1 && (
                        <div className="flex gap-2 mt-1">
                          {article.gallery.slice(1, 4).map((img, i) => (
                            <img
                              key={i}
                              src={img}
                              alt=""
                              className="w-14 h-10 object-cover rounded-lg opacity-80 hover:opacity-100 transition-opacity"
                              onError={(e) => {
                                e.target.style.display = "none";
                              }}
                            />
                          ))}
                          {article.gallery.length > 4 && (
                            <div className="w-14 h-10 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-[10px] font-bold">
                              +{article.gallery.length - 4}
                            </div>
                          )}
                        </div>
                      )}
                    </div>

                    {/* Link */}
                    <Link
                      to={`/green-campus/${article.id}`}
                      className="pt-4 border-t border-gray-50 flex items-center justify-between text-xs font-extrabold text-emerald-600 group-hover:text-emerald-700 cursor-pointer"
                    >
                      <span>{settings.read_more_label || ""}</span>
                      <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1 rtl:rotate-180" />
                    </Link>
                  </div>
                </article>
              ))
            ) : (
              <div className="text-center py-12 border border-dashed border-gray-200 rounded-3xl">
                <Leaf className="w-12 h-12 text-gray-300 mx-auto mb-3" />
                <p className="text-gray-500 font-semibold">
                  {settings.no_results_label || ""}
                </p>
              </div>
            )}
          </div>

          {/* Right: Sidebar */}
          <div className="order-3 lg:order-2 lg:col-span-4 flex flex-col gap-8">
            {/* Search widget */}
            <div className="hidden lg:block bg-gray-50 border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-gray-200/50 pb-2">
                {settings.search_title || ""}
              </h4>
              <div className="relative">
                <input
                  id="green-campus-search-input"
                  name="green_campus_search"
                  type="text"
                  autoComplete="off"
                  placeholder={settings.search_placeholder || ""}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-3 rounded-xl bg-white border border-gray-200 focus:outline-none focus:border-emerald-500 text-sm font-semibold transition-colors"
                />
                <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" />
              </div>
            </div>

            {/* Categories widget */}
            <div className="bg-gray-50 border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-gray-200/50 pb-2">
                {settings.categories_title || ""}
              </h4>
              <ul className="flex flex-col gap-3 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <button
                      onClick={() => setSelectedCategory(cat.value)}
                      className={`w-full flex items-center justify-between py-1.5 transition-all text-start ${
                        selectedCategory === cat.value
                          ? "text-emerald-600 font-bold"
                          : "text-gray-500 hover:text-navy"
                      }`}
                    >
                      <span>{cat.name}</span>
                      <span className="text-[10px] bg-white border border-gray-200/80 text-gray-400 px-2 py-0.5 rounded-md font-bold shrink-0">
                        {cat.count}
                      </span>
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            {/* Recent Posts widget */}
            <div className="bg-gray-50 border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-2">
                {settings.recent_title || ""}
              </h4>
              <div className="flex flex-col gap-4">
                {recentArticles.map((article) => (
                  <div
                    key={article.id}
                    className="flex gap-4 items-start group"
                  >
                    <img
                      src={article.image}
                      alt={article.title}
                      className="w-16 h-12 object-cover rounded-lg bg-white shrink-0"
                      onError={(e) => {
                        e.currentTarget.style.display = "none";
                      }}
                    />
                    <div className="flex flex-col gap-1 text-start min-w-0">
                      <h5 className="text-xs font-bold text-navy group-hover:text-emerald-600 transition-colors leading-snug line-clamp-2">
                        <Link to={`/green-campus/${article.id}`}>
                          {article.title}
                        </Link>
                      </h5>
                      <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                        {formatArticleDate(article.date)}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Contact Callout (ECO-COMMITTEE) */}
            <div className="bg-linear-to-br from-emerald-600 to-emerald-700 text-white p-8 rounded-3xl flex flex-col gap-5 text-center shadow-lg shadow-emerald-600/10">
              <Leaf className="w-10 h-10 mx-auto text-emerald-100" />
              <div>
                <h4 className="text-lg font-bold mb-2">
                  {settings.callout_title || ""}
                </h4>
                <p className="text-emerald-100 text-xs leading-relaxed">
                  {settings.callout_description || ""}
                </p>
              </div>
              <a
                href={`mailto:${settings.callout_email || ""}`}
                className="bg-white text-emerald-600 hover:bg-emerald-50 px-6 py-3.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-2"
              >
                <Mail className="w-4 h-4" />
                {settings.callout_cta_label || ""}
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
