import React from "react";
import { AlertTriangle } from "lucide-react";

export default function ConfirmDialog({
  isOpen,
  title,
  message,
  onConfirm,
  onCancel,
  confirmText = "Delete",
  cancelText = "Cancel",
}) {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-9999 flex items-center justify-center bg-navy/40 backdrop-blur-xs p-4">
      <div className="bg-white border border-gray-100 rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        <div className="flex items-center gap-3 mb-4 text-red-600">
          <div className="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
            <AlertTriangle className="w-5 h-5" />
          </div>
          <h3 className="font-extrabold text-navy text-lg">
            {title || "Are you sure?"}
          </h3>
        </div>

        <p className="text-gray-500 text-sm leading-relaxed mb-6 font-medium">
          {message ||
            "This action cannot be undone. Please confirm to proceed."}
        </p>

        <div className="flex justify-end gap-3">
          <button
            onClick={onCancel}
            className="px-4 py-2 border border-gray-200 hover:border-gray-300 text-navy font-bold text-xs rounded-xl transition-all cursor-pointer bg-white"
          >
            {cancelText}
          </button>
          <button
            onClick={onConfirm}
            className="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl transition-all shadow-md shadow-red-600/20 cursor-pointer"
          >
            {confirmText}
          </button>
        </div>
      </div>
    </div>
  );
}
