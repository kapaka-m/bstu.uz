import React, { useState, useEffect } from "react";
import { studentService } from "../../../services/studentService";
import { useLanguage } from "../../../context/LanguageContext";
import { formatLocalizedDate } from "../../../utils/dateFormat";
import { Loader2, CreditCard, Download } from "lucide-react";
import { publicAssetUrl } from "../../../lib/api";
import StatusBadge from "../../apanel/components/StatusBadge";

export default function StudentContracts() {
  const { language, t } = useLanguage();
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
        setError(t("contract.loadError"));
      } finally {
        setLoading(false);
      }
    };
    fetchContracts();
  }, [t]);

  if (loading) {
    return <LoadingState message={t("contract.loading")} />;
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
          {t("contract.title")}
        </h1>
        <p className="text-xs font-semibold text-gray-400">
          {t("contract.subtitle")}
        </p>
      </div>

      {error && (
        <div className="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-600 text-xs font-bold">
          {error}
        </div>
      )}

      {/* Contracts table list */}
      <div className="min-w-0 bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-xs space-y-4">
        {contracts.length > 0 ? (
          <div className="space-y-4">
            {contracts.map((c) => (
              <div
                key={c.id}
                className="p-5 border border-gray-100 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
              >
                <div className="min-w-0 grow space-y-1.5">
                  <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <h3 className="text-xs font-black text-navy uppercase tracking-wider break-all">
                      {t("contract.number")} #{c.contract_number}
                    </h3>
                    <StatusBadge status={c.payment_status} />
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        {t("contract.billingAmount")}
                      </p>
                      <p className="font-extrabold text-navy">
                        ${Number(c.amount).toLocaleString()}
                      </p>
                    </div>
                    <div>
                      <p className="text-[10px] text-gray-400 font-bold">
                        {t("common.issueDate")}
                      </p>
                      <p className="font-semibold text-gray-500">
                        {formatDate(c.created_at)}
                      </p>
                    </div>
                  </div>
                </div>
                <div className="flex gap-2 w-full sm:w-auto">
                  <a
                    href={publicAssetUrl(c.file_path)}
                    target="_blank"
                    rel="noreferrer"
                    className="w-full sm:w-auto text-center flex items-center justify-center gap-1.5 px-4 py-2.5 bg-primary text-white hover:bg-primary-hover text-xs font-extrabold rounded-xl transition-all cursor-pointer shadow-sm hover:shadow"
                  >
                    <Download className="w-4 h-4" />
                    <span>{t("contract.downloadPdf")}</span>
                  </a>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-12 text-gray-400 font-bold text-xs space-y-2">
            <CreditCard className="w-8 h-8 text-gray-300 mx-auto" />
            <p>{t("contract.empty")}</p>
            <p className="text-[10px] font-semibold">
              {t("contract.emptyHint")}
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
