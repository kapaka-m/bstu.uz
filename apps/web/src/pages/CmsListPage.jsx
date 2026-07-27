import React, { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { ArrowRight, Calendar, Search } from "lucide-react";
import EmptyState from "../components/common/EmptyState";
import ErrorState from "../components/common/ErrorState";
import LoadingState from "../components/common/LoadingState";
import { useLanguage } from "../context/LanguageContext";
import {
  asArray,
  cmsCategory,
  cmsDate,
  cmsExcerpt,
  cmsId,
  cmsImage,
  cmsTitle,
  filterCmsItems,
  formatCmsDate,
  uniqueCategories,
} from "../utils/cmsContent";

export default function CmsListPage({
  title,
  eyebrow,
  basePath,
  fetchItems,
  emptyTitle,
  imageFallback,
}) {
  const navigate = useNavigate();
  const location = useLocation();
  const { t, language } = useLanguage();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedCategory, setSelectedCategory] = useState("all");

  const loadItems = React.useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const result = await fetchItems();
      setItems(asArray(result));
    } catch (err) {
      setError(err);
    } finally {
      setLoading(false);
    }
  }, [fetchItems]);

  useEffect(() => {
    window.scrollTo(0, 0);
    const params = new URLSearchParams(location.search);
    setSearchQuery(params.get("search") || location.state?.search || "");
    setSelectedCategory(
      (params.get("category") || location.state?.category || "all").toLowerCase(),
    );
  }, [location.search, location.state]);

  useEffect(() => {
    loadItems();
  }, [loadItems, language]);

  const filteredItems = useMemo(
    () => filterCmsItems(items, searchQuery, selectedCategory),
    [items, searchQuery, selectedCategory],
  );

  const categories = useMemo(
    () =>
      uniqueCategories(items).map((category) => ({
        value: category,
        name:
          category === "all"
            ? t("common.all")
            : t(`categories.${category}`),
        count:
          category === "all"
            ? items.length
            : items.filter(
                (item) => cmsCategory(item, "general").toLowerCase() === category,
              ).length,
      })),
    [items, t],
  );

  const recentItems = items.slice(0, 3);

  if (loading) {
    return <LoadingState message={t("common.loading")} height="h-screen" />;
  }

  if (error) {
    return (
      <div className="pt-24">
        <ErrorState
          title={t("common.error")}
          message={error.message || t("common.tryAgain")}
          onRetry={loadItems}
          height="h-96"
        />
      </div>
    );
  }

  return (
    <div className="pt-20 bg-white">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-16 md:py-24">
        <div className="max-w-3xl mb-12 text-start">
          {eyebrow && (
            <p className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
              {eyebrow}
            </p>
          )}
          <h1 className="text-3xl md:text-5xl font-extrabold text-navy">
            {title}
          </h1>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          <div className="lg:col-span-8 flex flex-col gap-8">
            {filteredItems.length > 0 ? (
              filteredItems.map((item) => {
                const id = cmsId(item);
                const itemTitle = cmsTitle(item);
                const itemDate = formatCmsDate(cmsDate(item), language);

                return (
                  <article
                    key={id}
                    className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col md:flex-row gap-6 p-6 group text-start"
                  >
                    <Link
                      to={`${basePath}/${id}`}
                      className="w-full md:w-70 aspect-16/11 md:aspect-auto overflow-hidden rounded-2xl bg-gray-50 shrink-0 relative"
                    >
                      <img
                        src={cmsImage(item, imageFallback)}
                        alt={itemTitle}
                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                        onError={(event) => {
                          event.currentTarget.src = imageFallback;
                        }}
                      />
                      <span className="absolute top-3 left-3 rtl:left-auto rtl:right-3 bg-primary text-white text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm">
                        {cmsCategory(item)}
                      </span>
                    </Link>
                    <div className="flex flex-col justify-between grow py-2">
                      <div>
                        {itemDate && (
                          <div className="flex items-center gap-1 text-xs font-semibold text-gray-400 mb-3">
                            <Calendar className="w-3.5 h-3.5 text-primary" />
                            {itemDate}
                          </div>
                        )}
                        <h2 className="text-xl md:text-2xl font-extrabold text-navy group-hover:text-primary transition-colors duration-300 leading-snug mb-3">
                          <Link to={`${basePath}/${id}`}>{itemTitle}</Link>
                        </h2>
                        <p className="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-3">
                          {cmsExcerpt(item)}
                        </p>
                      </div>
                      <Link
                        to={`${basePath}/${id}`}
                        className="inline-flex items-center gap-1.5 text-sm font-bold text-navy hover:text-primary transition-colors group-hover:underline"
                      >
                        {t("common.readDetails")}
                        <ArrowRight
                          className={`w-4 h-4 transition-transform duration-300 ${language === "ar" ? "rotate-180 group-hover:-translate-x-0.5" : "group-hover:translate-x-0.5"}`}
                        />
                      </Link>
                    </div>
                  </article>
                );
              })
            ) : (
              <EmptyState
                title={emptyTitle || t("common.noContent")}
                message={t("common.noResults")}
                height="h-80"
              />
            )}
          </div>

          <aside className="lg:col-span-4 flex flex-col gap-8 text-start">
            <div className="bg-primary-light border border-gray-100 p-6 rounded-3xl">
              <h2 className="text-base font-extrabold text-navy mb-4">
                {t("common.search")}
              </h2>
              <div className="flex bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm">
                <input
                  type="text"
                  placeholder={t("common.searchPlaceholder")}
                  value={searchQuery}
                  onChange={(event) => setSearchQuery(event.target.value)}
                  className="grow px-4 py-3 text-sm focus:outline-none bg-white text-gray-700 w-full"
                  onKeyDown={(event) => {
                    if (event.key === "Enter") {
                      navigate(`${basePath}?search=${encodeURIComponent(event.currentTarget.value)}`);
                    }
                  }}
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center border-s border-gray-100 bg-gray-50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>

            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h2 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {t("common.categories")}
              </h2>
              <ul className="flex flex-col gap-3 font-semibold text-sm">
                {categories.map((category) => (
                  <li key={category.value}>
                    <button
                      type="button"
                      onClick={() => setSelectedCategory(category.value)}
                      className={`w-full flex items-center justify-between py-2 transition-all cursor-pointer ${
                        selectedCategory === category.value
                          ? "text-primary font-bold ps-1"
                          : "text-gray-500 hover:text-primary hover:ps-1"
                      }`}
                    >
                      <span className="capitalize">{category.name}</span>
                      <span className="bg-white px-2.5 py-1 rounded-lg border border-gray-100 text-xs text-gray-400 font-bold">
                        {category.count}
                      </span>
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            {recentItems.length > 0 && (
              <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
                <h2 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                  {t("common.recentPosts")}
                </h2>
                <div className="flex flex-col gap-5">
                  {recentItems.map((item) => {
                    const id = cmsId(item);
                    return (
                      <Link key={id} to={`${basePath}/${id}`} className="flex gap-4 group">
                        <img
                          src={cmsImage(item, imageFallback)}
                          alt={cmsTitle(item)}
                          className="w-16 h-16 rounded-xl object-cover shrink-0 bg-gray-100 border border-white shadow-sm"
                          loading="lazy"
                          onError={(event) => {
                            event.currentTarget.src = imageFallback;
                          }}
                        />
                        <div className="flex flex-col justify-center">
                          <h3 className="font-bold text-sm text-navy group-hover:text-primary transition-colors line-clamp-2 leading-tight">
                            {cmsTitle(item)}
                          </h3>
                          <span className="text-xs text-gray-400 mt-1 font-semibold">
                            {formatCmsDate(cmsDate(item), language)}
                          </span>
                        </div>
                      </Link>
                    );
                  })}
                </div>
              </div>
            )}
          </aside>
        </div>
      </div>
    </div>
  );
}
