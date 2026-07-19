import { api } from "../lib/api";

export const announcementService = {
  getAnnouncements(options = {}) {
    const params = new URLSearchParams();

    if (options.activeOnly) {
      params.set("active_only", "1");
    }

    const query = params.toString();
    return api
      .get(`/announcements${query ? `?${query}` : ""}`)
      .then((res) => res.data || []);
  },

  getAnnouncement(slug) {
    return api.get(`/announcements/${slug}`).then(res => res.data);
  }
};
