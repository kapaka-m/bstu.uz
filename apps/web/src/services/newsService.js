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

const normalizeNewsItem = (item = {}) => ({
  ...item,
  id: item.slug || item.id,
  img: item.image_url || item.image || "",
  description: item.summary || item.description || "",
  date: item.published_at || item.date || "",
  views: item.views_count ?? item.views ?? 0,
  publisher: item.publisher
    ? {
        ...item.publisher,
        image: item.publisher.image_url || item.publisher.image || "",
      }
    : null,
  paragraphs:
    typeof item.content === "string"
      ? item.content.split(/\n{2,}/).filter(Boolean)
      : item.paragraphs || [],
});

const normalizeNewsList = (response) => {
  const items = Array.isArray(response?.data) ? response.data : [];
  return {
    items: items.map(normalizeNewsItem),
    meta: response?.meta || {
      current_page: 1,
      last_page: 1,
      per_page: items.length,
      total: items.length,
    },
  };
};

export const newsService = {
  getSettings() {
    return cached("news-events/settings", () =>
      api.get("/news-events/settings").then((res) => res.data || {}),
    );
  },

  getNews(params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `/news${query ? "?" + query : ""}`;
    return cached(url, () => api.get(url).then(normalizeNewsList));
  },

  getNewsItem(slug, params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `/news/${slug}${query ? "?" + query : ""}`;
    return api.get(url).then((res) => normalizeNewsItem(res?.data));
  },
};
