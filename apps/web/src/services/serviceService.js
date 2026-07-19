import { api } from "../lib/api";

export const serviceService = {
  getServices() {
    return api.get("/services").then(res => res.data || []);
  },

  getService(slug) {
    return api.get(`/services/${slug}`).then(res => res.data);
  }
};
