import React from "react";
import { Check, Download, X } from "lucide-react";
import { useLanguage } from "../../context/LanguageContext";
import HousingStatus from "./HousingStatus";

export default function HousingReceipts({ payments = [], housing, onDownload, onReview, busy }) {
  const { t } = useLanguage();
  return (
    <div className="space-y-3 border-t border-gray-100 pt-4">
      <h3 className="text-sm font-bold text-navy">{t("interface.housingReceipts")}</h3>
      <p className="text-xs font-semibold text-gray-500">
        {housing?.due_months?.length ? `${t("interface.housingDueMonths")}: ${housing.due_months.join(", ")}` : t("interface.housingNoDueMonths")}
      </p>
      {!payments.length && <p className="text-xs text-gray-500">{t("interface.housingNoReceipts")}</p>}
      {payments.map(payment => (
        <div key={payment.id} className="flex min-w-0 flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3">
          <div className="min-w-0 space-y-1 text-xs">
            <p className="font-bold text-navy">{payment.month.slice(0, 7)} · {payment.amount} {payment.currency}</p>
            <p className="break-words text-gray-500"><HousingStatus status={payment.status} /></p>
            {payment.rejection_reason && <p className="break-words text-rose-700">{payment.rejection_reason}</p>}
          </div>
          <div className="flex gap-2">
            <button type="button" disabled={!!busy} onClick={() => onDownload(payment.id)} aria-label={t("button.download")} title={t("button.download")} className="rounded-lg border border-gray-200 p-2 text-navy disabled:opacity-50"><Download className="h-4 w-4" /></button>
            {onReview && payment.status === "UPLOADED" && <>
              <button type="button" disabled={!!busy} onClick={() => onReview(payment.id, "APPROVED")} aria-label={t("button.approve")} title={t("button.approve")} className="rounded-lg bg-emerald-600 p-2 text-white disabled:opacity-50"><Check className="h-4 w-4" /></button>
              <button type="button" disabled={!!busy} onClick={() => onReview(payment.id, "REJECTED")} aria-label={t("button.reject")} title={t("button.reject")} className="rounded-lg bg-rose-600 p-2 text-white disabled:opacity-50"><X className="h-4 w-4" /></button>
            </>}
          </div>
        </div>
      ))}
    </div>
  );
}
