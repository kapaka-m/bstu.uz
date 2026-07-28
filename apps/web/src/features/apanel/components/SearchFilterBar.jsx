import React from "react";
import { Search, Plus } from "lucide-react";
import { useLanguage } from "../../../context/LanguageContext";

export default function SearchFilterBar({
  searchQuery = "",
  onSearchChange,
  onSearchSubmit,
  filters = [],
  activeFilters = {},
  onFilterChange,
  onAddClick,
  addLabel,
}) {
  const { t } = useLanguage();
  const handleSubmit = (e) => {
    e.preventDefault();
    if (onSearchSubmit) onSearchSubmit();
  };

  return (
    <div className="bg-white border border-gray-100 p-5 rounded-3xl shadow-xs flex flex-col md:flex-row gap-4 items-center justify-between">
      {/* Left: Search & Filters */}
      <div className="flex flex-col sm:flex-row flex-wrap gap-4 w-full md:w-auto items-center">
        {onSearchChange && (
          <form onSubmit={handleSubmit} className="relative w-full sm:w-64">
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => onSearchChange(e.target.value)}
              placeholder={t("apanel.search.placeholder")}
              className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
            />
            <Search className="absolute left-3.5 top-3.5 w-3.5 h-3.5 text-gray-400" />
          </form>
        )}

        {/* Dynamic Filters */}
        {filters.map((filter) => (
          <div
            key={filter.name}
            className="flex items-center gap-1.5 w-full sm:w-auto"
          >
            {filter.label && (
              <span className="text-[10px] uppercase font-bold text-gray-400 shrink-0">
                {filter.label}:
              </span>
            )}
            <select
              value={activeFilters[filter.name] || ""}
              onChange={(e) => onFilterChange(filter.name, e.target.value)}
              className="w-full sm:w-auto text-xs font-semibold px-3 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-primary bg-white text-navy cursor-pointer"
            >
              <option value="">{t("common.all")}</option>
              {filter.options.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
          </div>
        ))}
      </div>

      {/* Right: Actions */}
      {onAddClick && (
        <button
          onClick={onAddClick}
          className="w-full md:w-auto inline-flex items-center justify-center gap-1.5 bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-xl text-xs font-extrabold shadow-sm hover:shadow-md cursor-pointer transition-all shrink-0"
        >
          <Plus className="w-4 h-4" />
          {addLabel || t("apanel.search.addNew")}
        </button>
      )}
    </div>
  );
}
