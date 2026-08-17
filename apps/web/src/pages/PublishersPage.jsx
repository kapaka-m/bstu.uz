import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Building2, Globe2, Mail, Phone, User } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { blogService } from "../services/blogService";

export default function PublishersPage() {
  const { language, isRtl, t } = useLanguage();
  const [publishers, setPublishers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  useEffect(() => {
    let alive = true;
    setLoading(true);

    blogService
      .getPublishers()
      .then((items) => {
        if (alive) setPublishers(Array.isArray(items) ? items : []);
      })
      .catch(() => {
        if (alive) setPublishers([]);
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  if (loading) return null;

  return (
    <div className="bg-white pt-20" dir={isRtl ? "rtl" : "ltr"}>
      <section className="border-b border-gray-100 bg-primary-light">
        <div className="container mx-auto max-w-7xl px-4 py-14 md:px-8 md:py-20">
          <div className="max-w-3xl text-start">
            <span className="mb-5 inline-flex items-center gap-2 rounded-full border border-white bg-white/70 px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-wider text-primary">
              <Building2 className="h-4 w-4" />
              {t("common.contentPublisher")}
            </span>
            <h1 className="text-3xl font-extrabold leading-tight text-navy md:text-5xl">
              {t("common.contentPublisher")}
            </h1>
          </div>
        </div>
      </section>

      <section className="container mx-auto max-w-7xl px-4 py-12 md:px-8 md:py-16">
        {publishers.length > 0 ? (
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            {publishers.map((publisher) => (
              <article
                key={publisher.slug || publisher.id}
                className="group flex min-w-0 flex-col rounded-3xl border border-gray-100 bg-white p-6 text-start shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
              >
                <div className="mb-5 flex min-w-0 items-start gap-4">
                  {publisher.image ? (
                    <img
                      src={publisher.image}
                      alt={publisher.name || publisher.slug}
                      className="h-16 w-16 shrink-0 rounded-2xl border border-gray-100 object-cover shadow-sm"
                    />
                  ) : (
                    <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                      <User className="h-7 w-7" />
                    </div>
                  )}
                  <div className="min-w-0">
                    <h2 className="line-clamp-2 text-lg font-extrabold leading-snug text-navy transition-colors group-hover:text-primary">
                      {publisher.name || publisher.slug}
                    </h2>
                    {publisher.description && (
                      <p className="mt-2 line-clamp-3 text-sm font-semibold leading-relaxed text-gray-500">
                        {publisher.description}
                      </p>
                    )}
                  </div>
                </div>

                <div className="mb-5 grow space-y-2 border-t border-gray-50 pt-4 text-xs font-semibold text-gray-500">
                  {publisher.email && (
                    <a href={`mailto:${publisher.email}`} className="flex min-w-0 items-center gap-2 hover:text-primary">
                      <Mail className="h-4 w-4 shrink-0 text-primary" />
                      <span className="truncate" dir="ltr">{publisher.email}</span>
                    </a>
                  )}
                  {publisher.phone && (
                    <a href={`tel:${String(publisher.phone).replace(/[^\d+]/g, "")}`} className="flex min-w-0 items-center gap-2 hover:text-primary">
                      <Phone className="h-4 w-4 shrink-0 text-primary" />
                      <span className="truncate" dir="ltr">{publisher.phone}</span>
                    </a>
                  )}
                  {publisher.website_url && (
                    <a href={publisher.website_url} target="_blank" rel="noopener noreferrer" className="flex min-w-0 items-center gap-2 hover:text-primary">
                      <Globe2 className="h-4 w-4 shrink-0 text-primary" />
                      <span className="truncate" dir="ltr">{publisher.website_url}</span>
                    </a>
                  )}
                </div>

                <Link
                  to={`/publishers/${publisher.slug || publisher.id}`}
                  className="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-primary"
                >
                  {t("common.readMore")}
                  <ArrowRight className={`h-4 w-4 ${isRtl ? "rotate-180" : ""}`} />
                </Link>
              </article>
            ))}
          </div>
        ) : (
          <div className="rounded-3xl border border-gray-100 bg-primary-light p-12 text-center text-sm font-bold text-gray-500">
            {t("common.notFound")}
          </div>
        )}
      </section>
    </div>
  );
}
