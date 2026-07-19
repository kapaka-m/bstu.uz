import React from "react";
import { AlertCircle, CheckCircle } from "lucide-react";

export default function TranslationTabs({
  activeTab,
  onTabChange,
  locales = ["en", "uz", "ru", "ar"],
  missingLocales = [],
}) {
  const languageNames = {
    en: "English",
    uz: "O'zbek",
    ru: "Русский",
    ar: "العربية",
  };

  return (
    <div className="flex border-b border-gray-150 gap-2 mb-6">
      {locales.map((locale) => {
        const isMissing = missingLocales.includes(locale);
        const isActive = activeTab === locale;
        const name = languageNames[locale] || locale.toUpperCase();

        return (
          <button
            key={locale}
            type="button"
            onClick={() => onTabChange(locale)}
            className={`flex items-center gap-1.5 px-4 py-3 border-b-2 text-xs font-extrabold cursor-pointer transition-all ${
              isActive
                ? "border-primary text-primary"
                : "border-transparent text-gray-400 hover:text-navy"
            }`}
          >
            <span>{name}</span>
            {isMissing ? (
              <span
                className="flex items-center text-amber-500 hover:text-amber-600"
                title="Missing translation fields"
              >
                <AlertCircle className="w-3.5 h-3.5 fill-amber-50" />
              </span>
            ) : (
              <span
                className="flex items-center text-emerald-500"
                title="All fields filled"
              >
                <CheckCircle className="w-3.5 h-3.5 fill-emerald-50" />
              </span>
            )}
          </button>
        );
      })}
    </div>
  );
}
