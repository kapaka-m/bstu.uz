import React, { useCallback, useEffect, useMemo, useState } from "react";
import {
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
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { publicAssetUrl } from "../../../lib/api";
import ConfirmDialog from "../components/ConfirmDialog";

const locales = ["en", "uz", "ru", "ar"];
const localeNames = {
  en: "English",
  uz: "Uzbek",
  ru: "Russian",
  ar: "Arabic",
};
const emptyTranslation = {
  title: "",
  category: "",
  description: "",
};

const emptyForm = {
  slug: "",
  url: "",
  thumbnail: "",
  video_type: "youtube",
  youtube_id: "",
  duration: "",
  views_count: 0,
  likes_count: 0,
  published_at: "",
  is_active: true,
  sort_order: 0,
  translations: Object.fromEntries(locales.map((locale) => [locale, emptyTranslation])),
};

const emptySettings = {
  home_limit: 4,
  subscriber_count: 0,
  youtube_channel_url: "",
  is_active: true,
  translations: Object.fromEntries(
    locales.map((locale) => [
      locale,
      {
        home_tag: "",
        home_title: "",
        home_subtitle: "",
        view_all_label: "",
        recommended_label: "",
        videos_label: "",
        description_title: "",
        show_more_label: "",
        show_less_label: "",
        like_label: "",
        liked_label: "",
        share_label: "",
        subscribe_label: "",
        subscribed_label: "",
        subscribers_label: "",
        views_label: "",
        channel_name: "",
        link_copied_label: "",
        no_videos_label: "",
        comments_label: "",
        reply_label: "",
        form_title: "",
        form_comment_label: "",
        form_submit_label: "",
        sign_in_title: "",
        sign_in_text: "",
        sign_in_action: "",
        signed_in_as_label: "",
        category_label: "",
        duration_label: "",
        platform_label: "",
        local_label: "",
        youtube_label: "",
        playing_label: "",
        verified_channel_label: "",
        category_labels: {},
      },
    ]),
  ),
};
const MAX_VIDEO_UPLOAD_BYTES = 200 * 1024 * 1024;

function slugify(value) {
  return value
    .toString()
    .toLowerCase()
    .trim()
    .replace(/['"]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function youtubeIdFromUrl(value) {
  const text = String(value || "");
  const match = text.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&?/]+)/i);
  return match?.[1] || "";
}

function toDateInput(value) {
  if (!value) return "";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "";
  return date.toISOString().slice(0, 10);
}

function mediaPreviewSrc(path) {
  return publicAssetUrl(path);
}

function findTranslation(translations, locale) {
  if (Array.isArray(translations)) {
    return translations.find((item) => item.locale === locale);
  }

  return translations?.[locale] || null;
}

function fromRecord(record) {
  const translations = Object.fromEntries(
    locales.map((locale) => {
      const existing = findTranslation(record.translations, locale);
      return [
        locale,
        {
          title: existing?.title || "",
          category: existing?.category || "",
          description: existing?.description || "",
        },
      ];
    }),
  );

  return {
    slug: record.slug || "",
    url: record.url || "",
    thumbnail: record.thumbnail || "",
    video_type: record.video_type || "youtube",
    youtube_id: record.youtube_id || "",
    duration: record.duration || "",
    views_count: Number(record.views_count || 0),
    likes_count: Number(record.likes_count || 0),
    published_at: toDateInput(record.published_at),
    is_active: Boolean(record.is_active),
    sort_order: Number(record.sort_order || 0),
    translations,
  };
}

function toPayload(form) {
  const fallback = form.translations.en || emptyTranslation;
  const translations = Object.fromEntries(
    locales.map((locale) => {
      const current = form.translations[locale] || emptyTranslation;
      return [
        locale,
        {
          title: current.title || fallback.title || form.slug,
          category: current.category || fallback.category || form.video_type,
          description: current.description || fallback.description || "",
        },
      ];
    }),
  );

  return {
    slug: form.slug,
    url: form.url,
    thumbnail: form.thumbnail || null,
    video_type: form.video_type,
    youtube_id: form.video_type === "youtube" ? form.youtube_id || youtubeIdFromUrl(form.url) : null,
    duration: form.duration || null,
    views_count: Number(form.views_count || 0),
    likes_count: Number(form.likes_count || 0),
    published_at: form.published_at || null,
    is_active: Boolean(form.is_active),
    sort_order: Number(form.sort_order || 0),
    translations,
  };
}

export default function ApanelVideoBdtu() {
  const [items, setItems] = useState([]);
  const [settingsForm, setSettingsForm] = useState(emptySettings);
  const [form, setForm] = useState(emptyForm);
  const [editingRecord, setEditingRecord] = useState(null);
  const [activeLocale, setActiveLocale] = useState("en");
  const [activeTab, setActiveTab] = useState("items");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [error, setError] = useState("");

  const fetchVideos = useCallback(async () => {
    setLoading(true);
    try {
      const pageData = await apanelService.listPage("videos", { per_page: 100 });
      setItems(pageData.items || []);
    } catch (err) {
      setError(err?.message || "Failed to load videos.");
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchSettings = useCallback(async () => {
    try {
      const response = await apanelService.getVideoGallerySettings();
      const setting = response?.data || response;
      setSettingsForm({
        ...emptySettings,
        ...setting,
        translations: Object.fromEntries(
          locales.map((locale) => {
            const existing = setting.translations?.find((item) => item.locale === locale);
            return [
              locale,
              {
                ...emptySettings.translations[locale],
                ...(existing || {}),
                category_labels: existing?.category_labels || {},
              },
            ];
          }),
        ),
      });
    } catch (err) {
      setError(err?.message || "Failed to load video gallery settings.");
    }
  }, []);

  useEffect(() => {
    fetchVideos();
    fetchSettings();
  }, [fetchVideos, fetchSettings]);

  const currentTranslation = form.translations[activeLocale] || emptyTranslation;
  const currentSettingsTranslation =
    settingsForm.translations[activeLocale] || emptySettings.translations[activeLocale];

  const sortedItems = useMemo(
    () => [...items].sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0)),
    [items],
  );
  const categoryOptions = useMemo(() => {
    const optionSet = new Set(["All"]);

    items.forEach((item) => {
      const translation = findTranslation(item.translations, activeLocale);
      if (translation?.category) optionSet.add(translation.category);
    });

    Object.keys(currentSettingsTranslation.category_labels || {}).forEach((category) => {
      if (category && (category === "All" || optionSet.has(category))) optionSet.add(category);
    });

    return [...optionSet];
  }, [activeLocale, currentSettingsTranslation.category_labels, items]);

  const resetForm = () => {
    setForm(emptyForm);
    setEditingRecord(null);
    setError("");
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

  const startEdit = (record) => {
    setEditingRecord(record);
    setForm(fromRecord(record));
    setActiveTab("form");
    setError("");
  };

  const handleUpload = async (file, targetField) => {
    if (!file) return;
    if (file.type.startsWith("video/") && file.size > MAX_VIDEO_UPLOAD_BYTES) {
      setError("Video file is too large. Please upload a video up to 200 MB.");
      return;
    }

    try {
      const media = await apanelService.uploadMedia(file, {
        type: file.type.startsWith("video/") ? "video" : "image",
        alt_text: form.translations.en.title || file.name,
      });
      setField(targetField, media.path || media.url || media.data?.path || "");
    } catch (err) {
      setError(err?.message || "Failed to upload media.");
    }
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");
    try {
      const payload = toPayload(form);
      if (editingRecord) {
        await apanelService.update("videos", editingRecord.id, payload);
      } else {
        await apanelService.create("videos", payload);
      }
      await fetchVideos();
      resetForm();
      setActiveTab("items");
    } catch (err) {
      setError(err?.message || "Failed to save video.");
    } finally {
      setSaving(false);
    }
  };

  const togglePublish = async (record) => {
    await apanelService.update("videos", record.id, {
      ...record,
      is_active: !record.is_active,
      translations: Object.fromEntries(
        (record.translations || []).map((translation) => [translation.locale, translation]),
      ),
    });
    fetchVideos();
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    await apanelService.delete("videos", pendingDelete.id);
    setPendingDelete(null);
    fetchVideos();
  };

  const saveSettings = async () => {
    setSaving(true);
    setError("");
    try {
      const normalizedSettings = {
        ...settingsForm,
        translations: Object.fromEntries(
          locales.map((locale) => {
            const existing = settingsForm.translations[locale] || emptySettings.translations[locale];
            const labels = { ...(existing.category_labels || {}) };

            items.forEach((item) => {
              const translation = findTranslation(item.translations, locale);
              if (translation?.category && !labels[translation.category]) {
                labels[translation.category] = translation.category;
              }
            });

            return [
              locale,
              {
                ...existing,
                category_labels: labels,
              },
            ];
          }),
        ),
      };

      await apanelService.updateVideoGallerySettings(normalizedSettings);
      await fetchSettings();
    } catch (err) {
      setError(err?.message || "Failed to save settings.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 className="text-2xl font-black text-navy">Video Gallery CMS</h1>
          <p className="text-sm font-semibold text-gray-500">
            Control homepage videos and the public video gallery page.
          </p>
        </div>
        <div className="flex gap-2">
          <button
            type="button"
            onClick={fetchVideos}
            className="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-extrabold text-navy hover:bg-gray-50"
          >
            <RefreshCw className="w-4 h-4" />
            Refresh
          </button>
          <button
            type="button"
            onClick={() => {
              resetForm();
              setActiveTab("form");
            }}
            className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white hover:bg-primary-hover"
          >
            <Plus className="w-4 h-4" />
            Add Video
          </button>
        </div>
      </div>

      {error && <FormError message={error} />}

      <div className="flex flex-wrap gap-2">
        {[
          ["items", "Video Items"],
          ["form", editingRecord ? "Edit Video" : "Create Video"],
          ["settings", "Video Gallery Settings"],
        ].map(([key, label]) => (
          <button
            key={key}
            type="button"
            onClick={() => setActiveTab(key)}
            className={`rounded-xl px-4 py-2 text-xs font-black ${
              activeTab === key ? "bg-primary text-white" : "bg-white border border-gray-100 text-navy"
            }`}
          >
            {label}
          </button>
        ))}
      </div>

      <div className="flex gap-2">
        {locales.map((locale) => (
          <button
            key={locale}
            type="button"
            onClick={() => setActiveLocale(locale)}
            className={`rounded-lg px-3 py-1.5 text-[11px] font-black uppercase ${
              activeLocale === locale ? "bg-navy text-white" : "bg-white border border-gray-100 text-gray-500"
            }`}
          >
            {locale}
          </button>
        ))}
      </div>

      {activeTab === "settings" && (
        <section className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-5">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <label className="space-y-1.5">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Home limit</span>
              <input type="number" min="1" max="12" value={settingsForm.home_limit} onChange={(e) => setSettingsForm((p) => ({ ...p, home_limit: Number(e.target.value) }))} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
            </label>
            <label className="space-y-1.5">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Subscribers</span>
              <input type="number" min="0" value={settingsForm.subscriber_count} onChange={(e) => setSettingsForm((p) => ({ ...p, subscriber_count: Number(e.target.value) }))} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
            </label>
            <label className="space-y-1.5">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">YouTube channel URL</span>
              <input value={settingsForm.youtube_channel_url || ""} onChange={(e) => setSettingsForm((p) => ({ ...p, youtube_channel_url: e.target.value }))} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
            </label>
          </div>

          {[
            ["home_tag", "Home tag"],
            ["home_title", "Home title"],
            ["home_subtitle", "Home subtitle"],
            ["view_all_label", "View all label"],
            ["recommended_label", "Recommended label"],
            ["videos_label", "Videos label"],
            ["description_title", "Description title"],
            ["like_label", "Like label"],
            ["liked_label", "Liked label"],
            ["share_label", "Share label"],
            ["subscribe_label", "Subscribe label"],
            ["subscribed_label", "Subscribed label"],
            ["subscribers_label", "Subscribers label"],
            ["views_label", "Views label"],
            ["channel_name", "Channel name"],
            ["link_copied_label", "Link copied label"],
            ["no_videos_label", "No videos label"],
            ["comments_label", "Comments label"],
            ["reply_label", "Reply label"],
            ["form_title", "Comment form title"],
            ["form_comment_label", "Comment textarea placeholder"],
            ["form_submit_label", "Comment submit label"],
            ["sign_in_title", "Sign-in title"],
            ["sign_in_text", "Sign-in text"],
            ["sign_in_action", "Sign-in action"],
            ["signed_in_as_label", "Signed-in-as label"],
            ["category_label", "Category label"],
            ["duration_label", "Duration label"],
            ["platform_label", "Platform label"],
            ["local_label", "Local platform label"],
            ["youtube_label", "YouTube platform label"],
            ["playing_label", "Playing badge label"],
            ["verified_channel_label", "Verified channel label"],
          ].map(([field, label]) => (
            <label key={field} className="space-y-1.5 block">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">{label}</span>
              <input value={currentSettingsTranslation[field] || ""} onChange={(e) => setSettingsTranslationField(field, e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
            </label>
          ))}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {categoryOptions.map((category) => (
              <label key={category} className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">{category} label</span>
                <input
                  value={currentSettingsTranslation.category_labels?.[category] || category}
                  onChange={(e) =>
                    setSettingsTranslationField("category_labels", {
                      ...(currentSettingsTranslation.category_labels || {}),
                      [category]: e.target.value,
                    })
                  }
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy"
                />
              </label>
            ))}
          </div>

          <button type="button" onClick={saveSettings} disabled={saving} className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-xs font-extrabold text-white hover:bg-primary-hover disabled:opacity-60">
            {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
            Save Settings
          </button>
        </section>
      )}

      {activeTab === "form" && (
        <section className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs">
          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <h2 className="text-lg font-black text-navy">
                {editingRecord ? "Edit Video" : "Create Video"}
              </h2>
              <p className="text-xs font-semibold text-gray-500">
                Update the video source, thumbnail, stats, publishing status, and translated content.
              </p>
            </div>

            <div className="rounded-2xl border border-gray-100 bg-gray-50/70 p-4 space-y-4">
              <div>
                <h3 className="text-sm font-black text-navy">Video Source & Publishing</h3>
                <p className="text-[11px] font-semibold text-gray-500">
                  These fields control the media file or YouTube video and the public visibility.
                </p>
              </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Slug</span>
                <input value={form.slug} onChange={(e) => setField("slug", slugify(e.target.value))} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" required />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Video type</span>
                <select value={form.video_type} onChange={(e) => setField("video_type", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy">
                  <option value="youtube">YouTube URL</option>
                  <option value="local">Uploaded video</option>
                </select>
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Sort order</span>
                <input type="number" value={form.sort_order} onChange={(e) => setField("sort_order", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Video URL or storage path</span>
                <div className="flex gap-2">
                  <input value={form.url} onChange={(e) => {
                    setField("url", e.target.value);
                    if (form.video_type === "youtube") setField("youtube_id", youtubeIdFromUrl(e.target.value));
                  }} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" required />
                  <label className="inline-flex items-center justify-center rounded-xl border border-gray-200 px-3 cursor-pointer">
                    <Upload className="w-4 h-4" />
                    <input type="file" accept="video/*" className="hidden" onChange={(e) => handleUpload(e.target.files?.[0], "url")} />
                  </label>
                </div>
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Thumbnail URL or storage path</span>
                <div className="flex gap-2">
                  <input value={form.thumbnail} onChange={(e) => setField("thumbnail", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
                  <label className="inline-flex items-center justify-center rounded-xl border border-gray-200 px-3 cursor-pointer">
                    <Upload className="w-4 h-4" />
                    <input type="file" accept="image/*" className="hidden" onChange={(e) => handleUpload(e.target.files?.[0], "thumbnail")} />
                  </label>
                </div>
              </label>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">YouTube ID</span>
                <input placeholder="YouTube ID" value={form.youtube_id} onChange={(e) => setField("youtube_id", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Duration</span>
                <input placeholder="4:15" value={form.duration} onChange={(e) => setField("duration", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Views count</span>
                <input type="number" placeholder="Views" value={form.views_count} onChange={(e) => setField("views_count", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Likes count</span>
                <input type="number" placeholder="Likes" value={form.likes_count} onChange={(e) => setField("likes_count", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Publish date</span>
                <input type="date" value={form.published_at} onChange={(e) => setField("published_at", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
            </div>

            <label className="inline-flex items-center gap-2 text-sm font-bold text-navy">
              <input type="checkbox" checked={form.is_active} onChange={(e) => setField("is_active", e.target.checked)} />
              Published
            </label>
            </div>

            <div className="rounded-2xl border border-gray-100 bg-white p-4 space-y-4">
              <div>
                <h3 className="text-sm font-black text-navy">
                  Translated Content - {localeNames[activeLocale] || activeLocale.toUpperCase()}
                </h3>
                <p className="text-[11px] font-semibold text-gray-500">
                  Use the language tabs above to edit the title, category, and description for each locale.
                </p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label className="space-y-1.5">
                  <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Title</span>
                  <input placeholder="Video title" value={currentTranslation.title} onChange={(e) => {
                    setTranslationField("title", e.target.value);
                    if (!form.slug && activeLocale === "en") setField("slug", slugify(e.target.value));
                  }} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
                </label>
                <label className="space-y-1.5">
                  <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Category</span>
                  <input placeholder="Category" value={currentTranslation.category} onChange={(e) => setTranslationField("category", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
                </label>
              </div>

              <label className="space-y-1.5 block">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Description</span>
                <textarea placeholder="Video description" value={currentTranslation.description} rows={5} onChange={(e) => setTranslationField("description", e.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
            </div>

            <div className="flex justify-end">
              <button type="submit" disabled={saving} className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-xs font-extrabold text-white hover:bg-primary-hover disabled:opacity-60">
                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                Save Video
              </button>
            </div>
          </form>
        </section>
      )}

      {activeTab === "items" && (
        <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full min-w-220 text-start">
              <thead className="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-400 font-black">
                <tr>
                  <th className="px-5 py-3 text-start">Video</th>
                  <th className="px-5 py-3 text-start">Category</th>
                  <th className="px-5 py-3 text-start">Stats</th>
                  <th className="px-5 py-3 text-start">Status</th>
                  <th className="px-5 py-3 text-end">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {loading ? (
                  <tr><td colSpan="5" className="px-5 py-12 text-center"><Loader2 className="w-7 h-7 animate-spin text-primary mx-auto" /></td></tr>
                ) : sortedItems.map((item) => {
                  const title = item.translations?.find((translation) => translation.locale === "en")?.title || item.slug;
                  const category = item.translations?.find((translation) => translation.locale === "en")?.category || "";
                  return (
                    <tr key={item.id} className="hover:bg-gray-50/70">
                      <td className="px-5 py-4">
                        <div className="flex items-center gap-3">
                          <div className="w-16 h-12 rounded-xl bg-gray-100 border border-gray-100 flex items-center justify-center text-gray-400 overflow-hidden">
                            {item.thumbnail ? <img src={mediaPreviewSrc(item.thumbnail)} alt={title} className="w-full h-full object-cover" /> : <ImageIcon className="w-4 h-4" />}
                          </div>
                          <div>
                            <p className="text-sm font-extrabold text-navy line-clamp-1">{title}</p>
                            <p className="text-[11px] font-semibold text-gray-400">{item.slug}</p>
                          </div>
                        </div>
                      </td>
                      <td className="px-5 py-4 text-xs font-bold text-gray-500">{category}</td>
                      <td className="px-5 py-4 text-xs font-semibold text-gray-500">{item.views_count || 0} views / {item.likes_count || 0} likes</td>
                      <td className="px-5 py-4">
                        <span className={`inline-flex px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider ${item.is_active ? "bg-emerald-50 text-emerald-700" : "bg-gray-100 text-gray-500"}`}>{item.is_active ? "Published" : "Hidden"}</span>
                      </td>
                      <td className="px-5 py-4">
                        <div className="flex items-center justify-end gap-2">
                          <button type="button" onClick={() => startEdit(item)} className="w-9 h-9 rounded-xl border border-gray-200 text-navy hover:bg-gray-50 flex items-center justify-center"><Edit3 className="w-4 h-4" /></button>
                          <button type="button" onClick={() => togglePublish(item)} className="w-9 h-9 rounded-xl border border-gray-200 text-navy hover:bg-gray-50 flex items-center justify-center">{item.is_active ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}</button>
                          <button type="button" onClick={() => setPendingDelete(item)} className="w-9 h-9 rounded-xl border border-rose-100 text-rose-500 hover:bg-rose-50 flex items-center justify-center"><Trash2 className="w-4 h-4" /></button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </section>
      )}
      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title="Delete video?"
        message={`This will permanently delete ${pendingDelete?.slug || "this video"}. This action cannot be undone.`}
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
