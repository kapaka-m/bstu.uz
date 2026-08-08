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

const normalizeBlogItem = (item = {}) => ({
  ...item,
  id: item.slug || item.id,
  slug: item.slug || item.id,
  image: item.image_url || item.image || "",
  categoryLabel: item.category_label || item.category || "",
  authorImage: item.author_image_url || item.author_image || null,
  department: item.department
    ? {
        ...item.department,
        image: item.department.image_url || item.department.image || "",
      }
    : null,
  excerpt: item.summary || item.excerpt || "",
  date: item.published_at || item.date || "",
  comments: item.comments_count ?? item.comments ?? 0,
  views: item.views_count ?? item.views ?? 0,
  paragraphs:
    typeof item.content === "string"
      ? item.content.split(/\n{2,}/).filter(Boolean)
      : item.paragraphs || [],
});

const normalizeBlogList = (response) => {
  const items = Array.isArray(response?.data) ? response.data : [];
  return {
    items: items.map(normalizeBlogItem),
    meta: response?.meta || {
      current_page: 1,
      last_page: 1,
      per_page: items.length,
      total: items.length,
    },
  };
};

export const blogService = {
  getSettings() {
    return cached("blog/settings", () =>
      api.get("/blog/settings").then((res) => res.data || {}),
    );
  },

  getBlog(params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `/blog${query ? `?${query}` : ""}`;
    return cached(url, () => api.get(url).then(normalizeBlogList));
  },

  getBlogItem(slug) {
    return api.get(`/blog/${slug}`).then((res) => normalizeBlogItem(res?.data));
  },

  getComments(slug) {
    return api.get(`/blog/${slug}/comments`).then((res) => res.data || []);
  },

  postComment(slug, payload) {
    return api.post(`/blog/${slug}/comments`, payload).then((res) => res.data);
  },

  getDepartments() {
    return cached("blog/departments", () =>
      api.get("/blog/departments").then((res) => res.data || []),
    );
  },

  getDepartment(slug) {
    return api.get(`/blog/departments/${slug}`).then((res) => {
      const department = res.data || {};
      return {
        ...department,
        image: department.image_url || department.image || "",
        blogs: (department.blogs || []).map(normalizeBlogItem),
      };
    });
  },
};
