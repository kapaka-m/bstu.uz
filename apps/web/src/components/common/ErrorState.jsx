import React from "react";
import { AlertTriangle } from "lucide-react";
import { useLanguage } from "../../context/LanguageContext";
import RetryButton from "./RetryButton";

export default function ErrorState({
  title,
  message,
  titleKey = "common.errorTitle",
  messageKey = "common.errorMessage",
  onRetry = null,
  height = "min-h-64",
}) {
  const { t } = useLanguage();
  const resolvedTitle = title || t(titleKey);
  const resolvedMessage = message || t(messageKey);

  return (
    <div
      className={`flex flex-col items-center justify-center p-6 text-center ${height} w-full`}
    >
      <div className="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 mb-4">
        <AlertTriangle className="w-6 h-6" />
      </div>
      <h3 className="text-gray-800 font-semibold text-lg mb-1">
        {resolvedTitle}
      </h3>
      <p className="text-gray-500 text-sm max-w-md mb-4">{resolvedMessage}</p>
      {onRetry && <RetryButton onRetry={onRetry} />}
    </div>
  );
}
