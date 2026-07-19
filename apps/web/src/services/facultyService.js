import { api } from "../lib/api";

export const facultyService = {
  getFaculties() {
    return api.get("/faculties").then(res => res.data || []);
  },

  getFaculty(slug) {
    return api.get(`/faculties/${slug}`).then(res => res.data);
  }
};
