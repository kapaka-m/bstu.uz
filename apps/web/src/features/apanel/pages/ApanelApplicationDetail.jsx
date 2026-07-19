import React, { useState, useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import { apanelService } from "../../../services/apanelService";
import { useAuth } from "../../../context/AuthContext";
import {
  ArrowLeft,
  Loader2,
  User,
  FileText,
  ClipboardList,
  History,
  CreditCard,
  ExternalLink,
  CheckCircle,
  XCircle,
} from "lucide-react";
import StatusBadge from "../components/StatusBadge";
import FormError from "../../../components/common/FormError";

export default function ApanelApplicationDetail() {
  const { id } = useParams();
  const { user: adminUser } = useAuth();

  const [application, setApplication] = useState(null);
  const [loading, setLoading] = useState(true);
  const [updating, setUpdating] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  // Update controls states
  const [status, setStatus] = useState("");
  const [comment, setComment] = useState("");
  const [sendNotify, setSendNotify] = useState(true);
  const [notifyTitle, setNotifyTitle] = useState("");
  const [notifyMessage, setNotifyMessage] = useState("");
  const [contractAmount, setContractAmount] = useState("");
  const [paymentAmount, setPaymentAmount] = useState("");

  const fetchApplication = React.useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      // Get the application details (which loads translations & relationships automatically)
      const data = await apanelService.get("applications", id);
      setApplication(data);
      setStatus(data.status || "");
      setNotifyTitle(
        `Application Status Update: ${String(data.status).replace("_", " ").toUpperCase()}`,
      );
      setNotifyMessage(
        `Dear student, the status of your application for ${data?.program?.translations?.[0]?.name || "selected program"} has been updated to ${String(data.status).replace("_", " ")}.`,
      );
    } catch {
      setError("Failed to load application details.");
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => {
    fetchApplication();
  }, [fetchApplication]);

  const handleStatusUpdate = async (e) => {
    e.preventDefault();
    if (!status) return;

    try {
      setUpdating(true);
      setError("");
      setSuccess("");

      // 1. Call status update (which also saves history log in Laravel)
      await apanelService.updateApplicationStatus(
        id,
        application,
        status,
        comment,
        adminUser?.id,
      );

      // 2. If send notification is toggled, call notify service
      if (sendNotify && application?.studentProfile?.user_id) {
        const title = notifyTitle || `Application Status Update`;
        const msg =
          notifyMessage ||
          `Your application status has been changed to ${status}.`;
        await apanelService.sendNotification(
          application.studentProfile.user_id,
          title,
          msg,
        );
      }

      setSuccess("Application status updated successfully!");
      setComment("");
      fetchApplication();
    } catch (err) {
      setError(err?.message || "Failed to update application status.");
    } finally {
      setUpdating(false);
    }
  };

  const handleDocumentAction = async (docId, action) => {
    try {
      const doc = application?.documents?.find((d) => d.id === docId);
      if (!doc) return;

      const statusMap = {
        approve: "approved",
        reject: "rejected",
        request: "requested",
      };

      await apanelService.update("application-documents", docId, {
        application_id: id,
        document_name: doc.document_name,
        document_type: doc.document_type || doc.document_name,
        file_path: doc.file_path,
        original_name: doc.original_name || null,
        mime_type: doc.mime_type || null,
        size: doc.size || null,
        status: statusMap[action],
        note:
          action === "request"
            ? "Replacement or missing document requested by admissions."
            : doc.note,
      });

      setSuccess(`Document status marked as ${statusMap[action]}!`);
      fetchApplication();
    } catch {
      setError("Failed to verify document.");
    }
  };

  const handleCreateContract = async () => {
    if (!contractAmount) {
      setError("Enter a contract amount first.");
      return;
    }

    try {
      setUpdating(true);
      setError("");
      setSuccess("");
      await apanelService.create("contracts", {
        application_id: Number(id),
        contract_number: `BSTU-${id}-${Date.now()}`,
        amount: Number(contractAmount),
        status: "pending",
      });
      await apanelService.updateApplicationStatus(
        id,
        application,
        "contract_pending",
        "Contract record created.",
        adminUser?.id,
      );
      setContractAmount("");
      setSuccess("Contract created and application marked contract pending.");
      fetchApplication();
    } catch (err) {
      setError(err?.message || "Failed to create contract.");
    } finally {
      setUpdating(false);
    }
  };

  const handleCreatePayment = async () => {
    const contract = application?.contracts?.[0];
    if (!contract) {
      setError("Create a contract before requesting payment.");
      return;
    }
    if (!paymentAmount) {
      setError("Enter a payment amount first.");
      return;
    }

    try {
      setUpdating(true);
      setError("");
      setSuccess("");
      await apanelService.create("payments", {
        contract_id: contract.id,
        payment_number: `PAY-${contract.id}-${Date.now()}`,
        amount: Number(paymentAmount),
        payment_date: new Date().toISOString().slice(0, 10),
        status: "pending",
      });
      await apanelService.updateApplicationStatus(
        id,
        application,
        "payment_pending",
        "Payment request created.",
        adminUser?.id,
      );
      setPaymentAmount("");
      setSuccess(
        "Payment request created and application marked payment pending.",
      );
      fetchApplication();
    } catch (err) {
      setError(err?.message || "Failed to create payment request.");
    } finally {
      setUpdating(false);
    }
  };

  const handleMarkEnrolled = async () => {
    try {
      setUpdating(true);
      setError("");
      setSuccess("");
      await apanelService.updateApplicationStatus(
        id,
        application,
        "enrolled",
        "Applicant marked as enrolled.",
        adminUser?.id,
      );
      setSuccess("Application marked as enrolled.");
      fetchApplication();
    } catch (err) {
      setError(err?.message || "Failed to mark enrolled.");
    } finally {
      setUpdating(false);
    }
  };

  const statuses = [
    "draft",
    "submitted",
    "under_review",
    "missing_documents",
    "accepted",
    "rejected",
    "contract_pending",
    "payment_pending",
    "enrolled",
    "active_student",
    "graduated",
  ];

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-100">
        <Loader2 className="w-8 h-8 animate-spin text-primary" />
      </div>
    );
  }

  if (!application) {
    return (
      <div className="bg-white border border-gray-100 rounded-3xl p-8 text-center text-gray-400 font-semibold shadow-xs">
        Application not found.
      </div>
    );
  }

  const student = application.studentProfile || {};
  const user = student.user || {};
  const programName =
    application.program?.translations?.[0]?.name ||
    application.program?.slug ||
    "Selected Program";

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Back Header */}
      <div className="flex items-center gap-3">
        <Link
          to="/apanel/applications"
          className="p-2 border border-gray-250 hover:border-gray-350 text-navy bg-white rounded-xl cursor-pointer transition-all"
        >
          <ArrowLeft className="w-4 h-4" />
        </Link>
        <div>
          <h1 className="text-xl font-extrabold text-navy uppercase tracking-wider">
            Application Review
          </h1>
          <p className="text-gray-400 text-xs font-semibold">
            ID: #{application.id} — Program: {programName}
          </p>
        </div>
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl text-emerald-700 text-xs font-bold">
          {success}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Column: Student Details & Documents */}
        <div className="lg:col-span-2 space-y-6">
          {/* Student Profile Card */}
          <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
            <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
              <User className="w-5 h-5 text-navy" />
              <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
                Student Profile
              </h3>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-semibold">
              <div className="space-y-1">
                <span className="text-[10px] text-gray-400 uppercase font-bold block">
                  Full Name:
                </span>
                <span className="text-navy font-extrabold">
                  {user.name || "—"}
                </span>
              </div>
              <div className="space-y-1">
                <span className="text-[10px] text-gray-400 uppercase font-bold block">
                  Email:
                </span>
                <span className="text-navy">{user.email || "—"}</span>
              </div>
              <div className="space-y-1">
                <span className="text-[10px] text-gray-400 uppercase font-bold block">
                  Nationality:
                </span>
                <span className="text-navy">{student.nationality || "—"}</span>
              </div>
              <div className="space-y-1">
                <span className="text-[10px] text-gray-400 uppercase font-bold block">
                  Passport Number:
                </span>
                <span className="text-navy">
                  {student.passport_number || "—"}
                </span>
              </div>
              <div className="space-y-1">
                <span className="text-[10px] text-gray-400 uppercase font-bold block">
                  Gender:
                </span>
                <span className="text-navy capitalize">
                  {student.gender || "—"}
                </span>
              </div>
              <div className="space-y-1">
                <span className="text-[10px] text-gray-400 uppercase font-bold block">
                  Phone Number:
                </span>
                <span className="text-navy">{student.phone || "—"}</span>
              </div>
            </div>
          </div>

          {/* Uploaded Documents List */}
          <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
            <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
              <FileText className="w-5 h-5 text-navy" />
              <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
                Verification Documents
              </h3>
            </div>

            <div className="divide-y divide-gray-50">
              {!application.documents || application.documents.length === 0 ? (
                <p className="text-xs font-semibold text-gray-400 py-4">
                  No documents uploaded.
                </p>
              ) : (
                application.documents.map((doc) => {
                  const path =
                    doc.file_path.startsWith("http") ||
                    doc.file_path.startsWith("/")
                      ? doc.file_path
                      : "/storage/" + doc.file_path;
                  const documentStatus =
                    doc.status ||
                    (doc.document_name.includes("[APPROVED]")
                      ? "approved"
                      : doc.document_name.includes("[REJECTED]")
                        ? "rejected"
                        : "pending");
                  const isApproved = documentStatus === "approved";
                  const isRejected = documentStatus === "rejected";
                  const isRequested = documentStatus === "requested";

                  return (
                    <div
                      key={doc.id}
                      className="py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs font-semibold"
                    >
                      <div className="space-y-1">
                        <span className="font-extrabold text-navy">
                          {doc.document_type ||
                            doc.document_name
                              .replace(" [APPROVED]", "")
                              .replace(" [REJECTED]", "")}
                        </span>
                        {doc.original_name && (
                          <p className="text-[10px] text-gray-400">
                            {doc.original_name}
                          </p>
                        )}
                        <a
                          href={path}
                          target="_blank"
                          rel="noreferrer"
                          className="text-primary hover:underline inline-flex items-center gap-1 text-[10px]"
                        >
                          View Document
                          <ExternalLink className="w-3 h-3" />
                        </a>
                      </div>

                      <div className="flex gap-2">
                        {isApproved ? (
                          <span className="inline-flex items-center gap-1 text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-extrabold border border-emerald-100">
                            Verified
                          </span>
                        ) : isRejected ? (
                          <span className="inline-flex items-center gap-1 text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-extrabold border border-rose-100">
                            Rejected
                          </span>
                        ) : isRequested ? (
                          <span className="inline-flex items-center gap-1 text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-extrabold border border-blue-100">
                            Requested
                          </span>
                        ) : (
                          <>
                            <button
                              onClick={() =>
                                handleDocumentAction(doc.id, "approve")
                              }
                              className="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[10px] flex items-center gap-1 cursor-pointer transition-all"
                            >
                              <CheckCircle className="w-3.5 h-3.5" />
                              Verify
                            </button>
                            <button
                              onClick={() =>
                                handleDocumentAction(doc.id, "reject")
                              }
                              className="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold text-[10px] flex items-center gap-1 cursor-pointer transition-all"
                            >
                              <XCircle className="w-3.5 h-3.5" />
                              Reject
                            </button>
                            <button
                              onClick={() =>
                                handleDocumentAction(doc.id, "request")
                              }
                              className="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-[10px] flex items-center gap-1 cursor-pointer transition-all"
                            >
                              Request
                            </button>
                          </>
                        )}
                      </div>
                    </div>
                  );
                })
              )}
            </div>
          </div>

          {/* Contracts & Billing */}
          <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
            <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
              <CreditCard className="w-5 h-5 text-navy" />
              <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
                Billing Contracts & Payments
              </h3>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Contracts */}
              <div className="space-y-3">
                <span className="text-[10px] text-gray-400 uppercase font-black tracking-wider block">
                  Active Contracts
                </span>
                {!application.contracts ||
                application.contracts.length === 0 ? (
                  <p className="text-xs font-semibold text-gray-400">
                    No contracts generated yet.
                  </p>
                ) : (
                  application.contracts.map((c) => (
                    <div
                      key={c.id}
                      className="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex justify-between items-center text-xs font-semibold"
                    >
                      <div>
                        <p className="font-bold text-navy">
                          #{c.contract_number}
                        </p>
                        <p className="text-[10px] text-gray-400 font-bold">
                          ${Number(c.amount).toLocaleString()}
                        </p>
                      </div>
                      <StatusBadge status={c.status} />
                    </div>
                  ))
                )}
              </div>

              {/* Payments */}
              <div className="space-y-3">
                <span className="text-[10px] text-gray-400 uppercase font-black tracking-wider block">
                  Billing Receipts
                </span>
                {!application.contracts ||
                application.contracts.length === 0 ? (
                  <p className="text-xs font-semibold text-gray-400">
                    No payment records found.
                  </p>
                ) : application.contracts.flatMap((c) => c.payments || [])
                    .length === 0 ? (
                  <p className="text-xs font-semibold text-gray-400">
                    No billing receipts found.
                  </p>
                ) : (
                  application.contracts
                    .flatMap((c) => c.payments || [])
                    .map((p) => (
                      <div
                        key={p.id}
                        className="p-3 bg-gray-50 rounded-2xl border border-gray-100 flex justify-between items-center text-xs font-semibold"
                      >
                        <div>
                          <p className="font-bold text-navy">
                            #{p.payment_number}
                          </p>
                          <p className="text-[10px] text-gray-400 font-bold">
                            ${Number(p.amount).toLocaleString()}
                          </p>
                        </div>
                        <StatusBadge status={p.status} />
                      </div>
                    ))
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Right Column: Status Controls & History Log */}
        <div className="space-y-6">
          {/* Status Controls */}
          <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
            <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
              <ClipboardList className="w-5 h-5 text-navy" />
              <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
                Update Decision
              </h3>
            </div>

            <form onSubmit={handleStatusUpdate} className="space-y-4">
              <div className="space-y-1">
                <label className="block text-[10px] font-extrabold text-navy uppercase tracking-wider">
                  Admissions Status
                </label>
                <select
                  value={status}
                  onChange={(e) => setStatus(e.target.value)}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold bg-white text-navy cursor-pointer"
                >
                  {statuses.map((s) => (
                    <option key={s} value={s}>
                      {s.replace("_", " ").toUpperCase()}
                    </option>
                  ))}
                </select>
              </div>

              <div className="space-y-1">
                <label className="block text-[10px] font-extrabold text-navy uppercase tracking-wider">
                  Internal Action Note
                </label>
                <textarea
                  value={comment}
                  onChange={(e) => setComment(e.target.value)}
                  placeholder="Approve passport credentials and mark for interview..."
                  rows={3}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold bg-white text-navy"
                />
              </div>

              {/* Notification Box toggle */}
              <div className="border-t border-gray-50 pt-4 space-y-3">
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={sendNotify}
                    onChange={(e) => setSendNotify(e.target.checked)}
                    className="w-4 h-4 text-primary border-gray-300 rounded-sm focus:ring-primary focus:ring-1"
                  />
                  <span className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                    Send Student Notification Alert
                  </span>
                </label>

                {sendNotify && (
                  <div className="space-y-2 border border-dashed border-gray-200 rounded-xl p-3 bg-gray-50/50">
                    <input
                      type="text"
                      value={notifyTitle}
                      onChange={(e) => setNotifyTitle(e.target.value)}
                      placeholder="Title"
                      className="w-full px-3 py-2 rounded-lg border border-gray-200 text-xs font-semibold bg-white text-navy"
                    />
                    <textarea
                      value={notifyMessage}
                      onChange={(e) => setNotifyMessage(e.target.value)}
                      placeholder="Message content..."
                      rows={3}
                      className="w-full px-3 py-2 rounded-lg border border-gray-200 text-xs font-semibold bg-white text-navy"
                    />
                  </div>
                )}
              </div>

              <button
                type="submit"
                disabled={updating}
                className="w-full bg-primary hover:bg-primary-hover text-white py-3 rounded-xl text-xs font-extrabold shadow-sm hover:shadow-md cursor-pointer transition-all flex items-center justify-center gap-1.5 mt-2"
              >
                {updating && <Loader2 className="w-4 h-4 animate-spin" />}
                Submit Decision
              </button>
            </form>
          </div>

          <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
            <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
              <CreditCard className="w-5 h-5 text-navy" />
              <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
                Contract & Payment Actions
              </h3>
            </div>

            <div className="space-y-3">
              <div className="flex gap-2">
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  value={contractAmount}
                  onChange={(e) => setContractAmount(e.target.value)}
                  placeholder="Contract amount"
                  className="min-w-0 flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold bg-white text-navy"
                />
                <button
                  type="button"
                  onClick={handleCreateContract}
                  disabled={updating}
                  className="px-3 py-2.5 bg-primary text-white rounded-xl text-[10px] font-extrabold cursor-pointer disabled:opacity-60"
                >
                  Create
                </button>
              </div>

              <div className="flex gap-2">
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  value={paymentAmount}
                  onChange={(e) => setPaymentAmount(e.target.value)}
                  placeholder="Payment amount"
                  className="min-w-0 flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold bg-white text-navy"
                />
                <button
                  type="button"
                  onClick={handleCreatePayment}
                  disabled={updating}
                  className="px-3 py-2.5 bg-primary text-white rounded-xl text-[10px] font-extrabold cursor-pointer disabled:opacity-60"
                >
                  Request
                </button>
              </div>

              <button
                type="button"
                onClick={handleMarkEnrolled}
                disabled={updating}
                className="w-full px-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold cursor-pointer disabled:opacity-60"
              >
                Mark Enrolled
              </button>
            </div>
          </div>

          {/* Status History Logs */}
          <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
            <div className="flex items-center gap-2 border-b border-gray-50 pb-4">
              <History className="w-5 h-5 text-navy" />
              <h3 className="font-extrabold text-navy text-sm uppercase tracking-wider">
                Status History
              </h3>
            </div>

            <div className="divide-y divide-gray-50 max-h-75 overflow-y-auto pr-1">
              {!application.status_histories ||
              application.status_histories.length === 0 ? (
                <p className="text-xs font-semibold text-gray-400 py-3">
                  No status changes logged.
                </p>
              ) : (
                application.status_histories.map((hist) => (
                  <div
                    key={hist.id}
                    className="py-3.5 space-y-1.5 text-xs font-semibold"
                  >
                    <div className="flex justify-between items-center">
                      <StatusBadge status={hist.new_status || hist.status} />
                      <span className="text-[10px] text-gray-400 font-bold">
                        {new Date(hist.created_at).toLocaleDateString()}
                      </span>
                    </div>
                    {(hist.note || hist.comment) && (
                      <p className="text-[11px] text-gray-500 font-medium leading-relaxed bg-gray-50 border border-gray-100 p-2.5 rounded-xl">
                        {hist.note || hist.comment}
                      </p>
                    )}
                    <p className="text-[9px] text-gray-400 font-bold uppercase tracking-wider">
                      Changed By User ID: #{hist.changed_by}
                    </p>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
