export function selectTranslation(item, locale) {
  const translations = Array.isArray(item?.translations) ? item.translations : [];
  return translations.find((entry) => entry.locale === locale)
    || translations.find((entry) => entry.locale === "en")
    || translations[0]
    || {};
}

export function getLocalizedValue(item, path, locale) {
  const parts = path.split(".");
  return parts.reduce((value, part, index) => {
    if (value == null) return undefined;
    if (parts[index - 1] === "translations" && part === "0" && Array.isArray(value)) {
      return selectTranslation({ translations: value }, locale);
    }
    return value[part];
  }, item);
}
