import React, { useCallback, useEffect, useMemo, useState } from "react";
import {
  CalendarClock,
  Loader2,
  Mail,
  Send,
  RefreshCw,
  Search,
  Trash2,
  Users,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import { formatLocalizedDate } from "../../../utils/dateFormat";
import ConfirmDialog from "../components/ConfirmDialog";
import { useApanelLocaleCodes } from "../utils/locales";
import { useLanguage } from "../../../context/LanguageContext";

function formatDate(value, locale, translate) {
  if (!value) return "—";
  return formatLocalizedDate(value, locale, translate, {
    day: "2-digit",
    month: "short",
    hour: "2-digit",
    minute: "2-digit",
  });
}

export default function ApanelNewsletterSubscriptions() {
  const { t } = useLanguage();
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || undefined;
  const [items, setItems] = useState([]);
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [deletingId, setDeletingId] = useState(null);
  const [pendingDelete, setPendingDelete] = useState(null);
  const [activeTab, setActiveTab] = useState("subscribers");
  const [sending, setSending] = useState(false);
  const [success, setSuccess] = useState("");
  const [campaign, setCampaign] = useState({
    subject: "",
    title: "",
    message: "",
    cta_label: "",
    cta_url: "",
    locale: "",
  });

  const fetchSubscriptions = useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const pageData = await apanelService.listPage(
        "newsletter-subscriptions",
        {
          search,
          page,
          sort_by: "subscribed_at",
          sort_dir: "desc",
        },
      );
      setItems(pageData.items);
      setTotal(pageData.total);
      setLastPage(pageData.lastPage);
    } catch (err) {
      setError(err?.message || t("apanel.newsletter.loadFailed"));
    } finally {
      setLoading(false);
    }
  }, [page, search, t]);

  useEffect(() => {
    fetchSubscriptions();
  }, [fetchSubscriptions]);

  const activeCount = useMemo(
    () => items.filter((item) => item.status === "active").length,
    [items],
  );

  const latestDate = items[0]?.subscribed_at || items[0]?.created_at;

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    try {
      setDeletingId(pendingDelete.id);
      await apanelService.delete("newsletter-subscriptions", pendingDelete.id);
      setPendingDelete(null);
      fetchSubscriptions();
    } catch (err) {
      setError(err?.message || t("apanel.newsletter.deleteFailed"));
    } finally {
      setDeletingId(null);
    }
  };

  const sendCampaign = async (event) => {
    event.preventDefault();
    try {
      setSending(true);
      setError("");
      setSuccess("");
      const response = await apanelService.sendNewsletterCampaign(campaign);
      setSuccess(`Newsletter campaign sent to ${response?.sent_count ?? 0} subscribers.`);
      setCampaign({
        subject: "",
        title: "",
        message: "",
        cta_label: "",
        cta_url: "",
        locale: "",
      });
    } catch (err) {
      setError(err?.message || "Failed to send newsletter campaign.");
    } finally {
      setSending(false);
    }
  };

  const updateCampaign = (key, value) => {
    setCampaign((current) => ({ ...current, [key]: value }));
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            {t("apanel.newsletter.title")}
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            {t("apanel.newsletter.subtitle")}
          </p>
        </div>
        <button
          type="button"
          onClick={fetchSubscriptions}
          className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-navy text-xs font-extrabold hover:bg-gray-50 cursor-pointer"
        >
          <RefreshCw className="w-4 h-4" />
          {t("button.refresh")}
        </button>
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
          {success}
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {[
          { label: t("apanel.newsletter.totalSubscribers"), value: total, icon: Users },
          { label: t("apanel.newsletter.activeOnPage"), value: activeCount, icon: Mail },
          {
            label: t("apanel.newsletter.latestSubscription"),
            value: latestDate ? formatDate(latestDate, primaryLocale, t) : "—",
            icon: CalendarClock,
          },
        ].map((stat) => {
          const Icon = stat.icon;
          return (
            <div
              key={stat.label}
              className="bg-white border border-gray-100 rounded-3xl p-5 shadow-xs flex items-center gap-4"
            >
              <div className="w-11 h-11 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                <Icon className="w-5 h-5" />
              </div>
              <div>
                <p className="text-[10px] uppercase tracking-wider font-black text-gray-400">
                  {stat.label}
                </p>
                <p className="text-lg font-extrabold text-navy mt-1">
                  {stat.value}
                </p>
              </div>
            </div>
          );
        })}
      </div>

      <div className="flex flex-wrap gap-2 rounded-3xl border border-gray-100 bg-white p-4 shadow-xs">
        {[
          ["subscribers", "Subscribers"],
          ["send", "Send Campaign"],
        ].map(([key, label]) => (
          <button
            type="button"
            key={key}
            onClick={() => setActiveTab(key)}
            className={`rounded-xl px-4 py-2 text-xs font-extrabold ${
              activeTab === key
                ? "bg-primary text-white"
                : "bg-gray-50 text-gray-600 hover:bg-primary/10 hover:text-primary"
            }`}
          >
            {label}
          </button>
        ))}
      </div>

      {activeTab === "send" && (
        <form onSubmit={sendCampaign} className="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs">
          <div className="mb-6">
            <h2 className="text-xl font-black text-navy">Send Newsletter Campaign</h2>
            <p className="mt-1 text-xs font-bold text-gray-400">
              Sends a professional email template to active newsletter subscribers.
            </p>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <label className="text-xs font-bold text-gray-500">
              Subject
              <input
                required
                value={campaign.subject}
                onChange={(event) => updateCampaign("subject", event.target.value)}
                className="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none"
              />
            </label>
            <label className="text-xs font-bold text-gray-500">
              Title
              <input
                required
                value={campaign.title}
                onChange={(event) => updateCampaign("title", event.target.value)}
                className="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none"
              />
            </label>
            <label className="text-xs font-bold text-gray-500 md:col-span-2">
              Message
              <textarea
                required
                rows={6}
                value={campaign.message}
                onChange={(event) => updateCampaign("message", event.target.value)}
                className="mt-1 w-full resize-y rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none"
              />
            </label>
            <label className="text-xs font-bold text-gray-500">
              CTA Label
              <input
                value={campaign.cta_label}
                onChange={(event) => updateCampaign("cta_label", event.target.value)}
                className="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none"
              />
            </label>
            <label className="text-xs font-bold text-gray-500">
              CTA URL
              <input
                value={campaign.cta_url}
                onChange={(event) => updateCampaign("cta_url", event.target.value)}
                className="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none"
              />
            </label>
            <label className="text-xs font-bold text-gray-500">
              Locale Filter
              <select
                value={campaign.locale}
                onChange={(event) => updateCampaign("locale", event.target.value)}
                className="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-navy focus:border-primary focus:outline-none"
              >
                <option value="">All active subscribers</option>
                {localeCodes.map((locale) => (
                  <option key={locale} value={locale}>
                    {locale.toUpperCase()}
                  </option>
                ))}
              </select>
            </label>
          </div>
          <div className="mt-6 flex justify-end">
            <button
              type="submit"
              disabled={sending}
              className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-primary/20 disabled:opacity-60"
            >
              {sending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Send className="h-4 w-4" />}
              {sending ? "Sending..." : "Send Campaign"}
            </button>
          </div>
        </form>
      )}

      {activeTab === "subscribers" && (
      <section className="bg-white border border-gray-100 rounded-3xl shadow-xs overflow-hidden">
        <div className="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center gap-3 justify-between">
          <div className="relative w-full md:max-w-sm">
            <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="search"
              value={search}
              onChange={(event) => {
                setSearch(event.target.value);
                setPage(1);
              }}
              placeholder={t("apanel.newsletter.searchPlaceholder")}
              className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy"
            />
          </div>
          <p className="text-xs font-bold text-gray-400">
            {t("apanel.newsletter.page")} {page} {t("apanel.newsletter.of")} {lastPage}
          </p>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full min-w-200 text-start">
            <thead className="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-400 font-black">
              <tr>
                <th className="px-5 py-3 text-start">{t("form.email")}</th>
                <th className="px-5 py-3 text-start">{t("apanel.newsletter.locale")}</th>
                <th className="px-5 py-3 text-start">{t("apanel.newsletter.status")}</th>
                <th className="px-5 py-3 text-start">{t("apanel.newsletter.subscribed")}</th>
                <th className="px-5 py-3 text-start">{t("apanel.newsletter.ipAddress")}</th>
                <th className="px-5 py-3 text-end">{t("apanel.newsletter.actions")}</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan="6" className="px-5 py-12 text-center">
                    <Loader2 className="w-7 h-7 animate-spin text-primary mx-auto" />
                  </td>
                </tr>
              ) : items.length === 0 ? (
                <tr>
                  <td
                    colSpan="6"
                    className="px-5 py-12 text-center text-sm font-bold text-gray-400"
                  >
                    {t("apanel.newsletter.empty")}
                  </td>
                </tr>
              ) : (
                items.map((item) => (
                  <tr key={item.id} className="hover:bg-gray-50/70">
                    <td className="px-5 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-primary-light text-primary flex items-center justify-center">
                          <Mail className="w-4 h-4" />
                        </div>
                        <span className="text-sm font-extrabold text-navy">
                          {item.email}
                        </span>
                      </div>
                    </td>
                    <td className="px-5 py-4 text-xs font-bold text-gray-500 uppercase">
                      {item.locale || "—"}
                    </td>
                    <td className="px-5 py-4">
                      <span className="inline-flex px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-wider">
                        {item.status || "active"}
                      </span>
                    </td>
                    <td className="px-5 py-4 text-xs font-semibold text-gray-500">
                      {formatDate(item.subscribed_at || item.created_at, primaryLocale, t)}
                    </td>
                    <td className="px-5 py-4 text-xs font-semibold text-gray-500">
                      {item.ip_address || "—"}
                    </td>
                    <td className="px-5 py-4 text-end">
                      <button
                        type="button"
                        onClick={() => setPendingDelete(item)}
                        disabled={deletingId === item.id}
                        className="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-rose-100 text-rose-600 hover:bg-rose-50 disabled:opacity-60 cursor-pointer"
                        aria-label={`${t("button.delete")} ${item.email}`}
                      >
                        {deletingId === item.id ? (
                          <Loader2 className="w-4 h-4 animate-spin" />
                        ) : (
                          <Trash2 className="w-4 h-4" />
                        )}
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="p-5 border-t border-gray-100 flex items-center justify-between">
          <button
            type="button"
            onClick={() => setPage((current) => Math.max(1, current - 1))}
            disabled={page <= 1}
            className="px-4 py-2 rounded-xl border border-gray-200 text-xs font-extrabold text-navy disabled:opacity-40 cursor-pointer"
          >
            {t("button.previous")}
          </button>
          <button
            type="button"
            onClick={() =>
              setPage((current) => Math.min(lastPage, current + 1))
            }
            disabled={page >= lastPage}
            className="px-4 py-2 rounded-xl border border-gray-200 text-xs font-extrabold text-navy disabled:opacity-40 cursor-pointer"
          >
            {t("button.next")}
          </button>
        </div>
      </section>
      )}
      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title={t("apanel.newsletter.deleteTitle")}
        message={`${t("apanel.newsletter.deleteMessage")} ${pendingDelete?.email || t("apanel.newsletter.thisSubscription")}`}
        confirmText={deletingId ? t("apanel.newsletter.deleting") : t("button.delete")}
        cancelText={t("button.cancel")}
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
