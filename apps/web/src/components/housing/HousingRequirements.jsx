import React from "react";
import { CheckCircle, Clock } from "lucide-react";
import { useLanguage } from "../../context/LanguageContext";

export default function HousingRequirements({ housing }) {
  const { t } = useLanguage();
  const labels = {
    admission: "student.nav.admission",
    enrollment: "interface.housingEnrollment",
    prikaz: "student.nav.prikaz",
    visa: "interface.housingVisa",
    contract_payment: "interface.housingContractPayment",
  };
  const rows = Object.entries(labels).map(([key, label]) => [label, housing?.requirements?.[key]]);
  rows.push(["interface.housingPinfl", housing?.pinfl_verified], ["interface.housingFirstPayment", housing?.first_month_paid]);
  return (
    <div className="space-y-3">
      <h3 className="text-sm font-bold text-navy">{t("interface.housingRequirements")}</h3>
      <ul className="grid gap-2 sm:grid-cols-2">
        {rows.map(([label, complete]) => (
          <li key={label} className="flex min-w-0 items-start gap-2 text-xs font-semibold">
            {complete ? <CheckCircle aria-hidden="true" className="h-4 w-4 shrink-0 text-emerald-600" /> : <Clock aria-hidden="true" className="h-4 w-4 shrink-0 text-amber-600" />}
            <span className="break-words">{t(label)} <span className="sr-only">{t(complete ? "status.completed" : "status.pending")}</span></span>
          </li>
        ))}
      </ul>
      <p className="text-xs font-semibold text-gray-500">{t("interface.housingMonthlyFee").replace(":amount", housing?.monthly_amount ?? 40)}</p>
    </div>
  );
}
