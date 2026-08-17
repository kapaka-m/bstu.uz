import React from "react";
import { Link } from "react-router-dom";
import { Home, SearchX } from "lucide-react";
import { useLanguage } from "../context/LanguageContext";

export default function NotFoundPage() {
  const { t, isRtl } = useLanguage();

  return (
    <div
      className="flex min-h-screen items-center justify-center bg-slate-50 px-4 pt-24 pb-16 text-center overflow-x-hidden"
      dir={isRtl ? "rtl" : "ltr"}
    >
      <div className="w-full max-w-xl rounded-3xl border border-gray-100 bg-white p-8 shadow-sm">
        <div className="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
          <SearchX className="h-8 w-8" />
        </div>
        <p className="mb-2 text-xs font-extrabold uppercase tracking-widest text-primary">
          404
        </p>
        <h1 className="text-2xl font-black leading-tight text-navy md:text-4xl">
          {t("common.notFound")}
        </h1>
        <p className="mx-auto mt-3 max-w-md text-sm font-semibold leading-relaxed text-gray-500">
          {t("common.notFoundDesc")}
        </p>
        <Link
          to="/"
          className="mt-7 inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-extrabold text-white shadow-md shadow-primary/20 transition-all hover:bg-primary-hover hover:shadow-primary/30"
        >
          <Home className="h-4 w-4" />
          {t("common.goHome")}
        </Link>
      </div>
    </div>
  );
}
