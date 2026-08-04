import { useCallback, useEffect, useMemo, useState } from "react";
import {
  Calendar,
  CheckCircle2,
  Edit3,
  Eye,
  EyeOff,
  Home,
  Image as ImageIcon,
  Loader2,
  Megaphone,
  Plus,
  RefreshCw,
  Save,
  Search,
  Sparkles,
  Trash2,
  Upload,
  X,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { publicAssetUrl } from "../../../lib/api";
import ConfirmDialog from "../components/ConfirmDialog";
import ApanelStatsCards from "../components/ApanelStatsCards";
import { useApanelLocaleCodes } from "../utils/locales";
import { useLanguage } from "../../../context/LanguageContext";

const emptyTranslation = { title: "", category_label: "", summary: "", content: "" };
const emptyForm = (localeCodes) => ({
  slug: "",
  type: "announcements",
  priority: "normal",
  image: "",
  starts_at: "",
  ends_at: "",
  is_published: true,
  views_count: 0,
  translations: Object.fromEntries(localeCodes.map((locale) => [locale, { ...emptyTranslation }])),
});

const emptySettingsTranslation = {
  home_tag: "",
  home_title: "",
  view_all_label: "",
  read_details_label: "",
  search_title: "",
  search_placeholder: "",
  categories_title: "",
  recent_title: "",
  all_label: "",
  views_label: "",
  important_label: "",
  loading_label: "",
  no_results_label: "",
  clear_filters_label: "",
  share_label: "",
  copy_link_label: "",
  copied_label: "",
  published_by_label: "",
  publisher_name: "",
};

const emptySettings = (localeCodes) => ({
  home_limit: 4,
  recent_limit: 5,
  important_limit: 3,
  is_active: true,
  translations: Object.fromEntries(
    localeCodes.map((locale) => [locale, { ...emptySettingsTranslation }]),
  ),
});

function slugify(value) {
  return value
    .toString()
    .toLowerCase()
    .trim()
    .replace(/['"]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function toDateInput(value) {
  if (!value) return "";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "";
  return date.toISOString().slice(0, 10);
}

function imagePreviewSrc(image) {
  return publicAssetUrl(image);
}

function translationsFromRecord(record, localeCodes) {
  return Object.fromEntries(
    localeCodes.map((locale) => {
      const existing = record.translations?.find((item) => item.locale === locale);
      return [locale, { ...emptyTranslation, ...(existing || {}) }];
    }),
  );
}

function fromRecord(record, localeCodes) {
  return {
    slug: record.slug || "",
    type: record.type || "announcements",
    priority: record.priority || "normal",
    image: record.image || "",
    starts_at: toDateInput(record.starts_at),
    ends_at: toDateInput(record.ends_at),
    is_published: Boolean(record.is_published ?? true),
    views_count: Number(record.views_count || 0),
    translations: translationsFromRecord(record, localeCodes),
  };
}

function toPayload(form, primaryLocale, localeCodes) {
  const fallback = form.translations[primaryLocale] || Object.values(form.translations || {})[0] || emptyTranslation;
  return {
    slug: form.slug,
    type: form.type,
    priority: form.priority,
    image: form.image || null,
    starts_at: form.starts_at || null,
    ends_at: form.ends_at || null,
    is_published: Boolean(form.is_published),
    views_count: Number(form.views_count || 0),
    translations: Object.fromEntries(
      localeCodes.map((locale) => {
        const current = form.translations[locale] || emptyTranslation;
        return [
          locale,
          {
            title: current.title || fallback.title || form.slug,
            category_label: current.category_label || fallback.category_label || form.type,
            summary: current.summary || fallback.summary || "",
            content: current.content || fallback.content || "",
          },
        ];
      }),
    ),
  };
}

export default function ApanelAnnouncements() {
  const { t } = useLanguage();
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || "";
  const [activeTab, setActiveTab] = useState("items");
  const [activeLocale, setActiveLocale] = useState("");
  const [items, setItems] = useState([]);
  const [settingsForm, setSettingsForm] = useState(() => emptySettings([]));
  const [form, setForm] = useState(() => emptyForm([]));
  const [editingRecord, setEditingRecord] = useState(null);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [savingSettings, setSavingSettings] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [deletingId, setDeletingId] = useState(null);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!localeCodes.length) return;
    setActiveLocale((current) => (localeCodes.includes(current) ? current : primaryLocale));
    setForm((current) => ({
        ...current,
        translations: {
        ...emptyForm(localeCodes).translations,
        ...current.translations,
      },
    }));
    setSettingsForm((current) => ({
        ...current,
        translations: {
        ...emptySettings(localeCodes).translations,
        ...current.translations,
      },
    }));
  }, [localeCodes, primaryLocale]);

  const fetchItems = useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const page = await apanelService.listPage("announcements", {
        search,
        per_page: 100,
        sort_by: "starts_at",
        sort_dir: "desc",
      });
      setItems(page.items);
    } catch (err) {
      setError(err?.message || "Failed to load announcements.");
    } finally {
      setLoading(false);
    }
  }, [search]);

  const fetchSettings = useCallback(async () => {
    try {
      const response = await apanelService.getAnnouncementSettings();
      const setting = response.data || response;
      setSettingsForm({
        home_limit: setting.home_limit || 4,
        recent_limit: setting.recent_limit || 5,
        important_limit: setting.important_limit || 3,
        is_active: Boolean(setting.is_active ?? true),
        translations: Object.fromEntries(
          localeCodes.map((locale) => {
            const existing = setting.translations?.find((item) => item.locale === locale);
            return [locale, { ...emptySettingsTranslation, ...(existing || {}) }];
          }),
        ),
      });
    } catch (err) {
      setError(err?.message || "Failed to load announcement settings.");
    }
  }, [localeCodes]);

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
    setForm(emptyForm(localeCodes));
    setActiveLocale(primaryLocale);
    setActiveTab("editor");
  };

  const startEdit = (record) => {
    setEditingRecord(record);
    setForm(fromRecord(record, localeCodes));
    setActiveLocale(primaryLocale);
    setActiveTab("editor");
  };

  const handleImageUpload = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    try {
      setUploading(true);
      const uploadTitle = form.translations[primaryLocale]?.title || file.name;
      const response = await apanelService.uploadMedia(file, {
        title: uploadTitle,
        alt_text: uploadTitle,
        type: "image",
        is_public: true,
      });
      const media = response.data || response;
      const imagePath = media.path || media.data?.path || "";

      setForm((current) => ({ ...current, image: imagePath }));

      if (editingRecord && imagePath) {
        await apanelService.update("announcements", editingRecord.id, {
          ...toPayload(form, primaryLocale, localeCodes),
          image: imagePath,
        });
        await fetchItems();
      }
    } catch (err) {
      setError(err?.message || "Image upload failed.");
    } finally {
      setUploading(false);
      event.target.value = "";
    }
  };

  const saveItem = async (event) => {
    event.preventDefault();
    try {
      setSaving(true);
      setError("");
      const payload = toPayload(form, primaryLocale, localeCodes);
      if (editingRecord) {
        await apanelService.update("announcements", editingRecord.id, payload);
      } else {
        await apanelService.create("announcements", payload);
      }
      setForm(emptyForm(localeCodes));
      setEditingRecord(null);
      setActiveTab("items");
      await fetchItems();
    } catch (err) {
      setError(err?.message || "Failed to save announcement.");
    } finally {
      setSaving(false);
    }
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    try {
      setDeletingId(pendingDelete.id);
      await apanelService.delete("announcements", pendingDelete.id);
      setPendingDelete(null);
      await fetchItems();
    } catch (err) {
      setError(err?.message || "Failed to delete announcement.");
    } finally {
      setDeletingId(null);
    }
  };

  const saveSettings = async (event) => {
    event.preventDefault();
    try {
      setSavingSettings(true);
      setError("");
      await apanelService.updateAnnouncementSettings(settingsForm);
      await fetchSettings();
    } catch (err) {
      setError(err?.message || "Failed to save settings.");
    } finally {
      setSavingSettings(false);
    }
  };

  const pageStats = useMemo(
    () => [
      {
        label: "Announcements",
        value: items.length,
        hint: "Public records",
        icon: Megaphone,
        tone: "text-amber-600 bg-amber-50 border-amber-100",
      },
      {
        label: "Published",
        value: items.filter((item) => item.is_published).length,
        hint: "Visible announcements",
        icon: CheckCircle2,
        tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
      },
      {
        label: "Important",
        value: items.filter((item) => item.priority === "important" || item.priority === "high").length,
        hint: `${settingsForm.important_limit || 0} important limit`,
        icon: Sparkles,
        tone: "text-violet-600 bg-violet-50 border-violet-100",
      },
      {
        label: "Home limit",
        value: settingsForm.home_limit || 0,
        hint: settingsForm.is_active ? "Homepage block active" : "Homepage block hidden",
        icon: Home,
        tone: "text-blue-600 bg-blue-50 border-blue-100",
      },
    ],
    [items, settingsForm.home_limit, settingsForm.important_limit, settingsForm.is_active],
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-navy mt-1">
            Announcements CMS
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            Manage public announcements, translations, publishing and homepage
            labels.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button
            onClick={() => setActiveTab("items")}
            className={`px-4 py-2 rounded-xl text-xs font-extrabold border ${activeTab === "items" ? "bg-navy text-white border-navy" : "bg-white text-gray-500 border-gray-100"}`}
          >
            Items
          </button>
          <button
            onClick={() => setActiveTab("settings")}
            className={`px-4 py-2 rounded-xl text-xs font-extrabold border ${activeTab === "settings" ? "bg-navy text-white border-navy" : "bg-white text-gray-500 border-gray-100"}`}
          >
            Settings
          </button>
          <button
            onClick={startCreate}
            className="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-extrabold"
          >
            <Plus className="w-4 h-4" />
            Add Announcement
          </button>
        </div>
      </div>

      <FormError message={error} />

      <ApanelStatsCards items={pageStats} />

      {activeTab === "items" && (
        <div className="bg-white border border-gray-100 rounded-2xl shadow-xs overflow-hidden">
          <div className="p-5 border-b border-gray-100 flex flex-col md:flex-row gap-3 md:items-center justify-between">
            <div className="relative md:w-80">
              <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                id="apanel-announcements-search"
                name="apanel_announcements_search"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder={t("apanel.announcements.searchPlaceholder")}
                className="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-100 text-sm focus:outline-none focus:border-primary"
              />
            </div>
            <button
              onClick={fetchItems}
              className="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 text-navy rounded-xl text-xs font-extrabold"
            >
              <RefreshCw className="w-4 h-4" />
              Refresh
            </button>
          </div>

          {loading ? (
            <div className="p-10 text-center text-gray-500 font-semibold">
              <Loader2 className="w-6 h-6 animate-spin mx-auto mb-2" />
              Loading announcements...
            </div>
          ) : (
            <div className="divide-y divide-gray-100">
              {filteredItems.map((record) => {
                const title =
                  record.translations?.find((item) => item.locale === primaryLocale)
                    ?.title ||
                  record.translations?.[0]
                    ?.title || record.slug;
                return (
                  <div
                    key={record.id}
                    className="p-5 flex flex-col lg:flex-row gap-4 lg:items-center"
                  >
                    <div className="w-full lg:w-36 h-24 rounded-xl overflow-hidden bg-slate-50 border border-gray-100 shrink-0">
                      {record.image ? (
                        <img
                          src={imagePreviewSrc(record.image)}
                          alt={title}
                          className="w-full h-full object-cover"
                        />
                      ) : (
                        <div className="w-full h-full grid place-items-center text-gray-300">
                          <ImageIcon className="w-7 h-7" />
                        </div>
                      )}
                    </div>
                    <div className="grow min-w-0">
                      <div className="flex flex-wrap items-center gap-2 mb-2">
                        <span className="text-[10px] font-black uppercase bg-slate-100 text-gray-500 px-2 py-1 rounded-lg">
                          {record.type}
                        </span>
                        {record.priority === "high" && (
                          <span className="text-[10px] font-black uppercase bg-amber-100 text-amber-700 px-2 py-1 rounded-lg inline-flex items-center gap-1">
                            <Sparkles className="w-3 h-3" />
                            Important
                          </span>
                        )}
                        <span className="text-[10px] font-black uppercase bg-blue-50 text-blue-600 px-2 py-1 rounded-lg inline-flex items-center gap-1">
                          <Eye className="w-3 h-3" />
                          {record.views_count || 0}
                        </span>
                      </div>
                      <h3 className="font-black text-navy truncate">{title}</h3>
                      <p className="text-xs text-gray-400 mt-1 flex items-center gap-1">
                        <Calendar className="w-3.5 h-3.5" />
                        {toDateInput(record.starts_at) || "No start date"}
                      </p>
                    </div>
                    <div className="flex items-center gap-2">
                      <span
                        className={`inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1.5 rounded-xl ${record.is_published ? "bg-green-50 text-green-700" : "bg-gray-100 text-gray-500"}`}
                      >
                        {record.is_published ? (
                          <Eye className="w-3.5 h-3.5" />
                        ) : (
                          <EyeOff className="w-3.5 h-3.5" />
                        )}
                        {record.is_published ? "Published" : "Hidden"}
                      </span>
                      <button
                        onClick={() => startEdit(record)}
                        className="p-2 rounded-xl bg-slate-50 text-navy hover:text-primary"
                      >
                        <Edit3 className="w-4 h-4" />
                      </button>
                      <button
                        onClick={() => setPendingDelete(record)}
                        disabled={deletingId === record.id}
                        className="p-2 rounded-xl bg-rose-50 text-rose-600 disabled:opacity-60"
                      >
                        {deletingId === record.id ? (
                          <Loader2 className="w-4 h-4 animate-spin" />
                        ) : (
                          <Trash2 className="w-4 h-4" />
                        )}
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      )}

      {activeTab === "editor" && (
        <form
          onSubmit={saveItem}
          className="bg-white border border-gray-100 rounded-2xl shadow-xs p-6 space-y-6"
        >
          <div className="flex items-center justify-between gap-3">
            <h2 className="font-black text-navy">
              {editingRecord ? "Edit Announcement" : "Add Announcement"}
            </h2>
            <button
              type="button"
              onClick={() => setActiveTab("items")}
              className="p-2 rounded-xl bg-slate-50 text-gray-500"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">{t("apanel.announcements.slug")}</span>
              <input
                value={form.slug}
                onChange={(e) =>
                  setForm((current) => ({
                    ...current,
                    slug: slugify(e.target.value),
                  }))
                }
                required
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              />
            </label>
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Category Key
              </span>
              <input
                value={form.type}
                onChange={(e) =>
                  setForm((current) => ({
                    ...current,
                    type: slugify(e.target.value) || "announcements",
                  }))
                }
                required
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              />
            </label>
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Priority
              </span>
              <select
                value={form.priority}
                onChange={(e) =>
                  setForm((current) => ({
                    ...current,
                    priority: e.target.value,
                  }))
                }
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              >
                <option value="normal">{t("apanel.announcements.normal")}</option>
                <option value="high">{t("apanel.announcements.important")}</option>
              </select>
            </label>
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Starts At
              </span>
              <input
                type="date"
                value={form.starts_at}
                onChange={(e) =>
                  setForm((current) => ({
                    ...current,
                    starts_at: e.target.value,
                  }))
                }
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              />
            </label>
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Ends At
              </span>
              <input
                type="date"
                value={form.ends_at}
                onChange={(e) =>
                  setForm((current) => ({
                    ...current,
                    ends_at: e.target.value,
                  }))
                }
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              />
            </label>
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Views Count
              </span>
              <input
                type="number"
                min="0"
                value={form.views_count}
                onChange={(e) =>
                  setForm((current) => ({
                    ...current,
                    views_count: e.target.value,
                  }))
                }
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              />
            </label>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-[1fr_220px] gap-4">
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Image URL or storage path
              </span>
              <input
                value={form.image}
                onChange={(e) =>
                  setForm((current) => ({ ...current, image: e.target.value }))
                }
                placeholder={t("apanel.announcements.imagePlaceholder")}
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              />
            </label>
            <label className="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 border border-gray-100 rounded-xl text-xs font-extrabold text-navy cursor-pointer mt-5">
              {uploading ? (
                <Loader2 className="w-4 h-4 animate-spin" />
              ) : (
                <Upload className="w-4 h-4" />
              )}
              Upload Image
              <input
                type="file"
                accept="image/*"
                className="hidden"
                onChange={handleImageUpload}
              />
            </label>
          </div>

          <label className="inline-flex items-center gap-2 text-sm font-extrabold text-navy">
            <input
              type="checkbox"
              checked={form.is_published}
              onChange={(e) =>
                setForm((current) => ({
                  ...current,
                  is_published: e.target.checked,
                }))
              }
            />
            Published
          </label>

          <div className="flex flex-wrap gap-2">
            {localeCodes.map((locale) => (
              <button
                type="button"
                key={locale}
                onClick={() => setActiveLocale(locale)}
                className={`px-3 py-2 rounded-xl text-xs font-black uppercase ${activeLocale === locale ? "bg-primary text-white" : "bg-slate-50 text-gray-500"}`}
              >
                {locale}
              </button>
            ))}
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Title
              </span>
              <input
                value={form.translations[activeLocale].title}
                onChange={(e) => {
                  updateTranslation(activeLocale, "title", e.target.value);
                  if (!editingRecord && activeLocale === primaryLocale) {
                    setForm((current) => ({
                      ...current,
                      slug: slugify(e.target.value),
                    }));
                  }
                }}
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              />
            </label>
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Category Label
              </span>
              <input
                value={form.translations[activeLocale].category_label}
                onChange={(e) =>
                  updateTranslation(
                    activeLocale,
                    "category_label",
                    e.target.value,
                  )
                }
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              />
            </label>
          </div>

          <label className="space-y-1 block">
            <span className="text-xs font-extrabold text-gray-500">
              Summary
            </span>
            <textarea
              value={form.translations[activeLocale].summary}
              onChange={(e) =>
                updateTranslation(activeLocale, "summary", e.target.value)
              }
              rows={3}
              className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
            />
          </label>
          <label className="space-y-1 block">
            <span className="text-xs font-extrabold text-gray-500">
              Content
            </span>
            <textarea
              value={form.translations[activeLocale].content}
              onChange={(e) =>
                updateTranslation(activeLocale, "content", e.target.value)
              }
              rows={8}
              className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
            />
          </label>

          <button
            disabled={saving}
            className="inline-flex items-center gap-2 bg-primary text-white px-5 py-3 rounded-xl text-xs font-extrabold disabled:opacity-60"
          >
            {saving ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <Save className="w-4 h-4" />
            )}
            Save Announcement
          </button>
        </form>
      )}

      {activeTab === "settings" && (
        <form
          onSubmit={saveSettings}
          className="bg-white border border-gray-100 rounded-2xl shadow-xs p-6 space-y-6"
        >
          <h2 className="font-black text-navy">{t("apanel.announcements.settingsTitle")}</h2>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            {["home_limit", "recent_limit", "important_limit"].map((key) => (
              <label key={key} className="space-y-1">
                <span className="text-xs font-extrabold text-gray-500">
                  {key.replace(/_/g, " ")}
                </span>
                <input
                  type="number"
                  min="1"
                  max="12"
                  value={settingsForm[key]}
                  onChange={(e) =>
                    setSettingsForm((current) => ({
                      ...current,
                      [key]: Number(e.target.value),
                    }))
                  }
                  className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
                />
              </label>
            ))}
            <label className="space-y-1">
              <span className="text-xs font-extrabold text-gray-500">
                Active
              </span>
              <select
                value={settingsForm.is_active ? "1" : "0"}
                onChange={(e) =>
                  setSettingsForm((current) => ({
                    ...current,
                    is_active: e.target.value === "1",
                  }))
                }
                className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
              >
                <option value="1">{t("status.active")}</option>
                <option value="0">{t("status.hidden")}</option>
              </select>
            </label>
          </div>

          <div className="flex flex-wrap gap-2">
            {localeCodes.map((locale) => (
              <button
                type="button"
                key={locale}
                onClick={() => setActiveLocale(locale)}
                className={`px-3 py-2 rounded-xl text-xs font-black uppercase ${activeLocale === locale ? "bg-primary text-white" : "bg-slate-50 text-gray-500"}`}
              >
                {locale}
              </button>
            ))}
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {Object.keys(emptySettingsTranslation).map((key) => (
              <label key={key} className="space-y-1">
                <span className="text-xs font-extrabold text-gray-500">
                  {key.replace(/_/g, " ")}
                </span>
                <input
                  value={settingsForm.translations[activeLocale][key] || ""}
                  onChange={(e) =>
                    updateSettingTranslation(activeLocale, key, e.target.value)
                  }
                  className="w-full border border-gray-100 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary"
                />
              </label>
            ))}
          </div>

          <button
            disabled={savingSettings}
            className="inline-flex items-center gap-2 bg-primary text-white px-5 py-3 rounded-xl text-xs font-extrabold disabled:opacity-60"
          >
            {savingSettings ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <Save className="w-4 h-4" />
            )}
            Save Settings
          </button>
        </form>
      )}
      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title={t("apanel.announcements.title.deleteAnnouncement")}
        message={`This will permanently delete ${pendingDelete?.slug || "this announcement"}. This action cannot be undone.`}
        confirmText={deletingId ? "Deleting..." : "Delete"}
        cancelText="Cancel"
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
