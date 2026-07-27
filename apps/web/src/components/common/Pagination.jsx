import React from "react";
import { useLanguage } from "../../context/LanguageContext";

export default function Pagination({
  currentPage,
  lastPage,
  onPageChange,
  nextLabel,
  prevLabel,
}) {
  const { t } = useLanguage();

  if (lastPage <= 1) return null;

  const resolvedPrevLabel = prevLabel || t("common.previous");
  const resolvedNextLabel = nextLabel || t("common.next");
  const statusLabel = t("common.paginationStatus")
    .replace("{currentPage}", currentPage)
    .replace("{lastPage}", lastPage);

  return (
    <div className="flex items-center justify-center gap-2 mt-8">
      <button
        disabled={currentPage === 1}
        onClick={() => onPageChange(currentPage - 1)}
        className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 active:scale-95 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200 cursor-pointer"
      >
        {resolvedPrevLabel}
      </button>
      <span className="text-sm text-gray-500 font-medium px-4">
        {statusLabel}
      </span>
      <button
        disabled={currentPage === lastPage}
        onClick={() => onPageChange(currentPage + 1)}
        className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 active:scale-95 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200 cursor-pointer"
      >
        {resolvedNextLabel}
      </button>
    </div>
  );
}
