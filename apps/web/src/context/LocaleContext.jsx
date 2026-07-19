/* eslint-disable react-refresh/only-export-components */
import React, { createContext, useContext, useState, useEffect } from "react";
import { localeStorage } from "../lib/locale";
import { translationService } from "../services/translationService";
import { menuService } from "../services/menuService";

const LocaleContext = createContext();
const fallbackTranslationLoaders = {
  ar: () => import("../data/fallbackTranslations/ar"),
  en: () => import("../data/fallbackTranslations/en"),
  ru: () => import("../data/fallbackTranslations/ru"),
  uz: () => import("../data/fallbackTranslations/uz"),
};
const fallbackTranslationCache = {};

async function loadFallbackTranslation(locale) {
  if (!fallbackTranslationCache[locale]) {
    const loader = fallbackTranslationLoaders[locale] || fallbackTranslationLoaders.en;
    fallbackTranslationCache[locale] = loader().then(
      (module) => module.translation || {},
    );
  }
  return fallbackTranslationCache[locale];
}

export function LocaleProvider({ children }) {
  const [locale, setLocale] = useState(() => localeStorage.getLocale());
  const [locales, setLocales] = useState([]);
  const [translations, setTranslations] = useState({});
  const [settings, setSettings] = useState({});
  const [headerMenu, setHeaderMenu] = useState([]);
  const [loading, setLoading] = useState(true);

  // Helper to resolve nested dot-notation paths (e.g. "nav.home")
  const resolvePath = (source, keyPath) => {
    if (!source || !keyPath) return undefined;
    const keys = keyPath.split(".");
    let result = source;
    for (const key of keys) {
      if (result && result[key] !== undefined) {
        result = result[key];
      } else {
        return undefined;
      }
    }
    return result;
  };

  const t = (key, fallback = null) => {
    const val = resolvePath(translations, key);
    if (val !== undefined && val !== null) {
      return val;
    }
    if (fallback !== null) {
      return fallback;
    }
    return key; // return key string so missing translation keys are visible during dev
  };

  const changeLocale = async (newLocale) => {
    setLocale(newLocale);
    localeStorage.setLocale(newLocale);
  };

  useEffect(() => {
    const loadAppData = async () => {
      try {
        setLoading(true);
        const [localesData, settingsData] = await Promise.all([
          translationService.getLocales(),
          translationService.getPublicSettings(),
        ]);
        setLocales(localesData);
        setSettings(settingsData);
      } catch (e) {
        console.error("Failed to load initial locales and settings", e);
      } finally {
        setLoading(false);
      }
    };
    loadAppData();
  }, []);

  useEffect(() => {
    const loadLocaleData = async () => {
      let fallbackTranslation = {};

      try {
        const dir = locale === "ar" ? "rtl" : "ltr";
        document.documentElement.dir = dir;
        document.documentElement.lang = locale;
        document.body.dir = dir;

        fallbackTranslation = await loadFallbackTranslation(locale);
        setTranslations(fallbackTranslation);

        const [transData, headerData] = await Promise.all([
          translationService.getTranslations(locale),
          menuService.getMenu("header"),
        ]);

        setTranslations(transData || fallbackTranslation);
        setHeaderMenu(headerData);
      } catch (e) {
        console.error(`Failed to load data for locale: ${locale}`, e);
        setTranslations(fallbackTranslation);
      }
    };
    loadLocaleData();
  }, [locale]);

  const logoSrc =
    locale === "ar"
      ? "/assets/img/bstu-ar.png"
      : locale === "en"
        ? "/assets/img/bstu-en.png"
        : locale === "ru"
          ? "/assets/img/bstu-ru.png"
          : "/assets/img/bstu.png";

  const contextValue = {
    locale,
    language: locale, // alias for backward-compatibility
    locales,
    translations,
    settings,
    headerMenu,
    footerMenu: [],
    logoSrc,
    changeLocale,
    changeLanguage: changeLocale, // alias for backward-compatibility
    setLanguage: changeLocale, // alias for older layouts
    t,
    loading,
  };

  return (
    <LocaleContext.Provider value={contextValue}>
      {children}
    </LocaleContext.Provider>
  );
}

export function useLocale() {
  const context = useContext(LocaleContext);
  if (!context) {
    throw new Error("useLocale must be used within a LocaleProvider");
  }
  return context;
}
