import { api } from "../lib/api";

export const greenCampusService = {
  getStats() {
    return api.get("/green-campus/stats").then(res => res.data || []);
  },

  getArticles() {
    return api.get("/green-campus/articles").then(res => res.data || []);
  },

  getArticle(slug) {
    return api.get(`/green-campus/articles/${slug}`).then(res => res.data);
  }
};
