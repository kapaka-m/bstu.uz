import React from "react";
import { Inbox } from "lucide-react";
import { useLanguage } from "../../context/LanguageContext";

export default function EmptyState({ title, message, height = "h-48" }) {
  const { t } = useLanguage();
  const resolvedTitle = title || t("common.emptyTitle");
  const resolvedMessage = message || t("common.emptyMessage");

  return (
    <div
      className={`flex flex-col items-center justify-center p-6 text-center ${height} w-full`}
    >
      <div className="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-4">
        <Inbox className="w-6 h-6" />
      </div>
      <h3 className="text-gray-700 font-medium text-base mb-1">
        {resolvedTitle}
      </h3>
      <p className="text-gray-400 text-sm max-w-sm">{resolvedMessage}</p>
    </div>
  );
}
