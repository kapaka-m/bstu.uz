import React, { useCallback, useEffect, useMemo, useState } from "react";
import {
  CheckCircle2,
  Edit3,
  Globe2,
  Languages,
  Loader2,
  Plus,
  RefreshCw,
  Save,
  Trash2,
  X,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { useLanguage } from "../../../context/LanguageContext";
import ConfirmDialog from "../components/ConfirmDialog";

const emptyForm = {
  code: "",
  name: "",
  native_name: "",
  direction: "ltr",
  is_active: true,
  sort_order: 0,
};

export default function ApanelLocales() {
  const { t } = useLanguage();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [editing, setEditing] = useState(null);
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [form, setForm] = useState(emptyForm);

  const fetchLocales = useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const page = await apanelService.listPage("locales", {
        per_page: 100,
        sort_by: "sort_order",
        sort_dir: "asc",
      });
      setItems(page.items || []);
    } catch (err) {
      setError(err?.message || t("apanel.locales.loadFailed"));
    } finally {
      setLoading(false);
    }
  }, [t]);

  useEffect(() => {
    fetchLocales();
  }, [fetchLocales]);

  const stats = useMemo(() => {
    const active = items.filter((item) => item.is_active).length;
    const rtl = items.filter((item) => item.direction === "rtl").length;
    return { total: items.length, active, rtl };
  }, [items]);

  const startCreate = () => {
    setEditing(null);
    setForm({
      ...emptyForm,
      sort_order: items.length + 1,
    });
    setIsFormOpen(true);
  };

  const startEdit = (item) => {
    setEditing(item);
    setForm({
      code: item.code || "",
      name: item.name || "",
      native_name: item.native_name || "",
      direction: item.direction || "ltr",
      is_active: Boolean(item.is_active),
      sort_order: Number(item.sort_order || 0),
    });
    setIsFormOpen(true);
  };

  const closeForm = () => {
    setEditing(null);
    setIsFormOpen(false);
    setForm(emptyForm);
  };

  const setField = (field, value) => {
    setForm((current) => ({ ...current, [field]: value }));
  };

  const saveLocale = async (event) => {
    event.preventDefault();
    try {
      setSaving(true);
      setError("");
      const payload = {
        ...form,
        code: form.code.trim().toLowerCase(),
        sort_order: Number(form.sort_order || 0),
        is_active: Boolean(form.is_active),
      };

      if (editing) {
        await apanelService.update("locales", editing.id, payload);
      } else {
        await apanelService.create("locales", payload);
      }

      closeForm();
      fetchLocales();
    } catch (err) {
      setError(err?.message || t("apanel.locales.saveFailed"));
    } finally {
      setSaving(false);
    }
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    try {
      await apanelService.delete("locales", pendingDelete.id);
      setPendingDelete(null);
      fetchLocales();
    } catch (err) {
      setError(err?.message || t("apanel.locales.deleteFailed"));
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            {t("apanel.locales.title")}
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            {t("apanel.locales.subtitle")}
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button
            type="button"
            onClick={fetchLocales}
            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-navy text-xs font-extrabold hover:bg-gray-50 cursor-pointer"
          >
            <RefreshCw className="w-4 h-4" />
            {t("button.refresh")}
          </button>
          <button
            type="button"
            onClick={startCreate}
            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-extrabold hover:bg-primary-hover cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            {t("apanel.locales.add")}
          </button>
        </div>
      </div>

      {error && <FormError message={error} />}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {[
          [t("apanel.locales.totalLocales"), stats.total, Globe2, "text-blue-600 bg-blue-50 border-blue-100"],
          [t("apanel.locales.activeLanguages"), stats.active, CheckCircle2, "text-emerald-600 bg-emerald-50 border-emerald-100"],
          [t("apanel.locales.rtlLanguages"), stats.rtl, Languages, "text-purple-600 bg-purple-50 border-purple-100"],
        ].map(([label, value, Icon, color]) => (
          <div key={label} className="bg-white border border-gray-100 rounded-3xl p-5 flex items-center justify-between shadow-xs">
            <div>
              <p className="text-[10px] uppercase tracking-wider font-black text-gray-400">
                {label}
              </p>
              <p className="text-3xl font-black text-navy mt-2">{value}</p>
            </div>
            <div className={`w-12 h-12 rounded-2xl border flex items-center justify-center ${color}`}>
              <Icon className="w-5 h-5" />
            </div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section className="xl:col-span-2 bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
          <div className="p-5 border-b border-gray-100 flex items-center justify-between gap-4">
            <div>
              <h2 className="text-lg font-extrabold text-navy">{t("apanel.locales.registryTitle")}</h2>
              <p className="text-xs font-semibold text-gray-400">
                {t("apanel.locales.registrySubtitle")}
              </p>
            </div>
          </div>

          {loading ? (
            <div className="p-12 flex justify-center">
              <Loader2 className="w-7 h-7 text-primary animate-spin" />
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-400 font-black">
                  <tr>
                    <th className="px-5 py-3 text-start">{t("apanel.locales.code")}</th>
                    <th className="px-5 py-3 text-start">{t("apanel.locales.language")}</th>
                    <th className="px-5 py-3 text-start">{t("apanel.locales.nativeName")}</th>
                    <th className="px-5 py-3 text-start">{t("apanel.locales.direction")}</th>
                    <th className="px-5 py-3 text-start">{t("apanel.locales.status")}</th>
                    <th className="px-5 py-3 text-start">{t("apanel.locales.sort")}</th>
                    <th className="px-5 py-3 text-end">{t("apanel.locales.actions")}</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {items.map((item) => (
                    <tr key={item.id} className="hover:bg-gray-50/60">
                      <td className="px-5 py-4">
                        <span className="inline-flex min-w-10 justify-center rounded-xl bg-navy text-white px-2 py-1 text-xs font-black uppercase">
                          {item.code}
                        </span>
                      </td>
                      <td className="px-5 py-4 font-extrabold text-navy">{item.name}</td>
                      <td className="px-5 py-4 font-semibold text-gray-500">{item.native_name}</td>
                      <td className="px-5 py-4">
                        <span className="rounded-lg bg-gray-100 text-gray-600 px-2 py-1 text-[10px] font-black uppercase">
                          {item.direction}
                        </span>
                      </td>
                      <td className="px-5 py-4">
                        <span className={`rounded-lg px-2 py-1 text-[10px] font-black uppercase ${
                          item.is_active ? "bg-emerald-50 text-emerald-700" : "bg-gray-100 text-gray-500"
                        }`}>
                          {item.is_active ? t("status.active") : t("status.hidden")}
                        </span>
                      </td>
                      <td className="px-5 py-4 font-bold text-gray-400">{item.sort_order}</td>
                      <td className="px-5 py-4">
                        <div className="flex justify-end gap-2">
                          <button
                            type="button"
                            onClick={() => startEdit(item)}
                            className="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-navy cursor-pointer"
                          >
                            <Edit3 className="w-4 h-4" />
                          </button>
                          <button
                            type="button"
                            onClick={() => setPendingDelete(item)}
                            className="p-2 rounded-xl border border-rose-100 text-rose-500 hover:bg-rose-50 cursor-pointer"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </section>

        {isFormOpen && (
          <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
            <div className="p-5 border-b border-gray-100 flex items-center justify-between">
              <h2 className="text-lg font-extrabold text-navy">
                {editing ? t("apanel.locales.edit") : t("apanel.locales.create")}
              </h2>
              <button type="button" onClick={closeForm} className="text-gray-400 hover:text-navy cursor-pointer">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={saveLocale} className="p-5 space-y-4">
              <Field label={t("apanel.locales.code")} value={form.code} onChange={(value) => setField("code", value)} placeholder={t("apanel.locales.codePlaceholder")} disabled={Boolean(editing)} />
              <Field label={t("apanel.locales.englishName")} value={form.name} onChange={(value) => setField("name", value)} placeholder={t("apanel.locales.englishNamePlaceholder")} />
              <Field label={t("apanel.locales.nativeName")} value={form.native_name} onChange={(value) => setField("native_name", value)} placeholder={t("apanel.locales.nativeNamePlaceholder")} />
              <label className="space-y-1.5 block">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">{t("apanel.locales.direction")}</span>
                <select
                  value={form.direction}
                  onChange={(event) => setField("direction", event.target.value)}
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy bg-white focus:outline-none focus:border-primary"
                >
                  <option value="ltr">LTR</option>
                  <option value="rtl">RTL</option>
                </select>
              </label>
              <Field label={t("apanel.locales.sortOrder")} type="number" value={form.sort_order} onChange={(value) => setField("sort_order", Number(value))} />
              <label className="flex min-h-11 items-center gap-2 rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-bold text-navy bg-white">
                <input
                  type="checkbox"
                  checked={form.is_active}
                  onChange={(event) => setField("is_active", event.target.checked)}
                  className="h-4 w-4 accent-primary"
                />
                {t("apanel.locales.activeInSwitcher")}
              </label>
              <button
                type="submit"
                disabled={saving}
                className="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover disabled:opacity-60 text-white px-5 py-3 rounded-xl text-xs font-extrabold cursor-pointer"
              >
                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                {t("apanel.locales.save")}
              </button>
            </form>
          </section>
        )}
      </div>
      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title={t("apanel.locales.deleteTitle")}
        message={`${t("apanel.locales.deleteMessage")} ${pendingDelete?.code || t("apanel.locales.thisLocale")}`}
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}

function Field({ label, value, onChange, type = "text", placeholder = "", disabled = false }) {
  return (
    <label className="space-y-1.5 block">
      <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">{label}</span>
      <input
        type={type}
        value={value ?? ""}
        disabled={disabled}
        placeholder={placeholder}
        onChange={(event) => onChange(event.target.value)}
        className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy bg-white focus:outline-none focus:border-primary disabled:bg-gray-50 disabled:text-gray-400"
      />
    </label>
  );
}
