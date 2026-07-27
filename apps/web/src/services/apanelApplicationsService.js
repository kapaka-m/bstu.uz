import { api, downloadBlob } from "../lib/api";

const unwrap = (res) => res?.data ?? res;

export const apanelApplicationsService = {
  list(params = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== "") query.append(key, value);
    });
    return api.get(`/apanel/applications-workflow${query.toString() ? `?${query}` : ""}`).then(unwrap);
  },
  get(id) {
    return api.get(`/apanel/applications-workflow/${id}`).then(unwrap);
  },
  documents(id) {
    return api.get(`/apanel/applications-workflow/${id}/documents`).then(unwrap);
  },
  reviewDocument(id, documentId, payload) {
    return api.post(`/apanel/applications-workflow/${id}/documents/${documentId}/review`, payload).then(unwrap);
  },
  downloadDocument(id, documentId) {
    return downloadBlob(`/apanel/applications-workflow/${id}/documents/${documentId}/download`);
  },
  requestDocument(id, payload) {
    return api.post(`/apanel/applications-workflow/${id}/documents/request`, payload).then(unwrap);
  },
  equivalency(id) {
    return api.get(`/apanel/applications-workflow/${id}/equivalency`).then(unwrap);
  },
  saveEquivalency(id, payload) {
    return api.put(`/apanel/applications-workflow/${id}/equivalency`, payload).then(unwrap);
  },
  issueEquivalency(id) {
    return api.post(`/apanel/applications-workflow/${id}/equivalency/issue`).then(unwrap);
  },
  payment(id) {
    return api.get(`/apanel/applications-workflow/${id}/payments`).then(unwrap);
  },
  reviewPayment(id, paymentId, payload) {
    return api.post(`/apanel/applications-workflow/${id}/payments/${paymentId}/review`, payload).then(unwrap);
  },
  reviewContractPayment(id, paymentId, payload) {
    return api.post(`/apanel/applications-workflow/${id}/contract-payments/${paymentId}/review`, payload).then(unwrap);
  },
  finalReview(id) {
    return api.get(`/apanel/applications-workflow/${id}/final-review`).then(unwrap);
  },
  approveFinalReview(id) {
    return api.post(`/apanel/applications-workflow/${id}/final-review/approve`).then(unwrap);
  },
  returnForCorrection(id, reason) {
    return api.post(`/apanel/applications-workflow/${id}/final-review/return`, { reason }).then(unwrap);
  },
  reject(id, reason) {
    return api.post(`/apanel/applications-workflow/${id}/final-review/reject`, { reason }).then(unwrap);
  },
  admission(id) {
    return api.get(`/apanel/applications-workflow/${id}/admission`).then(unwrap);
  },
  issueAdmission(id) {
    return api.post(`/apanel/applications-workflow/${id}/admission/issue`).then(unwrap);
  },
  downloadAdmission(id) {
    return downloadBlob(`/apanel/applications-workflow/${id}/admission/download`);
  },
  downloadContract(id) {
    return downloadBlob(`/apanel/applications-workflow/${id}/contract/download`);
  },
  issueEnrollment(id) {
    return api.post(`/apanel/applications-workflow/${id}/enrollment/issue`).then(unwrap);
  },
  downloadEnrollment(id) {
    return downloadBlob(`/apanel/applications-workflow/${id}/enrollment/download`);
  },
  issuePrikaz(id) {
    return api.post(`/apanel/applications-workflow/${id}/prikaz/issue`).then(unwrap);
  },
  downloadPrikaz(id) {
    return downloadBlob(`/apanel/applications-workflow/${id}/prikaz/download`);
  },
  reviewServiceFee(id, paymentId, payload) {
    return api.post(`/apanel/applications-workflow/${id}/service-fees/${paymentId}/review`, payload).then(unwrap);
  },
  updateVisa(id, payload) {
    return api.put(`/apanel/applications-workflow/${id}/visa`, payload).then(unwrap);
  },
  reviewHousing(id, payload) {
    return api.post(`/apanel/applications-workflow/${id}/housing/review`, payload).then(unwrap);
  },
  updateResidence(id, payload) {
    return api.put(`/apanel/applications-workflow/${id}/residence`, payload).then(unwrap);
  },
};
