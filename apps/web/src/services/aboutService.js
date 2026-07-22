import { api } from "../lib/api";
import { localeStorage } from "../lib/locale";

const unwrap = (response) => response?.data ?? response;
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

export const aboutService = {
  getPage(locale) {
    const query = locale ? `?locale=${encodeURIComponent(locale)}` : "";
    return cached(`about-page:${locale || "current"}`, () =>
      api.get(`/about-page${query}`).then((response) => unwrap(response)),
    );
  },
};
