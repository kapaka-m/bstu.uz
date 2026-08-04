import React, { useCallback, useEffect, useMemo, useState } from "react";
import { CheckCircle2, GripVertical, Languages, Layers3, Navigation, Plus, Save, Trash2 } from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import ConfirmDialog from "../components/ConfirmDialog";
import ApanelStatsCards from "../components/ApanelStatsCards";
import { useApanelLocaleOptions } from "../utils/locales";
import { useLanguage } from "../../../context/LanguageContext";

const itemTypes = [
  { value: "link", label: "Direct Link" },
  { value: "action", label: "Header Action Button" },
  { value: "group", label: "Dropdown Group" },
  { value: "media", label: "Media Center Dropdown" },
  { value: "faculties", label: "Faculties Dropdown" },
  { value: "structure", label: "Structure Dropdown" },
];

const createDefaultTranslations = (localeCodes) =>
  Object.fromEntries(localeCodes.map((locale) => [locale, { label: "" }]));

function cloneTranslations(translations = {}, localeCodes) {
  return {
    ...structuredClone(createDefaultTranslations(localeCodes)),
    ...Object.fromEntries(
      Object.entries(translations).map(([locale, value]) => [
        locale,
        { label: value?.label || "" },
      ]),
    ),
  };
}

function normalizeItem(item, index = 0, localeCodes = []) {
  return {
    id: item.id || null,
    parent_id: item.parent_id || null,
    route_name: item.route_name || "link",
    url: item.url || "",
    icon: item.icon || "",
    sort_order: item.sort_order ?? index + 1,
    is_active: item.is_active !== false,
    translations: cloneTranslations(item.translations, localeCodes),
    children: sortTree((item.children || []).map((c, i) => normalizeItem(c, i, localeCodes))),
  };
}

function createItem(sortOrder = 1, routeName = "link", localeCodes = []) {
  return {
    id: null,
    parent_id: null,
    route_name: routeName,
    url: routeName === "link" ? "/" : "",
    icon: "",
    sort_order: sortOrder,
    is_active: true,
    translations: structuredClone(createDefaultTranslations(localeCodes)),
    children: [],
  };
}

function sortTree(items) {
  if (!Array.isArray(items)) return [];
  return [...items]
    .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0))
    .map((item, index) => ({
      ...item,
      sort_order: index + 1,
      children: sortTree(item.children || []),
    }));
}

function updateTree(items, path, updater) {
  return items.map((item, index) => {
    if (index !== path[0]) return item;
    if (path.length === 1) return updater(item);
    return { ...item, children: updateTree(item.children || [], path.slice(1), updater) };
  });
}

function removeFromTree(items, path) {
  if (path.length === 1) {
    return items.filter((_, index) => index !== path[0]).map((item, index) => ({ ...item, sort_order: index + 1 }));
  }

  return items.map((item, index) =>
    index === path[0]
      ? { ...item, children: removeFromTree(item.children || [], path.slice(1)) }
      : item,
  );
}

function moveInTree(items, path, direction) {
  if (path.length === 1) {
    const currentIndex = path[0];
    const targetIndex = currentIndex + direction;
    if (targetIndex < 0 || targetIndex >= items.length) return items;
    const next = [...items];
    const [item] = next.splice(currentIndex, 1);
    next.splice(targetIndex, 0, item);
    return next.map((entry, index) => ({ ...entry, sort_order: index + 1 }));
  }

  return items.map((item, index) =>
    index === path[0]
      ? { ...item, children: moveInTree(item.children || [], path.slice(1), direction) }
      : item,
  );
}

function prepareItems(items, localeCodes, primaryLocale) {
  if (!Array.isArray(items)) return [];
  return items.map((item) => ({
    id: item.id,
    route_name: item.route_name,
    url: ["link", "group", "action"].includes(item.route_name) ? item.url || "" : "",
    icon: item.icon || "",
    sort_order: item.sort_order,
    is_active: item.is_active,
    translations: Object.fromEntries(
      localeCodes.map((locale) => [
        locale,
        { label: item.translations?.[locale]?.label || item.translations?.[primaryLocale]?.label || "" },
      ]),
    ),
    children: prepareItems(item.children || [], localeCodes, primaryLocale),
  }));
}

function errorMessage(err, fallback) {
  const errors = err?.errors || err?.data?.errors;
  if (errors && typeof errors === "object") {
    const [field, messages] = Object.entries(errors)[0] || [];
    const message = Array.isArray(messages) ? messages[0] : messages;
    if (message) return `${field}: ${message}`;
  }
  return err?.message || fallback;
}

function flattenNavbarItems(list = []) {
  return list.flatMap((item) => [item, ...flattenNavbarItems(item.children || [])]);
}

export default function ApanelHeaderNavbar() {
  const { t } = useLanguage();
  const localeOptions = useApanelLocaleOptions();
  const localeCodes = useMemo(
    () => localeOptions.map((locale) => locale.code),
    [localeOptions],
  );
  const primaryLocale = localeCodes[0] || "";
  const [isActive, setIsActive] = useState(true);
  const [items, setItems] = useState([]);
  const [activeLocale, setActiveLocale] = useState("");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [pendingDelete, setPendingDelete] = useState(null);

  const sortedItems = items;
  const flatItems = useMemo(() => flattenNavbarItems(sortedItems), [sortedItems]);
  const pageStats = useMemo(
    () => [
      {
        label: "Top menu",
        value: sortedItems.length,
        hint: "Main navbar entries",
        icon: Navigation,
        tone: "text-blue-600 bg-blue-50 border-blue-100",
      },
      {
        label: "Child links",
        value: Math.max(flatItems.length - sortedItems.length, 0),
        hint: "Dropdown links",
        icon: Layers3,
        tone: "text-violet-600 bg-violet-50 border-violet-100",
      },
      {
        label: "Active items",
        value: flatItems.filter((item) => item.is_active).length,
        hint: isActive ? "Header enabled" : "Header disabled",
        icon: CheckCircle2,
        tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
      },
      {
        label: "Locales",
        value: localeOptions.length,
        hint: "Navbar translations",
        icon: Languages,
        tone: "text-amber-600 bg-amber-50 border-amber-100",
      },
    ],
    [flatItems, isActive, localeOptions.length, sortedItems.length],
  );

  const loadNavbar = useCallback(async () => {
    if (!primaryLocale) return;

    try {
      setLoading(true);
      setError("");
      const data = await apanelService.getHeaderNavbar();
      const nextItems = sortTree((data.items || []).map((item, index) => normalizeItem(item, index, localeCodes)));
      setIsActive(data.is_active !== false);
      setItems(nextItems);
    } catch (err) {
      setError(err?.message || "Failed to load header navbar.");
      setItems([]);
    } finally {
      setLoading(false);
    }
  }, [localeCodes, primaryLocale]);

  useEffect(() => {
    loadNavbar();
  }, [loadNavbar]);

  useEffect(() => {
    if (!primaryLocale) return;

    setActiveLocale((current) =>
      current && localeCodes.includes(current) ? current : primaryLocale,
    );
    setItems((current) =>
      sortTree(current.map((item, index) => normalizeItem(item, index, localeCodes))),
    );
  }, [localeCodes, primaryLocale]);

  const updateItem = (path, field, value) => {
    setItems((current) => {
      const updated = updateTree(current, path, (item) => {
        const next = { ...item, [field]: value };
        if (field === "route_name" && !["link", "group", "action"].includes(value)) next.url = "";
        if (field === "route_name" && value === "link" && !next.url) next.url = "/";
        if (field === "route_name" && value === "action" && !next.url) next.url = "/login";
        return next;
      });
      return sortTree(updated);
    });
  };

  const updateLabel = (path, locale, label) => {
    setItems((current) => {
      const updated = updateTree(current, path, (item) => ({
        ...item,
        translations: {
          ...item.translations,
          [locale]: { label },
        },
      }));
      return sortTree(updated);
    });
  };

  const addItem = () => {
    setItems((current) => sortTree([...current, createItem(current.length + 1, "link", localeCodes)]));
  };

  const addChild = (path) => {
    setItems((current) => {
      const updated = updateTree(current, path, (item) => ({
        ...item,
        children: [...(item.children || []), createItem((item.children || []).length + 1, "link", localeCodes)],
      }));
      return sortTree(updated);
    });
  };

  const removeItem = () => {
    if (!pendingDelete) return;
    setItems((current) => {
      const updated = removeFromTree(current, pendingDelete.path);
      return sortTree(updated);
    });
    setPendingDelete(null);
  };

  const saveNavbar = async () => {
    try {
      setSaving(true);
      setError("");
      setSuccess("");
      const payload = {
        is_active: isActive,
        items: prepareItems(items, localeCodes, primaryLocale),
      };
      await apanelService.updateHeaderNavbar(payload);
      await loadNavbar();
      setSuccess(t("apanel.headerNavbar.saved"));
    } catch (err) {
      setError(errorMessage(err, "Failed to save header navbar."));
    } finally {
      setSaving(false);
    }
  };

  const renderItems = (list, parentPath = []) => (
    <div className="space-y-4">
      {(list || []).map((item, index) => {
        const path = [...parentPath, index];
        const label = item.translations?.[activeLocale]?.label || item.translations?.[primaryLocale]?.label || "";
        const canHaveChildren = !["link", "action"].includes(item.route_name) && parentPath.length < 2;

        return (
          <div key={item.id || `new-${path.join("-")}`} className={`rounded-2xl border border-gray-100 bg-gray-50 p-4 ${parentPath.length ? "ms-4 border-s-primary/20" : ""}`}>
            <div className="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div className="flex items-center gap-3">
                <GripVertical className="h-4 w-4 text-gray-300" />
                <div>
                  <p className="text-xs font-extrabold uppercase tracking-widest text-gray-400">
                    {parentPath.length ? `Child Level ${parentPath.length}` : "Top Menu"} #{index + 1}
                  </p>
                  <h3 className="text-sm font-black text-navy">{label}</h3>
                </div>
              </div>
              <div className="flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => setItems((current) => sortTree(moveInTree(current, path, -1)))}
                  className="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-500"
                >
                  Up
                </button>
                <button
                  type="button"
                  onClick={() => setItems((current) => sortTree(moveInTree(current, path, 1)))}
                  className="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-500"
                >
                  Down
                </button>
                {canHaveChildren && (
                  <button
                    type="button"
                    onClick={() => addChild(path)}
                    className="inline-flex items-center gap-1 rounded-xl border border-primary/20 bg-primary/5 px-3 py-2 text-xs font-bold text-primary"
                  >
                    <Plus className="h-3.5 w-3.5" />
                    Add Child
                  </button>
                )}
                <button
                  type="button"
                  onClick={() => setPendingDelete({ path, label })}
                  className="rounded-xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-600"
                >
                  <Trash2 className="h-4 w-4" />
                </button>
              </div>
            </div>

            <div className="grid grid-cols-1 gap-4 lg:grid-cols-6">
              <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
                Type
                <select
                  value={item.route_name}
                  onChange={(event) => updateItem(path, "route_name", event.target.value)}
                  className="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 outline-none focus:border-primary"
                >
                  {itemTypes.map((type) => (
                    <option key={type.value} value={type.value}>{type.label}</option>
                  ))}
                </select>
              </label>
              <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400 lg:col-span-2">
                Label ({activeLocale})
                <input
                  value={item.translations?.[activeLocale]?.label || ""}
                  onChange={(event) => updateLabel(path, activeLocale, event.target.value)}
                  className="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 outline-none focus:border-primary"
                />
              </label>
              <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
                Icon
                <input
                  value={item.icon || ""}
                  onChange={(event) => updateItem(path, "icon", event.target.value)}
                  placeholder={t("apanel.headerNavbar.keywordsPlaceholder")}
                  className="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 outline-none focus:border-primary"
                />
              </label>
              <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
                URL
                <input
                  value={item.url || ""}
                  disabled={!["link", "group", "action"].includes(item.route_name)}
                  onChange={(event) => updateItem(path, "url", event.target.value)}
                  className="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 outline-none focus:border-primary disabled:bg-gray-100 disabled:text-gray-400"
                />
              </label>
              <label className="flex min-h-10 items-center justify-between gap-3 self-end rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-600">
                Active
                <input
                  type="checkbox"
                  checked={item.is_active}
                  onChange={(event) => updateItem(path, "is_active", event.target.checked)}
                  className="h-4 w-4 accent-primary"
                />
              </label>
            </div>

            {item.children?.length > 0 && (
              <div className="mt-4 border-t border-gray-100 pt-4">
                {renderItems(item.children, path)}
              </div>
            )}
          </div>
        );
      })}
    </div>
  );

  if (loading) {
    return <div className="p-8 text-sm font-bold text-gray-500">{t("apanel.headerNavbar.loading")}</div>;
  }

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 className="mt-1 text-2xl font-black text-navy">
            Header Navbar CMS
          </h1>
          <p className="mt-1 text-sm font-semibold text-gray-500">
            Controls public header navigation, order, labels, dropdown groups,
            and child links.
          </p>
        </div>
        <button
          type="button"
          onClick={saveNavbar}
          disabled={saving}
          className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-primary-hover disabled:opacity-60"
        >
          <Save className="h-4 w-4" />
          {saving ? "Saving..." : "Save Header Navbar"}
        </button>
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
          {success}
        </div>
      )}

      <ApanelStatsCards items={pageStats} />

      <div className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
        <div className="mb-5 flex flex-col gap-4 border-b border-gray-100 pb-5 lg:flex-row lg:items-center lg:justify-between">
          <label className="flex min-h-10 items-center justify-between gap-3 rounded-xl border border-gray-200 px-3 py-2 text-xs font-bold text-gray-600 lg:w-48">
            Active
            <input
              type="checkbox"
              checked={isActive}
              onChange={(event) => setIsActive(event.target.checked)}
              className="h-4 w-4 accent-primary"
            />
          </label>

          <div className="inline-flex flex-wrap rounded-2xl bg-gray-100 p-1">
            {localeOptions.map((locale) => (
              <button
                key={locale.code}
                type="button"
                onClick={() => setActiveLocale(locale.code)}
                className={`rounded-xl px-4 py-2 text-xs font-extrabold transition ${activeLocale === locale.code ? "bg-primary text-white shadow-sm" : "text-gray-500 hover:text-navy"}`}
              >
                {locale.label}
              </button>
            ))}
          </div>
        </div>

        {renderItems(sortedItems)}

        <button
          type="button"
          onClick={addItem}
          className="mt-5 inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2 text-sm font-extrabold text-primary"
        >
          <Plus className="h-4 w-4" />
          Add Menu Item
        </button>
      </div>

      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title={`Delete ${pendingDelete?.label || "menu item"}?`}
        message="This navbar item and its child links will be removed after saving the CMS content."
        onConfirm={removeItem}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
