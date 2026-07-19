import { api } from "../lib/api";

export const staffService = {
  getStaff() {
    return api.get("/staff").then(res => res.data || []);
  },

  getStaffProfile(slug) {
    return api.get(`/staff/${slug}`).then(res => res.data);
  }
};
