import { useEffect, useMemo, useState } from "react";
import { Loader2, Save } from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { facultyPageCmsService } from "../../../services/facultyPageCmsService";
import { useApanelLocaleCodes } from "../utils/locales";

const fields = [
  ["home_label", "Home Label"],
  ["faculties_label", "Faculties Label"],
  ["industry_cooperation_label", "Hero Badge"],
  ["overview_label", "Overview Label"],
  ["leadership_label", "Leadership Label"],
  ["leadership_description", "Leadership Description", "textarea"],
  ["departments_label", "Departments Label"],
  ["departments_description", "Departments Description", "textarea"],
  ["head_of_department_label", "Head of Department Label"],
  ["learn_more_label", "Learn More Label"],
  ["academic_pathways_label", "Academic Pathways Label"],
  ["bachelor_programs_label", "Bachelor Programs Label"],
  ["bachelor_description", "Bachelor Description", "textarea"],
  ["master_specializations_label", "Master Specializations Label"],
  ["master_description", "Master Description", "textarea"],
  ["contact_label", "Contact Label"],
  ["contact_description", "Contact Description", "textarea"],
  ["not_found_title_label", "Not Found Title"],
  ["not_found_description", "Not Found Description", "textarea"],
  ["dean_contact_label", "Dean Contact Label"],
  ["deputy_dean_contacts_label", "Deputy Dean Contacts Label"],
  ["quick_department_links_label", "Quick Department Links Label"],
  ["phone_label", "Phone Label"],
  ["email_label", "Email Label"],
];

const emptyTranslation = Object.fromEntries(fields.map(([key]) => [key, ""]));

const inputClass =
  "mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none";

const normalizeTranslations = (translations = [], localeCodes) =>
  Object.fromEntries(
    localeCodes.map((locale) => {
      const existing = translations.find((item) => item.locale === locale);
      return [locale, { ...emptyTranslation, ...(existing || {}) }];
    }),
  );

export default function ApanelFacultyPageCms() {
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || "en";
  const [setting, setSetting] = useState(null);
  const [activeLocale, setActiveLocale] = useState(primaryLocale);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    setActiveLocale((current) => (localeCodes.includes(current) ? current : primaryLocale));
  }, [localeCodes, primaryLocale]);

  useEffect(() => {
    if (!localeCodes.length) return;

    let alive = true;
    setLoading(true);
    setError("");

    apanelService
      .getFacultyPageCms()
      .then((payload) => {
        if (!alive) return;
        const current = payload.setting || {};
        setSetting({
          key: current.key || "main",
          is_active: Boolean(current.is_active ?? true),
          settings: current.settings || {},
          translations: normalizeTranslations(current.translations || [], localeCodes),
        });
      })
      .catch((err) => {
        if (alive) setError(err?.message || "Failed to load Faculty page CMS.");
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [localeCodes]);

  const activeTranslation = useMemo(
    () => setting?.translations?.[activeLocale] || emptyTranslation,
    [setting, activeLocale],
  );

  const updateTranslation = (key, value) => {
    setSetting((current) => ({
      ...current,
      translations: {
        ...current.translations,
        [activeLocale]: {
          ...current.translations[activeLocale],
          [key]: value,
        },
      },
    }));
  };

  const save = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");

    try {
      await apanelService.updateFacultyPageCms({ setting });
      facultyPageCmsService.clearCache();
    } catch (err) {
      setError(err?.message || "Failed to save Faculty page CMS.");
    } finally {
      setSaving(false);
    }
  };

  if (loading || !setting) {
    return (
      <div className="flex min-h-80 items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <form onSubmit={save} className="space-y-6">
      <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <p className="text-xs font-black uppercase tracking-widest text-primary">
          CMS
        </p>
        <h1 className="mt-2 text-2xl font-black uppercase tracking-wide text-navy">
          Faculty Page
        </h1>
        <p className="mt-2 text-sm font-semibold text-gray-500">
          Manage the shared labels and helper text used across all public faculty detail pages.
        </p>
      </div>

      <FormError message={error} />

      <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <div className="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 className="text-xl font-black text-navy">Shared faculty labels</h2>
            <p className="mt-1 text-xs font-bold text-gray-400">
              These labels are applied to every faculty public page.
            </p>
          </div>
          <div className="flex flex-wrap gap-2">
            {localeCodes.map((locale) => (
              <button
                type="button"
                key={locale}
                onClick={() => setActiveLocale(locale)}
                className={`rounded-lg px-3 py-1.5 text-xs font-extrabold uppercase ${
                  activeLocale === locale
                    ? "bg-primary text-white"
                    : "bg-gray-50 text-gray-500 hover:bg-primary/10 hover:text-primary"
                }`}
              >
                {locale}
              </button>
            ))}
          </div>
        </div>

        <div className="grid gap-4 md:grid-cols-2">
          {fields.map(([key, label, type]) => (
            <label
              key={key}
              className={`text-xs font-bold text-gray-500 ${
                type === "textarea" ? "md:col-span-2" : ""
              }`}
            >
              {label}
              {type === "textarea" ? (
                <textarea
                  rows={3}
                  className={`${inputClass} resize-y`}
                  value={activeTranslation[key] || ""}
                  onChange={(event) => updateTranslation(key, event.target.value)}
                />
              ) : (
                <input
                  className={inputClass}
                  value={activeTranslation[key] || ""}
                  onChange={(event) => updateTranslation(key, event.target.value)}
                />
              )}
            </label>
          ))}
        </div>
      </div>

      <div className="sticky bottom-4 flex justify-end">
        <button
          type="submit"
          disabled={saving}
          className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-primary/20 disabled:opacity-60"
        >
          {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
          {saving ? "Saving..." : "Save Changes"}
        </button>
      </div>
    </form>
  );
}
