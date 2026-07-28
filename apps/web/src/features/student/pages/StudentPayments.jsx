import React, { useState, useEffect } from "react";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import { Loader2, CreditCard } from "lucide-react";

export default function StudentPayments() {
  const { t } = useLanguage();
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
        setError("Failed to load your payments record list.");
      } finally {
        setLoading(false);
      }
    };
    fetchPayments();
  }, []);

  if (loading) {
    return <LoadingState message="Loading payment transactions..." />;
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-200">
      <div>
        <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
          {t("payment.title")}
        </h1>
        <p className="text-xs font-semibold text-gray-400">
          Track and monitor your billing payments and uploaded receipts ledger
        </p>
      </div>

      {error && (
        <div className="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-600 text-xs font-bold">
          {error}
        </div>
      )}

      {/* Payments history ledger */}
      <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
        {payments.length > 0 ? (
          <div className="space-y-4">
            {payments.map((p) => (
              <div
                key={p.id}
                className="p-5 border border-gray-100 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
              >
                <div className="space-y-1.5 grow">
                  <div className="flex items-center gap-2">
                    <h3 className="text-xs font-black text-navy uppercase tracking-wider">
                      Payment #{p.transaction_id || "PENDING"}
                    </h3>
                    <span
                      className={`text-[9px] font-extrabold px-2 py-0.5 rounded-full ${p.status === "verified" ? "bg-emerald-50 text-emerald-600 border border-emerald-150" : "bg-amber-50 text-amber-600 border border-amber-150"}`}
                    >
                      {p.status.toUpperCase()}
                    </span>
                  </div>
                  <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        Paid Amount
                      </p>
                      <p className="font-extrabold text-navy">
                        ${Number(p.amount).toLocaleString()}
                      </p>
                    </div>
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        Payment Method
                      </p>
                      <p className="font-semibold text-gray-500 capitalize">
                        {p.payment_method || "Bank Transfer"}
                      </p>
                    </div>
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        Payment Date
                      </p>
                      <p className="font-semibold text-gray-500">
                        {p.payment_date
                          ? new Date(p.payment_date).toLocaleDateString()
                          : "Pending review"}
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
            <p>Your payment transaction history is empty.</p>
            <p className="text-[10px] font-semibold">
              Tuition payment records appear here once they are received and
              processed by BSTU Billing.
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
