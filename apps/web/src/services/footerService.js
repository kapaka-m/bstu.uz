import { api } from "../lib/api";
import { localeStorage } from "../lib/locale";

const CACHE_TTL_MS = 30000;
const responseCache = new Map();

const cached = (key, fetcher) => {
  const locale = localeStorage.getLocale();
  const cacheKey = `${locale}:${key}`;
  const cachedEntry = responseCache.get(cacheKey);

  if (cachedEntry && Date.now() - cachedEntry.time < CACHE_TTL_MS) {
    return Promise.resolve(cachedEntry.value);
  }

  return fetcher().then((value) => {
    responseCache.set(cacheKey, { time: Date.now(), value });
    return value;
  });
};

export const footerService = {
  getPublicFooter() {
    return cached("footer-web", () => api.get("/footer-web").then((res) => res.data));
  },

  getCmsFooter() {
    return api.get("/apanel/cms/footer-web").then((res) => res.data);
  },

  updateCmsFooter(payload) {
    return api.put("/apanel/cms/footer-web", payload).then((res) => res.data);
  },

  subscribe(email) {
    return api
      .post("/newsletter-subscriptions", { email })
      .then((res) => res.data);
  },
};
