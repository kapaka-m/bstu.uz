import { api } from "../lib/api";

const unwrap = (response) => response?.data ?? response;

const normalizeList = (response) => {
  const payload = response || {};
  const items = Array.isArray(payload.data)
    ? payload.data
    : Array.isArray(payload)
      ? payload
      : [];

  return {
    items: items.map(normalizeAnnouncement),
    meta: payload?.meta || {
      total: items.length,
      current_page: 1,
      last_page: 1,
      per_page: items.length,
    },
  };
};

const normalizeAnnouncement = (item = {}) => ({
  id: item.slug || item.id,
  numeric_id: item.id,
  slug: item.slug || item.id,
  title: item.title || "",
  excerpt: item.summary || item.excerpt || "",
  summary: item.summary || item.excerpt || "",
  content: item.content || "",
  category: item.category || item.type || "announcements",
  category_label: item.category_label || item.category || item.type || "Announcements",
  views: Number(item.views_count ?? item.views ?? 0),
  views_count: Number(item.views_count ?? item.views ?? 0),
  image: item.image_url || item.image || "",
  image_url: item.image_url || item.image || "",
  date: item.date || item.starts_at || item.created_at || "",
  starts_at: item.starts_at || item.date || item.created_at || "",
  ends_at: item.ends_at || "",
  important: Boolean(item.important || item.priority === "high"),
  priority: item.priority || "normal",
  is_published: Boolean(item.is_published ?? true),
});

export const announcementService = {
  getSettings() {
    return api.get("/announcements/settings").then((res) => unwrap(res));
  },

  getAnnouncements(options = {}) {
    const params = new URLSearchParams();

    Object.entries(options).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== "") {
        params.set(key === "activeOnly" ? "active_only" : key, value);
      }
    });

    const query = params.toString();
    return api
      .get(`/announcements${query ? `?${query}` : ""}`)
      .then(normalizeList);
  },

  getAnnouncement(slug) {
    return api
      .get(`/announcements/${slug}`)
      .then((res) => normalizeAnnouncement(unwrap(res)?.data || unwrap(res)));
  },
};
