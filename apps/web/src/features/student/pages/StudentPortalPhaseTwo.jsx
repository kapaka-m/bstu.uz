import React, { useCallback, useEffect, useMemo, useState } from "react";
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
import { useLanguage } from "../../../context/LanguageContext";

const statusClass = (status = "") => {
  const value = String(status).toUpperCase();
  if (["APPROVED", "COMPLETED", "ISSUED", "ADMISSION_ISSUED"].includes(value)) return "bg-emerald-50 text-emerald-700 border-emerald-100";
  if (["REJECTED", "REUPLOAD_REQUIRED", "APPLICATION_REJECTED"].includes(value)) return "bg-rose-50 text-rose-700 border-rose-100";
  if (["ACTION REQUIRED", "DOCUMENTS_REQUIRED", "APPLICATION_FEE_REQUIRED"].includes(value)) return "bg-amber-50 text-amber-700 border-amber-100";
  return "bg-blue-50 text-blue-700 border-blue-100";
};

const labelize = (value) => String(value || "").replaceAll("_", " ");
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
  const { t, locale } = useLanguage();
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
    if (path.includes("/service-fee")) return "serviceFee";
    if (path.includes("/admission")) return "admission";
    if (path.includes("/enrollment")) return "enrollment";
    if (path.includes("/prikaz")) return "prikaz";
    if (path.includes("/visa")) return "visa";
    if (path.includes("/housing")) return "housing";
    if (path.includes("/residence")) return "residence";
    if (path.includes("/notifications")) return "notifications";
    if (path.includes("/application")) return "application";
    return "dashboard";
  }, [location.pathname]);

  const load = useCallback(async () => {
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
      setError(err?.message || t("student.portal.loadError"));
    } finally {
      setLoading(false);
    }
  }, [t]);

  useEffect(() => {
    load();
  }, [load]);

  if (loading) return <LoadingState message={t("student.portal.loading")} />;
  if (error) return <FormError message={error} />;
  if (!summary?.application) {
    return <Panel title={t("application.noApplication")}><p className="text-xs font-bold text-gray-500">{t("application.noActiveForAccount")}</p></Panel>;
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
      setError(t("document.fileTooLarge"));
      return;
    }

    try {
      setBusy(requirement.document_type);
      setError("");
      await studentPortalService.uploadDocument(applicationId, requirement.document_type, file);
      setSuccess(t("document.uploaded"));
      await load();
    } catch (err) {
      setError(apiErrorMessage(err, t("document.uploadFailed")));
    } finally {
      setBusy("");
    }
  };

  const uploadReceipt = async (file) => {
    if (!file) return;
    if (file.size > maxUploadSize) {
      setError(t("document.fileTooLarge"));
      return;
    }

    try {
      setBusy("payment");
      await studentPortalService.uploadPaymentReceipt(applicationId, file);
      setSuccess(t("payment.receiptUploaded"));
      await load();
    } catch (err) {
      setError(apiErrorMessage(err, t("payment.receiptUploadFailed")));
    } finally {
      setBusy("");
    }
  };

  const uploadContractReceipt = async (file) => {
    if (!file) return;
    if (file.size > maxUploadSize) {
      setError(t("document.fileTooLarge"));
      return;
    }

    try {
      setBusy("contract_advance");
      await studentPortalService.uploadContractAdvanceReceipt(applicationId, file);
      setSuccess(t("contract.advanceReceiptUploaded"));
      await load();
    } catch (err) {
      setError(apiErrorMessage(err, t("contract.receiptUploadFailed")));
    } finally {
      setBusy("");
    }
  };

  const uploadServiceFeeReceipt = async (file) => {
    if (!file) return;
    if (file.size > maxUploadSize) {
      setError(t("document.fileTooLarge"));
      return;
    }

    try {
      setBusy("service_fee");
      await studentPortalService.uploadServiceFeeReceipt(applicationId, file);
      setSuccess(t("serviceFee.receiptUploaded"));
      await load();
    } catch (err) {
      setError(apiErrorMessage(err, t("serviceFee.receiptUploadFailed")));
    } finally {
      setBusy("");
    }
  };

  const submitHousingRequest = async () => {
    const preferredRoomType = window.prompt(t("housing.preferredRoomPrompt"));
    const notes = window.prompt(t("housing.notesPrompt"));
    try {
      setBusy("housing");
      await studentPortalService.submitHousingRequest(applicationId, {
        preferred_room_type: preferredRoomType || "",
        notes: notes || "",
      });
      setSuccess(t("housing.requestSubmitted"));
      await load();
    } catch (err) {
      setError(apiErrorMessage(err, t("housing.requestFailed")));
    } finally {
      setBusy("");
    }
  };

  const acceptEquivalency = async () => {
    setBusy("equivalency");
    await studentPortalService.acceptEquivalency(applicationId);
    setSuccess(t("equivalency.accepted"));
    setBusy("");
    await load();
  };

  const requestEquivalencyReview = async () => {
    const reason = window.prompt(t("equivalency.reviewReasonPrompt"));
    if (!reason) return;
    setBusy("equivalency");
    await studentPortalService.requestEquivalencyReview(applicationId, reason);
    setSuccess(t("equivalency.reviewRequested"));
    setBusy("");
    await load();
  };

  const renderDashboard = () => (
    <div className="space-y-6">
      <div className="bg-linear-to-r from-navy to-navy-dark rounded-3xl p-8 text-white shadow-xl">
        <p className="text-xs font-bold text-white/60 uppercase tracking-widest">{t("student.application.label")}</p>
        <h1 className="mt-2 text-2xl md:text-3xl font-extrabold">{student.full_name_english || user.name}</h1>
        <p className="mt-2 text-xs font-semibold text-white/70">{app.application_number} · {programName}</p>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">
        <Metric label={t("student.portal.completion")} value={`${summary.completion_percentage}%`} icon={CheckCircle} />
        <Metric label={t("application.currentStatus")} value={labelize(app.status)} icon={ClipboardList} />
        <Metric label={t("document.title")} value={labelize(app.documents_status)} icon={FileCheck} />
        <Metric label={t("student.nav.admission")} value={labelize(app.admission_status)} icon={ShieldCheck} />
        <Metric label={t("student.nav.enrollment")} value={summary.checks?.enrollment_issued ? t("status.issued") : t("status.pending")} icon={ShieldCheck} />
      </div>
      <Panel title={t("student.portal.nextStep")} icon={ClipboardList}>
        <p className="text-sm font-bold text-navy">{summary.next_action}</p>
      </Panel>
      {renderTimeline()}
      {renderQuickLinks(isTransfer)}
    </div>
  );

  const renderTimeline = () => (
    <Panel title={t("application.timeline")} icon={ClipboardList}>
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
    <Panel title={t("student.portal.applicationAreas")} icon={GraduationCap}>
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        {[
          ["/student/application", t("student.nav.applicationOverview"), ClipboardList],
          ["/student/profile", t("student.profile.section.personalShort"), User],
          ["/student/academic-information", t("student.nav.academicInformation"), GraduationCap],
          ["/student/documents", t("student.nav.requiredDocuments"), FileCheck],
          ...(showEquivalency ? [["/student/equivalency", t("student.nav.academicEquivalency"), GraduationCap]] : []),
          ["/student/payments", t("student.nav.payments"), CreditCard],
          ["/student/admission", t("student.nav.admission"), ShieldCheck],
          ["/student/enrollment", t("student.nav.enrollment"), ShieldCheck],
          ["/student/prikaz", t("student.nav.prikaz"), FileCheck],
          ["/student/service-fee", t("student.nav.serviceFee"), CreditCard],
          ["/student/visa", t("student.nav.visa"), ShieldCheck],
          ["/student/housing", t("student.nav.housing"), ClipboardList],
          ["/student/residence", t("student.nav.residence"), FileCheck],
          ["/student/notifications", t("student.notifications"), Bell],
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
      <Panel title={t("student.nav.applicationOverview")} icon={ClipboardList}>
        <InfoGrid rows={[
          [t("application.number"), app.application_number],
          [t("application.status"), labelize(app.status)],
          [t("common.createdAt"), app.created_at ? new Date(app.created_at).toLocaleString(locale) : ""],
          [t("common.updatedAt"), app.updated_at ? new Date(app.updated_at).toLocaleString(locale) : ""],
          [t("application.program"), programName],
          [t("student.portal.nextAction"), summary.next_action],
        ]} />
      </Panel>
      {renderTimeline()}
      <Panel title={t("application.statusHistory")} icon={ClipboardList}>
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
    <Panel title={t("student.profile.section.personalShort")} icon={User}>
      <InfoGrid rows={[
        [t("form.fullName"), student.full_name_english || user.name],
        [t("student.profile.birth_date"), student.birth_date],
        [t("student.profile.birthCountry"), student.country_of_birth],
        [t("student.profile.birthPlace"), student.place_of_birth],
        [t("student.profile.nationality"), student.nationality],
        [t("student.profile.gender"), student.gender],
        [t("student.profile.passport_number"), student.passport_number],
        [t("student.profile.passportType"), student.passport_type],
        [t("student.profile.passportIssueDate"), student.passport_issue_date],
        [t("student.profile.passport_expiry_date"), student.passport_expiry_date],
        [t("student.profile.issuingCountry"), student.passport_issuing_country],
        [t("student.profile.placeOfIssue"), student.passport_place_of_issue],
        [t("student.profile.primaryPhone"), student.phone],
        [t("student.profile.alternativePhone"), student.alternative_phone],
        [t("student.profile.messenger"), student.preferred_messenger],
        [t("student.profile.telegram"), student.telegram_username],
        [t("form.email"), user.email],
      ]} />
      <p className="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-2xl p-4">
        {t("student.profile.lockedNotice")}
      </p>
    </Panel>
  );

  const renderAcademic = () => (
    <Panel title={t("student.nav.academicInformation")} icon={GraduationCap}>
      <InfoGrid rows={[
        [t("application.degreeLevel"), app.degree_level],
        [t("student.type"), app.student_type === "transfer" ? t("student.type.transfer") : t("student.type.new")],
        [t("education.type"), app.study_mode],
        [t("faculty.title"), facultyName],
        [t("application.program"), programName],
        [t("application.languageOfStudy"), app.language_of_study],
        [t("application.intendedIntake"), app.intended_intake],
        [t("program.estimatedDuration"), app.program?.duration_years ? `${app.program.duration_years} ${t("time.years")}` : t("status.pendingReview")],
      ]} />
      {isTransfer && <p className="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-2xl p-4">{t("equivalency.transferNotice")}</p>}
    </Panel>
  );

  const renderDocuments = () => (
    <Panel title={t("student.nav.requiredDocuments")} icon={FileCheck}>
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
                  <Download className="inline w-4 h-4" /> {t("button.download")}
                </button>
              )}
              {req.status !== "APPROVED" && (
                <label className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold cursor-pointer">
                  {busy === req.document_type ? <Loader2 className="w-4 h-4 animate-spin" /> : <><Upload className="inline w-4 h-4 me-1" />{t("button.upload")}</>}
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
    if (!isTransfer) return <Panel title={t("student.nav.academicEquivalency")}><p className="text-xs font-bold text-gray-500">{t("equivalency.notRequired")}</p></Panel>;
    const eq = app.equivalency;
    return (
      <Panel title={t("student.nav.academicEquivalency")} icon={GraduationCap}>
        <InfoGrid rows={[
          [t("application.status"), eq?.status || t("status.waitingDocuments")],
          [t("equivalency.previousUniversity"), eq?.previous_university],
          [t("equivalency.previousCountry"), eq?.previous_country],
          [t("equivalency.previousProgram"), eq?.previous_program],
          [t("equivalency.acceptedCredits"), eq?.accepted_credits],
          [t("equivalency.rejectedCredits"), eq?.rejected_credits],
          [t("equivalency.entryYear"), eq?.proposed_entry_year],
          [t("equivalency.remainingDuration"), eq?.estimated_remaining_duration],
        ]} />
        <div className="space-y-2">
          {(eq?.courses || []).map((course) => (
            <div key={course.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between gap-3">
              <div>
                <p className="text-xs font-extrabold text-navy">{course.previous_course_name}</p>
                <p className="text-[11px] text-gray-500">{course.matched_university_course || t("equivalency.noMatchedCourse")}</p>
              </div>
              <StatusPill status={course.course_status} />
            </div>
          ))}
        </div>
        {["RESULT_ISSUED", "STUDENT_REVIEW_REQUIRED"].includes(eq?.status) && (
          <div className="flex gap-3">
            <button onClick={acceptEquivalency} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">{t("equivalency.acceptResult")}</button>
            <button onClick={requestEquivalencyReview} className="px-4 py-2 rounded-xl border border-gray-200 text-navy text-xs font-extrabold">{t("equivalency.requestReview")}</button>
          </div>
        )}
      </Panel>
    );
  };

  const renderPayments = () => (
    <div className="space-y-6">
      <Panel title={t("payment.applicationFee")} icon={CreditCard}>
        <InfoGrid rows={[
          [t("payment.fee"), t("payment.applicationFeeAmount")],
          [t("application.status"), labelize(app.application_fee_status)],
          [t("payment.canUploadReceipt"), summary.checks?.documents_approved && summary.checks?.equivalency_complete ? t("common.yes") : t("status.notYet")],
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
            {busy === "payment" ? <Loader2 className="w-4 h-4 animate-spin" /> : t("payment.uploadApplicationFeeReceipt")}
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

      <Panel title={t("contract.advancePayment")} icon={CreditCard}>
        {(app.contracts || []).length > 0 ? (app.contracts || []).map((contract) => (
          <div key={contract.id} className="space-y-4">
            <InfoGrid rows={[
              [t("contract.number"), contract.contract_number],
              [t("payment.totalAmount"), Number(contract.amount) > 0 ? `${contract.amount} ${contract.currency || ""}` : t("contract.toBeCalculated")],
              [t("contract.requiredAdvance"), Number(contract.advance_amount) > 0 ? `${contract.advance_amount} ${contract.currency || ""}` : t("contract.advancePercent")],
              [t("application.status"), summary.checks?.contract_advance_paid ? t("status.approved") : t("status.waitingPayment")],
            ]} />
            <button
              type="button"
              onClick={() => studentPortalService.downloadContract()}
              className="inline-flex items-center gap-2 rounded-xl bg-navy px-4 py-2 text-xs font-extrabold text-white"
            >
              <Download className="h-4 w-4" />
              {t("contract.downloadStudyPdf")}
            </button>
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
                {busy === "contract_advance" ? <Loader2 className="w-4 h-4 animate-spin" /> : t("contract.uploadAdvanceReceipt")}
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
          <p className="text-xs font-bold text-gray-500">{t("contract.advanceOpensAfterAdmission")}</p>
        )}
      </Panel>
    </div>
  );

  const renderAdmission = () => (
    <Panel title={t("student.nav.admission")} icon={ShieldCheck}>
      <p className="text-xs font-bold text-gray-500">{t("admission.applicationNumberNotice")}</p>
      {app.admission ? (
        <div className="space-y-4">
          <InfoGrid rows={[
            [t("student.name"), student.full_name_english || user.name],
            [t("application.number"), app.application_number],
            [t("admission.number"), app.admission.admission_number],
            [t("common.issueDate"), app.admission.issue_date],
            [t("application.degreeLevel"), app.degree_level],
            [t("faculty.title"), facultyName],
            [t("application.program"), programName],
            [t("application.status"), app.admission.status],
          ]} />
          <button
            type="button"
            onClick={() => studentPortalService.downloadAdmission()}
            className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-extrabold text-white hover:bg-primary-hover"
          >
            <Download className="h-4 w-4" />
            {t("admission.downloadPdf")}
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
          {Object.entries(summary.checks || {}).map(([key, value]) => (
            <div key={key} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
              <span className="text-xs font-extrabold text-navy">{labelize(key)}</span>
              <StatusPill status={value ? t("status.completed") : t("status.notStarted")} />
            </div>
          ))}
        </div>
      )}
    </Panel>
  );

  const renderEnrollment = () => (
    <Panel title={t("enrollment.certificate")} icon={ShieldCheck}>
      {app.enrollment ? (
        <div className="space-y-4">
          <InfoGrid rows={[
            [t("student.number"), app.enrollment.student_number],
            [t("common.academicYear"), app.enrollment.academic_year],
            [t("common.issueDate"), app.enrollment.issue_date],
            [t("application.status"), app.enrollment.status],
          ]} />
          <button
            type="button"
            onClick={() => studentPortalService.downloadEnrollment()}
            className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-extrabold text-white hover:bg-primary-hover"
          >
            <Download className="h-4 w-4" />
            {t("enrollment.downloadPdf")}
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
          {[
            [t("admission.issued"), summary.checks?.admission_issued],
            [t("contract.advanceApproved"), summary.checks?.contract_advance_paid],
            [t("enrollment.issued"), summary.checks?.enrollment_issued],
          ].map(([label, value]) => (
            <div key={label} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
              <span className="text-xs font-extrabold text-navy">{label}</span>
              <StatusPill status={value ? t("status.completed") : t("status.notStarted")} />
            </div>
          ))}
        </div>
      )}
    </Panel>
  );

  const renderPrikaz = () => (
    <Panel title={t("student.nav.prikaz")} icon={FileCheck}>
      {app.prikaz ? (
        <div className="space-y-4">
          <InfoGrid rows={[
            [t("prikaz.number"), app.prikaz.prikaz_number],
            [t("common.academicYear"), app.prikaz.academic_year],
            [t("common.issueDate"), app.prikaz.issue_date],
            [t("application.status"), app.prikaz.status],
          ]} />
          <button
            type="button"
            onClick={() => studentPortalService.downloadPrikaz()}
            className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-extrabold text-white hover:bg-primary-hover"
          >
            <Download className="h-4 w-4" />
            {t("prikaz.downloadPdf")}
          </button>
        </div>
      ) : (
        <InfoGrid rows={[
          [t("enrollment.issued"), summary.checks?.enrollment_issued ? t("common.yes") : t("common.no")],
          [t("prikaz.status"), t("prikaz.waitingIssuance")],
        ]} />
      )}
    </Panel>
  );

  const renderServiceFee = () => (
    <Panel title={t("student.nav.serviceFee")} icon={CreditCard}>
      {error && <FormError message={error} />}
      <InfoGrid rows={[
        [t("serviceFee.requiredFee"), t("serviceFee.amount")],
        [t("prikaz.issued"), summary.checks?.prikaz_issued ? t("common.yes") : t("common.no")],
        [t("student.nav.serviceFee"), summary.checks?.service_fee_paid ? t("status.approved") : t("status.pending")],
      ]} />
      <div className="space-y-3">
        {(app.service_fee_payments || []).map((payment) => (
          <div key={payment.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
            <div>
              <p className="text-xs font-extrabold text-navy">{payment.payment_number}</p>
              <p className="text-[11px] text-gray-500">{payment.amount} {payment.currency} · {payment.receipt_original_name}</p>
              {payment.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{payment.rejection_reason}</p>}
            </div>
            <StatusPill status={payment.status} />
          </div>
        ))}
      </div>
      {summary.checks?.prikaz_issued && !summary.checks?.service_fee_paid && (
        <label className="inline-flex px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold cursor-pointer">
          {busy === "service_fee" ? <Loader2 className="w-4 h-4 animate-spin" /> : t("serviceFee.uploadReceipt")}
          <input
            className="hidden"
            type="file"
            accept={uploadAccept}
            onChange={(e) => {
              uploadServiceFeeReceipt(e.target.files?.[0]);
              e.target.value = "";
            }}
          />
        </label>
      )}
    </Panel>
  );

  const renderVisa = () => (
    <Panel title={t("student.nav.visa")} icon={ShieldCheck}>
      <InfoGrid rows={[
        [t("visa.telexNumber"), app.visa_process?.telex_number],
        [t("visa.telexStatus"), labelize(app.visa_process?.telex_status)],
        [t("visa.status"), labelize(app.visa_process?.visa_status)],
        [t("common.notes"), app.visa_process?.visa_notes],
      ]} />
    </Panel>
  );

  const renderHousing = () => (
    <Panel title={t("student.nav.housing")} icon={ClipboardList}>
      <InfoGrid rows={[
        [t("housing.requested"), app.housing_request?.requested ? t("common.yes") : t("common.no")],
        [t("application.status"), labelize(app.housing_request?.status)],
        [t("housing.preferredRoom"), app.housing_request?.preferred_room_type],
        [t("common.notes"), app.housing_request?.notes],
        [t("common.administrationNotes"), app.housing_request?.admin_notes],
      ]} />
      {summary.checks?.enrollment_issued && !app.housing_request?.requested && (
        <button onClick={submitHousingRequest} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">
          {t("housing.requestHousing")}
        </button>
      )}
    </Panel>
  );

  const renderResidence = () => (
    <Panel title={t("student.nav.residence")} icon={FileCheck}>
      <InfoGrid rows={[
        [t("application.status"), labelize(app.residence_permit_process?.status)],
        [t("common.issuedAt"), app.residence_permit_process?.issued_at],
        [t("common.expiresAt"), app.residence_permit_process?.expires_at],
        [t("common.notes"), app.residence_permit_process?.notes],
        [t("common.administrationNotes"), app.residence_permit_process?.admin_notes],
      ]} />
    </Panel>
  );

  const renderNotifications = () => (
    <Panel title={t("student.notifications")} icon={Bell}>
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
      {mode === "prikaz" && renderPrikaz()}
      {mode === "serviceFee" && renderServiceFee()}
      {mode === "visa" && renderVisa()}
      {mode === "housing" && renderHousing()}
      {mode === "residence" && renderResidence()}
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
