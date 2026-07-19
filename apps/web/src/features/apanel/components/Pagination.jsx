import React from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

export default function Pagination({
  currentPage = 1,
  lastPage = 1,
  total = 0,
  perPage = 15,
  onPageChange,
}) {
  if (total === 0 || lastPage <= 1) return null;

  const startRecord = (currentPage - 1) * perPage + 1;
  const endRecord = Math.min(currentPage * perPage, total);

  return (
    <div className="flex flex-col sm:flex-row justify-between items-center gap-4 mt-6 pt-6 border-t border-gray-50 text-xs font-semibold text-gray-500">
      <div>
        Showing <span className="font-bold text-navy">{startRecord}</span> to{" "}
        <span className="font-bold text-navy">{endRecord}</span> of{" "}
        <span className="font-bold text-navy">{total}</span> records
      </div>

      <div className="flex items-center gap-1.5">
        <button
          onClick={() => currentPage > 1 && onPageChange(currentPage - 1)}
          disabled={currentPage === 1}
          className="p-2 border border-gray-100 hover:border-gray-200 hover:text-primary rounded-lg disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer bg-white transition-all"
        >
          <ChevronLeft className="w-4 h-4" />
        </button>

        {Array.from({ length: lastPage }, (_, i) => i + 1)
          .filter(
            (page) =>
              page === 1 ||
              page === lastPage ||
              Math.abs(page - currentPage) <= 1,
          )
          .map((page, idx, arr) => {
            const showEllipsis = idx > 0 && page - arr[idx - 1] > 1;
            return (
              <React.Fragment key={page}>
                {showEllipsis && (
                  <span className="px-2 text-gray-300">...</span>
                )}
                <button
                  onClick={() => onPageChange(page)}
                  className={`px-3 py-1.5 rounded-lg border cursor-pointer transition-all ${page === currentPage ? "bg-primary border-primary text-white font-bold" : "border-gray-100 hover:border-gray-200 text-gray-500 bg-white"}`}
                >
                  {page}
                </button>
              </React.Fragment>
            );
          })}

        <button
          onClick={() =>
            currentPage < lastPage && onPageChange(currentPage + 1)
          }
          disabled={currentPage === lastPage}
          className="p-2 border border-gray-100 hover:border-gray-200 hover:text-primary rounded-lg disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer bg-white transition-all"
        >
          <ChevronRight className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
}
