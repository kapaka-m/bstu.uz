import React, { useCallback, useEffect, useMemo, useState } from "react";
import {
  CalendarClock,
  Loader2,
  Mail,
  RefreshCw,
  Search,
  Trash2,
  Users,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { apanelService } from "../../../services/apanelService";

function formatDate(value) {
  if (!value) return "—";
  return new Intl.DateTimeFormat("en", {
    year: "numeric",
    month: "short",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  }).format(new Date(value));
}

export default function ApanelNewsletterSubscriptions() {
  const [items, setItems] = useState([]);
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [deletingId, setDeletingId] = useState(null);

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
      setError(err?.message || "Failed to load newsletter subscriptions.");
    } finally {
      setLoading(false);
    }
  }, [page, search]);

  useEffect(() => {
    fetchSubscriptions();
  }, [fetchSubscriptions]);

  const activeCount = useMemo(
    () => items.filter((item) => item.status === "active").length,
    [items],
  );

  const latestDate = items[0]?.subscribed_at || items[0]?.created_at;

  const deleteSubscription = async (id) => {
    try {
      setDeletingId(id);
      await apanelService.delete("newsletter-subscriptions", id);
      fetchSubscriptions();
    } catch (err) {
      setError(err?.message || "Failed to delete subscription.");
    } finally {
      setDeletingId(null);
    }
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            Newsletter Subscriptions
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            Emails collected from the public footer newsletter form.
          </p>
        </div>
        <button
          type="button"
          onClick={fetchSubscriptions}
          className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-navy text-xs font-extrabold hover:bg-gray-50 cursor-pointer"
        >
          <RefreshCw className="w-4 h-4" />
          Refresh
        </button>
      </div>

      {error && <FormError message={error} />}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {[
          { label: "Total subscribers", value: total, icon: Users },
          { label: "Active on this page", value: activeCount, icon: Mail },
          {
            label: "Latest subscription",
            value: latestDate ? formatDate(latestDate) : "—",
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
              placeholder="Search by email, locale, or status"
              className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold text-navy"
            />
          </div>
          <p className="text-xs font-bold text-gray-400">
            Page {page} of {lastPage}
          </p>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full min-w-200 text-start">
            <thead className="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-400 font-black">
              <tr>
                <th className="px-5 py-3 text-start">Email</th>
                <th className="px-5 py-3 text-start">Locale</th>
                <th className="px-5 py-3 text-start">Status</th>
                <th className="px-5 py-3 text-start">Subscribed</th>
                <th className="px-5 py-3 text-start">IP Address</th>
                <th className="px-5 py-3 text-end">Actions</th>
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
                    No newsletter subscriptions found.
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
                      {formatDate(item.subscribed_at || item.created_at)}
                    </td>
                    <td className="px-5 py-4 text-xs font-semibold text-gray-500">
                      {item.ip_address || "—"}
                    </td>
                    <td className="px-5 py-4 text-end">
                      <button
                        type="button"
                        onClick={() => deleteSubscription(item.id)}
                        disabled={deletingId === item.id}
                        className="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-rose-100 text-rose-600 hover:bg-rose-50 disabled:opacity-60 cursor-pointer"
                        aria-label={`Delete ${item.email}`}
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
            Previous
          </button>
          <button
            type="button"
            onClick={() =>
              setPage((current) => Math.min(lastPage, current + 1))
            }
            disabled={page >= lastPage}
            className="px-4 py-2 rounded-xl border border-gray-200 text-xs font-extrabold text-navy disabled:opacity-40 cursor-pointer"
          >
            Next
          </button>
        </div>
      </section>
    </div>
  );
}
