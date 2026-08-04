import React, { useCallback, useEffect, useMemo, useState } from "react";
import { Building2, Camera, CheckCircle2, Edit3, Plus, Save, Settings as SettingsIcon, SlidersHorizontal, Trash2, UploadCloud, UserRound, X } from "lucide-react";
import { centerService } from "../../../services/centerService";
import { apanelService } from "../../../services/apanelService";
import { publicAssetUrl } from "../../../lib/api";
import ConfirmDialog from "../components/ConfirmDialog";
import ApanelStatsCards from "../components/ApanelStatsCards";
import { useApanelLocaleOptions } from "../utils/locales";
import { useLanguage } from "../../../context/LanguageContext";

const emptyTranslations = (localeCodes) =>
  Object.fromEntries(
    localeCodes.map((locale) => [
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

const emptyForm = (localeCodes) => ({
  slug: "",
  image: "",
  email: "",
  phone: "",
  sort_order: 0,
  is_active: true,
  translations: emptyTranslations(localeCodes),
});

const emptySettingsTranslations = (localeCodes) =>
  Object.fromEntries(
    localeCodes.map((locale) => [
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

const emptySettingsForm = (localeCodes) => ({
  is_active: true,
  translations: emptySettingsTranslations(localeCodes),
});

const storageUrl = (path) => {
  return publicAssetUrl(path);
};

export default function ApanelCenters() {
  const { t } = useLanguage();
  const localeOptions = useApanelLocaleOptions();
  const localeCodes = useMemo(() => localeOptions.map((locale) => locale.code), [localeOptions]);
  const primaryLocale = localeCodes[0] || "";
  const [tab, setTab] = useState("list"); // "list", "editor", "settings"
  const [items, setItems] = useState([]);
  const [editing, setEditing] = useState(null);
  const [form, setForm] = useState(() => emptyForm([]));
  const [settingsForm, setSettingsForm] = useState(() => emptySettingsForm([]));
  const [activeLocale, setActiveLocale] = useState("");
  const [saving, setSaving] = useState(false);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [loading, setLoading] = useState(true);
  const [toast, setToast] = useState(null);

  useEffect(() => {
    if (!localeCodes.length) return;
    setActiveLocale((current) => (localeCodes.includes(current) ? current : primaryLocale));
    setForm((current) => ({
      ...current,
      translations: {
        ...emptyTranslations(localeCodes),
        ...current.translations,
      },
    }));
    setSettingsForm((current) => ({
      ...current,
      translations: {
        ...emptySettingsTranslations(localeCodes),
        ...current.translations,
      },
    }));
  }, [localeCodes, primaryLocale]);

  const sortedItems = useMemo(
    () => [...items].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0)),
    [items],
  );
  const pageStats = useMemo(
    () => [
      {
        label: "Total centers",
        value: items.length,
        hint: "Centers and departments",
        icon: Building2,
        tone: "text-blue-600 bg-blue-50 border-blue-100",
      },
      {
        label: "Active",
        value: items.filter((item) => item.is_active).length,
        hint: "Published center pages",
        icon: CheckCircle2,
        tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
      },
      {
        label: "With director",
        value: items.filter((item) =>
          Object.values(item.translations || {}).some((translation) => translation?.head),
        ).length,
        hint: "Profiles linked to staff cards",
        icon: UserRound,
        tone: "text-violet-600 bg-violet-50 border-violet-100",
      },
      {
        label: "Settings",
        value: settingsForm.is_active ? "Active" : "Hidden",
        hint: `${localeCodes.length} locales configured`,
        icon: SlidersHorizontal,
        tone: "text-amber-600 bg-amber-50 border-amber-100",
      },
    ],
    [items, localeCodes.length, settingsForm.is_active],
  );

  const showToast = (message) => {
    setToast(message);
    setTimeout(() => {
      setToast(null);
    }, 3000);
  };

  const load = useCallback(async () => {
    if (!localeCodes.length) return;

    setLoading(true);
    try {
      const [data, settingsData] = await Promise.all([
        centerService.adminGetCenters(),
        centerService.adminGetSettings(),
      ]);
      setItems(data || []);
      if (settingsData) {
        setSettingsForm(normalizeSettingsForm(settingsData, localeCodes));
      }
    } catch (err) {
      console.error("Failed to load centers:", err);
    } finally {
      setLoading(false);
    }
  }, [localeCodes]);

  useEffect(() => {
    load();
  }, [load]);

  const startCreate = () => {
    setEditing(null);
    setForm(emptyForm(localeCodes));
    setActiveLocale(primaryLocale);
    setTab("editor");
  };

  const startEdit = async (item) => {
    setLoading(true);
    try {
      const fullItem = await centerService.adminGetCenter(item.numeric_id);
      setEditing(item);
      setForm(normalizeCenterForm(fullItem, localeCodes));
      setActiveLocale(primaryLocale);
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
        showToast(t("apanel.centers.updated"));
      } else {
        await centerService.adminCreateCenter(form);
        showToast(t("apanel.centers.created"));
      }
      await load();
      setTab("list");
      setEditing(null);
      setForm(emptyForm(localeCodes));
    } catch (err) {
      console.error("Failed to save center:", err);
      showToast(t("apanel.centers.saveError"));
    } finally {
      setSaving(false);
    }
  };

  const saveSettings = async (event) => {
    event.preventDefault();
    setSaving(true);
    try {
      await centerService.adminUpdateSettings(settingsForm);
      showToast(t("apanel.centers.settingsUpdated"));
      await load();
      setTab("list");
    } catch (err) {
      console.error("Failed to save settings:", err);
      showToast(t("apanel.centers.settingsSaveError"));
    } finally {
      setSaving(false);
    }
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    try {
      await centerService.adminDeleteCenter(pendingDelete.numeric_id);
      showToast(t("apanel.centers.deleted"));
      setPendingDelete(null);
      await load();
    } catch (err) {
      console.error("Failed to delete center:", err);
      showToast(t("apanel.centers.deleteError"));
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
                  setActiveLocale(primaryLocale);
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

      <ApanelStatsCards items={pageStats} />

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
                  <th className="px-6 py-4">{t("apanel.centers.sort")}</th>
                  <th className="px-6 py-4">{t("apanel.centers.name")}</th>
                  <th className="px-6 py-4">{t("apanel.centers.slug")}</th>
                  <th className="px-6 py-4">{t("apanel.centers.headStaff")}</th>
                  <th className="px-6 py-4">{t("apanel.centers.status")}</th>
                  <th className="px-6 py-4 text-right">{t("apanel.centers.actions")}</th>
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
            <LocaleTabs active={activeLocale} onChange={setActiveLocale} localeOptions={localeOptions} />
          </div>

          <form onSubmit={saveCenter} className="space-y-6">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
              {/* Left Column: Core Fields */}
              <div className="space-y-4">
                <TextField
                  label={t("apanel.centers.label.slug")}
                  value={form.slug}
                  required
                  onChange={(val) => setForm((prev) => ({ ...prev, slug: val }))}
                />

                <TextField
                  label={t("apanel.centers.label.email")}
                  type="email"
                  value={form.email}
                  onChange={(val) => setForm((prev) => ({ ...prev, email: val }))}
                />

                <TextField
                  label={t("apanel.centers.label.phone")}
                  value={form.phone}
                  onChange={(val) => setForm((prev) => ({ ...prev, phone: val }))}
                />

                <div className="grid grid-cols-2 gap-4">
                  <TextField
                    label={t("apanel.centers.label.sortOrder")}
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
                      <option value="1">{t("status.active")}</option>
                      <option value="0">{t("status.draft")}</option>
                    </select>
                  </label>
                </div>
              </div>

              {/* Right Column: Image Upload */}
              <div className="space-y-2">
                <span className="text-xs font-bold text-gray-500">{t("apanel.centers.bannerProfileImage")}</span>
                <div className="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-100 p-6 text-center hover:border-primary/50 transition-colors relative group min-h-55">
                  {form.image ? (
                    <>
                      <img
                        src={storageUrl(form.image)}
                        alt="Preview"
                        className="max-h-45 w-full rounded-xl object-cover"
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
                      <span className="text-xs font-bold text-gray-400">{t("apanel.centers.clickUploadPhoto")}</span>
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
                  label={t("apanel.centers.label.nameTitle")}
                  value={form.translations[activeLocale]?.name || ""}
                  required={activeLocale === primaryLocale}
                  onChange={(val) => setTranslation("name", val)}
                />

                <TextField
                  label={t("apanel.centers.label.headOfCentreDepartment")}
                  value={form.translations[activeLocale]?.head || ""}
                  onChange={(val) => setTranslation("head", val)}
                />
              </div>

              <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <TextField
                  label={t("apanel.centers.label.headTitleJobPosition")}
                  value={form.translations[activeLocale]?.head_title || ""}
                  onChange={(val) => setTranslation("head_title", val)}
                />

                <TextField
                  label={t("apanel.centers.label.receptionOfficeHours")}
                  value={form.translations[activeLocale]?.office_hours || ""}
                  onChange={(val) => setTranslation("office_hours", val)}
                />
              </div>

              <TextArea
                label={t("apanel.centers.label.aboutTheCentreDepartment")}
                value={form.translations[activeLocale]?.about || ""}
                onChange={(val) => setTranslation("about", val)}
              />

              <TextArea
                label={t("apanel.centers.label.supervisorSpecificDescriptionIfLeftEmptyWillUseTheDefaultDescription")}
                value={form.translations[activeLocale]?.head_description || ""}
                onChange={(val) => setTranslation("head_description", val)}
              />

              <TextArea
                label={t("apanel.centers.label.functionsAndActivitiesOnePerLine")}
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
                  setForm(emptyForm(localeCodes));
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
            <LocaleTabs active={activeLocale} onChange={setActiveLocale} localeOptions={localeOptions} />
          </div>

          <form onSubmit={saveSettings} className="space-y-6">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
              <TextField
                label={t("apanel.centers.label.sidebarMenuCategoryTitle")}
                value={settingsForm.translations[activeLocale]?.sidebar_title || ""}
                required
                onChange={(val) => setSettingsTranslation("sidebar_title", val)}
              />

              <TextField
                label={t("apanel.centers.label.structureBreadcrumbSectionLabel")}
                value={settingsForm.translations[activeLocale]?.structure_label || ""}
                required
                onChange={(val) => setSettingsTranslation("structure_label", val)}
              />

              <TextField
                label={t("apanel.centers.label.aboutSectionHeaderLabel")}
                value={settingsForm.translations[activeLocale]?.about_label || ""}
                required
                onChange={(val) => setSettingsTranslation("about_label", val)}
              />

              <TextField
                label={t("apanel.centers.label.staffAndMembersTitleLabel")}
                value={settingsForm.translations[activeLocale]?.staff_label || ""}
                required
                onChange={(val) => setSettingsTranslation("staff_label", val)}
              />

              <TextField
                label={t("apanel.centers.label.functionsAndMissionsHeadingLabel")}
                value={settingsForm.translations[activeLocale]?.mission_label || ""}
                required
                onChange={(val) => setSettingsTranslation("mission_label", val)}
              />

              <TextField
                label={t("apanel.centers.label.supportWidgetTitle")}
                value={settingsForm.translations[activeLocale]?.support_title || ""}
                required
                onChange={(val) => setSettingsTranslation("support_title", val)}
              />

              <TextField
                label={t("apanel.centers.label.contactUniversityButtonLabel")}
                value={settingsForm.translations[activeLocale]?.contact_btn_label || ""}
                required
                onChange={(val) => setSettingsTranslation("contact_btn_label", val)}
              />

              <TextField
                label={t("apanel.centers.label.missionFunctionBadgeLabel")}
                value={settingsForm.translations[activeLocale]?.function_badge_label || ""}
                required
                onChange={(val) => setSettingsTranslation("function_badge_label", val)}
              />
            </div>

            <TextArea
              label={t("apanel.centers.label.defaultStaffSupervisingDescription")}
              value={settingsForm.translations[activeLocale]?.default_head_desc || ""}
              onChange={(val) => setSettingsTranslation("default_head_desc", val)}
            />

            <TextArea
              label={t("apanel.centers.label.supportWidgetDescriptionText")}
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
        title={t("apanel.centers.title.deleteCentreDepartment")}
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

function LocaleTabs({ active, onChange, localeOptions }) {
  return (
    <div className="flex flex-wrap gap-2">
      {localeOptions.map((locale) => (
        <button
          type="button"
          key={locale.code}
          onClick={() => onChange(locale.code)}
          className={`rounded-lg px-3 py-1.5 text-[11px] font-black uppercase transition-colors cursor-pointer ${
            active === locale.code ? "bg-primary text-white" : "bg-gray-55 text-gray-500 hover:text-navy hover:bg-gray-100"
          }`}
        >
          {locale.label}
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

function normalizeCenterForm(item = {}, localeCodes) {
  const translations = emptyTranslations(localeCodes);
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

function normalizeSettingsForm(data = {}, localeCodes) {
  const translations = emptySettingsTranslations(localeCodes);
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
