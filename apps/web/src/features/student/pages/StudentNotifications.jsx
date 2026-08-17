import React, { useState, useEffect } from "react";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import { formatLocalizedDate } from "../../../utils/dateFormat";
import { Loader2, Bell, Clock, CheckCheck, BellOff } from "lucide-react";

export default function StudentNotifications() {
  const { language, t } = useLanguage();
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [markingAll, setMarkingAll] = useState(false);

  const fetchNotifications = React.useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const data = await studentService.getNotifications();
      setNotifications(data);
    } catch (err) {
      console.error("Failed to load notifications", err);
      setError(t("notification.loadError"));
    } finally {
      setLoading(false);
    }
  }, [t]);

  useEffect(() => {
    fetchNotifications();
  }, [fetchNotifications]);

  const handleMarkRead = async (id) => {
    try {
      await studentService.markNotificationRead(id);
      setNotifications((prev) =>
        prev.map((n) => (n.id === id ? { ...n, is_read: true } : n)),
      );
    } catch (err) {
      console.error("Failed to mark notification as read", err);
    }
  };

  const handleMarkAllRead = async () => {
    try {
      setMarkingAll(true);
      await studentService.markAllNotificationsRead();
      setNotifications((prev) => prev.map((n) => ({ ...n, is_read: true })));
    } catch (err) {
      console.error("Failed to mark all as read", err);
    } finally {
      setMarkingAll(false);
    }
  };

  const unreadCount = notifications.filter((n) => !n.is_read).length;
  const translateMaybe = (value) => {
    if (!value || !String(value).includes(".")) return value;
    return t(value);
  };
  const formatDate = (value) =>
    formatLocalizedDate(value, language, t, {
      day: "numeric",
      month: "short",
    });

  if (loading) {
    return (
      <div className="flex flex-col items-center justify-center min-h-75 space-y-3">
        <Loader2 className="w-8 h-8 text-primary animate-spin" />
        <span className="text-xs font-bold text-navy select-none">
          {t("notification.loading")}
        </span>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-4xl min-w-0 space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-navy uppercase tracking-wider break-words">
            {t("student.notifications")}
          </h1>
          <p className="text-xs font-semibold text-gray-400">
            {t("student.notificationsSubtitle")}
          </p>
        </div>

        {unreadCount > 0 && (
          <button
            onClick={handleMarkAllRead}
            disabled={markingAll}
            className="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-primary/20 bg-primary/10 px-4 py-2.5 text-xs font-extrabold text-primary transition-all hover:bg-primary/20 disabled:cursor-not-allowed sm:w-auto"
          >
            {markingAll ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <CheckCheck className="w-4 h-4" />
            )}
            {t("button.markAllRead")} ({unreadCount})
          </button>
        )}
      </div>

      {error && (
        <div className="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-600 text-xs font-bold">
          {error}
        </div>
      )}

      <div className="min-w-0 bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-xs space-y-4">
        {notifications.length > 0 ? (
          <div className="space-y-3">
            {notifications.map((notif) => (
              <div
                key={notif.id}
                className={`flex min-w-0 gap-4 p-4 rounded-2xl border transition-all ${
                  notif.is_read
                    ? "border-gray-50 bg-white"
                    : "border-primary/20 bg-primary/5 shadow-sm"
                }`}
              >
                <div
                  className={`w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 ${
                    notif.is_read
                      ? "bg-gray-100 text-gray-400"
                      : "bg-primary/15 text-primary"
                  }`}
                >
                  <Bell className="w-5 h-5" />
                </div>

                <div className="space-y-1.5 grow min-w-0">
                  <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <h3
                      className={`min-w-0 text-xs font-black uppercase tracking-wider break-words ${
                        notif.is_read ? "text-gray-500" : "text-navy"
                      }`}
                    >
                      {translateMaybe(notif.title)}
                    </h3>
                    <div className="flex flex-wrap items-center gap-2 shrink-0">
                      <span className="text-[9px] text-gray-400 font-bold flex items-center gap-1">
                        <Clock className="w-3 h-3" />
                        {formatDate(notif.created_at)}
                      </span>
                      {!notif.is_read && (
                        <button
                          onClick={() => handleMarkRead(notif.id)}
                          className="text-[10px] font-extrabold text-primary hover:text-primary-hover underline cursor-pointer whitespace-nowrap"
                        >
                          {t("button.markRead")}
                        </button>
                      )}
                    </div>
                  </div>
                  <p className="text-xs text-gray-600 font-semibold leading-relaxed break-words">
                    {translateMaybe(notif.message)}
                  </p>
                  {!notif.is_read && (
                    <span className="inline-flex items-center gap-1 text-[9px] font-extrabold text-primary uppercase tracking-wider bg-primary/10 px-2 py-0.5 rounded-full">
                      {t("notification.unread")}
                    </span>
                  )}
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-14 text-gray-400 font-bold text-xs space-y-2">
            <BellOff className="w-10 h-10 text-gray-200 mx-auto" />
            <p className="text-sm font-semibold text-gray-400">
              {t("notification.none")}
            </p>
            <p className="text-[11px] text-gray-300 font-medium max-w-xs mx-auto">
              {t("notification.noneHint")}
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
