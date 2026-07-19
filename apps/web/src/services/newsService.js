import { api } from "../lib/api";

const normalizeNewsItem = (item = {}) => ({
  ...item,
  id: item.slug || item.id,
  img: item.image_url || item.image || "",
  description: item.summary || item.description || "",
  date: item.published_at || item.date || "",
  views: item.views_count ?? item.views ?? 0,
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
    return api.get("/news-events/settings").then((res) => res.data || {});
  },

  getNews(params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `/news${query ? "?" + query : ""}`;
    return api.get(url).then(normalizeNewsList);
  },

  getNewsItem(slug, params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `/news/${slug}${query ? "?" + query : ""}`;
    return api.get(url).then((res) => normalizeNewsItem(res?.data));
  },
};
