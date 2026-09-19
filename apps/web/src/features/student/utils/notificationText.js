const documentRequirementKeys = {
  passport: ["interface.documentPassportName", "interface.documentPassportDescription"],
  photo: ["interface.documentPhotoName", "interface.documentPhotoDescription"],
  secondary_certificate: ["interface.documentSecondaryCertificateName", "interface.documentSecondaryCertificateDescription"],
  secondary_transcript: ["interface.documentSecondaryTranscriptName", "interface.documentSecondaryTranscriptDescription"],
  bachelor_degree: ["interface.documentBachelorDegreeName", "interface.documentBachelorDegreeDescription"],
  bachelor_transcript: ["interface.documentBachelorTranscriptName", "interface.documentBachelorTranscriptDescription"],
  master_degree: ["interface.documentMasterDegreeName", "interface.documentMasterDegreeDescription"],
  master_transcript: ["interface.documentMasterTranscriptName", "interface.documentMasterTranscriptDescription"],
  university_transcript: ["interface.documentUniversityTranscriptName", "interface.documentUniversityTranscriptDescription"],
};

const legacyDocumentNameToType = {
  "Passport Copy": "passport",
  "Personal Photo": "photo",
  "Secondary School Certificate": "secondary_certificate",
  "Secondary School Transcript": "secondary_transcript",
  "Bachelor Degree / Diploma": "bachelor_degree",
  "Bachelor Transcript": "bachelor_transcript",
  "Master Degree / Diploma": "master_degree",
  "Master Transcript": "master_transcript",
  "University Transcript": "university_transcript",
};

const legacyNotificationTitleKeys = {
  "Document uploaded": "interface.notificationDocumentUploadedTitle",
  "Application status updated": "interface.notificationApplicationStatusUpdatedTitle",
};

const statusKeyAliases = {
  "action required": "action_required",
  "not required": "notRequired",
  not_required: "notRequired",
  "not uploaded": "notUploaded",
  not_uploaded: "notUploaded",
  "not eligible": "notEligible",
  not_eligible: "notEligible",
  "in progress": "inProgress",
  in_progress: "inProgress",
  notstarted: "notStarted",
  "not started": "notStarted",
  "pending review": "pendingReview",
  "pending verification": "pendingVerification",
  "reupload required": "reuploadRequired",
  reupload_required: "reuploadRequired",
  "under review": "underReview",
  under_review: "underReview",
  "waiting documents": "waitingDocuments",
  "waiting payment": "waitingPayment",
};

const statusTranslationKeys = {
  documents_required: "interface.applicationStatusDocumentsRequired",
  documents_under_review: "interface.applicationStatusDocumentsUnderReview",
  action_required: "interface.statusActionRequired",
  inProgress: "interface.statusInProgress",
  notEligible: "interface.statusNotEligible",
  notRequired: "interface.statusNotRequired",
  notUploaded: "interface.statusNotUploaded",
  uploaded: "interface.statusUploaded",
  required: "interface.statusRequired",
  underReview: "interface.statusUnderReview",
  reuploadRequired: "interface.statusReuploadRequired",
};

const renderTemplate = (template, values = {}) =>
  Object.entries(values).reduce(
    (text, [key, value]) => text.replaceAll(`:${key}`, value ?? ""),
    template,
  );

const normalizeStatusKey = (status = "") => {
  const normalized = String(status || "")
    .trim()
    .replace(/([a-z])([A-Z])/g, "$1_$2")
    .replace(/[\s-]+/g, "_")
    .toLowerCase();

  return statusKeyAliases[normalized] || statusKeyAliases[String(status || "").trim().toLowerCase()] || normalized;
};

const translateMaybe = (value, t, hasTranslation) =>
  hasTranslation?.(value) ? t(value) : value;

export const translateLegacyDocumentName = (value, t, hasTranslation) => {
  const type = legacyDocumentNameToType[value] || (documentRequirementKeys[value] ? value : "");
  const translationKey = type ? documentRequirementKeys[type]?.[0] : "";

  return translationKey && hasTranslation?.(translationKey) ? t(translationKey) : value;
};

export const translateNotificationTitle = (title, t, hasTranslation) => {
  const translationKey = legacyNotificationTitleKeys[title];
  if (translationKey && hasTranslation?.(translationKey)) return t(translationKey);

  return translateMaybe(title, t, hasTranslation);
};

export const translateNotificationStatus = (status, t, hasTranslation) => {
  const normalized = normalizeStatusKey(status);
  const translationKey = statusTranslationKeys[normalized];
  if (translationKey && hasTranslation?.(translationKey)) return t(translationKey);

  const key = `status.${normalized}`;
  return hasTranslation?.(key) ? t(key) : status;
};

export const translateNotificationMessage = (message, t, hasTranslation) => {
  if (hasTranslation?.(message)) return t(message);

  const documentMatch = String(message || "").match(/^(.+) was uploaded and is waiting for review\.$/);
  if (documentMatch && hasTranslation?.("interface.notificationDocumentUploadedMessage")) {
    return renderTemplate(t("interface.notificationDocumentUploadedMessage"), {
      document: translateLegacyDocumentName(documentMatch[1], t, hasTranslation),
    });
  }

  const statusMatch = String(message || "").match(/^Your application moved to (.+)\.$/);
  if (statusMatch && hasTranslation?.("interface.notificationApplicationStatusUpdatedMessage")) {
    return renderTemplate(t("interface.notificationApplicationStatusUpdatedMessage"), {
      status: translateNotificationStatus(statusMatch[1], t, hasTranslation),
    });
  }

  return message;
};
