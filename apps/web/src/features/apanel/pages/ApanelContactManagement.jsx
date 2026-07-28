import React, { useCallback, useEffect, useMemo, useState } from "react";
import {
  CheckCircle2,
  Clock3,
  Loader2,
  Mail,
  MailOpen,
  RefreshCw,
  Reply,
  Search,
  Trash2,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";
import ConfirmDialog from "../components/ConfirmDialog";
import { useApanelLocaleCodes } from "../utils/locales";

const statusOptions = [
  { value: "", label: "All messages" },
  { value: "pending", label: "Pending" },
  { value: "read", label: "Read" },
  { value: "replied", label: "Replied" },
  { value: "resolved", label: "Resolved" },
  { value: "archived", label: "Archived" },
];

function formatDate(value, locale) {
  if (!value) return "-";
  return new Intl.DateTimeFormat(locale, {
    year: "numeric",
    month: "short",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  }).format(new Date(value));
}

function normalizeMessage(item) {
  return {
    id: item.id,
    name: item.name || "",
    email: item.email || "",
    subject: item.subject || "",
    message: item.message || "",
    status: item.status || "pending",
    read_at: item.read_at || null,
    reply_message: item.reply_message || "",
    replied_at: item.replied_at || null,
    admin_notes: item.admin_notes || "",
    created_at: item.created_at || null,
  };
}

export default function ApanelContactManagement() {
  const localeCodes = useApanelLocaleCodes();
  const primaryLocale = localeCodes[0] || undefined;
  const [items, setItems] = useState([]);
  const [selected, setSelected] = useState(null);
  const [search, setSearch] = useState("");
  const [status, setStatus] = useState("");
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [pendingDelete, setPendingDelete] = useState(null);

  const fetchMessages = useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const pageData = await apanelService.listPage("inquiries", {
        search,
        status,
        page,
        sort_by: "created_at",
        sort_dir: "desc",
      });
      const nextItems = pageData.items.map(normalizeMessage);
      setItems(nextItems);
      setTotal(pageData.total);
      setLastPage(pageData.lastPage);
      setSelected((current) => {
        if (!current) return nextItems[0] || null;
        return nextItems.find((item) => item.id === current.id) || nextItems[0] || null;
      });
    } catch (err) {
      setError(err?.message || "Failed to load contact messages.");
    } finally {
      setLoading(false);
    }
  }, [page, search, status]);

  useEffect(() => {
    fetchMessages();
  }, [fetchMessages]);

  const stats = useMemo(
    () => ({
      unread: items.filter((item) => item.status === "pending" || !item.read_at).length,
      replied: items.filter((item) => item.status === "replied").length,
      latest: items[0]?.created_at,
    }),
    [items],
  );

  const updateSelectedField = (field, value) => {
    setSelected((current) => (current ? { ...current, [field]: value } : current));
  };

  const saveSelected = async (overrides = {}) => {
    if (!selected) return;
    const payload = {
      ...selected,
      ...overrides,
    };

    try {
      setSaving(true);
      setError("");
      setSuccess("");
      const saved = normalizeMessage(await apanelService.update("inquiries", selected.id, payload));
      setSelected(saved);
      setItems((current) => current.map((item) => (item.id === saved.id ? saved : item)));
      setSuccess("Contact message updated successfully.");
    } catch (err) {
      setError(err?.message || "Failed to update contact message.");
    } finally {
      setSaving(false);
    }
  };

  const markRead = () => {
    saveSelected({
      status: selected?.status === "pending" ? "read" : selected?.status,
      read_at: selected?.read_at || new Date().toISOString(),
    });
  };

  const saveReply = () => {
    saveSelected({
      status: "replied",
      read_at: selected?.read_at || new Date().toISOString(),
      replied_at: selected?.replied_at || new Date().toISOString(),
    });
  };

  const confirmDelete = async () => {
    if (!pendingDelete) return;
    try {
      setSaving(true);
      await apanelService.delete("inquiries", pendingDelete.id);
      setPendingDelete(null);
      setSelected(null);
      await fetchMessages();
      setSuccess("Contact message deleted successfully.");
    } catch (err) {
      setError(err?.message || "Failed to delete contact message.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 className="mt-1 text-2xl font-black uppercase tracking-wider text-navy">
            Contact Messages
          </h1>
          <p className="mt-1 text-xs font-semibold text-gray-400">
            Read, classify, and reply to inquiries submitted from the public
            Contact page.
          </p>
        </div>
        <button
          type="button"
          onClick={fetchMessages}
          className="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-extrabold text-navy hover:bg-gray-50"
        >
          <RefreshCw className="h-4 w-4" />
          Refresh
        </button>
      </div>

      {error && <FormError message={error} />}
      {success && (
        <div className="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
          {success}
        </div>
      )}

      <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
        {[
          { label: "Total messages", value: total, icon: Mail },
          { label: "Unread on this page", value: stats.unread, icon: MailOpen },
          {
            label: "Latest message",
            value: stats.latest ? formatDate(stats.latest, primaryLocale) : "-",
            icon: Clock3,
          },
        ].map((stat) => {
          const Icon = stat.icon;
          return (
            <div
              key={stat.label}
              className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
            >
              <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <Icon className="h-5 w-5" />
              </div>
              <p className="text-xs font-extrabold uppercase tracking-widest text-gray-400">
                {stat.label}
              </p>
              <p className="mt-1 text-2xl font-black text-navy">{stat.value}</p>
            </div>
          );
        })}
      </div>

      <div className="grid grid-cols-1 gap-6 xl:grid-cols-[420px_1fr]">
        <div className="rounded-3xl border border-gray-100 bg-white shadow-sm">
          <div className="space-y-3 border-b border-gray-100 p-4">
            <div className="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2">
              <Search className="h-4 w-4 text-gray-400" />
              <input
                id="apanel-contact-search"
                name="apanel_contact_search"
                value={search}
                onChange={(event) => {
                  setPage(1);
                  setSearch(event.target.value);
                }}
                placeholder="Search messages..."
                className="w-full text-sm font-semibold text-gray-700 outline-none"
              />
            </div>
            <select
              value={status}
              onChange={(event) => {
                setPage(1);
                setStatus(event.target.value);
              }}
              className="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-bold text-gray-600 outline-none focus:border-primary"
            >
              {statusOptions.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </div>

          <div className="max-h-160 overflow-y-auto p-3">
            {loading ? (
              <div className="flex items-center justify-center gap-2 p-8 text-sm font-bold text-gray-400">
                <Loader2 className="h-4 w-4 animate-spin" />
                Loading messages...
              </div>
            ) : items.length === 0 ? (
              <div className="p-8 text-center text-sm font-bold text-gray-400">
                No contact messages found.
              </div>
            ) : (
              <div className="space-y-2">
                {items.map((item) => (
                  <button
                    key={item.id}
                    type="button"
                    onClick={() => setSelected(item)}
                    className={`w-full rounded-2xl border p-4 text-start transition ${
                      selected?.id === item.id
                        ? "border-primary bg-primary/5"
                        : "border-gray-100 bg-gray-50 hover:border-primary/30"
                    }`}
                  >
                    <div className="mb-2 flex items-center justify-between gap-3">
                      <span className="truncate text-sm font-black text-navy">
                        {item.subject}
                      </span>
                      <span className="rounded-full bg-white px-2 py-1 text-[10px] font-black uppercase text-gray-500">
                        {item.status}
                      </span>
                    </div>
                    <p className="truncate text-xs font-bold text-gray-500">
                      {item.name} · {item.email}
                    </p>
                    <p className="mt-2 line-clamp-2 text-xs font-semibold leading-relaxed text-gray-400">
                      {item.message}
                    </p>
                    <p className="mt-3 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
                      {formatDate(item.created_at, primaryLocale)}
                    </p>
                  </button>
                ))}
              </div>
            )}
          </div>

          <div className="flex items-center justify-between border-t border-gray-100 p-4 text-xs font-bold text-gray-500">
            <button
              type="button"
              disabled={page <= 1}
              onClick={() => setPage((current) => Math.max(1, current - 1))}
              className="rounded-xl border border-gray-200 px-3 py-2 disabled:opacity-40"
            >
              Previous
            </button>
            <span>
              Page {page} of {lastPage}
            </span>
            <button
              type="button"
              disabled={page >= lastPage}
              onClick={() =>
                setPage((current) => Math.min(lastPage, current + 1))
              }
              className="rounded-xl border border-gray-200 px-3 py-2 disabled:opacity-40"
            >
              Next
            </button>
          </div>
        </div>

        <div className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
          {!selected ? (
            <div className="flex min-h-96 items-center justify-center text-sm font-bold text-gray-400">
              Select a message to read and reply.
            </div>
          ) : (
            <div className="space-y-5">
              <div className="flex flex-col gap-3 border-b border-gray-100 pb-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                  <p className="text-xs font-extrabold uppercase tracking-widest text-primary">
                    Message #{selected.id}
                  </p>
                  <h2 className="mt-1 text-2xl font-black text-navy">
                    {selected.subject}
                  </h2>
                  <p className="mt-2 text-sm font-bold text-gray-500">
                    {selected.name} ·{" "}
                    <a
                      href={`mailto:${selected.email}`}
                      className="text-primary hover:underline"
                    >
                      {selected.email}
                    </a>
                  </p>
                  <p className="mt-1 text-xs font-semibold text-gray-400">
                    Submitted {formatDate(selected.created_at, primaryLocale)}
                  </p>
                </div>
                <div className="flex flex-wrap gap-2">
                  <button
                    type="button"
                    onClick={markRead}
                    disabled={saving}
                    className="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-xs font-extrabold text-navy hover:bg-gray-50 disabled:opacity-60"
                  >
                    <CheckCircle2 className="h-4 w-4" />
                    Mark Read
                  </button>
                  <button
                    type="button"
                    onClick={() => setPendingDelete(selected)}
                    disabled={saving}
                    className="inline-flex items-center gap-2 rounded-xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs font-extrabold text-rose-600 disabled:opacity-60"
                  >
                    <Trash2 className="h-4 w-4" />
                    Delete
                  </button>
                </div>
              </div>

              <div className="rounded-2xl bg-gray-50 p-5">
                <p className="mb-2 text-xs font-extrabold uppercase tracking-widest text-gray-400">
                  Message
                </p>
                <p className="whitespace-pre-line text-sm font-semibold leading-relaxed text-gray-700">
                  {selected.message}
                </p>
              </div>

              <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
                  Status
                  <select
                    value={selected.status}
                    onChange={(event) =>
                      updateSelectedField("status", event.target.value)
                    }
                    className="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-bold text-gray-700 outline-none focus:border-primary"
                  >
                    {statusOptions
                      .filter((option) => option.value)
                      .map((option) => (
                        <option key={option.value} value={option.value}>
                          {option.label}
                        </option>
                      ))}
                  </select>
                </label>
                <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
                  Admin Notes
                  <textarea
                    value={selected.admin_notes}
                    onChange={(event) =>
                      updateSelectedField("admin_notes", event.target.value)
                    }
                    rows={4}
                    className="mt-2 w-full resize-y rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 outline-none focus:border-primary"
                  />
                </label>
              </div>

              <label className="block text-xs font-extrabold uppercase tracking-wide text-gray-400">
                Reply Message
                <textarea
                  value={selected.reply_message}
                  onChange={(event) =>
                    updateSelectedField("reply_message", event.target.value)
                  }
                  rows={7}
                  placeholder="Write the reply that should be recorded for this inquiry..."
                  className="mt-2 w-full resize-y rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold leading-relaxed text-gray-700 outline-none focus:border-primary"
                />
              </label>

              <div className="flex flex-wrap justify-end gap-3">
                <button
                  type="button"
                  onClick={() => saveSelected()}
                  disabled={saving}
                  className="rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-extrabold text-navy hover:bg-gray-50 disabled:opacity-60"
                >
                  Save Changes
                </button>
                <button
                  type="button"
                  onClick={saveReply}
                  disabled={saving}
                  className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-extrabold text-white shadow-sm hover:bg-primary-hover disabled:opacity-60"
                >
                  {saving ? (
                    <Loader2 className="h-4 w-4 animate-spin" />
                  ) : (
                    <Reply className="h-4 w-4" />
                  )}
                  Save Reply
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      <ConfirmDialog
        isOpen={Boolean(pendingDelete)}
        title={`Delete ${pendingDelete?.subject || "message"}?`}
        message="This contact inquiry will be permanently removed from the database."
        onConfirm={confirmDelete}
        onCancel={() => setPendingDelete(null)}
      />
    </div>
  );
}
