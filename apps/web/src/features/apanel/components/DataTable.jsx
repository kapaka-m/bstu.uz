import React from "react";
import {
  Edit2,
  Trash2,
  Eye,
  ArrowUpDown,
  Check,
  X,
  ShieldAlert,
} from "lucide-react";
import StatusBadge from "./StatusBadge";
import { useLanguage } from "../../../context/LanguageContext";

/** Resolve dot-notation key path from an object (e.g. "studentProfile.user.name") */
function getNestedValue(obj, keyPath) {
  return keyPath.split(".").reduce((acc, key) => {
    if (acc === null || acc === undefined) return undefined;
    // Support numeric indexes for arrays
    const numKey = Number(key);
    if (!isNaN(numKey) && Array.isArray(acc)) return acc[numKey];
    return acc[key];
  }, obj);
}

export default function DataTable({
  columns = [],
  data = [],
  sortBy = "id",
  sortDir = "desc",
  onSortChange,
  onViewClick,
  onEditClick,
  onDeleteClick,
  onStatusToggle,
}) {
  const { t } = useLanguage();
  const handleSort = (key) => {
    if (!onSortChange) return;
    const newDir = sortBy === key && sortDir === "desc" ? "asc" : "desc";
    onSortChange(key, newDir);
  };

  return (
    <div className="w-full bg-white border border-gray-150 rounded-3xl overflow-hidden shadow-xs">
      <div className="overflow-x-auto w-full">
        <table className="w-full text-start border-collapse">
          <thead>
            <tr className="bg-gray-50 border-b border-gray-100 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 text-start">
              {columns.map((col) => (
                <th
                  key={col.key}
                  onClick={() => col.sortable && handleSort(col.key)}
                  className={`px-6 py-4.5 text-start select-none ${col.sortable ? "cursor-pointer hover:text-navy transition-all" : ""}`}
                >
                  <div className="flex items-center gap-1">
                    <span>{col.label}</span>
                    {col.sortable && (
                      <ArrowUpDown className="w-3 h-3 shrink-0" />
                    )}
                  </div>
                </th>
              ))}
              <th className="px-6 py-4.5 text-end">{t("apanel.dataTable.actions")}</th>
            </tr>
          </thead>

          <tbody className="divide-y divide-gray-50 text-xs font-semibold text-navy">
            {data.length === 0 ? (
              <tr>
                <td
                  colSpan={columns.length + 1}
                  className="px-6 py-12 text-center text-gray-400 font-medium"
                >
                  <div className="flex flex-col items-center justify-center gap-2">
                    <ShieldAlert className="w-8 h-8 stroke-1 text-gray-300" />
                    <span>{t("apanel.dataTable.noRecords")}</span>
                  </div>
                </td>
              </tr>
            ) : (
              data.map((row, index) => (
                <tr
                  key={row.id || index}
                  className="hover:bg-gray-50/50 transition-all"
                >
                  {columns.map((col) => {
                    // Support nested dot-notation keys (e.g. "studentProfile.user.name")
                    const value = col.key.includes(".")
                      ? getNestedValue(row, col.key)
                      : row[col.key];

                    return (
                      <td
                        key={col.key}
                        className="px-6 py-4.5 max-w-xs truncate"
                      >
                        {col.type === "boolean" ||
                        col.key === "is_active" ||
                        col.key === "is_published" ||
                        col.key === "is_system" ||
                        col.key === "is_read" ? (
                          onStatusToggle ? (
                            <button
                              onClick={() =>
                                onStatusToggle(row.id, col.key, !value)
                              }
                              className={`p-1 rounded-lg border transition-all cursor-pointer ${
                                value
                                  ? "bg-emerald-50 text-emerald-600 border-emerald-100 hover:bg-emerald-100"
                                  : "bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100"
                              }`}
                              title={t("apanel.dataTable.toggleStatus")}
                            >
                              {value ? (
                                <Check className="w-3.5 h-3.5" />
                              ) : (
                                <X className="w-3.5 h-3.5" />
                              )}
                            </button>
                          ) : (
                            <StatusBadge status={value} />
                          )
                        ) : col.key === "status" || col.key === "degree" ? (
                          <StatusBadge status={value} />
                        ) : col.key === "created_at" ||
                          col.key === "updated_at" ||
                          col.key === "published_at" ? (
                          <span className="text-[10px] text-gray-400">
                            {value ? new Date(value).toLocaleString() : "—"}
                          </span>
                        ) : (
                          <span>
                            {value !== null && value !== undefined
                              ? String(value)
                              : "—"}
                          </span>
                        )}
                      </td>
                    );
                  })}

                  <td className="px-6 py-4.5 text-end shrink-0">
                    <div className="flex justify-end gap-1.5">
                      {onViewClick && (
                        <button
                          onClick={() => onViewClick(row)}
                          className="p-2 text-gray-400 hover:text-navy hover:bg-gray-100 rounded-lg cursor-pointer transition-all"
                          title={t("apanel.dataTable.viewDetails")}
                        >
                          <Eye className="w-3.5 h-3.5" />
                        </button>
                      )}
                      {onEditClick && (
                        <button
                          onClick={() => onEditClick(row)}
                          className="p-2 text-gray-400 hover:text-primary hover:bg-primary-light rounded-lg cursor-pointer transition-all"
                          title={t("button.edit")}
                        >
                          <Edit2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                      {onDeleteClick && (
                        <button
                          onClick={() => onDeleteClick(row)}
                          className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg cursor-pointer transition-all"
                          title={t("button.delete")}
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
