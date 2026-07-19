import React, { useState, useEffect } from "react";
import { applicationService } from "../../../services/applicationService";
import { useLanguage } from "../../../context/LanguageContext";
import { Loader2, CheckCircle2, Clock, FileText } from "lucide-react";

export default function StudentApplicationStatus() {
  const { t } = useLanguage();
  const [activeApp, setActiveApp] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchStatusHistory = async () => {
      try {
        setLoading(true);
        const apps = await applicationService.getApplications();
        const app =
          apps.find(
            (a) => a.status !== "graduated" && a.status !== "rejected",
          ) || apps[0];

        if (app) {
          // Fetch full application details including histories
          const details = await applicationService.getApplication(app.id);
          setActiveApp(details);
        }
      } catch (err) {
        console.error("Failed to load status history", err);
        setError("Failed to retrieve application status details.");
      } finally {
        setLoading(false);
      }
    };
    fetchStatusHistory();
  }, []);

  if (loading) {
    return <LoadingState message="Loading status timeline..." />;
  }

  if (!activeApp) {
    return (
      <div className="bg-white border border-gray-100 rounded-3xl p-8 text-center text-gray-400 font-bold shadow-xs">
        No active applications found. Please start an application first!
      </div>
    );
  }

  // Define full timeline points
  const timelineStages = [
    {
      key: "draft",
      label: "Draft Created",
      desc: "Select program options and save details",
    },
    {
      key: "submitted",
      label: "Submitted",
      desc: "Document pre-verification queue",
    },
    {
      key: "under_review",
      label: "Under Review",
      desc: "Academic board evaluation",
    },
    {
      key: "accepted",
      label: "Accepted",
      desc: "Admissions approval received",
    },
    {
      key: "enrolled",
      label: "Enrolled",
      desc: "Registered as active university student",
    },
  ];

  const currentStageIndex = timelineStages.findIndex(
    (s) => s.key === activeApp.status,
  );

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-200">
      <div>
        <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
          {t("application.trackingTitle", "Application Tracking System")}
        </h1>
        <p className="text-xs font-semibold text-gray-400">
          Track and monitor your registration progression steps
        </p>
      </div>

      {error && (
        <div className="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-600 text-xs font-bold">
          {error}
        </div>
      )}

      {/* Main card */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left 2 cols: Timeline */}
        <div className="lg:col-span-2 bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-6">
          <h3 className="text-sm font-extrabold text-navy uppercase tracking-wider pb-3 border-b border-gray-50 flex items-center gap-2">
            <Clock className="w-4.5 h-4.5 text-primary" />
            <span>Progress Stages</span>
          </h3>

          <div className="relative pl-6 border-l border-gray-100 ml-4 space-y-8 py-2">
            {timelineStages.map((stage, idx) => {
              const isPast = idx <= currentStageIndex;
              const isCurrent = activeApp.status === stage.key;

              return (
                <div key={stage.key} className="relative">
                  {/* Indicator Dot */}
                  <span
                    className={`absolute -left-10 top-0.5 w-8 h-8 rounded-full border flex items-center justify-center transition-all ${
                      isCurrent
                        ? "bg-primary text-white border-primary shadow-md shadow-primary/20 scale-110"
                        : isPast
                          ? "bg-emerald-55 text-white border-emerald-55"
                          : "bg-white text-gray-300 border-gray-200"
                    }`}
                  >
                    {isPast ? (
                      <CheckCircle2 className="w-4.5 h-4.5" />
                    ) : (
                      <Clock className="w-4 h-4" />
                    )}
                  </span>

                  <div className="space-y-1">
                    <h4
                      className={`text-xs font-black ${isCurrent ? "text-primary" : "text-navy"}`}
                    >
                      {stage.label}
                    </h4>
                    <p className="text-[11px] text-gray-400 font-semibold leading-relaxed">
                      {stage.desc}
                    </p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Right 1 col: History audit notes */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs flex flex-col justify-between space-y-4">
          <div className="space-y-4">
            <h3 className="text-sm font-extrabold text-navy uppercase tracking-wider pb-3 border-b border-gray-50 flex items-center gap-2">
              <FileText className="w-4.5 h-4.5 text-primary" />
              <span>Internal Comments</span>
            </h3>

            {activeApp.statusHistories &&
            activeApp.statusHistories.length > 0 ? (
              <div className="space-y-4 max-h-75 overflow-y-auto pr-1">
                {activeApp.statusHistories.map((hist) => (
                  <div
                    key={hist.id}
                    className="space-y-1 border-b border-gray-50 pb-3 last:border-0 last:pb-0"
                  >
                    <div className="flex justify-between items-center text-[10px] font-extrabold text-gray-400 uppercase">
                      <span>{hist.status.replace("_", " ")}</span>
                      <span>
                        {new Date(hist.created_at).toLocaleDateString()}
                      </span>
                    </div>
                    <p className="text-[11px] text-gray-600 font-bold leading-relaxed">
                      {hist.comment || "Status updated"}
                    </p>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-xs text-gray-400 font-bold text-center py-8">
                No comment notes registered yet.
              </p>
            )}
          </div>
        </div>
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
