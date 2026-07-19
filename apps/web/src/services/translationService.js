import { api } from "../lib/api";

export const translationService = {
  getLocales() {
    return api.get("/locales").then(res => res.data || []);
  },
  
  getTranslations(locale) {
    return api.get(`/translations?locale=${locale}`).then(res => res.data || {});
  },

  getPublicSettings() {
    return api.get("/settings/public").then(res => res.data || {});
  }
};
