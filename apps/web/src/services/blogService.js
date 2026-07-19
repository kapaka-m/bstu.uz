import { api } from "../lib/api";
import { localeStorage } from "../lib/locale";

const formatDisplayDate = (value) => {
  if (!value) return "";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  const locale = localeStorage.getLocale() || "en";
  if (locale === "uz") {
    const uzMonths = [
      "Yanvar",
      "Fevral",
      "Mart",
      "Aprel",
      "May",
      "Iyun",
      "Iyul",
      "Avgust",
      "Sentabr",
      "Oktabr",
      "Noyabr",
      "Dekabr",
    ];
    return `${date.getFullYear()} ${uzMonths[date.getMonth()]} ${String(date.getDate()).padStart(2, "0")}`;
  }

  const formatLocale = {
    en: "en-US",
    ru: "ru-RU",
    ar: "ar",
  }[locale] || locale;

  return new Intl.DateTimeFormat(formatLocale, {
    month: "short",
    day: "numeric",
    year: "numeric",
  }).format(date);
};

const normalizeBlogItem = (item = {}) => ({
  ...item,
  id: item.slug || item.id,
  slug: item.slug || item.id,
  image: item.image_url || item.image || "",
  categoryLabel: item.category_label || item.category || "",
  authorImage: item.author_image_url || item.author_image || null,
  excerpt: item.summary || item.excerpt || "",
  date: formatDisplayDate(item.published_at || item.date),
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
    return api.get("/blog/settings").then((res) => res.data || {});
  },

  getBlog(params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = `/blog${query ? `?${query}` : ""}`;
    return api.get(url).then(normalizeBlogList);
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
};
