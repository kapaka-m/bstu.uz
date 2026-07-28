import { useMemo } from "react";
import { useLanguage } from "../../../context/LanguageContext";

export function useApanelLocaleOptions() {
  const { locales = [] } = useLanguage();

  return useMemo(
    () =>
      locales
        .filter((locale) => locale?.code && locale.is_active !== false)
        .map((locale) => ({
          code: locale.code,
          label: locale.native_name || locale.name || locale.code.toUpperCase(),
        })),
    [locales],
  );
}

export function useApanelLocaleCodes() {
  const localeOptions = useApanelLocaleOptions();

  return useMemo(
    () => localeOptions.map((locale) => locale.code),
    [localeOptions],
  );
}

export function buildLocaleMap(localeCodes, createValue) {
  return Object.fromEntries(
    localeCodes.map((locale) => [locale, createValue(locale)]),
  );
}
