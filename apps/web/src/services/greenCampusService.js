import { api, publicAssetUrl } from "../lib/api";
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

const normalizeDate = (value) => {
  if (!value) return "";
  if (typeof value === "string") return value;
  if (value.date) return value.date;
  return "";
};

const normalizeArticle = (item) => {
  const gallery = Array.isArray(item.gallery) ? item.gallery : [];
  return {
    ...item,
    id: item.slug || item.id,
    image: publicAssetUrl(item.image),
    gallery: gallery.map(publicAssetUrl).filter(Boolean),
    date: normalizeDate(item.published_at || item.created_at),
    category: item.category || "",
    categoryLabel: item.category_label || item.category || "",
    paragraphs: item.content
      ? String(item.content)
          .split(/\n{2,}/)
          .map((paragraph) => paragraph.trim())
          .filter(Boolean)
      : [],
  };
};

export const greenCampusService = {
  getSettings() {
    return cached("green-campus/settings", () =>
      api.get("/green-campus/settings").then(res => res.data || {}),
    );
  },

  getStats() {
    return cached("green-campus/stats", () =>
      api.get("/green-campus/stats").then(res => res.data || []),
    );
  },

  getArticles() {
    return cached("green-campus/articles", () =>
      api.get("/green-campus/articles").then(res => (res.data || []).map(normalizeArticle)),
    );
  },

  getArticle(slug) {
    return api.get(`/green-campus/articles/${slug}`).then(res => normalizeArticle(res.data));
  }
};
