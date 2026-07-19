import React, { useState, useEffect } from "react";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import { Loader2, CreditCard, Download } from "lucide-react";

export default function StudentContracts() {
  const { t } = useLanguage();
  const [contracts, setContracts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchContracts = async () => {
      try {
        setLoading(true);
        setError("");
        const data = await studentService.getContracts();
        setContracts(data);
      } catch (err) {
        console.error("Failed to load contracts", err);
        setError("Failed to load your contracts list.");
      } finally {
        setLoading(false);
      }
    };
    fetchContracts();
  }, []);

  if (loading) {
    return <LoadingState message="Loading billing contracts..." />;
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-200">
      <div>
        <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
          {t("contract.title", "Tuition Contracts & Invoices")}
        </h1>
        <p className="text-xs font-semibold text-gray-400">
          View contract parameters, bill amounts, and download tuition documents
        </p>
      </div>

      {error && (
        <div className="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-600 text-xs font-bold">
          {error}
        </div>
      )}

      {/* Contracts table list */}
      <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
        {contracts.length > 0 ? (
          <div className="space-y-4">
            {contracts.map((c) => (
              <div
                key={c.id}
                className="p-5 border border-gray-100 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
              >
                <div className="space-y-1.5 grow">
                  <div className="flex items-center gap-2">
                    <h3 className="text-xs font-black text-navy uppercase tracking-wider">
                      Contract #{c.contract_number}
                    </h3>
                    <span
                      className={`text-[9px] font-extrabold px-2 py-0.5 rounded-full ${c.payment_status === "paid" ? "bg-emerald-50 text-emerald-600 border border-emerald-150" : "bg-amber-50 text-amber-600 border border-amber-150"}`}
                    >
                      {c.payment_status.toUpperCase()}
                    </span>
                  </div>
                  <div className="grid grid-cols-2 gap-4 text-xs">
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        Billing Amount
                      </p>
                      <p className="font-extrabold text-navy">
                        ${Number(c.amount).toLocaleString()}
                      </p>
                    </div>
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        Issue Date
                      </p>
                      <p className="font-semibold text-gray-500">
                        {new Date(c.created_at).toLocaleDateString()}
                      </p>
                    </div>
                  </div>
                </div>
                <div className="flex gap-2 w-full sm:w-auto">
                  <a
                    href={`/storage/${c.file_path}`}
                    target="_blank"
                    rel="noreferrer"
                    className="w-full sm:w-auto text-center flex items-center justify-center gap-1.5 px-4 py-2.5 bg-primary text-white hover:bg-primary-hover text-xs font-extrabold rounded-xl transition-all cursor-pointer shadow-sm hover:shadow"
                  >
                    <Download className="w-4 h-4" />
                    <span>Download Contract PDF</span>
                  </a>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-12 text-gray-400 font-bold text-xs space-y-2">
            <CreditCard className="w-8 h-8 text-gray-300 mx-auto" />
            <p>Your contract billing folder is currently empty.</p>
            <p className="text-[10px] font-semibold">
              Contract files are generated once your application is approved by
              the admissions panel.
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
