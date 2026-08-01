import React, { useEffect, useState } from "react";
import { useParams, Link, useNavigate } from "react-router-dom";
import { Calendar, Eye, Search, Folder, Tag } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { newsService } from "../services/newsService";
import { formatLocalizedDate } from "../utils/dateFormat";

export default function NewsDetails() {
  const navigate = useNavigate();
  const { id } = useParams();
  const { language, t } = useLanguage();
  const [news, setNews] = useState(null);
  const [newsItems, setNewsItems] = useState([]);
  const [settings, setSettings] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;

    setLoading(true);
    Promise.all([
      newsService.getSettings(),
      newsService.getNewsItem(id, { view: 1 }),
      newsService.getNews({ per_page: 100 }),
    ])
      .then(([settingsData, item, list]) => {
        if (!active) return;
        setSettings(settingsData);
        setNews(item);
        setNewsItems(list.items);
      })
      .catch(() => {
        if (!active) return;
        setSettings(null);
        setNews(null);
        setNewsItems([]);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [id, language]);

  const formatNewsDate = (dateValue) => {
    return formatLocalizedDate(dateValue, language, t, {
      day: "2-digit",
      month: "long",
    });
  };

  const uniqueCategories = [...new Set(newsItems.map((p) => p.category))];
  const categories = uniqueCategories.map((cat) => {
    const name =
      cat.toLowerCase() === "news"
        ? settings?.news_label || cat
        : cat.toLowerCase() === "events"
          ? settings?.events_label || cat
          : cat;
    const count = newsItems.filter(
      (p) => p.category.toLowerCase() === cat.toLowerCase(),
    ).length;
    return { name, count, value: cat.toLowerCase() };
  });

  const recentWidgetNews = newsItems
    .filter((p) => p.id !== news?.id)
    .slice(0, settings?.recent_limit || 5);
  const paragraphs = Array.isArray(news?.paragraphs) ? news.paragraphs : [];
  const categoryLabel = (category) =>
    category?.toLowerCase() === "events"
      ? settings?.events_label || category
      : settings?.news_label || category;

  if (loading) {
    return null;
  }

  if (!news) {
    return (
      <div className="pt-24 bg-white">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-20 text-center">
          <p className="text-gray-500 font-semibold">
            {settings?.no_results_label || ""}
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="pt-24 bg-white">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          <div className="lg:col-span-8 flex flex-col gap-8 text-start">
            <article className="flex flex-col gap-6">
              <div className="rounded-3xl overflow-hidden shadow-lg aspect-video bg-gray-50 border border-gray-100">
                <img
                  src={news.img}
                  alt={news.title}
                  className="w-full h-full object-cover"
                  loading="lazy"
                  onError={(e) => {
                    e.currentTarget.style.display = "none";
                  }}
                />
              </div>

              <h1 className="text-2xl md:text-4xl font-extrabold text-navy leading-snug">
                {news.title}
              </h1>

              <div className="flex items-center gap-4 text-xs md:text-sm font-semibold text-gray-400 border-b border-gray-200/50 pb-4">
                <span className="flex items-center gap-1">
                  <Calendar className="w-4 h-4 text-primary" />
                  <span>{formatNewsDate(news.date)}</span>
                </span>
                <span className="flex items-center gap-1">
                  <Eye className="w-4 h-4 text-primary" />
                  <span>
                    {news.views} {settings?.views_label || ""}
                  </span>
                </span>
              </div>

              <div className="text-gray-500 text-sm md:text-base leading-relaxed flex flex-col gap-6">
                {paragraphs.map((p, idx) => (
                  <p key={idx} className="leading-relaxed">
                    {p}
                  </p>
                ))}
              </div>

              <div className="flex flex-wrap items-center gap-6 border-t border-gray-200/50 pt-4 text-xs md:text-sm text-gray-400 font-semibold">
                <span className="flex items-center gap-1.5">
                  <Folder className="w-4 h-4 text-primary" />
                  <span className="text-navy">{categoryLabel(news.category)}</span>
                </span>
                <span className="flex items-center gap-1.5">
                  <Tag className="w-4 h-4 text-primary" />
                  <div className="flex gap-2">
                    <Link
                      to={`/news?category=${news.category.toLowerCase()}`}
                      className="bg-gray-50 hover:bg-primary/5 hover:text-primary transition-colors border border-gray-100 px-2.5 py-1 rounded-lg text-gray-500 font-bold"
                    >
                      {categoryLabel(news.category)}
                    </Link>
                  </div>
                </span>
              </div>
            </article>
          </div>

          <div className="lg:col-span-4 flex flex-col gap-8 text-start">
            <div className="bg-primary-light border border-gray-100 p-6 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {settings?.search_title || ""}
              </h4>
              <div className="flex bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <input
                  id="news-details-search-input"
                  name="news_search"
                  type="text"
                  autoComplete="off"
                  placeholder={settings?.search_placeholder || ""}
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
                    <Link
                      to={`/news?category=${cat.value}`}
                      className="w-full flex items-center justify-between py-2 text-gray-500 hover:text-primary transition-all"
                    >
                      <span>{cat.name}</span>
                      <span className="bg-white px-2.5 py-1 rounded-lg border border-gray-100 text-xs text-gray-400 font-bold">
                        ({cat.count})
                      </span>
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {settings?.recent_title || ""}
              </h4>
              <div className="flex flex-col gap-5">
                {recentWidgetNews.map((item) => (
                  <div key={item.id} className="flex gap-4">
                    <img
                      src={item.img}
                      alt={item.title}
                      className="w-16 h-16 rounded-xl object-cover shrink-0 bg-gray-100 border border-white shadow-sm"
                      loading="lazy"
                      onError={(e) => {
                        e.currentTarget.style.display = "none";
                      }}
                    />
                    <div className="flex flex-col justify-center">
                      <h5 className="font-bold text-sm text-navy hover:text-primary transition-colors line-clamp-2 leading-tight">
                        <Link to={`/news/${item.id}`}>{item.title}</Link>
                      </h5>
                      <span className="text-xs text-gray-400 mt-1 font-semibold">
                        {formatNewsDate(item.date)}
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
