import { api } from "../lib/api";

export const serviceService = {
  getSettings() {
    return api.get("/services/settings").then(res => res.data || {});
  },

  getServices() {
    return api.get("/services").then(res => res.data || []);
  },

  getHomeServices(limit) {
    const query = new URLSearchParams({ home: "1" });
    if (limit) query.set("limit", String(limit));
    return api.get(`/services?${query.toString()}`).then(res => res.data || []);
  }
};
