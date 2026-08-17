import { api } from "../lib/api";

export const studentService = {
  getProfile() {
    return api.get("/student/profile").then(res => res.data);
  },

  updateProfile(data) {
    return api.put("/student/profile", data).then(res => res.data);
  },

  getNotifications() {
    return api.get("/student/notifications").then(res => res.data || []);
  },

  markNotificationRead(id) {
    return api.patch(`/student/notifications/${id}/read`).then(res => res.data);
  },

  markAllNotificationsRead() {
    return api.post("/student/notifications/read-all").then(res => res.data);
  },

  getContracts() {
    return api.get("/student/contracts").then(res => res.data || []);
  },

  getPayments() {
    return api.get("/student/payments").then(res => res.data || []);
  },

  getDocumentRequests() {
    return api.get("/student/document-requests").then(res => res.data || []);
  },

  getSupportTickets() {
    return api.get("/student/support-tickets").then(res => res.data || []);
  },

  createSupportTicket(data) {
    return api.post("/student/support-tickets", data).then(res => res.data);
  },

  addSupportTicketMessage(id, data) {
    return api.post(`/student/support-tickets/${id}/messages`, data).then(res => res.data);
  }
};
