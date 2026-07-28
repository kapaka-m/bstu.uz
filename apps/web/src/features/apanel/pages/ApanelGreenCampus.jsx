import { useCallback, useEffect, useMemo, useState } from "react";
import {
  BarChart3,
  Edit3,
  Eye,
  EyeOff,
  Image as ImageIcon,
  Loader2,
  Plus,
  RefreshCw,
  Save,
  Trash2,
  Upload,
  X,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { publicAssetUrl } from "../../../lib/api";
import ConfirmDialog from "../components/ConfirmDialog";
import { useApanelLocaleCodes } from "../utils/locales";

const emptyArticleTranslation = {
  title: "",
  category: "",
  excerpt: "",
  content: "",
  author: "",
};

const createEmptyArticleForm = (localeCodes) => ({
  slug: "",
  category: "",
  image: "",
  gallery: [],
  views: 0,
  published_at: "",
  is_published: true,
  sort_order: 0,
  translations: Object.fromEntries(
    localeCodes.map((locale) => [locale, { ...emptyArticleTranslation }]),
  ),
});

const emptyStatTranslation = { value: "", label: "" };
const createEmptyStatForm = (localeCodes) => ({
  icon: "leaf",
  sort_order: 0,
  translations: Object.fromEntries(
    localeCodes.map((locale) => [locale, { ...emptyStatTranslation }]),
  ),
});

const emptySettingsTranslation = {
  home_tag: "",
  home_title: "",
  view_all_label: "",
  read_more_label: "",
  search_title: "",
  search_placeholder: "",
  categories_title: "",
  recent_title: "",
  all_label: "",
  no_results_label: "",
  callout_title: "",
  callout_description: "",
  callout_cta_label: "",
  callout_email: "",
  views_label: "",
  gallery_label: "",
  related_label: "",
  close_viewer_label: "",
  previous_image_label: "",
  next_image_label: "",
  category_labels: {},
};

const createEmptySettings = (localeCodes) => ({
  home_limit: 3,
  recent_limit: 4,
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

function mediaPreviewSrc(image) {
  return publicAssetUrl(image);
}

function translationsFromRecord(record, emptyTranslation, localeCodes) {
  const translations = Array.isArray(record.translations)
    ? record.translations
    : Object.entries(record.translations || {}).map(([locale, values]) => ({
        locale,
        ...(values || {}),
      }));

  return Object.fromEntries(
    localeCodes.map((locale) => {
      const existing = translations.find((item) => item.locale === locale);
      return [locale, { ...emptyTranslation, ...(existing || {}) }];
    }),
  );
}

function articleFromRecord(record, localeCodes) {
  return {
    slug: record.slug || "",
    category: record.category || "",
    image: record.image || "",
    gallery: Array.isArray(record.gallery) ? record.gallery : [],
    views: Number(record.views || 0),
    published_at: toDateInput(record.published_at || record.created_at),
    is_published: Boolean(record.is_published ?? true),
    sort_order: Number(record.sort_order || 0),
    translations: translationsFromRecord(record, emptyArticleTranslation, localeCodes),
  };
}

function statFromRecord(record, localeCodes) {
  return {
    icon: record.icon || "leaf",
    sort_order: Number(record.sort_order || 0),
    translations: translationsFromRecord(record, emptyStatTranslation, localeCodes),
  };
}

function articlePayload(form, localeCodes, primaryLocale) {
  const fallback = form.translations[primaryLocale] || emptyArticleTranslation;
  return {
    slug: form.slug,
    category: form.category,
    image: form.image || null,
    gallery: form.gallery.filter(Boolean),
    views: Number(form.views || 0),
    published_at: form.published_at || null,
    is_published: Boolean(form.is_published),
    sort_order: Number(form.sort_order || 0),
    translations: Object.fromEntries(
      localeCodes.map((locale) => {
        const current = form.translations[locale] || emptyArticleTranslation;
        return [
          locale,
          {
            title: current.title || fallback.title || form.slug,
            category: current.category || fallback.category || form.category,
            excerpt: current.excerpt || fallback.excerpt || "",
            content: current.content || fallback.content || "",
            author: current.author || fallback.author || "",
          },
        ];
      }),
    ),
  };
}

function statPayload(form, localeCodes, primaryLocale) {
  const fallback = form.translations[primaryLocale] || emptyStatTranslation;
  return {
    icon: form.icon,
    sort_order: Number(form.sort_order || 0),
    translations: Object.fromEntries(
      localeCodes.map((locale) => {
        const current = form.translations[locale] || emptyStatTranslation;
        return [
          locale,
          {
            value: current.value || fallback.value || "",
            label: current.label || fallback.label || "",
          },
        ];
      }),
    ),
  };
}

export default function ApanelGreenCampus() {
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || "";
  const [activeTab, setActiveTab] = useState("articles");
  const [activeLocale, setActiveLocale] = useState("");
  const [articles, setArticles] = useState([]);
  const [stats, setStats] = useState([]);
  const [articleForm, setArticleForm] = useState(() => createEmptyArticleForm([]));
  const [statForm, setStatForm] = useState(() => createEmptyStatForm([]));
  const [settingsForm, setSettingsForm] = useState(() => createEmptySettings([]));
  const [editingArticle, setEditingArticle] = useState(null);
  const [editingStat, setEditingStat] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [uploading, setUploading] = useState(false);
  const [pendingDelete, setPendingDelete] = useState(null);

  const fetchAll = useCallback(async () => {
    if (!primaryLocale) return;

    try {
      setLoading(true);
      setError("");
      const [articlesPage, statsPage, settings] = await Promise.all([
        apanelService.listPage("green-campus-articles", {
          per_page: 100,
          sort_by: "sort_order",
          sort_dir: "asc",
        }),
        apanelService.listPage("green-campus-stats", {
          per_page: 100,
          sort_by: "sort_order",
          sort_dir: "asc",
        }),
        apanelService.getGreenCampusSettings(),
      ]);

      const setting = settings.data || settings;
      setArticles(articlesPage.items);
      setStats(statsPage.items);
      setSettingsForm({
        home_limit: setting.home_limit || 3,
        recent_limit: setting.recent_limit || 4,
        is_active: Boolean(setting.is_active ?? true),
        translations: Object.fromEntries(
          localeCodes.map((locale) => {
            const existing = setting.translations?.find((item) => item.locale === locale);
            return [
              locale,
              {
                ...emptySettingsTranslation,
                ...(existing || {}),
                category_labels: existing?.category_labels || {},
              },
            ];
          }),
        ),
      });
    } catch (err) {
      setError(err?.message || "Failed to load Green Campus CMS.");
    } finally {
      setLoading(false);
    }
  }, [localeCodes, primaryLocale]);

  useEffect(() => {
    fetchAll();
  }, [fetchAll]);

  useEffect(() => {
    if (!primaryLocale) return;

    setActiveLocale((current) =>
      current && localeCodes.includes(current) ? current : primaryLocale,
    );
    setArticleForm((current) => ({
      ...current,
      translations: {
        ...createEmptyArticleForm(localeCodes).translations,
        ...Object.fromEntries(
          Object.entries(current.translations || {}).filter(([locale]) =>
            localeCodes.includes(locale),
          ),
        ),
      },
    }));
    setStatForm((current) => ({
      ...current,
      translations: {
        ...createEmptyStatForm(localeCodes).translations,
        ...Object.fromEntries(
          Object.entries(current.translations || {}).filter(([locale]) =>
            localeCodes.includes(locale),
          ),
        ),
      },
    }));
    setSettingsForm((current) => ({
      ...current,
      translations: {
        ...createEmptySettings(localeCodes).translations,
        ...Object.fromEntries(
          Object.entries(current.translations || {}).filter(([locale]) =>
            localeCodes.includes(locale),
          ),
        ),
      },
    }));
  }, [localeCodes, primaryLocale]);

  const categories = useMemo(() => {
    const values = new Set(articles.map((item) => item.category).filter(Boolean));
    if (articleForm.category) values.add(articleForm.category);
    Object.keys(settingsForm.translations[activeLocale]?.category_labels || {}).forEach((key) => values.add(key));
    return [...values];
  }, [activeLocale, articleForm.category, articles, settingsForm.translations]);

  const currentArticleTranslation =
    articleForm.translations[activeLocale] || emptyArticleTranslation;
  const currentStatTranslation =
    statForm.translations[activeLocale] || emptyStatTranslation;
  const currentSettingsTranslation =
    settingsForm.translations[activeLocale] || emptySettingsTranslation;

  const startCreateArticle = () => {
    setEditingArticle(null);
    setArticleForm({
      ...createEmptyArticleForm(localeCodes),
      published_at: new Date().toISOString().slice(0, 10),
    });
    setActiveLocale(primaryLocale);
  };

  const startEditArticle = (record) => {
    setEditingArticle(record);
    setArticleForm(articleFromRecord(record, localeCodes));
    setActiveLocale(primaryLocale);
  };

  const startCreateStat = () => {
    setEditingStat(null);
    setStatForm({
      ...createEmptyStatForm(localeCodes),
      sort_order: stats.length + 1,
    });
    setActiveLocale(primaryLocale);
  };

  const startEditStat = (record) => {
    setEditingStat(record);
    setStatForm(statFromRecord(record, localeCodes));
    setActiveLocale(primaryLocale);
  };

  const setArticleField = (field, value) => {
    setArticleForm((current) => ({ ...current, [field]: value }));
  };

  const setArticleTranslationField = (field, value) => {
    setArticleForm((current) => ({
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

  const setStatField = (field, value) => {
    setStatForm((current) => ({ ...current, [field]: value }));
  };

  const setStatTranslationField = (field, value) => {
    setStatForm((current) => ({
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

  const uploadArticleImage = async (event, galleryIndex = null) => {
    const file = event.target.files?.[0];
    event.target.value = "";
    if (!file) return;

    try {
      setUploading(true);
      const uploaded = await apanelService.uploadMedia(file, {
        title: file.name,
        alt_text: articleForm.translations[primaryLocale]?.title || file.name,
        type: "image",
        is_public: "1",
      });
      if (!uploaded?.path) throw new Error("Invalid upload response.");

      if (galleryIndex === null) {
        setArticleField("image", uploaded.path);
      } else {
        setArticleForm((current) => {
          const gallery = [...current.gallery];
          gallery[galleryIndex] = uploaded.path;
          return { ...current, gallery };
        });
      }
    } catch (err) {
      setError(err?.message || "Failed to upload image.");
    } finally {
      setUploading(false);
    }
  };

  const saveArticle = async (event) => {
    event.preventDefault();
    try {
      setSaving(true);
      setError("");
      const payload = articlePayload(articleForm, localeCodes, primaryLocale);
      if (editingArticle?.id) {
        await apanelService.update("green-campus-articles", editingArticle.id, payload);
      } else {
        await apanelService.create("green-campus-articles", payload);
      }
      setEditingArticle(null);
      setArticleForm(createEmptyArticleForm(localeCodes));
      fetchAll();
    } catch (err) {
      setError(err?.message || "Failed to save Green Campus article.");
    } finally {
      setSaving(false);
    }
  };

  const saveStat = async (event) => {
    event.preventDefault();
    try {
      setSaving(true);
      setError("");
      const payload = statPayload(statForm, localeCodes, primaryLocale);
      if (editingStat?.id) {
        await apanelService.update("green-campus-stats", editingStat.id, payload);
      } else {
        await apanelService.create("green-campus-stats", payload);
      }
      setEditingStat(null);
      setStatForm(createEmptyStatForm(localeCodes));
      fetchAll();
    } catch (err) {
      setError(err?.message || "Failed to save Green Campus stat.");
    } finally {
      setSaving(false);
    }
  };

  const saveSettings = async (event) => {
    event.preventDefault();
    try {
      setSaving(true);
      setError("");
      await apanelService.updateGreenCampusSettings(settingsForm);
      fetchAll();
    } catch (err) {
      setError(err?.message || "Failed to save Green Campus settings.");
    } finally {
      setSaving(false);
    }
  };

  const toggleArticle = async (record) => {
    await apanelService.update("green-campus-articles", record.id, {
      ...articlePayload(articleFromRecord(record, localeCodes), localeCodes, primaryLocale),
      is_published: !record.is_published,
    });
    fetchAll();
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    await apanelService.delete(pendingDelete.resource, pendingDelete.record.id);
    setPendingDelete(null);
    fetchAll();
  };

  const articleEditorOpen = editingArticle !== null || articleForm.slug || articleForm.published_at;
  const statEditorOpen = editingStat !== null || statForm.sort_order || statForm.translations[primaryLocale]?.value;

  return (
    <>
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            Green Campus CMS
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            Control homepage sustainability cards, listing page, details page, stats, and labels.
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <button
            type="button"
            onClick={fetchAll}
            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-navy text-xs font-extrabold hover:bg-gray-50 cursor-pointer"
          >
            <RefreshCw className="w-4 h-4" />
            Refresh
          </button>
          {activeTab === "articles" && (
            <button
              type="button"
              onClick={startCreateArticle}
              className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold hover:bg-emerald-700 cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              Add Initiative
            </button>
          )}
          {activeTab === "stats" && (
            <button
              type="button"
              onClick={startCreateStat}
              className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold hover:bg-emerald-700 cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              Add Stat
            </button>
          )}
        </div>
      </div>

      {error && <FormError message={error} />}

      <div className="inline-flex rounded-2xl border border-gray-200 bg-white p-1 shadow-xs">
        {[
          ["articles", "Initiatives"],
          ["stats", "Stats"],
          ["settings", "Settings"],
        ].map(([key, label]) => (
          <button
            key={key}
            type="button"
            onClick={() => setActiveTab(key)}
            className={`px-4 py-2 rounded-xl text-xs font-extrabold cursor-pointer ${
              activeTab === key ? "bg-emerald-600 text-white" : "text-gray-500 hover:text-navy"
            }`}
          >
            {label}
          </button>
        ))}
      </div>

      {loading ? (
        <div className="bg-white border border-gray-100 rounded-3xl p-10 text-center">
          <Loader2 className="w-6 h-6 animate-spin text-emerald-600 mx-auto" />
        </div>
      ) : null}

      {activeTab === "articles" && (
        <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <section className="xl:col-span-2 bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
            <div className="p-5 border-b border-gray-100">
              <h2 className="text-lg font-extrabold text-navy">Green Campus Initiatives</h2>
            </div>
            <div className="divide-y divide-gray-100">
              {articles.map((item) => {
                const en = item.translations?.find((tr) => tr.locale === "en") || {};
                return (
                  <div key={item.id} className="p-5 flex flex-col md:flex-row gap-4 md:items-center">
                    <div className="w-full md:w-32 aspect-video rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 shrink-0">
                      {item.image ? (
                        <img src={mediaPreviewSrc(item.image)} alt={en.title || item.slug} className="w-full h-full object-cover" />
                      ) : (
                        <div className="w-full h-full flex items-center justify-center text-gray-300">
                          <ImageIcon className="w-6 h-6" />
                        </div>
                      )}
                    </div>
                    <div className="grow min-w-0">
                      <div className="flex flex-wrap gap-2 mb-2">
                        <span className="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase">
                          {item.category}
                        </span>
                        <span className={`px-2.5 py-1 rounded-lg text-[10px] font-black uppercase ${item.is_published ? "bg-green-50 text-green-700" : "bg-gray-100 text-gray-500"}`}>
                          {item.is_published ? "Published" : "Hidden"}
                        </span>
                      </div>
                      <h3 className="text-sm font-extrabold text-navy line-clamp-1">{en.title || item.slug}</h3>
                      <p className="text-xs font-semibold text-gray-400 mt-1 line-clamp-2">{en.excerpt || ""}</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <button type="button" onClick={() => toggleArticle(item)} className="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-navy cursor-pointer">
                        {item.is_published ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                      </button>
                      <button type="button" onClick={() => startEditArticle(item)} className="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-navy cursor-pointer">
                        <Edit3 className="w-4 h-4" />
                      </button>
                      <button type="button" onClick={() => setPendingDelete({ resource: "green-campus-articles", record: item, label: "initiative" })} className="p-2 rounded-xl border border-rose-100 text-rose-500 hover:bg-rose-50 cursor-pointer">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          </section>

          {articleEditorOpen && (
            <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
              <div className="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 className="text-lg font-extrabold text-navy">{editingArticle ? "Edit Initiative" : "Create Initiative"}</h2>
                <button type="button" onClick={() => { setEditingArticle(null); setArticleForm(createEmptyArticleForm(localeCodes)); }} className="text-gray-400 hover:text-navy cursor-pointer">
                  <X className="w-5 h-5" />
                </button>
              </div>
              <form onSubmit={saveArticle} className="p-5 space-y-4">
                <LocaleTabs activeLocale={activeLocale} locales={localeCodes} onChange={setActiveLocale} />
                <Input label="Slug" value={articleForm.slug} onChange={(value) => setArticleField("slug", value)} />
                <Input label="Category Key" value={articleForm.category} onChange={(value) => setArticleField("category", slugify(value))} />
                <Input label="Published Date" type="date" value={articleForm.published_at} onChange={(value) => setArticleField("published_at", value)} />
                <Input label="Sort Order" type="number" value={articleForm.sort_order} onChange={(value) => setArticleField("sort_order", value)} />
                <Input label="Views" type="number" value={articleForm.views} onChange={(value) => setArticleField("views", value)} />
                <Checkbox label="Published" checked={articleForm.is_published} onChange={(value) => setArticleField("is_published", value)} />

                <UploadField
                  label="Main Image"
                  value={articleForm.image}
                  preview={mediaPreviewSrc(articleForm.image)}
                  uploading={uploading}
                  onValueChange={(value) => setArticleField("image", value)}
                  onUpload={(event) => uploadArticleImage(event)}
                />

                <GalleryEditor
                  gallery={articleForm.gallery}
                  uploading={uploading}
                  onChange={(gallery) => setArticleField("gallery", gallery)}
                  onUpload={uploadArticleImage}
                />

                <Input label="Title" value={currentArticleTranslation.title} onChange={(value) => {
                  setArticleTranslationField("title", value);
                  if (activeLocale === primaryLocale && !articleForm.slug) setArticleField("slug", slugify(value));
                }} />
                <Input label="Category Label" value={currentArticleTranslation.category} onChange={(value) => setArticleTranslationField("category", value)} />
                <Input label="Author" value={currentArticleTranslation.author} onChange={(value) => setArticleTranslationField("author", value)} />
                <Textarea label="Excerpt" value={currentArticleTranslation.excerpt} onChange={(value) => setArticleTranslationField("excerpt", value)} />
                <Textarea label="Content" value={currentArticleTranslation.content} rows={8} onChange={(value) => setArticleTranslationField("content", value)} />

                <button type="submit" disabled={saving} className="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white px-5 py-3 rounded-xl text-xs font-extrabold cursor-pointer">
                  <Save className="w-4 h-4" />
                  Save Initiative
                </button>
              </form>
            </section>
          )}
        </div>
      )}

      {activeTab === "stats" && (
        <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <section className="xl:col-span-2 bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
            <div className="p-5 border-b border-gray-100">
              <h2 className="text-lg font-extrabold text-navy">Sustainability Stats</h2>
            </div>
            <div className="divide-y divide-gray-100">
              {stats.map((item) => {
                const en = item.translations?.find((tr) => tr.locale === "en") || {};
                return (
                  <div key={item.id} className="p-5 flex items-center gap-4">
                    <div className="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                      <BarChart3 className="w-5 h-5" />
                    </div>
                    <div className="grow">
                      <h3 className="text-sm font-extrabold text-navy">{en.value}</h3>
                      <p className="text-xs font-semibold text-gray-400">{en.label}</p>
                    </div>
                    <button type="button" onClick={() => startEditStat(item)} className="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-navy cursor-pointer">
                      <Edit3 className="w-4 h-4" />
                    </button>
                    <button type="button" onClick={() => setPendingDelete({ resource: "green-campus-stats", record: item, label: "stat" })} className="p-2 rounded-xl border border-rose-100 text-rose-500 hover:bg-rose-50 cursor-pointer">
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                );
              })}
            </div>
          </section>

          {statEditorOpen && (
            <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
              <div className="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 className="text-lg font-extrabold text-navy">{editingStat ? "Edit Stat" : "Create Stat"}</h2>
                <button type="button" onClick={() => { setEditingStat(null); setStatForm(createEmptyStatForm(localeCodes)); }} className="text-gray-400 hover:text-navy cursor-pointer">
                  <X className="w-5 h-5" />
                </button>
              </div>
              <form onSubmit={saveStat} className="p-5 space-y-4">
                <LocaleTabs activeLocale={activeLocale} locales={localeCodes} onChange={setActiveLocale} />
                <Input label="Icon Key" value={statForm.icon} onChange={(value) => setStatField("icon", value)} />
                <Input label="Sort Order" type="number" value={statForm.sort_order} onChange={(value) => setStatField("sort_order", value)} />
                <Input label="Value" value={currentStatTranslation.value} onChange={(value) => setStatTranslationField("value", value)} />
                <Input label="Label" value={currentStatTranslation.label} onChange={(value) => setStatTranslationField("label", value)} />
                <button type="submit" disabled={saving} className="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white px-5 py-3 rounded-xl text-xs font-extrabold cursor-pointer">
                  <Save className="w-4 h-4" />
                  Save Stat
                </button>
              </form>
            </section>
          )}
        </div>
      )}

      {activeTab === "settings" && (
        <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
          <div className="p-5 border-b border-gray-100">
            <h2 className="text-lg font-extrabold text-navy">Green Campus Settings</h2>
            <p className="text-xs font-semibold text-gray-400">Control all visible labels for the public section, listing page, and detail page.</p>
          </div>
          <form onSubmit={saveSettings} className="p-5 space-y-5">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Input label="Home Limit" type="number" value={settingsForm.home_limit} onChange={(value) => setSettingsField("home_limit", Number(value))} />
              <Input label="Recent Limit" type="number" value={settingsForm.recent_limit} onChange={(value) => setSettingsField("recent_limit", Number(value))} />
              <Checkbox label="Active" checked={settingsForm.is_active} onChange={(value) => setSettingsField("is_active", value)} />
            </div>
            <LocaleTabs activeLocale={activeLocale} locales={localeCodes} onChange={setActiveLocale} />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {[
                ["home_tag", "Home tag"],
                ["home_title", "Home title"],
                ["view_all_label", "View all label"],
                ["read_more_label", "Read more label"],
                ["search_title", "Search title"],
                ["search_placeholder", "Search placeholder"],
                ["categories_title", "Categories title"],
                ["recent_title", "Recent title"],
                ["all_label", "All label"],
                ["no_results_label", "No results label"],
                ["callout_title", "Callout title"],
                ["callout_cta_label", "Callout CTA label"],
                ["callout_email", "Callout email"],
                ["views_label", "Views label"],
                ["gallery_label", "Gallery label"],
                ["related_label", "Related label"],
                ["close_viewer_label", "Close viewer label"],
                ["previous_image_label", "Previous image label"],
                ["next_image_label", "Next image label"],
              ].map(([field, label]) => (
                <Input
                  key={field}
                  label={label}
                  value={currentSettingsTranslation[field] || ""}
                  onChange={(value) => setSettingsTranslationField(field, value)}
                />
              ))}
              <Textarea
                label="Callout description"
                value={currentSettingsTranslation.callout_description || ""}
                onChange={(value) => setSettingsTranslationField("callout_description", value)}
              />
            </div>
            <div className="border border-gray-100 rounded-2xl p-4">
              <h3 className="text-sm font-extrabold text-navy mb-3">Category labels</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                {categories.map((category) => (
                  <Input
                    key={category}
                    label={category}
                    value={currentSettingsTranslation.category_labels?.[category] || ""}
                    onChange={(value) =>
                      setSettingsTranslationField("category_labels", {
                        ...(currentSettingsTranslation.category_labels || {}),
                        [category]: value,
                      })
                    }
                  />
                ))}
              </div>
            </div>
            <button type="submit" disabled={saving} className="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white px-5 py-3 rounded-xl text-xs font-extrabold cursor-pointer">
              <Save className="w-4 h-4" />
              Save Settings
            </button>
          </form>
        </section>
      )}
    </div>
    <ConfirmDialog
      isOpen={Boolean(pendingDelete)}
      title={`Delete ${pendingDelete?.label || "item"}?`}
      message={`This will permanently delete ${pendingDelete?.record?.slug || pendingDelete?.record?.key || "this item"}. This action cannot be undone.`}
      onConfirm={confirmDelete}
      onCancel={() => setPendingDelete(null)}
    />
    </>
  );
}

function LocaleTabs({ activeLocale, locales, onChange }) {
  return (
    <div className="flex flex-wrap gap-2">
      {locales.map((locale) => (
        <button
          key={locale}
          type="button"
          onClick={() => onChange(locale)}
          className={`px-3 py-2 rounded-xl text-xs font-extrabold uppercase cursor-pointer ${
            activeLocale === locale
              ? "bg-emerald-600 text-white"
              : "bg-gray-50 text-gray-500 border border-gray-100"
          }`}
        >
          {locale}
        </button>
      ))}
    </div>
  );
}

function Input({ label, value, onChange, type = "text" }) {
  return (
    <label className="space-y-1.5 block">
      <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">{label}</span>
      <input
        type={type}
        value={value ?? ""}
        onChange={(event) => onChange(event.target.value)}
        className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-emerald-600 bg-white"
      />
    </label>
  );
}

function Textarea({ label, value, onChange, rows = 4 }) {
  return (
    <label className="space-y-1.5 block md:col-span-2">
      <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">{label}</span>
      <textarea
        value={value ?? ""}
        rows={rows}
        onChange={(event) => onChange(event.target.value)}
        className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-emerald-600 bg-white resize-y"
      />
    </label>
  );
}

function Checkbox({ label, checked, onChange }) {
  return (
    <label className="space-y-1.5 block">
      <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">{label}</span>
      <span className="flex min-h-10.5 items-center gap-2 rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-bold text-navy bg-white">
        <input
          type="checkbox"
          checked={Boolean(checked)}
          onChange={(event) => onChange(event.target.checked)}
          className="h-4 w-4 accent-emerald-600"
        />
        <span>{checked ? "Enabled" : "Disabled"}</span>
      </span>
    </label>
  );
}

function UploadField({ label, value, preview, uploading, onValueChange, onUpload }) {
  return (
    <div className="space-y-2">
      <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">{label}</span>
      {preview && (
        <img src={preview} alt="" className="w-full aspect-video rounded-xl object-cover bg-gray-50 border border-gray-100" />
      )}
      <div className="flex gap-2">
        <input
          value={value || ""}
          onChange={(event) => onValueChange(event.target.value)}
          className="min-w-0 grow rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-emerald-600 bg-white"
        />
        <label className="inline-flex items-center justify-center gap-2 px-3 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-extrabold cursor-pointer border border-emerald-100">
          {uploading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Upload className="w-4 h-4" />}
          <input type="file" accept="image/*" className="hidden" onChange={onUpload} />
        </label>
      </div>
    </div>
  );
}

function GalleryEditor({ gallery, uploading, onChange, onUpload }) {
  return (
    <div className="space-y-2">
      <div className="flex items-center justify-between">
        <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Gallery</span>
        <button
          type="button"
          onClick={() => onChange([...gallery, ""])}
          className="text-xs font-extrabold text-emerald-700 hover:underline cursor-pointer"
        >
          Add image
        </button>
      </div>
      <div className="space-y-2">
        {gallery.map((image, index) => (
          <div key={index} className="flex gap-2">
            <input
              value={image || ""}
              onChange={(event) => {
                const next = [...gallery];
                next[index] = event.target.value;
                onChange(next);
              }}
              className="min-w-0 grow rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy focus:outline-none focus:border-emerald-600 bg-white"
            />
            <label className="inline-flex items-center justify-center px-3 rounded-xl bg-emerald-50 text-emerald-700 cursor-pointer border border-emerald-100">
              {uploading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Upload className="w-4 h-4" />}
              <input type="file" accept="image/*" className="hidden" onChange={(event) => onUpload(event, index)} />
            </label>
            <button
              type="button"
              onClick={() => onChange(gallery.filter((_, itemIndex) => itemIndex !== index))}
              className="px-3 rounded-xl border border-rose-100 text-rose-500 hover:bg-rose-50 cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}
