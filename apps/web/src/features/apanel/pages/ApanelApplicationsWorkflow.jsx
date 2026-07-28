import React, { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useParams } from "react-router-dom";
import { CheckCircle, Download, Plus, Search, XCircle } from "lucide-react";
import LoadingState from "../../../components/common/LoadingState";
import FormError from "../../../components/common/FormError";
import { apanelApplicationsService } from "../../../services/apanelApplicationsService";
import { useLanguage } from "../../../context/LanguageContext";

const statusClass = (status = "") => {
  const value = String(status).toUpperCase();
  if (["APPROVED", "COMPLETED", "ISSUED", "ADMISSION_ISSUED"].includes(value)) return "bg-emerald-50 text-emerald-700 border-emerald-100";
  if (["REJECTED", "REUPLOAD_REQUIRED", "APPLICATION_REJECTED"].includes(value)) return "bg-rose-50 text-rose-700 border-rose-100";
  return "bg-blue-50 text-blue-700 border-blue-100";
};
const labelize = (value) => String(value || "not_started").replaceAll("_", " ");
const Status = ({ value, t }) => <span className={`inline-flex px-2.5 py-1 rounded-full border text-[10px] font-black uppercase ${statusClass(value)}`}>{t(`status.${String(value || "not_started").toLowerCase()}`)}</span>;

function Panel({ title, children, action }) {
  return (
    <section className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-5">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-50 pb-3">
        <h2 className="text-sm font-extrabold text-navy uppercase tracking-wider">{title}</h2>
        {action}
      </div>
      {children}
    </section>
  );
}

export default function ApanelApplicationsWorkflow() {
  const { t } = useLanguage();
  const { id } = useParams();
  const location = useLocation();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [list, setList] = useState(null);
  const [snapshot, setSnapshot] = useState(null);
  const [search, setSearch] = useState("");
  const [busy, setBusy] = useState("");

  const section = useMemo(() => {
    const path = location.pathname;
    if (path.endsWith("/documents")) return "documents";
    if (path.endsWith("/equivalency")) return "equivalency";
    if (path.endsWith("/payments")) return "payments";
    if (path.endsWith("/final-review")) return "final";
    if (path.endsWith("/admission")) return "admission";
    if (path.endsWith("/enrollment")) return "enrollment";
    if (path.endsWith("/prikaz")) return "prikaz";
    if (path.endsWith("/service-fee")) return "serviceFee";
    if (path.endsWith("/visa")) return "visa";
    if (path.endsWith("/housing")) return "housing";
    if (path.endsWith("/residence")) return "residence";
    return id ? "overview" : "list";
  }, [location.pathname, id]);

  const load = async () => {
    try {
      setLoading(true);
      setError("");
      if (id) {
        const data = await apanelApplicationsService.get(id);
        setSnapshot(data);
      } else {
        const data = await apanelApplicationsService.list({ search, per_page: 20 });
        setList(data);
      }
    } catch (err) {
      setError(err?.message || t("apanel.workflow.loadFailed"));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
    // The loader intentionally reacts to route changes and uses the latest local search state on submit.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id, location.pathname]);

  if (loading) return <LoadingState message={t("apanel.workflow.loading")} />;
  if (error) return <FormError message={error} />;

  if (!id) {
    const rows = list?.data || [];
    return (
      <div className="space-y-6 animate-in fade-in duration-200">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">{t("apanel.workflow.applications")}</h1>
          <p className="text-xs font-semibold text-gray-400">{t("apanel.workflow.subtitle")}</p>
        </div>
        <Panel
          title={t("apanel.workflow.allApplications")}
          action={
            <form
              onSubmit={(e) => {
                e.preventDefault();
                load();
              }}
              className="flex gap-2"
            >
              <input value={search} onChange={(e) => setSearch(e.target.value)} className="px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold" placeholder={t("apanel.workflow.searchPlaceholder")} />
              <button className="px-3 py-2 rounded-xl bg-primary text-white text-xs font-extrabold"><Search className="w-4 h-4" /></button>
            </form>
          }
        >
          <div className="overflow-x-auto">
            <table className="min-w-full text-xs">
              <thead>
                <tr className="text-left text-gray-400 uppercase">
                  {[
                    t("apanel.workflow.application"),
                    t("apanel.workflow.student"),
                    t("apanel.workflow.passport"),
                    t("apanel.workflow.program"),
                    t("apanel.workflow.studentType"),
                    t("apanel.workflow.status"),
                    t("apanel.workflow.documents"),
                    t("apanel.workflow.payment"),
                    t("apanel.workflow.admission"),
                    "",
                  ].map((h) => <th key={h} className="py-3 px-3">{h}</th>)}
                </tr>
              </thead>
              <tbody>
                {rows.map((app) => (
                  <tr key={app.id} className="border-t border-gray-50">
                    <td className="py-3 px-3 font-extrabold text-navy">{app.application_number || `#${app.id}`}</td>
                    <td className="py-3 px-3">{app.student_profile?.full_name_english || app.student_profile?.user?.name}</td>
                    <td className="py-3 px-3">{app.student_profile?.passport_number}</td>
                    <td className="py-3 px-3">{app.program?.translations?.[0]?.name || app.program?.slug}</td>
                    <td className="py-3 px-3">{app.student_type}</td>
                    <td className="py-3 px-3"><Status value={app.status} t={t} /></td>
                    <td className="py-3 px-3"><Status value={app.documents_status} t={t} /></td>
                    <td className="py-3 px-3"><Status value={app.application_fee_status} t={t} /></td>
                    <td className="py-3 px-3"><Status value={app.admission_status} t={t} /></td>
                    <td className="py-3 px-3"><Link className="text-primary font-extrabold" to={`/apanel/applications/${app.id}`}>{t("apanel.workflow.open")}</Link></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Panel>
      </div>
    );
  }

  const app = snapshot?.application;
  if (!app) return <FormError message={t("apanel.workflow.notFound")} />;
  const student = app.student_profile || {};
  const programName = app.program?.translations?.[0]?.name || app.program?.slug;
  const base = `/apanel/applications/${id}`;

  const action = async (fn, message) => {
    try {
      setBusy(message);
      setError("");
      setSuccess("");
      await fn();
      setSuccess(message);
      await load();
    } catch (err) {
      setError(err?.message || t("apanel.workflow.actionFailed"));
    } finally {
      setBusy("");
    }
  };

  const renderTabs = () => (
    <div className="flex flex-wrap gap-2">
      {[
        [base, t("apanel.workflow.overview")],
        [`${base}/documents`, t("apanel.workflow.documents")],
        ...(app.student_type === "transfer" ? [[`${base}/equivalency`, t("apanel.workflow.academicReview")]] : []),
        [`${base}/payments`, t("apanel.workflow.payments")],
        [`${base}/final-review`, t("apanel.workflow.finalReview")],
        [`${base}/admission`, t("apanel.workflow.admission")],
        [`${base}/enrollment`, t("apanel.workflow.enrollment")],
        [`${base}/prikaz`, t("apanel.workflow.prikaz")],
        [`${base}/service-fee`, t("apanel.workflow.serviceFee")],
        [`${base}/visa`, t("apanel.workflow.visa")],
        [`${base}/housing`, t("apanel.workflow.housing")],
        [`${base}/residence`, t("apanel.workflow.residence")],
      ].map(([to, label]) => (
        <Link key={to} to={to} className={`px-3 py-2 rounded-xl text-xs font-extrabold border ${location.pathname === to ? "bg-primary text-white border-primary" : "bg-white text-navy border-gray-100"}`}>{label}</Link>
      ))}
    </div>
  );

  const renderOverview = () => (
    <Panel title={t("apanel.workflow.applicationFile")}>
      <Info rows={[
        [t("apanel.workflow.applicationNumber"), app.application_number],
        [t("apanel.workflow.studentName"), student.full_name_english || student.user?.name],
        [t("apanel.workflow.email"), student.user?.email],
        [t("apanel.workflow.passport"), student.passport_number],
        [t("apanel.workflow.nationality"), student.nationality],
        [t("apanel.workflow.program"), programName],
        [t("apanel.workflow.degree"), app.degree_level],
        [t("apanel.workflow.studentType"), app.student_type],
        [t("apanel.workflow.currentStatus"), labelize(app.status)],
      ]} />
      <Timeline items={snapshot.timeline || []} t={t} />
    </Panel>
  );

  const renderDocuments = () => (
    <Panel
      title={t("apanel.workflow.documentsReview")}
      action={<button onClick={() => requestExtraDocument(action, t)} className="px-3 py-2 rounded-xl bg-primary text-white text-xs font-extrabold flex items-center gap-1"><Plus className="w-4 h-4" /> {t("apanel.workflow.requestDocument")}</button>}
    >
      <div className="space-y-3">
        {(snapshot.requirements || []).map((req) => (
          <div key={req.document_type} className="rounded-2xl border border-gray-100 p-4 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div>
              <p className="text-xs font-extrabold text-navy">{req.name}</p>
              <p className="text-[11px] text-gray-500">{req.document?.original_name || t("apanel.workflow.notUploaded")}</p>
              {req.document?.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{req.document.rejection_reason}</p>}
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Status value={req.status} t={t} />
              {req.document && (
                <>
                  <button onClick={() => apanelApplicationsService.downloadDocument(id, req.document.id)} className="px-3 py-2 rounded-xl border border-gray-200 text-navy text-xs font-extrabold">{t("button.download")}</button>
                  <button onClick={() => action(() => apanelApplicationsService.reviewDocument(id, req.document.id, { status: "APPROVED" }), t("apanel.workflow.documentApproved"))} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold"><CheckCircle className="inline w-4 h-4" /></button>
                  <button onClick={() => rejectDocument(action, req.document.id, t)} className="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold"><XCircle className="inline w-4 h-4" /></button>
                </>
              )}
            </div>
          </div>
        ))}
      </div>
    </Panel>
  );

  const renderEquivalency = () => (
    <Panel title={t("apanel.workflow.academicEquivalency")}>
      <Info rows={[
        [t("apanel.workflow.status"), app.equivalency?.status],
        [t("apanel.workflow.previousUniversity"), app.equivalency?.previous_university],
        [t("apanel.workflow.acceptedCredits"), app.equivalency?.accepted_credits],
        [t("apanel.workflow.rejectedCredits"), app.equivalency?.rejected_credits],
        [t("apanel.workflow.entryYear"), app.equivalency?.proposed_entry_year],
      ]} />
      <div className="flex flex-wrap gap-2">
        <button onClick={() => seedEquivalency(action, t)} className="px-3 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">{t("apanel.workflow.saveDraftResult")}</button>
        <button onClick={() => action(() => apanelApplicationsService.issueEquivalency(id), t("apanel.workflow.equivalencyIssued"))} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">{t("apanel.workflow.issueResult")}</button>
      </div>
    </Panel>
  );

  const renderPayments = () => (
    <Panel title={t("apanel.workflow.paymentsReview")}>
      <div className="space-y-6">
        <div className="space-y-3">
          <h3 className="text-xs font-extrabold text-navy uppercase tracking-wider">{t("apanel.workflow.applicationFee")}</h3>
          {(app.application_fee_payments || []).map((payment) => (
            <div key={payment.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between gap-4">
              <div>
                <p className="text-xs font-extrabold text-navy">{payment.payment_number}</p>
                <p className="text-[11px] text-gray-500">{payment.amount} {payment.currency} · {payment.receipt_original_name}</p>
                {payment.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{payment.rejection_reason}</p>}
              </div>
              <div className="flex gap-2">
                <Status value={payment.status} t={t} />
                <button onClick={() => action(() => apanelApplicationsService.reviewPayment(id, payment.id, { status: "APPROVED" }), t("apanel.workflow.paymentApproved"))} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">{t("button.approve")}</button>
                <button onClick={() => rejectPayment(action, payment.id, t)} className="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold">{t("button.reject")}</button>
              </div>
            </div>
          ))}
        </div>
        <div className="space-y-3">
          <h3 className="text-xs font-extrabold text-navy uppercase tracking-wider">{t("apanel.workflow.contractAdvancePayment")}</h3>
          {(app.contracts || []).map((contract) => (
            <div key={contract.id} className="rounded-2xl border border-gray-100 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <Info rows={[
                [t("apanel.workflow.contractNumber"), contract.contract_number],
                [t("apanel.workflow.amount"), [contract.amount, contract.currency].filter(Boolean).join(" ")],
                [t("apanel.workflow.advance"), [contract.advance_amount, contract.currency].filter(Boolean).join(" ")],
              ]} />
              <button onClick={() => apanelApplicationsService.downloadContract(id)} className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-navy text-white text-xs font-extrabold">
                <Download className="h-4 w-4" />
                {t("apanel.workflow.contractPdf")}
              </button>
            </div>
          ))}
          {(app.contracts || []).flatMap((contract) => (contract.payments || []).map((payment) => ({ ...payment, contract }))).map((payment) => (
            <div key={payment.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between gap-4">
              <div>
                <p className="text-xs font-extrabold text-navy">{payment.payment_number}</p>
                <p className="text-[11px] text-gray-500">{payment.amount} {payment.currency} · {payment.receipt_original_name}</p>
                <p className="text-[10px] font-bold text-gray-400">{payment.contract.contract_number}</p>
                {payment.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{payment.rejection_reason}</p>}
              </div>
              <div className="flex gap-2">
                <Status value={payment.status} t={t} />
                <button onClick={() => action(() => apanelApplicationsService.reviewContractPayment(id, payment.id, { status: "APPROVED" }), t("apanel.workflow.contractPaymentApproved"))} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">{t("button.approve")}</button>
                <button onClick={() => action(() => apanelApplicationsService.reviewContractPayment(id, payment.id, { status: "REUPLOAD_REQUIRED", rejection_reason: window.prompt(t("apanel.workflow.rejectionReasonPrompt")) || t("apanel.workflow.clearerContractReceipt") }), t("apanel.workflow.contractPaymentRejected"))} className="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold">{t("button.reject")}</button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </Panel>
  );

  const renderFinal = () => (
    <Panel title={t("apanel.workflow.finalReviewChecklist")}>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
        {Object.entries(snapshot.checks || {}).map(([key, value]) => (
          <div key={key} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
            <span className="text-xs font-extrabold text-navy">{labelize(key)}</span>
            <Status value={value ? "completed" : "not_started"} t={t} />
          </div>
        ))}
      </div>
      <div className="flex flex-wrap gap-2">
        <button onClick={() => action(() => apanelApplicationsService.approveFinalReview(id), t("apanel.workflow.finalReviewApproved"))} className="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">{t("apanel.workflow.approveApplication")}</button>
        <button onClick={() => returnCorrection(action, t)} className="px-4 py-2 rounded-xl bg-amber-500 text-white text-xs font-extrabold">{t("apanel.workflow.returnForCorrection")}</button>
        <button onClick={() => rejectApplication(action, t)} className="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold">{t("apanel.workflow.rejectApplication")}</button>
      </div>
    </Panel>
  );

  const renderAdmission = () => (
    <Panel title={t("apanel.workflow.admission")}>
      {app.admission ? (
        <div className="space-y-4">
          <Info rows={[
            [t("apanel.workflow.admissionNumber"), app.admission.admission_number],
            [t("apanel.workflow.issueDate"), app.admission.issue_date],
            [t("apanel.workflow.status"), app.admission.status],
          ]} />
          <button onClick={() => apanelApplicationsService.downloadAdmission(id)} className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-navy text-white text-xs font-extrabold">
            <Download className="h-4 w-4" />
            {t("apanel.workflow.downloadAdmissionPdf")}
          </button>
        </div>
      ) : <p className="text-xs font-bold text-gray-500">{t("apanel.workflow.admissionNotIssued")}</p>}
      <button onClick={() => action(() => apanelApplicationsService.issueAdmission(id), t("apanel.workflow.admissionIssued"))} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">{t("apanel.workflow.issueAdmission")}</button>
    </Panel>
  );

  const renderEnrollment = () => (
    <Panel title={t("apanel.workflow.enrollment")}>
      {app.enrollment ? (
        <div className="space-y-4">
          <Info rows={[
            [t("apanel.workflow.studentNumber"), app.enrollment.student_number],
            [t("apanel.workflow.academicYear"), app.enrollment.academic_year],
            [t("apanel.workflow.issueDate"), app.enrollment.issue_date],
            [t("apanel.workflow.status"), app.enrollment.status],
          ]} />
          <button onClick={() => apanelApplicationsService.downloadEnrollment(id)} className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-navy text-white text-xs font-extrabold">
            <Download className="h-4 w-4" />
            {t("apanel.workflow.downloadEnrollmentPdf")}
          </button>
        </div>
      ) : (
        <Info rows={[
          [t("apanel.workflow.admissionIssued"), snapshot.checks?.admission_issued ? t("common.yes") : t("common.no")],
          [t("apanel.workflow.contractPaymentApproved"), snapshot.checks?.contract_advance_paid ? t("common.yes") : t("common.no")],
        ]} />
      )}
      <button onClick={() => action(() => apanelApplicationsService.issueEnrollment(id), t("apanel.workflow.enrollmentCertificateIssued"))} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">{t("apanel.workflow.issueEnrollmentCertificate")}</button>
    </Panel>
  );

  const renderPrikaz = () => (
    <Panel title={t("apanel.workflow.prikaz")}>
      {app.prikaz ? (
        <div className="space-y-4">
          <Info rows={[
            [t("apanel.workflow.prikazNumber"), app.prikaz.prikaz_number],
            [t("apanel.workflow.academicYear"), app.prikaz.academic_year],
            [t("apanel.workflow.issueDate"), app.prikaz.issue_date],
            [t("apanel.workflow.status"), app.prikaz.status],
          ]} />
          <button onClick={() => apanelApplicationsService.downloadPrikaz(id)} className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-navy text-white text-xs font-extrabold">
            <Download className="h-4 w-4" />
            {t("apanel.workflow.downloadPrikazPdf")}
          </button>
        </div>
      ) : (
        <Info rows={[
          [t("apanel.workflow.enrollmentIssued"), snapshot.checks?.enrollment_issued ? t("common.yes") : t("common.no")],
          [t("apanel.workflow.prikaz"), t("apanel.workflow.notIssued")],
        ]} />
      )}
      <button onClick={() => action(() => apanelApplicationsService.issuePrikaz(id), t("apanel.workflow.prikazIssued"))} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">{t("apanel.workflow.issuePrikaz")}</button>
    </Panel>
  );

  const renderServiceFee = () => (
    <Panel title={t("apanel.workflow.serviceFeeReview")}>
      <Info rows={[
        [t("apanel.workflow.requiredAmount"), app.service_fee_required_amount || t("apanel.workflow.serviceFeeDefaultAmount")],
        [t("apanel.workflow.prikazIssued"), snapshot.checks?.prikaz_issued ? t("common.yes") : t("common.no")],
        [t("apanel.workflow.serviceFeeApproved"), snapshot.checks?.service_fee_paid ? t("common.yes") : t("common.no")],
      ]} />
      <div className="space-y-3">
        {(app.service_fee_payments || []).map((payment) => (
          <div key={payment.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between gap-4">
            <div>
              <p className="text-xs font-extrabold text-navy">{payment.payment_number}</p>
              <p className="text-[11px] text-gray-500">{payment.amount} {payment.currency} · {payment.receipt_original_name}</p>
              {payment.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{payment.rejection_reason}</p>}
            </div>
            <div className="flex gap-2">
              <Status value={payment.status} t={t} />
              <button onClick={() => action(() => apanelApplicationsService.reviewServiceFee(id, payment.id, { status: "APPROVED" }), t("apanel.workflow.serviceFeeApproved"))} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">{t("button.approve")}</button>
              <button onClick={() => action(() => apanelApplicationsService.reviewServiceFee(id, payment.id, { status: "REUPLOAD_REQUIRED", rejection_reason: window.prompt(t("apanel.workflow.rejectionReasonPrompt")) || t("apanel.workflow.clearerServiceFeeReceipt") }), t("apanel.workflow.serviceFeeRejected"))} className="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold">{t("button.reject")}</button>
            </div>
          </div>
        ))}
      </div>
    </Panel>
  );

  const renderVisa = () => (
    <Panel title={t("apanel.workflow.visa")}>
      <Info rows={[
        [t("apanel.workflow.telexNumber"), app.visa_process?.telex_number],
        [t("apanel.workflow.telexStatus"), app.visa_process?.telex_status],
        [t("apanel.workflow.visaStatus"), app.visa_process?.visa_status],
        [t("apanel.workflow.notes"), app.visa_process?.visa_notes],
      ]} />
      <div className="flex flex-wrap gap-2">
        <button onClick={() => updateVisa(action, id, "telex", t)} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">{t("apanel.workflow.markTelexIssued")}</button>
        <button onClick={() => action(() => apanelApplicationsService.updateVisa(id, { visa_status: "ISSUED", visa_notes: t("apanel.workflow.visaReadyNote") }), t("apanel.workflow.visaMarkedReady"))} className="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">{t("apanel.workflow.markVisaReady")}</button>
      </div>
    </Panel>
  );

  const renderHousing = () => (
    <Panel title={t("apanel.workflow.housing")}>
      <Info rows={[
        [t("apanel.workflow.requested"), app.housing_request?.requested ? t("common.yes") : t("common.no")],
        [t("apanel.workflow.status"), app.housing_request?.status],
        [t("apanel.workflow.preferredRoom"), app.housing_request?.preferred_room_type],
        [t("apanel.workflow.studentNotes"), app.housing_request?.notes],
        [t("apanel.workflow.adminNotes"), app.housing_request?.admin_notes],
      ]} />
      <div className="flex flex-wrap gap-2">
        <button onClick={() => action(() => apanelApplicationsService.reviewHousing(id, { status: "APPROVED", admin_notes: window.prompt(t("apanel.workflow.housingNotesPrompt")) || "" }), t("apanel.workflow.housingApproved"))} className="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">{t("apanel.workflow.approveHousing")}</button>
        <button onClick={() => action(() => apanelApplicationsService.reviewHousing(id, { status: "REJECTED", admin_notes: window.prompt(t("apanel.workflow.housingRejectionPrompt")) || t("apanel.workflow.housingUnavailable") }), t("apanel.workflow.housingRejected"))} className="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold">{t("apanel.workflow.rejectHousing")}</button>
        <button onClick={() => action(() => apanelApplicationsService.reviewHousing(id, { status: "NOT_REQUIRED", admin_notes: t("apanel.workflow.housingNotRequiredNote") }), t("apanel.workflow.housingMarkedNotRequired"))} className="px-4 py-2 rounded-xl border border-gray-200 text-navy text-xs font-extrabold">{t("apanel.workflow.notRequired")}</button>
      </div>
    </Panel>
  );

  const renderResidence = () => (
    <Panel title={t("apanel.workflow.residence")}>
      <Info rows={[
        [t("apanel.workflow.status"), app.residence_permit_process?.status],
        [t("apanel.workflow.issuedAt"), app.residence_permit_process?.issued_at],
        [t("apanel.workflow.expiresAt"), app.residence_permit_process?.expires_at],
        [t("apanel.workflow.adminNotes"), app.residence_permit_process?.admin_notes],
      ]} />
      <div className="flex flex-wrap gap-2">
        <button onClick={() => action(() => apanelApplicationsService.updateResidence(id, { status: "IN_PROGRESS", admin_notes: t("apanel.workflow.residenceInProgressNote") }), t("apanel.workflow.residenceMarkedInProgress"))} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">{t("apanel.workflow.markInProgress")}</button>
        <button onClick={() => action(() => apanelApplicationsService.updateResidence(id, { status: "ISSUED", expires_at: window.prompt(t("apanel.workflow.residenceExpiryPrompt")) || "", admin_notes: t("apanel.workflow.residenceIssuedNote") }), t("apanel.workflow.residenceIssued"))} className="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">{t("apanel.workflow.markIssued")}</button>
      </div>
    </Panel>
  );

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">{app.application_number || `${t("apanel.workflow.application")} #${id}`}</h1>
          <p className="text-xs font-semibold text-gray-400">{student.full_name_english || student.user?.name} · {programName}</p>
        </div>
        {renderTabs()}
      </div>
      {success && <div className="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-xs font-bold text-emerald-700">{busy || success}</div>}
      {section === "overview" && renderOverview()}
      {section === "documents" && renderDocuments()}
      {section === "equivalency" && renderEquivalency()}
      {section === "payments" && renderPayments()}
      {section === "final" && renderFinal()}
      {section === "admission" && renderAdmission()}
      {section === "enrollment" && renderEnrollment()}
      {section === "prikaz" && renderPrikaz()}
      {section === "serviceFee" && renderServiceFee()}
      {section === "visa" && renderVisa()}
      {section === "housing" && renderHousing()}
      {section === "residence" && renderResidence()}
    </div>
  );
}

function Info({ rows }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
      {rows.map(([label, value]) => (
        <div key={label} className="rounded-2xl border border-gray-100 bg-gray-50/60 p-4">
          <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">{label}</p>
          <p className="mt-1 text-xs font-extrabold text-navy wrap-break-word">{value || "—"}</p>
        </div>
      ))}
    </div>
  );
}

function Timeline({ items, t }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
      {items.map((item) => (
        <div key={item.key} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
          <span className="text-xs font-extrabold text-navy">{item.label}</span>
          <Status value={item.status} t={t} />
        </div>
      ))}
    </div>
  );
}

function rejectDocument(action, documentId, t) {
  const reason = window.prompt(t("apanel.workflow.documentRejectionPrompt"));
  if (!reason) return;
  action(() => apanelApplicationsService.reviewDocument(window.location.pathname.split("/")[3], documentId, { status: "REUPLOAD_REQUIRED", rejection_reason: reason }), t("apanel.workflow.documentRejected"));
}

function rejectPayment(action, paymentId, t) {
  const reason = window.prompt(t("apanel.workflow.paymentRejectionPrompt"));
  if (!reason) return;
  action(() => apanelApplicationsService.reviewPayment(window.location.pathname.split("/")[3], paymentId, { status: "REJECTED", rejection_reason: reason }), t("apanel.workflow.paymentRejected"));
}

function updateVisa(action, id, type, t) {
  if (type === "telex") {
    const telexNumber = window.prompt(t("apanel.workflow.telexNumberPrompt"));
    if (!telexNumber) return;
    action(
      () =>
        apanelApplicationsService.updateVisa(id, {
          telex_number: telexNumber,
          telex_status: "ISSUED",
          visa_status: "IN_PROGRESS",
          visa_notes: t("apanel.workflow.telexIssuedNote"),
        }),
      t("apanel.workflow.telexIssued"),
    );
  }
}

function requestExtraDocument(action, t) {
  const name = window.prompt(t("apanel.workflow.additionalDocumentPrompt"));
  if (!name) return;
  const type = name.toLowerCase().replace(/[^a-z0-9]+/g, "_").replace(/^_|_$/g, "");
  action(() => apanelApplicationsService.requestDocument(window.location.pathname.split("/")[3], { document_type: type, name, is_required: true, request_reason: t("apanel.workflow.requestedByAdministration") }), t("apanel.workflow.additionalDocumentRequested"));
}

function seedEquivalency(action, t) {
  action(() => apanelApplicationsService.saveEquivalency(window.location.pathname.split("/")[3], {
    status: "UNDER_REVIEW",
    previous_university: t("apanel.workflow.pendingReviewerInput"),
    general_academic_notes: t("apanel.workflow.equivalencyDraftNotes"),
    courses: [],
  }), t("apanel.workflow.equivalencyDraftSaved"));
}

function returnCorrection(action, t) {
  const reason = window.prompt(t("apanel.workflow.correctionPrompt"));
  if (!reason) return;
  action(() => apanelApplicationsService.returnForCorrection(window.location.pathname.split("/")[3], reason), t("apanel.workflow.applicationReturned"));
}

function rejectApplication(action, t) {
  const reason = window.prompt(t("apanel.workflow.applicationRejectionPrompt"));
  if (!reason) return;
  action(() => apanelApplicationsService.reject(window.location.pathname.split("/")[3], reason), t("apanel.workflow.applicationRejected"));
}
