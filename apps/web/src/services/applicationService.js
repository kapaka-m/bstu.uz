import { api } from "../lib/api";

export const applicationService = {
  getApplications() {
    return api.get("/applications").then(res => res.data || []);
  },

  getApplication(id) {
    return api.get(`/applications/${id}`).then(res => res.data);
  },

  createApplication(data) {
    return api.post("/applications", data).then(res => res.data);
  },

  updateApplication(id, data) {
    return api.put(`/applications/${id}`, data).then(res => res.data);
  },

  submitApplication(id) {
    return api.post(`/applications/${id}/submit`).then(res => res.data);
  },

  uploadDocument(applicationId, documentType, file) {
    const formData = new FormData();
    formData.append("document_type", documentType);
    formData.append("document_name", documentType);
    formData.append("file", file);
    return api.post(`/applications/${applicationId}/documents`, formData).then(res => res.data);
  },

  deleteDocument(applicationId, documentId) {
    return api.delete(`/applications/${applicationId}/documents/${documentId}`).then(res => res.data);
  },

  downloadDocument(documentId) {
    return api.get(`/student/documents/${documentId}/download`);
  }
};
