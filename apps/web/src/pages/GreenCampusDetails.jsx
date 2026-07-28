import React, { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import {
  Calendar,
  User,
  Search,
  Leaf,
  Mail,
  Eye,
  X,
  ChevronLeft,
  ChevronRight,
} from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { greenCampusService } from "../services/greenCampusService";

function GalleryLightbox({ images, startIndex, onClose, labels }) {
  const [current, setCurrent] = useState(startIndex);

  useEffect(() => {
    const handleKey = (e) => {
      if (e.key === "ArrowRight") setCurrent((c) => (c + 1) % images.length);
      if (e.key === "ArrowLeft")
        setCurrent((c) => (c - 1 + images.length) % images.length);
      if (e.key === "Escape") onClose();
    };
    window.addEventListener("keydown", handleKey);
    return () => window.removeEventListener("keydown", handleKey);
  }, [images.length, onClose]);

  return (
    <div
      className="fixed inset-0 z-9999 bg-black/90 flex items-center justify-center"
      onClick={onClose}
    >
      <button
        className="absolute top-4 right-4 text-white hover:text-gray-300 z-10"
        aria-label={labels.close}
        onClick={onClose}
      >
        <X className="w-8 h-8" />
      </button>
      {images.length > 1 && (
        <>
          <button
            className="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full z-10"
            aria-label={labels.previous}
            onClick={(e) => {
              e.stopPropagation();
              setCurrent((c) => (c - 1 + images.length) % images.length);
            }}
          >
            <ChevronLeft className="w-6 h-6" />
          </button>
          <button
            className="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full z-10"
            aria-label={labels.next}
            onClick={(e) => {
              e.stopPropagation();
              setCurrent((c) => (c + 1) % images.length);
            }}
          >
            <ChevronRight className="w-6 h-6" />
          </button>
        </>
      )}
      <img
        src={images[current]}
        alt={`Gallery ${current + 1}`}
        className="max-h-[85vh] max-w-[90vw] object-contain rounded-xl shadow-2xl"
        onClick={(e) => e.stopPropagation()}
        onError={(e) => {
          e.currentTarget.style.display = "none";
        }}
      />
      <div className="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/60 text-sm font-semibold">
        {current + 1} / {images.length}
      </div>
    </div>
  );
}

export default function GreenCampusDetails() {
  const { language } = useLanguage();
  const { id } = useParams();
  const [lightboxIndex, setLightboxIndex] = useState(null);
  const [article, setArticle] = useState(null);
  const [articles, setArticles] = useState([]);
  const [settings, setSettings] = useState({});

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let active = true;

    Promise.all([
      greenCampusService.getSettings(),
      greenCampusService.getArticle(id),
      greenCampusService.getArticles(),
    ])
      .then(([nextSettings, nextArticle, nextArticles]) => {
        if (!active) return;
        setSettings(nextSettings);
        setArticle(nextArticle);
        setArticles(nextArticles);
        if (nextArticle?.title) {
          document.title = nextArticle.title;
        }
      })
      .catch(() => {
        if (!active) return;
        setSettings({});
        setArticle(null);
        setArticles([]);
      });

    return () => {
      active = false;
    };
  }, [id, language]);

  const recentArticles = articles
    .filter((a) => a.id !== article?.id)
    .slice(0, Number(settings.recent_limit || 4));

  const uniqueCategories = [
    "all",
    ...new Set(articles.map((a) => a.category.toLowerCase())),
  ];
  const categoryLabels = settings.category_labels || {};
  const categoryLabel = (category) => categoryLabels[category] || category;
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

  const uniqueGallery = Array.from(new Set([article?.image, ...(article?.gallery || [])].filter(Boolean)));
  const galleryImages = uniqueGallery;
  const galleryThumbnails = galleryImages.slice(1);

  const formatArticleDate = (dateValue) => {
    const parsed = new Date(dateValue);
    if (Number.isNaN(parsed.getTime())) return dateValue;

    return new Intl.DateTimeFormat(language || undefined, {
      day: "numeric",
      month: "short",
      year: "numeric",
    }).format(parsed);
  };

  if (!article) {
    return <div className="pt-24 bg-white min-h-screen" />;
  }

  return (
    <div className="pt-24 bg-white text-start">
      {lightboxIndex !== null && (
        <GalleryLightbox
          images={galleryImages}
          startIndex={lightboxIndex}
          labels={{
            close: settings.close_viewer_label || "",
            previous: settings.previous_image_label || "",
            next: settings.next_image_label || "",
          }}
          onClose={() => setLightboxIndex(null)}
        />
      )}

      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          {/* Left: Article Body */}
          <div className="lg:col-span-8 flex flex-col gap-6">
            {/* Main Featured Image */}
            <div
              className="w-full aspect-video rounded-3xl overflow-hidden shadow-sm bg-gray-50 border border-gray-100 cursor-pointer"
              onClick={() => setLightboxIndex(0)}
            >
              <img
                src={article.image}
                alt={article.title}
                className="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                onError={(e) => {
                  e.currentTarget.style.display = "none";
                }}
              />
            </div>

            {/* Meta tags */}
            <div className="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold text-gray-400 mt-2">
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
                  {article.views.toLocaleString(language)} {settings.views_label || ""}
                </span>
              )}
            </div>

            {/* Main Title */}
            <h1 className="text-2xl md:text-3xl font-extrabold text-navy leading-tight">
              {article.title}
            </h1>

            {/* Paragraphs */}
            <div className="flex flex-col gap-5 text-gray-600 text-xs md:text-sm font-semibold leading-relaxed">
              {article.paragraphs.map((para, idx) => (
                <p key={idx}>{para}</p>
              ))}
            </div>

            {/* Photo Gallery */}
            {galleryThumbnails.length > 0 && (
              <div className="mt-4">
                <h3 className="text-base font-extrabold text-navy mb-4 border-b border-gray-100 pb-2">
                  {settings.gallery_label || ""}
                </h3>
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  {galleryThumbnails.map((img, idx) => (
                    <div
                      key={idx}
                      className="aspect-4/3 overflow-hidden rounded-2xl cursor-pointer bg-gray-50 border border-gray-100 hover:shadow-md transition-shadow group"
                      onClick={() => setLightboxIndex(idx + 1)}
                    >
                      <img
                        src={img}
                        alt={`Gallery ${idx + 2}`}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                        onError={(e) => {
                          e.currentTarget.style.display = "none";
                        }}
                      />
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Related Articles */}
            {recentArticles.length > 0 && (
              <div className="mt-4">
                <h3 className="text-base font-extrabold text-navy mb-6 border-b border-gray-100 pb-2">
                  {settings.related_label || ""}
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  {recentArticles.slice(0, 2).map((art) => (
                    <Link
                      key={art.id}
                      to={`/green-campus/${art.id}`}
                      className="flex flex-col gap-3 group border border-gray-100 rounded-2xl overflow-hidden hover:shadow-lg transition-all"
                    >
                      <div className="w-full aspect-video overflow-hidden bg-gray-50">
                        <img
                          src={art.image}
                          alt={art.title}
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                          onError={(e) => {
                            e.currentTarget.style.display = "none";
                          }}
                        />
                      </div>
                      <div className="p-4">
                        <span className="text-[9px] text-emerald-600 font-bold uppercase tracking-wider">
                          {categoryLabel(art.category)}
                        </span>
                        <h4 className="text-sm font-extrabold text-navy group-hover:text-emerald-600 transition-colors leading-snug mt-1 line-clamp-2">
                          {art.title}
                        </h4>
                      </div>
                    </Link>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Right: Sidebar */}
          <div className="lg:col-span-4 flex flex-col gap-8">
            {/* Search Widget */}
            <div className="bg-gray-50 border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-gray-200/50 pb-2">
                {settings.search_title || ""}
              </h4>
              <div className="relative">
                <Link to="/green-campus" className="block">
                  <input
                    id="green-campus-details-search-input"
                    name="green_campus_search"
                    type="text"
                    autoComplete="off"
                    readOnly
                    placeholder={settings.search_placeholder || ""}
                    className="w-full pl-10 pr-4 py-3 rounded-xl bg-white border border-gray-200 cursor-pointer text-sm font-semibold transition-colors"
                  />
                  <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" />
                </Link>
              </div>
            </div>

            {/* Categories Widget */}
            <div className="bg-gray-50 border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-gray-200/50 pb-2">
                {settings.categories_title || ""}
              </h4>
              <ul className="flex flex-col gap-3 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <Link
                      to={`/green-campus?category=${cat.value}`}
                      className="w-full flex items-center justify-between py-1.5 transition-all text-start text-gray-500 hover:text-emerald-600"
                    >
                      <span>{cat.name}</span>
                      <span className="text-[10px] bg-white border border-gray-200/80 text-gray-400 px-2 py-0.5 rounded-md font-bold shrink-0">
                        {cat.count}
                      </span>
                    </Link>
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
                {recentArticles.map((art) => (
                  <div key={art.id} className="flex gap-4 items-start group">
                    <img
                      src={art.image}
                      alt={art.title}
                      className="w-16 h-12 object-cover rounded-lg bg-white shrink-0"
                      onError={(e) => {
                        e.currentTarget.style.display = "none";
                      }}
                    />
                    <div className="flex flex-col gap-1 text-start min-w-0">
                      <h5 className="text-xs font-bold text-navy group-hover:text-emerald-600 transition-colors leading-snug line-clamp-2">
                        <Link to={`/green-campus/${art.id}`}>
                          {art.title}
                        </Link>
                      </h5>
                      <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                        {formatArticleDate(art.date)}
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
