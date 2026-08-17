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

export const profileSlugFromName = (name = "") =>
  String(name)
    .trim()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");

const profileSlugFromImage = (image = "") => {
  const filename = String(image).split(/[/?#]/).filter(Boolean).pop() || "";
  const basename = filename.replace(/\.[a-z0-9]+$/i, "");
  return profileSlugFromName(basename.replace(/[_]+/g, " "));
};

const cleanValue = (value = "") => {
  const text = String(value || "").trim();
  return text === "-" ? "" : text;
};

const normalizeCenter = (item = {}) => {
  const defaultTrans = item.translations?.find((t) => t.locale === "en") || item.translations?.[0] || {};
  const head = cleanValue(item.head || defaultTrans.head);
  const image = item.image || "";
  return {
    id: item.slug || item.id,
    numeric_id: item.id,
    slug: item.slug || item.id,
    name: item.name || defaultTrans.name || "",
    head,
    headProfileSlug: item.headProfileSlug || item.head_profile_slug || profileSlugFromName(head) || profileSlugFromImage(image),
    headTitle: cleanValue(item.headTitle || defaultTrans.head_title || defaultTrans.headTitle),
    officeHours: cleanValue(item.officeHours || defaultTrans.office_hours || defaultTrans.officeHours),
    about: item.about || defaultTrans.about || "",
    functions: Array.isArray(item.functions) ? item.functions : (Array.isArray(defaultTrans.functions) ? defaultTrans.functions : []),
    image,
    email: cleanValue(item.email),
    phone: cleanValue(item.phone),
    sort_order: Number(item.sort_order ?? 0),
    is_active: Boolean(item.is_active ?? true),
    headDescription: cleanValue(item.headDescription || defaultTrans.head_description || defaultTrans.headDescription),
  };
};

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
