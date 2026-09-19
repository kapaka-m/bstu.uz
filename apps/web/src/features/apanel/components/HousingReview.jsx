import React, { useState } from "react";
import { useLanguage } from "../../../context/LanguageContext";
import { apanelApplicationsService } from "../../../services/apanelApplicationsService";
import HousingRequirements from "../../../components/housing/HousingRequirements";
import HousingReceipts from "../../../components/housing/HousingReceipts";
import FormError from "../../../components/common/FormError";

export default function HousingReview({ application, housing, onSaved }) {
  const { t } = useLanguage();
  const [verified, setVerified] = useState(false);
  const [notes, setNotes] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);
  const request = application.housing_request;
  const run = async (operation, refresh = true) => {
    setBusy(true);
    setError("");
    try {
      await operation();
      if (refresh) await onSaved();
    } catch (err) {
      setError(Object.values(err?.errors || {}).flat().join(" ") || err?.message || t("interface.housingFailed"));
    } finally { setBusy(false); }
  };
  const reviewReceipt = (id, status) => {
    const reason = status === "REJECTED" ? window.prompt(t("interface.housingRejectionReason")) : null;
    if (status === "REJECTED" && !reason?.trim()) return;
    run(() => apanelApplicationsService.reviewHousingReceipt(application.id, id, { status, rejection_reason: reason }));
  };
  const review = status => run(() => apanelApplicationsService.reviewHousing(application.id, { status, pinfl_verified: verified, admin_notes: notes }));
  const canApprove = request?.requested && housing?.eligible && housing?.first_month_paid && /^[0-9]{14}$/.test(request?.pinfl || "") && verified;
  return (
    <div className="space-y-5">
      <HousingRequirements housing={housing} />
      {error && <FormError message={error} />}
      {request?.requested && <div className="space-y-3 border-t border-gray-100 pt-4">
        <p className="text-sm font-bold text-navy">{t("interface.housingPinfl")}: <bdi>{request.pinfl || "—"}</bdi></p>
        <label className="flex items-start gap-2 text-xs font-semibold"><input type="checkbox" checked={verified} onChange={event => setVerified(event.target.checked)} className="mt-0.5" />{t("interface.housingPinflConfirm")}</label>
        <label className="block space-y-1 text-xs font-bold">{t("common.administrationNotes")}<textarea value={notes} onChange={event => setNotes(event.target.value)} maxLength={3000} className="w-full rounded-lg border border-gray-200 p-3 text-sm" /></label>
        <div className="flex flex-wrap gap-2">
          <button type="button" disabled={busy || !canApprove} onClick={() => review("APPROVED")} className="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white disabled:opacity-40">{t("apanel.workflow.approveHousing")}</button>
          <button type="button" disabled={busy} onClick={() => review("REJECTED")} className="rounded-lg bg-rose-600 px-4 py-2 text-xs font-bold text-white disabled:opacity-40">{t("apanel.workflow.rejectHousing")}</button>
          <button type="button" disabled={busy} onClick={() => review("NOT_REQUIRED")} className="rounded-lg border border-gray-200 px-4 py-2 text-xs font-bold text-navy disabled:opacity-40">{t("apanel.workflow.notRequired")}</button>
        </div>
      </div>}
      <HousingReceipts payments={request?.payments} housing={housing} busy={busy} onReview={reviewReceipt} onDownload={id => run(() => apanelApplicationsService.downloadHousingReceipt(application.id, id), false)} />
    </div>
  );
}
