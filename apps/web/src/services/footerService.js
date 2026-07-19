import { api } from "../lib/api";

export const footerService = {
  getPublicFooter() {
    return api.get("/footer-web").then((res) => res.data);
  },

  getCmsFooter() {
    return api.get("/apanel/cms/footer-web").then((res) => res.data);
  },

  updateCmsFooter(payload) {
    return api.put("/apanel/cms/footer-web", payload).then((res) => res.data);
  },

  subscribe(email) {
    return api
      .post("/newsletter-subscriptions", { email })
      .then((res) => res.data);
  },
};
