import { api } from "../lib/api";

const cache = new Map();

const unwrapPayload = (response) => response?.data ?? response;

export const authCmsService = {
  async get(locale = "") {
    const key = locale || "default";
    if (cache.has(key)) return cache.get(key);
    const path = locale ? `/auth-cms?locale=${encodeURIComponent(locale)}` : "/auth-cms";
    const promise = api.get(path).then(unwrapPayload);
    cache.set(key, promise);
    return promise;
  },

  clearCache() {
    cache.clear();
  },
};
