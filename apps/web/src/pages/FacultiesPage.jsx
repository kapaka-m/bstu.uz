import React, { useEffect } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Building2, GraduationCap } from "lucide-react";
import PageHeader from "../components/PageHeader";
import EmptyState from "../components/common/EmptyState";
import ErrorState from "../components/common/ErrorState";
import LoadingState from "../components/common/LoadingState";
import { useAppData } from "../context/AppDataContext";
import { useLanguage } from "../context/LanguageContext";

function getSlug(item) {
  return item.slug || item.id || item.code || "";
}

export default function FacultiesPage() {
  const { t, language } = useLanguage();
  const { faculties, loading, error, retry } = useAppData();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  return (
    <div className="pt-20 bg-white">
      <PageHeader
        title={t("common.faculties")}
        breadcrumbs={[{ label: t("common.faculties") }]}
      />

      <section className="py-16 md:py-24 bg-primary-light/40">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          {loading ? (
            <LoadingState message={t("common.loading")} />
          ) : error ? (
            <ErrorState
              message={t("faculties.loadError")}
              onRetry={retry}
            />
          ) : faculties.length === 0 ? (
            <EmptyState
              title={t("faculties.emptyTitle")}
              message={t("faculties.emptyDesc")}
            />
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              {faculties.map((faculty) => {
                const slug = getSlug(faculty);
                return (
                  <Link
                    key={slug}
                    to={`/faculty/${slug}`}
                    className="group bg-white border border-gray-100 rounded-3xl p-7 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all text-start"
                  >
                    <div className="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-6">
                      <Building2 className="w-6 h-6" />
                    </div>
                    <h2 className="text-xl font-extrabold text-navy group-hover:text-primary transition-colors">
                      {faculty.name || t("common.untitled")}
                    </h2>
                    {faculty.description || faculty.about ? (
                      <p className="text-sm text-gray-500 leading-relaxed mt-3 line-clamp-3">
                        {faculty.description || faculty.about}
                      </p>
                    ) : null}
                    <div className="flex items-center justify-between mt-6 pt-5 border-t border-gray-50">
                      <span className="inline-flex items-center gap-1.5 text-xs font-extrabold text-gray-400">
                        <GraduationCap className="w-4 h-4" />
                        {faculty.code || t("common.faculty")}
                      </span>
                      <span className="inline-flex items-center gap-1 text-xs font-extrabold text-primary">
                        {t("common.learnMore")}
                        <ArrowRight
                          className={`w-4 h-4 transition-transform group-hover:translate-x-1 ${
                            language === "ar" ? "rotate-180 group-hover:-translate-x-1" : ""
                          }`}
                        />
                      </span>
                    </div>
                  </Link>
                );
              })}
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
