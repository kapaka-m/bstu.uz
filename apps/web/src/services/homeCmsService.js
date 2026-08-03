import { api } from "../lib/api";
import { localeStorage } from "../lib/locale";

const unwrap = (response) => response?.data ?? response;
const cache = new Map();

export const homeCmsService = {
  getSections() {
    const locale = localeStorage.getLocale() || "default";
    if (!cache.has(locale)) {
      cache.set(locale, api.get("/home-sections").then((response) => unwrap(response) || {}));
    }
    return cache.get(locale);
  },

  clearCache() {
    cache.clear();
  },
};
