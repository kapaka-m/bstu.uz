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

const unwrap = (response) => response?.data ?? response;

const normalizeCenter = (item = {}) => ({
  id: item.slug || item.id,
  numeric_id: item.id,
  slug: item.slug || item.id,
  name: item.name || "",
  head: item.head || "",
  headTitle: item.headTitle || "",
  officeHours: item.officeHours || "",
  about: item.about || "",
  functions: Array.isArray(item.functions) ? item.functions : [],
  image: item.image || "",
  email: item.email || "",
  phone: item.phone || "",
  sort_order: Number(item.sort_order ?? 0),
  is_active: Boolean(item.is_active ?? true),
});

export const centerService = {
  getCenters() {
    return cached("university-centers", () =>
      api
        .get("/university-centers")
        .then((res) => {
          const data = unwrap(res);
          const items = Array.isArray(data) ? data : (data?.data || []);
          return items.map(normalizeCenter);
        })
    );
  },

  getCenter(slug) {
    return api
      .get(`/university-centers/${slug}`)
      .then((res) => normalizeCenter(unwrap(res)));
  },

  getSettings() {
    return cached("university-centers/settings", () =>
      api.get("/university-centers/settings").then((res) => unwrap(res))
    );
  },

  adminGetSettings() {
    return api
      .get("/apanel/cms/university-centers/settings")
      .then((res) => unwrap(res));
  },

  adminUpdateSettings(data) {
    // Clear cache
    responseCache.clear();
    return api
      .put("/apanel/cms/university-centers/settings", data)
      .then((res) => unwrap(res));
  },

  adminGetCenters() {
    return api
      .get("/apanel/university-centers?per_page=100&sort_by=sort_order&sort_dir=asc")
      .then((res) => {
        const data = unwrap(res);
        const items = Array.isArray(data) ? data : (data?.data || []);
        return items.map(normalizeCenter);
      });
  },

  adminGetCenter(id) {
    return api
      .get(`/apanel/university-centers/${id}`)
      .then((res) => unwrap(res));
  },

  adminCreateCenter(data) {
    // Clear cache
    responseCache.clear();
    return api
      .post("/apanel/university-centers", data)
      .then((res) => unwrap(res));
  },

  adminUpdateCenter(id, data) {
    // Clear cache
    responseCache.clear();
    return api
      .put(`/apanel/university-centers/${id}`, data)
      .then((res) => unwrap(res));
  },

  adminDeleteCenter(id) {
    // Clear cache
    responseCache.clear();
    return api
      .delete(`/apanel/university-centers/${id}`)
      .then((res) => unwrap(res));
  },
};
