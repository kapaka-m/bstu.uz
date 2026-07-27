import React from "react";
import { useLanguage } from "../../context/LanguageContext";

export default function LoadingState({ message, height = "h-64" }) {
  const { t } = useLanguage();
  const resolvedMessage = message || t("common.loading");

  return (
    <div
      className={`flex flex-col items-center justify-center ${height} w-full`}
    >
      <div className="relative w-16 h-16">
        <div className="absolute inset-0 rounded-full border-4 border-emerald-500/20 animate-pulse"></div>
        <div className="absolute inset-0 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin"></div>
      </div>
      <p className="mt-4 text-gray-500 text-sm font-medium animate-pulse">
        {resolvedMessage}
      </p>
    </div>
  );
}
