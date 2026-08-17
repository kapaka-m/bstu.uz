import { useEffect, useMemo, useState } from "react";
import { Loader2, Save } from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { programPageCmsService } from "../../../services/programPageCmsService";
import { useApanelLocaleCodes } from "../utils/locales";

const fields = [
  ["back_to_programs_label", "Back to Programs Label"],
  ["course_curriculum_label", "Course Curriculum Label"],
  ["courses_label", "Courses Label"],
  ["year_label", "Year Label"],
  ["semester_label", "Semester Label"],
  ["admission_requirements_label", "Admission Requirements Label"],
  ["documents_label", "Documents Label"],
  ["quick_facts_label", "Quick Facts Label"],
  ["program_code_label", "Program Code Label"],
  ["degree_level_label", "Degree Level Label"],
  ["duration_label", "Duration Label"],
  ["years_label", "Years Label"],
  ["language_of_instruction_label", "Language of Instruction Label"],
  ["study_mode_label", "Study Mode Label"],
  ["tuition_fee_label", "Tuition Fee Label"],
  ["intake_period_label", "Intake Period Label"],
  ["intake_date_label", "Intake Date Label"],
  ["parent_faculty_label", "Parent Faculty Label"],
  ["parent_department_label", "Parent Department Label"],
  ["program_coordinator_label", "Program Coordinator Label"],
  ["academic_staff_label", "Academic Staff Label"],
  ["faculty_helpdesk_label", "Faculty Helpdesk Label"],
  ["faculty_helpdesk_description", "Faculty Helpdesk Description", "textarea"],
  ["contact_university_label", "Contact University Label"],
  ["career_opportunities_label", "Career Opportunities Label"],
  ["apply_now_label", "Apply Now Label"],
  ["apply_description", "Apply Description", "textarea"],
  ["not_found_title_label", "Not Found Title"],
  ["not_found_description", "Not Found Description", "textarea"],
  ["no_details_label", "No Details Label"],
  ["degree_bachelor_label", "Bachelor Label"],
  ["degree_master_label", "Master Label"],
  ["degree_phd_label", "PhD Label"],
  ["mode_full_time_label", "Full-time Label"],
  ["mode_part_time_label", "Part-time Label"],
  ["mode_evening_label", "Evening Label"],
  ["mode_distance_label", "Distance Label"],
  ["language_english_label", "English Label"],
  ["language_uzbek_label", "Uzbek Label"],
  ["language_russian_label", "Russian Label"],
  ["language_arabic_label", "Arabic Label"],
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

export default function ApanelProgramPageCms() {
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
      .getProgramPageCms()
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
        if (alive) setError(err?.message || "Failed to load Program page CMS.");
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
      await apanelService.updateProgramPageCms({ setting });
      programPageCmsService.clearCache();
    } catch (err) {
      setError(err?.message || "Failed to save Program page CMS.");
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
        <p className="text-xs font-black uppercase tracking-widest text-primary">CMS</p>
        <h1 className="mt-2 text-2xl font-black uppercase tracking-wide text-navy">
          Program Pages
        </h1>
        <p className="mt-2 text-sm font-semibold text-gray-500">
          Manage the shared labels used across all public program detail pages.
        </p>
      </div>

      <FormError message={error} />

      <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <div className="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 className="text-xl font-black text-navy">Shared program labels</h2>
            <p className="mt-1 text-xs font-bold text-gray-400">
              These labels replace the general translation dictionary on public program pages.
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
              className={`text-xs font-bold text-gray-500 ${type === "textarea" ? "md:col-span-2" : ""}`}
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
