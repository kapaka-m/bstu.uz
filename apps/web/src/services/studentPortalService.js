import { api } from "../lib/api";
import { authStorage } from "../lib/auth";

const unwrap = (res) => res?.data ?? res;

export const studentPortalService = {
  summary() {
    return api.get("/student/application-summary").then(unwrap);
  },
  profile() {
    return api.get("/student/profile").then(unwrap);
  },
  academicInformation() {
    return api.get("/student/academic-information").then(unwrap);
  },
  documents() {
    return api.get("/student/documents/checklist").then(unwrap);
  },
  uploadDocument(applicationId, documentType, file, studentNotes = "") {
    const form = new FormData();
    form.append("document_type", documentType);
    form.append("file", file);
    if (studentNotes) form.append("student_notes", studentNotes);
    return api.post(`/applications/${applicationId}/documents/private`, form).then(unwrap);
  },
  downloadDocument(documentId) {
    return downloadPrivate(`/student/private-documents/${documentId}/download`);
  },
  equivalency() {
    return api.get("/student/equivalency").then(unwrap);
  },
  acceptEquivalency(applicationId) {
    return api.post(`/applications/${applicationId}/equivalency/accept`).then(unwrap);
  },
  requestEquivalencyReview(applicationId, reason) {
    return api.post(`/applications/${applicationId}/equivalency/request-review`, { reason }).then(unwrap);
  },
  payment() {
    return api.get("/student/application-fee").then(unwrap);
  },
  uploadPaymentReceipt(applicationId, file) {
    const form = new FormData();
    form.append("file", file);
    return api.post(`/applications/${applicationId}/application-fee/receipt`, form).then(unwrap);
  },
  contractAdvance() {
    return api.get("/student/contract-advance").then(unwrap);
  },
  uploadContractAdvanceReceipt(applicationId, file) {
    const form = new FormData();
    form.append("file", file);
    return api.post(`/applications/${applicationId}/contract-advance/receipt`, form).then(unwrap);
  },
  admission() {
    return api.get("/student/admission").then(unwrap);
  },
  downloadAdmission() {
    return downloadPrivate("/student/admission/download");
  },
  enrollment() {
    return api.get("/student/enrollment").then(unwrap);
  },
  downloadEnrollment() {
    return downloadPrivate("/student/enrollment/download");
  },
};

async function downloadPrivate(path) {
  const base = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1";
  const response = await fetch(`${base}${path}`, {
    headers: {
      Authorization: `Bearer ${authStorage.getToken()}`,
      Accept: "application/octet-stream",
    },
  });
  if (!response.ok) throw new Error("Download failed");
  const blob = await response.blob();
  const url = URL.createObjectURL(blob);
  window.open(url, "_blank", "noopener,noreferrer");
  setTimeout(() => URL.revokeObjectURL(url), 30000);
}
