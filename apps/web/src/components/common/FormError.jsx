import React from "react";

export default function FormError({ message }) {
  if (!message) return null;

  return (
    <div
      className="p-4 mb-4 text-sm text-red-700 bg-red-50 rounded-lg border border-red-200 flex items-center"
      role="alert"
    >
      <svg
        className="w-5 h-5 mr-2 shrink-0"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path
          fillRule="evenodd"
          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
          clipRule="evenodd"
        />
      </svg>
      <span>{message}</span>
    </div>
  );
}
