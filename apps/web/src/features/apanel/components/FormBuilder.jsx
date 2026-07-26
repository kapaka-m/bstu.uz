import React, { useState, useEffect } from "react";
import TranslationTabs from "./TranslationTabs";
import MediaPicker from "./MediaPicker";
import { Loader2, AlertCircle } from "lucide-react";

const locales = ["en", "uz", "ru", "ar"];

const formatJsonFieldValue = (value) => {
  if (value === null || value === undefined || value === "") return "";
  if (typeof value === "string") return value;
  return JSON.stringify(value, null, 2);
};

const parseJsonFieldValue = (value, label) => {
  if (value === null || value === undefined || String(value).trim() === "") {
    return null;
  }

  try {
    return JSON.parse(value);
  } catch {
    throw new Error(`${label} must be valid JSON.`);
  }
};

export default function FormBuilder({
  fields = [],
  initialValues = {},
  onSubmit,
  onCancel,
  isSubmitting = false,
  isEdit = false,
  validationErrors = {},
}) {
  const [activeLocale, setActiveLocale] = useState("en");
  const [formState, setFormState] = useState({});
  const [formError, setFormError] = useState("");

  // Compile missing translations checklist for warnings
  const [missingLocales, setMissingLocales] = useState([]);

  // Initialize form state
  useEffect(() => {
    const state = { translations: {} };

    // Setup translations sub-objects
    locales.forEach((loc) => {
      state.translations[loc] = {};
    });

    // Populate values
    fields.forEach((field) => {
      if (field.translated) {
        locales.forEach((loc) => {
          let value = "";
          // Check if initialValues has translations array
          if (Array.isArray(initialValues?.translations)) {
            const found = initialValues.translations.find(
              (t) => t.locale === loc,
            );
            value = found ? found[field.name] : "";
          } else if (initialValues?.translations?.[loc]) {
            value = initialValues.translations[loc][field.name] || "";
          }
          state.translations[loc][field.name] =
            field.type === "json" ? formatJsonFieldValue(value) : value || "";
        });
      } else {
        const value =
          initialValues?.[field.name] !== undefined
            ? initialValues[field.name]
            : "";
        state[field.name] =
          field.type === "json" ? formatJsonFieldValue(value) : value;
        if (field.type === "boolean" && state[field.name] === "") {
          state[field.name] = false;
        }
      }
    });

    setFormState(state);
  }, [fields, initialValues]);

  // Check completeness whenever translations change
  useEffect(() => {
    const missing = [];
    const translatedFields = fields.filter((f) => f.translated);

    if (translatedFields.length > 0 && formState.translations) {
      locales.forEach((loc) => {
        const hasEmpty = translatedFields.some((field) => {
          const val = formState.translations[loc]?.[field.name];
          return !val || String(val).trim() === "";
        });
        if (hasEmpty) {
          missing.push(loc);
        }
      });
    }
    setMissingLocales(missing);
  }, [formState, fields]);

  const handleRootChange = (name, value) => {
    setFormState((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleTranslationChange = (locale, name, value) => {
    setFormState((prev) => {
      const copy = { ...prev.translations };
      copy[locale] = { ...copy[locale], [name]: value };
      return { ...prev, translations: copy };
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (onSubmit) {
      try {
        const preparedState = { ...formState };
        const translatedJsonFields = fields.filter(
          (field) => field.translated && field.type === "json",
        );
        const rootJsonFields = fields.filter(
          (field) => !field.translated && field.type === "json",
        );

        if (translatedJsonFields.length > 0) {
          preparedState.translations = { ...preparedState.translations };
          locales.forEach((loc) => {
            preparedState.translations[loc] = {
              ...preparedState.translations[loc],
            };

            translatedJsonFields.forEach((field) => {
              preparedState.translations[loc][field.name] =
                parseJsonFieldValue(
                  preparedState.translations[loc]?.[field.name],
                  `${field.label} (${loc.toUpperCase()})`,
                );
            });
          });
        }

        rootJsonFields.forEach((field) => {
          preparedState[field.name] = parseJsonFieldValue(
            preparedState[field.name],
            field.label,
          );
        });

        setFormError("");
        onSubmit(preparedState);
      } catch (err) {
        setFormError(err.message || "Please enter valid JSON.");
      }
    }
  };

  // Check if there are any translated fields
  const hasTranslations = fields.some((f) => f.translated);

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {Object.keys(validationErrors).length > 0 && (
        <div className="bg-rose-50 border border-rose-100 p-4 rounded-2xl flex gap-3 text-rose-600 text-xs font-bold">
          <AlertCircle className="w-5 h-5 shrink-0" />
          <div className="space-y-1">
            <p>Please fix the validation errors below:</p>
            <ul className="list-disc pl-4 space-y-0.5 font-semibold">
              {Object.entries(validationErrors).map(([key, errs]) => (
                <li key={key}>
                  {key}: {Array.isArray(errs) ? errs.join(", ") : String(errs)}
                </li>
              ))}
            </ul>
          </div>
        </div>
      )}

      {formError && (
        <div className="bg-rose-50 border border-rose-100 p-4 rounded-2xl flex gap-3 text-rose-600 text-xs font-bold">
          <AlertCircle className="w-5 h-5 shrink-0" />
          <p>{formError}</p>
        </div>
      )}

      {/* Translation Tabs selector if applicable */}
      {hasTranslations && (
        <TranslationTabs
          activeTab={activeLocale}
          onTabChange={setActiveLocale}
          missingLocales={missingLocales}
        />
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
        {fields.map((field) => {
          // Determine if we should show this field in the active tab context
          if (field.translated) {
            // Render translated input inside the tab
            const value =
              formState.translations?.[activeLocale]?.[field.name] || "";
            const key = `field-${field.name}-${activeLocale}`;

            return (
              <div
                key={key}
                className={
                  field.type === "textarea" ||
                  field.type === "richtext" ||
                  field.type === "json"
                    ? "md:col-span-2"
                    : "col-span-1"
                }
              >
                <label className="block text-xs font-bold text-navy mb-1.5 uppercase tracking-wider">
                  {field.label} ({activeLocale.toUpperCase()}){" "}
                  {field.required && <span className="text-red-500">*</span>}
                </label>

                {field.type === "textarea" ||
                field.type === "richtext" ||
                field.type === "json" ? (
                  <textarea
                    value={value}
                    onChange={(e) =>
                      handleTranslationChange(
                        activeLocale,
                        field.name,
                        e.target.value,
                      )
                    }
                    required={field.required && activeLocale === "en"} // Require at least English fallback
                    rows={field.type === "json" ? 8 : 6}
                    className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
                  />
                ) : (
                  <input
                    type="text"
                    value={value}
                    onChange={(e) =>
                      handleTranslationChange(
                        activeLocale,
                        field.name,
                        e.target.value,
                      )
                    }
                    required={field.required && activeLocale === "en"}
                    className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
                  />
                )}
              </div>
            );
          }

          // Otherwise render non-translated field
          const value = formState[field.name];

          return (
            <div
              key={field.name}
              className={
                field.type === "textarea" || field.type === "json"
                  ? "md:col-span-2"
                  : "col-span-1"
              }
            >
              {field.type !== "boolean" && (
                <label className="block text-xs font-bold text-navy mb-1.5 uppercase tracking-wider">
                  {field.label}{" "}
                  {field.required && <span className="text-red-500">*</span>}
                </label>
              )}

              {field.type === "select" ? (
                <select
                  value={value || ""}
                  onChange={(e) => handleRootChange(field.name, e.target.value)}
                  required={field.required}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy cursor-pointer"
                >
                  <option value="">Select option</option>
                  {(field.options || []).map((opt) => (
                    <option
                      key={typeof opt === "object" ? opt.value : opt}
                      value={typeof opt === "object" ? opt.value : opt}
                    >
                      {typeof opt === "object" ? opt.label : opt}
                    </option>
                  ))}
                </select>
              ) : field.type === "checkbox-group" ? (
                (() => {
                  const selectedValues = typeof value === "string"
                    ? value.split(",").map(v => v.trim().toLowerCase())
                    : Array.isArray(value)
                      ? value.map(v => String(v).trim().toLowerCase())
                      : [];

                  const handleCheckboxGroupChange = (optVal, checked) => {
                    let nextList = [...selectedValues];
                    const normOptVal = String(optVal).trim().toLowerCase();
                    if (checked) {
                      if (!nextList.includes(normOptVal)) {
                        nextList.push(normOptVal);
                      }
                    } else {
                      nextList = nextList.filter((v) => v !== normOptVal);
                    }
                    const formattedList = (field.options || []).map(opt => {
                      const val = typeof opt === "object" ? opt.value : opt;
                      const lbl = typeof opt === "object" ? opt.label : opt;
                      return nextList.includes(String(val).toLowerCase()) ? lbl : null;
                    }).filter(Boolean);

                    handleRootChange(field.name, formattedList.join(", "));
                  };

                  return (
                    <div className="flex flex-wrap gap-4 py-2">
                      {(field.options || []).map((opt) => {
                        const optVal = typeof opt === "object" ? opt.value : opt;
                        const optLabel = typeof opt === "object" ? opt.label : opt;
                        const isChecked = selectedValues.includes(String(optVal).toLowerCase());

                        return (
                          <label key={optVal} className="flex items-center gap-2 cursor-pointer">
                            <input
                              type="checkbox"
                              checked={isChecked}
                              onChange={(e) => handleCheckboxGroupChange(optVal, e.target.checked)}
                              className="w-4 h-4 text-primary border-gray-300 rounded-sm focus:ring-primary focus:ring-1"
                            />
                            <span className="text-xs font-semibold text-navy">
                              {optLabel}
                            </span>
                          </label>
                        );
                      })}
                    </div>
                  );
                })()
              ) : field.type === "boolean" ? (
                <label className="flex items-center gap-2 cursor-pointer py-2">
                  <input
                    type="checkbox"
                    checked={!!value}
                    onChange={(e) =>
                      handleRootChange(field.name, e.target.checked)
                    }
                    className="w-4 h-4 text-primary border-gray-300 rounded-sm focus:ring-primary focus:ring-1"
                  />
                  <span className="text-xs font-bold text-navy uppercase tracking-wider">
                    {field.label}
                  </span>
                </label>
              ) : field.type === "media" ? (
                <MediaPicker
                  value={value}
                  onChange={(val) => handleRootChange(field.name, val)}
                  label={field.label}
                />
              ) : field.type === "textarea" || field.type === "json" ? (
                <textarea
                  value={value || ""}
                  onChange={(e) => handleRootChange(field.name, e.target.value)}
                  required={field.required}
                  rows={field.type === "json" ? 8 : 4}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
                />
              ) : (
                <input
                  type={field.type || "text"}
                  value={value !== null && value !== undefined ? value : ""}
                  onChange={(e) =>
                    handleRootChange(
                      field.name,
                      field.type === "number"
                        ? e.target.value === ""
                          ? ""
                          : Number(e.target.value)
                        : e.target.value,
                    )
                  }
                  required={field.required}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
                />
              )}
            </div>
          );
        })}
      </div>

      <div className="flex justify-end gap-3 pt-4 border-t border-gray-50">
        {onCancel && (
          <button
            type="button"
            onClick={onCancel}
            className="px-5 py-2.5 border border-gray-200 hover:border-gray-300 text-navy font-bold text-xs rounded-xl transition-all cursor-pointer bg-white"
          >
            Cancel
          </button>
        )}
        <button
          type="submit"
          disabled={isSubmitting}
          className="bg-primary hover:bg-primary-hover text-white px-6 py-2.5 rounded-xl text-xs font-extrabold shadow-sm hover:shadow-md cursor-pointer transition-all inline-flex items-center gap-1.5"
        >
          {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
          {isEdit ? "Update Record" : "Create Record"}
        </button>
      </div>
    </form>
  );
}
