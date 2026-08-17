import React from "react";
import {
  Edit2,
  Trash2,
  Eye,
  ArrowUpDown,
  ArrowDown,
  ArrowUp,
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

function formatCellValue(value) {
  if (value === null || value === undefined || value === "") return "—";
  if (Array.isArray(value)) return value.length ? value.join(", ") : "—";
  if (typeof value === "object") return JSON.stringify(value);
  return String(value);
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
    <div className="w-full max-w-full min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div className="w-full max-w-full overflow-x-auto">
        <table className="min-w-full border-separate border-spacing-0 text-start">
          <thead className="sticky top-0 z-10">
            <tr className="border-b border-gray-200 bg-gray-50/95 text-start text-[10px] font-black uppercase tracking-widest text-gray-500 backdrop-blur">
              {columns.map((col) => (
                <th
                  key={col.key}
                  onClick={() => col.sortable && handleSort(col.key)}
                  className={`whitespace-nowrap border-b border-gray-200 px-5 py-3.5 text-start select-none ${
                    col.sortable ? "cursor-pointer transition-all hover:bg-white hover:text-navy" : ""
                  }`}
                >
                  <div className="flex items-center gap-1.5">
                    <span>{col.label}</span>
                    {col.sortable && (
                      sortBy === col.key ? (
                        sortDir === "asc" ? (
                          <ArrowUp className="h-3.5 w-3.5 shrink-0 text-primary" />
                        ) : (
                          <ArrowDown className="h-3.5 w-3.5 shrink-0 text-primary" />
                        )
                      ) : (
                        <ArrowUpDown className="h-3.5 w-3.5 shrink-0 text-gray-300" />
                      )
                    )}
                  </div>
                </th>
              ))}
              <th className="sticky right-0 whitespace-nowrap border-b border-gray-200 bg-gray-50/95 px-5 py-3.5 text-end backdrop-blur">
                {t("apanel.dataTable.actions")}
              </th>
            </tr>
          </thead>

          <tbody className="text-xs font-bold text-navy">
            {data.length === 0 ? (
              <tr>
                <td
                  colSpan={columns.length + 1}
                  className="px-6 py-16 text-center font-medium text-gray-400"
                >
                  <div className="flex flex-col items-center justify-center gap-3">
                    <div className="flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50">
                      <ShieldAlert className="h-7 w-7 stroke-1 text-gray-300" />
                    </div>
                    <span className="text-sm font-extrabold">{t("apanel.dataTable.noRecords")}</span>
                  </div>
                </td>
              </tr>
            ) : (
              data.map((row, index) => (
                <tr
                  key={row.id || index}
                  className="group transition-all odd:bg-white even:bg-gray-50/35 hover:bg-primary-light/30"
                >
                  {columns.map((col, colIndex) => {
                    // Support nested dot-notation keys (e.g. "studentProfile.user.name")
                    const value = col.key.includes(".")
                      ? getNestedValue(row, col.key)
                      : row[col.key];
                    const displayValue = formatCellValue(value);

                    return (
                      <td
                        key={col.key}
                        className="max-w-80 border-b border-gray-100 px-5 py-4 align-middle"
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
                              className={`inline-flex min-h-8 items-center gap-1.5 rounded-xl border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider transition-all cursor-pointer ${
                                value
                                  ? "bg-emerald-50 text-emerald-700 border-emerald-100 hover:bg-emerald-100"
                                  : "bg-rose-50 text-rose-700 border-rose-100 hover:bg-rose-100"
                              }`}
                              title={t("apanel.dataTable.toggleStatus")}
                            >
                              {value ? (
                                <>
                                  <Check className="w-3.5 h-3.5" />
                                  <span>Active</span>
                                </>
                              ) : (
                                <>
                                  <X className="w-3.5 h-3.5" />
                                  <span>Inactive</span>
                                </>
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
                          <span className="whitespace-nowrap text-[10px] font-extrabold text-gray-400">
                            {value ? new Date(value).toLocaleString() : "—"}
                          </span>
                        ) : (
                          <span
                            className={`block max-w-80 truncate ${
                              displayValue === "—"
                                ? "text-gray-300"
                                : colIndex === 0
                                  ? "font-black text-navy"
                                  : "text-gray-600"
                            }`}
                            title={displayValue !== "—" ? displayValue : ""}
                          >
                            {displayValue}
                          </span>
                        )}
                      </td>
                    );
                  })}

                  <td className="sticky right-0 shrink-0 border-b border-gray-100 bg-inherit px-5 py-4 text-end align-middle shadow-[-12px_0_18px_-18px_rgba(15,23,42,0.4)]">
                    <div className="flex justify-end gap-1 rounded-xl border border-gray-100 bg-white p-1 shadow-xs">
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
