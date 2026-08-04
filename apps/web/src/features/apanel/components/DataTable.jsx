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
    <div className="w-full max-w-full min-w-0 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
      <div className="w-full max-w-full overflow-x-auto">
        <table className="min-w-full border-collapse text-start">
          <thead>
            <tr className="border-b border-gray-100 bg-gray-50/80 text-start text-[10px] font-black uppercase tracking-widest text-gray-500">
              {columns.map((col) => (
                <th
                  key={col.key}
                  onClick={() => col.sortable && handleSort(col.key)}
                  className={`whitespace-nowrap px-5 py-4 text-start select-none ${col.sortable ? "cursor-pointer hover:text-navy transition-all" : ""}`}
                >
                  <div className="flex items-center gap-1.5">
                    <span>{col.label}</span>
                    {col.sortable && (
                      <ArrowUpDown className="h-3 w-3 shrink-0 text-gray-300" />
                    )}
                  </div>
                </th>
              ))}
              <th className="whitespace-nowrap px-5 py-4 text-end">{t("apanel.dataTable.actions")}</th>
            </tr>
          </thead>

          <tbody className="divide-y divide-gray-50 text-xs font-bold text-navy">
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
                  className="transition-all hover:bg-primary-light/30"
                >
                  {columns.map((col) => {
                    // Support nested dot-notation keys (e.g. "studentProfile.user.name")
                    const value = col.key.includes(".")
                      ? getNestedValue(row, col.key)
                      : row[col.key];

                    return (
                      <td
                        key={col.key}
                        className="max-w-72 px-5 py-4 align-middle"
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
                          <span className="whitespace-nowrap text-[10px] font-extrabold text-gray-400">
                            {value ? new Date(value).toLocaleString() : "—"}
                          </span>
                        ) : (
                          <span className="block max-w-72 truncate" title={value !== null && value !== undefined ? String(value) : ""}>
                            {value !== null && value !== undefined
                              ? String(value)
                              : "—"}
                          </span>
                        )}
                      </td>
                    );
                  })}

                  <td className="shrink-0 px-5 py-4 text-end align-middle">
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
