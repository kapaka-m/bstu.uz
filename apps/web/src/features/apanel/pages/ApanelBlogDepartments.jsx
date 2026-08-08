import React, { useCallback, useEffect, useMemo, useState } from "react";
import { Edit3, Image as ImageIcon, Loader2, Plus, RefreshCw, Save, Trash2, Upload, X } from "lucide-react";
import FormError from "../../../components/common/FormError";
import { publicAssetUrl } from "../../../lib/api";
import { apanelService } from "../../../services/apanelService";
import ConfirmDialog from "../components/ConfirmDialog";
import { useApanelLocaleCodes } from "../utils/locales";

const emptyTranslation = {
  name: "",
  description: "",
  meta_title: "",
  meta_description: "",
};

const slugify = (value) =>
  value
    .toString()
    .toLowerCase()
    .trim()
    .replace(/['"]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");

const emptyForm = (localeCodes) => ({
  slug: "",
  image: "",
  email: "",
  phone: "",
  website_url: "",
  sort_order: 0,
  is_active: true,
  translations: Object.fromEntries(localeCodes.map((locale) => [locale, { ...emptyTranslation }])),
});

const fromRecord = (record, localeCodes) => ({
  slug: record.slug || "",
  image: record.image || "",
  email: record.email || "",
  phone: record.phone || "",
  website_url: record.website_url || "",
  sort_order: Number(record.sort_order || 0),
  is_active: Boolean(record.is_active),
  translations: Object.fromEntries(
    localeCodes.map((locale) => {
      const existing = record.translations?.find((item) => item.locale === locale);
      return [locale, { ...emptyTranslation, ...(existing || {}) }];
    }),
  ),
});

const toPayload = (form, primaryLocale, localeCodes) => {
  const fallback = form.translations[primaryLocale] || Object.values(form.translations || {})[0] || emptyTranslation;

  return {
    slug: form.slug,
    image: form.image || null,
    email: form.email || null,
    phone: form.phone || null,
    website_url: form.website_url || null,
    sort_order: Number(form.sort_order || 0),
    is_active: Boolean(form.is_active),
    translations: Object.fromEntries(
      localeCodes.map((locale) => {
        const current = form.translations[locale] || emptyTranslation;
        const name = current.name || fallback.name || form.slug;
        return [
          locale,
          {
            name,
            description: current.description || fallback.description || "",
            meta_title: current.meta_title || name,
            meta_description: current.meta_description || current.description || fallback.description || "",
          },
        ];
      }),
    ),
  };
};

export default function ApanelBlogDepartments() {
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || "en";
  const [items, setItems] = useState([]);
  const [form, setForm] = useState(() => emptyForm([]));
  const [activeLocale, setActiveLocale] = useState("");
  const [editingRecord, setEditingRecord] = useState(null);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);
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
  }, [localeCodes, primaryLocale]);

  const fetchItems = useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const page = await apanelService.listPage("blog-departments", {
        per_page: 100,
        sort_by: "sort_order",
        sort_dir: "asc",
      });
      setItems(page.items);
    } catch (err) {
      setError(err?.message || "Failed to load content publishers.");
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchItems();
  }, [fetchItems]);

  const currentTranslation = form.translations[activeLocale] || emptyTranslation;
  const editorOpen = editingRecord !== null || form.slug;

  const startCreate = () => {
    setEditingRecord(null);
    setForm(emptyForm(localeCodes));
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

  const setField = (field, value) => setForm((current) => ({ ...current, [field]: value }));

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

  const uploadImage = async (event) => {
    const file = event.target.files?.[0];
    event.target.value = "";
    if (!file) return;

    try {
      setUploading(true);
      setError("");
      const uploaded = await apanelService.uploadMedia(file, {
        title: file.name,
        alt_text: currentTranslation.name || file.name,
        type: "image",
        is_public: "1",
      });
      setField("image", uploaded.path);
    } catch (err) {
      setError(err?.message || "Failed to upload image.");
    } finally {
      setUploading(false);
    }
  };

  const save = async (event) => {
    event.preventDefault();
    try {
      setSaving(true);
      setError("");
      const payload = toPayload(form, primaryLocale, localeCodes);
      if (editingRecord?.id) {
        await apanelService.update("blog-departments", editingRecord.id, payload);
      } else {
        await apanelService.create("blog-departments", payload);
      }
      closeEditor();
      fetchItems();
    } catch (err) {
      setError(err?.message || "Failed to save content publisher.");
    } finally {
      setSaving(false);
    }
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    try {
      setError("");
      await apanelService.delete("blog-departments", pendingDelete.id);
      setPendingDelete(null);
      fetchItems();
    } catch (err) {
      setError(err?.message || "Failed to delete content publisher.");
    }
  };

  const stats = useMemo(
    () => ({
      total: items.length,
      active: items.filter((item) => item.is_active).length,
    }),
    [items],
  );

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            Content Publishers
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            Manage publishers used by blog posts, news, announcements, green campus articles, and videos.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button type="button" onClick={fetchItems} className="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-extrabold text-navy">
            <RefreshCw className="h-4 w-4" />
            Refresh
          </button>
          <button type="button" onClick={startCreate} className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white">
            <Plus className="h-4 w-4" />
            Add Publisher
          </button>
        </div>
      </div>

      {error && <FormError message={error} />}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {[
          ["Total Publishers", stats.total],
          ["Active Publishers", stats.active],
        ].map(([label, value]) => (
          <div key={label} className="rounded-3xl border border-gray-100 bg-white p-5 shadow-xs">
            <p className="text-[10px] uppercase tracking-wider font-black text-gray-400">{label}</p>
            <p className="mt-1 text-lg font-extrabold text-navy">{value}</p>
          </div>
        ))}
      </div>

      {editorOpen && (
        <section className="rounded-3xl border border-gray-100 bg-white shadow-xs overflow-hidden">
          <div className="flex items-center justify-between gap-4 border-b border-gray-100 p-5">
            <h2 className="text-lg font-extrabold text-navy">
              {editingRecord ? "Edit Publisher" : "Create Publisher"}
            </h2>
            <button type="button" onClick={closeEditor} className="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 text-gray-500">
              <X className="h-4 w-4" />
            </button>
          </div>
          <form onSubmit={save} className="p-5 space-y-5">
            <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Slug</span>
                <input value={form.slug} onChange={(event) => setField("slug", slugify(event.target.value))} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" required />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Email</span>
                <input type="email" value={form.email} onChange={(event) => setField("email", event.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Phone</span>
                <input value={form.phone} onChange={(event) => setField("phone", event.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Status</span>
                <select value={form.is_active ? "1" : "0"} onChange={(event) => setField("is_active", event.target.value === "1")} className="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy">
                  <option value="1">Active</option>
                  <option value="0">Hidden</option>
                </select>
              </label>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Website URL</span>
                <input value={form.website_url} onChange={(event) => setField("website_url", event.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Sort Order</span>
                <input type="number" value={form.sort_order} onChange={(event) => setField("sort_order", Number(event.target.value))} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
            </div>

            <label className="space-y-1.5 block">
              <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Image URL or storage path</span>
              <div className="flex gap-3">
                <div className="w-11 h-11 rounded-xl bg-primary-light text-primary flex items-center justify-center shrink-0">
                  <ImageIcon className="w-4 h-4" />
                </div>
                <input value={form.image} onChange={(event) => setField("image", event.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
                <label className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-navy text-white text-xs font-extrabold hover:bg-primary transition-colors cursor-pointer shrink-0">
                  {uploading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Upload className="w-4 h-4" />}
                  Upload
                  <input type="file" accept="image/jpeg,image/png,image/webp" className="hidden" disabled={uploading} onChange={uploadImage} />
                </label>
              </div>
              {form.image && <img src={publicAssetUrl(form.image)} alt="" className="mt-3 h-24 w-24 rounded-2xl object-cover border border-gray-100" />}
            </label>

            <div className="flex flex-wrap gap-2">
              {localeCodes.map((locale) => (
                <button type="button" key={locale} onClick={() => setActiveLocale(locale)} className={`px-4 py-2 rounded-xl text-xs font-extrabold uppercase ${activeLocale === locale ? "bg-primary text-white" : "bg-gray-50 text-gray-500"}`}>
                  {locale}
                </button>
              ))}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Name</span>
                <input
                  value={currentTranslation.name}
                  onChange={(event) => setTranslationField("name", event.target.value)}
                  onBlur={() => {
                    if (activeLocale === primaryLocale && !form.slug) setField("slug", slugify(currentTranslation.name));
                  }}
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy"
                  required={activeLocale === primaryLocale}
                />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Meta Title</span>
                <input value={currentTranslation.meta_title} onChange={(event) => setTranslationField("meta_title", event.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Description</span>
                <textarea rows={5} value={currentTranslation.description} onChange={(event) => setTranslationField("description", event.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
              <label className="space-y-1.5">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Meta Description</span>
                <textarea rows={5} value={currentTranslation.meta_description} onChange={(event) => setTranslationField("meta_description", event.target.value)} className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy" />
              </label>
            </div>

            <div className="flex justify-end">
              <button disabled={saving} className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-xs font-extrabold text-white disabled:opacity-60">
                {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
                Save Publisher
              </button>
            </div>
          </form>
        </section>
      )}

      <section className="rounded-3xl border border-gray-100 bg-white shadow-xs overflow-hidden">
        {loading ? (
          <div className="flex min-h-64 items-center justify-center">
            <Loader2 className="h-8 w-8 animate-spin text-primary" />
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full text-start text-sm">
              <thead className="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-400">
                <tr>
                  <th className="px-5 py-3 text-start">Publisher</th>
                  <th className="px-5 py-3 text-start">Slug</th>
                  <th className="px-5 py-3 text-start">Status</th>
                  <th className="px-5 py-3 text-end">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {items.map((item) => {
                  const translation = item.translations?.find((entry) => entry.locale === primaryLocale) || item.translations?.[0] || {};
                  return (
                    <tr key={item.id}>
                      <td className="px-5 py-4">
                        <div className="flex items-center gap-3">
                          {item.image && <img src={publicAssetUrl(item.image)} alt="" className="h-11 w-11 rounded-xl object-cover border border-gray-100" />}
                          <span className="font-extrabold text-navy">{translation.name || item.slug}</span>
                        </div>
                      </td>
                      <td className="px-5 py-4 text-xs font-bold text-gray-500">{item.slug}</td>
                      <td className="px-5 py-4 text-xs font-bold text-gray-500">{item.is_active ? "Active" : "Hidden"}</td>
                      <td className="px-5 py-4">
                        <div className="flex justify-end gap-2">
                          <button type="button" onClick={() => startEdit(item)} className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Edit3 className="h-4 w-4" />
                          </button>
                          <button type="button" onClick={() => setPendingDelete(item)} className="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-600">
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
      </section>

      <ConfirmDialog
        open={Boolean(pendingDelete)}
        title="Delete Content Publisher"
        message="This publisher will be removed. Linked content will keep its main content but lose this publisher profile link."
        confirmLabel="Delete"
        loading={false}
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
