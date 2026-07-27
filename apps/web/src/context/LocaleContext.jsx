/* eslint-disable react-refresh/only-export-components */
import React, { createContext, useContext, useState, useEffect } from "react";
import { localeStorage } from "../lib/locale";
import { translationService } from "../services/translationService";
import { menuService } from "../services/menuService";

const LocaleContext = createContext();
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1";
const PUBLIC_BASE_URL = API_BASE_URL.replace(/\/api\/v1\/?$/, "");

const normalizeMenuItems = (menu) => {
  if (Array.isArray(menu)) return menu;
  if (Array.isArray(menu?.data)) return menu.data;
  if (Array.isArray(menu?.items)) return menu.items;
  if (Array.isArray(menu?.data?.items)) return menu.data.items;
  return [];
};

const resolvePublicAssetUrl = (value, fallback = "") => {
  const path = String(value || "").trim();
  if (!path) return fallback;
  if (/^https?:\/\//i.test(path)) return path;
  if (path.startsWith("/storage/")) return `${PUBLIC_BASE_URL}${path}`;
  if (path.startsWith("storage/")) return `${PUBLIC_BASE_URL}/${path}`;
  if (path.startsWith("/")) return path;
  return `${PUBLIC_BASE_URL}/storage/${path}`;
};

const setLinkHref = (selector, href, attributes = {}) => {
  if (!href) return;
  let link = document.head.querySelector(selector);
  if (!link) {
    link = document.createElement("link");
    Object.entries(attributes).forEach(([key, value]) => {
      link.setAttribute(key, value);
    });
    document.head.appendChild(link);
  }
  link.setAttribute("href", href);
};

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
      try {
        const dir = locale === "ar" ? "rtl" : "ltr";
        document.documentElement.dir = dir;
        document.documentElement.lang = locale;
        document.body.dir = dir;

        const [translationsResult, headerResult] = await Promise.allSettled([
          translationService.getTranslations(locale),
          menuService.getMenu("header"),
        ]);

        if (translationsResult.status === "fulfilled") {
          setTranslations(translationsResult.value || {});
        } else {
          setTranslations({});
        }

        if (headerResult.status === "fulfilled") {
          setHeaderMenu(normalizeMenuItems(headerResult.value));
        } else {
          setHeaderMenu([]);
        }
      } catch (e) {
        console.error(`Failed to load data for locale: ${locale}`, e);
        setTranslations({});
      }
    };
    loadLocaleData();
  }, [locale]);

  useEffect(() => {
    if (settings.site_name) {
      document.title = settings.site_name;
    }

    const metaDescription = document.head.querySelector('meta[name="description"]');
    if (metaDescription && settings.site_meta_description) {
      metaDescription.setAttribute("content", settings.site_meta_description);
    }

    const metaKeywords = document.head.querySelector('meta[name="keywords"]');
    if (metaKeywords && settings.site_meta_keywords) {
      metaKeywords.setAttribute("content", settings.site_meta_keywords);
    }

    setLinkHref(
      'link[rel="icon"]',
      resolvePublicAssetUrl(
        settings.branding_favicon_png || settings.branding_favicon_ico,
      ),
      { rel: "icon" },
    );
    setLinkHref(
      'link[rel="apple-touch-icon"]',
      resolvePublicAssetUrl(settings.branding_apple_touch_icon),
      { rel: "apple-touch-icon" },
    );
    setLinkHref(
      'link[rel="icon"][sizes="32x32"]',
      resolvePublicAssetUrl(settings.branding_favicon_32),
      { rel: "icon", sizes: "32x32", type: "image/png" },
    );
    setLinkHref(
      'link[rel="icon"][sizes="16x16"]',
      resolvePublicAssetUrl(settings.branding_favicon_16),
      { rel: "icon", sizes: "16x16", type: "image/png" },
    );
  }, [settings]);

  const logoSrc =
    locale === "ar"
      ? resolvePublicAssetUrl(settings.branding_logo_ar)
      : locale === "en"
        ? resolvePublicAssetUrl(settings.branding_logo_en)
        : locale === "ru"
          ? resolvePublicAssetUrl(settings.branding_logo_ru)
          : resolvePublicAssetUrl(settings.branding_logo_default);

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
