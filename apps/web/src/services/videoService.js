import { api } from "../lib/api";

const normalizeVideo = (item = {}) => ({
  ...item,
  id: item.slug || item.id,
  slug: item.slug || item.id,
  isLocal: item.isLocal || item.video_type === "local",
  videoUrl: item.video_url || item.url || "",
  youtubeId: item.youtubeId || item.youtube_id || "",
  poster: item.poster || item.thumbnail || "",
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
    return api.get("/videos/settings").then((res) => res.data || {});
  },

  getVideos() {
    return api.get("/videos").then(normalizeList);
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
