import React, { useState, useEffect } from "react";
import { applicationService } from "../../../services/applicationService";
import { useLanguage } from "../../../context/LanguageContext";
import { formatLocalizedDate } from "../../../utils/dateFormat";
import { Loader2, CheckCircle2, Clock, FileText } from "lucide-react";
import StatusBadge from "../../apanel/components/StatusBadge";

export default function StudentApplicationStatus() {
  const { language, t, isRtl } = useLanguage();
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
        setError(t("application.statusLoadError"));
      } finally {
        setLoading(false);
      }
    };
    fetchStatusHistory();
  }, [t]);

  if (loading) {
    return <LoadingState message={t("application.statusLoading")} />;
  }

  if (!activeApp) {
    return (
      <div className="bg-white border border-gray-100 rounded-3xl p-8 text-center text-gray-400 font-bold shadow-xs">
        {t("application.noActiveStartFirst")}
      </div>
    );
  }

  // Define full timeline points
  const timelineStages = [
    {
      key: "draft",
      label: t("application.timeline.draft"),
      desc: t("application.timeline.draftDesc"),
    },
    {
      key: "submitted",
      label: t("application.timeline.submitted"),
      desc: t("application.timeline.submittedDesc"),
    },
    {
      key: "under_review",
      label: t("application.timeline.underReview"),
      desc: t("application.timeline.underReviewDesc"),
    },
    {
      key: "accepted",
      label: t("application.timeline.accepted"),
      desc: t("application.timeline.acceptedDesc"),
    },
    {
      key: "enrolled",
      label: t("application.timeline.enrolled"),
      desc: t("application.timeline.enrolledDesc"),
    },
  ];

  const currentStageIndex = timelineStages.findIndex(
    (s) => s.key === activeApp.status,
  );

  const formatDate = (value) =>
    formatLocalizedDate(value, language, t, {
      day: "numeric",
      month: "short",
    });

  return (
    <div className="mx-auto max-w-4xl min-w-0 space-y-6 animate-in fade-in duration-200">
      <div>
        <h1 className="text-xl sm:text-2xl font-extrabold text-navy uppercase tracking-wider break-words">
          {t("application.trackingTitle")}
        </h1>
        <p className="text-xs font-semibold text-gray-400">
          {t("application.trackingSubtitle")}
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
        <div className="lg:col-span-2 bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-xs space-y-6">
          <h3 className="text-sm font-extrabold text-navy uppercase tracking-wider pb-3 border-b border-gray-50 flex items-center gap-2">
            <Clock className="w-4.5 h-4.5 text-primary" />
            <span>{t("application.progressStages")}</span>
          </h3>

          <div className={`relative space-y-8 py-2 ${isRtl ? "me-4 border-e pe-6" : "ms-4 border-s ps-6"} border-gray-100`}>
            {timelineStages.map((stage, idx) => {
              const isPast = idx <= currentStageIndex;
              const isCurrent = activeApp.status === stage.key;

              return (
                <div key={stage.key} className="relative">
                  {/* Indicator Dot */}
                  <span
                    className={`absolute top-0.5 w-8 h-8 rounded-full border flex items-center justify-center transition-all ${
                      isRtl ? "-right-10" : "-left-10"
                    } ${
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
        <div className="bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
          <div className="space-y-4">
            <h3 className="text-sm font-extrabold text-navy uppercase tracking-wider pb-3 border-b border-gray-50 flex items-center gap-2">
              <FileText className="w-4.5 h-4.5 text-primary" />
              <span>{t("application.internalComments")}</span>
            </h3>

            {activeApp.statusHistories &&
            activeApp.statusHistories.length > 0 ? (
              <div className="space-y-4 max-h-75 overflow-y-auto pe-1">
                {activeApp.statusHistories.map((hist) => (
                  <div
                    key={hist.id}
                    className="space-y-1 border-b border-gray-50 pb-3 last:border-0 last:pb-0"
                  >
                    <div className="flex flex-col gap-1 text-[10px] font-extrabold text-gray-400 uppercase sm:flex-row sm:items-center sm:justify-between">
                      <StatusBadge status={hist.status} />
                      <span>
                        {formatDate(hist.created_at)}
                      </span>
                    </div>
                    <p className="text-[11px] text-gray-600 font-bold leading-relaxed break-words">
                      {hist.comment || t("application.statusUpdated")}
                    </p>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-xs text-gray-400 font-bold text-center py-8">
                {t("application.noComments")}
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
