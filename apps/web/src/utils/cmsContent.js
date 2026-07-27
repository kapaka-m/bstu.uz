export function asArray(value) {
  if (Array.isArray(value)) return value;
  if (!value) return [];
  if (value.success === true && value.data !== undefined) return asArray(value.data);
  if (Array.isArray(value.data)) return value.data;
  if (value.data && typeof value.data === "object") return asArray(value.data);
  if (Array.isArray(value.items)) return value.items;
  if (value.items && typeof value.items === "object") return asArray(value.items);
  if (Array.isArray(value.results)) return value.results;
  if (value.results && typeof value.results === "object") return asArray(value.results);
  return [];
}

export function normalizeListResponse(response) {
  return asArray(response);
}

export function normalizeItemResponse(response) {
  if (!response) return null;
  if (response.success === true && response.data !== undefined) {
    return normalizeItemResponse(response.data);
  }
  if (response.data && !Array.isArray(response.data)) {
    return normalizeItemResponse(response.data);
  }
  if (Array.isArray(response.data)) {
    return response.data[0] || null;
  }
  return response;
}

export function textValue(value, fallback = "") {
  if (value === null || value === undefined) return fallback;
  if (typeof value === "string") return value;
  if (typeof value === "number") return String(value);
  if (typeof value === "object") {
    return (
      value.title ||
      value.name ||
      value.value ||
      value.text ||
      Object.values(value).find((entry) => typeof entry === "string") ||
      fallback
    );
  }
  return fallback;
}

export function cmsId(item) {
  return String(item?.slug || item?.id || item?.code || "");
}

export function cmsTitle(item, fallback = "") {
  return textValue(item?.title || item?.name || item?.heading, fallback);
}

export function cmsExcerpt(item, fallback = "") {
  return textValue(
    item?.excerpt ||
      item?.summary ||
      item?.description ||
      item?.short_description ||
      item?.content ||
      item?.body,
    fallback,
  );
}

export function cmsCategory(item, fallback = "") {
  return textValue(item?.category || item?.type || item?.status, fallback);
}

export function cmsImage(item, fallback = "") {
  const value =
    item?.image_url ||
    item?.thumbnail_url ||
    item?.cover_url ||
    item?.image ||
    item?.thumbnail ||
    item?.img ||
    item?.photo ||
    item?.avatar;

  if (!value) return fallback;
  if (/^https?:\/\//i.test(value)) return value;
  if (value.startsWith("/")) return value;
  if (value.startsWith("storage/")) return `/${value}`;
  return `/storage/${value}`;
}

export function cmsDate(item) {
  return (
    item?.published_at ||
    item?.date ||
    item?.created_at ||
    item?.updated_at ||
    ""
  );
}

export function formatCmsDate(value, locale) {
  if (!value) return "";
  if (/^\d{4}$/.test(String(value).trim())) return String(value);
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return String(value);

  return new Intl.DateTimeFormat(locale, {
    day: "2-digit",
    month: "long",
    year: "numeric",
  }).format(parsed);
}

export function cmsParagraphs(item) {
  const source =
    item?.paragraphs ||
    item?.content_blocks ||
    item?.body ||
    item?.content ||
    item?.description;

  if (Array.isArray(source)) return source.map((entry) => textValue(entry)).filter(Boolean);
  if (source && typeof source === "object") {
    return Object.values(source).map((entry) => textValue(entry)).filter(Boolean);
  }
  if (typeof source === "string") {
    return source
      .split(/\n{2,}/)
      .map((entry) => entry.trim())
      .filter(Boolean);
  }
  return [];
}

export function uniqueCategories(items) {
  return [
    "all",
    ...new Set(
      items
        .map((item) => cmsCategory(item).toLowerCase())
        .filter(Boolean),
    ),
  ];
}

export function filterCmsItems(items, searchQuery, selectedCategory) {
  const query = searchQuery.trim().toLowerCase();
  return items.filter((item) => {
    const searchable = `${cmsTitle(item)} ${cmsExcerpt(item)} ${cmsCategory(item)}`.toLowerCase();
    const category = cmsCategory(item).toLowerCase();
    const matchesSearch = !query || searchable.includes(query);
    const matchesCategory =
      selectedCategory === "all" || category === selectedCategory.toLowerCase();
    return matchesSearch && matchesCategory;
  });
}
