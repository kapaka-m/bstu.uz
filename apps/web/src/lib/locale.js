import { storage } from "./storage";

const LOCALE_KEY = "bstu_lang";

export const localeStorage = {
  getLocale(defaultValue = null) {
    return storage.get(LOCALE_KEY, defaultValue);
  },

  setLocale(locale) {
    storage.set(LOCALE_KEY, locale);
  },

  clearLocale() {
    storage.remove(LOCALE_KEY);
  }
};
