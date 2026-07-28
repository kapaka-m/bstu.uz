import React, { useState, useEffect } from "react";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import { Loader2, MessageSquare, Send, HelpCircle, Plus } from "lucide-react";
import FormError from "../../../components/common/FormError";

export default function StudentSupport() {
  const { t } = useLanguage();
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const [subject, setSubject] = useState("");
  const [message, setMessage] = useState("");
  const [priority, setPriority] = useState("normal");

  const fetchTickets = React.useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const data = await studentService.getSupportTickets();
      setTickets(data);
    } catch {
      setError("Failed to retrieve support tickets history.");
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchTickets();
  }, [fetchTickets]);

  const handleSubmitTicket = async (e) => {
    e.preventDefault();
    if (!subject || !message) return;

    try {
      setSubmitting(true);
      setError("");
      setSuccess("");

      await studentService.createSupportTicket({ subject, message, priority });

      setSuccess(
        "Support ticket submitted! Our admissions team will respond shortly.",
      );
      setSubject("");
      setMessage("");
      setPriority("normal");
      fetchTickets();
    } catch (err) {
      setError(err?.message || "Failed to submit support request.");
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return <LoadingState message="Loading support center..." />;
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-200">
      <div>
        <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
          {t("support.title")}
        </h1>
        <p className="text-xs font-semibold text-gray-400">
          Open an inquiry ticket, check status, or communicate directly with
          Admissions
        </p>
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-600 text-xs font-bold">
          {success}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left col: Form */}
        <div className="lg:col-span-2 bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
          <h3 className="text-sm font-extrabold text-navy uppercase tracking-wider pb-3 border-b border-gray-50 flex items-center gap-2">
            <Plus className="w-4.5 h-4.5 text-primary" />
            <span>Create Support Request</span>
          </h3>

          <form onSubmit={handleSubmitTicket} className="space-y-4">
            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Inquiry Subject *
              </label>
              <input
                type="text"
                value={subject}
                onChange={(e) => setSubject(e.target.value)}
                placeholder="e.g., Question about document verification"
                required
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Priority Level
              </label>
              <select
                value={priority}
                onChange={(e) => setPriority(e.target.value)}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white cursor-pointer"
              >
                <option value="low">Low — General Query</option>
                <option value="normal">Normal — Standard Request</option>
                <option value="high">High — Urgent Issue</option>
              </select>
            </div>

            <div className="space-y-1">
              <label className="text-[10px] font-extrabold text-navy uppercase tracking-wider">
                Detailed Message *
              </label>
              <textarea
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder="Please describe your questions or issues in detail..."
                required
                rows={5}
                className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy bg-white"
              />
            </div>

            <div className="flex justify-end pt-2">
              <button
                type="submit"
                disabled={submitting}
                className="px-6 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-extrabold rounded-xl shadow-md hover:shadow-lg cursor-pointer transition-all flex items-center gap-1.5"
              >
                {submitting ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <>
                    <Send className="w-4 h-4" />
                    <span>Send Inquiry</span>
                  </>
                )}
              </button>
            </div>
          </form>
        </div>

        {/* Right col: History */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs flex flex-col justify-between space-y-4">
          <div className="space-y-4">
            <h3 className="text-sm font-extrabold text-navy uppercase tracking-wider pb-3 border-b border-gray-50 flex items-center gap-2">
              <MessageSquare className="w-4.5 h-4.5 text-primary" />
              <span>Request History</span>
            </h3>

            {tickets.length > 0 ? (
              <div className="space-y-4 max-h-75 overflow-y-auto pr-1">
                {tickets.map((t) => (
                  <div
                    key={t.id}
                    className="space-y-1 border-b border-gray-50 pb-3 last:border-0 last:pb-0"
                  >
                    <div className="flex justify-between items-center text-[10px] font-extrabold text-gray-400 uppercase">
                      <span>{t.priority?.toUpperCase() || "NORMAL"}</span>
                      <span>{new Date(t.created_at).toLocaleDateString()}</span>
                    </div>
                    <h4 className="text-xs font-black text-navy leading-snug">
                      {t.subject}
                    </h4>
                    <span
                      className={`inline-block text-[9px] font-extrabold px-1.5 py-0.5 rounded-md ${t.status === "open" ? "bg-amber-50 text-amber-600 border border-amber-100" : "bg-emerald-50 text-emerald-600 border border-emerald-100"}`}
                    >
                      {t.status?.toUpperCase() || "OPEN"}
                    </span>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8 text-gray-400 font-bold text-xs space-y-2">
                <HelpCircle className="w-8 h-8 text-gray-300 mx-auto" />
                <p>No support tickets opened.</p>
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
