import React from "react";
import RetryButton from "./RetryButton";

export default function ErrorState({
  title = "Something went wrong",
  message = "We encountered an error loading this section. Please try again.",
  onRetry = null,
  height = "min-h-64",
}) {
  return (
    <div
      className={`flex flex-col items-center justify-center p-6 text-center ${height} w-full`}
    >
      <div className="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 mb-4">
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
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
      </div>
      <h3 className="text-gray-800 font-semibold text-lg mb-1">{title}</h3>
      <p className="text-gray-500 text-sm max-w-md mb-4">{message}</p>
      {onRetry && <RetryButton onRetry={onRetry} />}
    </div>
  );
}
