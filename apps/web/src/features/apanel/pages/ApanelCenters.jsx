import React, { useEffect, useMemo, useState } from "react";
import { Camera, Edit3, Plus, Save, Trash2, UploadCloud, X, Settings as SettingsIcon, ListCollapse } from "lucide-react";
import { centerService } from "../../../services/centerService";
import { apanelService } from "../../../services/apanelService";
import ConfirmDialog from "../components/ConfirmDialog";

const LOCALES = ["en", "uz", "ru", "ar"];

const emptyTranslations = () =>
  Object.fromEntries(
    LOCALES.map((locale) => [
      locale,
      {
        name: "",
        head: "",
        head_title: "",
        office_hours: "",
        about: "",
        functions: [],
        head_description: "",
      },
    ]),
  );

const emptyForm = () => ({
  slug: "",
  image: "",
  email: "",
  phone: "",
  sort_order: 0,
  is_active: true,
  translations: emptyTranslations(),
});

const emptySettingsTranslations = () =>
  Object.fromEntries(
    LOCALES.map((locale) => [
      locale,
      {
        sidebar_title: "",
        structure_label: "",
        about_label: "",
        staff_label: "",
        default_head_desc: "",
        mission_label: "",
        support_title: "",
        support_desc: "",
        contact_btn_label: "",
        function_badge_label: "",
      },
    ]),
  );

const emptySettingsForm = () => ({
  is_active: true,
  translations: emptySettingsTranslations(),
});

const storageUrl = (path) => {
  if (!path) return "";
  if (/^https?:\/\//i.test(path) || path.startsWith("/")) return path;
  const apiBase = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1";
  return `${apiBase.replace(/\/api\/v1\/?$/, "")}/storage/${path.replace(/^public\//, "")}`;
};

export default function ApanelCenters() {
  const [tab, setTab] = useState("list"); // "list", "editor", "settings"
  const [items, setItems] = useState([]);
  const [editing, setEditing] = useState(null);
  const [form, setForm] = useState(emptyForm());
  const [settingsForm, setSettingsForm] = useState(emptySettingsForm());
  const [activeLocale, setActiveLocale] = useState("en");
  const [saving, setSaving] = useState(false);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [loading, setLoading] = useState(true);
  const [toast, setToast] = useState(null);

  const sortedItems = useMemo(
    () => [...items].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0)),
    [items],
  );

  const showToast = (message) => {
    setToast(message);
    setTimeout(() => {
      setToast(null);
    }, 3000);
  };

  const load = async () => {
    setLoading(true);
    try {
      const [data, settingsData] = await Promise.all([
        centerService.adminGetCenters(),
        centerService.adminGetSettings(),
      ]);
      setItems(data || []);
      if (settingsData) {
        setSettingsForm(normalizeSettingsForm(settingsData));
      }
    } catch (err) {
      console.error("Failed to load centers:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, []);

  const startCreate = () => {
    setEditing(null);
    setForm(emptyForm());
    setActiveLocale("en");
    setTab("editor");
  };

  const startEdit = async (item) => {
    setLoading(true);
    try {
      const fullItem = await centerService.adminGetCenter(item.numeric_id);
      setEditing(item);
      setForm(normalizeCenterForm(fullItem));
      setActiveLocale("en");
      setTab("editor");
    } catch (err) {
      console.error("Failed to load center details:", err);
    } finally {
      setLoading(false);
    }
  };

  const saveCenter = async (event) => {
    event.preventDefault();
    setSaving(true);
    try {
      if (editing?.numeric_id) {
        await centerService.adminUpdateCenter(editing.numeric_id, form);
        showToast("Centre / Department updated successfully!");
      } else {
        await centerService.adminCreateCenter(form);
        showToast("Centre / Department created successfully!");
      }
      await load();
      setTab("list");
      setEditing(null);
      setForm(emptyForm());
    } catch (err) {
      console.error("Failed to save center:", err);
      showToast("Error occurred while saving.");
    } finally {
      setSaving(false);
    }
  };

  const saveSettings = async (event) => {
    event.preventDefault();
    setSaving(true);
    try {
      await centerService.adminUpdateSettings(settingsForm);
      showToast("Settings updated successfully!");
      await load();
      setTab("list");
    } catch (err) {
      console.error("Failed to save settings:", err);
      showToast("Error occurred while saving settings.");
    } finally {
      setSaving(false);
    }
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    try {
      await centerService.adminDeleteCenter(pendingDelete.numeric_id);
      showToast("Centre / Department deleted successfully.");
      setPendingDelete(null);
      await load();
    } catch (err) {
      console.error("Failed to delete center:", err);
      showToast("Error occurred while deleting.");
    }
  };

  const uploadImage = async (file) => {
    if (!file) return;
    try {
      const uploaded = await apanelService.uploadMedia(file, {
        alt_key: `centers.${form.slug || "center"}.image`,
        type: "image",
      });
      setForm((prev) => ({ ...prev, image: uploaded?.path || uploaded?.data?.path || "" }));
    } catch (err) {
      console.error("Image upload failed:", err);
    }
  };

  return (
    <div className="space-y-6">
      {/* Toast Notification */}
      {toast && (
        <div className="fixed top-6 right-6 z-9999 flex items-center gap-3 rounded-2xl bg-navy/90 border border-navy/10 px-5 py-3 text-xs font-bold text-white shadow-xl animate-in fade-in slide-in-from-top-4 duration-300 backdrop-blur-md">
          <div className="h-2 w-2 rounded-full bg-primary animate-pulse" />
          {toast}
        </div>
      )}

      {/* Header */}
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-black tracking-tight text-navy">
            Centres and Departments
          </h1>
          <p className="text-xs font-bold text-gray-400">
            Manage structural centers, student offices, and general settings.
          </p>
        </div>

        <div className="flex flex-wrap gap-2">
          {tab === "list" && (
            <>
              <button
                onClick={() => {
                  setActiveLocale("en");
                  setTab("settings");
                }}
                className="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-gray-100 bg-white px-4 py-2.5 text-xs font-extrabold text-navy transition-all hover:bg-gray-55"
              >
                <SettingsIcon className="w-4 h-4 text-gray-500" />
                Customize Labels
              </button>
              <button
                onClick={startCreate}
                className="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary/10 transition-all hover:bg-primary-dark"
              >
                <Plus className="w-4 h-4" />
                Add Centre / Department
              </button>
            </>
          )}
        </div>
      </div>

      {loading && tab === "list" ? (
        <div className="flex h-64 items-center justify-center rounded-3xl border border-gray-100 bg-white shadow-sm">
          <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
        </div>
      ) : tab === "list" ? (
        <div className="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
          <div className="overflow-x-auto">
            <table className="w-full border-collapse text-left text-sm text-gray-500">
              <thead className="bg-gray-55 text-xs font-bold uppercase text-navy border-b border-gray-100">
                <tr>
                  <th className="px-6 py-4">Sort</th>
                  <th className="px-6 py-4">Name</th>
                  <th className="px-6 py-4">Slug</th>
                  <th className="px-6 py-4">Head / Staff</th>
                  <th className="px-6 py-4">Status</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 font-semibold">
                {sortedItems.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-6 py-10 text-center font-bold text-gray-400">
                      No centers or departments found.
                    </td>
                  </tr>
                ) : (
                  sortedItems.map((item) => (
                    <tr key={item.slug} className="hover:bg-gray-50/50">
                      <td className="px-6 py-4">
                        <span className="inline-flex items-center justify-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-navy">
                          {item.sort_order}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-3">
                          {item.image && (
                            <img
                              src={storageUrl(item.image)}
                              alt={item.name}
                              className="h-10 w-10 rounded-xl object-cover border border-gray-100"
                            />
                          )}
                          <div>
                            <div className="font-extrabold text-navy">{item.name}</div>
                            <div className="text-[10px] text-gray-400 font-bold">{item.email || "No Email"}</div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="font-mono text-xs text-gray-500 bg-gray-50 px-2 py-0.5 rounded">
                          {item.slug}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <div>
                          <div className="text-navy">{item.head || "Not Set"}</div>
                          <div className="text-[10px] text-primary font-bold uppercase">{item.headTitle || "No Title"}</div>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span
                          className={`inline-flex rounded-full px-2 py-1 text-[10px] font-black uppercase ${
                            item.is_active
                              ? "bg-green-50 text-green-700"
                              : "bg-red-50 text-red-600"
                          }`}
                        >
                          {item.is_active ? "Active" : "Draft"}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <div className="flex justify-end gap-2">
                          <button
                            onClick={() => startEdit(item)}
                            className="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-gray-100 bg-white text-gray-500 transition-colors hover:text-primary hover:bg-gray-50"
                          >
                            <Edit3 className="w-4 h-4" />
                          </button>
                          <button
                            onClick={() => setPendingDelete(item)}
                            className="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-gray-100 bg-white text-gray-500 transition-colors hover:text-red-600 hover:bg-red-50"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      ) : tab === "editor" ? (
        /* EDITOR TAB */
        <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
          <div className="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
              <h2 className="text-lg font-black text-navy">
                {editing ? "Edit Centre / Department" : "New Centre / Department"}
              </h2>
              <p className="text-xs font-bold text-gray-400">
                Provide basic details and translations in all 4 languages.
              </p>
            </div>
            <LocaleTabs active={activeLocale} onChange={setActiveLocale} />
          </div>

          <form onSubmit={saveCenter} className="space-y-6">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
              {/* Left Column: Core Fields */}
              <div className="space-y-4">
                <TextField
                  label="Slug"
                  value={form.slug}
                  required
                  onChange={(val) => setForm((prev) => ({ ...prev, slug: val }))}
                />

                <TextField
                  label="Email"
                  type="email"
                  value={form.email}
                  onChange={(val) => setForm((prev) => ({ ...prev, email: val }))}
                />

                <TextField
                  label="Phone"
                  value={form.phone}
                  onChange={(val) => setForm((prev) => ({ ...prev, phone: val }))}
                />

                <div className="grid grid-cols-2 gap-4">
                  <TextField
                    label="Sort Order"
                    type="number"
                    value={form.sort_order}
                    onChange={(val) => setForm((prev) => ({ ...prev, sort_order: Number(val) }))}
                  />

                  <label className="flex flex-col justify-end pb-1 text-xs font-bold text-gray-500">
                    Status
                    <select
                      value={form.is_active ? "1" : "0"}
                      onChange={(e) => setForm((prev) => ({ ...prev, is_active: e.target.value === "1" }))}
                      className="mt-1 rounded-xl border border-gray-100 px-3 py-2.5 text-sm text-navy outline-none focus:border-primary"
                    >
                      <option value="1">Active</option>
                      <option value="0">Draft</option>
                    </select>
                  </label>
                </div>
              </div>

              {/* Right Column: Image Upload */}
              <div className="space-y-2">
                <span className="text-xs font-bold text-gray-500">Banner / Profile Image</span>
                <div className="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-100 p-6 text-center hover:border-primary/50 transition-colors relative group min-h-[220px]">
                  {form.image ? (
                    <>
                      <img
                        src={storageUrl(form.image)}
                        alt="Preview"
                        className="max-h-[180px] w-full rounded-xl object-cover"
                      />
                      <label className="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 rounded-2xl transition-opacity cursor-pointer">
                        <Camera className="w-8 h-8 text-white" />
                        <input
                          type="file"
                          accept="image/*"
                          className="hidden"
                          onChange={(e) => uploadImage(e.target.files?.[0])}
                        />
                      </label>
                    </>
                  ) : (
                    <label className="flex flex-col items-center justify-center gap-2 cursor-pointer w-full h-full py-8">
                      <UploadCloud className="w-10 h-10 text-gray-300" />
                      <span className="text-xs font-bold text-gray-400">Click to upload photo</span>
                      <input
                        type="file"
                        accept="image/*"
                        className="hidden"
                        onChange={(e) => uploadImage(e.target.files?.[0])}
                      />
                    </label>
                  )}
                </div>
              </div>
            </div>

            {/* TRANSLATION SECTION */}
            <div className="rounded-2xl bg-gray-55 p-6 space-y-4">
              <div className="flex items-center gap-2 border-b border-gray-100 pb-2">
                <span className="text-xs font-black uppercase text-primary bg-primary-light px-2 py-0.5 rounded">
                  {activeLocale}
                </span>
                <span className="text-xs font-bold text-gray-500">
                  Translation Fields
                </span>
              </div>

              <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <TextField
                  label="Name / Title"
                  value={form.translations[activeLocale]?.name || ""}
                  required={activeLocale === "en"}
                  onChange={(val) => setTranslation("name", val)}
                />

                <TextField
                  label="Head of Centre / Department"
                  value={form.translations[activeLocale]?.head || ""}
                  onChange={(val) => setTranslation("head", val)}
                />
              </div>

              <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <TextField
                  label="Head Title / Job Position"
                  value={form.translations[activeLocale]?.head_title || ""}
                  onChange={(val) => setTranslation("head_title", val)}
                />

                <TextField
                  label="Reception / Office Hours"
                  value={form.translations[activeLocale]?.office_hours || ""}
                  onChange={(val) => setTranslation("office_hours", val)}
                />
              </div>

              <TextArea
                label="About the Centre / Department"
                value={form.translations[activeLocale]?.about || ""}
                onChange={(val) => setTranslation("about", val)}
              />

              <TextArea
                label="Supervisor Specific Description (If left empty, will use the default description)"
                value={form.translations[activeLocale]?.head_description || ""}
                onChange={(val) => setTranslation("head_description", val)}
              />

              <TextArea
                label="Functions & Activities (One per line)"
                value={(form.translations[activeLocale]?.functions || []).join("\n")}
                onChange={(val) =>
                  setTranslation(
                    "functions",
                    val
                      .split("\n")
                      .map((line) => line.trim())
                      .filter(Boolean),
                  )
                }
              />
            </div>

            {/* Form actions */}
            <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4">
              <button
                type="submit"
                disabled={saving}
                className="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary/10 transition-all hover:bg-primary-dark disabled:opacity-50"
              >
                <Save className="w-4 h-4" />
                {saving ? "Saving..." : "Save"}
              </button>
              <button
                type="button"
                onClick={() => {
                  setTab("list");
                  setEditing(null);
                  setForm(emptyForm());
                }}
                className="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-gray-100 bg-white px-4 py-2.5 text-xs font-extrabold text-navy hover:bg-gray-50"
              >
                <X className="w-4 h-4" />
                Cancel
              </button>
            </div>
          </form>
        </div>
      ) : (
        /* CUSTOM SETTINGS TAB */
        <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
          <div className="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
              <h2 className="text-lg font-black text-navy flex items-center gap-2">
                <SettingsIcon className="w-5 h-5 text-primary" />
                Customize Static Labels & Settings
              </h2>
              <p className="text-xs font-bold text-gray-400">
                Change buttons, sidebar titles, and standard support card descriptions for all 4 languages.
              </p>
            </div>
            <LocaleTabs active={activeLocale} onChange={setActiveLocale} />
          </div>

          <form onSubmit={saveSettings} className="space-y-6">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
              <TextField
                label="Sidebar / Menu Category Title"
                value={settingsForm.translations[activeLocale]?.sidebar_title || ""}
                required
                onChange={(val) => setSettingsTranslation("sidebar_title", val)}
              />

              <TextField
                label="Structure Breadcrumb Section Label"
                value={settingsForm.translations[activeLocale]?.structure_label || ""}
                required
                onChange={(val) => setSettingsTranslation("structure_label", val)}
              />

              <TextField
                label="About Section Header Label"
                value={settingsForm.translations[activeLocale]?.about_label || ""}
                required
                onChange={(val) => setSettingsTranslation("about_label", val)}
              />

              <TextField
                label="Staff & Members Title Label"
                value={settingsForm.translations[activeLocale]?.staff_label || ""}
                required
                onChange={(val) => setSettingsTranslation("staff_label", val)}
              />

              <TextField
                label="Functions & Missions Heading Label"
                value={settingsForm.translations[activeLocale]?.mission_label || ""}
                required
                onChange={(val) => setSettingsTranslation("mission_label", val)}
              />

              <TextField
                label="Support Widget Title"
                value={settingsForm.translations[activeLocale]?.support_title || ""}
                required
                onChange={(val) => setSettingsTranslation("support_title", val)}
              />

              <TextField
                label="Contact University Button Label"
                value={settingsForm.translations[activeLocale]?.contact_btn_label || ""}
                required
                onChange={(val) => setSettingsTranslation("contact_btn_label", val)}
              />

              <TextField
                label="Mission / Function Badge Label"
                value={settingsForm.translations[activeLocale]?.function_badge_label || ""}
                required
                onChange={(val) => setSettingsTranslation("function_badge_label", val)}
              />
            </div>

            <TextArea
              label="Default Staff Supervising Description"
              value={settingsForm.translations[activeLocale]?.default_head_desc || ""}
              onChange={(val) => setSettingsTranslation("default_head_desc", val)}
            />

            <TextArea
              label="Support Widget Description text"
              value={settingsForm.translations[activeLocale]?.support_desc || ""}
              onChange={(val) => setSettingsTranslation("support_desc", val)}
            />

            {/* Form actions */}
            <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4">
              <button
                type="submit"
                disabled={saving}
                className="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white shadow-md shadow-primary/10 transition-all hover:bg-primary-dark disabled:opacity-50"
              >
                <Save className="w-4 h-4" />
                {saving ? "Saving Settings..." : "Save Settings"}
              </button>
              <button
                type="button"
                onClick={() => {
                  setTab("list");
                }}
                className="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-gray-100 bg-white px-4 py-2.5 text-xs font-extrabold text-navy hover:bg-gray-55"
              >
                <X className="w-4 h-4" />
                Cancel
              </button>
            </div>
          </form>
        </div>
      )}

      {/* CONFIRM DELETE DIALOG */}
      <ConfirmDialog
        isOpen={pendingDelete !== null}
        title="Delete Centre / Department"
        message={`Are you sure you want to delete "${pendingDelete?.name}"? This action cannot be undone.`}
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );

  function setTranslation(key, value) {
    setForm((prev) => {
      const trans = { ...prev.translations };
      trans[activeLocale] = {
        ...trans[activeLocale],
        [key]: value,
      };
      return {
        ...prev,
        translations: trans,
      };
    });
  }

  function setSettingsTranslation(key, value) {
    setSettingsForm((prev) => {
      const trans = { ...prev.translations };
      trans[activeLocale] = {
        ...trans[activeLocale],
        [key]: value,
      };
      return {
        ...prev,
        translations: trans,
      };
    });
  }
}

function LocaleTabs({ active, onChange }) {
  return (
    <div className="flex flex-wrap gap-2">
      {LOCALES.map((locale) => (
        <button
          type="button"
          key={locale}
          onClick={() => onChange(locale)}
          className={`rounded-lg px-3 py-1.5 text-[11px] font-black uppercase transition-colors cursor-pointer ${
            active === locale ? "bg-primary text-white" : "bg-gray-55 text-gray-500 hover:text-navy hover:bg-gray-100"
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
    <label className="space-y-1 text-xs font-bold text-gray-500 capitalize block">
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
    <label className="space-y-1 text-xs font-bold text-gray-500 block">
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

function normalizeCenterForm(item = {}) {
  const translations = emptyTranslations();
  (item.translations || []).forEach((translation) => {
    translations[translation.locale] = {
      name: translation.name || "",
      head: translation.head || "",
      head_title: translation.head_title || "",
      office_hours: translation.office_hours || "",
      about: translation.about || "",
      functions: Array.isArray(translation.functions) ? translation.functions : [],
      head_description: translation.head_description || "",
    };
  });

  return {
    slug: item.slug || "",
    image: item.image || "",
    email: item.email || "",
    phone: item.phone || "",
    sort_order: Number(item.sort_order || 0),
    is_active: Boolean(item.is_active ?? true),
    translations,
  };
}

function normalizeSettingsForm(data = {}) {
  const translations = emptySettingsTranslations();
  (data.translations || []).forEach((translation) => {
    translations[translation.locale] = {
      sidebar_title: translation.sidebar_title || "",
      structure_label: translation.structure_label || "",
      about_label: translation.about_label || "",
      staff_label: translation.staff_label || "",
      default_head_desc: translation.default_head_desc || "",
      mission_label: translation.mission_label || "",
      support_title: translation.support_title || "",
      support_desc: translation.support_desc || "",
      contact_btn_label: translation.contact_btn_label || "",
      function_badge_label: translation.function_badge_label || "",
    };
  });

  return {
    is_active: Boolean(data.is_active ?? true),
    translations,
  };
}
