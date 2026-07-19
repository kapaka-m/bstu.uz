import { api } from "../lib/api";

export const pageService = {
  getPages() {
    return api.get("/pages").then(res => res.data || []);
  },

  getPage(slug) {
    return api.get(`/pages/${slug}`).then(res => res.data);
  },

  getPageBlocks(slug) {
    return api.get(`/page-blocks/${slug}`).then(res => res.data || []);
  }
};
