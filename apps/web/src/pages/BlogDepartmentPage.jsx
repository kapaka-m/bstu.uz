import React, { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { ArrowRight, Calendar, Mail, Phone, User, Globe2, Building2 } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { blogService } from "../services/blogService";
import { formatLocalizedDate } from "../utils/dateFormat";

const asText = (value, fallback = "") => {
  if (typeof value === "string" || typeof value === "number") return String(value);
  if (value && typeof value === "object") return Object.values(value).filter(Boolean).join(" ");
  return fallback;
};

export default function BlogDepartmentPage() {
  const { id } = useParams();
  const { language, isRtl, t } = useLanguage();
  const [department, setDepartment] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let alive = true;
    setLoading(true);

    blogService
      .getDepartment(id)
      .then((payload) => {
        if (!alive) return;
        setDepartment(payload || null);
        if (payload?.name) document.title = payload.name;
      })
      .catch(() => {
        if (alive) setDepartment(null);
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [id, language]);

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

  if (!department) {
    return (
      <div className="pt-24 min-h-screen bg-primary-light flex flex-col items-center justify-center text-center px-4">
        <h1 className="text-3xl font-extrabold text-navy mb-3">{t("common.notFound")}</h1>
        <Link to="/" className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white">
          {t("common.goHome")}
        </Link>
      </div>
    );
  }

  return (
    <div className="pt-20 bg-white" dir={isRtl ? "rtl" : "ltr"}>
      <section className="bg-primary-light border-b border-gray-100">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-14 md:py-20">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div className="lg:col-span-8 text-start">
              <span className="inline-flex items-center gap-2 rounded-full border border-white bg-white/70 px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-wider text-primary mb-5">
                <Building2 className="h-4 w-4" />
                {t("common.contentPublisher")}
              </span>
              <h1 className="text-3xl md:text-5xl font-extrabold text-navy leading-tight mb-5">
                {department.name}
              </h1>
              {department.description && (
                <p className="max-w-3xl text-sm md:text-lg leading-relaxed text-gray-500">
                  {department.description}
                </p>
              )}
            </div>

            <div className="lg:col-span-4">
              <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm text-start">
                {department.image ? (
                  <img
                    src={department.image}
                    alt={department.name}
                    className="mb-5 h-24 w-24 rounded-3xl object-cover border border-gray-100 shadow-sm"
                  />
                ) : (
                  <div className="mb-5 h-24 w-24 rounded-3xl bg-primary/10 text-primary flex items-center justify-center">
                    <User className="h-10 w-10" />
                  </div>
                )}
                <div className="space-y-3 text-sm font-semibold text-gray-500">
                  {department.email && (
                    <a href={`mailto:${department.email}`} className="flex items-center gap-2 hover:text-primary break-all">
                      <Mail className="h-4 w-4 text-primary shrink-0" />
                      <span dir="ltr">{department.email}</span>
                    </a>
                  )}
                  {department.phone && (
                    <a href={`tel:${String(department.phone).replace(/[^\d+]/g, "")}`} className="flex items-center gap-2 hover:text-primary">
                      <Phone className="h-4 w-4 text-primary shrink-0" />
                      <span dir="ltr">{department.phone}</span>
                    </a>
                  )}
                  {department.website_url && (
                    <a href={department.website_url} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 hover:text-primary break-all">
                      <Globe2 className="h-4 w-4 text-primary shrink-0" />
                      <span dir="ltr">{department.website_url}</span>
                    </a>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="mb-8 flex items-center justify-between gap-4">
          <h2 className="text-2xl font-extrabold text-navy">{t("common.publicationsTextbooks")}</h2>
          <span className="rounded-full bg-primary/10 px-3 py-1 text-xs font-extrabold text-primary">
            {contentGroups.reduce((total, group) => total + group.items.length, 0)}
          </span>
        </div>

        <div className="space-y-10">
          {contentGroups.map((group) => (
            <div key={group.key}>
              <h3 className="mb-5 text-lg font-extrabold text-navy">{group.title}</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {group.items.map((post) => {
                  const href = group.route === "video-bdtu" ? "/video-bdtu" : `/${group.route}/${post.slug || post.id}`;
                  const image = post.image || post.img || post.poster || post.thumbnail || "";
                  const description = asText(post.excerpt || post.description || post.summary);

                  return (
                    <article key={`${group.key}-${post.slug || post.id}`} className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm text-start">
                      {image && (
                        <Link to={href} className="block mb-5 overflow-hidden rounded-2xl bg-gray-50 aspect-16/10">
                          <img src={image} alt={asText(post.title)} className="h-full w-full object-cover transition-transform duration-500 hover:scale-105" />
                        </Link>
                      )}
                      <div className="mb-3 flex items-center gap-2 text-xs font-semibold text-gray-400">
                        <Calendar className="h-3.5 w-3.5 text-primary" />
                        {formatDate(post.date || post.published_at || post.publishedAt)}
                      </div>
                      <h4 className="mb-3 text-lg font-extrabold leading-snug text-navy hover:text-primary">
                        <Link to={href}>{asText(post.title)}</Link>
                      </h4>
                      {description && (
                        <p className="mb-5 line-clamp-3 text-sm leading-relaxed text-gray-500">
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
      </section>
    </div>
  );
}
