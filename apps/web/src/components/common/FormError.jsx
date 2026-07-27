import React from "react";
import { AlertCircle } from "lucide-react";

export default function FormError({ message }) {
  if (!message) return null;

  return (
    <div
      className="p-4 mb-4 text-sm text-red-700 bg-red-50 rounded-lg border border-red-200 flex items-center"
      role="alert"
    >
      <AlertCircle className="w-5 h-5 me-2 shrink-0" />
      <span>{message}</span>
    </div>
  );
}
