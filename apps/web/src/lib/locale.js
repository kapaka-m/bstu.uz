import { storage } from "./storage";

const LOCALE_KEY = "bstu_lang";
const DEFAULT_LOCALE = import.meta.env.VITE_DEFAULT_LOCALE || "en";

export const localeStorage = {
  getLocale() {
    return storage.get(LOCALE_KEY, DEFAULT_LOCALE);
  },

  setLocale(locale) {
    storage.set(LOCALE_KEY, locale);
  }
};
