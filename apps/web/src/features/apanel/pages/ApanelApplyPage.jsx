import { useEffect, useMemo, useState } from "react";
import { Loader2, Save } from "lucide-react";
import FormError from "../../../components/common/FormError";
import { useLanguage } from "../../../context/LanguageContext";
import { apanelService } from "../../../services/apanelService";
import { useApanelLocaleCodes } from "../utils/locales";

const inputClass =
  "mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none";

const fieldGroups = [
  {
    title: "Header and Steps",
    fields: [
      ["title", "Page Title"],
      ["subtitle", "Subtitle", "textarea"],
      ["personal", "Step 1 Label"],
      ["academic", "Step 2 Label"],
      ["review", "Step 3 Label"],
      ["passportHint", "Passport Hint", "textarea"],
    ],
  },
  {
    title: "Personal Information",
    fields: [
      ["fullName", "Full Name Label"],
      ["fullNamePlaceholder", "Full Name Placeholder"],
      ["birthDate", "Birth Date Label"],
      ["birthDatePlaceholder", "Birth Date Placeholder"],
      ["countryBirth", "Country of Birth Label"],
      ["countryBirthPlaceholder", "Country of Birth Placeholder"],
      ["placeBirth", "Place of Birth Label"],
      ["placeBirthPlaceholder", "Place of Birth Placeholder"],
      ["nationality", "Nationality Label"],
      ["nationalityPlaceholder", "Nationality Placeholder"],
      ["gender", "Gender Label"],
      ["genderPlaceholder", "Gender Placeholder"],
      ["passportNumber", "Passport Number Label"],
      ["passportNumberPlaceholder", "Passport Number Placeholder"],
      ["passportType", "Passport Type Label"],
      ["passportTypePlaceholder", "Passport Type Placeholder"],
      ["issueDate", "Issue Date Label"],
      ["issueDatePlaceholder", "Issue Date Placeholder"],
      ["expiryDate", "Expiry Date Label"],
      ["expiryDatePlaceholder", "Expiry Date Placeholder"],
      ["issuingCountry", "Issuing Country Label"],
      ["issuingCountryPlaceholder", "Issuing Country Placeholder"],
      ["placeIssue", "Place of Issue Label"],
      ["placeIssuePlaceholder", "Place of Issue Placeholder"],
      ["primaryPhone", "Primary Phone Label"],
      ["primaryPhonePlaceholder", "Primary Phone Placeholder"],
      ["messenger", "Messenger Label"],
      ["messengerPlaceholder", "Messenger Placeholder"],
      ["telegram", "Telegram Label"],
      ["telegramPlaceholder", "Telegram Placeholder"],
      ["alternativePhone", "Alternative Phone Label"],
      ["alternativePhonePlaceholder", "Alternative Phone Placeholder"],
    ],
  },
  {
    title: "Academic Information",
    fields: [
      ["degree", "Degree Label"],
      ["degreePlaceholder", "Degree Placeholder"],
      ["studentType", "Student Type Label"],
      ["studentTypePlaceholder", "Student Type Placeholder"],
      ["faculty", "Faculty Label"],
      ["facultyPlaceholder", "Faculty Placeholder"],
      ["program", "Program Label"],
      ["programPlaceholder", "Program Placeholder"],
      ["educationType", "Education Type Label"],
      ["educationTypePlaceholder", "Education Type Placeholder"],
      ["language", "Study Language Label"],
      ["languagePlaceholder", "Study Language Placeholder"],
      ["intake", "Intake Label"],
      ["intakePlaceholder", "Intake Placeholder"],
      ["duration", "Duration Label"],
      ["yearsLabel", "Years Label"],
      ["transferNote", "Transfer Note", "textarea"],
    ],
  },
  {
    title: "Account, Review, and Buttons",
    fields: [
      ["email", "Email Label"],
      ["emailPlaceholder", "Email Placeholder"],
      ["password", "Password Label"],
      ["passwordPlaceholder", "Password Placeholder"],
      ["confirmPassword", "Confirm Password Label"],
      ["confirmPasswordPlaceholder", "Confirm Password Placeholder"],
      ["personalInfo", "Personal Review Title"],
      ["academicInfo", "Academic Review Title"],
      ["accountInfo", "Account Review Title"],
      ["editPersonal", "Edit Personal Label"],
      ["editAcademic", "Edit Academic Label"],
      ["editLogin", "Edit Login Label"],
      ["terms", "Terms Text", "textarea"],
      ["confirm", "Confirmation Text", "textarea"],
      ["back", "Back Button"],
      ["next", "Next Button"],
      ["submit", "Submit Button"],
      ["submitting", "Submitting Label"],
    ],
  },
  {
    title: "Success and Validation Messages",
    fields: [
      ["successTitle", "Success Title"],
      ["successText", "Success Text", "textarea"],
      ["applicationNumber", "Application Number Label"],
      ["dashboard", "Dashboard Button"],
      ["required", "Required Message"],
      ["invalidName", "Invalid Name Message"],
      ["expiredPassport", "Expired Passport Message"],
      ["expiryAfterIssue", "Expiry After Issue Message"],
      ["invalidPhone", "Invalid Phone Message"],
      ["duplicatePhone", "Duplicate Phone Message"],
      ["unavailableProgram", "Unavailable Program Message"],
      ["invalidEmail", "Invalid Email Message"],
      ["passwordWeak", "Weak Password Message"],
      ["passwordMatch", "Password Match Message"],
      ["apiFailed", "API Failed Message"],
    ],
  },
  {
    title: "Option Labels",
    fields: [
      ["options.gender.male", "Gender: Male"],
      ["options.gender.female", "Gender: Female"],
      ["options.passport_type.ordinary", "Passport Type: Ordinary"],
      ["options.passport_type.biometric", "Passport Type: Biometric"],
      ["options.messenger.telegram", "Messenger: Telegram"],
      ["options.messenger.whatsapp", "Messenger: WhatsApp"],
      ["options.messenger.both", "Messenger: Both"],
      ["options.degree_level.bachelor", "Degree: Bachelor"],
      ["options.degree_level.master", "Degree: Master"],
      ["options.degree_level.phd", "Degree: PhD"],
      ["options.degree_level.doctorate", "Degree: Doctorate"],
      ["options.student_type.new", "Student Type: New"],
      ["options.student_type.transfer", "Student Type: Transfer"],
      ["options.education_type.full_time", "Education Type: Full-time"],
      ["options.education_type.part_time", "Education Type: Part-time"],
      ["options.education_type.distance", "Education Type: Distance"],
      ["options.study_language.uzbek", "Study Language: Uzbek"],
      ["options.study_language.russian", "Study Language: Russian"],
      ["options.study_language.english", "Study Language: English"],
    ],
  },
];

const getPath = (source, path) =>
  path.split(".").reduce(
    (value, key) => (value && value[key] !== undefined ? value[key] : ""),
    source,
  );

const setPath = (source, path, value) => {
  const next = { ...(source || {}) };
  const segments = path.split(".");
  let target = next;

  segments.forEach((segment, index) => {
    if (index === segments.length - 1) {
      target[segment] = value;
      return;
    }

    target[segment] = { ...(target[segment] || {}) };
    target = target[segment];
  });

  return next;
};

export default function ApanelApplyPage() {
  const { t } = useLanguage();
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || "en";
  const [page, setPage] = useState(null);
  const [activeLocale, setActiveLocale] = useState(primaryLocale);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const editorLocaleCodes = page?.locales?.length ? page.locales : localeCodes;

  useEffect(() => {
    setActiveLocale((current) => (localeCodes.includes(current) ? current : primaryLocale));
  }, [localeCodes, primaryLocale]);

  useEffect(() => {
    if (!localeCodes.length) return;
    let alive = true;
    setLoading(true);

    apanelService
      .getApplyPage()
      .then((payload) => {
        if (!alive) return;
        setPage({
          ...payload,
          translations: (payload.translations || []).map((translation) => ({
            locale: translation.locale,
            content: translation.content || {},
          })),
        });
      })
      .catch((err) => {
        if (alive) setError(err?.message || "Failed to load apply page CMS.");
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [localeCodes]);

  const activeTranslation = useMemo(
    () => page?.translations?.find((item) => item.locale === activeLocale),
    [activeLocale, page],
  );

  const updateContent = (path, value) => {
    setPage((current) => ({
      ...current,
      translations: current.translations.map((translation) =>
        translation.locale === activeLocale
          ? { ...translation, content: setPath(translation.content, path, value) }
          : translation,
      ),
    }));
  };

  const save = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");
    setSuccess("");

    try {
      const payload = await apanelService.updateApplyPage({
        is_published: page.is_published,
        settings: page.settings || {},
        translations: page.translations,
      });

      setPage(payload);
      setSuccess("Apply page content saved.");
    } catch (err) {
      setError(err?.message || "Failed to save apply page CMS.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="p-8 text-sm font-bold text-gray-500">Loading apply page CMS...</div>;
  }

  return (
    <form onSubmit={save} className="space-y-6 p-6">
      <div className="flex flex-col gap-4 rounded-3xl bg-navy p-6 text-white md:flex-row md:items-center md:justify-between">
        <div>
          <p className="text-xs font-black uppercase tracking-widest text-white/60">CMS</p>
          <h1 className="mt-1 text-2xl font-extrabold !text-white">Apply Page</h1>
          <p className="mt-2 text-sm font-semibold text-white/70">
            Manage all public application form labels, messages, and option text.
          </p>
        </div>
        <label className="flex items-center gap-2 text-sm font-bold">
          <input
            type="checkbox"
            checked={Boolean(page?.is_published)}
            onChange={(event) =>
              setPage((current) => ({ ...current, is_published: event.target.checked }))
            }
          />
          Published
        </label>
      </div>

      <FormError message={error} />
      {success && (
        <div className="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
          {success}
        </div>
      )}

      <div className="flex flex-wrap gap-2">
        {editorLocaleCodes.map((locale) => (
          <button
            key={locale}
            type="button"
            onClick={() => setActiveLocale(locale)}
            className={`rounded-xl px-4 py-2 text-xs font-extrabold uppercase ${
              activeLocale === locale
                ? "bg-primary text-white"
                : "bg-white text-gray-500 border border-gray-100"
            }`}
          >
            {locale}
          </button>
        ))}
      </div>

      {activeTranslation && (
        <div className="space-y-5">
          {fieldGroups.map((group) => (
            <section key={group.title} className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
              <h2 className="text-lg font-extrabold text-navy">{group.title}</h2>
              <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                {group.fields.map(([path, label, type]) => {
                  const value = getPath(activeTranslation.content, path) || "";
                  const multiline = type === "textarea";

                  return (
                    <label
                      key={path}
                      className={multiline ? "text-xs font-bold text-gray-500 md:col-span-2" : "text-xs font-bold text-gray-500"}
                    >
                      {label}
                      {multiline ? (
                        <textarea
                          rows={3}
                          className={`${inputClass} resize-y`}
                          value={value}
                          onChange={(event) => updateContent(path, event.target.value)}
                        />
                      ) : (
                        <input
                          className={inputClass}
                          value={value}
                          onChange={(event) => updateContent(path, event.target.value)}
                        />
                      )}
                    </label>
                  );
                })}
              </div>
            </section>
          ))}
        </div>
      )}

      <button
        disabled={saving}
        className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white disabled:opacity-60"
      >
        {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
        {t("common.save") || "Save"}
      </button>
    </form>
  );
}
