import React from "react";
import { useLanguage } from "../../context/LanguageContext";

export default function RetryButton({ onRetry, label, className = "" }) {
  const { t } = useLanguage();
  const resolvedLabel = label || t("common.retryConnection");

  return (
    <button
      onClick={onRetry}
      className={`px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer ${className}`}
    >
      {resolvedLabel}
    </button>
  );
}
