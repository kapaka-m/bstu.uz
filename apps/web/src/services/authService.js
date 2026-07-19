import { api } from "../lib/api";

export const authService = {
  login(email, password) {
    return api.post("/auth/login", { email, password }).then(res => res.data);
  },

  register(name, email, password, password_confirmation) {
    return api.post("/auth/register", { name, email, password, password_confirmation }).then(res => res.data);
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
