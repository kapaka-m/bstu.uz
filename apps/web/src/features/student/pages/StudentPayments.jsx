import React, { useState, useEffect } from "react";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import { formatLocalizedDate } from "../../../utils/dateFormat";
import { Loader2, CreditCard } from "lucide-react";
import StatusBadge from "../../apanel/components/StatusBadge";

export default function StudentPayments() {
  const { language, t } = useLanguage();
  const [payments, setPayments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchPayments = async () => {
      try {
        setLoading(true);
        setError("");
        const data = await studentService.getPayments();
        setPayments(data);
      } catch (err) {
        console.error("Failed to load payments", err);
        setError(t("payment.loadError"));
      } finally {
        setLoading(false);
      }
    };
    fetchPayments();
  }, [t]);

  if (loading) {
    return <LoadingState message={t("payment.loading")} />;
  }

  const formatDate = (value) =>
    formatLocalizedDate(value, language, t, {
      day: "numeric",
      month: "short",
    });

  return (
    <div className="mx-auto max-w-4xl min-w-0 space-y-6 animate-in fade-in duration-200">
      <div>
        <h1 className="text-xl sm:text-2xl font-extrabold text-navy uppercase tracking-wider break-words">
          {t("payment.title")}
        </h1>
        <p className="text-xs font-semibold text-gray-400">
          {t("payment.subtitle")}
        </p>
      </div>

      {error && (
        <div className="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-600 text-xs font-bold">
          {error}
        </div>
      )}

      {/* Payments history ledger */}
      <div className="min-w-0 bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-xs space-y-4">
        {payments.length > 0 ? (
          <div className="space-y-4">
            {payments.map((p) => (
              <div
                key={p.id}
                className="p-5 border border-gray-100 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
              >
                <div className="min-w-0 grow space-y-1.5">
                  <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <h3 className="text-xs font-black text-navy uppercase tracking-wider break-all">
                      {t("payment.itemNumber")} #{p.transaction_id || t("status.pending")}
                    </h3>
                    <StatusBadge status={p.status} />
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        {t("payment.paidAmount")}
                      </p>
                      <p className="font-extrabold text-navy">
                        ${Number(p.amount).toLocaleString()}
                      </p>
                    </div>
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        {t("payment.method")}
                      </p>
                      <p className="font-semibold text-gray-500 capitalize">
                        {p.payment_method || t("payment.method.bankTransfer")}
                      </p>
                    </div>
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        {t("payment.date")}
                      </p>
                      <p className="font-semibold text-gray-500">
                        {p.payment_date
                          ? formatDate(p.payment_date)
                          : t("status.pendingReview")}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-12 text-gray-400 font-bold text-xs space-y-3">
            <CreditCard className="w-8 h-8 text-gray-300 mx-auto" />
            <p>{t("payment.empty")}</p>
            <p className="text-[10px] font-semibold">
              {t("payment.emptyHint")}
            </p>
          </div>
        )}
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
