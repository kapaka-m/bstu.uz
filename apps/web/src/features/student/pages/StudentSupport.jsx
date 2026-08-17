import React, { useState, useEffect } from "react";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import { useAuth } from "../../../context/AuthContext";
import { formatLocalizedDate } from "../../../utils/dateFormat";
import { Loader2, MessageSquare, Send, HelpCircle, Plus, UserRound, Building2 } from "lucide-react";
import FormError from "../../../components/common/FormError";
import StatusBadge from "../../apanel/components/StatusBadge";

export default function StudentSupport() {
  const { language, t, hasTranslation } = useLanguage();
  const { user } = useAuth();
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [replying, setReplying] = useState(false);
  const [selectedTicketId, setSelectedTicketId] = useState(null);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const [subject, setSubject] = useState("");
  const [message, setMessage] = useState("");
  const [priority, setPriority] = useState("normal");
  const [replyMessage, setReplyMessage] = useState("");

  const fetchTickets = React.useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const data = await studentService.getSupportTickets();
      const list = Array.isArray(data) ? data : [];
      setTickets(list);
      setSelectedTicketId((current) => current || list[0]?.id || null);
    } catch {
      setError(t("support.loadError"));
    } finally {
      setLoading(false);
    }
  }, [t]);

  useEffect(() => {
    fetchTickets();
  }, [fetchTickets]);

  const formatDate = (value) =>
    formatLocalizedDate(value, language, t, {
      day: "numeric",
      month: "short",
    });
  const priorityLabel = (value) => {
    const key = `support.priority.${value || "normal"}`;
    return hasTranslation?.(key) ? t(key) : value || t("support.priority.normalValue");
  };
  const label = (key, fallback) => (hasTranslation?.(key) ? t(key) : fallback);
  const selectedTicket =
    tickets.find((ticket) => ticket.id === selectedTicketId) || tickets[0] || null;

  const handleSubmitTicket = async (e) => {
    e.preventDefault();
    if (!subject || !message) return;

    try {
      setSubmitting(true);
      setError("");
      setSuccess("");

      const createdTicket = await studentService.createSupportTicket({ subject, message, priority });

      setSuccess(
        t("support.submitted"),
      );
      setSubject("");
      setMessage("");
      setPriority("normal");
      setSelectedTicketId(createdTicket?.id || null);
      await fetchTickets();
    } catch (err) {
      setError(err?.message || t("support.submitFailed"));
    } finally {
      setSubmitting(false);
    }
  };

  const handleReply = async (e) => {
    e.preventDefault();
    if (!selectedTicket || !replyMessage.trim()) return;

    try {
      setReplying(true);
      setError("");
      setSuccess("");
      const updatedTicket = await studentService.addSupportTicketMessage(selectedTicket.id, {
        message: replyMessage,
      });
      setTickets((prev) =>
        prev.map((ticket) => (ticket.id === updatedTicket?.id ? updatedTicket : ticket)),
      );
      setReplyMessage("");
      setSuccess(label("support.replySent", "Message sent successfully."));
    } catch (err) {
      setError(err?.message || label("support.replyFailed", "Failed to send message."));
    } finally {
      setReplying(false);
    }
  };

  if (loading) {
    return <LoadingState message={t("support.loading")} />;
  }

  return (
    <div className="mx-auto max-w-4xl min-w-0 space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 className="text-xl sm:text-2xl font-extrabold text-navy uppercase tracking-wider break-words">
            {label("support.messagesTitle", t("support.title"))}
          </h1>
          <p className="text-xs font-semibold text-gray-400">
            {label("support.messagesSubtitle", t("support.subtitle"))}
          </p>
        </div>
        <div className="inline-flex w-fit items-center gap-2 rounded-2xl border border-primary/15 bg-primary/5 px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-primary">
          <MessageSquare className="h-4 w-4" />
          {label("support.adminCommunication", "Student and administration communication")}
        </div>
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-600 text-xs font-bold">
          {success}
        </div>
      )}

      <div className="grid grid-cols-1 xl:grid-cols-5 gap-6">
        {/* Left col: Form */}
        <div className="min-w-0 xl:col-span-2 bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-xs space-y-4">
          <h3 className="text-sm font-extrabold text-navy uppercase tracking-wider pb-3 border-b border-gray-50 flex items-center gap-2">
            <Plus className="w-4.5 h-4.5 text-primary" />
            <span>{t("support.createRequest")}</span>
          </h3>

          <form onSubmit={handleSubmitTicket} className="space-y-4">
            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("support.inquirySubjectRequired")}
              </label>
              <input
                type="text"
                value={subject}
                onChange={(e) => setSubject(e.target.value)}
                placeholder={t("support.subjectPlaceholder")}
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("support.priorityLevel")}
              </label>
              <select
                value={priority}
                onChange={(e) => setPriority(e.target.value)}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white cursor-pointer"
              >
                <option value="low">{t("support.priority.low")}</option>
                <option value="normal">{t("support.priority.normal")}</option>
                <option value="high">{t("support.priority.high")}</option>
              </select>
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                {t("support.detailedMessageRequired")}
              </label>
              <textarea
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder={t("support.messagePlaceholder")}
                required
                rows={5}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="flex justify-stretch pt-2 sm:justify-end">
              <button
                type="submit"
                disabled={submitting}
                className="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-primary px-6 py-3 text-xs font-extrabold text-white shadow-md transition-all hover:bg-primary-hover hover:shadow-lg disabled:cursor-not-allowed sm:w-auto"
              >
                {submitting ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <>
                    <Send className="w-4 h-4" />
                    <span>{t("support.sendInquiry")}</span>
                  </>
                )}
              </button>
            </div>
          </form>
        </div>

        {/* Right col: Conversation */}
        <div className="min-w-0 xl:col-span-3 bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-xs space-y-5">
          <div className="flex flex-col gap-3 border-b border-gray-50 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="min-w-0">
              <h3 className="flex items-center gap-2 text-sm font-extrabold text-navy uppercase tracking-wider">
                <MessageSquare className="w-4.5 h-4.5 text-primary" />
                <span>{label("support.conversation", "Conversation")}</span>
              </h3>
              {selectedTicket && (
                <p className="mt-1 truncate text-[11px] font-bold text-gray-400">
                  {selectedTicket.subject}
                </p>
              )}
            </div>

            {tickets.length > 0 && (
              <select
                value={selectedTicket?.id || ""}
                onChange={(e) => setSelectedTicketId(Number(e.target.value))}
                className="w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-navy outline-none transition-all focus:border-primary sm:w-64"
              >
                {tickets.map((ticket) => (
                  <option key={ticket.id} value={ticket.id}>
                    #{ticket.id} - {ticket.subject}
                  </option>
                ))}
              </select>
            )}
          </div>

          {selectedTicket ? (
            <>
              <div className="student-sidebar-scroll max-h-[28rem] min-h-72 space-y-3 overflow-y-auto rounded-2xl bg-gray-50 p-3">
                {(selectedTicket.messages || []).length > 0 ? (
                  selectedTicket.messages.map((ticketMessage) => {
                    const fromStudent = Number(ticketMessage.user_id) === Number(user?.id);
                    return (
                      <div
                        key={ticketMessage.id}
                        className={`flex min-w-0 gap-2 ${fromStudent ? "justify-end" : "justify-start"}`}
                      >
                        <div
                          className={`flex max-w-[88%] min-w-0 gap-2 rounded-3xl px-4 py-3 shadow-2xs ${
                            fromStudent
                              ? "bg-primary text-white"
                              : "border border-gray-100 bg-white text-navy"
                          }`}
                        >
                          <span
                            className={`mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl ${
                              fromStudent ? "bg-white/15 text-white" : "bg-primary/10 text-primary"
                            }`}
                          >
                            {fromStudent ? (
                              <UserRound className="h-4 w-4" />
                            ) : (
                              <Building2 className="h-4 w-4" />
                            )}
                          </span>
                          <span className="min-w-0">
                            <span className="block text-[10px] font-black uppercase tracking-wider opacity-80">
                              {fromStudent
                                ? label("support.fromStudent", "Student")
                                : label("support.fromAdministration", "Administration")}
                            </span>
                            <span className="mt-1 block break-words text-xs font-semibold leading-relaxed">
                              {ticketMessage.message}
                            </span>
                            <span className="mt-2 block text-[10px] font-bold opacity-70">
                              {formatDate(ticketMessage.created_at)}
                            </span>
                          </span>
                        </div>
                      </div>
                    );
                  })
                ) : (
                  <div className="flex h-72 flex-col items-center justify-center text-center text-gray-400">
                    <HelpCircle className="h-9 w-9 text-gray-300" />
                    <p className="mt-2 text-xs font-bold">
                      {label("support.noMessages", "No messages yet.")}
                    </p>
                  </div>
                )}
              </div>

              <form onSubmit={handleReply} className="flex flex-col gap-3 sm:flex-row">
                <textarea
                  value={replyMessage}
                  onChange={(e) => setReplyMessage(e.target.value)}
                  rows={2}
                  placeholder={label("support.replyPlaceholder", "Write a reply to the administration...")}
                  className="min-h-12 grow rounded-2xl border border-gray-200 bg-white px-4 py-3 text-xs font-semibold text-navy outline-none transition-all focus:border-primary"
                />
                <button
                  type="submit"
                  disabled={replying || !replyMessage.trim()}
                  className="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-5 py-3 text-xs font-extrabold text-white shadow-md transition-all hover:bg-primary-hover disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {replying ? <Loader2 className="h-4 w-4 animate-spin" /> : <Send className="h-4 w-4" />}
                  {label("support.sendReply", "Send reply")}
                </button>
              </form>
            </>
          ) : (
            <div className="flex min-h-72 flex-col items-center justify-center rounded-2xl bg-gray-50 text-center text-gray-400">
              <HelpCircle className="h-9 w-9 text-gray-300" />
              <p className="mt-2 text-xs font-bold">{t("support.noTickets")}</p>
            </div>
          )}
        </div>

        {/* Full-width ticket history */}
        <div className="min-w-0 xl:col-span-5 bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
          <div className="space-y-4">
            <h3 className="text-sm font-extrabold text-navy uppercase tracking-wider pb-3 border-b border-gray-50 flex items-center gap-2">
              <MessageSquare className="w-4.5 h-4.5 text-primary" />
              <span>{t("support.requestHistory")}</span>
            </h3>

            {tickets.length > 0 ? (
              <div className="space-y-4 max-h-75 overflow-y-auto pe-1">
                {tickets.map((ticket) => (
                  <div
                    key={ticket.id}
                    className="space-y-1 border-b border-gray-50 pb-3 last:border-0 last:pb-0"
                  >
                    <div className="flex flex-col gap-1 text-[10px] font-extrabold text-gray-400 uppercase sm:flex-row sm:items-center sm:justify-between">
                      <span className="break-words">{priorityLabel(ticket.priority)}</span>
                      <span>{formatDate(ticket.created_at)}</span>
                    </div>
                    <h4 className="text-xs font-black text-navy leading-snug break-words">
                      {ticket.subject}
                    </h4>
                    <StatusBadge status={ticket.status || "open"} />
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8 text-gray-400 font-bold text-xs space-y-2">
                <HelpCircle className="w-8 h-8 text-gray-300 mx-auto" />
                <p>{t("support.noTickets")}</p>
              </div>
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
