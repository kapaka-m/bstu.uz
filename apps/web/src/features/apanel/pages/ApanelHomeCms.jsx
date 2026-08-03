import { useEffect, useMemo, useState } from "react";
import { Loader2, Plus, Save, Trash2 } from "lucide-react";
import FormError from "../../../components/common/FormError";
import { useLanguage } from "../../../context/LanguageContext";
import { publicAssetUrl } from "../../../lib/api";
import { apanelService } from "../../../services/apanelService";
import { homeCmsService } from "../../../services/homeCmsService";
import MediaPicker from "../components/MediaPicker";
import { useApanelLocaleCodes } from "../utils/locales";

const sectionLabels = {
  hero: "Hero",
  registrar_office: "Registrar Office",
  alt_features: "Feature List",
  strategic_goals: "Strategic Goals",
  core_values: "Core Values",
  identity: "Identity",
  stats: "Stats",
};

const emptySectionTranslation = {
  eyebrow: "",
  title: "",
  subtitle: "",
  description: "",
  secondary_title: "",
  secondary_description: "",
  cta_label: "",
  cta_url: "",
  image_alt: "",
};

const emptyItemTranslation = {
  title: "",
  description: "",
  label: "",
  action_label: "",
};

const sectionFields = [
  ["eyebrow", "Eyebrow"],
  ["title", "Title"],
  ["subtitle", "Subtitle"],
  ["description", "Description"],
  ["secondary_title", "Secondary Title"],
  ["secondary_description", "Secondary Description"],
  ["cta_label", "CTA Label"],
  ["cta_url", "CTA URL"],
  ["image_alt", "Image Alt"],
];

const itemFields = [
  ["title", "Title"],
  ["label", "Label"],
  ["description", "Description"],
  ["action_label", "Action Label"],
];

const sectionFieldKeysBySection = {
  hero: ["title", "subtitle", "cta_label", "secondary_title", "image_alt"],
  registrar_office: ["eyebrow", "title", "description", "cta_label", "cta_url", "image_alt"],
  alt_features: ["image_alt"],
  strategic_goals: ["eyebrow", "title", "subtitle", "description", "secondary_title", "cta_label", "cta_url", "image_alt"],
  stats: [],
  core_values: ["eyebrow", "title"],
  identity: ["eyebrow", "title", "description", "secondary_description", "cta_label", "cta_url", "image_alt"],
};

const itemFieldKeysBySection = {
  registrar_office: ["title"],
  alt_features: ["title", "description"],
  strategic_goals: ["title"],
  stats: ["label"],
  core_values: ["title", "description"],
};

const itemMetaKeysBySection = {
  registrar_office: [],
  alt_features: [],
  strategic_goals: [],
  stats: [
    ["value", "Value"],
    ["suffix", "Suffix"],
  ],
  core_values: [],
};

const defaultItemMetaFields = [
  ["item_key", "Key"],
  ["value", "Value"],
  ["suffix", "Suffix"],
  ["icon", "Icon"],
  ["url", "URL"],
];

const normalizeTranslations = (translations = [], localeCodes, emptyShape) =>
  Object.fromEntries(
    localeCodes.map((locale) => {
      const existing = translations.find((item) => item.locale === locale);
      return [locale, { ...emptyShape, ...(existing || {}) }];
    }),
  );

const normalizeSection = (section, localeCodes) => ({
  section_key: section.section_key,
  section_type: section.section_type || "content",
  sort_order: Number(section.sort_order || 0),
  is_active: Boolean(section.is_active ?? true),
  settings: section.settings || {},
  translations: normalizeTranslations(section.translations, localeCodes, emptySectionTranslation),
  items: (section.items || []).map((item, index) => ({
    item_key: item.item_key || `item_${index + 1}`,
    icon: item.icon || "",
    value: item.value || "",
    suffix: item.suffix || "",
    url: item.url || "",
    sort_order: Number(item.sort_order ?? index),
    is_active: Boolean(item.is_active ?? true),
    settings: item.settings || {},
    translations: normalizeTranslations(item.translations, localeCodes, emptyItemTranslation),
  })),
});

const inputClass =
  "w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none";

const itemlessSections = new Set(["hero", "identity"]);

const sectionSettingFieldsBySection = {
  hero: [
    ["cta_url", "CTA URL"],
    ["video_url", "Hero Video URL"],
  ],
};

const sectionFieldLabel = (sectionKey, key, fallback) => {
  if (sectionKey === "hero" && key === "secondary_title") {
    return "CTA Label Secondary";
  }

  return fallback;
};

export default function ApanelHomeCms() {
  const { t } = useLanguage();
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || "en";
  const [sections, setSections] = useState([]);
  const [activeSection, setActiveSection] = useState("");
  const [activeLocale, setActiveLocale] = useState("");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    setActiveLocale((current) => (localeCodes.includes(current) ? current : primaryLocale));
  }, [localeCodes, primaryLocale]);

  useEffect(() => {
    if (!localeCodes.length) return;
    let alive = true;

    setLoading(true);
    apanelService
      .getHomeCms()
      .then((payload) => {
        if (!alive) return;
        const nextSections = (payload.sections || []).map((section) =>
          normalizeSection(section, localeCodes),
        );
        setSections(nextSections);
        setActiveSection((current) => current || nextSections[0]?.section_key || "");
      })
      .catch((err) => {
        if (alive) setError(err?.message || "Failed to load home CMS.");
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [localeCodes]);

  const section = useMemo(
    () => sections.find((item) => item.section_key === activeSection),
    [sections, activeSection],
  );
  const showItems = section ? !itemlessSections.has(section.section_key) : false;
  const visibleSectionFields = useMemo(() => {
    if (!section) return [];
    const allowed = sectionFieldKeysBySection[section.section_key];
    return sectionFields.filter(([key]) => !allowed || allowed.includes(key));
  }, [section]);

  const updateSection = (patch) => {
    setSections((current) =>
      current.map((item) =>
        item.section_key === activeSection ? { ...item, ...patch } : item,
      ),
    );
  };

  const updateSectionTranslation = (key, value) => {
    updateSection({
      translations: {
        ...section.translations,
        [activeLocale]: {
          ...section.translations[activeLocale],
          [key]: value,
        },
      },
    });
  };

  const updateSectionSettings = (key, value) => {
    updateSection({
      settings: {
        ...(section.settings || {}),
        [key]: value,
      },
    });
  };

  const updateItem = (index, patch) => {
    updateSection({
      items: section.items.map((item, itemIndex) =>
        itemIndex === index ? { ...item, ...patch } : item,
      ),
    });
  };

  const updateItemTranslation = (index, key, value) => {
    const item = section.items[index];
    updateItem(index, {
      translations: {
        ...item.translations,
        [activeLocale]: {
          ...item.translations[activeLocale],
          [key]: value,
        },
      },
    });
  };

  const addItem = () => {
    const index = section.items.length + 1;
    updateSection({
      items: [
        ...section.items,
        {
          item_key: `item_${Date.now()}`,
          icon: "",
          value: "",
          suffix: "",
          url: "",
          sort_order: index,
          is_active: true,
          settings: {},
          translations: Object.fromEntries(
            localeCodes.map((locale) => [locale, { ...emptyItemTranslation }]),
          ),
        },
      ],
    });
  };

  const removeItem = (index) => {
    updateSection({
      items: section.items.filter((_, itemIndex) => itemIndex !== index),
    });
  };

  const save = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");

    try {
      await apanelService.updateHomeCms({ sections });
      homeCmsService.clearCache();
    } catch (err) {
      setError(err?.message || "Failed to save home CMS.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex min-h-80 items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <form onSubmit={save} className="space-y-6">
      <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <h1 className="text-2xl font-black uppercase tracking-wide text-navy">
          Home CMS
        </h1>
        <p className="mt-2 text-sm font-semibold text-gray-500">
          Manage homepage sections in all active languages.
        </p>
      </div>

      <FormError message={error} />

      <div className="flex flex-wrap gap-2 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm">
        {sections.map((item) => (
          <button
            type="button"
            key={item.section_key}
            onClick={() => setActiveSection(item.section_key)}
            className={`rounded-xl px-4 py-2 text-xs font-extrabold ${
              activeSection === item.section_key
                ? "bg-primary text-white"
                : "bg-gray-50 text-gray-600 hover:bg-primary/10 hover:text-primary"
            }`}
          >
            {sectionLabels[item.section_key] || item.section_key}
          </button>
        ))}
      </div>

      {section && (
        <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
          <div className="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <h2 className="text-xl font-black text-navy">
                {sectionLabels[section.section_key] || section.section_key}
              </h2>
              <p className="mt-1 text-xs font-bold text-gray-400">
                Section key: {section.section_key}
              </p>
            </div>
            <div className="flex flex-wrap gap-2">
              {localeCodes.map((locale) => (
                <button
                  type="button"
                  key={locale}
                  onClick={() => setActiveLocale(locale)}
                  className={`rounded-lg px-3 py-1.5 text-xs font-extrabold uppercase ${
                    activeLocale === locale
                      ? "bg-primary text-white"
                      : "bg-gray-50 text-gray-500"
                  }`}
                >
                  {locale}
                </button>
              ))}
            </div>
          </div>

          <div className="mb-6 grid gap-4 md:grid-cols-3">
            <label className="text-xs font-bold text-gray-500">
              Sort Order
              <input
                type="number"
                min="0"
                className={`${inputClass} mt-1`}
                value={section.sort_order}
                onChange={(event) => updateSection({ sort_order: Number(event.target.value) })}
              />
            </label>
            <label className="flex min-h-10.5 items-center gap-2 self-end rounded-xl border border-gray-100 px-3 py-2.5 text-sm font-bold text-navy">
              <input
                type="checkbox"
                checked={section.is_active}
                onChange={(event) => updateSection({ is_active: event.target.checked })}
              />
              Active
            </label>
          </div>

          {visibleSectionFields.length > 0 && (
            <div className="grid gap-4 md:grid-cols-2">
              {visibleSectionFields.map(([key, label]) => {
              const multiline = key.includes("description") || key === "subtitle";
              const value = section.translations[activeLocale]?.[key] || "";
              const displayLabel = sectionFieldLabel(section.section_key, key, label);

              return (
                <label key={key} className="text-xs font-bold text-gray-500">
                  {displayLabel}
                  {multiline ? (
                    <textarea
                      rows={4}
                      className={`${inputClass} mt-1 resize-y`}
                      value={value}
                      onChange={(event) => updateSectionTranslation(key, event.target.value)}
                    />
                  ) : (
                    <input
                      className={`${inputClass} mt-1`}
                      value={value}
                      onChange={(event) => updateSectionTranslation(key, event.target.value)}
                    />
                  )}
                </label>
              );
            })}
            </div>
          )}

          {(sectionSettingFieldsBySection[section.section_key] || []).length > 0 && (
            <div className="mt-6 grid gap-4 md:grid-cols-2">
              {sectionSettingFieldsBySection[section.section_key].map(([key, label]) =>
                key === "video_url" ? (
                  <div key={key}>
                    <MediaPicker
                      label={label}
                      value={section.settings?.[key] || ""}
                      onChange={(value) => updateSectionSettings(key, value)}
                    />
                  </div>
                ) : (
                  <label key={key} className="text-xs font-bold text-gray-500">
                    {label}
                    <input
                      className={`${inputClass} mt-1`}
                      value={section.settings?.[key] || ""}
                      onChange={(event) => updateSectionSettings(key, event.target.value)}
                    />
                  </label>
                ),
              )}
            </div>
          )}

          {["hero", "registrar_office", "alt_features", "strategic_goals", "identity"].includes(section.section_key) && (
            <div className="mt-6 rounded-2xl border border-gray-100 bg-gray-50/50 p-4">
              <MediaPicker
                label={
                  section.section_key === "hero"
                    ? "Hero Image"
                    : section.section_key === "registrar_office"
                    ? "Registrar Image"
                    : section.section_key === "strategic_goals"
                      ? "Strategic Goals Image"
                      : section.section_key === "identity"
                        ? "Identity Image"
                        : "Feature List Image"
                }
                value={section.settings?.image || ""}
                onChange={(value) => updateSectionSettings("image", value)}
              />
              {section.settings?.image && (
                <div className="mt-4 overflow-hidden rounded-2xl border border-gray-100 bg-white">
                  <img
                    src={publicAssetUrl(section.settings.image)}
                    alt={section.translations[activeLocale]?.image_alt || section.translations[activeLocale]?.title || ""}
                    className="h-44 w-full object-cover"
                  />
                </div>
              )}
            </div>
          )}

          {section.section_key === "hero" && (
            <div className="mt-6 rounded-2xl border border-gray-100 bg-gray-50/50 p-4">
              <h3 className="mb-4 text-sm font-black uppercase tracking-wide text-navy">
                Hero Cards
              </h3>
              <div className="grid gap-4 lg:grid-cols-2">
                {[
                  ["student_count", "Students Card"],
                  ["accreditation", "Accreditation Card"],
                ].map(([itemKey, title]) => {
                  const itemIndex = section.items.findIndex((item) => item.item_key === itemKey);
                  const item = section.items[itemIndex];
                  if (!item) return null;

                  return (
                    <div key={itemKey} className="rounded-2xl border border-gray-100 bg-white p-4">
                      <h4 className="mb-4 text-xs font-black uppercase tracking-wide text-navy">
                        {title}
                      </h4>
                      {itemKey === "student_count" && (
                        <div className="mb-4 grid gap-3 sm:grid-cols-2">
                          <label className="text-xs font-bold text-gray-500">
                            Value
                            <input
                              className={`${inputClass} mt-1`}
                              value={item.value || ""}
                              onChange={(event) => updateItem(itemIndex, { value: event.target.value })}
                            />
                          </label>
                          <label className="text-xs font-bold text-gray-500">
                            Suffix
                            <input
                              className={`${inputClass} mt-1`}
                              value={item.suffix || ""}
                              onChange={(event) => updateItem(itemIndex, { suffix: event.target.value })}
                            />
                          </label>
                        </div>
                      )}
                      <div className="grid gap-3">
                        <label className="text-xs font-bold text-gray-500">
                          {itemKey === "student_count" ? "Label" : "Title"}
                          <input
                            className={`${inputClass} mt-1`}
                            value={
                              itemKey === "student_count"
                                ? item.translations[activeLocale]?.label || ""
                                : item.translations[activeLocale]?.title || ""
                            }
                            onChange={(event) =>
                              updateItemTranslation(
                                itemIndex,
                                itemKey === "student_count" ? "label" : "title",
                                event.target.value,
                              )
                            }
                          />
                        </label>
                        {itemKey === "accreditation" && (
                          <label className="text-xs font-bold text-gray-500">
                            Label
                            <input
                              className={`${inputClass} mt-1`}
                              value={item.translations[activeLocale]?.label || ""}
                              onChange={(event) =>
                                updateItemTranslation(itemIndex, "label", event.target.value)
                              }
                            />
                          </label>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          )}

          {showItems && (
          <div className="mt-8 border-t border-gray-100 pt-6">
            <div className="mb-4 flex items-center justify-between">
              <h3 className="text-sm font-black uppercase tracking-wide text-navy">
                Items
              </h3>
              <button
                type="button"
                onClick={addItem}
                className="inline-flex items-center gap-2 rounded-xl bg-primary/10 px-3 py-2 text-xs font-extrabold text-primary"
              >
                <Plus className="h-4 w-4" />
                Add Item
              </button>
            </div>

            <div className="space-y-4">
              {section.items.map((item, index) => (
                <div key={item.item_key || index} className="rounded-2xl border border-gray-100 p-4">
                  {section.section_key === "core_values" && (
                    <div className="mb-4 rounded-2xl border border-gray-100 bg-gray-50/50 p-4">
                      <MediaPicker
                        label="Card Image"
                        value={item.settings?.image || ""}
                        onChange={(value) =>
                          updateItem(index, {
                            settings: {
                              ...(item.settings || {}),
                              image: value,
                            },
                          })
                        }
                      />
                      {item.settings?.image && (
                        <div className="mt-4 overflow-hidden rounded-2xl border border-gray-100 bg-white">
                          <img
                            src={publicAssetUrl(item.settings.image)}
                            alt={item.translations[activeLocale]?.title || ""}
                            className="h-36 w-full object-cover"
                          />
                        </div>
                      )}
                    </div>
                  )}

                  <div className="mb-4 grid gap-3 md:grid-cols-5">
                    {(itemMetaKeysBySection[section.section_key] ?? defaultItemMetaFields).map(([key, label]) => (
                      <label key={key} className="text-xs font-bold text-gray-500">
                        {label}
                        <input
                          className={`${inputClass} mt-1`}
                          value={item[key] || ""}
                          onChange={(event) => updateItem(index, { [key]: event.target.value })}
                        />
                      </label>
                    ))}
                  </div>

                  <div className="grid gap-3 md:grid-cols-4">
                    {itemFields
                      .filter(([key]) => {
                        const allowed = itemFieldKeysBySection[section.section_key];
                        return !allowed || allowed.includes(key);
                      })
                      .map(([key, label]) => {
                      const multiline = key === "description";
                      const value = item.translations[activeLocale]?.[key] || "";

                      return (
                        <label key={key} className="text-xs font-bold text-gray-500">
                          {label}
                          {multiline ? (
                            <textarea
                              rows={3}
                              className={`${inputClass} mt-1 resize-y`}
                              value={value}
                              onChange={(event) =>
                                updateItemTranslation(index, key, event.target.value)
                              }
                            />
                          ) : (
                            <input
                              className={`${inputClass} mt-1`}
                              value={value}
                              onChange={(event) =>
                                updateItemTranslation(index, key, event.target.value)
                              }
                            />
                          )}
                        </label>
                      );
                    })}
                  </div>

                  <div className="mt-4 flex items-center justify-between">
                    <label className="flex items-center gap-2 text-xs font-bold text-navy">
                      <input
                        type="checkbox"
                        checked={item.is_active}
                        onChange={(event) => updateItem(index, { is_active: event.target.checked })}
                      />
                      Active item
                    </label>
                    <button
                      type="button"
                      onClick={() => removeItem(index)}
                      className="inline-flex items-center gap-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-extrabold text-red-600"
                    >
                      <Trash2 className="h-4 w-4" />
                      Remove
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
          )}
        </div>
      )}

      <button
        disabled={saving}
        className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white disabled:opacity-60"
      >
        {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
        {t("common.save") || "Save"}
      </button>
    </form>
  );
}
