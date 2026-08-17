import { api } from "../lib/api";

export const programService = {
  getPrograms(params = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== "") {
        query.set(key, value);
      }
    });

    const queryString = query.toString();
    return api.get(`/programs${queryString ? `?${queryString}` : ""}`).then(res => res.data || []);
  },

  getProgram(slug) {
    return api.get(`/programs/${slug}`).then(res => res.data);
  },

  getCourses() {
    return api.get("/courses").then(res => res.data || []);
  }
};
