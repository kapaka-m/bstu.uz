import { api, downloadBlob } from "../lib/api";

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
    return downloadBlob(`/student/private-documents/${documentId}/download`);
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
    return downloadBlob("/student/admission/download");
  },
  enrollment() {
    return api.get("/student/enrollment").then(unwrap);
  },
  downloadEnrollment() {
    return downloadBlob("/student/enrollment/download");
  },
  downloadContract() {
    return downloadBlob("/student/contract/download");
  },
  prikaz() {
    return api.get("/student/prikaz").then(unwrap);
  },
  downloadPrikaz() {
    return downloadBlob("/student/prikaz/download");
  },
  serviceFee() {
    return api.get("/student/service-fee").then(unwrap);
  },
  uploadServiceFeeReceipt(applicationId, file) {
    const form = new FormData();
    form.append("file", file);
    return api.post(`/applications/${applicationId}/service-fee/receipt`, form).then(unwrap);
  },
  visa() {
    return api.get("/student/visa").then(unwrap);
  },
  housing() {
    return api.get("/student/housing").then(unwrap);
  },
  submitHousingRequest(applicationId, payload) {
    return api.post(`/applications/${applicationId}/housing/request`, payload).then(unwrap);
  },
  residence() {
    return api.get("/student/residence").then(unwrap);
  },
};
