import React, { useEffect, useState } from "react";
import { useParams, Link, useNavigate } from "react-router-dom";
import { Calendar, Eye, Search, Folder, Tag, User, AlertCircle, Newspaper } from "lucide-react";
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
  const [error, setError] = useState("");

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;

    setLoading(true);
    setError("");
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
        setError(t("common.loadError"));
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [id, language, t]);

  const formatNewsDate = (dateValue) => {
    return formatLocalizedDate(dateValue, language, t, {
      day: "2-digit",
      month: "long",
    });
  };

  const uniqueCategories = [...new Set(newsItems.map((p) => p.category || "news"))];
  const categories = uniqueCategories.map((cat) => {
    const normalizedCat = String(cat || "news").toLowerCase();
    const name =
      normalizedCat === "news"
        ? settings?.news_label || cat
        : normalizedCat === "events"
          ? settings?.events_label || cat
          : cat;
    const count = newsItems.filter(
      (p) => String(p.category || "").toLowerCase() === normalizedCat,
    ).length;
    return { name, count, value: normalizedCat };
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

  const publisher = news.publisher || null;
  const publisherRoute = publisher?.slug ? `/publishers/${publisher.slug}` : "";

  return (
    <div className="pt-24 bg-white overflow-x-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 min-w-0">
        <div className="grid min-w-0 grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
          <div className="lg:col-span-8 flex min-w-0 flex-col gap-8 text-start">
            <article className="flex min-w-0 flex-col gap-6">
              <div className="rounded-3xl overflow-hidden shadow-lg aspect-video bg-primary/10 border border-gray-100">
                {news.img ? (
                  <img
                    src={news.img}
                    alt={news.title}
                    className="w-full h-full object-cover"
                    loading="lazy"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-primary">
                    <Newspaper className="w-14 h-14" />
                  </div>
                )}
              </div>

              <h1 className="text-2xl md:text-4xl font-extrabold text-navy leading-snug break-words">
                {news.title}
              </h1>

              <div className="flex min-w-0 flex-wrap items-center gap-x-4 gap-y-2 text-xs md:text-sm font-semibold text-gray-400 border-b border-gray-200/50 pb-4">
                <span className="flex min-w-0 items-center gap-1">
                  <Calendar className="w-4 h-4 text-primary" />
                  <span className="truncate">{formatNewsDate(news.date)}</span>
                </span>
                <span className="flex items-center gap-1">
                  <Eye className="w-4 h-4 text-primary" />
                  <span>
                    {news.views} {settings?.views_label || ""}
                  </span>
                </span>
                {publisher?.name && (
                  <span className="flex min-w-0 items-center gap-1">
                    <User className="w-4 h-4 text-primary" />
                    {publisherRoute ? (
                      <Link to={publisherRoute} className="min-w-0 truncate hover:text-primary transition-colors">
                        {publisher.name}
                      </Link>
                    ) : (
                      <span>{publisher.name}</span>
                    )}
                  </span>
                )}
              </div>

              <div className="text-gray-500 text-sm md:text-base leading-relaxed flex min-w-0 flex-col gap-6 break-words">
                {paragraphs.length > 0 ? (
                  paragraphs.map((p, idx) => (
                    <p key={idx} className="leading-relaxed break-words">
                      {p}
                    </p>
                  ))
                ) : (
                  <p className="leading-relaxed break-words">{news.description}</p>
                )}
              </div>

              <div className="flex min-w-0 flex-wrap items-center gap-4 md:gap-6 border-t border-gray-200/50 pt-4 text-xs md:text-sm text-gray-400 font-semibold">
                <span className="flex items-center gap-1.5">
                  <Folder className="w-4 h-4 text-primary" />
                  <span className="text-navy">{categoryLabel(news.category)}</span>
                </span>
                <span className="flex items-center gap-1.5">
                  <Tag className="w-4 h-4 text-primary" />
                  <div className="flex min-w-0 gap-2">
                    <Link
                      to={`/news?category=${String(news.category || "news").toLowerCase()}`}
                      className="min-w-0 break-words bg-gray-50 hover:bg-primary/5 hover:text-primary transition-colors border border-gray-100 px-2.5 py-1 rounded-lg text-gray-500 font-bold"
                    >
                      {categoryLabel(news.category)}
                    </Link>
                  </div>
                </span>
              </div>
            </article>
          </div>

          <div className="lg:col-span-4 flex min-w-0 flex-col gap-8 text-start">
            <div className="bg-primary-light border border-gray-100 p-6 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {settings?.search_title || ""}
              </h4>
              <div className="flex min-w-0 bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <input
                  id="news-details-search-input"
                  name="news_search"
                  type="text"
                  autoComplete="off"
                  placeholder={settings?.search_placeholder || ""}
                  className="min-w-0 grow px-4 py-3 text-sm focus:outline-none bg-white text-gray-700 w-full"
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
                      className="w-full flex min-w-0 items-center justify-between gap-3 py-2 text-gray-500 hover:text-primary transition-all"
                    >
                      <span className="min-w-0 break-words">{cat.name}</span>
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
                  <div key={item.id} className="flex min-w-0 gap-4">
                    {item.img ? (
                      <img
                        src={item.img}
                        alt={item.title}
                        className="w-16 h-16 rounded-xl object-cover shrink-0 bg-gray-100 border border-white shadow-sm"
                        loading="lazy"
                      />
                    ) : (
                      <div className="w-16 h-16 rounded-xl shrink-0 bg-primary/10 border border-primary/10 flex items-center justify-center text-primary">
                        <Newspaper className="w-5 h-5" />
                      </div>
                    )}
                    <div className="flex flex-col justify-center min-w-0">
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
