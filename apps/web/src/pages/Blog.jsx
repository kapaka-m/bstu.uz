import React, { useState, useEffect, useMemo } from "react";
import { Link, useLocation } from "react-router-dom";
import { Search, Calendar, User, ArrowRight, AlertCircle, BookOpen } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { blogService } from "../services/blogService";
import { formatLocalizedDate } from "../utils/dateFormat";

const asText = (value, fallback = "") => {
  if (typeof value === "string" || typeof value === "number") {
    return String(value);
  }
  if (Array.isArray(value)) {
    return value.filter(Boolean).join(" ");
  }
  if (value && typeof value === "object") {
    return Object.values(value).filter(Boolean).join(" ");
  }
  return fallback;
};

const labelText = (value, fallback) => asText(value, asText(fallback));

export default function Blog() {
  const location = useLocation();
  const { language, isRtl, t } = useLanguage();
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCategory, setSelectedCategory] = useState("all");
  const [posts, setPosts] = useState([]);
  const [settings, setSettings] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

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

    if (location.state) {
      if (location.state.category) {
        setSelectedCategory(location.state.category.toLowerCase());
      }
      if (location.state.search) {
        setSearchQuery(location.state.search);
      }
    }
  }, [location.state, location.search]);

  useEffect(() => {
    let alive = true;

    const loadBlog = async () => {
      try {
        setLoading(true);
        setError("");
        const [nextSettings, response] = await Promise.all([
          blogService.getSettings(),
          blogService.getBlog({ per_page: 100, page: 1 }),
        ]);
        if (alive) {
          setSettings(nextSettings);
          setPosts(response.items || []);
        }
      } catch {
        if (alive) {
          setSettings({});
          setPosts([]);
          setError(t("common.loadError"));
        }
      } finally {
        if (alive) setLoading(false);
      }
    };

    loadBlog();

    return () => {
      alive = false;
    };
  }, [language, t]);

  const filteredPosts = useMemo(() => {
    return posts.filter((post) => {
      const query = searchQuery.toLowerCase();
      const title = asText(post.title);
      const excerpt = asText(post.excerpt);
      const author = asText(post.author);
      const category = asText(post.category);
      const categoryLabel = asText(post.categoryLabel);
      const matchesSearch =
        title.toLowerCase().includes(query) ||
        excerpt.toLowerCase().includes(query) ||
        author.toLowerCase().includes(query) ||
        categoryLabel.toLowerCase().includes(query);
      const matchesCategory =
        selectedCategory === "all" ||
        category.toLowerCase() === selectedCategory.toLowerCase();

      return matchesSearch && matchesCategory;
    });
  }, [posts, searchQuery, selectedCategory]);

  const categories = useMemo(() => {
    const labelByValue = posts.reduce((labels, post) => {
      const value = asText(post.category).toLowerCase();
      if (value && !labels[value]) {
        labels[value] = asText(post.categoryLabel, asText(post.category));
      }
      return labels;
    }, {});
    const unique = ["all", ...Object.keys(labelByValue)];
    return unique.map((cat) => ({
      name:
        cat === "all"
          ? labelText(settings.all_blog_label, "")
          : labelByValue[cat],
      count:
        cat === "all"
          ? posts.length
          : posts.filter((post) => asText(post.category).toLowerCase() === cat)
              .length,
      value: cat,
    }));
  }, [posts, settings.all_blog_label]);
  const formatPostDate = (value) =>
    formatLocalizedDate(value, language, t, {
      day: "numeric",
      month: "short",
    });

  const recentPosts = posts.slice(0, Number(settings.recent_limit || 3));
  const tags =
    Array.isArray(settings.tags) && settings.tags.length > 0
      ? settings.tags
          .map((tag) => {
            if (tag && typeof tag === "object") {
              return {
                value: asText(tag.value || tag.label).toLowerCase(),
                label: asText(tag.label || tag.value),
              };
            }
            const value = asText(tag);
            return { value: value.toLowerCase(), label: value };
          })
          .filter((tag) => tag.value && tag.label)
      : categories
          .filter((cat) => cat.value !== "all")
          .map((cat) => ({ value: cat.value, label: cat.name }));

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
    <div className="pt-20 bg-white overflow-x-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 min-w-0">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start min-w-0">
          <div className="order-1 lg:hidden min-w-0">
            <div className="bg-primary-light border border-gray-100 p-5 sm:p-6 rounded-3xl min-w-0">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {labelText(settings.search_title, "")}
              </h4>
              <div className="flex min-w-0 bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <input
                  id="blog-search-input-mobile"
                  name="blog_search_mobile"
                  type="text"
                  autoComplete="off"
                  placeholder={labelText(
                    settings.search_placeholder,
                    "",
                  )}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="min-w-0 grow px-4 py-3 text-sm focus:outline-none"
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center border-s border-gray-100 bg-gray-50 shrink-0">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>
          </div>

          {/* Left: Blog Posts List */}
          <div className="order-2 lg:order-1 lg:col-span-8 flex min-w-0 flex-col gap-10">
            {filteredPosts.length > 0 ? (
              filteredPosts.map((post) => (
                <article
                  key={post.slug || post.id}
                  className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col md:flex-row gap-6 p-5 sm:p-6 group min-w-0 text-start"
                >
                  {/* Photo area */}
                  <div className="w-full md:w-70 aspect-16/11 md:aspect-auto overflow-hidden rounded-2xl bg-primary/10 shrink-0">
                    {post.image ? (
                      <img
                        src={post.image}
                        alt={asText(post.title)}
                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-primary">
                        <BookOpen className="w-10 h-10" />
                      </div>
                    )}
                  </div>

                  {/* Text area */}
                  <div className="flex flex-col justify-between grow py-2 min-w-0">
                    <div>
                      {/* Meta */}
                      <div className="flex min-w-0 flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold text-gray-400 mb-3">
                        <span className="flex min-w-0 items-center gap-1">
                          <Calendar className="w-3.5 h-3.5" />
                          <span className="truncate">{formatPostDate(post.date)}</span>
                        </span>
                        <span className="flex min-w-0 items-center gap-1">
                          <User className="w-3.5 h-3.5" />
                          {post.department?.slug ? (
                            <Link to={`/publishers/${post.department.slug}`} className="min-w-0 truncate hover:text-primary transition-colors">
                              {asText(post.department.name, asText(post.author))}
                            </Link>
                          ) : (
                            <span className="min-w-0 truncate">{asText(post.author)}</span>
                          )}
                        </span>
                      </div>

                      {/* Title */}
                      <h2 className="text-xl md:text-2xl font-bold text-navy group-hover:text-primary transition-colors duration-300 leading-snug mb-3 break-words">
                        <Link to={`/blog/${post.slug}`}>
                          {asText(post.title)}
                        </Link>
                      </h2>

                      {/* Excerpt */}
                      <p className="text-gray-500 text-sm md:text-base leading-relaxed line-clamp-3 mb-6 break-words">
                        {asText(post.excerpt)}
                      </p>
                    </div>

                    {/* Read More */}
                    <div>
                      <Link
                        to={`/blog/${post.slug}`}
                        className="inline-flex items-center gap-1.5 text-sm font-bold text-navy hover:text-primary transition-colors group-hover:text-primary"
                      >
                        {labelText(
                          settings.read_more_label,
                          "",
                        )}
                        <ArrowRight
                          className={`w-4 h-4 transition-transform duration-300 ${isRtl ? "rotate-180 group-hover:-translate-x-1" : "group-hover:translate-x-1"}`}
                        />
                      </Link>
                    </div>
                  </div>
                </article>
              ))
            ) : (
              <div className="text-center py-20 bg-gray-50 rounded-3xl border border-gray-100">
                <p className="text-gray-500 font-semibold mb-2">
                  {labelText(
                    settings.no_results_label,
                    "",
                  )}
                </p>
                <button
                  onClick={() => {
                    setSearchQuery("");
                    setSelectedCategory("all");
                  }}
                  className="text-primary hover:underline text-sm font-bold"
                >
                  {labelText(
                    settings.clear_filters_label,
                    "",
                  )}
                </button>
              </div>
            )}
          </div>

          {/* Right: Sidebar */}
          <div className="order-3 lg:order-2 lg:col-span-4 flex min-w-0 flex-col gap-8">
            {/* Search Box */}
            <div className="hidden lg:block bg-primary-light border border-gray-100 p-6 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {labelText(settings.search_title, "")}
              </h4>
              <div className="flex min-w-0 bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <input
                  id="blog-search-input"
                  name="blog_search"
                  type="text"
                  autoComplete="off"
                  placeholder={labelText(
                    settings.search_placeholder,
                    "",
                  )}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="min-w-0 grow px-4 py-3 text-sm focus:outline-none"
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center border-s border-gray-100 bg-gray-50 shrink-0">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>

            {/* Categories */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {labelText(
                  settings.categories_title,
                  "",
                )}
              </h4>
              <ul className="flex flex-col gap-3 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <button
                      onClick={() => setSelectedCategory(cat.value)}
                      className={`w-full flex min-w-0 items-center justify-between gap-3 py-2 transition-all text-start ${
                        selectedCategory === cat.value
                          ? "text-primary font-bold ps-1"
                          : "text-gray-500 hover:text-primary hover:ps-1"
                      }`}
                    >
                      <span className="min-w-0 break-words">{cat.name}</span>
                      <span className="shrink-0 bg-white px-2.5 py-1 rounded-lg border border-gray-100 text-xs text-gray-400">
                        {cat.count}
                      </span>
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            {/* Recent Posts */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {labelText(
                  settings.recent_title,
                  "",
                )}
              </h4>
              <div className="flex flex-col gap-5">
                {recentPosts.map((post) => (
                  <div key={post.slug || post.id} className="flex min-w-0 gap-4">
                    {post.image ? (
                      <img
                        src={post.image}
                        alt={asText(post.title)}
                        className="w-16 h-16 rounded-xl object-cover shrink-0 bg-gray-100 border border-white shadow-sm"
                      />
                    ) : (
                      <div className="w-16 h-16 rounded-xl shrink-0 bg-primary/10 border border-primary/10 flex items-center justify-center text-primary">
                        <BookOpen className="w-5 h-5" />
                      </div>
                    )}
                    <div className="flex flex-col justify-center min-w-0">
                      <h5 className="font-bold text-sm text-navy hover:text-primary transition-colors line-clamp-2 leading-tight">
                        <Link to={`/blog/${post.slug}`}>
                          {asText(post.title)}
                        </Link>
                      </h5>
                      <span className="text-xs text-gray-400 mt-1 font-semibold">
                        {formatPostDate(post.date)}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Tags */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {labelText(settings.tags_title, "")}
              </h4>
              <div className="flex flex-wrap gap-2">
                {tags.map((tag) => (
                  <button
                    key={tag.value}
                    onClick={() => {
                      setSearchQuery("");
                      setSelectedCategory(tag.value);
                    }}
                  className="max-w-full break-words px-3 py-1.5 bg-white hover:bg-primary hover:text-white border border-gray-200/30 rounded-lg text-xs font-bold text-gray-400 transition-colors shadow-sm cursor-pointer"
                  >
                    {tag.label}
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
