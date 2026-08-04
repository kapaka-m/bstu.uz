let activeLocale = "en";
let activeTranslations = {};

const resolvePath = (source, keyPath) => {
  if (!source || !keyPath) return undefined;

  return keyPath.split(".").reduce((result, key) => {
    if (result && Object.prototype.hasOwnProperty.call(result, key)) {
      return result[key];
    }

    return undefined;
  }, source);
};

export const setApiMessageDictionary = (locale, translations) => {
  activeLocale = locale || "en";
  activeTranslations = translations || {};
};

export const translateApiMessageKey = (key, fallback = "") => {
  const value = resolvePath(activeTranslations, key);

  if (typeof value === "string" && value.trim() !== "") {
    return value;
  }

  return fallback;
};

export const getApiMessageLocale = () => activeLocale;
