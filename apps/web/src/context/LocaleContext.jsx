/* eslint-disable react-refresh/only-export-components */
import React, { createContext, useContext, useState, useEffect, useRef } from "react";
import { localeStorage } from "../lib/locale";
import { publicAssetUrl } from "../lib/api";
import { setApiMessageDictionary } from "../lib/apiMessageDictionary";
import { translationService } from "../services/translationService";
import { menuService } from "../services/menuService";

const LocaleContext = createContext();

const normalizeMenuItems = (menu) => {
  if (Array.isArray(menu)) return menu;
  if (Array.isArray(menu?.data)) return menu.data;
  if (Array.isArray(menu?.items)) return menu.items;
  if (Array.isArray(menu?.data?.items)) return menu.data.items;
  return [];
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
  const [translationsLoading, setTranslationsLoading] = useState(true);
  const missingTranslationKeys = useRef(new Set());

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

  const hasTranslation = (key) => {
    const val = resolvePath(translations, key);
    return val !== undefined && val !== null && val !== "";
  };

  const t = (key) => {
    const val = resolvePath(translations, key);
    if (val !== undefined && val !== null) {
      if (typeof val === "object") {
        if (key && !missingTranslationKeys.current.has(key)) {
          missingTranslationKeys.current.add(key);
          console.warn(`CMS translation key is not renderable text: ${key}`);
        }

        return "";
      }

      return val;
    }

    if (key && !missingTranslationKeys.current.has(key)) {
      missingTranslationKeys.current.add(key);
      console.warn(`Missing CMS translation: ${key}`);
    }

    return "";
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
        const activeLocales = Array.isArray(localesData)
          ? localesData.filter((item) => item?.is_active !== false)
          : [];
        const storedLocale = localeStorage.getLocale();
        const nextLocale =
          activeLocales.find((item) => item.code === storedLocale)?.code ||
          activeLocales[0]?.code ||
          storedLocale;

        setLocales(localesData);
        setSettings(settingsData);
        if (nextLocale && nextLocale !== storedLocale) {
          localeStorage.setLocale(nextLocale);
        }
        if (nextLocale) {
          setLocale((currentLocale) =>
            currentLocale === nextLocale ? currentLocale : nextLocale,
          );
        }
      } catch (e) {
        console.error("Failed to load initial locales and settings", e);
      } finally {
        setLoading(false);
      }
    };
    loadAppData();
  }, []);

  useEffect(() => {
    if (!locale) {
      return;
    }

    const loadLocaleData = async () => {
      try {
        setTranslationsLoading(true);
        const currentLocale = locales.find((item) => item.code === locale);
        const dir = currentLocale?.direction || "ltr";
        document.documentElement.dir = dir;
        document.documentElement.lang = locale;
        document.body.dir = dir;

        const [translationsResult, headerResult] = await Promise.allSettled([
          translationService.getTranslations(locale),
          menuService.getMenu("header"),
        ]);

        if (translationsResult.status === "fulfilled") {
          const nextTranslations = translationsResult.value || {};
          setTranslations(nextTranslations);
          setApiMessageDictionary(locale, nextTranslations);
        } else {
          setTranslations({});
          setApiMessageDictionary(locale, {});
        }

        if (headerResult.status === "fulfilled") {
          setHeaderMenu(normalizeMenuItems(headerResult.value));
        } else {
          setHeaderMenu([]);
        }
      } catch (e) {
        console.error(`Failed to load data for locale: ${locale}`, e);
        setTranslations({});
        setApiMessageDictionary(locale, {});
      } finally {
        setTranslationsLoading(false);
      }
    };
    loadLocaleData();
  }, [locale, locales]);

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
      publicAssetUrl(
        settings.branding_favicon_png || settings.branding_favicon_ico,
      ),
      { rel: "icon" },
    );
    setLinkHref(
      'link[rel="apple-touch-icon"]',
      publicAssetUrl(settings.branding_apple_touch_icon),
      { rel: "apple-touch-icon" },
    );
    setLinkHref(
      'link[rel="icon"][sizes="32x32"]',
      publicAssetUrl(settings.branding_favicon_32),
      { rel: "icon", sizes: "32x32", type: "image/png" },
    );
    setLinkHref(
      'link[rel="icon"][sizes="16x16"]',
      publicAssetUrl(settings.branding_favicon_16),
      { rel: "icon", sizes: "16x16", type: "image/png" },
    );
  }, [settings]);

  const currentLocale = locales.find((item) => item.code === locale) || null;
  const direction = currentLocale?.direction || "ltr";
  const isRtl = direction === "rtl";
  const logoSrc = publicAssetUrl(
    settings[`branding_logo_${locale}`] || settings.branding_logo_default,
  );

  const contextValue = {
    locale,
    language: locale, // alias for backward-compatibility
    locales,
    translations,
    settings,
    headerMenu,
    footerMenu: [],
    logoSrc,
    direction,
    isRtl,
    changeLocale,
    changeLanguage: changeLocale, // alias for backward-compatibility
    setLanguage: changeLocale, // alias for older layouts
    hasTranslation,
    t,
    loading: loading || translationsLoading,
    translationsLoading,
    translationsReady: !translationsLoading && Object.keys(translations).length > 0,
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
