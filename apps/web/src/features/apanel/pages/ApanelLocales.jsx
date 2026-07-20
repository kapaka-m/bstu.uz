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

const emptyForm = {
  code: "",
  name: "",
  native_name: "",
  direction: "ltr",
  is_active: true,
  sort_order: 0,
};

export default function ApanelLocales() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [editing, setEditing] = useState(null);
  const [isFormOpen, setIsFormOpen] = useState(false);
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
      setError(err?.message || "Failed to load locales.");
    } finally {
      setLoading(false);
    }
  }, []);

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
      setError(err?.message || "Failed to save locale.");
    } finally {
      setSaving(false);
    }
  };

  const deleteLocale = async (item) => {
    if (!window.confirm(`Delete locale ${item.code}?`)) return;

    try {
      await apanelService.delete("locales", item.id);
      fetchLocales();
    } catch (err) {
      setError(err?.message || "Failed to delete locale.");
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            Locales
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            Manage website languages, text direction, activation state, and display order.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button
            type="button"
            onClick={fetchLocales}
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
            Add Locale
          </button>
        </div>
      </div>

      {error && <FormError message={error} />}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {[
          ["Total Locales", stats.total, Globe2, "text-blue-600 bg-blue-50 border-blue-100"],
          ["Active Languages", stats.active, CheckCircle2, "text-emerald-600 bg-emerald-50 border-emerald-100"],
          ["RTL Languages", stats.rtl, Languages, "text-purple-600 bg-purple-50 border-purple-100"],
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
              <h2 className="text-lg font-extrabold text-navy">Language Registry</h2>
              <p className="text-xs font-semibold text-gray-400">
                Public language switcher follows these active locale records.
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
                    <th className="px-5 py-3 text-start">Code</th>
                    <th className="px-5 py-3 text-start">Language</th>
                    <th className="px-5 py-3 text-start">Native Name</th>
                    <th className="px-5 py-3 text-start">Direction</th>
                    <th className="px-5 py-3 text-start">Status</th>
                    <th className="px-5 py-3 text-start">Sort</th>
                    <th className="px-5 py-3 text-end">Actions</th>
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
                          {item.is_active ? "Active" : "Hidden"}
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
                            onClick={() => deleteLocale(item)}
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
                {editing ? "Edit Locale" : "Create Locale"}
              </h2>
              <button type="button" onClick={closeForm} className="text-gray-400 hover:text-navy cursor-pointer">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={saveLocale} className="p-5 space-y-4">
              <Field label="Code" value={form.code} onChange={(value) => setField("code", value)} placeholder="en, uz, ru, ar" disabled={Boolean(editing)} />
              <Field label="English Name" value={form.name} onChange={(value) => setField("name", value)} placeholder="English" />
              <Field label="Native Name" value={form.native_name} onChange={(value) => setField("native_name", value)} placeholder="English" />
              <label className="space-y-1.5 block">
                <span className="text-[10px] uppercase font-black tracking-wider text-gray-400">Direction</span>
                <select
                  value={form.direction}
                  onChange={(event) => setField("direction", event.target.value)}
                  className="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-semibold text-navy bg-white focus:outline-none focus:border-primary"
                >
                  <option value="ltr">LTR</option>
                  <option value="rtl">RTL</option>
                </select>
              </label>
              <Field label="Sort Order" type="number" value={form.sort_order} onChange={(value) => setField("sort_order", Number(value))} />
              <label className="flex min-h-11 items-center gap-2 rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-bold text-navy bg-white">
                <input
                  type="checkbox"
                  checked={form.is_active}
                  onChange={(event) => setField("is_active", event.target.checked)}
                  className="h-4 w-4 accent-primary"
                />
                Active in public language switcher
              </label>
              <button
                type="submit"
                disabled={saving}
                className="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover disabled:opacity-60 text-white px-5 py-3 rounded-xl text-xs font-extrabold cursor-pointer"
              >
                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                Save Locale
              </button>
            </form>
          </section>
        )}
      </div>
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
