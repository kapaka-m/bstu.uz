import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useAppData } from "../../../context/AppDataContext";
import { applicationService } from "../../../services/applicationService";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import {
  Loader2,
  ClipboardCheck,
  AlertCircle,
  ArrowRight,
  Save,
  Send,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import StatusBadge from "../../apanel/components/StatusBadge";

export default function StudentApplication() {
  const { t } = useLanguage();
  const navigate = useNavigate();
  const {
    faculties,
    departments,
    programs,
    loading: dataLoading,
  } = useAppData();

  const [activeApp, setActiveApp] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [profile, setProfile] = useState(null);

  // Filters state
  const [selectedDegree, setSelectedDegree] = useState("");
  const [selectedFaculty, setSelectedFaculty] = useState("");
  const [selectedDepartment, setSelectedDepartment] = useState("");
  const [selectedLanguage, setSelectedLanguage] = useState("");
  const [selectedMode, setSelectedMode] = useState("");
  const [selectedProgramId, setSelectedProgramId] = useState("");
  const normalizeOption = (value) =>
    String(value || "")
      .trim()
      .toLowerCase()
      .replaceAll("_", "-");

  const loadApplication = React.useCallback(async () => {
    try {
      setLoading(true);
      setError("");

      const [apps, prof] = await Promise.all([
        applicationService.getApplications().catch(() => []),
        studentService.getProfile().catch(() => null),
      ]);

      setProfile(prof);
      const app =
        apps.find((a) => a.status !== "graduated" && a.status !== "rejected") ||
        apps[0];

      if (app) {
        setActiveApp(app);
        setSelectedProgramId(app.program_id || "");
        setSelectedDegree(app.degree_level || "");
        setSelectedFaculty(app.faculty_id || "");
        setSelectedDepartment(app.department_id || "");
        setSelectedLanguage(app.language_of_study || "");
        setSelectedMode(app.study_mode || "");

        // Auto-select filters from active program
        const prog = programs.find((p) => p.id === app.program_id);
        if (prog && !app.degree_level) {
          setSelectedDegree(normalizeOption(prog.degree));
          setSelectedFaculty(prog.faculty_id || "");
          setSelectedDepartment(prog.department_id || "");
          setSelectedLanguage(normalizeOption(prog.language_of_study));
          setSelectedMode(normalizeOption(prog.study_mode));
        }
      }
    } catch {
      setError("Failed to load application data.");
    } finally {
      setLoading(false);
    }
  }, [programs]);

  useEffect(() => {
    if (!dataLoading) {
      loadApplication();
    }
  }, [dataLoading, loadApplication]);

  // Derived filter lists
  const filteredDepartments = departments.filter(
    (d) => !selectedFaculty || d.faculty_id === Number(selectedFaculty),
  );

  const filteredPrograms = programs.filter((p) => {
    if (selectedDegree && normalizeOption(p.degree) !== selectedDegree)
      return false;
    if (selectedFaculty && p.faculty_id !== Number(selectedFaculty))
      return false;
    if (selectedDepartment && p.department_id !== Number(selectedDepartment))
      return false;
    if (
      selectedLanguage &&
      normalizeOption(p.language_of_study) !== selectedLanguage
    )
      return false;
    if (selectedMode && normalizeOption(p.study_mode) !== selectedMode)
      return false;
    return true;
  });

  const selectedProgramDetails = programs.find(
    (p) => p.id === Number(selectedProgramId),
  );

  const handleSaveDraft = async () => {
    if (!selectedProgramId) {
      setError("Please select a program first.");
      return;
    }

    if (!profile) {
      setError(
        "Please complete your profile details first before saving an application.",
      );
      return;
    }

    try {
      setSaving(true);
      setError("");
      setSuccess("");
      const payload = {
        program_id: Number(selectedProgramId),
        faculty_id: selectedFaculty
          ? Number(selectedFaculty)
          : selectedProgramDetails?.faculty_id || null,
        department_id: selectedDepartment
          ? Number(selectedDepartment)
          : selectedProgramDetails?.department_id || null,
        degree_level:
          selectedDegree ||
          normalizeOption(selectedProgramDetails?.degree) ||
          null,
        language_of_study:
          selectedLanguage ||
          normalizeOption(selectedProgramDetails?.language_of_study) ||
          null,
        study_mode:
          selectedMode ||
          normalizeOption(selectedProgramDetails?.study_mode) ||
          null,
      };

      if (activeApp) {
        // Update existing application
        const updated = await applicationService.updateApplication(
          activeApp.id,
          payload,
        );
        setActiveApp(updated);
        setSuccess("Application draft updated successfully.");
      } else {
        // Create new application
        const created = await applicationService.createApplication(payload);
        setActiveApp(created);
        setSuccess("Application draft created successfully.");
      }
    } catch (err) {
      setError(err?.message || "Failed to save application draft.");
    } finally {
      setSaving(false);
    }
  };

  const handleSubmitApplication = async () => {
    if (!activeApp) return;

    // Check required documents
    const requiredTypes = [
      "passport",
      "photo",
      "education_certificate",
      "transcript",
    ];
    const uploadedTypes =
      activeApp.documents?.map((d) => d.document_type || d.document_name) || [];
    const missing = requiredTypes.filter(
      (type) => !uploadedTypes.includes(type),
    );

    if (missing.length > 0) {
      setError(
        `Cannot submit: Missing required documents (${missing.join(", ")}). Go to Upload Documents first.`,
      );
      return;
    }

    try {
      setSubmitting(true);
      setError("");
      setSuccess("");
      await applicationService.submitApplication(activeApp.id);
      setSuccess("Your application was submitted successfully!");
      navigate("/student/application/status");
    } catch (err) {
      setError(err?.message || "Failed to submit application.");
    } finally {
      setSubmitting(false);
    }
  };

  if (dataLoading || loading) {
    return <LoadingState message="Loading academic programs..." />;
  }

  // If application is already submitted (not in draft status)
  const isReadOnly = activeApp && activeApp.status !== "draft";

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            {t("student.application.title", "Start/Edit Application")}
          </h1>
          <p className="text-xs font-semibold text-gray-400">
            Select your academic options and complete your admission request
          </p>
        </div>
        {activeApp && (
          <div className="flex items-center gap-2">
            <span className="text-xs font-bold text-gray-400">
              Current Status:
            </span>
            <StatusBadge status={activeApp.status} />
          </div>
        )}
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-600 text-xs font-bold">
          {success}
        </div>
      )}

      {isReadOnly && (
        <div className="bg-blue-50 border border-blue-100 rounded-2xl p-6 flex items-start gap-4">
          <AlertCircle className="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
          <div className="space-y-1.5">
            <p className="text-xs font-extrabold text-navy">
              Application Submitted for Review
            </p>
            <p className="text-[11px] text-gray-500 font-semibold leading-relaxed">
              Your application for{" "}
              <strong>{activeApp?.program?.translations?.[0]?.name}</strong> is
              currently being reviewed. You cannot modify your academic
              selections now.
            </p>
            <Link
              to="/student/application/status"
              className="inline-flex items-center gap-1 text-xs font-extrabold text-primary hover:underline pt-1.5"
            >
              <span>View Status Timeline</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>
      )}

      {/* Selector Options Box */}
      <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-6">
        <div className="flex items-center gap-2 pb-3 border-b border-gray-50 text-navy font-extrabold uppercase text-xs tracking-wider">
          <ClipboardCheck className="w-4 h-4 text-primary" />
          <span>Academic Selections</span>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Degree Level */}
          <div className="space-y-1">
            <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
              Degree Level
            </label>
            <select
              value={selectedDegree}
              onChange={(e) => {
                setSelectedDegree(e.target.value);
                setSelectedProgramId("");
              }}
              disabled={isReadOnly}
              className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white disabled:bg-gray-50"
            >
              <option value="">All Degrees</option>
              <option value="bachelor">Bachelor</option>
              <option value="master">Master</option>
              <option value="phd">PhD</option>
            </select>
          </div>

          {/* Study Language */}
          <div className="space-y-1">
            <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
              Language of Study
            </label>
            <select
              value={selectedLanguage}
              onChange={(e) => {
                setSelectedLanguage(e.target.value);
                setSelectedProgramId("");
              }}
              disabled={isReadOnly}
              className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white disabled:bg-gray-50"
            >
              <option value="">All Languages</option>
              <option value="english">English</option>
              <option value="uzbek">Uzbek</option>
              <option value="russian">Russian</option>
              <option value="arabic">Arabic</option>
            </select>
          </div>

          {/* Faculty */}
          <div className="space-y-1">
            <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
              Faculty
            </label>
            <select
              value={selectedFaculty}
              onChange={(e) => {
                setSelectedFaculty(e.target.value);
                setSelectedDepartment("");
                setSelectedProgramId("");
              }}
              disabled={isReadOnly}
              className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white disabled:bg-gray-50"
            >
              <option value="">Select Faculty</option>
              {faculties.map((f) => (
                <option key={f.id} value={f.id}>
                  {f.translations?.[0]?.name || f.slug}
                </option>
              ))}
            </select>
          </div>

          {/* Department */}
          <div className="space-y-1">
            <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
              Department
            </label>
            <select
              value={selectedDepartment}
              onChange={(e) => {
                setSelectedDepartment(e.target.value);
                setSelectedProgramId("");
              }}
              disabled={isReadOnly || !selectedFaculty}
              className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white disabled:bg-gray-50 disabled:text-gray-400"
            >
              <option value="">Select Department</option>
              {filteredDepartments.map((d) => (
                <option key={d.id} value={d.id}>
                  {d.translations?.[0]?.name || d.slug}
                </option>
              ))}
            </select>
          </div>

          {/* Study Mode */}
          <div className="space-y-1">
            <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
              Study Mode
            </label>
            <select
              value={selectedMode}
              onChange={(e) => {
                setSelectedMode(e.target.value);
                setSelectedProgramId("");
              }}
              disabled={isReadOnly}
              className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white disabled:bg-gray-50"
            >
              <option value="">All Modes</option>
              <option value="full-time">Full-time</option>
              <option value="part-time">Part-time</option>
              <option value="distance">Distance</option>
            </select>
          </div>

          {/* Target Program Selection */}
          <div className="space-y-1">
            <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
              Academic Program *
            </label>
            <select
              value={selectedProgramId}
              onChange={(e) => setSelectedProgramId(e.target.value)}
              disabled={isReadOnly}
              className="w-full px-4 py-2.5 rounded-xl border border-primary/40 focus:outline-none focus:border-primary text-xs font-bold text-navy bg-white disabled:bg-gray-50"
            >
              <option value="">Choose Program</option>
              {filteredPrograms.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.translations?.[0]?.name} ($
                  {Number(p.tuition_fee).toLocaleString()}/year)
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Selected Details summary */}
        {selectedProgramDetails && (
          <div className="p-5 rounded-2xl border border-primary/10 bg-primary/5 space-y-3">
            <h4 className="text-xs font-black text-navy uppercase tracking-wider">
              Selected Program Specifications
            </h4>
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
              <div>
                <p className="text-[10px] text-gray-400 font-bold">
                  Tuition Fee
                </p>
                <p className="font-extrabold text-navy">
                  ${Number(selectedProgramDetails.tuition_fee).toLocaleString()}{" "}
                  / year
                </p>
              </div>
              <div>
                <p className="text-[10px] text-gray-400 font-bold">Duration</p>
                <p className="font-extrabold text-navy">
                  {selectedProgramDetails.duration_years} Years
                </p>
              </div>
              <div>
                <p className="text-[10px] text-gray-400 font-bold">
                  Study Language
                </p>
                <p className="font-extrabold text-navy uppercase">
                  {selectedProgramDetails.language_of_study}
                </p>
              </div>
              <div>
                <p className="text-[10px] text-gray-400 font-bold">
                  Study Mode
                </p>
                <p className="font-extrabold text-navy capitalize">
                  {selectedProgramDetails.study_mode}
                </p>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Form Buttons */}
      {!isReadOnly && (
        <div className="flex justify-end gap-3">
          <button
            onClick={handleSaveDraft}
            disabled={saving}
            className="px-5 py-3 bg-white hover:bg-gray-50 border border-gray-250 text-navy text-xs font-bold rounded-xl cursor-pointer transition-all flex items-center gap-1.5"
          >
            {saving ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <Save className="w-4 h-4" />
            )}
            <span>Save Draft</span>
          </button>

          {activeApp && (
            <button
              onClick={handleSubmitApplication}
              disabled={submitting}
              className="px-6 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-extrabold rounded-xl shadow-md hover:shadow-lg cursor-pointer transition-all flex items-center gap-1.5"
            >
              {submitting ? (
                <Loader2 className="w-4 h-4 animate-spin" />
              ) : (
                <Send className="w-4 h-4" />
              )}
              <span>Submit Application</span>
            </button>
          )}
        </div>
      )}
    </div>
  );
}

function LoadingState({ message }) {
  return (
    <div className="flex flex-col items-center justify-center min-h-75 space-y-3">
      <Loader2 className="w-8 h-8 text-primary animate-spin" />
      <span className="text-xs font-bold text-navy select-none">{message}</span>
    </div>
  );
}
