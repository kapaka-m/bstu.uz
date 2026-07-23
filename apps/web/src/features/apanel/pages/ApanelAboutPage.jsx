import React, {
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";
import { Plus, Save, Trash2, Upload } from "lucide-react";
import { apanelService } from "../../../services/apanelService";
import ConfirmDialog from "../components/ConfirmDialog";

const API_ORIGIN = (
  import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1"
).replace(/\/api\/v1\/?$/, "");

const LOCALES = [
  { code: "en", label: "English" },
  { code: "uz", label: "O'zbek" },
  { code: "ru", label: "Русский" },
  { code: "ar", label: "العربية" },
];

const SECTIONS = [
  { key: "controls", label: "Page Controls" },
  { key: "hero", label: "Hero" },
  { key: "identity", label: "Identity" },
  { key: "goals", label: "Mission & Vision" },
  { key: "values", label: "Values" },
  { key: "stats", label: "Stats" },
  { key: "facultiesList", label: "Faculties" },
  { key: "rector", label: "Rector" },
  { key: "timeline", label: "Timeline" },
];

const defaultContent = {
  hero: {},
  identity: {},
  goals: {},
  values: {},
  stats: { items: [] },
  facultiesList: { items: [] },
  rector: {},
  timeline: { items: [] },
};

const emptyForm = {
  hero_contact_url: "",
  hero_campus_url: "",
  identity_image: "",
  rector_profile_slug: "",
  is_published: true,
  translations: Object.fromEntries(
    LOCALES.map((locale) => [
      locale.code,
      { content: structuredClone(defaultContent) },
    ]),
  ),
};

const statIcons = [
  "users",
  "graduation-cap",
  "building",
  "book-open",
  "microscope",
  "cpu",
  "globe",
  "landmark",
];
const statColors = [
  "from-blue-500 to-cyan-500",
  "from-purple-500 to-indigo-500",
  "from-emerald-500 to-teal-500",
  "from-amber-500 to-orange-500",
  "from-rose-500 to-pink-500",
  "from-indigo-500 to-blue-500",
  "from-cyan-500 to-teal-500",
  "from-violet-500 to-purple-500",
];
const facultyColors = [
  "border-blue-500/20 hover:border-blue-500",
  "border-purple-500/20 hover:border-purple-500",
  "border-emerald-500/20 hover:border-emerald-500",
  "border-amber-500/20 hover:border-amber-500",
];

const clone = (value) => JSON.parse(JSON.stringify(value || {}));

const sortDeep = (value) => {
  if (Array.isArray(value)) return value.map(sortDeep);
  if (!value || typeof value !== "object") return value;
  return Object.keys(value)
    .sort()
    .reduce((result, key) => {
      result[key] = sortDeep(value[key]);
      return result;
    }, {});
};

const sameJson = (left, right) =>
  JSON.stringify(sortDeep(left || {})) === JSON.stringify(sortDeep(right || {}));

const resolveAssetUrl = (path) => {
  if (!path) return "";
  if (
    path.startsWith("http://") ||
    path.startsWith("https://") ||
    path.startsWith("/")
  ) {
    return path;
  }
  return `${API_ORIGIN}/storage/${path}`;
};

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
        <textarea
          value={value || ""}
          onChange={(event) => onChange(event.target.value)}
          rows={4}
          className={`${className} resize-y leading-relaxed`}
        />
      ) : (
        <input
          value={value || ""}
          onChange={(event) => onChange(event.target.value)}
          className={className}
        />
      )}
    </label>
  );
}

function SelectField({ label, value, options, onChange }) {
  return (
    <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
      {label}
      <select
        value={value || ""}
        onChange={(event) => onChange(event.target.value)}
        className="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 outline-none focus:border-primary"
      >
        {options.map((option) => (
          <option key={option} value={option}>
            {option}
          </option>
        ))}
      </select>
    </label>
  );
}

export default function ApanelAboutPage() {
  const [form, setForm] = useState(emptyForm);
  const [activeLocale, setActiveLocale] = useState("en");
  const [activeSection, setActiveSection] = useState("hero");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [confirmAction, setConfirmAction] = useState(null);
  const [timelineDraft, setTimelineDraft] = useState(null);
  const formRef = useRef(emptyForm);
  const savedControlsRef = useRef({
    hero_contact_url: "",
    hero_campus_url: "",
    rector_profile_slug: "",
    identity_image: "",
  });

  const content = form.translations[activeLocale]?.content || defaultContent;
  const activeLocaleLabel = useMemo(
    () =>
      LOCALES.find((locale) => locale.code === activeLocale)?.label ||
      activeLocale,
    [activeLocale],
  );

  const applyPageToForm = useCallback((page) => {
    const translations = clone(emptyForm.translations);
    (page.translations || []).forEach((translation) => {
      translations[translation.locale] = {
        content: { ...clone(defaultContent), ...clone(translation.content) },
      };
    });

    const controls = {
      hero_contact_url: page.hero_contact_url || "",
      hero_campus_url: page.hero_campus_url || "",
      identity_image: page.identity_image || page.identity_image_url || "",
      rector_profile_slug: page.rector_profile_slug || "",
    };
    savedControlsRef.current = controls;

    const nextForm = {
      ...controls,
      is_published: Boolean(page.is_published),
      translations,
    };
    formRef.current = nextForm;
    setForm(nextForm);
  }, []);

  const loadAboutPageForm = useCallback(
    async (isAlive = () => true) => {
      const page = await apanelService.getAboutPage();
      if (isAlive()) applyPageToForm(page);
      return page;
    },
    [applyPageToForm],
  );

  useEffect(() => {
    let alive = true;
    setLoading(true);

    loadAboutPageForm(() => alive)
      .catch((err) =>
        setError(err?.message || "Failed to load About page CMS."),
      )
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [loadAboutPageForm]);

  const updateField = (field, value) => {
    setForm((current) => {
      const updated = { ...current, [field]: value };
      formRef.current = updated;
      return updated;
    });
  };

  const updateContent = (path, value) => {
    setForm((current) => {
      const translations = clone(current.translations);
      const nextContent = {
        ...clone(defaultContent),
        ...clone(translations[activeLocale]?.content),
      };
      const keys = path.split(".");
      let target = nextContent;
      keys.slice(0, -1).forEach((key) => {
        target[key] =
          target[key] && typeof target[key] === "object" ? target[key] : {};
        target = target[key];
      });
      target[keys.at(-1)] = value;
      translations[activeLocale] = { content: nextContent };
      const updated = { ...current, translations };
      formRef.current = updated;
      return updated;
    });
  };

  const updateArrayItem = (path, index, field, value) => {
    const items = clone(
      path.split(".").reduce((acc, key) => acc?.[key], content) || [],
    );
    items[index] = { ...(items[index] || {}), [field]: value };
    updateContent(path, items);
  };

  const addArrayItem = (path, item) => {
    setForm((current) => {
      const translations = clone(current.translations);
      LOCALES.forEach((locale) => {
        const nextContent = {
          ...clone(defaultContent),
          ...clone(translations[locale.code]?.content),
        };
        const keys = path.split(".");
        let target = nextContent;
        keys.slice(0, -1).forEach((key) => {
          target[key] =
            target[key] && typeof target[key] === "object" ? target[key] : {};
          target = target[key];
        });
        const items = Array.isArray(target[keys.at(-1)])
          ? target[keys.at(-1)]
          : [];
        target[keys.at(-1)] = [
          ...items,
          locale.code === activeLocale ? clone(item) : {},
        ];
        translations[locale.code] = { content: nextContent };
      });
      const updated = { ...current, translations };
      formRef.current = updated;
      return updated;
    });
  };

  const formWithAddedArrayItem = (sourceForm, path, itemFactory) => {
    const nextForm = clone(sourceForm);
    const translations = clone(nextForm.translations);

    LOCALES.forEach((locale) => {
      const nextContent = {
        ...clone(defaultContent),
        ...clone(translations[locale.code]?.content),
      };
      const keys = path.split(".");
      let target = nextContent;
      keys.slice(0, -1).forEach((key) => {
        target[key] =
          target[key] && typeof target[key] === "object" ? target[key] : {};
        target = target[key];
      });
      const items = Array.isArray(target[keys.at(-1)])
        ? target[keys.at(-1)]
        : [];
      target[keys.at(-1)] = [...items, clone(itemFactory(locale.code))];
      translations[locale.code] = { content: nextContent };
    });

    return { ...nextForm, translations };
  };

  const buildPayload = (sourceForm) => ({
    hero_contact_url:
      sourceForm.hero_contact_url ||
      savedControlsRef.current.hero_contact_url ||
      "",
    hero_campus_url:
      sourceForm.hero_campus_url ||
      savedControlsRef.current.hero_campus_url ||
      "",
    identity_image:
      sourceForm.identity_image ||
      savedControlsRef.current.identity_image ||
      "",
    rector_profile_slug:
      sourceForm.rector_profile_slug ||
      savedControlsRef.current.rector_profile_slug ||
      "",
    is_published: Boolean(sourceForm.is_published),
    translations: Object.fromEntries(
      LOCALES.map((locale) => [
        locale.code,
        {
          content: {
            ...clone(defaultContent),
            ...clone(sourceForm.translations?.[locale.code]?.content),
          },
        },
      ]),
    ),
  });

  const persistForm = async (
    sourceForm,
    message = "About page content saved successfully.",
  ) => {
    setSaving(true);
    setError("");
    setSuccess("");
    const payload = buildPayload(sourceForm);
    await apanelService.updateAboutPage(payload);
    const reloadedPage = await loadAboutPageForm();
    const reloadedTranslations = Object.fromEntries(
      (reloadedPage?.translations || []).map((translation) => [
        translation.locale,
        translation.content || {},
      ]),
    );
    const activePayload = payload.translations?.[activeLocale]?.content || {};
    const activeSaved = reloadedTranslations[activeLocale] || {};

    if (!sameJson(activeSaved, activePayload)) {
      throw new Error(
        "The server accepted the request, but the saved content did not match the submitted data. Please reload the CMS and try again.",
      );
    }
    setSuccess(message);
  };

  const removeArrayItemFromAllLocales = async (path, index) => {
    const nextForm = await new Promise((resolve) => {
      setForm((current) => {
        const translations = clone(current.translations);
        LOCALES.forEach((locale) => {
          const nextContent = {
            ...clone(defaultContent),
            ...clone(translations[locale.code]?.content),
          };
          const keys = path.split(".");
          let target = nextContent;
          keys.slice(0, -1).forEach((key) => {
            target[key] =
              target[key] && typeof target[key] === "object" ? target[key] : {};
            target = target[key];
          });
          const items = Array.isArray(target[keys.at(-1)])
            ? target[keys.at(-1)]
            : [];
          target[keys.at(-1)] = items.filter(
            (_, itemIndex) => itemIndex !== index,
          );
          translations[locale.code] = { content: nextContent };
        });
        const updated = { ...current, translations };
        formRef.current = updated;
        resolve(updated);
        return updated;
      });
    });

    await persistForm(nextForm, "Item deleted successfully.");
  };

  const requestRemoveArrayItem = (path, index, label) => {
    setConfirmAction({
      title: `Delete ${label}?`,
      message: `This will remove ${label} from all languages and save the change to the database.`,
      confirmText: "Delete",
      onConfirm: async () => {
        try {
          setConfirmAction(null);
          await removeArrayItemFromAllLocales(path, index);
        } catch (err) {
          setError(errorMessage(err, `Failed to delete ${label}.`));
        } finally {
          setSaving(false);
        }
      },
    });
  };

  const handleUpload = async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    try {
      setUploading(true);
      setError("");
      const media = await apanelService.uploadMedia(file, {
        alt_key: "about.identity.image",
        type: "image",
      });
      updateField("identity_image", media.path || media.url || "");
    } catch (err) {
      setError(errorMessage(err, "Failed to upload image."));
    } finally {
      setUploading(false);
      event.target.value = "";
    }
  };

  const handleSave = async () => {
    try {
      await persistForm(formRef.current);
    } catch (err) {
      setError(errorMessage(err, "Failed to save About page content."));
    } finally {
      setSaving(false);
    }
  };

  const handleAddTimelineItem = async () => {
    const draft = {
      year: timelineDraft?.year?.trim() || "",
      title: timelineDraft?.title?.trim() || "",
      desc: timelineDraft?.desc?.trim() || "",
    };

    if (!draft.year || !draft.title || !draft.desc) {
      setError(
        "Please fill Year, Title, and Description before adding the timeline item.",
      );
      return;
    }

    try {
      setError("");
      setSuccess("");
      const nextForm = formWithAddedArrayItem(
        formRef.current,
        "timeline.items",
        (localeCode) => ({
          year: draft.year,
          title: localeCode === activeLocale ? draft.title : "",
          desc: localeCode === activeLocale ? draft.desc : "",
        }),
      );
      formRef.current = nextForm;
      setForm(nextForm);
      setTimelineDraft(null);
      await persistForm(nextForm, "Timeline item added successfully.");
    } catch (err) {
      setError(errorMessage(err, "Failed to add timeline item."));
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="p-8 text-sm font-bold text-gray-500">
        Loading About page CMS...
      </div>
    );
  }

  const renderBasicSection = (sectionKey, fields) => (
    <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
      {fields.map((field) => (
        <Field
          key={field.key}
          label={field.label}
          value={content[sectionKey]?.[field.key]}
          multiline={field.multiline}
          onChange={(value) =>
            updateContent(`${sectionKey}.${field.key}`, value)
          }
        />
      ))}
    </div>
  );

  const renderStats = () => (
    <div className="space-y-4">
      {(content.stats?.items || []).map((item, index) => (
        <div
          key={index}
          className="rounded-2xl border border-gray-100 bg-gray-50 p-4"
        >
          <div className="mb-4 flex items-center justify-between">
            <h3 className="text-sm font-extrabold text-navy">
              Stat #{index + 1}
            </h3>
            <button
              type="button"
              onClick={() =>
                requestRemoveArrayItem(
                  "stats.items",
                  index,
                  `Stat #${index + 1}`,
                )
              }
              className="text-rose-600"
            >
              <Trash2 className="h-4 w-4" />
            </button>
          </div>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Field
              label="Number"
              value={item.number}
              onChange={(value) =>
                updateArrayItem("stats.items", index, "number", value)
              }
            />
            <Field
              label="Label"
              value={item.label}
              onChange={(value) =>
                updateArrayItem("stats.items", index, "label", value)
              }
            />
            <Field
              label="Description"
              value={item.desc}
              multiline
              onChange={(value) =>
                updateArrayItem("stats.items", index, "desc", value)
              }
            />
            <SelectField
              label="Icon"
              value={item.icon}
              options={statIcons}
              onChange={(value) =>
                updateArrayItem("stats.items", index, "icon", value)
              }
            />
            <SelectField
              label="Color"
              value={item.color}
              options={statColors}
              onChange={(value) =>
                updateArrayItem("stats.items", index, "color", value)
              }
            />
          </div>
        </div>
      ))}
      <button
        type="button"
        onClick={() =>
          addArrayItem("stats.items", {
            number: "",
            label: "",
            desc: "",
            icon: "users",
            color: statColors[0],
          })
        }
        className="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2 text-sm font-extrabold text-primary"
      >
        <Plus className="h-4 w-4" />
        Add Stat
      </button>
    </div>
  );

  const renderFaculties = () => (
    <div className="space-y-4">
      {(content.facultiesList?.items || []).map((item, index) => (
        <div
          key={index}
          className="rounded-2xl border border-gray-100 bg-gray-50 p-4"
        >
          <div className="mb-4 flex items-center justify-between">
            <h3 className="text-sm font-extrabold text-navy">
              Faculty #{index + 1}
            </h3>
            <button
              type="button"
              onClick={() =>
                requestRemoveArrayItem(
                  "facultiesList.items",
                  index,
                  `Faculty #${index + 1}`,
                )
              }
              className="text-rose-600"
            >
              <Trash2 className="h-4 w-4" />
            </button>
          </div>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Field
              label="ID"
              value={item.id}
              onChange={(value) =>
                updateArrayItem("facultiesList.items", index, "id", value)
              }
            />
            <Field
              label="Name"
              value={item.name}
              onChange={(value) =>
                updateArrayItem("facultiesList.items", index, "name", value)
              }
            />
            <Field
              label="Dean"
              value={item.dean}
              onChange={(value) =>
                updateArrayItem("facultiesList.items", index, "dean", value)
              }
            />
            <Field
              label="Count"
              value={item.count}
              onChange={(value) =>
                updateArrayItem("facultiesList.items", index, "count", value)
              }
            />
            <Field
              label="Link"
              value={item.link}
              onChange={(value) =>
                updateArrayItem("facultiesList.items", index, "link", value)
              }
            />
            <SelectField
              label="Color"
              value={item.color}
              options={facultyColors}
              onChange={(value) =>
                updateArrayItem("facultiesList.items", index, "color", value)
              }
            />
            <Field
              label="Description"
              value={item.desc}
              multiline
              onChange={(value) =>
                updateArrayItem("facultiesList.items", index, "desc", value)
              }
            />
          </div>
        </div>
      ))}
      <button
        type="button"
        onClick={() =>
          addArrayItem("facultiesList.items", {
            id: "",
            name: "",
            dean: "",
            count: "",
            desc: "",
            link: "",
            color: facultyColors[0],
          })
        }
        className="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2 text-sm font-extrabold text-primary"
      >
        <Plus className="h-4 w-4" />
        Add Faculty
      </button>
    </div>
  );

  const renderTimeline = () => (
    <div className="space-y-4">
      {(content.timeline?.items || []).map((item, index) => (
        <div
          key={index}
          className="rounded-2xl border border-gray-100 bg-gray-50 p-4"
        >
          <div className="mb-4 flex items-center justify-between">
            <h3 className="text-sm font-extrabold text-navy">
              Timeline Item #{index + 1}
            </h3>
            <button
              type="button"
              onClick={() =>
                requestRemoveArrayItem(
                  "timeline.items",
                  index,
                  `Timeline Item #${index + 1}`,
                )
              }
              className="text-rose-600"
            >
              <Trash2 className="h-4 w-4" />
            </button>
          </div>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Field
              label="Year"
              value={item.year}
              onChange={(value) =>
                updateArrayItem("timeline.items", index, "year", value)
              }
            />
            <Field
              label="Title"
              value={item.title}
              onChange={(value) =>
                updateArrayItem("timeline.items", index, "title", value)
              }
            />
            <Field
              label="Description"
              value={item.desc}
              multiline
              onChange={(value) =>
                updateArrayItem("timeline.items", index, "desc", value)
              }
            />
          </div>
        </div>
      ))}
      <button
        type="button"
        onClick={() => setTimelineDraft({ year: "", title: "", desc: "" })}
        className="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2 text-sm font-extrabold text-primary"
      >
        <Plus className="h-4 w-4" />
        Add Timeline Item
      </button>
    </div>
  );

  const renderControls = () => (
    <div className="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_1fr_1fr_1.25fr]">
      <Field
        label="Contact Button URL"
        value={form.hero_contact_url}
        onChange={(value) => updateField("hero_contact_url", value)}
      />
      <Field
        label="Campus Button URL"
        value={form.hero_campus_url}
        onChange={(value) => updateField("hero_campus_url", value)}
      />
      <Field
        label="Rector Profile Slug"
        value={form.rector_profile_slug}
        onChange={(value) => updateField("rector_profile_slug", value)}
      />
      <Field
        label="Identity Image URL or storage path"
        value={form.identity_image}
        onChange={(value) => updateField("identity_image", value)}
      />
      <div className="rounded-2xl border border-gray-100 bg-gray-50 p-3">
        <p className="mb-2 text-xs font-extrabold uppercase tracking-wide text-gray-400">
          Identity Image Preview
        </p>
        <div className="aspect-16/10 overflow-hidden rounded-xl border border-gray-100 bg-white">
          {form.identity_image ? (
            <img
              src={resolveAssetUrl(form.identity_image)}
              alt="About identity preview"
              className="h-full w-full object-cover"
              onError={(event) => {
                event.currentTarget.style.display = "none";
              }}
            />
          ) : (
            <div className="flex h-full items-center justify-center text-xs font-bold text-gray-400">
              No image
            </div>
          )}
        </div>
      </div>
      <div className="flex flex-col justify-end gap-3">
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
        <label className="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-primary/40 bg-primary/5 px-4 py-2.5 text-xs font-extrabold text-primary">
          <Upload className="h-4 w-4" />
          {uploading ? "Uploading..." : "Upload Identity Image"}
          <input
            type="file"
            accept="image/*"
            onChange={handleUpload}
            className="hidden"
          />
        </label>
      </div>
    </div>
  );

  const sectionContent = {
    controls: renderControls(),
    hero: renderBasicSection("hero", [
      { key: "badge", label: "Badge" },
      { key: "title", label: "Title" },
      { key: "subtitle", label: "Subtitle", multiline: true },
      { key: "admissionsBtn", label: "Contact Button Label" },
      { key: "campusBtn", label: "Campus Button Label" },
      { key: "profileTitle", label: "Profile Card Title" },
      { key: "autonomyTitle", label: "Autonomy Title" },
      { key: "autonomyDesc", label: "Autonomy Description", multiline: true },
      { key: "qsTitle", label: "QS Title" },
      { key: "qsDesc", label: "QS Description", multiline: true },
      { key: "legacyTitle", label: "Legacy Title" },
      { key: "legacyDesc", label: "Legacy Description", multiline: true },
    ]),
    identity: renderBasicSection("identity", [
      { key: "badge", label: "Badge" },
      { key: "title", label: "Title" },
      { key: "desc1", label: "Description 1", multiline: true },
      { key: "desc2", label: "Description 2", multiline: true },
    ]),
    goals: renderBasicSection("goals", [
      { key: "badge", label: "Badge" },
      { key: "title", label: "Title" },
      { key: "missionTitle", label: "Mission Title" },
      { key: "missionDesc", label: "Mission Description", multiline: true },
      { key: "visionTitle", label: "Vision Title" },
      { key: "visionDesc", label: "Vision Description", multiline: true },
    ]),
    values: renderBasicSection("values", [
      { key: "badge", label: "Badge" },
      { key: "title", label: "Title" },
      { key: "integrityTitle", label: "Integrity Title" },
      { key: "integrityDesc", label: "Integrity Description", multiline: true },
      { key: "innovationTitle", label: "Innovation Title" },
      {
        key: "innovationDesc",
        label: "Innovation Description",
        multiline: true,
      },
      { key: "inclusivityTitle", label: "Inclusivity Title" },
      {
        key: "inclusivityDesc",
        label: "Inclusivity Description",
        multiline: true,
      },
    ]),
    stats: (
      <div className="space-y-6">
        {renderBasicSection("stats", [
          { key: "badge", label: "Badge" },
          { key: "title", label: "Title" },
          { key: "subtitle", label: "Subtitle", multiline: true },
        ])}
        {renderStats()}
      </div>
    ),
    facultiesList: (
      <div className="space-y-6">
        {renderBasicSection("facultiesList", [
          { key: "badge", label: "Badge" },
          { key: "title", label: "Title" },
          { key: "subtitle", label: "Subtitle", multiline: true },
          { key: "facultyBadge", label: "Faculty Card Badge" },
          { key: "deanLabel", label: "Dean Label" },
          { key: "exploreBtn", label: "Explore Button Label" },
        ])}
        {renderFaculties()}
      </div>
    ),
    rector: renderBasicSection("rector", [
      { key: "badge", label: "Badge" },
      { key: "title", label: "Title" },
      { key: "name", label: "Fallback Name" },
      { key: "degree", label: "Fallback Degree" },
      { key: "quote1", label: "Quote 1", multiline: true },
      { key: "quote2", label: "Quote 2", multiline: true },
      { key: "profileBtn", label: "Profile Button Label" },
      { key: "appealBadge", label: "Appeal Badge" },
      { key: "appealTitle", label: "Appeal Title" },
      { key: "appealQuote", label: "Appeal Quote", multiline: true },
      { key: "studentsTitle", label: "Students Card Title" },
      {
        key: "studentsDesc",
        label: "Students Card Description",
        multiline: true,
      },
      { key: "parentsTitle", label: "Parents Card Title" },
      {
        key: "parentsDesc",
        label: "Parents Card Description",
        multiline: true,
      },
      { key: "teachersTitle", label: "Teachers Card Title" },
      {
        key: "teachersDesc",
        label: "Teachers Card Description",
        multiline: true,
      },
    ]),
    timeline: (
      <div className="space-y-6">
        {renderBasicSection("timeline", [
          { key: "badge", label: "Badge" },
          { key: "title", label: "Title" },
          { key: "subtitle", label: "Subtitle", multiline: true },
        ])}
        {renderTimeline()}
      </div>
    ),
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 className="mt-1 text-2xl font-black text-navy">About Page CMS</h1>
          <p className="mt-1 text-sm font-semibold text-gray-500">
            Full control for the public About page at /about.
          </p>
        </div>
        <button
          type="button"
          onClick={handleSave}
          disabled={saving}
          className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-primary-hover disabled:opacity-60"
        >
          <Save className="h-4 w-4" />
          {saving ? "Saving..." : "Save About Page"}
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
        <div className="border-b border-gray-100 pb-4">
          <div className="flex flex-wrap gap-2">
            {SECTIONS.map((section) => (
              <button
                key={section.key}
                type="button"
                onClick={() => setActiveSection(section.key)}
                className={`rounded-xl px-4 py-2 text-xs font-extrabold transition ${
                  activeSection === section.key
                    ? "bg-navy text-white shadow-sm"
                    : "bg-gray-100 text-gray-500 hover:bg-gray-200"
                }`}
              >
                {section.label}
              </button>
            ))}
          </div>
        </div>

        {activeSection !== "controls" && (
          <div className="border-b border-gray-100 py-4">
            <div className="inline-flex flex-wrap rounded-2xl bg-gray-100 p-1">
              {LOCALES.map((locale) => (
                <button
                  key={locale.code}
                  type="button"
                  onClick={() => setActiveLocale(locale.code)}
                  className={`rounded-xl px-4 py-2 text-xs font-extrabold transition ${
                    activeLocale === locale.code
                      ? "bg-primary text-white shadow-sm"
                      : "text-gray-500 hover:text-navy"
                  }`}
                >
                  {locale.label}
                </button>
              ))}
            </div>
          </div>
        )}

        <div className="pt-6">
          <div className="mb-6">
            {activeSection !== "controls" && (
              <p className="text-xs font-extrabold uppercase tracking-widest text-primary">
                {activeLocaleLabel}
              </p>
            )}
            <h2 className="mt-1 text-xl font-black text-navy">
              {SECTIONS.find((section) => section.key === activeSection)?.label}
            </h2>
          </div>
          {sectionContent[activeSection]}
        </div>
      </div>
      <ConfirmDialog
        isOpen={Boolean(confirmAction)}
        title={confirmAction?.title}
        message={confirmAction?.message}
        confirmText={confirmAction?.confirmText}
        onConfirm={confirmAction?.onConfirm}
        onCancel={() => setConfirmAction(null)}
      />
      {timelineDraft && (
        <div className="fixed inset-0 z-9999 flex items-center justify-center bg-navy/40 p-4 backdrop-blur-xs">
          <div className="w-full max-w-lg rounded-3xl border border-gray-100 bg-white p-6 shadow-2xl">
            <div className="mb-5">
              <p className="text-xs font-extrabold uppercase tracking-widest text-primary">
                {activeLocaleLabel}
              </p>
              <h3 className="mt-1 text-xl font-black text-navy">
                Add Timeline Item
              </h3>
              <p className="mt-1 text-sm font-semibold text-gray-500">
                This item will be saved to the database immediately.
              </p>
            </div>

            <div className="space-y-4">
              <Field
                label="Year"
                value={timelineDraft.year}
                onChange={(value) =>
                  setTimelineDraft((current) => ({ ...current, year: value }))
                }
              />
              <Field
                label="Title"
                value={timelineDraft.title}
                onChange={(value) =>
                  setTimelineDraft((current) => ({ ...current, title: value }))
                }
              />
              <Field
                label="Description"
                value={timelineDraft.desc}
                multiline
                onChange={(value) =>
                  setTimelineDraft((current) => ({ ...current, desc: value }))
                }
              />
            </div>

            <div className="mt-6 flex justify-end gap-3">
              <button
                type="button"
                onClick={() => setTimelineDraft(null)}
                className="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-navy transition hover:border-gray-300"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={handleAddTimelineItem}
                disabled={saving}
                className="rounded-xl bg-primary px-4 py-2 text-xs font-bold text-white shadow-md shadow-primary/20 transition hover:bg-primary-hover disabled:opacity-60"
              >
                {saving ? "Saving..." : "Add Item"}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
