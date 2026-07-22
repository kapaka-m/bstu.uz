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

  const pending = fetcher().then((value) => {
    responseCache.set(cacheKey, { time: Date.now(), value });
    return value;
  }).catch((error) => {
    responseCache.delete(cacheKey);
    throw error;
  });

  responseCache.set(cacheKey, { time: Date.now(), value: pending });
  return pending;
};

const normalizeMenuItems = (response) => {
  if (Array.isArray(response)) return response;
  if (Array.isArray(response?.data)) return response.data;
  if (Array.isArray(response?.items)) return response.items;
  if (Array.isArray(response?.data?.items)) return response.data.items;
  return [];
};

export const menuService = {
  getMenu(location) {
    return cached(`menus:${location}`, () =>
      api.get(`/menus/${location}`)
        .then(normalizeMenuItems),
    ).catch(() => []);
  }
};
