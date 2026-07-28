import React, { useCallback, useEffect, useMemo, useState } from "react";
import {
  Calendar,
  Edit3,
  Eye,
  EyeOff,
  Image as ImageIcon,
  Loader2,
  Plus,
  RefreshCw,
  Save,
  Search,
  Trash2,
  Upload,
  X,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { publicAssetUrl } from "../../../lib/api";
import ConfirmDialog from "../components/ConfirmDialog";
import { useApanelLocaleCodes } from "../utils/locales";

const emptyTranslation = {
  title: "",
  author: "",
  category_label: "",
  summary: "",
  content: "",
  meta_title: "",
  meta_description: "",
};

const emptyForm = (localeCodes) => ({
  slug: "",
  image: "",
  author: "",
  author_image: "",
  category: "",
  published_at: "",
  is_published: true,
  views_count: 0,
  translations: Object.fromEntries(
    localeCodes.map((locale) => [locale, { ...emptyTranslation }]),
  ),
});

const emptySettings = (localeCodes) => ({
  home_limit: 3,
  recent_limit: 5,
  home_icon: "book-open",
  tags: [],
  is_active: true,
  translations: Object.fromEntries(
    localeCodes.map((locale) => [
      locale,
      {
        home_tag: "",
        home_title: "",
        view_all_label: "",
        read_more_label: "",
        search_title: "",
        search_placeholder: "",
        categories_title: "",
        recent_title: "",
        tags_title: "",
        all_blog_label: "",
        loading_label: "",
        no_results_label: "",
        clear_filters_label: "",
        back_to_blog_label: "",
        comments_label: "",
        reply_label: "",
        form_title: "",
        form_name_label: "",
        form_email_label: "",
        form_comment_label: "",
        form_submit_label: "",
        comment_login_title: "",
        comment_login_text: "",
        comment_login_action: "",
        signed_in_as_label: "",
      },
    ]),
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

function fromRecord(record, localeCodes) {
  const translations = Object.fromEntries(
    localeCodes.map((locale) => {
      const existing = record.translations?.find((item) => item.locale === locale);
      return [
        locale,
        {
          ...emptyTranslation,
          title: existing?.title || "",
          author: existing?.author || "",
          category_label: existing?.category_label || "",
          summary: existing?.summary || "",
          content: existing?.content || "",
          meta_title: existing?.meta_title || "",
          meta_description: existing?.meta_description || "",
        },
      ];
    }),
  );

  return {
    slug: record.slug || "",
    image: record.image || "",
    author: record.author || "",
    author_image: record.author_image || "",
    category: record.category || "",
    published_at: toDateInput(record.published_at),
    is_published: Boolean(record.is_published),
    views_count: Number(record.views_count || 0),
    translations,
  };
}

function toPayload(form, primaryLocale, localeCodes) {
  const fallback = form.translations[primaryLocale] || Object.values(form.translations || {})[0] || emptyTranslation;
  const translations = Object.fromEntries(
    localeCodes.map((locale) => {
      const current = form.translations[locale] || emptyTranslation;
      return [
        locale,
        {
          title: current.title || fallback.title || form.slug,
          author: current.author || fallback.author || form.author || "",
          category_label:
            current.category_label || fallback.category_label || form.category || "",
          summary: current.summary || fallback.summary || "",
          content: current.content || fallback.content || "",
          meta_title: current.meta_title || current.title || fallback.title || form.slug,
          meta_description:
            current.meta_description || current.summary || fallback.summary || "",
        },
      ];
    }),
  );

  return {
    slug: form.slug,
    image: form.image || null,
    author: form.author || translations[primaryLocale]?.author || null,
    author_image: form.author_image || null,
    category: form.category,
    published_at: form.published_at || null,
    is_published: Boolean(form.is_published),
    views_count: Number(form.views_count || 0),
    translations,
  };
}

function formatDate(value, locale) {
  if (!value) return "";
  return new Intl.DateTimeFormat(locale, {
    year: "numeric",
    month: "short",
    day: "2-digit",
  }).format(new Date(value));
}

function imagePreviewSrc(image) {
  return publicAssetUrl(image);
}

export default function ApanelBlog() {
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || "";
  const [activeTab, setActiveTab] = useState("items");
  const [items, setItems] = useState([]);
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [deletingId, setDeletingId] = useState(null);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [error, setError] = useState("");
  const [editingRecord, setEditingRecord] = useState(null);
  const [form, setForm] = useState(() => emptyForm([]));
  const [settingsForm, setSettingsForm] = useState(() => emptySettings([]));
  const [savingSettings, setSavingSettings] = useState(false);
  const [uploadingImage, setUploadingImage] = useState(false);
  const [uploadingAuthorImage, setUploadingAuthorImage] = useState(false);
  const [activeLocale, setActiveLocale] = useState("");

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

  const fetchBlog = useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const pageData = await apanelService.listPage("blogs", {
        search,
        page,
        per_page: 100,
        sort_by: "published_at",
        sort_dir: "desc",
        
      });
      setItems(pageData.items);
      setTotal(pageData.total);
      setLastPage(pageData.lastPage);
    } catch (err) {
      setError(err?.message || "Failed to load blog.");
    } finally {
      setLoading(false);
    }
  }, [page, search]);

  useEffect(() => {
    fetchBlog();
  }, [fetchBlog]);

  const fetchSettings = useCallback(async () => {
    try {
      const response = await apanelService.getBlogSettings();
      const setting = response.data || response;
      setSettingsForm({
        home_limit: setting.home_limit || 3,
        recent_limit: setting.recent_limit || 5,
        home_icon: setting.home_icon || "book-open",
        tags: setting.tags || [],
        is_active: Boolean(setting.is_active ?? true),
        translations: Object.fromEntries(
          localeCodes.map((locale) => {
            const existing = setting.translations?.find(
              (item) => item.locale === locale,
            );
            return [
              locale,
              {
                ...emptySettings(localeCodes).translations[locale],
                ...(existing || {}),
              },
            ];
          }),
        ),
      });
    } catch (err) {
      setError(err?.message || "Failed to load blog settings.");
    }
  }, [localeCodes]);

  useEffect(() => {
    fetchSettings();
  }, [fetchSettings]);

  const publishedCount = useMemo(
    () => items.filter((item) => item.is_published).length,
    [items],
  );

  const startCreate = () => {
    setEditingRecord(null);
    setForm({
      ...emptyForm(localeCodes),
      published_at: new Date().toISOString().slice(0, 10),
      translations: Object.fromEntries(
        localeCodes.map((locale) => [locale, { ...emptyTranslation }]),
      ),
    });
    setActiveLocale(primaryLocale);
    setError("");
  };

  const startEdit = (record) => {
    setEditingRecord(record);
    setForm(fromRecord(record, localeCodes));
    setActiveLocale(primaryLocale);
    setError("");
  };

  const closeEditor = () => {
    setEditingRecord(null);
    setForm(emptyForm(localeCodes));
  };

  const setField = (field, value) => {
    setForm((current) => ({ ...current, [field]: value }));
  };

  const setTranslationField = (field, value) => {
    setForm((current) => ({
      ...current,
      translations: {
        ...current.translations,
        [activeLocale]: {
          ...current.translations[activeLocale],
          [field]: value,
        },
      },
    }));
  };

  const setSettingsField = (field, value) => {
    setSettingsForm((current) => ({ ...current, [field]: value }));
  };

  const setSettingsTranslationField = (field, value) => {
    setSettingsForm((current) => ({
      ...current,
      translations: {
        ...current.translations,
        [activeLocale]: {
          ...current.translations[activeLocale],
          [field]: value,
        },
      },
    }));
  };

  const handleEnglishTitleBlur = () => {
    if (!form.slug && form.translations[primaryLocale]?.title) {
      setField("slug", slugify(form.translations[primaryLocale].title));
    }
  };

  const uploadImage = async (event) => {
    const file = event.target.files?.[0];
    event.target.value = "";

    if (!file) return;

    try {
      setUploadingImage(true);
      setError("");
      const uploaded = await apanelService.uploadMedia(file, {
        title: file.name,
        alt_text: form.translations[primaryLocale]?.title || file.name,
        type: "image",
        is_public: "1",
      });

      if (!uploaded?.path) {
        throw new Error("Invalid upload response.");
      }

      setField("image", uploaded.path);
    } catch (err) {
      setError(err?.message || "Failed to upload image.");
    } finally {
      setUploadingImage(false);
    }
  };

  const uploadAuthorImage = async (event) => {
    const file = event.target.files?.[0];
    event.target.value = "";

    if (!file) return;

    try {
      setUploadingAuthorImage(true);
      setError("");
      const uploaded = await apanelService.uploadMedia(file, {
        title: file.name,
        alt_text: form.translations[primaryLocale]?.author || form.author || file.name,
        type: "image",
        is_public: "1",
      });

      if (!uploaded?.path) {
        throw new Error("Invalid upload response.");
      }

      setField("author_image", uploaded.path);
    } catch (err) {
      setError(err?.message || "Failed to upload author image.");
    } finally {
      setUploadingAuthorImage(false);
    }
  };

  const saveRecord = async (event) => {
    event.preventDefault();
    try {
      setSaving(true);
      setError("");
      const payload = toPayload(form, primaryLocale, localeCodes);
      if (editingRecord?.id) {
        await apanelService.update("blogs", editingRecord.id, payload);
      } else {
        await apanelService.create("blogs", payload);
      }
      closeEditor();
      fetchBlog();
    } catch (err) {
      setError(err?.message || "Failed to save blog post.");
    } finally {
      setSaving(false);
    }
  };

  const togglePublish = async (record) => {
    try {
      setError("");
      await apanelService.update("blogs", record.id, {
        ...toPayload({
          ...fromRecord(record, localeCodes),
          is_published: !record.is_published,
        }, primaryLocale, localeCodes),
      });
      fetchBlog();
    } catch (err) {
      setError(err?.message || "Failed to update publish status.");
    }
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    try {
      setDeletingId(pendingDelete.id);
      setError("");
      await apanelService.delete("blogs", pendingDelete.id);
      setPendingDelete(null);
      fetchBlog();
    } catch (err) {
      setError(err?.message || "Failed to delete blog post.");
    } finally {
      setDeletingId(null);
    }
  };

  const saveSettings = async (event) => {
    event.preventDefault();
    try {
      setSavingSettings(true);
      setError("");
      await apanelService.updateBlogSettings({
        ...settingsForm,
        tags: Array.isArray(settingsForm.tags) ? settingsForm.tags : [],
      });
      fetchSettings();
    } catch (err) {
      setError(err?.message || "Failed to save blog settings.");
    } finally {
      setSavingSettings(false);
    }
  };

  const currentTranslation = form.translations[activeLocale] || emptyTranslation;
  const currentSettingsTranslation =
    settingsForm.translations[activeLocale] ||
    emptySettings(localeCodes).translations[activeLocale] ||
    {};
  const editorOpen = editingRecord !== null || form.slug || form.published_at;

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            Blog CMS
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            Control homepage blog posts, the public blog listing, and blog detail
            pages.
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <button
            type="button"
            onClick={fetchBlog}
            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-navy text-xs font-extrabold hover:bg-gray-50 cursor-pointer"
          >
            <RefreshCw className="w-4 h-4" />
            Refresh
          </button>
          <button
            type="button"
            onClick={startCreate}
            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-extrabold hover:bg-primary-hover cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            Add Blog Post
          </button>
        </div>
      </div>

      {error && <FormError message={error} />}

      <div className="inline-flex rounded-2xl border border-gray-200 bg-white p-1 shadow-xs">
        {[
          { key: "items", label: "Blog Items" },
          { key: "settings", label: "Blog Settings" },
        ].map((tab) => (
          <button
            key={tab.key}
            type="button"
            onClick={() => setActiveTab(tab.key)}
            className={`px-4 py-2 rounded-xl text-xs font-extrabold cursor-pointer ${
              activeTab === tab.key
                ? "bg-primary text-white"
                : "text-gray-500 hover:text-navy"
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {activeTab === "items" && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {[
            { label: "Total Blog Posts", value: total, icon: Calendar },
            { label: "Published on page", value: publishedCount, icon: Eye },
            {
              label: "Hidden on page",
              value: items.length - publishedCount,
              icon: EyeOff,
            },
          ].map((stat) => {
            const Icon = stat.icon;
            return (
              <div
                key={stat.label}
                className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs flex items-center gap-4"
              >
                <div className="w-11 h-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                  <Icon className="w-5 h-5" />
                </div>
                <div>
                  <p className="text-[10px] uppercase tracking-wider font-black text-gray-400">
                    {stat.label}
                  </p>
                  <p className="text-lg font-extrabold text-navy mt-1">
                    {stat.value}
                  </p>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {activeTab === "settings" && (
        <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
          <div className="p-5 border-b border-gray-100">
            <h2 className="text-lg font-extrabold text-navy">
              Blog Settings
            </h2>
            <p className="text-xs font-semibold text-gray-400">
              Control labels, section headings, sidebar text, and item limits.
            </p>
          </div>
          <form onSubmit={saveSettings} className="p-5 space-y-5">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Home items limit
                </span>
                <input
                  type="number"
                  min="1"
                  max="12"
                  value={settingsForm.home_limit}
                  onChange={(event) =>
                    setSettingsField("home_limit", Number(event.target.value))
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Recent posts limit
                </span>
                <input
                  type="number"
                  min="1"
                  max="12"
                  value={settingsForm.recent_limit}
                  onChange={(event) =>
                    setSettingsField("recent_limit", Number(event.target.value))
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Settings status
                </span>
                <select
                  value={settingsForm.is_active ? "1" : "0"}
                  onChange={(event) =>
                    setSettingsField("is_active", event.target.value === "1")
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary bg-white"
                >
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Home Icon
                </span>
                <input
                  value={settingsForm.home_icon || ""}
                  onChange={(event) =>
                    setSettingsField("home_icon", event.target.value)
                  }
                  placeholder="book-open, pen-line, newspaper"
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                />
              </label>
            </div>

            <div className="flex flex-wrap gap-2">
              {localeCodes.map((locale) => (
                <button
                  type="button"
                  key={locale}
                  onClick={() => setActiveLocale(locale)}
                  className={`px-4 py-2 rounded-xl text-xs font-extrabold uppercase cursor-pointer ${
                    activeLocale === locale
                      ? "bg-primary text-white"
                      : "bg-gray-50 text-gray-500 hover:text-navy"
                  }`}
                >
                  {locale}
                </button>
              ))}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
              {[
                ["home_tag", "Home Tag"],
                ["home_title", "Home Title"],
                ["view_all_label", "View All Label"],
                ["read_more_label", "Read More Label"],
                ["search_title", "Search Title"],
                ["search_placeholder", "Search Placeholder"],
                ["categories_title", "Categories Title"],
                ["recent_title", "Recent Title"],
                ["tags_title", "Tags Title"],
                ["all_blog_label", "All Blog Label"],
                ["loading_label", "Loading Label"],
                ["no_results_label", "No Results Label"],
                ["clear_filters_label", "Clear Filters Label"],
                ["back_to_blog_label", "Back To Blog Label"],
                ["comments_label", "Comments Label"],
                ["reply_label", "Reply Label"],
                ["form_title", "Comment Form Title"],
                ["form_name_label", "Form Name Label"],
                ["form_email_label", "Form Email Label"],
                ["form_comment_label", "Form Comment Label"],
                ["form_submit_label", "Form Submit Label"],
                ["comment_login_title", "Login Required Title"],
                ["comment_login_text", "Login Required Text"],
                ["comment_login_action", "Login Button Label"],
                ["signed_in_as_label", "Signed-in-as Label"],
              ].map(([field, label]) => (
                <label key={field} className="space-y-1.5">
                  <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                    {label}
                  </span>
                  <input
                    value={currentSettingsTranslation[field] || ""}
                    onChange={(event) =>
                      setSettingsTranslationField(field, event.target.value)
                    }
                    className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                  />
                </label>
              ))}
            </div>

            <div className="flex justify-end">
              <button
                type="submit"
                disabled={savingSettings}
                className="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-primary text-white text-xs font-extrabold hover:bg-primary-hover disabled:opacity-60 cursor-pointer"
              >
                {savingSettings ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <Save className="w-4 h-4" />
                )}
                Save Settings
              </button>
            </div>
          </form>
        </section>
      )}

      {activeTab === "items" && editorOpen && (
        <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
          <div className="p-5 border-b border-gray-100 flex items-center justify-between gap-4">
            <div>
              <h2 className="text-lg font-extrabold text-navy">
                {editingRecord ? "Edit Blog Post" : "Create Blog Post"}
              </h2>
              <p className="text-xs font-semibold text-gray-400">
                Fill every language tab so the public website works in four
                languages.
              </p>
            </div>
            <button
              type="button"
              onClick={closeEditor}
              className="w-9 h-9 rounded-xl border border-gray-200 text-gray-500 hover:text-navy hover:bg-gray-50 flex items-center justify-center cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <form onSubmit={saveRecord} className="p-5 space-y-5">
            <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Slug
                </span>
                <input
                  value={form.slug}
                  onChange={(event) =>
                    setField("slug", slugify(event.target.value))
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                  required
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Category
                </span>
                <input
                  value={form.category}
                  onChange={(event) => setField("category", event.target.value)}
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary bg-white"
                  required
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Published Date
                </span>
                <input
                  type="date"
                  value={form.published_at}
                  onChange={(event) =>
                    setField("published_at", event.target.value)
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Status
                </span>
                <select
                  value={form.is_published ? "1" : "0"}
                  onChange={(event) =>
                    setField("is_published", event.target.value === "1")
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary bg-white"
                >
                  <option value="1">Published</option>
                  <option value="0">Hidden</option>
                </select>
              </label>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Default Author
                </span>
                <input
                  value={form.author}
                  onChange={(event) => setField("author", event.target.value)}
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                  placeholder="Author name"
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Author Image URL or storage path
                </span>
                <div className="flex gap-3">
                  <input
                    value={form.author_image}
                    onChange={(event) =>
                      setField("author_image", event.target.value)
                    }
                    className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                    placeholder="media/uploads/author-image.jpg"
                  />
                  <label className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-navy text-white text-xs font-extrabold hover:bg-primary transition-colors cursor-pointer shrink-0">
                    {uploadingAuthorImage ? (
                      <Loader2 className="w-4 h-4 animate-spin" />
                    ) : (
                      <Upload className="w-4 h-4" />
                    )}
                    Upload
                    <input
                      type="file"
                      accept="image/jpeg,image/png,image/webp"
                      className="hidden"
                      disabled={uploadingAuthorImage}
                      onChange={uploadAuthorImage}
                    />
                  </label>
                </div>
              </label>
            </div>

            <label className="space-y-1.5 block">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                Image URL or storage path
              </span>
              <div className="flex gap-3">
                <div className="w-11 h-11 rounded-xl bg-primary-light text-primary flex items-center justify-center shrink-0">
                  <ImageIcon className="w-4 h-4" />
                </div>
                <input
                  value={form.image}
                  onChange={(event) => setField("image", event.target.value)}
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                  placeholder="https://... or media/uploads/image.jpg"
                />
                <label className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-navy text-white text-xs font-extrabold hover:bg-primary transition-colors cursor-pointer shrink-0">
                  {uploadingImage ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <Upload className="w-4 h-4" />
                  )}
                  Upload
                  <input
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    className="hidden"
                    disabled={uploadingImage}
                    onChange={uploadImage}
                  />
                </label>
              </div>
            </label>

            <div className="flex flex-wrap gap-2">
              {localeCodes.map((locale) => (
                <button
                  type="button"
                  key={locale}
                  onClick={() => setActiveLocale(locale)}
                  className={`px-4 py-2 rounded-xl text-xs font-extrabold uppercase cursor-pointer ${
                    activeLocale === locale
                      ? "bg-primary text-white"
                      : "bg-gray-50 text-gray-500 hover:text-navy"
                  }`}
                >
                  {locale}
                </button>
              ))}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Title
                </span>
                <input
                  value={currentTranslation.title}
                  onChange={(event) =>
                    setTranslationField("title", event.target.value)
                  }
                  onBlur={
                    activeLocale === primaryLocale ? handleEnglishTitleBlur : undefined
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                  required={activeLocale === primaryLocale}
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Meta Title
                </span>
                <input
                  value={currentTranslation.meta_title}
                  onChange={(event) =>
                    setTranslationField("meta_title", event.target.value)
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                />
              </label>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Author
                </span>
                <input
                  value={currentTranslation.author || ""}
                  onChange={(event) =>
                    setTranslationField("author", event.target.value)
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                  Category Label
                </span>
                <input
                  value={currentTranslation.category_label || ""}
                  onChange={(event) =>
                    setTranslationField("category_label", event.target.value)
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                />
              </label>
            </div>

            <label className="space-y-1.5 block">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                Summary
              </span>
              <textarea
                value={currentTranslation.summary}
                onChange={(event) =>
                  setTranslationField("summary", event.target.value)
                }
                rows={3}
                className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
              />
            </label>

            <label className="space-y-1.5 block">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                Content
              </span>
              <textarea
                value={currentTranslation.content}
                onChange={(event) =>
                  setTranslationField("content", event.target.value)
                }
                rows={8}
                className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
                placeholder="Separate paragraphs with a blank line."
              />
            </label>

            <label className="space-y-1.5 block">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">
                Meta Description
              </span>
              <textarea
                value={currentTranslation.meta_description}
                onChange={(event) =>
                  setTranslationField("meta_description", event.target.value)
                }
                rows={2}
                className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-primary"
              />
            </label>

            <div className="flex justify-end">
              <button
                type="submit"
                disabled={saving}
                className="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-primary text-white text-xs font-extrabold hover:bg-primary-hover disabled:opacity-60 cursor-pointer"
              >
                {saving ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <Save className="w-4 h-4" />
                )}
                Save Blog Post
              </button>
            </div>
          </form>
        </section>
      )}

      {activeTab === "items" && (
        <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
          <div className="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center gap-3 justify-between">
            <div className="relative w-full md:max-w-sm">
              <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="search"
                value={search}
                onChange={(event) => {
                  setSearch(event.target.value);
                  setPage(1);
                }}
                placeholder="Search title, slug, category, or content"
                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy"
              />
            </div>
            <p className="text-xs font-bold text-gray-400">
              Page {page} of {lastPage}
            </p>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full min-w-240 text-start">
              <thead className="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-400 font-black">
                <tr>
                  <th className="px-5 py-3 text-start">Blog Post</th>
                  <th className="px-5 py-3 text-start">Category</th>
                  <th className="px-5 py-3 text-start">Date</th>
                  <th className="px-5 py-3 text-start">Status</th>
                  <th className="px-5 py-3 text-start">Views</th>
                  <th className="px-5 py-3 text-end">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  <tr>
                    <td colSpan="6" className="px-5 py-12 text-center">
                      <Loader2 className="w-7 h-7 animate-spin text-primary mx-auto" />
                    </td>
                  </tr>
                ) : items.length === 0 ? (
                  <tr>
                    <td
                      colSpan="6"
                      className="px-5 py-12 text-center text-sm font-bold text-gray-400"
                    >
                      No blog posts found.
                    </td>
                  </tr>
                ) : (
                  items.map((item) => {
                    const title =
                      item.translations?.find(
                        (translation) => translation.locale === primaryLocale,
                      )?.title ||
                      item.translations?.[0]?.title ||
                      item.slug;
                    return (
                      <tr key={item.id} className="hover:bg-gray-50/70">
                        <td className="px-5 py-4">
                          <div className="flex items-center gap-3">
                            {imagePreviewSrc(item.image) ? (
                              <img
                                src={imagePreviewSrc(item.image)}
                                alt={title}
                                className="w-16 h-12 rounded-xl object-cover bg-gray-100 border border-gray-100"
                                onError={(event) => {
                                  event.currentTarget.style.display = "none";
                                }}
                              />
                            ) : (
                              <div className="w-16 h-12 rounded-xl bg-gray-100 border border-gray-100 flex items-center justify-center text-gray-400">
                                <ImageIcon className="w-4 h-4" />
                              </div>
                            )}
                            <div>
                              <p className="text-sm font-extrabold text-navy line-clamp-1">
                                {title}
                              </p>
                              <p className="text-[11px] font-semibold text-gray-400">
                                /blog/{item.slug}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td className="px-5 py-4 text-xs font-bold text-gray-500">
                          {item.category}
                        </td>
                        <td className="px-5 py-4 text-xs font-semibold text-gray-500">
                          {formatDate(item.published_at, activeLocale || primaryLocale)}
                        </td>
                        <td className="px-5 py-4">
                          <span
                            className={`inline-flex px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider ${
                              item.is_published
                                ? "bg-emerald-50 text-emerald-700"
                                : "bg-gray-100 text-gray-500"
                            }`}
                          >
                            {item.is_published ? "Published" : "Hidden"}
                          </span>
                        </td>
                        <td className="px-5 py-4 text-xs font-semibold text-gray-500">
                          {item.views_count || 0}
                        </td>
                        <td className="px-5 py-4">
                          <div className="flex items-center justify-end gap-2">
                            <button
                              type="button"
                              onClick={() => startEdit(item)}
                              className="w-9 h-9 rounded-xl border border-gray-200 text-navy hover:bg-gray-50 flex items-center justify-center cursor-pointer"
                            >
                              <Edit3 className="w-4 h-4" />
                            </button>
                            <button
                              type="button"
                              onClick={() => togglePublish(item)}
                              className="w-9 h-9 rounded-xl border border-gray-200 text-primary hover:bg-primary/5 flex items-center justify-center cursor-pointer"
                            >
                              {item.is_published ? (
                                <EyeOff className="w-4 h-4" />
                              ) : (
                                <Eye className="w-4 h-4" />
                              )}
                            </button>
                            <button
                              type="button"
                              onClick={() => setPendingDelete(item)}
                              disabled={deletingId === item.id}
                              className="w-9 h-9 rounded-xl border border-rose-100 text-rose-600 hover:bg-rose-50 disabled:opacity-60 flex items-center justify-center cursor-pointer"
                            >
                              {deletingId === item.id ? (
                                <Loader2 className="w-4 h-4 animate-spin" />
                              ) : (
                                <Trash2 className="w-4 h-4" />
                              )}
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>

          <div className="p-5 border-t border-gray-100 flex items-center justify-between">
            <button
              type="button"
              onClick={() => setPage((current) => Math.max(1, current - 1))}
              disabled={page <= 1}
              className="px-4 py-2 rounded-xl border border-gray-200 text-xs font-extrabold text-navy disabled:opacity-40 cursor-pointer"
            >
              Previous
            </button>
            <button
              type="button"
              onClick={() =>
                setPage((current) => Math.min(lastPage, current + 1))
              }
              disabled={page >= lastPage}
              className="px-4 py-2 rounded-xl border border-gray-200 text-xs font-extrabold text-navy disabled:opacity-40 cursor-pointer"
            >
              Next
            </button>
          </div>
        </section>
      )}
      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title="Delete blog post?"
        message={`This will permanently delete ${pendingDelete?.slug || "this blog post"}. This action cannot be undone.`}
        confirmText={deletingId ? "Deleting..." : "Delete"}
        cancelText="Cancel"
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
