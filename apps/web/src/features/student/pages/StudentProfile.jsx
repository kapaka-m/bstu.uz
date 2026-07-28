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
      setSuccess("Profile details saved successfully!");
    } catch (err) {
      setError(err?.message || "Failed to save profile. Check form fields.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <LoadingState message="Loading profile details..." />;
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            {t("student.profile.title")}
          </h1>
          <p className="text-xs font-semibold text-gray-400">
            Complete all verification details before program application
            submission
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
            <span>1. Personal Information</span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Phone Number *
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
                Gender *
              </label>
              <select
                name="gender"
                value={form.gender}
                onChange={handleChange}
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              >
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Birth Date *
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
                Passport / National ID Number *
              </label>
              <input
                type="text"
                name="passport_number"
                value={form.passport_number}
                onChange={handleChange}
                placeholder="AA1234567"
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Passport Expiry Date
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
                Nationality *
              </label>
              <input
                type="text"
                name="nationality"
                value={form.nationality}
                onChange={handleChange}
                placeholder="e.g. Uzbek, Russian, Afghan"
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1 md:col-span-2">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Home Permanent Address *
              </label>
              <textarea
                name="address"
                value={form.address}
                onChange={handleChange}
                placeholder="Street address, City, Country"
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
            <span>2. Guardian Details (Optional)</span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Guardian's Full Name
              </label>
              <input
                type="text"
                name="guardian_name"
                value={form.guardian_name}
                onChange={handleChange}
                placeholder="Guardian full name"
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Relation to Student
              </label>
              <input
                type="text"
                name="guardian_relation"
                value={form.guardian_relation}
                onChange={handleChange}
                placeholder="e.g. Father, Mother, Uncle"
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Guardian Phone
              </label>
              <input
                type="text"
                name="guardian_phone"
                value={form.guardian_phone}
                onChange={handleChange}
                placeholder="Guardian phone number"
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Guardian Email
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
            <span>3. Academic Background (Optional)</span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1 md:col-span-2">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Institution / High School Name
              </label>
              <input
                type="text"
                name="education_institution_name"
                value={form.education_institution_name}
                onChange={handleChange}
                placeholder="School name"
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Degree Obtained / Diploma Title
              </label>
              <input
                type="text"
                name="education_degree_obtained"
                value={form.education_degree_obtained}
                onChange={handleChange}
                placeholder="e.g. High School Diploma, Bachelor of Science"
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                GPA / Score Average
              </label>
              <input
                type="text"
                name="education_gpa"
                value={form.education_gpa}
                onChange={handleChange}
                placeholder="e.g. 3.8/4.0 or 92%"
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Graduation Year
              </label>
              <input
                type="number"
                name="education_graduation_year"
                value={form.education_graduation_year}
                onChange={handleChange}
                placeholder="Year"
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
                <span>Save Profile Info</span>
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
