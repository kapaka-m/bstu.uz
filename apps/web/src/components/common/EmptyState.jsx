import React from "react";

export default function EmptyState({
  title = "No content available",
  message = "There is currently no data to display in this list.",
  height = "h-48",
}) {
  return (
    <div
      className={`flex flex-col items-center justify-center p-6 text-center ${height} w-full`}
    >
      <div className="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-4">
        <svg
          className="w-6 h-6"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
          />
        </svg>
      </div>
      <h3 className="text-gray-700 font-medium text-base mb-1">{title}</h3>
      <p className="text-gray-400 text-sm max-w-sm">{message}</p>
    </div>
  );
}
