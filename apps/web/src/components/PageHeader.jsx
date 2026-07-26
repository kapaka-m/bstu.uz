import React from "react";
import { Link } from "react-router-dom";
import { ChevronRight } from "lucide-react";

export default function PageHeader({ title, breadcrumbs }) {
  const visibleBreadcrumbs = Array.isArray(breadcrumbs) ? breadcrumbs : [];

  return (
    <div className="bg-primary-light py-16 md:py-20 border-b border-gray-100">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl flex flex-col items-center justify-center text-center">
        {/* Title */}
        <h1 className="text-3xl md:text-5xl font-extrabold text-navy mb-4 tracking-tight">
          {title}
        </h1>
        
        {/* Breadcrumbs */}
        {visibleBreadcrumbs.length > 0 && (
          <nav className="flex items-center gap-2 text-xs md:text-sm font-semibold text-gray-500">
            <Link to="/" className="hover:text-primary transition-colors">
              Home
            </Link>
            {visibleBreadcrumbs.map((crumb, index) => (
              <React.Fragment key={index}>
                <ChevronRight className="w-3.5 h-3.5 text-gray-300 shrink-0" />
                {crumb.path ? (
                  <Link to={crumb.path} className="hover:text-primary transition-colors">
                    {crumb.label}
                  </Link>
                ) : (
                  <span className="text-gray-400 font-bold">{crumb.label}</span>
                )}
              </React.Fragment>
            ))}
          </nav>
        )}
      </div>
    </div>
  );
}
