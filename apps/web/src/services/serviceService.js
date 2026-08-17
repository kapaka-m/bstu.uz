import { api } from "../lib/api";

const unwrap = (response) => response?.data ?? response;
const unwrapList = (response) => {
  const payload = unwrap(response);
  return Array.isArray(payload) ? payload : (payload?.data || []);
};
const unwrapObject = (response) => {
  const payload = unwrap(response);
  return payload?.data || payload || {};
};

export const serviceService = {
  getSettings() {
    return api.get("/services/settings").then(unwrapObject);
  },

  getServices() {
    return api.get("/services").then(unwrapList);
  },

  getHomeServices(limit) {
    const query = new URLSearchParams({ home: "1" });
    if (limit) query.set("limit", String(limit));
    return api.get(`/services?${query.toString()}`).then(unwrapList);
  }
};
