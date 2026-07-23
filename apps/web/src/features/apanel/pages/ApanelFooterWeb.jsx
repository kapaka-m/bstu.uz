import React, { useEffect, useMemo, useState } from "react";
import { Save, Loader2, Plus, Trash2 } from "lucide-react";
import FormError from "../../../components/common/FormError";
import { footerService } from "../../../services/footerService";
import ConfirmDialog from "../components/ConfirmDialog";


const LOCALES = ["en", "uz", "ru", "ar"];
const TRANSLATION_FIELDS = [
  ["logo_alt", "Logo alt text"],
  ["description", "Footer description", "textarea"],
  ["useful_links_title", "Useful links title"],
  ["faculties_title", "Faculties title"],
  ["contact_title", "Contact title"],
  ["address_line_1", "Address line 1"],
  ["address_line_2", "Address line 2"],
  ["phone_label", "Phone label"],
  ["email_label", "Email label"],
  ["rights_text", "Rights text"],
];
const PROMO_FIELDS = [
  ["admissions_badge", "Admissions badge"],
  ["admissions_heading", "Admissions heading"],
  ["admissions_description", "Admissions description", "textarea"],
  ["admissions_button_label", "Apply button label"],
  ["newsletter_title", "Newsletter title"],
  ["newsletter_description", "Newsletter description", "textarea"],
  ["newsletter_placeholder", "Newsletter input placeholder"],
  ["newsletter_success_message", "Newsletter success message"],
];

const emptyFooter = {
  useful_links: [],
  faculty_links: [],
  social_links: [],
  admissions_apply_url: "/apply",
  phone: "",
  email: "",
  copyright_year: "",
  is_active: true,
  translations: LOCALES.reduce((acc, locale) => {
    acc[locale] = {
      logo_alt: "",
      description: "",
      admissions_badge: "",
      admissions_heading: "",
      admissions_description: "",
      admissions_button_label: "",
      newsletter_title: "",
      newsletter_description: "",
      newsletter_placeholder: "",
      newsletter_success_message: "",
      useful_links_title: "",
      faculties_title: "",
      contact_title: "",
      address_line_1: "",
      address_line_2: "",
      phone_label: "",
      email_label: "",
      rights_text: "",
      useful_link_labels: {},
      faculty_link_labels: {},
    };
    return acc;
  }, {}),
};

function normalizeFooter(record) {
  const next = {
    ...emptyFooter,
    ...record,
    useful_links: record?.useful_links || [],
    faculty_links: record?.faculty_links || [],
    social_links: record?.social_links || [],
    translations: { ...emptyFooter.translations },
  };

  (record?.translations || []).forEach((translation) => {
    next.translations[translation.locale] = {
      ...emptyFooter.translations[translation.locale],
      ...translation,
      useful_link_labels: translation.useful_link_labels || {},
      faculty_link_labels: translation.faculty_link_labels || {},
    };
  });

  return next;
}

function TextField({ label, value, onChange, type = "text", textarea = false }) {
  const classes =
    "w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-sm font-semibold bg-white text-navy";

  return (
    <label className="space-y-1.5">
      <span className="block text-[11px] font-extrabold uppercase tracking-wider text-gray-400">
        {label}
      </span>
      {textarea ? (
        <textarea
          value={value || ""}
          onChange={(e) => onChange(e.target.value)}
          rows={4}
          className={classes}
        />
      ) : (
        <input
          type={type}
          value={value || ""}
          onChange={(e) => onChange(e.target.value)}
          className={classes}
        />
      )}
    </label>
  );
}

function LinkEditor({ title, links, onChange, labelValues, onLabelChange, onDeleteClick }) {
  const updateLink = (index, field, value) => {
    const next = [...links];
    next[index] = { ...next[index], [field]: value };
    onChange(next);
  };

  return (
    <section className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs space-y-4">
      <div className="flex items-center justify-between gap-3">
        <h2 className="text-sm font-black text-navy uppercase tracking-wider">
          {title}
        </h2>
        <button
          type="button"
          onClick={() => onChange([...links, { key: "", url: "" }])}
          className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-primary text-white text-xs font-extrabold cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          Add
        </button>
      </div>

      <div className="space-y-3">
        {links.map((link, index) => (
          <div
            key={`${link.key}-${index}`}
            className="grid grid-cols-1 lg:grid-cols-12 gap-3 rounded-2xl border border-gray-100 p-3"
          >
            <div className="lg:col-span-2">
              <TextField
                label="Key"
                value={link.key}
                onChange={(value) => updateLink(index, "key", value)}
              />
            </div>
            <div className="lg:col-span-4">
              <TextField
                label="URL"
                value={link.url}
                onChange={(value) => updateLink(index, "url", value)}
              />
            </div>
            <div className="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
              {LOCALES.map((locale) => (
                <TextField
                  key={locale}
                  label={`${locale.toUpperCase()} label`}
                  value={labelValues[locale]?.[link.key] || ""}
                  onChange={(value) => onLabelChange(locale, link.key, value)}
                />
              ))}
            </div>
            <div className="lg:col-span-1 flex items-end">
              <button
                type="button"
                onClick={() => onDeleteClick(index)}
                className="w-full h-10 rounded-xl border border-rose-100 text-rose-600 hover:bg-rose-50 cursor-pointer inline-flex items-center justify-center"
                aria-label="Remove link"
              >
                <Trash2 className="w-4 h-4" />
              </button>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}

export default function ApanelFooterWeb() {
  const [footer, setFooter] = useState(emptyFooter);
  const [activeLocale, setActiveLocale] = useState("en");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [saved, setSaved] = useState(false);
  const [pendingDelete, setPendingDelete] = useState(null);

  const handleDeleteClick = (type, index, item) => {
    const label = item?.key || `Item ${index + 1}`;
    setPendingDelete({ type, index, label });
  };

  const handleSocialDeleteClick = (index, social) => {
    const label = social?.label || social?.key || `Social ${index + 1}`;
    setPendingDelete({ type: "social", index, label });
  };

  const confirmDelete = () => {
    if (!pendingDelete) return;
    const { type, index } = pendingDelete;
    if (type === "useful") {
      const next = footer.useful_links.filter((_, i) => i !== index);
      updateRoot("useful_links", next);
    } else if (type === "faculty") {
      const next = footer.faculty_links.filter((_, i) => i !== index);
      updateRoot("faculty_links", next);
    } else if (type === "social") {
      const next = footer.social_links.filter((_, i) => i !== index);
      updateRoot("social_links", next);
    }
    setPendingDelete(null);
  };


  useEffect(() => {
    const loadFooter = async () => {
      try {
        setLoading(true);
        setError("");
        const data = await footerService.getCmsFooter();
        setFooter(normalizeFooter(data));
      } catch (err) {
        setError(err?.message || "Failed to load footer CMS content.");
      } finally {
        setLoading(false);
      }
    };

    loadFooter();
  }, []);

  const linkLabels = useMemo(
    () => ({
      useful: LOCALES.reduce((acc, locale) => {
        acc[locale] = footer.translations[locale]?.useful_link_labels || {};
        return acc;
      }, {}),
      faculty: LOCALES.reduce((acc, locale) => {
        acc[locale] = footer.translations[locale]?.faculty_link_labels || {};
        return acc;
      }, {}),
    }),
    [footer.translations],
  );

  const updateRoot = (field, value) => {
    setFooter((prev) => ({ ...prev, [field]: value }));
  };

  const updateTranslation = (locale, field, value) => {
    setFooter((prev) => ({
      ...prev,
      translations: {
        ...prev.translations,
        [locale]: {
          ...prev.translations[locale],
          [field]: value,
        },
      },
    }));
  };

  const updateLinkLabel = (group, locale, key, value) => {
    if (!key) return;
    const field =
      group === "useful" ? "useful_link_labels" : "faculty_link_labels";

    setFooter((prev) => ({
      ...prev,
      translations: {
        ...prev.translations,
        [locale]: {
          ...prev.translations[locale],
          [field]: {
            ...(prev.translations[locale]?.[field] || {}),
            [key]: value,
          },
        },
      },
    }));
  };

  const handleSave = async () => {
    try {
      setSaving(true);
      setSaved(false);
      setError("");
      const data = await footerService.updateCmsFooter(footer);
      setFooter(normalizeFooter(data));
      setSaved(true);
    } catch (err) {
      setError(err?.message || "Failed to save footer CMS content.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-75 flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            CMS Footer Web
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            /apanel/cms/footer-web
          </p>
        </div>
        <button
          type="button"
          onClick={handleSave}
          disabled={saving}
          className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover disabled:opacity-60 text-white text-xs font-extrabold cursor-pointer"
        >
          {saving ? (
            <Loader2 className="w-4 h-4 animate-spin" />
          ) : (
            <Save className="w-4 h-4" />
          )}
          Save Footer
        </button>
      </div>

      {error && <FormError message={error} />}
      {saved && (
        <div className="bg-emerald-50 border border-emerald-100 rounded-2xl px-4 py-3 text-sm font-bold text-emerald-700">
          Footer content saved successfully.
        </div>
      )}

      <section className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs space-y-4">
        <h2 className="text-sm font-black text-navy uppercase tracking-wider">
          Global Footer Settings
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <TextField
            label="Phone"
            value={footer.phone}
            onChange={(value) => updateRoot("phone", value)}
          />
          <TextField
            label="Email"
            type="email"
            value={footer.email}
            onChange={(value) => updateRoot("email", value)}
          />
          <TextField
            label="Copyright year"
            type="number"
            value={footer.copyright_year}
            onChange={(value) =>
              updateRoot("copyright_year", value === "" ? "" : Number(value))
            }
          />
        </div>
      </section>

      <section className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs space-y-5">
        <div className="flex flex-wrap gap-2">
          {LOCALES.map((locale) => (
            <button
              key={locale}
              type="button"
              onClick={() => setActiveLocale(locale)}
              className={`px-4 py-2 rounded-xl text-xs font-extrabold uppercase cursor-pointer ${
                activeLocale === locale
                  ? "bg-primary text-white"
                  : "bg-gray-50 text-gray-500 hover:bg-gray-100"
              }`}
            >
              {locale}
            </button>
          ))}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {TRANSLATION_FIELDS.map(([field, label, type]) => (
            <TextField
              key={field}
              label={label}
              textarea={type === "textarea"}
              value={footer.translations[activeLocale]?.[field]}
              onChange={(value) =>
                updateTranslation(activeLocale, field, value)
              }
            />
          ))}
        </div>
      </section>

      <section className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs space-y-5">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h2 className="text-sm font-black text-navy uppercase tracking-wider">
              Admissions Banner & Newsletter
            </h2>
            <p className="text-xs font-semibold text-gray-400 mt-1">
              Controls the promotional banner and subscription box above the
              main footer.
            </p>
          </div>
          <div className="w-full sm:w-64">
            <TextField
              label="Apply button URL"
              value={footer.admissions_apply_url}
              onChange={(value) => updateRoot("admissions_apply_url", value)}
            />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {PROMO_FIELDS.map(([field, label, type]) => (
            <TextField
              key={field}
              label={label}
              textarea={type === "textarea"}
              value={footer.translations[activeLocale]?.[field]}
              onChange={(value) =>
                updateTranslation(activeLocale, field, value)
              }
            />
          ))}
        </div>
      </section>

      <LinkEditor
        title="Primary Link Group"
        links={footer.useful_links}
        onChange={(value) => updateRoot("useful_links", value)}
        labelValues={linkLabels.useful}
        onLabelChange={(locale, key, value) =>
          updateLinkLabel("useful", locale, key, value)
        }
        onDeleteClick={(index) => handleDeleteClick("useful", index, footer.useful_links[index])}
      />

      <LinkEditor
        title="Faculty Links"
        links={footer.faculty_links}
        onChange={(value) => updateRoot("faculty_links", value)}
        labelValues={linkLabels.faculty}
        onLabelChange={(locale, key, value) =>
          updateLinkLabel("faculty", locale, key, value)
        }
        onDeleteClick={(index) => handleDeleteClick("faculty", index, footer.faculty_links[index])}
      />

      <section className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs space-y-4">
        <div className="flex items-center justify-between gap-3">
          <h2 className="text-sm font-black text-navy uppercase tracking-wider">
            Social Media
          </h2>
          <button
            type="button"
            onClick={() =>
              updateRoot("social_links", [
                ...footer.social_links,
                { key: "", label: "", url: "" },
              ])
            }
            className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-primary text-white text-xs font-extrabold cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            Add
          </button>
        </div>
        <div className="space-y-3">
          {footer.social_links.map((social, index) => (
            <div
              key={`${social.key}-${index}`}
              className="grid grid-cols-1 md:grid-cols-12 gap-3 rounded-2xl border border-gray-100 p-3"
            >
              {["key", "label", "url"].map((field) => (
                <div key={field} className="md:col-span-3">
                  <TextField
                    label={field}
                    value={social[field]}
                    onChange={(value) => {
                      const next = [...footer.social_links];
                      next[index] = { ...next[index], [field]: value };
                      updateRoot("social_links", next);
                    }}
                  />
                </div>
              ))}
              <div className="md:col-span-3 flex items-end">
                <button
                  type="button"
                  onClick={() => handleSocialDeleteClick(index, social)}
                  className="w-full h-10 rounded-xl border border-rose-100 text-rose-600 hover:bg-rose-50 cursor-pointer inline-flex items-center justify-center"
                  aria-label="Remove social link"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            </div>
          ))}
        </div>
      </section>

      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title={
          pendingDelete?.type === "useful"
            ? "Delete Link?"
            : pendingDelete?.type === "faculty"
            ? "Delete Faculty Link?"
            : "Delete Social Media Link?"
        }
        message={`Are you sure you want to delete the link "${pendingDelete?.label || ""}"? This will remove it from the list. You will need to click "Save Footer" to apply the changes.`}
        confirmText="Delete"
        cancelText="Cancel"
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
