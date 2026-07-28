import React from "react";
import { AlertCircle, CheckCircle } from "lucide-react";
import { useLanguage } from "../../../context/LanguageContext";

export default function TranslationTabs({
  activeTab,
  onTabChange,
  locales,
  missingLocales = [],
}) {
  const { locales: availableLocales, t } = useLanguage();
  const tabLocales = locales || availableLocales.map((item) => item.code);
  const localeNames = Object.fromEntries(
    availableLocales.map((item) => [item.code, item.native_name || item.name || item.code.toUpperCase()]),
  );

  return (
    <div className="flex border-b border-gray-150 gap-2 mb-6">
      {tabLocales.map((locale) => {
        const isMissing = missingLocales.includes(locale);
        const isActive = activeTab === locale;
        const name = localeNames[locale] || locale.toUpperCase();

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
                title={t("apanel.translations.missingFields")}
              >
                <AlertCircle className="w-3.5 h-3.5 fill-amber-50" />
              </span>
            ) : (
              <span
                className="flex items-center text-emerald-500"
                title={t("apanel.translations.allFieldsFilled")}
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
