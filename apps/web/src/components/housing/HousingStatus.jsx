import React from "react";
import { useLanguage } from "../../context/LanguageContext";

const keys = {
  NOT_REQUESTED: "status.notStarted",
  NOT_STARTED: "status.notStarted",
  UPLOADED: "status.pending",
  UNDER_REVIEW: "status.under_review",
  APPROVED: "status.approved",
  COMPLETED: "status.completed",
  REJECTED: "status.rejected",
  NOT_REQUIRED: "apanel.workflow.notRequired",
};

export default function HousingStatus({ status }) {
  const { t } = useLanguage();
  return <span className="text-xs font-semibold">{t(keys[status] || "status.notStarted")}</span>;
}
