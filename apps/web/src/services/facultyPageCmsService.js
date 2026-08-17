import { api } from "../lib/api";

const cache = new Map();
const unwrapPayload = (response) => response?.data ?? response;

export const facultyPageCmsService = {
  async get(locale = "") {
    const key = locale || "default";
    if (cache.has(key)) return cache.get(key);
    const path = locale ? `/faculty-page?locale=${encodeURIComponent(locale)}` : "/faculty-page";
    const promise = api.get(path).then(unwrapPayload);
    cache.set(key, promise);
    return promise;
  },

  clearCache() {
    cache.clear();
  },
};
