import { api } from "../lib/api";

const cache = new Map();
const unwrapPayload = (response) => response?.data ?? response;

export const departmentPageCmsService = {
  async get(locale = "") {
    const key = locale || "default";
    if (cache.has(key)) return cache.get(key);
    const path = locale ? `/department-page?locale=${encodeURIComponent(locale)}` : "/department-page";
    const promise = api.get(path).then(unwrapPayload);
    cache.set(key, promise);
    return promise;
  },

  clearCache() {
    cache.clear();
  },
};
