import React, { useEffect, useState } from "react";
import { Link, useLocation, useParams } from "react-router-dom";
import { AlertCircle, ArrowRight, Calendar, FileText, Globe2, Mail, Phone, User, Building2 } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { blogService } from "../services/blogService";
import { formatLocalizedDate } from "../utils/dateFormat";

const asText = (value, fallback = "") => {
  if (typeof value === "string" || typeof value === "number") return String(value);
  if (Array.isArray(value)) return value.filter(Boolean).join(" ");
  if (value && typeof value === "object") return Object.values(value).filter(Boolean).join(" ");
  return fallback;
};

const pageCopy = {
  en: {
    allPublishers: "All Publishers",
    publisherProfile: "Publisher Profile",
    publishedContent: "Published Content",
    totalItems: "Total Items",
    noContent: "No published content is available for this publisher yet.",
  },
  uz: {
    allPublishers: "Barcha manbalar",
    publisherProfile: "Manba profili",
    publishedContent: "Nashr qilingan kontent",
    totalItems: "Jami materiallar",
    noContent: "Bu manba uchun hozircha nashr qilingan kontent mavjud emas.",
  },
  ru: {
    allPublishers: "Все источники",
    publisherProfile: "Профиль источника",
    publishedContent: "Опубликованные материалы",
    totalItems: "Всего материалов",
    noContent: "Для этого источника пока нет опубликованных материалов.",
  },
  ar: {
    allPublishers: "كل الناشرين",
    publisherProfile: "ملف الناشر",
    publishedContent: "المحتوى المنشور",
    totalItems: "إجمالي المواد",
    noContent: "لا يوجد محتوى منشور لهذا الناشر حتى الآن.",
  },
};

export default function BlogDepartmentPage() {
  const { id } = useParams();
  const location = useLocation();
  const { language, isRtl, t } = useLanguage();
  const [department, setDepartment] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const copy = pageCopy[language] || pageCopy.en;

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError("");

    const request = location.pathname.startsWith("/publishers/")
      ? blogService.getPublisher(id)
      : blogService.getDepartment(id);

    request
      .then((payload) => {
        if (!alive) return;
        setDepartment(payload || null);
      })
      .catch(() => {
        if (alive) {
          setDepartment(null);
          setError(t("common.loadError"));
        }
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [id, language, location.pathname, t]);

  const formatDate = (value) =>
    formatLocalizedDate(value, language, t, {
      day: "numeric",
      month: "short",
    });

  const contentGroups = department
    ? [
        { key: "blogs", title: t("nav.blog"), items: department.blogs || [], route: "blog" },
        { key: "news", title: t("nav.news"), items: department.news || [], route: "news" },
        { key: "announcements", title: t("nav.announcements"), items: department.announcements || [], route: "announcements" },
        { key: "green", title: t("nav.greenCampus"), items: department.green_campus_articles || [], route: "green-campus" },
        { key: "videos", title: t("nav.videoBdtu"), items: department.videos || [], route: "video-bdtu" },
      ].filter((group) => group.items.length > 0)
    : [];

  if (loading) return null;

  if (error) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-50/50 px-4 pt-24">
        <div className="flex max-w-md items-center gap-3 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-bold text-red-600">
          <AlertCircle className="h-5 w-5 shrink-0" />
          <span className="break-words">{error}</span>
        </div>
      </div>
    );
  }

  if (!department) {
    return (
      <div className="pt-24 min-h-screen bg-primary-light flex flex-col items-center justify-center text-center px-4">
        <h1 className="text-3xl font-extrabold text-navy mb-3">{t("common.notFound")}</h1>
        <Link to="/publishers" className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white">
          {copy.allPublishers}
        </Link>
      </div>
    );
  }

  const totalContent = contentGroups.reduce((total, group) => total + group.items.length, 0);

  return (
    <div className="pt-20 bg-white overflow-x-hidden" dir={isRtl ? "rtl" : "ltr"}>
      <section className="bg-primary-light border-b border-gray-100">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-20 min-w-0">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center min-w-0">
            <div className="lg:col-span-8 text-start min-w-0">
              <Link to="/publishers" className="mb-5 inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-primary hover:text-primary-hover">
                <ArrowRight className={`h-4 w-4 ${isRtl ? "" : "rotate-180"}`} />
                {copy.allPublishers}
              </Link>
              <span className="inline-flex items-center gap-2 rounded-full border border-white bg-white/70 px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-wider text-primary mb-5">
                <Building2 className="h-4 w-4" />
                {copy.publisherProfile}
              </span>
              <h1 className="text-3xl md:text-5xl font-extrabold text-navy leading-tight mb-5 break-words">
                {asText(department.name, department.slug)}
              </h1>
              {department.description && (
                <p className="max-w-3xl text-sm md:text-lg leading-relaxed text-gray-500 break-words">
                  {asText(department.description)}
                </p>
              )}
            </div>

            <div className="lg:col-span-4 min-w-0">
              <div className="rounded-3xl border border-gray-100 bg-white p-5 sm:p-6 shadow-sm text-start min-w-0">
                {department.image ? (
                  <img
                    src={department.image}
                    alt={asText(department.name, department.slug)}
                    className="mb-5 h-24 w-24 rounded-3xl object-cover border border-gray-100 shadow-sm"
                  />
                ) : (
                  <div className="mb-5 h-24 w-24 rounded-3xl bg-primary/10 text-primary flex items-center justify-center">
                    <User className="h-10 w-10" />
                  </div>
                )}
                <div className="mb-5 rounded-2xl border border-gray-100 bg-slate-50 px-4 py-3">
                  <span className="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">
                    {copy.totalItems}
                  </span>
                  <span className="mt-1 block text-2xl font-black text-navy">
                    {totalContent}
                  </span>
                </div>
                <div className="space-y-3 text-sm font-semibold text-gray-500">
                  {department.email && (
                    <a href={`mailto:${department.email}`} className="flex min-w-0 items-center gap-2 hover:text-primary">
                      <Mail className="h-4 w-4 text-primary shrink-0" />
                      <span className="truncate" dir="ltr">{department.email}</span>
                    </a>
                  )}
                  {department.phone && (
                    <a href={`tel:${String(department.phone).replace(/[^\d+]/g, "")}`} className="flex min-w-0 items-center gap-2 hover:text-primary">
                      <Phone className="h-4 w-4 text-primary shrink-0" />
                      <span className="truncate" dir="ltr">{department.phone}</span>
                    </a>
                  )}
                  {department.website_url && (
                    <a href={department.website_url} target="_blank" rel="noopener noreferrer" className="flex min-w-0 items-center gap-2 hover:text-primary">
                      <Globe2 className="h-4 w-4 text-primary shrink-0" />
                      <span className="truncate" dir="ltr">{department.website_url}</span>
                    </a>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 min-w-0">
        <div className="mb-8 flex min-w-0 items-center justify-between gap-4">
          <h2 className="min-w-0 break-words text-2xl font-extrabold text-navy">{copy.publishedContent}</h2>
          <span className="rounded-full bg-primary/10 px-3 py-1 text-xs font-extrabold text-primary">
            {totalContent}
          </span>
        </div>

        {contentGroups.length > 0 ? (
          <div className="space-y-10">
            {contentGroups.map((group) => (
              <div key={group.key}>
                <h3 className="mb-5 text-lg font-extrabold text-navy">{group.title}</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 min-w-0">
                  {group.items.map((post) => {
                    const href = group.route === "video-bdtu" ? "/video-bdtu" : `/${group.route}/${post.slug || post.id}`;
                    const image = post.image || post.img || post.poster || post.thumbnail || "";
                    const description = asText(post.excerpt || post.description || post.summary);

                    return (
                      <article key={`${group.key}-${post.slug || post.id}`} className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm text-start min-w-0 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                        {image && (
                          <Link to={href} className="block mb-5 overflow-hidden rounded-2xl bg-gray-50 aspect-16/10">
                            <img src={image} alt={asText(post.title)} className="h-full w-full object-cover transition-transform duration-500 hover:scale-105" />
                          </Link>
                        )}
                        <div className="mb-3 flex min-w-0 items-center gap-2 text-xs font-semibold text-gray-400">
                          <Calendar className="h-3.5 w-3.5 text-primary" />
                          <span className="truncate">{formatDate(post.date || post.published_at || post.publishedAt)}</span>
                        </div>
                        <h4 className="mb-3 text-lg font-extrabold leading-snug text-navy hover:text-primary break-words">
                          <Link to={href}>{asText(post.title)}</Link>
                        </h4>
                        {description && (
                          <p className="mb-5 line-clamp-3 text-sm leading-relaxed text-gray-500 break-words">
                            {description}
                          </p>
                        )}
                        <Link to={href} className="inline-flex items-center gap-1.5 text-xs font-extrabold text-primary">
                          {t("common.readMore")}
                          <ArrowRight className={`h-4 w-4 ${isRtl ? "rotate-180" : ""}`} />
                        </Link>
                      </article>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="rounded-3xl border border-gray-100 bg-primary-light p-8 sm:p-10 text-center text-sm font-bold text-gray-500">
            <FileText className="mx-auto mb-4 h-9 w-9 text-primary" />
            <p className="break-words">{copy.noContent}</p>
          </div>
        )}
      </section>
    </div>
  );
}
