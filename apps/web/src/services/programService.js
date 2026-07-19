import { api } from "../lib/api";

export const programService = {
  getPrograms() {
    return api.get("/programs").then(res => res.data || []);
  },

  getProgram(slug) {
    return api.get(`/programs/${slug}`).then(res => res.data);
  },

  getCourses() {
    return api.get("/courses").then(res => res.data || []);
  }
};
