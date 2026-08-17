import React from "react";
import { useLanguage } from "../../../context/LanguageContext";

export default function StatusBadge({ status }) {
  const { t, hasTranslation } = useLanguage();
  const normalized = String(status || "")
    .toLowerCase()
    .trim()
    .replace(/[\s-]+/g, "_");
  const aliases = {
    not_started: "notStarted",
    pending_review: "pendingReview",
    pending_verification: "pendingVerification",
    waiting_documents: "waitingDocuments",
    waiting_payment: "waitingPayment",
  };
  const translationKey = normalized ? `status.${aliases[normalized] || normalized}` : "";
  const translated = translationKey && hasTranslation?.(translationKey) ? t(translationKey) : "";
  const label = normalized
    ? translated ||
      normalized
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase())
    : "—";

  const configs = {
    // Boolean active/inactive
    true: "bg-emerald-50 text-emerald-700 border-emerald-100",
    false: "bg-rose-50 text-rose-700 border-rose-100",
    active: "bg-emerald-50 text-emerald-700 border-emerald-100",
    inactive: "bg-rose-50 text-rose-700 border-rose-100",

    // Application statuses
    draft: "bg-blue-50 text-blue-700 border-blue-100",
    submitted: "bg-amber-50 text-amber-700 border-amber-100",
    under_review: "bg-orange-50 text-orange-700 border-orange-100",
    missing_documents: "bg-red-50 text-red-700 border-red-100",
    accepted: "bg-emerald-50 text-emerald-700 border-emerald-100",
    rejected: "bg-rose-50 text-rose-700 border-rose-100",
    contract_pending: "bg-purple-50 text-purple-700 border-purple-100",
    payment_pending: "bg-purple-50 text-purple-700 border-purple-100",
    enrolled: "bg-teal-50 text-teal-700 border-teal-100",
    active_student: "bg-cyan-50 text-cyan-700 border-cyan-100",
    graduated: "bg-gray-50 text-gray-700 border-gray-100",

    // Billing/payment
    paid: "bg-emerald-50 text-emerald-700 border-emerald-100",
    pending: "bg-amber-50 text-amber-700 border-amber-100",
    failed: "bg-red-50 text-red-700 border-red-100",
    verified: "bg-emerald-50 text-emerald-700 border-emerald-100",
    action_required: "bg-amber-50 text-amber-700 border-amber-100",
    documents_required: "bg-amber-50 text-amber-700 border-amber-100",
    application_fee_required: "bg-amber-50 text-amber-700 border-amber-100",
    reupload_required: "bg-rose-50 text-rose-700 border-rose-100",
    application_rejected: "bg-rose-50 text-rose-700 border-rose-100",
    admission_issued: "bg-emerald-50 text-emerald-700 border-emerald-100",
    result_issued: "bg-emerald-50 text-emerald-700 border-emerald-100",
    student_review_required: "bg-amber-50 text-amber-700 border-amber-100",
  };

  const style =
    configs[normalized] || "bg-gray-50 text-gray-700 border-gray-100";

  return (
    <span
      className={`inline-flex min-h-6 max-w-full items-center rounded-full border px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider break-words ${style}`}
    >
      {label}
    </span>
  );
}
