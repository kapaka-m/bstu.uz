import { api } from "../lib/api";

const mediaUrl = (path) => {
  if (!path) return "";
  if (path.startsWith("http://") || path.startsWith("https://") || path.startsWith("/")) {
    return path;
  }

  const apiBase = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1";
  return `${apiBase.replace(/\/api\/v1\/?$/, "")}/storage/${path.replace(/^public\//, "")}`;
};

const normalizeArticle = (item) => {
  const gallery = Array.isArray(item.gallery) ? item.gallery : [];
  return {
    ...item,
    id: item.slug || item.id,
    image: mediaUrl(item.image),
    gallery: gallery.map(mediaUrl).filter(Boolean),
    date: item.published_at || item.created_at,
    category: item.category || "sustainability",
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
    return api.get("/green-campus/settings").then(res => res.data || {});
  },

  getStats() {
    return api.get("/green-campus/stats").then(res => res.data || []);
  },

  getArticles() {
    return api.get("/green-campus/articles").then(res => (res.data || []).map(normalizeArticle));
  },

  getArticle(slug) {
    return api.get(`/green-campus/articles/${slug}`).then(res => normalizeArticle(res.data));
  }
};
