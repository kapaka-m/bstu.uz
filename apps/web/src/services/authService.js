import { api } from "../lib/api";

export const authService = {
  login(email, password, intendedRole = null) {
    return api.post("/auth/login", {
      email,
      password,
      ...(intendedRole ? { intended_role: intendedRole } : {}),
    }).then(res => res.data);
  },

  logout() {
    return api.post("/auth/logout").then(res => res);
  },

  getCurrentUser() {
    return api.get("/auth/user").then(res => res.data);
  },

  forgotPassword(email) {
    return api.post("/auth/forgot-password", { email }).then(res => res);
  },

  resetPassword(data) {
    return api.post("/auth/reset-password", data).then(res => res);
  }
};
