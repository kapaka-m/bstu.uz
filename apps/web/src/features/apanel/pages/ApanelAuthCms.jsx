import { useEffect, useMemo, useState } from "react";
import { Loader2, Save } from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { authCmsService } from "../../../services/authCmsService";
import { useApanelLocaleCodes } from "../utils/locales";

const pageLabels = {
  login: "Login",
  forgot_password: "Forgot Password",
  reset_password: "Reset Password",
};

const emptyPageTranslation = {
  title: "",
  subtitle: "",
  email_label: "",
  email_placeholder: "",
  password_label: "",
  password_placeholder: "",
  confirm_password_label: "",
  confirm_password_placeholder: "",
  submit_label: "",
  loading_label: "",
  forgot_password_label: "",
  secondary_text: "",
  secondary_action_label: "",
  secondary_action_url: "",
  success_title: "",
  success_message: "",
  back_label: "",
  show_password_label: "",
  hide_password_label: "",
  validation_required_message: "",
  validation_mismatch_message: "",
  error_message: "",
  logo_alt: "",
};

const emptyEmailTranslation = {
  subject: "",
  brand_name: "",
  greeting: "",
  intro: "",
  action_label: "",
  expiry_notice: "",
  no_action_notice: "",
  salutation: "",
  signature: "",
  subcopy: "",
  footer: "",
};

const fieldsByPage = {
  login: [
    ["title", "Title"],
    ["subtitle", "Subtitle", "textarea"],
    ["email_label", "Email Label"],
    ["email_placeholder", "Email Placeholder"],
    ["password_label", "Password Label"],
    ["password_placeholder", "Password Placeholder"],
    ["forgot_password_label", "Forgot Password Label"],
    ["submit_label", "Submit Label"],
    ["loading_label", "Loading Label"],
    ["secondary_text", "Secondary Text"],
    ["secondary_action_label", "Secondary Action Label"],
    ["secondary_action_url", "Secondary Action URL"],
    ["show_password_label", "Show Password Label"],
    ["hide_password_label", "Hide Password Label"],
    ["validation_required_message", "Required Message"],
    ["error_message", "Error Message"],
    ["logo_alt", "Logo Alt"],
  ],
  forgot_password: [
    ["title", "Title"],
    ["subtitle", "Subtitle", "textarea"],
    ["email_label", "Email Label"],
    ["email_placeholder", "Email Placeholder"],
    ["submit_label", "Submit Label"],
    ["loading_label", "Loading Label"],
    ["success_title", "Success Title"],
    ["success_message", "Success Message", "textarea"],
    ["back_label", "Back Label"],
    ["error_message", "Error Message"],
    ["logo_alt", "Logo Alt"],
  ],
  reset_password: [
    ["title", "Title"],
    ["email_label", "Email Label"],
    ["email_placeholder", "Email Placeholder"],
    ["password_label", "Password Label"],
    ["password_placeholder", "Password Placeholder"],
    ["confirm_password_label", "Confirm Password Label"],
    ["confirm_password_placeholder", "Confirm Password Placeholder"],
    ["submit_label", "Submit Label"],
    ["loading_label", "Loading Label"],
    ["success_title", "Success Title"],
    ["success_message", "Success Message", "textarea"],
    ["back_label", "Back Label"],
    ["show_password_label", "Show Password Label"],
    ["hide_password_label", "Hide Password Label"],
    ["validation_required_message", "Required Message"],
    ["validation_mismatch_message", "Mismatch Message"],
    ["error_message", "Error Message"],
    ["logo_alt", "Logo Alt"],
  ],
};

const emailFields = [
  ["subject", "Subject"],
  ["brand_name", "Brand Name"],
  ["greeting", "Greeting"],
  ["intro", "Intro", "textarea"],
  ["action_label", "Button Label"],
  ["expiry_notice", "Expiry Notice"],
  ["no_action_notice", "No Action Notice", "textarea"],
  ["salutation", "Salutation"],
  ["signature", "Signature"],
  ["subcopy", "Subcopy", "textarea"],
  ["footer", "Footer"],
];

const emailSettingFields = [
  ["brand_url", "Brand URL"],
  ["button_color", "Button Color"],
  ["frontend_reset_path", "Reset Page Path"],
  ["expire_minutes", "Expire Minutes"],
];

const inputClass =
  "mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none";

const normalizeTranslations = (translations = [], localeCodes, emptyShape) =>
  Object.fromEntries(
    localeCodes.map((locale) => {
      const existing = translations.find((item) => item.locale === locale);
      return [locale, { ...emptyShape, ...(existing || {}) }];
    }),
  );

const normalizePage = (page, localeCodes) => ({
  page_key: page.page_key,
  is_active: Boolean(page.is_active ?? true),
  settings: page.settings || {},
  translations: normalizeTranslations(page.translations, localeCodes, emptyPageTranslation),
});

const normalizeEmailTemplate = (template, localeCodes) => ({
  template_key: template.template_key,
  is_active: Boolean(template.is_active ?? true),
  settings: template.settings || {},
  translations: normalizeTranslations(template.translations, localeCodes, emptyEmailTranslation),
});

export default function ApanelAuthCms() {
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || "en";
  const [pages, setPages] = useState([]);
  const [emailTemplates, setEmailTemplates] = useState([]);
  const [activeTab, setActiveTab] = useState("login");
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
    apanelService
      .getAuthCms()
      .then((payload) => {
        if (!alive) return;
        setPages((payload.pages || []).map((page) => normalizePage(page, localeCodes)));
        setEmailTemplates(
          (payload.email_templates || []).map((template) =>
            normalizeEmailTemplate(template, localeCodes),
          ),
        );
      })
      .catch((err) => {
        if (alive) setError(err?.message || "Failed to load auth CMS.");
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [localeCodes]);

  const activePage = useMemo(
    () => pages.find((page) => page.page_key === activeTab),
    [pages, activeTab],
  );

  const emailTemplate = emailTemplates.find((template) => template.template_key === "password_reset");

  const updatePageTranslation = (key, value) => {
    setPages((current) =>
      current.map((page) =>
        page.page_key === activeTab
          ? {
              ...page,
              translations: {
                ...page.translations,
                [activeLocale]: {
                  ...page.translations[activeLocale],
                  [key]: value,
                },
              },
            }
          : page,
      ),
    );
  };

  const updateEmailTranslation = (key, value) => {
    setEmailTemplates((current) =>
      current.map((template) =>
        template.template_key === "password_reset"
          ? {
              ...template,
              translations: {
                ...template.translations,
                [activeLocale]: {
                  ...template.translations[activeLocale],
                  [key]: value,
                },
              },
            }
          : template,
      ),
    );
  };

  const updateEmailSetting = (key, value) => {
    setEmailTemplates((current) =>
      current.map((template) =>
        template.template_key === "password_reset"
          ? {
              ...template,
              settings: {
                ...(template.settings || {}),
                [key]: key === "expire_minutes" ? Number(value || 0) : value,
              },
            }
          : template,
      ),
    );
  };

  const save = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError("");

    try {
      await apanelService.updateAuthCms({
        pages,
        email_templates: emailTemplates,
      });
      authCmsService.clearCache();
    } catch (err) {
      setError(err?.message || "Failed to save auth CMS.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex min-h-80 items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <form onSubmit={save} className="space-y-6">
      <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <h1 className="text-2xl font-black uppercase tracking-wide text-navy">
          CMS Auth
        </h1>
        <p className="mt-2 text-sm font-semibold text-gray-500">
          Manage login, password reset pages, and reset email templates in every active language.
        </p>
      </div>

      <FormError message={error} />

      <div className="flex flex-wrap gap-2 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm">
        {pages.map((page) => (
          <button
            type="button"
            key={page.page_key}
            onClick={() => setActiveTab(page.page_key)}
            className={`rounded-xl px-4 py-2 text-xs font-extrabold ${
              activeTab === page.page_key
                ? "bg-primary text-white"
                : "bg-gray-50 text-gray-600 hover:bg-primary/10 hover:text-primary"
            }`}
          >
            {pageLabels[page.page_key] || page.page_key}
          </button>
        ))}
        <button
          type="button"
          onClick={() => setActiveTab("password_reset_email")}
          className={`rounded-xl px-4 py-2 text-xs font-extrabold ${
            activeTab === "password_reset_email"
              ? "bg-primary text-white"
              : "bg-gray-50 text-gray-600 hover:bg-primary/10 hover:text-primary"
          }`}
        >
          Reset Email Template
        </button>
      </div>

      <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <div className="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 className="text-xl font-black text-navy">
              {activeTab === "password_reset_email"
                ? "Reset Email Template"
                : pageLabels[activeTab] || activeTab}
            </h2>
            <p className="mt-1 text-xs font-bold text-gray-400">
              {activeTab === "password_reset_email"
                ? "Template key: password_reset"
                : `Page key: ${activeTab}`}
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
                    : "bg-gray-50 text-gray-500"
                }`}
              >
                {locale}
              </button>
            ))}
          </div>
        </div>

        {activeTab !== "password_reset_email" && activePage && (
          <div className="grid gap-4 md:grid-cols-2">
            {(fieldsByPage[activeTab] || []).map(([key, label, type]) => (
              <label key={key} className="text-xs font-bold text-gray-500">
                {label}
                {type === "textarea" ? (
                  <textarea
                    rows={4}
                    className={`${inputClass} resize-y`}
                    value={activePage.translations[activeLocale]?.[key] || ""}
                    onChange={(event) => updatePageTranslation(key, event.target.value)}
                  />
                ) : (
                  <input
                    className={inputClass}
                    value={activePage.translations[activeLocale]?.[key] || ""}
                    onChange={(event) => updatePageTranslation(key, event.target.value)}
                  />
                )}
              </label>
            ))}
          </div>
        )}

        {activeTab === "password_reset_email" && emailTemplate && (
          <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
              {emailSettingFields.map(([key, label]) => (
                <label key={key} className="text-xs font-bold text-gray-500">
                  {label}
                  <input
                    type={key === "expire_minutes" ? "number" : "text"}
                    min={key === "expire_minutes" ? "1" : undefined}
                    className={inputClass}
                    value={emailTemplate.settings?.[key] ?? ""}
                    onChange={(event) => updateEmailSetting(key, event.target.value)}
                  />
                </label>
              ))}
            </div>

            <div className="grid gap-4 md:grid-cols-2">
              {emailFields.map(([key, label, type]) => (
                <label key={key} className="text-xs font-bold text-gray-500">
                  {label}
                  {type === "textarea" ? (
                    <textarea
                      rows={4}
                      className={`${inputClass} resize-y`}
                      value={emailTemplate.translations[activeLocale]?.[key] || ""}
                      onChange={(event) => updateEmailTranslation(key, event.target.value)}
                    />
                  ) : (
                    <input
                      className={inputClass}
                      value={emailTemplate.translations[activeLocale]?.[key] || ""}
                      onChange={(event) => updateEmailTranslation(key, event.target.value)}
                    />
                  )}
                </label>
              ))}
            </div>

            <div className="rounded-2xl border border-primary/10 bg-primary/5 p-4 text-xs font-bold text-navy">
              Available placeholders: {"{minutes}"}, {"{year}"}, {"{action_label}"}
            </div>
          </div>
        )}
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
