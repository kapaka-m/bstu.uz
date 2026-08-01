import React, { useState, useEffect } from "react";
import { useLocation, Link, useNavigate } from "react-router-dom";
import { Search, Calendar, ArrowRight } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { newsService } from "../services/newsService";
import { formatLocalizedDate } from "../utils/dateFormat";

export default function NewsPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { language, isRtl, t } = useLanguage();
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCategory, setSelectedCategory] = useState("all");
  const [newsItems, setNewsItems] = useState([]);
  const [settings, setSettings] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);

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

  useEffect(() => {
    let active = true;

    setLoading(true);
    Promise.all([newsService.getSettings(), newsService.getNews({ per_page: 100 })])
      .then(([settingsData, payload]) => {
        if (!active) return;
        setSettings(settingsData);
        setNewsItems(payload.items);
      })
      .catch(() => {
        if (active) {
          setSettings(null);
          setNewsItems([]);
        }
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [language]);

  const formatNewsDate = (dateValue) => {
    return formatLocalizedDate(dateValue, language, t, {
      day: "2-digit",
      month: "long",
    });
  };

  const filteredNews = newsItems.filter((item) => {
    const title = item.title || "";
    const desc = item.description || "";
    const matchesSearch =
      title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      desc.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCategory =
      selectedCategory === "all" ||
      item.category.toLowerCase() === selectedCategory.toLowerCase();
    return matchesSearch && matchesCategory;
  });

  const uniqueCategories = [
    "all",
    ...new Set(newsItems.map((item) => item.category.toLowerCase())),
  ];
  const categories = uniqueCategories.map((cat) => {
    const name =
      cat === "all"
        ? settings?.all_news_label || ""
        : cat === "events"
          ? settings?.events_label || cat
          : settings?.news_label || cat;
    const count =
      cat === "all"
        ? newsItems.length
        : newsItems.filter((p) => p.category.toLowerCase() === cat).length;
    return { name, count, value: cat };
  });

  const categoryLabel = (category) =>
    category?.toLowerCase() === "events"
      ? settings?.events_label || category
      : settings?.news_label || category;

  const recentNews = newsItems.slice(0, settings?.recent_limit || 5);

  if (loading) {
    return null;
  }

  return (
    <div className="pt-20 bg-white">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-16 md:py-24">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          <div className="order-1 lg:hidden">
            <div className="bg-primary-light border border-gray-100 p-6 rounded-3xl text-start">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {settings?.search_title || ""}
              </h4>
              <div className="flex bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <input
                  id="news-search-input-mobile"
                  name="news_search_mobile"
                  type="text"
                  autoComplete="off"
                  placeholder={settings?.search_placeholder || ""}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="grow px-4 py-3 text-sm focus:outline-none bg-white text-gray-700 w-full"
                  onKeyDown={(e) => {
                    if (e.key === "Enter") {
                      navigate(`/news?search=${e.target.value}`);
                    }
                  }}
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center border-s border-gray-100 bg-gray-50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>
          </div>

          <div className="order-2 lg:order-1 lg:col-span-8 flex flex-col gap-8">
            {filteredNews.length > 0 ? (
              filteredNews.map((item) => (
                <article
                  key={item.id}
                  className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col md:flex-row gap-6 p-6 group text-start"
                >
                  <div className="w-full md:w-70 aspect-16/11 md:aspect-auto overflow-hidden rounded-2xl bg-gray-50 shrink-0 relative">
                    <Link to={`/news/${item.id}`}>
                      <img
                        src={item.img}
                        alt={item.title}
                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                        onError={(e) => {
                          e.currentTarget.style.display = "none";
                        }}
                      />
                    </Link>
                    <span className="absolute top-3 left-3 rtl:left-auto rtl:right-3 bg-primary text-white text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm">
                      {categoryLabel(item.category)}
                    </span>
                  </div>

                  <div className="flex flex-col justify-between grow py-2">
                    <div>
                      <div className="flex items-center gap-4 text-xs font-semibold text-gray-400 mb-3">
                        <span className="flex items-center gap-1">
                          <Calendar className="w-3.5 h-3.5 text-primary" />
                          {formatNewsDate(item.date)}
                        </span>
                      </div>

                      <h2 className="text-xl font-extrabold text-navy group-hover:text-primary transition-colors duration-300 leading-snug mb-3">
                        <Link
                          to={`/news/${item.id}`}
                          className="hover:underline"
                        >
                          {item.title}
                        </Link>
                      </h2>

                      <p className="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-3">
                        {item.description}
                      </p>
                    </div>

                    <div>
                      <Link
                        to={`/news/${item.id}`}
                        className="inline-flex items-center gap-1.5 text-sm font-bold text-navy hover:text-primary transition-colors group-hover:underline"
                      >
                        {settings?.read_details_label || ""}
                        <ArrowRight
                          className={`w-4 h-4 transition-transform duration-300 ${isRtl ? "rotate-180 group-hover:-translate-x-0.5" : "group-hover:translate-x-0.5"}`}
                        />
                      </Link>
                    </div>
                  </div>
                </article>
              ))
            ) : (
              <div className="text-center py-20 bg-gray-50 rounded-3xl border border-gray-100">
                <p className="text-gray-500 font-semibold mb-2">
                  {settings?.no_results_label || ""}
                </p>
                <button
                  onClick={() => {
                    setSearchQuery("");
                    setSelectedCategory("all");
                  }}
                  className="text-primary hover:underline text-sm font-bold cursor-pointer"
                >
                  {settings?.clear_filters_label || ""}
                </button>
              </div>
            )}
          </div>

          <div className="order-3 lg:order-2 lg:col-span-4 flex flex-col gap-8 text-start">
            <div className="hidden lg:block bg-primary-light border border-gray-100 p-6 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {settings?.search_title || ""}
              </h4>
              <div className="flex bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <input
                  id="news-search-input"
                  name="news_search"
                  type="text"
                  autoComplete="off"
                  placeholder={settings?.search_placeholder || ""}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="grow px-4 py-3 text-sm focus:outline-none bg-white text-gray-700 w-full"
                  onKeyDown={(e) => {
                    if (e.key === "Enter") {
                      navigate(`/news?search=${e.target.value}`);
                    }
                  }}
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center border-s border-gray-100 bg-gray-50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>

            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {settings?.categories_title || ""}
              </h4>
              <ul className="flex flex-col gap-3 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <button
                      onClick={() => setSelectedCategory(cat.value)}
                      className={`w-full flex items-center justify-between py-2 transition-all cursor-pointer ${
                        selectedCategory === cat.value
                          ? "text-primary font-bold ps-1"
                          : "text-gray-500 hover:text-primary hover:ps-1"
                      }`}
                    >
                      <span>{cat.name}</span>
                      <span className="bg-white px-2.5 py-1 rounded-lg border border-gray-100 text-xs text-gray-400 font-bold">
                        {cat.count}
                      </span>
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {settings?.recent_title || ""}
              </h4>
              <div className="flex flex-col gap-5">
                {recentNews.map((news) => (
                  <div key={news.id} className="flex gap-4">
                    <img
                      src={news.img}
                      alt={news.title}
                      className="w-16 h-16 rounded-xl object-cover shrink-0 bg-gray-100 border border-white shadow-sm"
                      loading="lazy"
                      onError={(e) => {
                        e.currentTarget.style.display = "none";
                      }}
                    />
                    <div className="flex flex-col justify-center">
                      <h5 className="font-bold text-sm text-navy hover:text-primary transition-colors line-clamp-2 leading-tight">
                        <Link to={`/news/${news.id}`}>{news.title}</Link>
                      </h5>
                      <span className="text-xs text-gray-400 mt-1 font-semibold">
                        {formatNewsDate(news.date)}
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
