import React, { useEffect, useState } from "react";
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
} from "lucide-react";
import { motion } from "framer-motion";
import { announcementsData } from "../data/announcementsData";
import { useLanguage } from "../context/LanguageContext";

export default function AnnouncementDetails() {
  const { t, language } = useLanguage();
  const navigate = useNavigate();
  const { id } = useParams();
  const [copied, setCopied] = useState(false);

  // Find specific announcement
  const announcement =
    announcementsData.find((p) => String(p.id) === String(id)) ||
    announcementsData[0];

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  const uniqueCategories = [
    ...new Set(announcementsData.map((p) => p.category)),
  ];
  const categories = uniqueCategories.map((cat) => {
    const name = t(
      `announcements.categories.${cat.toLowerCase()}`,
      cat.charAt(0).toUpperCase() + cat.slice(1),
    );
    const count = announcementsData.filter(
      (p) => p.category.toLowerCase() === cat.toLowerCase(),
    ).length;
    return { name, count, value: cat.toLowerCase() };
  });

  const recentWidgetAnnouncements = announcementsData
    .filter((p) => p.id !== announcement.id)
    .slice(0, 5);

  const title = t(`announcements.items.${announcement.id}.title`);
  const paragraphs = t(`announcements.items.${announcement.id}.paragraphs`);
  const paragraphArray = Array.isArray(paragraphs) ? paragraphs : [];
  const translatedCategory = t(
    `announcements.categories.${announcement.category.toLowerCase()}`,
    announcement.category,
  );

  const handleCopyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="pt-24 min-h-screen bg-slate-50/50">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16 text-start">
        {/* Back Button */}

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
          {/* Left: Announcement Content */}
          <div className="lg:col-span-8 flex flex-col gap-8">
            <motion.article
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              className="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-xs flex flex-col gap-6"
            >
              {/* Image */}
              <div className="rounded-2xl overflow-hidden shadow-xs aspect-video bg-gray-50 border border-slate-100 relative group">
                <img
                  src={announcement.image}
                  alt={title}
                  className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-103"
                />
                <span
                  className={`absolute top-4 ${language === "ar" ? "right-4" : "left-4"} bg-primary text-white text-xs font-extrabold uppercase px-3 py-1 rounded-lg shadow-sm`}
                >
                  {translatedCategory}
                </span>
              </div>

              {/* Title */}
              <h1 className="text-2xl md:text-4xl font-extrabold text-navy leading-snug">
                {title}
              </h1>

              {/* Meta */}
              <div className="flex flex-wrap items-center gap-6 text-xs md:text-sm font-semibold text-gray-400 border-b border-gray-100 pb-5">
                <span className="flex items-center gap-1.5">
                  <Calendar className="w-4 h-4 text-primary" />
                  <span>{announcement.date}</span>
                </span>
                <span className="flex items-center gap-1.5">
                  <Eye className="w-4 h-4 text-primary" />
                  <span>
                    {announcement.views}{" "}
                    {t("announcements.viewsLabel", "Views")}
                  </span>
                </span>
                <span className="flex items-center gap-1.5">
                  <User className="w-4 h-4 text-primary" />
                  <span>
                    {t("announcements.publishedBy", "Published By")}: Admin
                  </span>
                </span>
              </div>

              {/* Content Paragraphs */}
              <div className="text-gray-600 text-sm md:text-base leading-relaxed flex flex-col gap-5 whitespace-pre-line font-medium">
                {paragraphArray.map((p, idx) => (
                  <p key={idx} className="leading-relaxed">
                    {p}
                  </p>
                ))}
              </div>

              {/* Meta Bottom & Share */}
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-6 border-t border-gray-100 pt-5 text-xs md:text-sm text-gray-400 font-semibold">
                <div className="flex flex-wrap items-center gap-6">
                  <span className="flex items-center gap-1.5">
                    <Folder className="w-4 h-4 text-primary" />
                    <span className="text-navy font-bold">
                      {translatedCategory}
                    </span>
                  </span>
                  <span className="flex items-center gap-1.5">
                    <Tag className="w-4 h-4 text-primary" />
                    <div className="flex gap-2">
                      <Link
                        to={`/announcements?category=${announcement.category.toLowerCase()}`}
                        className="bg-slate-50 hover:bg-primary/5 hover:text-primary transition-colors border border-slate-100 px-3 py-1 rounded-lg text-gray-500 font-extrabold"
                      >
                        {translatedCategory}
                      </Link>
                    </div>
                  </span>
                </div>

                {/* Share Button */}
                <div className="flex items-center gap-2">
                  <span className="text-xs font-extrabold text-gray-400 flex items-center gap-1">
                    <Share2 className="w-3.5 h-3.5 text-primary" />
                    {t("announcements.shareLabel", "Share")}:
                  </span>
                  <button
                    onClick={handleCopyLink}
                    className="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-primary/10 hover:text-primary transition-all rounded-lg text-xs font-bold border border-slate-100 cursor-pointer"
                  >
                    {copied ? (
                      <>
                        <Check className="w-3.5 h-3.5 text-green-500" />
                        <span className="text-green-600">
                          {t("announcements.copied", "Copied!")}
                        </span>
                      </>
                    ) : (
                      <>
                        <Copy className="w-3.5 h-3.5" />
                        <span>{t("announcements.copyLink", "Copy Link")}</span>
                      </>
                    )}
                  </button>
                </div>
              </div>
            </motion.article>
          </div>

          {/* Right: Sidebar */}
          <div className="lg:col-span-4 flex flex-col gap-8 text-start">
            {/* Search Box */}
            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {t("announcements.searchTitle", "Search")}
              </h4>
              <div className="flex bg-slate-50 border border-gray-100 rounded-xl overflow-hidden hover:border-gray-200 transition-all">
                <input
                  type="text"
                  placeholder={t(
                    "announcements.searchPlaceholder",
                    "Search announcements...",
                  )}
                  className="grow px-4 py-3 text-sm bg-transparent focus:outline-none"
                  onKeyDown={(e) => {
                    if (e.key === "Enter") {
                      navigate(`/announcements?search=${e.target.value}`);
                    }
                  }}
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center bg-slate-100/50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>

            {/* Categories */}
            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-slate-50 pb-3">
                {t("announcements.categoriesTitle", "Categories")}
              </h4>
              <ul className="flex flex-col gap-2 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <Link
                      to={`/announcements?category=${cat.value}`}
                      className="w-full flex items-center justify-between py-2.5 px-3 rounded-xl text-gray-500 hover:bg-slate-50 hover:text-primary transition-all"
                    >
                      <span>{cat.name}</span>
                      <span className="bg-slate-50 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold text-gray-400">
                        ({cat.count})
                      </span>
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            {/* Recent Announcements Widget */}
            <div className="bg-white border border-gray-100 p-6 rounded-3xl shadow-xs">
              <h4 className="text-base font-extrabold text-navy mb-4 border-b border-slate-50 pb-3">
                {t("announcements.recentTitle", "Recent Announcements")}
              </h4>
              <div className="flex flex-col gap-4">
                {recentWidgetAnnouncements.map((ann) => {
                  const title = t(`announcements.items.${ann.id}.title`);
                  return (
                    <div key={ann.id} className="flex gap-3 group">
                      <img
                        src={ann.image}
                        alt={title}
                        className="w-14 h-14 rounded-xl object-cover shrink-0 bg-gray-50 border border-slate-100"
                      />
                      <div className="flex flex-col justify-center text-start">
                        <h5 className="font-extrabold text-xs text-navy group-hover:text-primary transition-colors line-clamp-2 leading-snug">
                          <Link to={`/announcements/${ann.id}`}>{title}</Link>
                        </h5>
                        <span className="text-[10px] text-gray-400 mt-1 font-bold">
                          {ann.date}
                        </span>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
