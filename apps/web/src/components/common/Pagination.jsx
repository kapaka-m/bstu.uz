import React from "react";

export default function Pagination({
  currentPage,
  lastPage,
  onPageChange,
  nextLabel = "Next",
  prevLabel = "Previous",
}) {
  if (lastPage <= 1) return null;

  return (
    <div className="flex items-center justify-center space-x-2 mt-8">
      <button
        disabled={currentPage === 1}
        onClick={() => onPageChange(currentPage - 1)}
        className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 active:scale-95 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200 cursor-pointer"
      >
        {prevLabel}
      </button>
      <span className="text-sm text-gray-500 font-medium px-4">
        Page {currentPage} of {lastPage}
      </span>
      <button
        disabled={currentPage === lastPage}
        onClick={() => onPageChange(currentPage + 1)}
        className="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 active:scale-95 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200 cursor-pointer"
      >
        {nextLabel}
      </button>
    </div>
  );
}
