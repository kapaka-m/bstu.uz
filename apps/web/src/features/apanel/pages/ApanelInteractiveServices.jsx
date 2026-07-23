import { useCallback, useEffect, useMemo, useState } from "react";
import {
  Edit3,
  Eye,
  EyeOff,
  Loader2,
  Plus,
  RefreshCw,
  Save,
  Search,
  Trash2,
  X,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import ConfirmDialog from "../components/ConfirmDialog";

const locales = ["en", "uz", "ru", "ar"];
const colors = ["cyan", "teal", "red", "indigo", "orange", "pink", "blue", "emerald", "violet", "amber", "rose"];
const icons = [
  "contact",
  "user-check",
  "pie-chart",
  "graduation-cap",
  "credit-card",
  "book-open",
  "calendar",
  "list-todo",
  "file-text",
  "users",
  "map",
  "trending-up",
  "mail",
  "home",
  "send",
  "alert-triangle",
];

const emptyTranslation = {
  title: "",
  description: "",
  action_label: "",
};

const emptyForm = {
  slug: "",
  icon: "contact",
  url: "",
  color: "cyan",
  home_visible: true,
  opens_new_tab: true,
  sort_order: 0,
  is_active: true,
  translations: Object.fromEntries(locales.map((locale) => [locale, { ...emptyTranslation }])),
};

const emptySettingsTranslation = {
  home_tag: "",
  home_title: "",
  view_all_label: "",
  loading_label: "",
  no_results_label: "",
};

const emptySettings = {
  home_limit: 4,
  is_active: true,
  translations: Object.fromEntries(
    locales.map((locale) => [locale, { ...emptySettingsTranslation }]),
  ),
};

function slugify(value) {
  return value
    .toString()
    .toLowerCase()
    .trim()
    .replace(/['"]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function translationsFromRecord(record) {
  return Object.fromEntries(
    locales.map((locale) => {
      const existing = record.translations?.find((item) => item.locale === locale);
      return [locale, { ...emptyTranslation, ...(existing || {}) }];
    }),
  );
}

function fromRecord(record) {
  return {
    slug: record.slug || "",
    icon: record.icon || "contact",
    url: record.url || "",
    color: record.color || "cyan",
    home_visible: Boolean(record.home_visible ?? true),
    opens_new_tab: Boolean(record.opens_new_tab ?? true),
    sort_order: Number(record.sort_order || 0),
    is_active: Boolean(record.is_active ?? true),
    translations: translationsFromRecord(record),
  };
}

function toPayload(form) {
  const fallback = form.translations.en || emptyTranslation;
  return {
    ...form,
    url: form.url || null,
    sort_order: Number(form.sort_order || 0),
    translations: Object.fromEntries(
      locales.map((locale) => {
        const current = form.translations[locale] || emptyTranslation;
        return [
          locale,
          {
            title: current.title || fallback.title || form.slug,
            description: current.description || fallback.description || "",
            action_label: current.action_label || fallback.action_label || "",
          },
        ];
      }),
    ),
  };
}

function inputClass() {
  return "w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none";
}

function toggleClass() {
  return "mt-1 flex h-11 w-full items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 text-sm font-bold text-navy";
}

export default function ApanelInteractiveServices() {
  const [activeTab, setActiveTab] = useState("items");
  const [activeLocale, setActiveLocale] = useState("en");
  const [items, setItems] = useState([]);
  const [form, setForm] = useState(emptyForm);
  const [settingsForm, setSettingsForm] = useState(emptySettings);
  const [editingRecord, setEditingRecord] = useState(null);
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [savingSettings, setSavingSettings] = useState(false);
  const [deletingId, setDeletingId] = useState(null);
  const [error, setError] = useState("");

  const fetchItems = useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const page = await apanelService.listPage("services", {
        search,
        per_page: 100,
        sort_by: "sort_order",
        sort_dir: "asc",
      });
      setItems(page.items);
    } catch (err) {
      setError(err?.message || "Failed to load interactive services.");
    } finally {
      setLoading(false);
    }
  }, [search]);

  const fetchSettings = useCallback(async () => {
    try {
      const response = await apanelService.getInteractiveServiceSettings();
      const setting = response.data || response;
      setSettingsForm({
        home_limit: Number(setting.home_limit || 4),
        is_active: Boolean(setting.is_active ?? true),
        translations: Object.fromEntries(
          locales.map((locale) => {
            const existing = setting.translations?.find((item) => item.locale === locale);
            return [locale, { ...emptySettingsTranslation, ...(existing || {}) }];
          }),
        ),
      });
    } catch (err) {
      setError(err?.message || "Failed to load interactive service settings.");
    }
  }, []);

  useEffect(() => {
    fetchItems();
  }, [fetchItems]);

  useEffect(() => {
    fetchSettings();
  }, [fetchSettings]);

  const filteredItems = useMemo(() => items, [items]);

  const updateTranslation = (locale, key, value) => {
    setForm((current) => ({
      ...current,
      translations: {
        ...current.translations,
        [locale]: { ...current.translations[locale], [key]: value },
      },
    }));
  };

  const updateSettingTranslation = (locale, key, value) => {
    setSettingsForm((current) => ({
      ...current,
      translations: {
        ...current.translations,
        [locale]: { ...current.translations[locale], [key]: value },
      },
    }));
  };

  const startCreate = () => {
    setEditingRecord(null);
    setForm(emptyForm);
    setActiveLocale("en");
    setActiveTab("items");
    setIsFormOpen(true);
  };

  const startEdit = (record) => {
    setEditingRecord(record);
    setForm(fromRecord(record));
    setActiveLocale("en");
    setActiveTab("items");
    setIsFormOpen(true);
  };

  const closeForm = () => {
    setIsFormOpen(false);
    setEditingRecord(null);
    setForm(emptyForm);
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");
    try {
      const payload = toPayload(form);
      if (editingRecord) {
        await apanelService.update("services", editingRecord.id, payload);
      } else {
        await apanelService.create("services", payload);
      }
      setForm(emptyForm);
      setEditingRecord(null);
      setIsFormOpen(false);
      await fetchItems();
    } catch (err) {
      setError(err?.message || "Failed to save interactive service.");
    } finally {
      setSaving(false);
    }
  };

  const togglePublish = async (record) => {
    const payload = toPayload(fromRecord(record));
    payload.is_active = !record.is_active;
    await apanelService.update("services", record.id, payload);
    await fetchItems();
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    setDeletingId(pendingDelete.id);
    try {
      await apanelService.delete("services", pendingDelete.id);
      setPendingDelete(null);
      await fetchItems();
    } finally {
      setDeletingId(null);
    }
  };

  const handleSettingsSubmit = async (event) => {
    event.preventDefault();
    setSavingSettings(true);
    setError("");
    try {
      await apanelService.updateInteractiveServiceSettings(settingsForm);
      await fetchSettings();
    } catch (err) {
      setError(err?.message || "Failed to save settings.");
    } finally {
      setSavingSettings(false);
    }
  };

  const currentTranslation = form.translations[activeLocale] || emptyTranslation;
  const currentSettingsTranslation = settingsForm.translations[activeLocale] || emptySettingsTranslation;

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 className="mt-1 text-2xl font-black text-navy">
            Interactive Services CMS
          </h1>
          <p className="mt-2 text-sm font-semibold text-gray-500">
            Manage homepage interactive services and the public services page.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button
            onClick={() => setActiveTab("items")}
            className={`rounded-xl px-4 py-2 text-sm font-extrabold ${activeTab === "items" ? "bg-primary text-white" : "bg-gray-50 text-gray-600"}`}
          >
            Services
          </button>
          <button
            onClick={() => setActiveTab("settings")}
            className={`rounded-xl px-4 py-2 text-sm font-extrabold ${activeTab === "settings" ? "bg-primary text-white" : "bg-gray-50 text-gray-600"}`}
          >
            Settings
          </button>
          <button
            onClick={fetchItems}
            className="inline-flex items-center gap-2 rounded-xl border border-gray-100 px-4 py-2 text-sm font-bold text-gray-600"
          >
            <RefreshCw className="h-4 w-4" />
            Refresh
          </button>
        </div>
      </div>

      <FormError message={error} />

      {activeTab === "items" ? (
        <div className="grid gap-6">
          <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div className="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
              <div className="relative max-w-md grow">
                <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                <input
                  value={search}
                  onChange={(event) => setSearch(event.target.value)}
                  className="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-3 text-sm font-semibold focus:border-primary focus:outline-none"
                  placeholder="Search services..."
                />
              </div>
              <button
                onClick={startCreate}
                className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-extrabold text-white"
              >
                <Plus className="h-4 w-4" />
                Add Service
              </button>
            </div>

            {loading ? (
              <div className="flex items-center justify-center py-16 text-primary">
                <Loader2 className="h-6 w-6 animate-spin" />
              </div>
            ) : (
              <div className="overflow-x-auto rounded-2xl border border-gray-100">
                <table className="w-full min-w-215 text-left text-sm">
                  <thead className="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                      <th className="px-4 py-3">Service</th>
                      <th className="px-4 py-3">URL</th>
                      <th className="px-4 py-3">Home</th>
                      <th className="px-4 py-3">Status</th>
                      <th className="px-4 py-3 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {filteredItems.map((item) => {
                      const en = item.translations?.find(
                        (translation) => translation.locale === "en",
                      );
                      return (
                        <tr key={item.id} className="align-top">
                          <td className="px-4 py-4">
                            <div className="font-extrabold text-navy">
                              {en?.title || item.slug}
                            </div>
                            <div className="mt-1 text-xs font-semibold text-gray-400">
                              {item.slug}
                            </div>
                          </td>
                          <td className="max-w-xs truncate px-4 py-4 text-xs font-semibold text-gray-500">
                            {item.url}
                          </td>
                          <td className="px-4 py-4">
                            <span
                              className={`rounded-full px-2.5 py-1 text-xs font-bold ${item.home_visible ? "bg-emerald-50 text-emerald-700" : "bg-gray-100 text-gray-500"}`}
                            >
                              {item.home_visible ? "Visible" : "Hidden"}
                            </span>
                          </td>
                          <td className="px-4 py-4">
                            <span
                              className={`rounded-full px-2.5 py-1 text-xs font-bold ${item.is_active ? "bg-primary/10 text-primary" : "bg-red-50 text-red-700"}`}
                            >
                              {item.is_active ? "Published" : "Draft"}
                            </span>
                          </td>
                          <td className="px-4 py-4">
                            <div className="flex justify-end gap-2">
                              <button
                                onClick={() => startEdit(item)}
                                className="rounded-lg border border-gray-100 p-2 text-gray-500 hover:text-primary"
                              >
                                <Edit3 className="h-4 w-4" />
                              </button>
                              <button
                                onClick={() => togglePublish(item)}
                                className="rounded-lg border border-gray-100 p-2 text-gray-500 hover:text-primary"
                              >
                                {item.is_active ? (
                                  <EyeOff className="h-4 w-4" />
                                ) : (
                                  <Eye className="h-4 w-4" />
                                )}
                              </button>
                              <button
                                onClick={() => setPendingDelete(item)}
                                disabled={deletingId === item.id}
                                className="rounded-lg border border-red-100 p-2 text-red-500 hover:bg-red-50 disabled:opacity-50"
                              >
                                <Trash2 className="h-4 w-4" />
                              </button>
                            </div>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </div>

          {isFormOpen && (
            <div className="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 p-4 backdrop-blur-sm">
              <form
                onSubmit={handleSubmit}
                className="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl border border-gray-100 bg-white p-6 shadow-2xl"
              >
                <div className="mb-5 flex items-center justify-between">
                  <div>
                    <p className="text-xs font-extrabold uppercase tracking-widest text-primary">
                      Service
                    </p>
                    <h2 className="text-lg font-black text-navy">
                      {editingRecord ? "Edit Service" : "Add Service"}
                    </h2>
                  </div>
                  <button
                    type="button"
                    onClick={closeForm}
                    className="rounded-lg p-2 text-gray-400 hover:bg-gray-50"
                  >
                    <X className="h-4 w-4" />
                  </button>
                </div>

                <datalist id="interactive-service-icons">
                  {icons.map((icon) => (
                    <option key={icon} value={icon} />
                  ))}
                </datalist>
                <datalist id="interactive-service-colors">
                  {colors.map((color) => (
                    <option key={color} value={color} />
                  ))}
                </datalist>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <label className="text-xs font-bold text-gray-500">
                    Slug
                    <input
                      className={`${inputClass()} mt-1`}
                      value={form.slug}
                      onChange={(event) =>
                        setForm((current) => ({
                          ...current,
                          slug: slugify(event.target.value),
                        }))
                      }
                      required
                    />
                  </label>

                  <label className="text-xs font-bold text-gray-500 lg:col-span-2">
                    URL
                    <input
                      className={`${inputClass()} mt-1`}
                      value={form.url}
                      onChange={(event) =>
                        setForm((current) => ({
                          ...current,
                          url: event.target.value,
                        }))
                      }
                    />
                  </label>

                  <label className="text-xs font-bold text-gray-500">
                    Icon
                    <input
                      list="interactive-service-icons"
                      className={`${inputClass()} mt-1`}
                      value={form.icon}
                      onChange={(event) =>
                        setForm((current) => ({
                          ...current,
                          icon: slugify(event.target.value),
                        }))
                      }
                    />
                  </label>

                  <label className="text-xs font-bold text-gray-500">
                    Color
                    <input
                      list="interactive-service-colors"
                      className={`${inputClass()} mt-1`}
                      value={form.color}
                      onChange={(event) =>
                        setForm((current) => ({
                          ...current,
                          color: slugify(event.target.value),
                        }))
                      }
                    />
                  </label>

                  <label className="text-xs font-bold text-gray-500">
                    Order
                    <input
                      type="number"
                      className={`${inputClass()} mt-1`}
                      value={form.sort_order}
                      onChange={(event) =>
                        setForm((current) => ({
                          ...current,
                          sort_order: event.target.value,
                        }))
                      }
                    />
                  </label>

                  <label className="text-xs font-bold text-gray-500">
                    Home
                    <span className={toggleClass()}>
                      <input
                        type="checkbox"
                        checked={form.home_visible}
                        onChange={(event) =>
                          setForm((current) => ({
                            ...current,
                            home_visible: event.target.checked,
                          }))
                        }
                      />
                      Show on home
                    </span>
                  </label>

                  <label className="text-xs font-bold text-gray-500">
                    Active
                    <span className={toggleClass()}>
                      <input
                        type="checkbox"
                        checked={form.is_active}
                        onChange={(event) =>
                          setForm((current) => ({
                            ...current,
                            is_active: event.target.checked,
                          }))
                        }
                      />
                      Published
                    </span>
                  </label>

                  <label className="text-xs font-bold text-gray-500">
                    Opens New Tab
                    <span className={toggleClass()}>
                      <input
                        type="checkbox"
                        checked={form.opens_new_tab}
                        onChange={(event) =>
                          setForm((current) => ({
                            ...current,
                            opens_new_tab: event.target.checked,
                          }))
                        }
                      />
                      External tab
                    </span>
                  </label>

                  <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 lg:col-span-3">
                    {locales.map((locale) => (
                      <button
                        type="button"
                        key={locale}
                        onClick={() => setActiveLocale(locale)}
                        className={`rounded-lg px-3 py-1.5 text-xs font-extrabold uppercase ${activeLocale === locale ? "bg-primary text-white" : "bg-gray-50 text-gray-500"}`}
                      >
                        {locale}
                      </button>
                    ))}
                  </div>

                  <label className="text-xs font-bold text-gray-500">
                    Title
                    <input
                      className={`${inputClass()} mt-1`}
                      value={currentTranslation.title}
                      onChange={(event) =>
                        updateTranslation(
                          activeLocale,
                          "title",
                          event.target.value,
                        )
                      }
                    />
                  </label>
                  <label className="text-xs font-bold text-gray-500">
                    Button Label
                    <input
                      className={`${inputClass()} mt-1`}
                      value={currentTranslation.action_label}
                      onChange={(event) =>
                        updateTranslation(
                          activeLocale,
                          "action_label",
                          event.target.value,
                        )
                      }
                    />
                  </label>
                  <label className="text-xs font-bold text-gray-500 lg:col-span-3">
                    Description
                    <textarea
                      className={`${inputClass()} mt-1 min-h-24`}
                      value={currentTranslation.description}
                      onChange={(event) =>
                        updateTranslation(
                          activeLocale,
                          "description",
                          event.target.value,
                        )
                      }
                    />
                  </label>

                  <button
                    disabled={saving}
                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 text-sm font-extrabold text-white disabled:opacity-60 lg:col-span-3"
                  >
                    {saving ? (
                      <Loader2 className="h-4 w-4 animate-spin" />
                    ) : (
                      <Save className="h-4 w-4" />
                    )}
                    Save Service
                  </button>
                </div>
              </form>
            </div>
          )}
        </div>
      ) : (
        <form
          onSubmit={handleSettingsSubmit}
          className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm"
        >
          <div className="mb-6 grid gap-4 md:grid-cols-[180px_180px_1fr]">
            <label className="text-xs font-bold text-gray-500">
              Home Limit
              <input
                type="number"
                min="1"
                max="24"
                className={`${inputClass()} mt-1`}
                value={settingsForm.home_limit}
                onChange={(event) =>
                  setSettingsForm((current) => ({
                    ...current,
                    home_limit: Number(event.target.value),
                  }))
                }
              />
            </label>
            <label className="flex min-h-10.5 items-center gap-2 self-end rounded-xl border border-gray-100 px-3 py-2.5 text-sm font-bold text-navy">
              <input
                type="checkbox"
                checked={settingsForm.is_active}
                onChange={(event) =>
                  setSettingsForm((current) => ({
                    ...current,
                    is_active: event.target.checked,
                  }))
                }
              />
              Active
            </label>
          </div>

          <div className="mb-5 flex flex-wrap gap-2">
            {locales.map((locale) => (
              <button
                type="button"
                key={locale}
                onClick={() => setActiveLocale(locale)}
                className={`rounded-lg px-3 py-1.5 text-xs font-extrabold uppercase ${activeLocale === locale ? "bg-primary text-white" : "bg-gray-50 text-gray-500"}`}
              >
                {locale}
              </button>
            ))}
          </div>

          <div className="grid gap-4 md:grid-cols-2">
            {Object.keys(emptySettingsTranslation).map((key) => (
              <label key={key} className="text-xs font-bold text-gray-500">
                {key.replaceAll("_", " ")}
                <input
                  className={`${inputClass()} mt-1`}
                  value={currentSettingsTranslation[key] || ""}
                  onChange={(event) =>
                    updateSettingTranslation(
                      activeLocale,
                      key,
                      event.target.value,
                    )
                  }
                />
              </label>
            ))}
          </div>

          <button
            disabled={savingSettings}
            className="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white disabled:opacity-60"
          >
            {savingSettings ? (
              <Loader2 className="h-4 w-4 animate-spin" />
            ) : (
              <Save className="h-4 w-4" />
            )}
            Save Settings
          </button>
        </form>
      )}
      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title="Delete service?"
        message={`This will permanently delete ${pendingDelete?.slug || "this service"}. This action cannot be undone.`}
        confirmText={deletingId ? "Deleting..." : "Delete"}
        cancelText="Cancel"
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
