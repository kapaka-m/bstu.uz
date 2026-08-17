import React, { useEffect, useMemo, useState } from "react";
import { useParams, Link, useNavigate } from "react-router-dom";
import {
  Calendar,
  Eye,
  Search,
  Folder,
  Tag,
  Share2,
  User,
  Copy,
  Check,
  AlertCircle,
  Megaphone,
} from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { announcementService } from "../services/announcementService";
import { formatLocalizedDate } from "../utils/dateFormat";

export default function AnnouncementDetails() {
  const { language, isRtl, t } = useLanguage();
  const navigate = useNavigate();
  const { id } = useParams();
  const [copied, setCopied] = useState(false);
  const [announcement, setAnnouncement] = useState(null);
  const [announcements, setAnnouncements] = useState([]);
  const [settings, setSettings] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    window.scrollTo(0, 0);
    let mounted = true;
    setLoading(true);
    setError("");

    Promise.all([
      announcementService.getSettings().catch(() => null),
      announcementService.getAnnouncement(id).catch(() => null),
      announcementService
        .getAnnouncements({ per_page: 100 })
        .catch(() => ({ items: [] })),
    ])
      .then(([settingsData, itemData, listData]) => {
        if (!mounted) return;
        setSettings(settingsData);
        setAnnouncement(itemData);
        setAnnouncements(listData.items || []);
      })
      .catch(() => {
        if (!mounted) return;
        setSettings(null);
        setAnnouncement(null);
        setAnnouncements([]);
        setError(t("common.loadError"));
      })
      .finally(() => {
        if (mounted) setLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, [id, language, t]);

  const formatDate = (value) => {
    return formatLocalizedDate(value, language, t, {
      month: "short",
      day: "2-digit",
    });
  };

  const categories = useMemo(() => {
    const grouped = announcements.reduce((acc, item) => {
      const value = item.category.toLowerCase();
      if (!acc[value]) acc[value] = { name: item.category_label, count: 0, value };
      acc[value].count += 1;
      return acc;
    }, {});
    return Object.values(grouped);
  }, [announcements]);

  const recentWidgetAnnouncements = announcements
    .filter((p) => p.slug !== announcement?.slug)
    .slice(0, Number(settings?.recent_limit || 5));

  const paragraphArray = (announcement?.content || "")
    .split(/\n{2,}/)
    .map((text) => text.trim())
    .filter(Boolean);

  const handleCopyLink = () => {
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(window.location.href);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  if (loading) {
    return null;
  }

  if (error) {
    return (
      <div className="pt-24 min-h-screen bg-slate-50/50 flex items-center justify-center px-4">
        <div className="flex items-center gap-3 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-bold text-red-600">
          <AlertCircle className="w-5 h-5 shrink-0" />
          <span>{error}</span>
        </div>
      </div>
    );
  }

  if (!announcement) {
    return (
      <div className="pt-24 min-h-screen bg-slate-50/50">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-16 text-center text-gray-500 font-semibold">
          {settings?.no_results_label || ""}
        </div>
      </div>
    );
  }

  const publisher = announcement.publisher || null;
  const publisherName = publisher?.name || settings?.publisher_name || "";
  const publisherRoute = publisher?.slug ? `/publishers/${publisher.slug}` : "";

  return (
    <div className="pt-24 min-h-screen bg-slate-50/50 overflow-x-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 text-start min-w-0">
        <div className="grid min-w-0 grid-cols-1 lg:grid-cols-12 gap-10 items-start">
          <div className="lg:col-span-8 flex min-w-0 flex-col gap-8">
            <motion.article
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              className="bg-white border border-gray-100 rounded-3xl p-5 sm:p-6 md:p-8 shadow-xs flex min-w-0 flex-col gap-6"
            >
              <div className="rounded-2xl overflow-hidden shadow-xs aspect-video bg-primary/10 border border-slate-100 relative group">
                {announcement.image ? (
                  <img
                    src={announcement.image}
                    alt={announcement.title}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-103"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-primary">
                    <Megaphone className="w-14 h-14" />
                  </div>
                )}
                <span
                  className={`absolute top-4 ${isRtl ? "right-4" : "left-4"} bg-primary text-white text-xs font-extrabold uppercase px-3 py-1 rounded-lg shadow-sm`}
                >
                  {announcement.category_label}
                </span>
              </div>

              <h1 className="text-2xl md:text-4xl font-extrabold text-navy leading-snug break-words">
                {announcement.title}
              </h1>

              <div className="flex min-w-0 flex-wrap items-center gap-4 md:gap-6 text-xs md:text-sm font-semibold text-gray-400 border-b border-gray-100 pb-5">
                <span className="flex min-w-0 items-center gap-1.5">
                  <Calendar className="w-4 h-4 text-primary" />
                  <span className="truncate">{formatDate(announcement.date)}</span>
                </span>
                <span className="flex items-center gap-1.5">
                  <Eye className="w-4 h-4 text-primary" />
                  <span>
                    {announcement.views} {settings?.views_label || ""}
                  </span>
                </span>
                <span className="flex min-w-0 items-center gap-1.5">
                  <User className="w-4 h-4 text-primary" />
                  <span className="min-w-0 break-words">
                    {settings?.published_by_label || ""}:{" "}
                    {publisherRoute ? (
                      <Link to={publisherRoute} className="text-navy hover:text-primary transition-colors">
                        {publisherName}
                      </Link>
                    ) : (
                      publisherName
                    )}
                  </span>
                </span>
              </div>

              <div className="text-gray-600 text-sm md:text-base leading-relaxed flex min-w-0 flex-col gap-5 whitespace-pre-line font-medium break-words">
                {paragraphArray.length ? (
                  paragraphArray.map((p, idx) => (
                    <p key={idx} className="leading-relaxed break-words">
                      {p}
                    </p>
                  ))
                ) : (
                  <p className="leading-relaxed break-words">{announcement.excerpt}</p>
                )}
              </div>

              <div className="flex min-w-0 flex-col sm:flex-row sm:items-center justify-between gap-6 border-t border-gray-100 pt-5 text-xs md:text-sm text-gray-400 font-semibold">
                <div className="flex min-w-0 flex-wrap items-center gap-4 md:gap-6">
                  <span className="flex items-center gap-1.5">
                    <Folder className="w-4 h-4 text-primary" />
                    <span className="text-navy font-bold">
                      {announcement.category_label}
                    </span>
                  </span>
                  <span className="flex items-center gap-1.5">
                    <Tag className="w-4 h-4 text-primary" />
                    <div className="flex min-w-0 gap-2">
                      <Link
                        to={`/announcements?category=${announcement.category}`}
                        className="min-w-0 break-words bg-slate-50 hover:bg-primary/5 hover:text-primary transition-colors border border-slate-100 px-3 py-1 rounded-lg text-gray-500 font-extrabold"
                      >
                        {announcement.category_label}
                      </Link>
                    </div>
                  </span>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                  <span className="text-xs font-extrabold text-gray-400 flex items-center gap-1">
                    <Share2 className="w-3.5 h-3.5 text-primary" />
                    {settings?.share_label || ""}:
                  </span>
                  <button
                    onClick={handleCopyLink}
                    className="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-primary/10 hover:text-primary transition-all rounded-lg text-xs font-bold border border-slate-100 cursor-pointer"
                  >
                    {copied ? (
                      <>
                        <Check className="w-3.5 h-3.5 text-green-500" />
                        <span className="text-green-600">
                          {settings?.copied_label || ""}
                        </span>
                      </>
                    ) : (
                      <>
                        <Copy className="w-3.5 h-3.5" />
                        <span>{settings?.copy_link_label || ""}</span>
                      </>
                    )}
                  </button>
                </div>
              </div>
            </motion.article>
          </div>

          <div className="lg:col-span-4 flex min-w-0 flex-col gap-8 text-start">
            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {settings?.search_title || ""}
              </h4>
              <div className="flex min-w-0 bg-slate-50 border border-gray-100 rounded-xl overflow-hidden hover:border-gray-200 transition-all">
                <input
                  id="announcement-details-search"
                  name="announcement_details_search"
                  type="text"
                  placeholder={settings?.search_placeholder || ""}
                  className="min-w-0 grow px-4 py-3 text-sm bg-transparent focus:outline-none"
                  onKeyDown={(e) => {
                    if (e.key === "Enter") {
                      navigate(`/announcements?search=${encodeURIComponent(e.target.value)}`);
                    }
                  }}
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center bg-slate-100/50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>

            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-slate-50 pb-3">
                {settings?.categories_title || ""}
              </h4>
              <ul className="flex flex-col gap-2 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <Link
                      to={`/announcements?category=${cat.value}`}
                      className="w-full flex min-w-0 items-center justify-between gap-3 py-2.5 px-3 rounded-xl text-gray-500 hover:bg-slate-50 hover:text-primary transition-all"
                    >
                      <span className="min-w-0 break-words">{cat.name}</span>
                      <span className="bg-slate-50 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold text-gray-400">
                        ({cat.count})
                      </span>
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-slate-50 pb-3">
                {settings?.recent_title || ""}
              </h4>
              <div className="flex flex-col gap-4">
                {recentWidgetAnnouncements.map((ann) => (
                  <div key={ann.slug} className="flex min-w-0 gap-3 group">
                    {ann.image ? (
                      <img
                        src={ann.image}
                        alt={ann.title}
                        className="w-14 h-14 rounded-xl object-cover shrink-0 bg-gray-50 border border-slate-100"
                      />
                    ) : (
                      <div className="w-14 h-14 rounded-xl shrink-0 bg-primary/10 border border-primary/10 flex items-center justify-center text-primary">
                        <Megaphone className="w-5 h-5" />
                      </div>
                    )}
                    <div className="flex flex-col justify-center text-start min-w-0">
                      <h5 className="font-extrabold text-xs text-navy group-hover:text-primary transition-colors line-clamp-2 leading-snug">
                        <Link to={`/announcements/${ann.slug}`}>{ann.title}</Link>
                      </h5>
                      <span className="text-[10px] text-gray-400 mt-1 font-bold">
                        {formatDate(ann.date)}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
