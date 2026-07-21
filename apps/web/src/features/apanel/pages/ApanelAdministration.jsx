import React, { useEffect, useMemo, useState } from "react";
import { Camera, Edit3, Plus, Save, Trash2, UploadCloud, X } from "lucide-react";
import { apanelService } from "../../../services/apanelService";
import ConfirmDialog from "../components/ConfirmDialog";

const LOCALES = ["en", "uz", "ru", "ar"];
const emptyTranslations = () =>
  Object.fromEntries(
    LOCALES.map((locale) => [
      locale,
      {
        full_name: "",
        position: "",
        degree: "",
        office_hours: "",
        about: "",
        details: "",
        achievements: [],
      },
    ]),
  );

const emptyForm = () => ({
  slug: "",
  photo: "",
  email: "",
  phone: "",
  telegram_url: "",
  sort_order: 0,
  is_rector: false,
  is_published: true,
  translations: emptyTranslations(),
});

const emptySettingsTranslations = () =>
  Object.fromEntries(
    LOCALES.map((locale) => [
      locale,
      {
        home_tag: "",
        home_title: "",
        reception_label: "",
        phone_label: "",
        email_label: "",
        telegram_label: "",
        rector_bot_label: "",
        structure_title: "",
        profile_category_label: "",
        email_address_label: "",
        phone_number_label: "",
        office_hours_label: "",
        academic_rank_label: "",
        biography_label: "",
        duties_label: "",
        achievements_label: "",
      },
    ]),
  );

const storageUrl = (path) => {
  if (!path) return "";
  if (/^https?:\/\//i.test(path) || path.startsWith("/")) return path;
  const apiBase = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1";
  return `${apiBase.replace(/\/api\/v1\/?$/, "")}/storage/${path.replace(/^public\//, "")}`;
};

export default function ApanelAdministration() {
  const [tab, setTab] = useState("profiles");
  const [items, setItems] = useState([]);
  const [editing, setEditing] = useState(null);
  const [form, setForm] = useState(emptyForm());
  const [settings, setSettings] = useState({
    home_limit: 6,
    is_active: true,
    translations: emptySettingsTranslations(),
  });
  const [activeLocale, setActiveLocale] = useState("en");
  const [saving, setSaving] = useState(false);
  const [pendingDelete, setPendingDelete] = useState(null);

  const sortedItems = useMemo(
    () => [...items].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0)),
    [items],
  );

  const load = async () => {
    const [profilesPage, settingsData] = await Promise.all([
      apanelService.listPage("administration-profiles", { per_page: 100, sort_by: "sort_order", sort_dir: "asc" }),
      apanelService.getAdministrationSettings(),
    ]);
    setItems(profilesPage.items || []);
    setSettings(normalizeSettings(settingsData));
  };

  useEffect(() => {
    load().catch((err) => console.error("Administration CMS load failed", err));
  }, []);

  const startCreate = () => {
    setEditing(null);
    setForm(emptyForm());
    setTab("editor");
  };

  const startEdit = (item) => {
    setEditing(item);
    setForm(normalizeProfileForm(item));
    setTab("editor");
  };

  const saveProfile = async (event) => {
    event.preventDefault();
    setSaving(true);
    try {
      if (editing?.id) {
        await apanelService.update("administration-profiles", editing.id, form);
      } else {
        await apanelService.create("administration-profiles", form);
      }
      await load();
      setTab("profiles");
      setEditing(null);
      setForm(emptyForm());
    } finally {
      setSaving(false);
    }
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    await apanelService.delete("administration-profiles", pendingDelete.id);
    setPendingDelete(null);
    await load();
  };

  const uploadPhoto = async (file) => {
    if (!file) return;
    const uploaded = await apanelService.uploadMedia(file, {
      alt_key: `administration.${form.slug || "profile"}.photo`,
      type: "image",
    });
    setForm((prev) => ({ ...prev, photo: uploaded?.path || uploaded?.data?.path || "" }));
  };

  const saveSettings = async (event) => {
    event.preventDefault();
    setSaving(true);
    try {
      await apanelService.updateAdministrationSettings(settings);
      await load();
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy">Administration CMS</h1>
          <p className="text-sm text-gray-500 font-semibold mt-1">
            Manage university leadership shown on the homepage, profile pages, and Structure menu.
          </p>
        </div>
        <button
          onClick={startCreate}
          className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white shadow-sm hover:bg-primary-hover"
        >
          <Plus className="w-4 h-4" />
          Add Profile
        </button>
      </div>

      <div className="flex flex-wrap gap-2">
        {[
          ["profiles", "Profiles"],
          ["editor", editing ? "Edit Profile" : "Add Profile"],
          ["settings", "Settings"],
        ].map(([key, label]) => (
          <button
            key={key}
            onClick={() => setTab(key)}
            className={`rounded-xl px-4 py-2 text-xs font-extrabold border transition ${
              tab === key ? "bg-navy text-white border-navy" : "bg-white text-navy border-gray-100 hover:border-primary/30"
            }`}
          >
            {label}
          </button>
        ))}
      </div>

      {tab === "profiles" && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
          {sortedItems.map((item) => {
            const en = item.translations?.find((translation) => translation.locale === "en") || item.translations?.[0] || {};
            return (
              <div key={item.id} className="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex gap-4">
                <div className="w-24 h-28 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                  {item.photo ? (
                    <img src={storageUrl(item.photo)} alt={en.full_name || item.slug} className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-primary">
                      <Camera className="w-7 h-7" />
                    </div>
                  )}
                </div>
                <div className="min-w-0 flex-1">
                  <div className="flex items-start justify-between gap-3">
                    <div>
                      <h2 className="text-sm font-extrabold text-navy">{en.full_name || item.slug}</h2>
                      <p className="text-xs text-gray-500 font-semibold mt-1">{en.position}</p>
                    </div>
                    <span className={`text-[10px] font-black rounded-full px-2 py-1 ${item.is_published ? "bg-emerald-50 text-emerald-700" : "bg-gray-100 text-gray-500"}`}>
                      {item.is_published ? "Published" : "Hidden"}
                    </span>
                  </div>
                  <div className="mt-3 flex flex-wrap gap-2 text-[11px] font-bold text-gray-500">
                    <span>#{item.sort_order}</span>
                    {item.is_rector && <span className="text-primary">Rector</span>}
                    <span>{item.phone}</span>
                  </div>
                  <div className="mt-4 flex gap-2">
                    <button onClick={() => startEdit(item)} className="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 px-3 py-2 text-xs font-bold text-navy hover:border-primary/30">
                      <Edit3 className="w-3.5 h-3.5" />
                      Edit
                    </button>
                    <button onClick={() => setPendingDelete(item)} className="inline-flex items-center gap-1.5 rounded-lg border border-rose-100 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50">
                      <Trash2 className="w-3.5 h-3.5" />
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {tab === "editor" && (
        <ProfileForm
          activeLocale={activeLocale}
          form={form}
          saving={saving}
          setActiveLocale={setActiveLocale}
          setForm={setForm}
          onCancel={() => setTab("profiles")}
          onSave={saveProfile}
          onUpload={uploadPhoto}
        />
      )}

      {tab === "settings" && (
        <form onSubmit={saveSettings} className="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-5">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label className="space-y-1 text-xs font-bold text-gray-500">
              Home Limit
              <input
                type="number"
                value={settings.home_limit}
                onChange={(e) => setSettings((prev) => ({ ...prev, home_limit: Number(e.target.value) }))}
                className="w-full rounded-xl border border-gray-100 px-3 py-2.5 text-sm text-navy outline-none focus:border-primary"
              />
            </label>
            <div className="space-y-1 text-xs font-bold text-gray-500">
              <span>Active</span>
              <label className="flex min-h-10.5 items-center gap-2 rounded-xl border border-gray-100 px-3 py-2.5 text-sm font-bold text-navy">
                <input
                  type="checkbox"
                  checked={Boolean(settings.is_active)}
                  onChange={(e) => setSettings((prev) => ({ ...prev, is_active: e.target.checked }))}
                />
                Active
              </label>
            </div>
          </div>

          <LocaleTabs active={activeLocale} onChange={setActiveLocale} />
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {[
              "home_tag",
              "home_title",
              "reception_label",
              "phone_label",
              "email_label",
              "telegram_label",
              "rector_bot_label",
              "structure_title",
              "profile_category_label",
              "email_address_label",
              "phone_number_label",
              "office_hours_label",
              "academic_rank_label",
              "biography_label",
              "duties_label",
              "achievements_label",
            ].map((field) => (
              <TextField
                key={field}
                label={field.replaceAll("_", " ")}
                value={settings.translations?.[activeLocale]?.[field] || ""}
                onChange={(value) =>
                  setSettings((prev) => ({
                    ...prev,
                    translations: {
                      ...prev.translations,
                      [activeLocale]: {
                        ...prev.translations[activeLocale],
                        [field]: value,
                      },
                    },
                  }))
                }
              />
            ))}
          </div>

          <button className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white">
            <Save className="w-4 h-4" />
            {saving ? "Saving..." : "Save Settings"}
          </button>
        </form>
      )}
      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title="Delete profile?"
        message={`This will permanently delete ${pendingDelete?.slug || "this profile"}. This action cannot be undone.`}
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}

function ProfileForm({ activeLocale, form, saving, setActiveLocale, setForm, onCancel, onSave, onUpload }) {
  const t = form.translations[activeLocale] || {};
  const setTranslation = (field, value) => {
    setForm((prev) => ({
      ...prev,
      translations: {
        ...prev.translations,
        [activeLocale]: {
          ...prev.translations[activeLocale],
          [field]: value,
        },
      },
    }));
  };

  return (
    <form onSubmit={onSave} className="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-5">
      <div className="grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-5">
        <div className="space-y-3">
          <div className="aspect-4/5 rounded-2xl overflow-hidden bg-gray-100 border border-gray-100">
            {form.photo ? (
              <img src={storageUrl(form.photo)} alt="" className="w-full h-full object-cover" />
            ) : (
              <div className="w-full h-full flex items-center justify-center text-primary">
                <Camera className="w-8 h-8" />
              </div>
            )}
          </div>
          <label className="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-100 px-4 py-2.5 text-xs font-extrabold text-navy hover:border-primary/30 cursor-pointer">
            <UploadCloud className="w-4 h-4" />
            Upload Photo
            <input type="file" accept="image/*" className="hidden" onChange={(e) => onUpload(e.target.files?.[0])} />
          </label>
        </div>

        <div className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <TextField label="Slug" value={form.slug} onChange={(value) => setForm((prev) => ({ ...prev, slug: value }))} required />
            <TextField label="Photo URL or storage path" value={form.photo} onChange={(value) => setForm((prev) => ({ ...prev, photo: value }))} />
            <TextField label="Phone" value={form.phone} onChange={(value) => setForm((prev) => ({ ...prev, phone: value }))} />
            <TextField label="Email" type="email" value={form.email} onChange={(value) => setForm((prev) => ({ ...prev, email: value }))} />
            <TextField label="Telegram URL" value={form.telegram_url} onChange={(value) => setForm((prev) => ({ ...prev, telegram_url: value }))} />
            <TextField label="Sort Order" type="number" value={form.sort_order} onChange={(value) => setForm((prev) => ({ ...prev, sort_order: Number(value) }))} />
          </div>
          <div className="flex flex-wrap gap-3">
            <label className="inline-flex items-center gap-2 text-xs font-bold text-navy">
              <input type="checkbox" checked={form.is_rector} onChange={(e) => setForm((prev) => ({ ...prev, is_rector: e.target.checked }))} />
              Rector
            </label>
            <label className="inline-flex items-center gap-2 text-xs font-bold text-navy">
              <input type="checkbox" checked={form.is_published} onChange={(e) => setForm((prev) => ({ ...prev, is_published: e.target.checked }))} />
              Published
            </label>
          </div>
        </div>
      </div>

      <LocaleTabs active={activeLocale} onChange={setActiveLocale} />
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <TextField label="Full Name" value={t.full_name || ""} onChange={(value) => setTranslation("full_name", value)} required />
        <TextField label="Position" value={t.position || ""} onChange={(value) => setTranslation("position", value)} required />
        <TextField label="Degree" value={t.degree || ""} onChange={(value) => setTranslation("degree", value)} />
        <TextField label="Office Hours" value={t.office_hours || ""} onChange={(value) => setTranslation("office_hours", value)} />
      </div>
      <TextArea label="About" value={t.about || ""} onChange={(value) => setTranslation("about", value)} />
      <TextArea label="Details" value={t.details || ""} onChange={(value) => setTranslation("details", value)} />
      <TextArea
        label="Achievements"
        value={(t.achievements || []).join("\n")}
        onChange={(value) => setTranslation("achievements", value.split("\n").map((line) => line.trim()).filter(Boolean))}
      />

      <div className="flex flex-wrap gap-2">
        <button className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white">
          <Save className="w-4 h-4" />
          {saving ? "Saving..." : "Save"}
        </button>
        <button type="button" onClick={onCancel} className="inline-flex items-center gap-2 rounded-xl border border-gray-100 px-4 py-2.5 text-xs font-extrabold text-navy">
          <X className="w-4 h-4" />
          Cancel
        </button>
      </div>
    </form>
  );
}

function LocaleTabs({ active, onChange }) {
  return (
    <div className="flex flex-wrap gap-2">
      {LOCALES.map((locale) => (
        <button
          type="button"
          key={locale}
          onClick={() => onChange(locale)}
          className={`rounded-lg px-3 py-1.5 text-[11px] font-black uppercase ${
            active === locale ? "bg-primary text-white" : "bg-gray-50 text-gray-500 hover:text-navy"
          }`}
        >
          {locale}
        </button>
      ))}
    </div>
  );
}

function TextField({ label, value, onChange, type = "text", required = false }) {
  return (
    <label className="space-y-1 text-xs font-bold text-gray-500 capitalize">
      {label}
      <input
        type={type}
        value={value}
        required={required}
        onChange={(e) => onChange(e.target.value)}
        className="w-full rounded-xl border border-gray-100 px-3 py-2.5 text-sm text-navy outline-none focus:border-primary"
      />
    </label>
  );
}

function TextArea({ label, value, onChange }) {
  return (
    <label className="space-y-1 text-xs font-bold text-gray-500">
      {label}
      <textarea
        value={value}
        rows={4}
        onChange={(e) => onChange(e.target.value)}
        className="w-full rounded-xl border border-gray-100 px-3 py-2.5 text-sm text-navy outline-none focus:border-primary"
      />
    </label>
  );
}

function normalizeProfileForm(item = {}) {
  const translations = emptyTranslations();
  (item.translations || []).forEach((translation) => {
    translations[translation.locale] = {
      full_name: translation.full_name || "",
      position: translation.position || "",
      degree: translation.degree || "",
      office_hours: translation.office_hours || "",
      about: translation.about || "",
      details: translation.details || "",
      achievements: Array.isArray(translation.achievements) ? translation.achievements : [],
    };
  });

  return {
    slug: item.slug || "",
    photo: item.photo || "",
    email: item.email || "",
    phone: item.phone || "",
    telegram_url: item.telegram_url || "",
    sort_order: Number(item.sort_order || 0),
    is_rector: Boolean(item.is_rector),
    is_published: Boolean(item.is_published ?? true),
    translations,
  };
}

function normalizeSettings(data = {}) {
  const translations = emptySettingsTranslations();
  (data.translations || []).forEach((translation) => {
    translations[translation.locale] = {
      ...translations[translation.locale],
      ...translation,
    };
  });

  return {
    home_limit: Number(data.home_limit || 6),
    is_active: Boolean(data.is_active ?? true),
    translations,
  };
}
