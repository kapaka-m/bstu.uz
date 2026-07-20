import { api } from "../lib/api";
import { localeStorage } from "../lib/locale";

const CACHE_TTL_MS = 30000;
const cache = new Map();

const getLocale = (locale) => locale || localeStorage.getLocale();

const withLocale = (path, locale) => {
  const currentLocale = getLocale(locale);
  const separator = path.includes("?") ? "&" : "?";
  return `${path}${separator}locale=${encodeURIComponent(currentLocale)}`;
};

const cached = (key, fetcher, locale) => {
  const currentLocale = getLocale(locale);
  const cacheKey = `${currentLocale}:${key}`;
  const entry = cache.get(cacheKey);

  if (entry && Date.now() - entry.time < CACHE_TTL_MS) {
    return Promise.resolve(entry.value);
  }

  return fetcher().then((value) => {
    cache.set(cacheKey, { time: Date.now(), value });
    return value;
  });
};

const unwrap = (response) => response?.data ?? response;

const normalizeProfile = (item = {}) => ({
  id: item.id,
  slug: item.slug,
  path: item.path || `/profile/${item.slug}`,
  name: item.name || item.full_name || "",
  full_name: item.full_name || item.name || "",
  role: item.position || item.title || "",
  position: item.position || item.title || "",
  title: item.title || item.position || "",
  degree: item.degree || item.position || "",
  photo: item.image || item.photo_url || item.photo || "",
  image: item.image || item.photo_url || item.photo || "",
  reception: item.officeHours || item.office_hours || "",
  officeHours: item.officeHours || item.office_hours || "",
  office_hours: item.office_hours || item.officeHours || "",
  phone: item.phone || "",
  email: item.email || "",
  telegram: item.telegram || item.telegram_url || "",
  telegram_url: item.telegram_url || item.telegram || "",
  about: item.about || "",
  details: item.details || "",
  achievements: Array.isArray(item.achievements) ? item.achievements : [],
  sort_order: Number(item.sort_order || 0),
  is_rector: Boolean(item.is_rector),
  is_published: Boolean(item.is_published ?? true),
  translations: item.translations || [],
});

const normalizeList = (response) => {
  const payload = unwrap(response) || {};
  const items = Array.isArray(payload.data) ? payload.data : Array.isArray(payload) ? payload : [];
  return items.map(normalizeProfile);
};

export const administrationService = {
  getSettings(locale) {
    return cached(
      "administration/settings",
      () => api.get(withLocale("/administration/settings", locale)).then((res) => unwrap(res)),
      locale,
    );
  },

  getProfiles(options = {}, locale) {
    const query = new URLSearchParams();
    Object.entries(options).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== "") {
        query.set(key, value);
      }
    });
    query.set("locale", getLocale(locale));

    const suffix = query.toString() ? `?${query}` : "";
    return cached(
      `administration${suffix}`,
      () => api.get(`/administration${suffix}`).then(normalizeList),
      locale,
    );
  },

  getProfile(slug, locale) {
    return api.get(withLocale(`/administration/${slug}`, locale)).then((res) => normalizeProfile(unwrap(res)));
  },
};
