import React, { useState, useEffect } from "react";
import { applicationService } from "../../../services/applicationService";
import { useLanguage } from "../../../context/LanguageContext";
import { Loader2, Upload, Trash2, ExternalLink } from "lucide-react";
import FormError from "../../../components/common/FormError";

export default function StudentDocuments() {
  const { t } = useLanguage();
  const [activeApp, setActiveApp] = useState(null);
  const [loading, setLoading] = useState(true);
  const [uploadingType, setUploadingType] = useState(null);
  const [deletingId, setDeletingId] = useState(null);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const docTypes = [
    {
      key: "passport",
      label: "Passport Scanned Copy *",
      desc: "Main information page with photo",
    },
    {
      key: "photo",
      label: "Applicant Passport Photo (3x4) *",
      desc: "Recent color photo with white background",
    },
    {
      key: "education_certificate",
      label: "Diploma / School Certificate *",
      desc: "Proof of completed educational stage",
    },
    {
      key: "transcript",
      label: "Academic Transcript *",
      desc: "List of grades and course evaluations",
    },
    {
      key: "medical_certificate",
      label: "Medical Certificate (Form 086)",
      desc: "General health clearance check",
    },
    {
      key: "language_certificate",
      label: "Language Proficiency Certificate",
      desc: "e.g., IELTS, TOEFL, CEFR (If applicable)",
    },
    {
      key: "payment_receipt",
      label: "Tuition / Application Fee Receipt",
      desc: "Proof of billing transaction payment slip",
    },
    {
      key: "other",
      label: "Other Documents",
      desc: "Any additional supplementary recommendations",
    },
  ];

  const loadDocuments = React.useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const apps = await applicationService.getApplications();
      const app =
        apps.find((a) => a.status !== "graduated" && a.status !== "rejected") ||
        apps[0];
      if (app) {
        // Retrieve full details with documents
        const details = await applicationService.getApplication(app.id);
        setActiveApp(details);
      }
    } catch {
      setError("Failed to fetch documents checklist.");
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadDocuments();
  }, [loadDocuments]);

  const handleFileUpload = async (e, typeKey) => {
    const file = e.target.files?.[0];
    if (!file || !activeApp) return;

    // Check size limit: max 10MB
    if (file.size > 10 * 1024 * 1024) {
      setError("File exceeds maximum allowed size (10 MB).");
      return;
    }

    try {
      setUploadingType(typeKey);
      setError("");
      setSuccess("");
      await applicationService.uploadDocument(activeApp.id, typeKey, file);
      setSuccess(`Document uploaded successfully!`);
      loadDocuments();
    } catch (err) {
      setError(err?.message || "Failed to upload file.");
    } finally {
      setUploadingType(null);
    }
  };

  const handleDelete = async (docId) => {
    if (!activeApp) return;
    try {
      setDeletingId(docId);
      setError("");
      setSuccess("");
      await applicationService.deleteDocument(activeApp.id, docId);
      setSuccess("Document deleted successfully!");
      loadDocuments();
    } catch {
      setError("Failed to delete document.");
    } finally {
      setDeletingId(null);
    }
  };

  if (loading) {
    return <LoadingState message="Loading documents checklist..." />;
  }

  if (!activeApp) {
    return (
      <div className="bg-white border border-gray-100 rounded-3xl p-8 text-center text-gray-400 font-bold shadow-xs">
        No active applications found. Please start an application draft first!
      </div>
    );
  }

  const getDocStatus = (docName) => {
    if (docName.startsWith("[APPROVED]"))
      return {
        label: "APPROVED",
        color: "bg-emerald-50 text-emerald-600 border-emerald-100",
      };
    if (docName.startsWith("[REJECTED]"))
      return {
        label: "REJECTED",
        color: "bg-rose-50 text-rose-600 border-rose-100",
      };
    return {
      label: "PENDING VERIFICATION",
      color: "bg-amber-50 text-amber-600 border-amber-100",
    };
  };

  const getNormalizedStatus = (doc) => {
    if (doc?.status === "approved")
      return {
        label: "APPROVED",
        color: "bg-emerald-50 text-emerald-600 border-emerald-100",
      };
    if (doc?.status === "rejected")
      return {
        label: "REJECTED",
        color: "bg-rose-50 text-rose-600 border-rose-100",
      };
    if (doc?.status === "requested")
      return {
        label: "REQUESTED",
        color: "bg-blue-50 text-blue-600 border-blue-100",
      };
    return getDocStatus(doc?.document_name || "");
  };

  const getCleanDocName = (docName) => {
    return docName.replace("[APPROVED]", "").replace("[REJECTED]", "").trim();
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-200">
      <div>
        <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
          {t("document.checklistTitle", "Application Documents Checklist")}
        </h1>
        <p className="text-xs font-semibold text-gray-400">
          Upload scan copies of required documents in PDF, PNG, or JPEG format
          (max 10MB per file)
        </p>
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-600 text-xs font-bold">
          {success}
        </div>
      )}

      {/* Grid List */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {docTypes.map((type) => {
          // Find matching uploaded document
          const uploadedDoc = activeApp.documents?.find(
            (d) =>
              (d.document_type || getCleanDocName(d.document_name)) ===
              type.key,
          );
          const status = uploadedDoc ? getNormalizedStatus(uploadedDoc) : null;

          return (
            <div
              key={type.key}
              className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs flex flex-col justify-between space-y-4"
            >
              <div className="space-y-1">
                <h3 className="text-xs font-black text-navy uppercase tracking-wider">
                  {type.label}
                </h3>
                <p className="text-[11px] text-gray-400 font-semibold leading-relaxed">
                  {type.desc}
                </p>
              </div>

              {uploadedDoc ? (
                <div className="space-y-3 pt-3 border-t border-gray-50">
                  <div className="flex justify-between items-center">
                    <span
                      className={`text-[9px] font-extrabold px-2 py-0.5 rounded-md border ${status.color}`}
                    >
                      {status.label}
                    </span>
                    <div className="flex gap-2">
                      <a
                        href={`/storage/${uploadedDoc.file_path}`}
                        target="_blank"
                        rel="noreferrer"
                        className="p-1.5 bg-gray-50 border border-gray-150 rounded-lg hover:text-primary transition-all cursor-pointer"
                        title="View Document File"
                      >
                        <ExternalLink className="w-3.5 h-3.5" />
                      </a>
                      {activeApp.status === "draft" &&
                        status.label !== "APPROVED" && (
                          <button
                            onClick={() => handleDelete(uploadedDoc.id)}
                            disabled={deletingId === uploadedDoc.id}
                            className="p-1.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg hover:bg-rose-100 transition-all cursor-pointer"
                            title="Delete File"
                          >
                            {deletingId === uploadedDoc.id ? (
                              <Loader2 className="w-3.5 h-3.5 animate-spin" />
                            ) : (
                              <Trash2 className="w-3.5 h-3.5" />
                            )}
                          </button>
                        )}
                    </div>
                  </div>
                </div>
              ) : (
                <div className="pt-3 border-t border-gray-50">
                  {activeApp.status === "draft" ? (
                    <label className="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-primary/10 text-primary border border-primary/20 hover:bg-primary/15 transition-all text-xs font-extrabold rounded-xl cursor-pointer">
                      {uploadingType === type.key ? (
                        <Loader2 className="w-4 h-4 animate-spin" />
                      ) : (
                        <>
                          <Upload className="w-4 h-4" />
                          <span>Upload File</span>
                        </>
                      )}
                      <input
                        type="file"
                        accept=".pdf,.png,.jpg,.jpeg"
                        onChange={(e) => handleFileUpload(e, type.key)}
                        className="hidden"
                        disabled={uploadingType !== null}
                      />
                    </label>
                  ) : (
                    <span className="text-[10px] text-gray-400 font-bold">
                      Application submitted. Upload locked.
                    </span>
                  )}
                </div>
              )}
            </div>
          );
        })}
      </div>
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
