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

  const pending = fetcher().then((value) => {
    responseCache.set(cacheKey, { time: Date.now(), value });
    return value;
  }).catch((error) => {
    responseCache.delete(cacheKey);
    throw error;
  });

  responseCache.set(cacheKey, { time: Date.now(), value: pending });
  return pending;
};

const normalizeVideo = (item = {}) => ({
  ...item,
  id: item.slug || item.id,
  slug: item.slug || item.id,
  isLocal: item.isLocal || item.video_type === "local",
  videoUrl: publicAssetUrl(item.video_url || item.url || ""),
  youtubeId: item.youtubeId || item.youtube_id || "",
  poster: publicAssetUrl(item.poster || item.thumbnail || ""),
  title: item.title || "",
  category: item.category || "",
  duration: item.duration || "",
  viewsCount: Number(item.views_count || 0),
  likesCount: Number(item.likes_count || 0),
  publishedAt: item.published_at || "",
  description: item.description || "",
});

const normalizeList = (response) => {
  const items = Array.isArray(response?.data) ? response.data : [];
  return items.map(normalizeVideo);
};

export const videoService = {
  getSettings() {
    return cached("videos/settings", () =>
      api.get("/videos/settings").then((res) => res.data || {}),
    );
  },

  getVideos() {
    return cached("videos", () => api.get("/videos").then(normalizeList));
  },

  recordView(slug) {
    return api.post(`/videos/${slug}/view`).then((res) => res.data || {});
  },

  recordLike(slug) {
    return api.post(`/videos/${slug}/like`).then((res) => res.data || {});
  },

  getComments(slug) {
    return api.get(`/videos/${slug}/comments`).then((res) => res.data || []);
  },

  postComment(slug, payload) {
    return api.post(`/videos/${slug}/comments`, payload).then((res) => res.data);
  },
};
