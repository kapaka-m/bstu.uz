import React, { useState } from "react";
import { Upload } from "lucide-react";
import { useLanguage } from "../../../context/LanguageContext";
import { studentPortalService } from "../../../services/studentPortalService";
import HousingRequirements from "../../../components/housing/HousingRequirements";
import HousingReceipts from "../../../components/housing/HousingReceipts";
import FormError from "../../../components/common/FormError";

function ReceiptInput({ required = true }) {
  const { t } = useLanguage();
  const [filename, setFilename] = useState("");
  return (
    <label className="min-w-0 space-y-1 text-xs font-bold">
      <span>{t("interface.housingReceipt")}</span>
      <input name="file" type="file" required={required} accept=".pdf,.jpg,.jpeg,.png,.webp,.heic,.heif" onChange={event => setFilename(event.target.files?.[0]?.name || "")} className="peer sr-only" />
      <span className="flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-navy peer-focus-visible:outline-2 peer-focus-visible:outline-primary"><Upload aria-hidden="true" className="h-4 w-4 shrink-0" /><bdi className="min-w-0 break-all">{filename || t("interface.housingChooseReceipt")}</bdi></span>
    </label>
  );
}

export default function StudentHousing({ application, housing, onSaved }) {
  const { t } = useLanguage();
  const request = application.housing_request;
  const payments = request?.payments || [];
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const canApply = housing?.eligible && (!request?.requested || request.status === "REJECTED" || !request.pinfl || !request.start_month);
  const startMonth = request?.start_month?.slice(0, 7) || housing?.current_month;
  const openMonths = (housing?.due_months || []).filter(month => !payments.some(payment => payment.month.slice(0, 7) === month && ["APPROVED", "UPLOADED"].includes(payment.status)));
  const firstReceiptPresent = payments.some(payment => payment.month.slice(0, 7) === startMonth && ["APPROVED", "UPLOADED"].includes(payment.status));
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
  const submit = (event, initial) => {
    event.preventDefault();
    const data = new FormData(event.currentTarget);
    const file = data.get("file");
    if (file?.size > 10 * 1024 * 1024) { setError(t("document.fileTooLarge")); return; }
    if (!file?.size) data.delete("file");
    run(() => initial ? studentPortalService.submitHousingRequest(application.id, data) : studentPortalService.uploadHousingReceipt(application.id, data));
  };
  const inputClass = "w-full min-w-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-navy";
  return (
    <div className="space-y-5">
      <HousingRequirements housing={housing} />
      {error && <FormError message={error} />}
      {!housing?.eligible && <p className="text-xs font-semibold text-amber-700">{t("interface.housingPrerequisites")}</p>}
      {canApply && <form onSubmit={event => submit(event, true)} className="grid gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2">
        <label className="space-y-1 text-xs font-bold">{t("interface.housingPinfl")}<input name="pinfl" dir="ltr" defaultValue={request?.pinfl || ""} required pattern="[0-9]{14}" maxLength={14} inputMode="numeric" autoComplete="off" className={inputClass} /></label>
        <label className="space-y-1 text-xs font-bold">{t("interface.housingStartMonth")}<input name="month" dir="ltr" value={startMonth || ""} readOnly required className={inputClass} /></label>
        <label className="space-y-1 text-xs font-bold">{t("housing.preferredRoom")}<input name="preferred_room_type" maxLength={120} defaultValue={request?.preferred_room_type || ""} className={inputClass} /></label>
        <ReceiptInput required={!firstReceiptPresent} />
        <label className="space-y-1 text-xs font-bold sm:col-span-2">{t("common.notes")}<textarea name="notes" maxLength={2000} defaultValue={request?.notes || ""} className={inputClass} /></label>
        <button disabled={busy} className="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white disabled:opacity-50"><Upload className="h-4 w-4" />{t("housing.requestHousing")}</button>
      </form>}
      {!canApply && request?.requested && !["REJECTED", "NOT_REQUIRED"].includes(request.status) && openMonths.length > 0 && <form onSubmit={event => submit(event, false)} className="grid gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2">
        <label className="space-y-1 text-xs font-bold">{t("interface.housingMonth")}<select name="month" required className={inputClass}>{openMonths.map(month => <option key={month} value={month}>{month}</option>)}</select></label>
        <ReceiptInput />
        <button disabled={busy} className="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white disabled:opacity-50"><Upload className="h-4 w-4" />{t("interface.housingUpload")}</button>
      </form>}
      <HousingReceipts payments={payments} housing={housing} busy={busy} onDownload={id => run(() => studentPortalService.downloadHousingReceipt(application.id, id), false)} />
    </div>
  );
}
