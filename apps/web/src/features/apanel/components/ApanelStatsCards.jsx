import React from "react";

export default function ApanelStatsCards({ items = [] }) {
  const visibleItems = items.filter(Boolean);

  if (visibleItems.length === 0) return null;

  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      {visibleItems.map((item) => {
        const Icon = item.icon;
        const tone = item.tone || "text-primary bg-primary/10 border-primary/10";

        return (
          <div
            key={item.label}
            className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
          >
            <div className="flex items-start justify-between gap-4">
              <div className="min-w-0">
                <p className="text-[10px] font-black uppercase tracking-widest text-gray-400">
                  {item.label}
                </p>
                <p className="mt-2 truncate text-3xl font-black leading-none text-navy">
                  {item.value ?? 0}
                </p>
                {item.hint && (
                  <p className="mt-2 truncate text-xs font-bold text-gray-400">
                    {item.hint}
                  </p>
                )}
              </div>
              {Icon && (
                <div className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border ${tone}`}>
                  <Icon className="h-5 w-5" />
                </div>
              )}
            </div>
          </div>
        );
      })}
    </div>
  );
}
