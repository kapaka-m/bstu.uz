import React, { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useParams } from "react-router-dom";
import { CheckCircle, Download, Plus, Search, XCircle } from "lucide-react";
import LoadingState from "../../../components/common/LoadingState";
import FormError from "../../../components/common/FormError";
import { apanelApplicationsService } from "../../../services/apanelApplicationsService";

const statusClass = (status = "") => {
  const value = String(status).toUpperCase();
  if (["APPROVED", "COMPLETED", "ISSUED", "ADMISSION_ISSUED"].includes(value)) return "bg-emerald-50 text-emerald-700 border-emerald-100";
  if (["REJECTED", "REUPLOAD_REQUIRED", "APPLICATION_REJECTED"].includes(value)) return "bg-rose-50 text-rose-700 border-rose-100";
  return "bg-blue-50 text-blue-700 border-blue-100";
};
const labelize = (value) => String(value || "not_started").replaceAll("_", " ");
const Status = ({ value }) => <span className={`inline-flex px-2.5 py-1 rounded-full border text-[10px] font-black uppercase ${statusClass(value)}`}>{labelize(value)}</span>;

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
      setError(err?.message || "Unable to load applications.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
    // The loader intentionally reacts to route changes and uses the latest local search state on submit.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id, location.pathname]);

  if (loading) return <LoadingState message="Loading applications workflow..." />;
  if (error) return <FormError message={error} />;

  if (!id) {
    const rows = list?.data || [];
    return (
      <div className="space-y-6 animate-in fade-in duration-200">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">Applications</h1>
          <p className="text-xs font-semibold text-gray-400">Review international student applications, documents, payments, and admissions.</p>
        </div>
        <Panel
          title="All Applications"
          action={
            <form
              onSubmit={(e) => {
                e.preventDefault();
                load();
              }}
              className="flex gap-2"
            >
              <input value={search} onChange={(e) => setSearch(e.target.value)} className="px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold" placeholder="Search name, passport, email..." />
              <button className="px-3 py-2 rounded-xl bg-primary text-white text-xs font-extrabold"><Search className="w-4 h-4" /></button>
            </form>
          }
        >
          <div className="overflow-x-auto">
            <table className="min-w-full text-xs">
              <thead>
                <tr className="text-left text-gray-400 uppercase">
                  {["Application", "Student", "Passport", "Program", "Student Type", "Status", "Documents", "Payment", "Admission", ""].map((h) => <th key={h} className="py-3 px-3">{h}</th>)}
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
                    <td className="py-3 px-3"><Status value={app.status} /></td>
                    <td className="py-3 px-3"><Status value={app.documents_status} /></td>
                    <td className="py-3 px-3"><Status value={app.application_fee_status} /></td>
                    <td className="py-3 px-3"><Status value={app.admission_status} /></td>
                    <td className="py-3 px-3"><Link className="text-primary font-extrabold" to={`/apanel/applications/${app.id}`}>Open</Link></td>
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
  if (!app) return <FormError message="Application not found." />;
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
      setError(err?.message || "Action failed.");
    } finally {
      setBusy("");
    }
  };

  const renderTabs = () => (
    <div className="flex flex-wrap gap-2">
      {[
        [base, "Overview"],
        [`${base}/documents`, "Documents"],
        ...(app.student_type === "transfer" ? [[`${base}/equivalency`, "Academic Review"]] : []),
        [`${base}/payments`, "Payments"],
        [`${base}/final-review`, "Final Review"],
        [`${base}/admission`, "Admission"],
        [`${base}/enrollment`, "Enrollment"],
      ].map(([to, label]) => (
        <Link key={to} to={to} className={`px-3 py-2 rounded-xl text-xs font-extrabold border ${location.pathname === to ? "bg-primary text-white border-primary" : "bg-white text-navy border-gray-100"}`}>{label}</Link>
      ))}
    </div>
  );

  const renderOverview = () => (
    <Panel title="Application File">
      <Info rows={[
        ["Application Number", app.application_number],
        ["Student Name", student.full_name_english || student.user?.name],
        ["Email", student.user?.email],
        ["Passport", student.passport_number],
        ["Nationality", student.nationality],
        ["Program", programName],
        ["Degree", app.degree_level],
        ["Student Type", app.student_type],
        ["Current Status", labelize(app.status)],
      ]} />
      <Timeline items={snapshot.timeline || []} />
    </Panel>
  );

  const renderDocuments = () => (
    <Panel
      title="Documents Review"
      action={<button onClick={() => requestExtraDocument(action)} className="px-3 py-2 rounded-xl bg-primary text-white text-xs font-extrabold flex items-center gap-1"><Plus className="w-4 h-4" /> Request Document</button>}
    >
      <div className="space-y-3">
        {(snapshot.requirements || []).map((req) => (
          <div key={req.document_type} className="rounded-2xl border border-gray-100 p-4 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div>
              <p className="text-xs font-extrabold text-navy">{req.name}</p>
              <p className="text-[11px] text-gray-500">{req.document?.original_name || "Not uploaded"}</p>
              {req.document?.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{req.document.rejection_reason}</p>}
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Status value={req.status} />
              {req.document && (
                <>
                  <button onClick={() => apanelApplicationsService.downloadDocument(id, req.document.id)} className="px-3 py-2 rounded-xl border border-gray-200 text-navy text-xs font-extrabold">Download</button>
                  <button onClick={() => action(() => apanelApplicationsService.reviewDocument(id, req.document.id, { status: "APPROVED" }), "Document approved")} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold"><CheckCircle className="inline w-4 h-4" /></button>
                  <button onClick={() => rejectDocument(action, req.document.id)} className="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold"><XCircle className="inline w-4 h-4" /></button>
                </>
              )}
            </div>
          </div>
        ))}
      </div>
    </Panel>
  );

  const renderEquivalency = () => (
    <Panel title="Academic Equivalency">
      <Info rows={[
        ["Status", app.equivalency?.status],
        ["Previous University", app.equivalency?.previous_university],
        ["Accepted Credits", app.equivalency?.accepted_credits],
        ["Rejected Credits", app.equivalency?.rejected_credits],
        ["Entry Year", app.equivalency?.proposed_entry_year],
      ]} />
      <div className="flex flex-wrap gap-2">
        <button onClick={() => seedEquivalency(action)} className="px-3 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">Save Draft Result</button>
        <button onClick={() => action(() => apanelApplicationsService.issueEquivalency(id), "Equivalency result issued")} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">Issue Result</button>
      </div>
    </Panel>
  );

  const renderPayments = () => (
    <Panel title="Payments Review">
      <div className="space-y-6">
        <div className="space-y-3">
          <h3 className="text-xs font-extrabold text-navy uppercase tracking-wider">Application Fee</h3>
          {(app.application_fee_payments || []).map((payment) => (
            <div key={payment.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between gap-4">
              <div>
                <p className="text-xs font-extrabold text-navy">{payment.payment_number}</p>
                <p className="text-[11px] text-gray-500">{payment.amount} {payment.currency} · {payment.receipt_original_name}</p>
                {payment.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{payment.rejection_reason}</p>}
              </div>
              <div className="flex gap-2">
                <Status value={payment.status} />
                <button onClick={() => action(() => apanelApplicationsService.reviewPayment(id, payment.id, { status: "APPROVED" }), "Payment approved")} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">Approve</button>
                <button onClick={() => rejectPayment(action, payment.id)} className="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold">Reject</button>
              </div>
            </div>
          ))}
        </div>
        <div className="space-y-3">
          <h3 className="text-xs font-extrabold text-navy uppercase tracking-wider">30% Contract Payment</h3>
          {(app.contracts || []).flatMap((contract) => (contract.payments || []).map((payment) => ({ ...payment, contract }))).map((payment) => (
            <div key={payment.id} className="rounded-2xl border border-gray-100 p-4 flex justify-between gap-4">
              <div>
                <p className="text-xs font-extrabold text-navy">{payment.payment_number}</p>
                <p className="text-[11px] text-gray-500">{payment.amount} {payment.currency} · {payment.receipt_original_name}</p>
                <p className="text-[10px] font-bold text-gray-400">{payment.contract.contract_number}</p>
                {payment.rejection_reason && <p className="text-[11px] font-bold text-rose-600">{payment.rejection_reason}</p>}
              </div>
              <div className="flex gap-2">
                <Status value={payment.status} />
                <button onClick={() => action(() => apanelApplicationsService.reviewContractPayment(id, payment.id, { status: "APPROVED" }), "30% payment approved")} className="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">Approve</button>
                <button onClick={() => action(() => apanelApplicationsService.reviewContractPayment(id, payment.id, { status: "REUPLOAD_REQUIRED", rejection_reason: window.prompt("Rejection reason:") || "Please upload a clearer contract payment receipt." }), "30% payment rejected")} className="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold">Reject</button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </Panel>
  );

  const renderFinal = () => (
    <Panel title="Final Review Checklist">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
        {Object.entries(snapshot.checks || {}).map(([key, value]) => (
          <div key={key} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
            <span className="text-xs font-extrabold text-navy">{labelize(key)}</span>
            <Status value={value ? "Completed" : "Not Started"} />
          </div>
        ))}
      </div>
      <div className="flex flex-wrap gap-2">
        <button onClick={() => action(() => apanelApplicationsService.approveFinalReview(id), "Final review approved")} className="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">Approve Application</button>
        <button onClick={() => returnCorrection(action)} className="px-4 py-2 rounded-xl bg-amber-500 text-white text-xs font-extrabold">Return for Correction</button>
        <button onClick={() => rejectApplication(action)} className="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-extrabold">Reject Application</button>
      </div>
    </Panel>
  );

  const renderAdmission = () => (
    <Panel title="Admission">
      {app.admission ? (
        <div className="space-y-4">
          <Info rows={[["Admission Number", app.admission.admission_number], ["Issue Date", app.admission.issue_date], ["Status", app.admission.status]]} />
          <button onClick={() => apanelApplicationsService.downloadAdmission(id)} className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-navy text-white text-xs font-extrabold">
            <Download className="h-4 w-4" />
            Download Admission PDF
          </button>
        </div>
      ) : <p className="text-xs font-bold text-gray-500">Admission is not issued yet.</p>}
      <button onClick={() => action(() => apanelApplicationsService.issueAdmission(id), "Admission issued")} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">Issue Admission</button>
    </Panel>
  );

  const renderEnrollment = () => (
    <Panel title="Enrollment">
      {app.enrollment ? (
        <div className="space-y-4">
          <Info rows={[["Student Number", app.enrollment.student_number], ["Academic Year", app.enrollment.academic_year], ["Issue Date", app.enrollment.issue_date], ["Status", app.enrollment.status]]} />
          <button onClick={() => apanelApplicationsService.downloadEnrollment(id)} className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-navy text-white text-xs font-extrabold">
            <Download className="h-4 w-4" />
            Download Enrollment PDF
          </button>
        </div>
      ) : (
        <Info rows={[["Admission Issued", snapshot.checks?.admission_issued ? "Yes" : "No"], ["30% Payment Approved", snapshot.checks?.contract_advance_paid ? "Yes" : "No"]]} />
      )}
      <button onClick={() => action(() => apanelApplicationsService.issueEnrollment(id), "Enrollment certificate issued")} className="px-4 py-2 rounded-xl bg-primary text-white text-xs font-extrabold">Issue Enrollment Certificate</button>
    </Panel>
  );

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">{app.application_number || `Application #${id}`}</h1>
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

function Timeline({ items }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
      {items.map((item) => (
        <div key={item.key} className="rounded-2xl border border-gray-100 p-4 flex justify-between">
          <span className="text-xs font-extrabold text-navy">{item.label}</span>
          <Status value={item.status} />
        </div>
      ))}
    </div>
  );
}

function rejectDocument(action, documentId) {
  const reason = window.prompt("Rejection reason shown to student:");
  if (!reason) return;
  action(() => apanelApplicationsService.reviewDocument(window.location.pathname.split("/")[3], documentId, { status: "REUPLOAD_REQUIRED", rejection_reason: reason }), "Document rejected");
}

function rejectPayment(action, paymentId) {
  const reason = window.prompt("Payment rejection reason shown to student:");
  if (!reason) return;
  action(() => apanelApplicationsService.reviewPayment(window.location.pathname.split("/")[3], paymentId, { status: "REJECTED", rejection_reason: reason }), "Payment rejected");
}

function requestExtraDocument(action) {
  const name = window.prompt("Additional document name:");
  if (!name) return;
  const type = name.toLowerCase().replace(/[^a-z0-9]+/g, "_").replace(/^_|_$/g, "");
  action(() => apanelApplicationsService.requestDocument(window.location.pathname.split("/")[3], { document_type: type, name, is_required: true, request_reason: "Requested by administration" }), "Additional document requested");
}

function seedEquivalency(action) {
  action(() => apanelApplicationsService.saveEquivalency(window.location.pathname.split("/")[3], {
    status: "UNDER_REVIEW",
    previous_university: "Pending academic reviewer input",
    general_academic_notes: "Draft equivalency record created. Replace this with reviewed academic notes.",
    courses: [],
  }), "Equivalency draft saved");
}

function returnCorrection(action) {
  const reason = window.prompt("Correction instructions for student:");
  if (!reason) return;
  action(() => apanelApplicationsService.returnForCorrection(window.location.pathname.split("/")[3], reason), "Application returned for correction");
}

function rejectApplication(action) {
  const reason = window.prompt("Application rejection reason:");
  if (!reason) return;
  action(() => apanelApplicationsService.reject(window.location.pathname.split("/")[3], reason), "Application rejected");
}
