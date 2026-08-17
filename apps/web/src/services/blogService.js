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

const unwrap = (response) => response?.data ?? response;

const normalizeBlogItem = (item = {}) => ({
  ...item,
  id: item.slug || item.id,
  slug: item.slug || item.id,
  image: publicAssetUrl(item.image_url || item.image || ""),
  categoryLabel: item.category_label || item.category || "",
  authorImage: publicAssetUrl(item.author_image_url || item.author_image || ""),
  department: item.department
    ? {
        ...item.department,
        image: publicAssetUrl(item.department.image_url || item.department.image || ""),
      }
    : null,
  excerpt: item.summary || item.excerpt || "",
  date: item.published_at || item.date || "",
  comments: item.comments_count ?? item.comments ?? 0,
  views: item.views_count ?? item.views ?? 0,
  paragraphs:
    typeof item.content === "string"
      ? item.content
          .split(/\n{2,}/)
          .map((paragraph) => paragraph.trim())
          .filter(Boolean)
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

const normalizePublisherContentItem = (item = {}) => {
  const image = publicAssetUrl(item.image_url || item.image || item.thumbnail_url || item.thumbnail || "");

  return {
    ...item,
    id: item.slug || item.id,
    slug: item.slug || item.id,
    image,
    thumbnail: image,
    poster: image,
    excerpt: item.summary || item.excerpt || item.description || "",
    summary: item.summary || item.excerpt || item.description || "",
    date: item.published_at || item.starts_at || item.date || item.created_at || "",
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
      api.get("/blog/departments").then((res) => unwrap(res) || []),
    );
  },

  getPublishers() {
    return cached("publishers", () =>
      api.get("/publishers").then((res) =>
        (unwrap(res) || []).map((publisher) => ({
          ...publisher,
          image: publicAssetUrl(publisher.image_url || publisher.image || ""),
        })),
      ),
    );
  },

  getPublisher(slug) {
    return api.get(`/publishers/${slug}`).then((res) => {
      const publisher = unwrap(res) || {};
      return {
        ...publisher,
        image: publicAssetUrl(publisher.image_url || publisher.image || ""),
        blogs: (publisher.blogs || []).map(normalizeBlogItem),
        news: (publisher.news || []).map(normalizePublisherContentItem),
        announcements: (publisher.announcements || []).map(normalizePublisherContentItem),
        green_campus_articles: (publisher.green_campus_articles || []).map(normalizePublisherContentItem),
        videos: (publisher.videos || []).map(normalizePublisherContentItem),
      };
    });
  },

  getDepartment(slug) {
    return api.get(`/blog/departments/${slug}`).then((res) => {
      const department = unwrap(res) || {};
      return {
        ...department,
        image: publicAssetUrl(department.image_url || department.image || ""),
        blogs: (department.blogs || []).map(normalizeBlogItem),
        news: (department.news || []).map(normalizePublisherContentItem),
        announcements: (department.announcements || []).map(normalizePublisherContentItem),
        green_campus_articles: (department.green_campus_articles || []).map(normalizePublisherContentItem),
        videos: (department.videos || []).map(normalizePublisherContentItem),
      };
    });
  },
};
