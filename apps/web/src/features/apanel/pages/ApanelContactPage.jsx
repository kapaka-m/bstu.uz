import React, { useCallback, useEffect, useMemo, useState } from "react";
import { Plus, Save, Trash2 } from "lucide-react";
import { apanelService } from "../../../services/apanelService";
import ConfirmDialog from "../components/ConfirmDialog";
import { useApanelLocaleOptions } from "../utils/locales";

const defaultContent = {
  tag: "",
  title: "",
  mapTitle: "",
  cards: [],
  form: {
    nameLabel: "",
    namePlaceholder: "",
    emailLabel: "",
    emailPlaceholder: "",
    subjectLabel: "",
    subjectPlaceholder: "",
    messageLabel: "",
    messagePlaceholder: "",
    sendingLabel: "",
    sendLabel: "",
    successMessage: "",
    errorMessage: "",
  },
  faq: {
    tag: "",
    title: "",
    items: [],
  },
};

const createEmptyForm = (localeCodes) => ({
  map_embed_url: "",
  is_published: true,
  translations: Object.fromEntries(
    localeCodes.map((locale) => [
      locale,
      { content: structuredClone(defaultContent) },
    ]),
  ),
});

const clone = (value) => JSON.parse(JSON.stringify(value || {}));

const errorMessage = (err, fallback) => {
  const errors = err?.errors || err?.data?.errors;
  if (errors && typeof errors === "object") {
    const [field, messages] = Object.entries(errors)[0] || [];
    const message = Array.isArray(messages) ? messages[0] : messages;
    if (message) return `${field}: ${message}`;
  }
  return err?.message || fallback;
};

function Field({ label, value, onChange, multiline = false }) {
  const className =
    "mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 outline-none focus:border-primary";

  return (
    <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
      {label}
      {multiline ? (
        <textarea value={value || ""} onChange={(event) => onChange(event.target.value)} rows={4} className={`${className} resize-y leading-relaxed`} />
      ) : (
        <input value={value || ""} onChange={(event) => onChange(event.target.value)} className={className} />
      )}
    </label>
  );
}

function SelectField({ label, value, options, onChange }) {
  return (
    <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
      {label}
      <select value={value || ""} onChange={(event) => onChange(event.target.value)} className="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 outline-none focus:border-primary">
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    </label>
  );
}

export default function ApanelContactPage() {
  const localeOptions = useApanelLocaleOptions();
  const localeCodes = useMemo(
    () => localeOptions.map((locale) => locale.code),
    [localeOptions],
  );
  const primaryLocale = localeCodes[0] || "";
  const initialForm = useMemo(
    () => createEmptyForm(localeCodes),
    [localeCodes],
  );

  const [form, setForm] = useState(() => createEmptyForm([]));
  const [activeLocale, setActiveLocale] = useState("");
  const [activeSection, setActiveSection] = useState("content");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [confirmAction, setConfirmAction] = useState(null);

  const content = form.translations[activeLocale]?.content || defaultContent;
  const activeLocaleLabel = useMemo(
    () =>
      localeOptions.find((locale) => locale.code === activeLocale)?.label ||
      activeLocale,
    [activeLocale, localeOptions],
  );

  const applyPageToForm = useCallback((page) => {
    const translations = clone(initialForm.translations);
    (page.translations || []).forEach((translation) => {
      if (!localeCodes.includes(translation.locale)) return;
      translations[translation.locale] = {
        content: { ...clone(defaultContent), ...clone(translation.content) },
      };
    });

    setForm({
      map_embed_url: page.map_embed_url || "",
      is_published: Boolean(page.is_published),
      translations,
    });
  }, [initialForm.translations, localeCodes]);

  const loadContactPage = useCallback(async (isAlive = () => true) => {
    const page = await apanelService.getContactPage();
    if (isAlive()) applyPageToForm(page);
    return page;
  }, [applyPageToForm]);

  useEffect(() => {
    if (!primaryLocale) return undefined;

    let alive = true;
    setLoading(true);

    loadContactPage(() => alive)
      .catch((err) => setError(err?.message || "Failed to load Contact page CMS."))
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [loadContactPage, primaryLocale]);

  useEffect(() => {
    if (!primaryLocale) return;

    setActiveLocale((current) =>
      current && localeCodes.includes(current) ? current : primaryLocale,
    );
    setForm((current) => {
      const translations = clone(initialForm.translations);
      Object.entries(current.translations || {}).forEach(([locale, value]) => {
        if (localeCodes.includes(locale)) {
          translations[locale] = {
            content: {
              ...clone(defaultContent),
              ...clone(value?.content),
            },
          };
        }
      });
      return { ...current, translations };
    });
  }, [initialForm.translations, localeCodes, primaryLocale]);

  const updateField = (field, value) => {
    setForm((current) => ({ ...current, [field]: value }));
  };

  const updateContent = (path, value) => {
    setForm((current) => {
      const translations = clone(current.translations);
      const nextContent = { ...clone(defaultContent), ...clone(translations[activeLocale]?.content) };
      const keys = path.split(".");
      let target = nextContent;
      keys.slice(0, -1).forEach((key) => {
        target[key] = target[key] && typeof target[key] === "object" ? target[key] : {};
        target = target[key];
      });
      target[keys.at(-1)] = value;
      translations[activeLocale] = { content: nextContent };
      return { ...current, translations };
    });
  };

  const updateArrayItem = (path, index, field, value) => {
    const items = clone(path.split(".").reduce((acc, key) => acc?.[key], content) || []);
    items[index] = { ...(items[index] || {}), [field]: value };
    updateContent(path, items);
  };

  const addArrayItem = (path, item) => {
    const items = clone(path.split(".").reduce((acc, key) => acc?.[key], content) || []);
    updateContent(path, [...items, item]);
  };

  const removeArrayItem = (path, index) => {
    const items = clone(path.split(".").reduce((acc, key) => acc?.[key], content) || []);
    updateContent(path, items.filter((_, itemIndex) => itemIndex !== index));
  };

  const requestRemove = (path, index, label) => {
    setConfirmAction({
      title: `Delete ${label}?`,
      message: "This item will be removed from the current language content after saving.",
      onConfirm: () => {
        removeArrayItem(path, index);
        setConfirmAction(null);
      },
    });
  };

  const handleSave = async () => {
    try {
      setSaving(true);
      setError("");
      setSuccess("");
      await apanelService.updateContactPage({
        map_embed_url: form.map_embed_url || "",
        is_published: Boolean(form.is_published),
        translations: Object.fromEntries(
          localeCodes.map((locale) => [
            locale,
            {
              content: {
                ...clone(defaultContent),
                ...clone(form.translations?.[locale]?.content),
              },
            },
          ]),
        ),
      });
      await loadContactPage();
      setSuccess("Contact page content saved successfully.");
    } catch (err) {
      setError(errorMessage(err, "Failed to save Contact page content."));
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="p-8 text-sm font-bold text-gray-500">Loading Contact page CMS...</div>;
  }

  const renderCards = () => (
    <div className="space-y-4">
      {(content.cards || []).map((card, index) => (
        <div key={index} className="rounded-2xl border border-gray-100 bg-gray-50 p-4">
          <div className="mb-4 flex items-center justify-between">
            <h3 className="text-sm font-extrabold text-navy">Contact Card #{index + 1}</h3>
            <button type="button" onClick={() => requestRemove("cards", index, `Contact Card #${index + 1}`)} className="text-rose-600">
              <Trash2 className="h-4 w-4" />
            </button>
          </div>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <SelectField
              label="Kind"
              value={card.kind}
              options={[
                { value: "address", label: "Address" },
                { value: "phone", label: "Phone" },
                { value: "email", label: "Email" },
                { value: "hours", label: "Hours" },
              ]}
              onChange={(value) => updateArrayItem("cards", index, "kind", value)}
            />
            <Field label="Title" value={card.title} onChange={(value) => updateArrayItem("cards", index, "title", value)} />
            <Field
              label="Details, one per line"
              value={(card.details || []).join("\n")}
              multiline
              onChange={(value) => updateArrayItem("cards", index, "details", value.split("\n").map((line) => line.trim()).filter(Boolean))}
            />
          </div>
        </div>
      ))}
      <button
        type="button"
        onClick={() => addArrayItem("cards", { kind: "address", title: "", details: [] })}
        className="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2 text-sm font-extrabold text-primary"
      >
        <Plus className="h-4 w-4" />
        Add Contact Card
      </button>
    </div>
  );

  const renderFaq = () => (
    <div className="space-y-4">
      {(content.faq?.items || []).map((item, index) => (
        <div key={index} className="rounded-2xl border border-gray-100 bg-gray-50 p-4">
          <div className="mb-4 flex items-center justify-between">
            <h3 className="text-sm font-extrabold text-navy">FAQ #{index + 1}</h3>
            <button type="button" onClick={() => requestRemove("faq.items", index, `FAQ #${index + 1}`)} className="text-rose-600">
              <Trash2 className="h-4 w-4" />
            </button>
          </div>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Field label="Question" value={item.question} onChange={(value) => updateArrayItem("faq.items", index, "question", value)} />
            <Field label="Answer" value={item.answer} multiline onChange={(value) => updateArrayItem("faq.items", index, "answer", value)} />
          </div>
        </div>
      ))}
      <button
        type="button"
        onClick={() => addArrayItem("faq.items", { id: Date.now(), question: "", answer: "" })}
        className="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2 text-sm font-extrabold text-primary"
      >
        <Plus className="h-4 w-4" />
        Add FAQ
      </button>
    </div>
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 className="mt-1 text-2xl font-black text-navy">
            Contact Page CMS
          </h1>
          <p className="mt-1 text-sm font-semibold text-gray-500">
            Full control for the public Contact page at /contact.
          </p>
        </div>
        <button
          type="button"
          onClick={handleSave}
          disabled={saving}
          className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-primary-hover disabled:opacity-60"
        >
          <Save className="h-4 w-4" />
          {saving ? "Saving..." : "Save Contact Page"}
        </button>
      </div>

      {(error || success) && (
        <div
          className={`rounded-2xl border p-4 text-sm font-bold ${error ? "border-rose-100 bg-rose-50 text-rose-700" : "border-emerald-100 bg-emerald-50 text-emerald-700"}`}
        >
          {error || success}
        </div>
      )}

      <div className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
        <div className="flex flex-wrap gap-2 border-b border-gray-100 pb-4">
          {[
            ["controls", "Page Controls"],
            ["content", "Content"],
            ["cards", "Contact Cards"],
            ["form", "Form Labels"],
            ["faq", "FAQ"],
          ].map(([key, label]) => (
            <button
              key={key}
              type="button"
              onClick={() => setActiveSection(key)}
              className={`rounded-xl px-4 py-2 text-xs font-extrabold transition ${activeSection === key ? "bg-navy text-white shadow-sm" : "bg-gray-100 text-gray-500 hover:bg-gray-200"}`}
            >
              {label}
            </button>
          ))}
        </div>

        {activeSection !== "controls" && (
          <div className="border-b border-gray-100 py-4">
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
        )}

        <div className="pt-6">
          {activeSection !== "controls" && (
            <p className="mb-4 text-xs font-extrabold uppercase tracking-widest text-primary">
              {activeLocaleLabel}
            </p>
          )}

          {activeSection === "controls" && (
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_auto]">
              <Field
                label="Map Embed URL"
                value={form.map_embed_url}
                onChange={(value) => updateField("map_embed_url", value)}
              />
              <label className="flex min-h-10 items-center justify-between gap-3 rounded-xl border border-gray-200 px-3 py-2 text-xs font-bold text-gray-600">
                Active
                <input
                  type="checkbox"
                  checked={form.is_published}
                  onChange={(event) =>
                    updateField("is_published", event.target.checked)
                  }
                  className="h-4 w-4 accent-primary"
                />
              </label>
            </div>
          )}

          {activeSection === "content" && (
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
              <Field
                label="Tag"
                value={content.tag}
                onChange={(value) => updateContent("tag", value)}
              />
              <Field
                label="Title"
                value={content.title}
                onChange={(value) => updateContent("title", value)}
              />
              <Field
                label="Map Title"
                value={content.mapTitle}
                onChange={(value) => updateContent("mapTitle", value)}
              />
            </div>
          )}

          {activeSection === "cards" && renderCards()}

          {activeSection === "form" && (
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
              {Object.keys(defaultContent.form).map((key) => (
                <Field
                  key={key}
                  label={key}
                  value={content.form?.[key]}
                  multiline={key.includes("Message")}
                  onChange={(value) => updateContent(`form.${key}`, value)}
                />
              ))}
            </div>
          )}

          {activeSection === "faq" && (
            <div className="space-y-6">
              <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Field
                  label="FAQ Tag"
                  value={content.faq?.tag}
                  onChange={(value) => updateContent("faq.tag", value)}
                />
                <Field
                  label="FAQ Title"
                  value={content.faq?.title}
                  onChange={(value) => updateContent("faq.title", value)}
                />
              </div>
              {renderFaq()}
            </div>
          )}
        </div>
      </div>

      <ConfirmDialog
        isOpen={Boolean(confirmAction)}
        title={confirmAction?.title}
        message={confirmAction?.message}
        onConfirm={confirmAction?.onConfirm}
        onCancel={() => setConfirmAction(null)}
      />
    </div>
  );
}
