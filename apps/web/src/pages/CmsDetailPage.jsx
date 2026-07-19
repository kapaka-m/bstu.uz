import React, { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { ArrowLeft, Calendar, Folder, Search } from "lucide-react";
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
  cmsParagraphs,
  cmsTitle,
  formatCmsDate,
} from "../utils/cmsContent";

export default function CmsDetailPage({
  backLabel = "Back",
  basePath,
  fetchItem,
  fetchItems,
  imageFallback,
}) {
  const navigate = useNavigate();
  const { id } = useParams();
  const { t, language } = useLanguage();
  const [item, setItem] = useState(null);
  const [recentItems, setRecentItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const loadContent = React.useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const [detail, list] = await Promise.all([
        fetchItem(id),
        fetchItems ? fetchItems() : Promise.resolve([]),
      ]);
      setItem(detail || null);
      setRecentItems(asArray(list).filter((entry) => cmsId(entry) !== String(id)).slice(0, 5));
    } catch (err) {
      setError(err);
    } finally {
      setLoading(false);
    }
  }, [fetchItem, fetchItems, id]);

  useEffect(() => {
    window.scrollTo(0, 0);
    loadContent();
  }, [loadContent, language]);

  if (loading) {
    return <LoadingState message={t("common.loading", "Loading content...")} height="h-screen" />;
  }

  if (error) {
    return (
      <div className="pt-24">
        <ErrorState
          title={t("common.error", "Content unavailable")}
          message={error.message || t("common.tryAgain", "Please try again.")}
          onRetry={loadContent}
          height="h-96"
        />
      </div>
    );
  }

  if (!item) {
    return (
      <div className="pt-24">
        <EmptyState
          title={t("common.notFound", "Content not found")}
          message={t("common.noResults", "No matching content was found.")}
          height="h-96"
        />
      </div>
    );
  }

  const title = cmsTitle(item);
  const paragraphs = cmsParagraphs(item);
  const date = formatCmsDate(cmsDate(item), language);

  return (
    <div className="pt-24 bg-white">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="mb-10">
          <Link
            to={basePath}
            className="inline-flex items-center gap-2 text-sm font-bold text-primary hover:text-primary-hover group"
          >
            <ArrowLeft
              className={`w-4 h-4 transition-transform ${language === "ar" ? "rotate-180 group-hover:translate-x-1" : "group-hover:-translate-x-1"}`}
            />
            {backLabel}
          </Link>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          <article className="lg:col-span-8 flex flex-col gap-6 text-start">
            <div className="rounded-3xl overflow-hidden shadow-lg aspect-video bg-gray-50 border border-gray-100">
              <img
                src={cmsImage(item, imageFallback)}
                alt={title}
                className="w-full h-full object-cover"
                loading="lazy"
                onError={(event) => {
                  event.currentTarget.src = imageFallback;
                }}
              />
            </div>

            <h1 className="text-2xl md:text-4xl font-extrabold text-navy leading-snug">
              {title}
            </h1>

            <div className="flex flex-wrap items-center gap-4 text-xs md:text-sm font-semibold text-gray-400 border-b border-gray-200/50 pb-4">
              {date && (
                <span className="flex items-center gap-1">
                  <Calendar className="w-4 h-4 text-primary" />
                  <span>{date}</span>
                </span>
              )}
              <span className="flex items-center gap-1">
                <Folder className="w-4 h-4 text-primary" />
                <span>{cmsCategory(item)}</span>
              </span>
            </div>

            <div className="text-gray-500 text-sm md:text-base leading-relaxed flex flex-col gap-6">
              {paragraphs.length > 0 ? (
                paragraphs.map((paragraph, index) => (
                  <p key={index} className="leading-relaxed">
                    {paragraph}
                  </p>
                ))
              ) : (
                <p>{cmsExcerpt(item)}</p>
              )}
            </div>
          </article>

          <aside className="lg:col-span-4 flex flex-col gap-8 text-start">
            <div className="bg-primary-light border border-gray-100 p-6 rounded-3xl">
              <h2 className="text-base font-extrabold text-navy mb-4">
                {t("common.search", "Search")}
              </h2>
              <div className="flex bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm">
                <input
                  type="text"
                  placeholder={t("common.searchPlaceholder", "Search...")}
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

            {recentItems.length > 0 && (
              <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
                <h2 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                  {t("common.recentPosts", "Recent")}
                </h2>
                <div className="flex flex-col gap-5">
                  {recentItems.map((entry) => {
                    const entryId = cmsId(entry);
                    return (
                      <Link key={entryId} to={`${basePath}/${entryId}`} className="flex gap-4 group">
                        <img
                          src={cmsImage(entry, imageFallback)}
                          alt={cmsTitle(entry)}
                          className="w-16 h-16 rounded-xl object-cover shrink-0 bg-gray-100 border border-white shadow-sm"
                          loading="lazy"
                          onError={(event) => {
                            event.currentTarget.src = imageFallback;
                          }}
                        />
                        <div className="flex flex-col justify-center">
                          <h3 className="font-bold text-sm text-navy group-hover:text-primary transition-colors line-clamp-2 leading-tight">
                            {cmsTitle(entry)}
                          </h3>
                          <span className="text-xs text-gray-400 mt-1 font-semibold">
                            {formatCmsDate(cmsDate(entry), language)}
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
