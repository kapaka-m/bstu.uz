import React, { useState, useEffect } from "react";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import { Loader2, Save, User, FileText, Compass } from "lucide-react";
import FormError from "../../../components/common/FormError";

export default function StudentProfile() {
  const { t } = useLanguage();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const [form, setForm] = useState({
    phone: "",
    gender: "male",
    birth_date: "",
    passport_number: "",
    passport_expiry_date: "",
    nationality: "",
    address: "",

    guardian_name: "",
    guardian_relation: "",
    guardian_phone: "",
    guardian_email: "",

    education_institution_name: "",
    education_degree_obtained: "",
    education_gpa: "",
    education_graduation_year: new Date().getFullYear(),
  });

  useEffect(() => {
    const fetchProfile = async () => {
      try {
        setLoading(true);
        const data = await studentService.getProfile();
        if (data) {
          setForm({
            phone: data.phone || "",
            gender: data.gender || "male",
            birth_date: data.birth_date
              ? String(data.birth_date).slice(0, 10)
              : "",
            passport_number: data.passport_number || "",
            passport_expiry_date: data.passport_expiry_date
              ? String(data.passport_expiry_date).slice(0, 10)
              : "",
            nationality: data.nationality || "",
            address: data.address || "",

            guardian_name: data.guardians?.[0]?.name || "",
            guardian_relation: data.guardians?.[0]?.relation || "",
            guardian_phone: data.guardians?.[0]?.phone || "",
            guardian_email: data.guardians?.[0]?.email || "",

            education_institution_name:
              data.education_backgrounds?.[0]?.institution_name || "",
            education_degree_obtained:
              data.education_backgrounds?.[0]?.degree_obtained || "",
            education_gpa: data.education_backgrounds?.[0]?.gpa || "",
            education_graduation_year:
              data.education_backgrounds?.[0]?.graduation_year ||
              new Date().getFullYear(),
          });
        }
      } catch (err) {
        console.error("Failed to load profile", err);
      } finally {
        setLoading(false);
      }
    };
    fetchProfile();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      setSaving(true);
      setError("");
      setSuccess("");
      await studentService.updateProfile(form);
      setSuccess(t("student.profile.saved"));
    } catch (err) {
      setError(err?.message || t("student.profile.saveFailed"));
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <LoadingState message={t("student.profile.loading")} />;
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            {t("student.profile.title")}
          </h1>
          <p className="text-xs font-semibold text-gray-400">
            {t("student.profile.subtitle")}
          </p>
        </div>
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-600 text-xs font-bold">
          {success}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* SECTION 1: Personal Info */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 pb-3 border-b border-gray-50 text-navy font-extrabold uppercase text-xs tracking-wider">
            <User className="w-4 h-4 text-primary" />
            <span>{t("student.profile.section.personal")}</span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.phone")} *
              </label>
              <input
                type="text"
                name="phone"
                value={form.phone}
                onChange={handleChange}
                placeholder="+998 90 123-45-67"
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.gender")} *
              </label>
              <select
                name="gender"
                value={form.gender}
                onChange={handleChange}
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              >
                <option value="male">{t("gender.male")}</option>
                <option value="female">{t("gender.female")}</option>
              </select>
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.birth_date")} *
              </label>
              <input
                type="date"
                name="birth_date"
                value={form.birth_date}
                onChange={handleChange}
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.passport_number")} *
              </label>
              <input
                type="text"
                name="passport_number"
                value={form.passport_number}
                onChange={handleChange}
                placeholder={t("student.profile.passportPlaceholder")}
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.passport_expiry_date")}
              </label>
              <input
                type="date"
                name="passport_expiry_date"
                value={form.passport_expiry_date}
                onChange={handleChange}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.nationality")} *
              </label>
              <input
                type="text"
                name="nationality"
                value={form.nationality}
                onChange={handleChange}
                placeholder={t("student.profile.nationalityPlaceholder")}
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1 md:col-span-2">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.address")} *
              </label>
              <textarea
                name="address"
                value={form.address}
                onChange={handleChange}
                placeholder={t("student.profile.addressPlaceholder")}
                required
                rows={3}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>
          </div>
        </div>

        {/* SECTION 2: Guardian Info */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 pb-3 border-b border-gray-50 text-navy font-extrabold uppercase text-xs tracking-wider">
            <Compass className="w-4 h-4 text-primary" />
            <span>{t("student.profile.section.guardian")}</span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.guardian_name")}
              </label>
              <input
                type="text"
                name="guardian_name"
                value={form.guardian_name}
                onChange={handleChange}
                placeholder={t("student.profile.guardianNamePlaceholder")}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.guardian_relation")}
              </label>
              <input
                type="text"
                name="guardian_relation"
                value={form.guardian_relation}
                onChange={handleChange}
                placeholder={t("student.profile.guardianRelationPlaceholder")}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.guardian_phone")}
              </label>
              <input
                type="text"
                name="guardian_phone"
                value={form.guardian_phone}
                onChange={handleChange}
                placeholder={t("student.profile.guardianPhonePlaceholder")}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.guardian_email")}
              </label>
              <input
                type="email"
                name="guardian_email"
                value={form.guardian_email}
                onChange={handleChange}
                placeholder="guardian@example.com"
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>
          </div>
        </div>

        {/* SECTION 3: Education Background */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
          <div className="flex items-center gap-2 pb-3 border-b border-gray-50 text-navy font-extrabold uppercase text-xs tracking-wider">
            <FileText className="w-4 h-4 text-primary" />
            <span>{t("student.profile.section.education")}</span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1 md:col-span-2">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.education_institution_name")}
              </label>
              <input
                type="text"
                name="education_institution_name"
                value={form.education_institution_name}
                onChange={handleChange}
                placeholder={t("student.profile.schoolNamePlaceholder")}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.education_degree_obtained")}
              </label>
              <input
                type="text"
                name="education_degree_obtained"
                value={form.education_degree_obtained}
                onChange={handleChange}
                placeholder={t("student.profile.degreePlaceholder")}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.education_gpa")}
              </label>
              <input
                type="text"
                name="education_gpa"
                value={form.education_gpa}
                onChange={handleChange}
                placeholder={t("student.profile.gpaPlaceholder")}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("student.profile.education_graduation_year")}
              </label>
              <input
                type="number"
                name="education_graduation_year"
                value={form.education_graduation_year}
                onChange={handleChange}
                placeholder={t("time.year")}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>
          </div>
        </div>

        {/* Submit Actions */}
        <div className="flex justify-end gap-3">
          <button
            type="submit"
            disabled={saving}
            className="px-6 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-extrabold rounded-xl shadow-md hover:shadow-lg cursor-pointer transition-all flex items-center gap-1.5"
          >
            {saving ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <>
                <Save className="w-4 h-4" />
                <span>{t("button.saveProfile")}</span>
              </>
            )}
          </button>
        </div>
      </form>
    </div>
  );
}

// Reusable LoadingState component fallback if not defined
function LoadingState({ message }) {
  return (
    <div className="flex flex-col items-center justify-center min-h-75 space-y-3">
      <Loader2 className="w-8 h-8 text-primary animate-spin" />
      <span className="text-xs font-bold text-navy select-none">{message}</span>
    </div>
  );
}
