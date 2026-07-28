import React, { useEffect, useMemo, useRef, useState } from "react";
import { Link } from "react-router-dom";
import { AnimatePresence, motion } from "framer-motion";
import {
  AlertCircle,
  Calendar,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Eye,
  EyeOff,
  GraduationCap,
  Loader2,
  Lock,
  Mail,
  Phone,
  Shield,
  User,
} from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { authStorage } from "../lib/auth";
import { initialApplicationService } from "../services/initialApplicationService";

const steps = ["personal", "academic", "review"];

const initialForm = {
  full_name_english: "",
  birth_date: "",
  country_of_birth: "",
  place_of_birth: "",
  nationality: "",
  gender: "",
  passport_number: "",
  passport_type: "",
  passport_issue_date: "",
  passport_expiry_date: "",
  passport_issuing_country: "",
  passport_place_of_issue: "",
  primary_phone: "",
  preferred_messenger: "",
  telegram_username: "",
  alternative_phone: "",
  degree_level: "",
  student_type: "new",
  education_type: "",
  faculty_id: "",
  program_id: "",
  study_language: "",
  intended_intake: "",
  email: "",
  password: "",
  password_confirmation: "",
  terms_agreement: false,
  information_confirmation: false,
};

const cleanPhone = (value) => value.replace(/[^\d+]/g, "");
const optionKey = (group, value) =>
  `initialApplication.options.${group}.${String(value || "").replace(/[^a-zA-Z0-9]+/g, "_")}`;

function Field({ id, label, error, children, hint }) {
  return (
    <div className="space-y-1.5">
      <label htmlFor={id} className="text-[11px] font-extrabold text-navy uppercase tracking-wider">
        {label}
      </label>
      {children}
      {hint && <p className="text-[11px] font-semibold text-gray-400">{hint}</p>}
      {error && <p className="text-xs font-bold text-red-500">{error}</p>}
    </div>
  );
}

function Input({ id, label, error, hint, icon: Icon, ...props }) {
  return (
    <Field id={id} label={label} error={error} hint={hint}>
      <div className="relative">
        {Icon && <Icon className="absolute left-3 top-3.5 w-4 h-4 text-gray-400" />}
        <input
          id={id}
          aria-invalid={Boolean(error)}
          className={`w-full ${Icon ? "pl-10" : "pl-4"} pr-4 py-3 rounded-xl border text-sm font-semibold outline-none focus:ring-2 bg-white ${error ? "border-red-300 focus:ring-red-100" : "border-gray-200 focus:border-primary focus:ring-primary/15"}`}
          {...props}
        />
      </div>
    </Field>
  );
}

function Select({ id, label, error, options, ...props }) {
  return (
    <Field id={id} label={label} error={error}>
      <select
        id={id}
        aria-invalid={Boolean(error)}
        className={`w-full px-4 py-3 rounded-xl border text-sm font-semibold outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 ${error ? "border-red-300 focus:ring-red-100" : "border-gray-200 bg-white focus:border-primary focus:ring-primary/15"}`}
        {...props}
      >
        <option value="">--</option>
        {options.map((option) => (
          <option key={option.value} value={option.value}>{option.label}</option>
        ))}
      </select>
    </Field>
  );
}

function ReviewBox({ title, action, children }) {
  return (
    <section className="rounded-2xl border border-gray-100 bg-gray-50/70 p-5">
      <div className="mb-4 flex items-center justify-between gap-3">
        <h3 className="text-sm font-extrabold text-navy">{title}</h3>
        <button type="button" onClick={action.onClick} className="text-xs font-extrabold text-primary hover:underline">
          {action.label}
        </button>
      </div>
      <div className="divide-y divide-gray-100">{children}</div>
    </section>
  );
}

function Row({ label, value }) {
  return (
    <div className="flex justify-between gap-4 py-2 text-xs">
      <span className="font-bold text-gray-400">{label}</span>
      <span className="text-right font-extrabold text-navy">{value || "--"}</span>
    </div>
  );
}

export default function ApplyPage() {
  const { t: translate, language, isRtl } = useLanguage();
  const t = useMemo(
    () =>
      new Proxy(
        {},
        {
          get: (_, key) => translate(`initialApplication.${String(key)}`),
        },
      ),
    [translate],
  );

  const firstErrorRef = useRef(null);
  const [step, setStep] = useState(0);
  const [direction, setDirection] = useState(1);
  const [form, setForm] = useState(initialForm);
  const [metadata, setMetadata] = useState(null);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [success, setSuccess] = useState(null);

  useEffect(() => {
    let active = true;
    initialApplicationService.getMetadata()
      .then((data) => {
        if (!active) return;
        setMetadata(data);
        setForm((current) => ({
          ...current,
          passport_type: data.passport_types?.includes("ordinary") ? "ordinary" : data.passport_types?.[0] || "",
          degree_level: "",
          education_type: "",
          study_language: "",
          intended_intake: data.intakes?.[0] || "",
        }));
      })
      .finally(() => active && setLoading(false));
    return () => { active = false; };
  }, [language]);

  const programs = useMemo(() => metadata?.programs || [], [metadata]);
  const filteredFaculties = useMemo(() => {
    if (!form.degree_level) return [];

    const map = new Map();
    programs
      .filter((program) => program.degree === form.degree_level)
      .forEach((program) => {
        if (program.faculty?.id) map.set(program.faculty.id, program.faculty);
      });
    return [...map.values()];
  }, [programs, form.degree_level]);

  const availablePrograms = useMemo(() => {
    if (!form.degree_level || !form.faculty_id) return [];

    return programs.filter((program) =>
      program.degree === form.degree_level
      && String(program.faculty?.id) === String(form.faculty_id)
    );
  }, [programs, form.degree_level, form.faculty_id]);

  const selectedProgram = programs.find((program) => String(program.id) === String(form.program_id));
  const selectedProgramEducationTypes = selectedProgram?.available_education_types || [];
  const selectedProgramLanguages = selectedProgram?.available_study_languages || [];
  const optionLabel = (group, value) => translate(optionKey(group, value));
  const passwordScore = [
    form.password.length >= 8,
    /[A-Za-z]/.test(form.password),
    /\d/.test(form.password),
    /[^A-Za-z0-9]/.test(form.password),
  ].filter(Boolean).length;

  const setValue = (field, value) => {
    let next = value;
    if (field === "full_name_english") next = value.toUpperCase().replace(/[^A-Z\s'-]/g, "");
    if (field === "place_of_birth") next = value.replace(/[^A-Za-z\s'-]/g, "");
    if (field === "passport_number") next = value.toUpperCase().replace(/\s+/g, "").replace(/[^A-Z0-9-]/g, "");
    if (field === "email") next = value.trim().toLowerCase();
    if (field.includes("phone")) next = cleanPhone(value);

    setForm((current) => {
      const updated = { ...current, [field]: next };
      if (field === "degree_level") {
        updated.faculty_id = "";
        updated.program_id = "";
        updated.education_type = "";
        updated.study_language = "";
      }
      if (field === "faculty_id") {
        updated.program_id = "";
        updated.education_type = "";
        updated.study_language = "";
      }
      if (field === "program_id") {
        updated.education_type = "";
        updated.study_language = "";
      }
      return updated;
    });
    setErrors((current) => ({ ...current, [field]: "" }));
  };

  const validate = (targetStep = step) => {
    const e = {};
    const required = (field) => { if (!String(form[field] || "").trim()) e[field] = t.required; };

    if (targetStep === 0) {
      ["full_name_english", "birth_date", "country_of_birth", "place_of_birth", "nationality", "gender", "passport_number", "passport_type", "passport_issue_date", "passport_expiry_date", "passport_issuing_country", "passport_place_of_issue", "primary_phone", "preferred_messenger"].forEach(required);
      if (form.full_name_english && !/^[A-Z][A-Z\s'-]*$/.test(form.full_name_english)) e.full_name_english = t.invalidName;
      if (form.place_of_birth && !/^[A-Za-z][A-Za-z\s'-]*$/.test(form.place_of_birth)) e.place_of_birth = t.invalidName;
      if (form.birth_date && form.birth_date > new Date().toISOString().slice(0, 10)) e.birth_date = t.required;
      if (form.passport_expiry_date && form.passport_expiry_date <= new Date().toISOString().slice(0, 10)) e.passport_expiry_date = t.expiredPassport;
      if (form.passport_issue_date && form.passport_expiry_date && form.passport_expiry_date <= form.passport_issue_date) e.passport_expiry_date = t.expiryAfterIssue;
      if (form.primary_phone && !/^\+[1-9]\d{7,14}$/.test(form.primary_phone)) e.primary_phone = t.invalidPhone;
      if (form.alternative_phone && !/^\+[1-9]\d{7,14}$/.test(form.alternative_phone)) e.alternative_phone = t.invalidPhone;
      if (form.alternative_phone && form.alternative_phone === form.primary_phone) e.alternative_phone = t.duplicatePhone;
    }

    if (targetStep === 1) {
      ["degree_level", "student_type", "education_type", "faculty_id", "program_id", "study_language", "intended_intake"].forEach(required);
      if (form.program_id && !availablePrograms.some((program) => String(program.id) === String(form.program_id))) e.program_id = t.unavailableProgram;
      if (form.education_type && !selectedProgramEducationTypes.includes(form.education_type)) e.education_type = t.unavailableProgram;
      if (form.study_language && !selectedProgramLanguages.includes(form.study_language)) e.study_language = t.unavailableProgram;
    }

    if (targetStep === 2) {
      ["email", "password", "password_confirmation"].forEach(required);
      if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) e.email = t.invalidEmail;
      if (form.password && !/^(?=.*[A-Za-z])(?=.*\d).{8,}$/.test(form.password)) e.password = t.passwordWeak;
      if (form.password !== form.password_confirmation) e.password_confirmation = t.passwordMatch;
      if (!form.terms_agreement) e.terms_agreement = t.required;
      if (!form.information_confirmation) e.information_confirmation = t.required;
    }

    setErrors(e);
    return e;
  };

  const focusFirstError = () => {
    setTimeout(() => {
      const first = document.querySelector("[aria-invalid='true']");
      if (first) first.focus();
      firstErrorRef.current?.scrollIntoView({ behavior: "smooth", block: "center" });
    }, 0);
  };

  const next = () => {
    const e = validate(step);
    if (Object.keys(e).length) return focusFirstError();
    setDirection(1);
    setStep((current) => current + 1);
    window.scrollTo(0, 0);
  };

  const back = () => {
    setDirection(-1);
    setStep((current) => Math.max(0, current - 1));
    window.scrollTo(0, 0);
  };

  const submit = async () => {
    const e = validate(2);
    if (Object.keys(e).length) return focusFirstError();
    setSubmitting(true);
    setErrors({});
    try {
      const data = await initialApplicationService.submit(form);
      authStorage.setToken(data.access_token);
      authStorage.setUser(data.user);
      setSuccess(data);
      window.scrollTo(0, 0);
    } catch (err) {
      setErrors(err?.errors || { form: err?.message || t.apiFailed });
      focusFirstError();
    } finally {
      setSubmitting(false);
    }
  };

  const goDashboard = () => {
    window.location.assign("/student/dashboard");
  };

  if (loading) {
    return <div className="min-h-screen pt-24 flex items-center justify-center"><Loader2 className="w-8 h-8 animate-spin text-primary" /></div>;
  }

  if (success) {
    return (
      <div className="min-h-screen pt-24 bg-primary-light/40 px-4 py-10">
        <div className="mx-auto max-w-2xl rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-sm">
          <CheckCircle2 className="mx-auto mb-5 h-14 w-14 text-emerald-500" />
          <h1 className="mb-2 text-2xl font-extrabold text-navy">{t.successTitle}</h1>
          <p className="mx-auto mb-6 max-w-xl text-sm font-semibold leading-relaxed text-gray-500">{t.successText}</p>
          <div className="mb-6 rounded-2xl border border-primary/10 bg-primary/5 p-5">
            <p className="text-xs font-bold text-gray-400">{t.fullName}</p>
            <p className="mb-3 text-lg font-extrabold text-navy">{form.full_name_english}</p>
            <p className="text-xs font-bold text-gray-400">{t.applicationNumber}</p>
            <p className="text-xl font-black tracking-widest text-primary">{success.application_number}</p>
            <p className="mt-3 text-xs font-bold text-gray-400">{form.email}</p>
          </div>
          <button onClick={goDashboard} className="rounded-xl bg-primary px-6 py-3 text-sm font-extrabold text-white shadow-md shadow-primary/20 hover:bg-primary-hover">
            {t.dashboard}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-primary-light/35 pt-20" dir={isRtl ? "rtl" : "ltr"}>
      <header className="bg-navy px-4 py-12 text-center text-white">
        <div className="mx-auto max-w-4xl">
          <GraduationCap className="mx-auto mb-3 h-10 w-10 text-primary" />
          <h1 className="text-2xl font-extrabold md:text-4xl">{t.title}</h1>
          <p className="mt-3 text-sm font-semibold text-white/70">{t.subtitle}</p>
        </div>
      </header>

      <main className="mx-auto max-w-5xl px-4 py-10">
        <div className="mb-8 grid grid-cols-3 gap-2">
          {steps.map((item, index) => (
            <button key={item} type="button" onClick={() => index < step && setStep(index)} className={`rounded-2xl border p-3 text-center text-xs font-extrabold ${index <= step ? "border-primary/20 bg-white text-primary" : "border-gray-100 bg-white/70 text-gray-400"}`}>
              <span className={`mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-full ${index < step ? "bg-emerald-500 text-white" : index === step ? "bg-primary text-white" : "bg-gray-100 text-gray-400"}`}>
                {index < step ? <CheckCircle2 className="h-4 w-4" /> : index + 1}
              </span>
              {t[item]}
            </button>
          ))}
        </div>

        {errors.form && (
          <div ref={firstErrorRef} className="mb-5 flex gap-2 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm font-bold text-red-600">
            <AlertCircle className="h-5 w-5 shrink-0" /> {errors.form}
          </div>
        )}

        <AnimatePresence mode="wait" custom={direction}>
          <motion.section
            key={step}
            custom={direction}
            initial={{ opacity: 0, x: direction > 0 ? 40 : -40 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: direction > 0 ? -40 : 40 }}
            transition={{ duration: 0.2 }}
            className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm md:p-8"
          >
            {step === 0 && (
              <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div className="md:col-span-2 rounded-2xl bg-primary/5 p-4 text-sm font-semibold text-primary">{t.passportHint}</div>
                <Input id="full_name_english" label={t.fullName} value={form.full_name_english} onChange={(e) => setValue("full_name_english", e.target.value)} error={errors.full_name_english} icon={User} />
                <Input id="birth_date" label={t.birthDate} type="date" max={new Date().toISOString().slice(0, 10)} value={form.birth_date} onChange={(e) => setValue("birth_date", e.target.value)} error={errors.birth_date} icon={Calendar} />
                <Select id="country_of_birth" label={t.countryBirth} value={form.country_of_birth} onChange={(e) => setValue("country_of_birth", e.target.value)} error={errors.country_of_birth} options={(metadata?.countries || []).map((x) => ({ value: x, label: x }))} />
                <Input id="place_of_birth" label={t.placeBirth} value={form.place_of_birth} onChange={(e) => setValue("place_of_birth", e.target.value)} error={errors.place_of_birth} />
                <Select id="nationality" label={t.nationality} value={form.nationality} onChange={(e) => setValue("nationality", e.target.value)} error={errors.nationality} options={(metadata?.nationalities || []).map((x) => ({ value: x, label: x }))} />
                <Select id="gender" label={t.gender} value={form.gender} onChange={(e) => setValue("gender", e.target.value)} error={errors.gender} options={(metadata?.genders || []).map((x) => ({ value: x, label: optionLabel("gender", x) }))} />
                <Input id="passport_number" label={t.passportNumber} value={form.passport_number} onChange={(e) => setValue("passport_number", e.target.value)} error={errors.passport_number} />
                <Select id="passport_type" label={t.passportType} value={form.passport_type} onChange={(e) => setValue("passport_type", e.target.value)} error={errors.passport_type} options={(metadata?.passport_types || []).map((x) => ({ value: x, label: optionLabel("passport_type", x) }))} />
                <Input id="passport_issue_date" label={t.issueDate} type="date" max={new Date().toISOString().slice(0, 10)} value={form.passport_issue_date} onChange={(e) => setValue("passport_issue_date", e.target.value)} error={errors.passport_issue_date} />
                <Input id="passport_expiry_date" label={t.expiryDate} type="date" value={form.passport_expiry_date} onChange={(e) => setValue("passport_expiry_date", e.target.value)} error={errors.passport_expiry_date} />
                <Select id="passport_issuing_country" label={t.issuingCountry} value={form.passport_issuing_country} onChange={(e) => setValue("passport_issuing_country", e.target.value)} error={errors.passport_issuing_country} options={(metadata?.countries || []).map((x) => ({ value: x, label: x }))} />
                <Input id="passport_place_of_issue" label={t.placeIssue} value={form.passport_place_of_issue} onChange={(e) => setValue("passport_place_of_issue", e.target.value)} error={errors.passport_place_of_issue} />
                <Input id="primary_phone" label={t.primaryPhone} value={form.primary_phone} onChange={(e) => setValue("primary_phone", e.target.value)} error={errors.primary_phone} icon={Phone} placeholder="+998901234567" />
                <Select id="preferred_messenger" label={t.messenger} value={form.preferred_messenger} onChange={(e) => setValue("preferred_messenger", e.target.value)} error={errors.preferred_messenger} options={(metadata?.messengers || []).map((x) => ({ value: x, label: optionLabel("messenger", x) }))} />
                {(form.preferred_messenger === "telegram" || form.preferred_messenger === "both") && <Input id="telegram_username" label={t.telegram} value={form.telegram_username} onChange={(e) => setValue("telegram_username", e.target.value)} error={errors.telegram_username} />}
                <Input id="alternative_phone" label={t.alternativePhone} value={form.alternative_phone} onChange={(e) => setValue("alternative_phone", e.target.value)} error={errors.alternative_phone} icon={Phone} placeholder="+998901234568" />
              </div>
            )}

            {step === 1 && (
              <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Select id="degree_level" label={t.degree} value={form.degree_level} onChange={(e) => setValue("degree_level", e.target.value)} error={errors.degree_level} options={(metadata?.degrees || []).map((x) => ({ value: x, label: optionLabel("degree_level", x) }))} />
                <Select id="student_type" label={t.studentType} value={form.student_type} onChange={(e) => setValue("student_type", e.target.value)} error={errors.student_type} options={(metadata?.student_types || []).map((x) => ({ value: x, label: optionLabel("student_type", x) }))} />
                <Select id="faculty_id" label={t.faculty} value={form.faculty_id} onChange={(e) => setValue("faculty_id", e.target.value)} error={errors.faculty_id} disabled={!form.degree_level} options={filteredFaculties.map((x) => ({ value: x.id, label: x.name }))} />
                <Select id="program_id" label={t.program} value={form.program_id} onChange={(e) => setValue("program_id", e.target.value)} error={errors.program_id} disabled={!form.faculty_id} options={availablePrograms.map((x) => ({ value: x.id, label: `${x.code} - ${x.name}` }))} />
                <Select
                  id="education_type"
                  label={t.educationType}
                  value={form.education_type}
                  onChange={(e) => setValue("education_type", e.target.value)}
                  error={errors.education_type}
                  disabled={!selectedProgram}
                  options={selectedProgramEducationTypes.map((x) => ({ value: x, label: optionLabel("education_type", x) }))}
                />
                <Select
                  id="study_language"
                  label={t.language}
                  value={form.study_language}
                  onChange={(e) => setValue("study_language", e.target.value)}
                  error={errors.study_language}
                  disabled={!selectedProgram}
                  options={selectedProgramLanguages.map((x) => ({ value: x, label: optionLabel("study_language", x) }))}
                />
                <Select id="intended_intake" label={t.intake} value={form.intended_intake} onChange={(e) => setValue("intended_intake", e.target.value)} error={errors.intended_intake} options={(metadata?.intakes || []).map((x) => ({ value: x, label: x }))} />
                <div className="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                  <p className="text-[11px] font-extrabold uppercase tracking-wider text-gray-400">{t.duration}</p>
                  <p className="mt-1 text-sm font-extrabold text-navy">{selectedProgram?.duration_years ? `${selectedProgram.duration_years} ${t.yearsLabel}` : "--"}</p>
                </div>
                {form.student_type === "transfer" && <div className="md:col-span-2 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm font-bold text-amber-700">{t.transferNote}</div>}
              </div>
            )}

            {step === 2 && (
              <div className="space-y-6">
                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                  <Input id="email" label={t.email} type="email" value={form.email} onChange={(e) => setValue("email", e.target.value)} error={errors.email} icon={Mail} />
                  <Field id="password" label={t.password} error={errors.password}>
                    <div className="relative">
                      <Lock className="absolute left-3 top-3.5 h-4 w-4 text-gray-400" />
                      <input id="password" type={showPassword ? "text" : "password"} aria-invalid={Boolean(errors.password)} value={form.password} onChange={(e) => setValue("password", e.target.value)} className={`w-full rounded-xl border bg-white py-3 pl-10 pr-11 text-sm font-semibold outline-none focus:ring-2 ${errors.password ? "border-red-300 focus:ring-red-100" : "border-gray-200 focus:border-primary focus:ring-primary/15"}`} />
                      <button type="button" onClick={() => setShowPassword((x) => !x)} className="absolute right-3 top-3 text-gray-400">{showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}</button>
                    </div>
                    <div className="mt-2 flex gap-1">{[1, 2, 3, 4].map((x) => <span key={x} className={`h-1.5 flex-1 rounded-full ${passwordScore >= x ? "bg-primary" : "bg-gray-100"}`} />)}</div>
                  </Field>
                  <Input id="password_confirmation" label={t.confirmPassword} type={showPassword ? "text" : "password"} value={form.password_confirmation} onChange={(e) => setValue("password_confirmation", e.target.value)} error={errors.password_confirmation} icon={Lock} />
                </div>

                <ReviewBox title={t.personalInfo} action={{ label: t.editPersonal, onClick: () => setStep(0) }}>
                  <Row label={t.fullName} value={form.full_name_english} />
                  <Row label={t.birthDate} value={form.birth_date} />
                  <Row label={t.nationality} value={form.nationality} />
                  <Row label={t.passportNumber} value={form.passport_number} />
                  <Row label={t.primaryPhone} value={form.primary_phone} />
                  <Row label={t.messenger} value={optionLabel("messenger", form.preferred_messenger)} />
                </ReviewBox>
                <ReviewBox title={t.academicInfo} action={{ label: t.editAcademic, onClick: () => setStep(1) }}>
                  <Row label={t.degree} value={optionLabel("degree_level", form.degree_level)} />
                  <Row label={t.studentType} value={optionLabel("student_type", form.student_type)} />
                  <Row label={t.educationType} value={optionLabel("education_type", form.education_type)} />
                  <Row label={t.faculty} value={selectedProgram?.faculty?.name} />
                  <Row label={t.program} value={selectedProgram?.name} />
                  <Row label={t.language} value={optionLabel("study_language", form.study_language)} />
                  <Row label={t.intake} value={form.intended_intake} />
                </ReviewBox>
                <ReviewBox title={t.accountInfo} action={{ label: t.editLogin, onClick: () => document.getElementById("email")?.focus() }}>
                  <Row label={t.email} value={form.email} />
                </ReviewBox>

                <label className="flex gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4 text-sm font-bold text-gray-600">
                  <input type="checkbox" checked={form.terms_agreement} onChange={(e) => setValue("terms_agreement", e.target.checked)} className="mt-1 accent-primary" />
                  <span>{t.terms}{errors.terms_agreement && <em className="ml-2 not-italic text-red-500">{errors.terms_agreement}</em>}</span>
                </label>
                <label className="flex gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4 text-sm font-bold text-gray-600">
                  <input type="checkbox" checked={form.information_confirmation} onChange={(e) => setValue("information_confirmation", e.target.checked)} className="mt-1 accent-primary" />
                  <span>{t.confirm}{errors.information_confirmation && <em className="ml-2 not-italic text-red-500">{errors.information_confirmation}</em>}</span>
                </label>
              </div>
            )}
          </motion.section>
        </AnimatePresence>

        <div className="mt-8 flex items-center justify-between gap-4">
          {step > 0 ? (
            <button type="button" onClick={back} className="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-extrabold text-navy hover:border-primary hover:text-primary">
              <ChevronLeft className="h-4 w-4" /> {t.back}
            </button>
          ) : <Link to="/programs" className="text-sm font-bold text-gray-400 hover:text-primary">{t.back}</Link>}

          {step < 2 ? (
            <button type="button" onClick={next} className="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-extrabold text-white shadow-md shadow-primary/20 hover:bg-primary-hover">
              {t.next} <ChevronRight className="h-4 w-4" />
            </button>
          ) : (
            <button type="button" disabled={submitting} onClick={submit} className="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-extrabold text-white shadow-md shadow-primary/20 hover:bg-primary-hover disabled:opacity-70">
              {submitting ? <Loader2 className="h-4 w-4 animate-spin" /> : <Shield className="h-4 w-4" />}
              {submitting ? t.submitting : t.submit}
            </button>
          )}
        </div>
      </main>
    </div>
  );
}
