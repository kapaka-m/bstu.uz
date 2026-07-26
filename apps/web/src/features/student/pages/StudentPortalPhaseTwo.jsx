import React, { useEffect, useMemo, useState } from "react";
import { Link, useLocation } from "react-router-dom";
import {
  Bell,
  CheckCircle,
  ClipboardList,
  CreditCard,
  Download,
  FileCheck,
  GraduationCap,
  Loader2,
  ShieldCheck,
  Upload,
  User,
} from "lucide-react";
import LoadingState from "../../../components/common/LoadingState";
import FormError from "../../../components/common/FormError";
import { studentPortalService } from "../../../services/studentPortalService";
import { studentService } from "../../../services/studentService";

const statusClass = (status = "") => {
  const value = String(status).toUpperCase();
  if (["APPROVED", "COMPLETED", "ISSUED", "ADMISSION_ISSUED"].includes(value)) return "bg-emerald-50 text-emerald-700 border-emerald-100";
  if (["REJECTED", "REUPLOAD_REQUIRED", "APPLICATION_REJECTED"].includes(value)) return "bg-rose-50 text-rose-700 border-rose-100";
  if (["ACTION REQUIRED", "DOCUMENTS_REQUIRED", "APPLICATION_FEE_REQUIRED"].includes(value)) return "bg-amber-50 text-amber-700 border-amber-100";
  return "bg-blue-50 text-blue-700 border-blue-100";
};

const labelize = (value) => String(value || "Not Started").replaceAll("_", " ");
const uploadAccept = ".pdf,.jpg,.jpeg,.png,.webp,.heic,.heif";
const maxUploadSize = 10 * 1024 * 1024;

const apiErrorMessage = (err, fallback) => {
  const fieldMessages = err?.errors
    ? Object.entries(err.errors).flatMap(([field, messages]) =>
        (Array.isArray(messages) ? messages : [messages]).map((message) => `${field}: ${message}`),
      )
    : [];

  return fieldMessages.length ? fieldMessages.join(" ") : err?.message || fallback;
};

function StatusPill({ status }) {
  return <span className={`inline-flex px-2.5 py-1 rounded-full border text-[10px] font-black uppercase ${statusClass(status)}`}>{labelize(status)}</span>;
}

function Panel({ title, icon: Icon = ClipboardList, children, action }) {
  return (
    <section className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-5">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-50">
        <div className="flex items-center gap-2 text-navy">
          <Icon className="w-4 h-4 text-primary" />
          <h2 className="text-sm font-extrabold uppercase tracking-wider">{title}</h2>
        </div>
        {action}
      </div>
      {children}
    </section>
  );
}

function InfoGrid({ rows }) {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
      {rows.map(([label, value]) => (
        <div key={label} className="rounded-2xl border border-gray-100 bg-gray-50/60 p-4">
          <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">{label}</p>
          <p className="mt-1 text-xs font-extrabold text-navy wrap-break-word">{value || "—"}</p>
        </div>
      ))}
    </div>
  );
}

export default function StudentPortalPhaseTwo() {
  const location = useLocation();
  const [summary, setSummary] = useState(null);
  const [profile, setProfile] = useState(null);
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [busy, setBusy] = useState("");

  const mode = useMemo(() => {
    const path = location.pathname;
    if (path.includes("/profile")) return "profile";
    if (path.includes("/academic-information")) return "academic";
    if (path.includes("/documents")) return "documents";
    if (path.includes("/equivalency")) return "equivalency";
    if (path.includes("/payments")) return "payments";
    if (path.includes("/admission")) return "admission";
    if (path.includes("/enrollment")) return "enrollment";
    if (path.includes("/notifications")) return "notifications";
    if (path.includes("/application")) return "application";
    return "dashboard";
  }, [location.pathname]);

  const load = async () => {
    try {
      setLoading(true);
      setError("");
      const [summaryData, profileData, notificationData] = await Promise.all([
        studentPortalService.summary(),
        studentPortalService.profile().catch(() => null),
        studentService.getNotifications().catch(() => []),
      ]);
      setSummary(summaryData);
      setProfile(profileData);
      setNotifications(notificationData || []);
    } catch (err) {
      setError(err?.message || "Unable to load student portal data.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, []);

  if (loading) return <LoadingState message="Loading student application..." />;
  if (error) return <FormError message={error} />;
  if (!summary?.application) {
    return <Panel title="No Application"><p className="text-xs font-bold text-gray-500">No active application was found for this account.</p></Panel>;
  }

  const app = summary.application;
  const student = app.student_profile || app.studentProfile || profile || {};
  const user = student.user || {};
  const programName = app.program?.translations?.[0]?.name || app.program?.slug;
  const facultyName = app.faculty?.translations?.[0]?.name || app.faculty?.slug;
  const applicationId = app.id;
  const isTransfer = String(app.student_type).toLowerCase() === "transfer";

  const uploadDoc = async (requirement, file) => {
    if (!file) return;
    if (file.size > maxUploadSize) {
      setError("File exceeds maximum allowed size (10 MB).");
      return;
    }

    try {
      setBusy(requirement.document_type);
      setError("");
      await studentPortalService.uploadDocument(applicationId, requirement.document_type, file);
      setSuccess("Document uploaded successfully.");
      await load();
    } catch (err) {
      setError(apiErrorMessage(err, "Upload failed."));
    } finally {
      setBusy("");
    }
  };

  const uploadReceipt = async (file) => {
    if (!file) return;
    if (file.size > maxUploadSize) {
      setError("File exceeds maximum allowed size (10 MB).");
      return;
    }

    try {
      setBusy("payment");
      await studentPortalService.uploadPaymentReceipt(applicationId, file);
      setSuccess("Payment receipt uploaded for review.");
      await load();
    } catch (err) {
      setError(apiErrorMessage(err, "Receipt upload failed."));
    } finally {
      setBusy("");
    }
  };

  const uploadContractReceipt = async (file) => {
    if (!file) return;
    if (file.size > maxUploadSize) {
      setError("File exceeds maximum allowed size (10 MB).");
      return;
    }

    try {
      setBusy("contract_advance");
      await studentPortalService.uploadContractAdvanceReceipt(applicationId, file);
      setSuccess("30% contract payment receipt uploaded for review.");
      await load();
    } catch (err) {
      setError(apiErrorMessage(err, "Contract payment receipt upload failed."));
    } finally {
      setBusy("");
    }
  };

  const acceptEquivalency = async () => {
    setBusy("equivalency");
    await studentPortalService.acceptEquivalency(applicationId);
    setSuccess("Equivalency result accepted.");
    setBusy("");
    await load();
  };

  const requestEquivalencyReview = async () => {
    const reason = window.prompt("Please write the reason for requesting review:");
    if (!reason) return;
    setBusy("equivalency");
    await studentPortalService.requestEquivalencyReview(applicationId, reason);
    setSuccess("Equivalency review request submitted.");
    setBusy("");
    await load();
  };

  const renderDashboard = () => (
    <div className="space-y-6">
      <div className="bg-linear-to-r from-navy to-navy-dark rounded-3xl p-8 text-white shadow-xl">
        <p className="text-xs font-bold text-white/60 uppercase tracking-widest">Student Application</p>
        <h1 className="mt-2 text-2xl md:text-3xl font-extrabold">{student.full_name_english || user.name}</h1>
        <p className="mt-2 text-xs font-semibold text-white/70">{app.application_number} · {programName}</p>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">
        <Metric label="Completion" value={`${summary.completion_percentage}%`} icon={CheckCircle} />
        <Metric label="Current Status" value={labelize(app.status)} icon={ClipboardList} />
        <Metric label="Documents" value={labelize(app.documents_status)} icon={FileCheck} />
        <Metric label="Admission" value={labelize(app.admission_status)} icon={ShieldCheck} />
        <Metric label="Enrollment" value={summary.checks?.enrollment_issued ? "Issued" : "Pending"} icon={ShieldCheck} />
      </div>
      <Panel title="Next Step" icon={ClipboardList}>
        <p className="text-sm font-bold text-navy">{summary.next_action}</p>
      </Panel>
      {renderTimeline()}
      {renderQuickLinks(isTransfer)}
    </div>
  );

  const renderTimeline = () => (
    <Panel title="Application Timeline" icon={ClipboardList}>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
        {(summary.timeline || []).map((item) => (
          <div key={item.key} className="flex items-center justify-between gap-3 rounded-2xl border border-gray-100 p-4">
            <span className="text-xs font-extrabold text-navy">{item.label}</span>
            <StatusPill status={item.status} />
          </div>
        ))}
      </div>
    </Panel>
  );

  const renderQuickLinks = (showEquivalency) => (
    <Panel title="Application Areas" icon={GraduationCap}>
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        {[
          ["/student/application", "Application Overview", ClipboardList],
          ["/student/profile", "Personal Information", User],
          ["/student/academic-information", "Academic Information", GraduationCap],
          ["/student/documents", "Required Documents", FileCheck],
          ...(showEquivalency ? [["/student/equivalency", "Academic Equivalency", GraduationCap]] : []),
          ["/student/payments", "Payments", CreditCard],
          ["/student/admission", "Admission", ShieldCheck],
          ["/student/enrollment", "Enrollment", ShieldCheck],
          ["/student/notifications", "Notifications", Bell],
        ].map(([to, label, Icon]) => (
          <Link key={to} to={to} className="rounded-2xl border border-gray-100 p-4 hover:border-primary/30 hover:bg-primary/5 transition-all">
            <Icon className="w-5 h-5 text-primary" />
            <p className="mt-3 text-xs font-extrabold text-navy">{label}</p>
          </Link>
        ))}
      </div>
    </Panel>
  );

  const renderApplication = () => (
    <div className="space-y-6">
      <Panel title="Application Overview" icon={ClipboardList}>
        <InfoGrid rows={[
          ["Application Number", app.application_number],
          ["Status", labelize(app.status)],
          ["Created At", app.created_at ? new Date(app.created_at).toLocaleString() : ""],
          ["Updated At", app.updated_at ? new Date(app.updated_at).toLocaleString() : ""],
          ["Program", programName],
          ["Next Action", summary.next_action],
        ]} />
      </Panel>
      {renderTimeline()}
      <Panel title="Status History" icon={ClipboardList}>
        <div className="space-y-3">
          {(app.status_histories || app.statusHistories || []).map((item) => (
            <div key={item.id} className="rounded-2xl border border-gray-100 p-4">
              <StatusPill status={item.new_status || item.status} />
              <p className="mt-2 text-xs font-bold text-gray-500">{item.comment || item.note}</p>
            </div>
          ))}
        </div>
      </Panel>
    </div>
  );

  const renderProfile = () => (
    <Panel title="Personal Information" icon={User}>
      <InfoGrid rows={[
        ["Full Name", student.full_name_english || user.name],
        ["Date of Birth", student.birth_date],
        ["Birth Country", student.country_of_birth],
        ["Birth Place", student.place_of_birth],
        ["Nationality", student.nationality],
        ["Gender", student.gender],
        ["Passport Number", student.passport_number],
        ["Passport Type", student.passport_type],
        ["Passport Issue Date", student.passport_issue_date],
        ["Passport Expiry Date", student.passport_expiry_date],
        ["Issuing Country", student.passport_issuing_country],
        ["Place of Issue", student.passport_place_of_issue],
        ["Primary Phone", student.phone],
        ["Alternative Phone", student.alternative_phone],
        ["Messenger", student.preferred_messenger],
        ["Telegram", student.telegram_username],
        ["Email", user.email],
      ]} />
      <p className="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-2xl p-4">
        Sensitive fields are locked while your application is under review. Contact administration if a correction is required.
      </p>
    </Panel>
  );

  const renderAcademic = () => (
    <Panel title="Academic Information" icon={GraduationCap}>
      <InfoGrid rows={[
        ["Degree Level", app.degree_level],
        ["Student Type", app.student_type === "transfer" ? "Transfer Student" : "New Student"],
        ["Education Type", app.study_mode],
        ["Faculty", facultyName],
        ["Program", programName],
        ["Study Language", app.language_of_study],
        ["Intended Intake", app.intended_intake],
        ["Estimated Duration", app.program?.duration_years ? `${app.program.duration_years} years` : "Pending review"],
      ]} />
      {isTransfer && <p className="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-2xl p-4">Your study year and final study duration will be determined after transcript review and academic equivalency.</p>}
    </Panel>
  );

  const renderDocuments = () => (
    <Panel title="Required Documents" icon={FileCheck}>
      {success && <Success message={success} />}
      {error && <FormError message={error} />}
      <div className="space-y-3">
        {(summary.requirements || []).map((req) => (
          <div key={req.document_type} className="rounded-2xl border border-gray-100 p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
              <div className="flex items-center gap-2">
                <p className="text-xs font-extrabold text-navy">{req.name}</p>
                <StatusPill status={req.status} />
              </div>
              <p className="mt-1 text-[11px] font-semibold text-gray-500">{req.description}</p>
              {req.document?.rejection_reason && <p className="mt-2 text-[11px] font-bold text-rose-600">{req.document.rejection_reason}</p>}
              {req.document && <p className="mt-2 text-[10px] font-bold text-gray-400">Version {req.document.current_version} · {req.document.original_name}</p>}
            </div>
            <div className="flex items-center gap-2">
              {req.document && (
                <button
                  onClick={() => studentPortalService.downloadDocument(req.document.id)}
                  className="text-xs font-extrabold text-primary"
                >
                  <Download className="inline w-4 h-4" /> Download
                </button>
              )}
              {req.status !== "APPROVED" && (
                <label className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold cursor-pointer">
                  {busy === req.document_type ? <Loader2 className="w-4 h-4 animate-spin" /> : <><Upload className="inline w-4 h-4 me-1" />Upload</>}
                  <input
                    className="hidden"
                    type="file"
                    accept={uploadAccept}
                    onChange={(e) => {
                      uploadDoc(req, e.target.files?.[0]);
                      e.target.value = "";
                    }}
                  />
                </label>
              )}
            </div>
          </div>
        ))}
      </div>
    </Panel>
  );

  const renderEquivalency = () => {
    if (!isTransfer) return <Panel title="Academic Equivalency"><p className="text-xs font-bold text-gray-500">Academic equivalency is not required for new students.</p></Panel>;
    const eq = app.equivalency;
    return (
      <Panel title="Academic Equivalency" icon={GraduationCap}>
        <InfoGrid rows={[
          ["Status", eq?.status || "WAITING_DOCUMENTS"],
          ["Previous University", eq?.previous_university],
          ["Previous Country", eq?.previous_country],
          ["Previous Program", eq?.previous_program],
          ["Accepted Credits", eq?.accepted_credits],
          ["Rejected Credits", eq?.rejected_credits],
          ["Entry Year", eq?.proposed_entry_year],
          ["Remaining Duration", eq?.estimated_remaining_duration],
        ]} />
        <div className="space-y-2">
          {(eq?.courses || []).map((course) => (
            <div key={course.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between gap-3">
              <div>
                <p className="text-xs font-extrabold text-navy">{course.previous_course_name}</p>
                <p className="text-[11px] text-gray-500">{course.matched_university_course || "No matched course"}</p>
              </div>
              <StatusPill status={course.course_status} />
            </div>
          ))}
        </div>
        {["RESULT_ISSUED", "STUDENT_REVIEW_REQUIRED"].includes(eq?.status) && (
          <div className="flex gap-3">
            <button onClick={acceptEquivalency} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">Accept Equivalency Result</button>
            <button onClick={requestEquivalencyReview} className="px-4 py-2 rounded-xl border border-gray-200 text-navy text-xs font-extrabold">Request Review</button>
          </div>
        )}
      </Panel>
    );
  };

  const renderPayments = () => (
    <div className="space-y-6">
      <Panel title="Application Fee Payment" icon={CreditCard}>
        <InfoGrid rows={[
          ["Fee", "50 USD"],
          ["Status", labelize(app.application_fee_status)],
          ["Can Upload Receipt", summary.checks?.documents_approved && summary.checks?.equivalency_complete ? "Yes" : "Not yet"],
        ]} />
        <div className="space-y-3">
          {(app.application_fee_payments || []).map((payment) => (
            <div key={payment.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
              <div>
                <p className="text-xs font-extrabold text-navy">{payment.payment_number}</p>
                <p className="text-[11px] text-gray-500">{payment.receipt_original_name}</p>
                {payment.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{payment.rejection_reason}</p>}
              </div>
              <StatusPill status={payment.status} />
            </div>
          ))}
        </div>
        {summary.checks?.documents_approved && summary.checks?.equivalency_complete && !summary.checks?.payment_approved && (
          <label className="inline-flex px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold cursor-pointer">
            {busy === "payment" ? <Loader2 className="w-4 h-4 animate-spin" /> : "Upload 50 USD Receipt"}
            <input
              className="hidden"
              type="file"
              accept={uploadAccept}
              onChange={(e) => {
                uploadReceipt(e.target.files?.[0]);
                e.target.value = "";
              }}
            />
          </label>
        )}
      </Panel>

      <Panel title="30% Contract Payment" icon={CreditCard}>
        {(app.contracts || []).length > 0 ? (app.contracts || []).map((contract) => (
          <div key={contract.id} className="space-y-4">
            <InfoGrid rows={[
              ["Contract Number", contract.contract_number],
              ["Total Amount", Number(contract.amount) > 0 ? `${contract.amount} ${contract.currency || "USD"}` : "To be calculated"],
              ["Required Advance", Number(contract.advance_amount) > 0 ? `${contract.advance_amount} ${contract.currency || "USD"}` : "30% of contract"],
              ["Status", summary.checks?.contract_advance_paid ? "Approved" : "Waiting payment"],
            ]} />
            <div className="space-y-3">
              {(contract.payments || []).map((payment) => (
                <div key={payment.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
                  <div>
                    <p className="text-xs font-extrabold text-navy">{payment.payment_number}</p>
                    <p className="text-[11px] text-gray-500">{payment.receipt_original_name}</p>
                    {payment.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{payment.rejection_reason}</p>}
                  </div>
                  <StatusPill status={payment.status} />
                </div>
              ))}
            </div>
            {!summary.checks?.contract_advance_paid && (
              <label className="inline-flex px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold cursor-pointer">
                {busy === "contract_advance" ? <Loader2 className="w-4 h-4 animate-spin" /> : "Upload 30% Contract Receipt"}
                <input
                  className="hidden"
                  type="file"
                  accept={uploadAccept}
                  onChange={(e) => {
                    uploadContractReceipt(e.target.files?.[0]);
                    e.target.value = "";
                  }}
                />
              </label>
            )}
          </div>
        )) : (
          <p className="text-xs font-bold text-gray-500">The 30% contract payment opens after admission is issued.</p>
        )}
      </Panel>
    </div>
  );

  const renderAdmission = () => (
    <Panel title="Admission" icon={ShieldCheck}>
      <p className="text-xs font-bold text-gray-500">Application Number is not an Admission Number.</p>
      {app.admission ? (
        <div className="space-y-4">
          <InfoGrid rows={[
            ["Student Name", student.full_name_english || user.name],
            ["Application Number", app.application_number],
            ["Admission Number", app.admission.admission_number],
            ["Issue Date", app.admission.issue_date],
            ["Degree", app.degree_level],
            ["Faculty", facultyName],
            ["Program", programName],
            ["Status", app.admission.status],
          ]} />
          <button
            type="button"
            onClick={() => studentPortalService.downloadAdmission()}
            className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-extrabold text-white hover:bg-primary-hover"
          >
            <Download className="h-4 w-4" />
            Download Admission PDF
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
          {Object.entries(summary.checks || {}).map(([key, value]) => (
            <div key={key} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
              <span className="text-xs font-extrabold text-navy">{labelize(key)}</span>
              <StatusPill status={value ? "Completed" : "Not Started"} />
            </div>
          ))}
        </div>
      )}
    </Panel>
  );

  const renderEnrollment = () => (
    <Panel title="Enrollment Certificate" icon={ShieldCheck}>
      {app.enrollment ? (
        <div className="space-y-4">
          <InfoGrid rows={[
            ["Student Number", app.enrollment.student_number],
            ["Academic Year", app.enrollment.academic_year],
            ["Issue Date", app.enrollment.issue_date],
            ["Status", app.enrollment.status],
          ]} />
          <button
            type="button"
            onClick={() => studentPortalService.downloadEnrollment()}
            className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-extrabold text-white hover:bg-primary-hover"
          >
            <Download className="h-4 w-4" />
            Download Enrollment PDF
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
          {[
            ["Admission issued", summary.checks?.admission_issued],
            ["30% contract payment approved", summary.checks?.contract_advance_paid],
            ["Enrollment certificate issued", summary.checks?.enrollment_issued],
          ].map(([label, value]) => (
            <div key={label} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
              <span className="text-xs font-extrabold text-navy">{label}</span>
              <StatusPill status={value ? "Completed" : "Not Started"} />
            </div>
          ))}
        </div>
      )}
    </Panel>
  );

  const renderNotifications = () => (
    <Panel title="Notifications" icon={Bell}>
      <div className="space-y-3">
        {notifications.map((item) => (
          <div key={item.id} className="rounded-2xl border border-gray-100 p-4">
            <p className="text-xs font-extrabold text-navy">{item.title}</p>
            <p className="mt-1 text-[11px] font-semibold text-gray-500">{item.message}</p>
          </div>
        ))}
      </div>
    </Panel>
  );

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {success && mode !== "documents" && <Success message={success} />}
      {mode === "dashboard" && renderDashboard()}
      {mode === "application" && renderApplication()}
      {mode === "profile" && renderProfile()}
      {mode === "academic" && renderAcademic()}
      {mode === "documents" && renderDocuments()}
      {mode === "equivalency" && renderEquivalency()}
      {mode === "payments" && renderPayments()}
      {mode === "admission" && renderAdmission()}
      {mode === "enrollment" && renderEnrollment()}
      {mode === "notifications" && renderNotifications()}
    </div>
  );
}

function Metric({ label, value, icon: Icon }) {
  return (
    <div className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs">
      <Icon className="w-5 h-5 text-primary" />
      <p className="mt-4 text-[10px] font-black uppercase tracking-wider text-gray-400">{label}</p>
      <p className="mt-1 text-lg font-extrabold text-navy">{value}</p>
    </div>
  );
}

function Success({ message }) {
  return <div className="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-xs font-bold text-emerald-700">{message}</div>;
}
