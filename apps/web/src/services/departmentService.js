import { api } from "../lib/api";

export const departmentService = {
  getDepartments() {
    return api.get("/departments").then(res => res.data || []);
  },

  getDepartment(slug) {
    return api.get(`/departments/${slug}`).then(res => res.data);
  }
};
