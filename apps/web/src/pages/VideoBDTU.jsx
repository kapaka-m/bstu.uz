import React, { useState, useEffect, useRef } from "react";
import { Link } from "react-router-dom";
import { ThumbsUp, Share2, Award, Check, Eye, Calendar, ChevronUp, Bell, MessageSquare, CornerDownRight, User } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { useAuth } from "../context/AuthContext";
import { videoService } from "../services/videoService";
import { formatLocalizedDate } from "../utils/dateFormat";

const ALL_CATEGORY = "__all";

export default function VideoBDTU() {
  const { t, hasTranslation, language, logoSrc } = useLanguage();
  const { user, isAuthenticated, loading: authLoading } = useAuth();
  const [videos, setVideos] = useState([]);
  const [settings, setSettings] = useState({});
  const [loading, setLoading] = useState(true);
  const [activeVideo, setActiveVideo] = useState(null);
  const [likedVideos, setLikedVideos] = useState({});
  const [isSubscribed, setIsSubscribed] = useState(false);
  const [subCount, setSubCount] = useState(0);
  const [activeCategory, setActiveCategory] = useState(ALL_CATEGORY);
  const [isDescExpanded, setIsDescExpanded] = useState(false);
  const [showShareToast, setShowShareToast] = useState(false);
  const [commentsList, setCommentsList] = useState([]);
  const [commentForm, setCommentForm] = useState({ comment: "" });
  const [replyTarget, setReplyTarget] = useState(null);
  const [now] = useState(() => Date.now());
  const playerRef = useRef(null);
  const activePublisher = activeVideo?.publisher || null;
  const activePublisherRoute = activePublisher?.slug ? `/publishers/${activePublisher.slug}` : "";
  const activeVideoSlug = activeVideo?.slug || activeVideo?.id;

  const formatViews = (viewsCount) => {
    const numericValue = Number(viewsCount || 0);

    const locale = language || undefined;
    const formattedNumber = new Intl.NumberFormat(locale, {
      notation: numericValue >= 1000 ? "compact" : "standard",
      maximumFractionDigits: 1,
    }).format(Math.round(numericValue));

    return `${formattedNumber} ${settings.views_label || ""}`.trim();
  };

  const formatDate = (dateText) => {
    if (!dateText) return "";
    const published = new Date(dateText);
    if (Number.isNaN(published.getTime())) return String(dateText);
    const days = Math.max(
      1,
      Math.round((now - published.getTime()) / 86400000),
    );
    const unit = days < 30 ? "day" : days < 365 ? "month" : "year";
    const count =
      days < 30
        ? days
        : days < 365
          ? Math.round(days / 30)
          : Math.round(days / 365);
    const value = -count;
    return new Intl.RelativeTimeFormat(language || undefined, {
      numeric: "auto",
    }).format(value, unit);
  };
  const formatCommentDate = (dateText) =>
    formatLocalizedDate(dateText, language, t, {
      day: "numeric",
      month: "short",
    });

  // Scroll to top on page load
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  useEffect(() => {
    let active = true;
    setLoading(true);

    Promise.all([videoService.getSettings(), videoService.getVideos()])
      .then(([settingsData, videoItems]) => {
        if (!active) return;
        setSettings(settingsData);
        setVideos(videoItems);
        setActiveVideo((current) => {
          if (!current) return videoItems[0] || null;
          const currentKey = current.slug || current.id;
          return videoItems.find((video) => (video.slug || video.id) === currentKey) || videoItems[0] || null;
        });
        setSubCount(Number(settingsData.subscriber_count || 0));
      })
      .catch(() => {
        if (!active) return;
        setSettings({});
        setVideos([]);
        setActiveVideo(null);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [language]);

  useEffect(() => {
    if (!activeVideoSlug) return;
    let active = true;

    videoService
      .getComments(activeVideoSlug)
      .then((comments) => {
        if (active) setCommentsList(comments || []);
      })
      .catch(() => {
        if (active) setCommentsList([]);
      });

    return () => {
      active = false;
    };
  }, [activeVideoSlug]);

  // Update subscriber count dynamically
  const handleSubscribeToggle = () => {
    if (settings.youtube_channel_url) {
      window.open(settings.youtube_channel_url, "_blank");
    }
    if (isSubscribed) {
      setSubCount((prev) => prev - 1);
    } else {
      setSubCount((prev) => prev + 1);
    }
    setIsSubscribed(!isSubscribed);
  };

  const syncActiveVideoStats = (slug, patch) => {
    setVideos((current) =>
      current.map((video) =>
        (video.slug || video.id) === slug ? { ...video, ...patch } : video,
      ),
    );
    setActiveVideo((current) =>
      current && (current.slug || current.id) === slug ? { ...current, ...patch } : current,
    );
  };

  // Toggle Like state
  const handleLikeToggle = (videoId) => {
    const slug = activeVideo.slug || activeVideo.id;
    setLikedVideos((prev) => ({
      ...prev,
      [videoId]: !prev[videoId],
    }));
    if (!likedVideos[videoId]) {
      videoService
        .recordLike(slug)
        .then((payload) => syncActiveVideoStats(slug, { likesCount: payload.likes_count }))
        .catch(() => {});
    }
  };

  // Copy link to clipboard
  const handleShareClick = () => {
    if (!activeVideo) return;
    const videoUrl = activeVideo.isLocal
      ? window.location.href
      : `https://www.youtube.com/watch?v=${activeVideo.youtubeId}`;
    navigator.clipboard.writeText(videoUrl).then(() => {
      setShowShareToast(true);
      setTimeout(() => {
        setShowShareToast(false);
      }, 3000);
    });
  };

  // Handle active video selection & scroll player into view on small screens
  const handleVideoSelect = (video) => {
    setActiveVideo(video);
    setIsDescExpanded(false);
    const slug = video.slug || video.id;
    videoService
      .recordView(slug)
      .then((payload) => syncActiveVideoStats(slug, { viewsCount: payload.views_count }))
      .catch(() => {});
    if (playerRef.current && window.innerWidth < 1024) {
      playerRef.current.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  };

  const handleReplyClick = (commentId, authorName) => {
    if (!isAuthenticated) {
      document.getElementById("video-comment-form")?.scrollIntoView({ behavior: "smooth" });
      return;
    }

    setReplyTarget({ id: commentId, author: authorName });
    document.getElementById("video-comment-form")?.scrollIntoView({ behavior: "smooth" });
  };

  const handleCommentSubmit = async (event) => {
    event.preventDefault();
    if (!activeVideo || !isAuthenticated || !commentForm.comment) return;

    try {
      const created = await videoService.postComment(activeVideo.slug || activeVideo.id, {
        content: commentForm.comment,
        parent_id: replyTarget?.id || null,
      });
      setCommentsList((current) => [...current, created]);
      setCommentForm({ comment: "" });
      setReplyTarget(null);
    } catch {
      setCommentForm((current) => ({ ...current }));
    }
  };

  // Filter videos for sidebar list
  const filteredVideos = activeCategory === ALL_CATEGORY
    ? videos
    : videos.filter(v => v.category === activeCategory);

  const categoryLabels = settings.category_labels || {};
  const labelForCategory = (category) => categoryLabels[category] || category;
  const categories = [
    {
      value: ALL_CATEGORY,
      label: settings.recommended_label || "",
    },
    ...[...new Set(videos.map((video) => video.category).filter(Boolean))].map(
      (category) => ({
        value: category,
        label: labelForCategory(category),
      }),
    ),
  ];

  if (loading || !activeVideo) {
    return null;
  }

  return (
    <div className="pt-20 bg-white min-h-screen">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          {/* LEFT COLUMN: Main Video Player & Details */}
          <div ref={playerRef} className="lg:col-span-8 flex flex-col gap-6">
            {/* Video Player */}
            <div className="w-full aspect-video rounded-3xl overflow-hidden shadow-lg border border-gray-100 bg-black relative">
              {activeVideo.isLocal ? (
                <video
                  src={activeVideo.videoUrl}
                  controls
                  autoPlay
                  className="w-full h-full object-contain absolute inset-0 bg-black"
                />
              ) : (
                <iframe
                  title={activeVideo.title}
                  src={`https://www.youtube.com/embed/${activeVideo.youtubeId}?autoplay=1&rel=0`}
                  className="w-full h-full border-0 absolute inset-0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                ></iframe>
              )}
            </div>

            {/* Title & Metadata */}
            <div className="flex flex-col gap-3 text-start">
              <h2 className="text-xl md:text-2xl font-extrabold text-navy leading-snug">
                {activeVideo.title}
              </h2>

              {/* Views, Date & Interactive Actions */}
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div className="flex items-center gap-3 text-xs md:text-sm font-semibold text-gray-400">
                  <span className="flex items-center gap-1.5">
                    <Eye className="w-4 h-4 text-primary" />
                    {formatViews(activeVideo.viewsCount)}
                  </span>
                  <span>•</span>
                  <span className="flex items-center gap-1.5">
                    <Calendar className="w-4 h-4" />
                    {formatDate(activeVideo.publishedAt)}
                  </span>
                </div>

                {/* Actions Button Group */}
                <div className="flex items-center gap-2">
                  {/* Like Button */}
                  <button
                    onClick={() => handleLikeToggle(activeVideo.id)}
                    className={`inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs md:text-sm font-extrabold transition-all border cursor-pointer hover:scale-105 active:scale-95 ${
                      likedVideos[activeVideo.id]
                        ? "bg-primary text-white border-primary shadow-md shadow-primary/20"
                        : "bg-gray-50 border-gray-100 text-navy hover:bg-gray-100"
                    }`}
                  >
                    <ThumbsUp
                      className={`w-4 h-4 ${likedVideos[activeVideo.id] ? "fill-current" : ""}`}
                    />
                    {likedVideos[activeVideo.id]
                      ? settings.liked_label || ""
                      : settings.like_label || ""}
                    <span>
                      {Number(activeVideo.likesCount || 0).toLocaleString(
                        language,
                      )}
                    </span>
                  </button>

                  {/* Share Button */}
                  <button
                    onClick={handleShareClick}
                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs md:text-sm font-extrabold transition-all border border-gray-100 bg-gray-50 text-navy hover:bg-gray-100 cursor-pointer hover:scale-105 active:scale-95"
                  >
                    <Share2 className="w-4 h-4" />
                    {settings.share_label || ""}
                  </button>
                </div>
              </div>
            </div>

            {/* Channel Info & Description */}
            <div className="flex flex-col gap-5 text-start">
              {/* Channel Profile Row */}
              <div className="flex items-center justify-between gap-4 bg-primary-light/30 border border-primary-light p-5 rounded-2xl">
                <div className="flex items-center gap-3.5">
                  {logoSrc && hasTranslation("common.logoAlt") && (
                    <div className="w-12 h-12 rounded-full overflow-hidden border-2 border-primary bg-white flex items-center justify-center shadow-sm shrink-0">
                      <img
                        src={logoSrc}
                        alt={t("common.logoAlt")}
                        className="w-9 h-9 object-contain"
                        onError={(e) => {
                          e.currentTarget.style.display = "none";
                        }}
                      />
                    </div>
                  )}
                  <div className="flex flex-col">
                    <div className="flex items-center gap-1.5">
                      <span className="font-extrabold text-sm md:text-base text-navy leading-tight">
                        {activePublisherRoute ? (
                          <Link to={activePublisherRoute} className="hover:text-primary transition-colors">
                            {activePublisher?.name || settings.channel_name || ""}
                          </Link>
                        ) : (
                          activePublisher?.name || settings.channel_name || ""
                        )}
                      </span>
                      <span
                        className="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white shrink-0"
                        title={settings.verified_channel_label || ""}
                      >
                        <Check className="w-2.5 h-2.5 stroke-[3.5]" />
                      </span>
                    </div>
                    <span className="text-[11px] md:text-xs text-gray-400 font-bold">
                      {subCount.toLocaleString(language)}{" "}
                      {settings.subscribers_label || ""}
                    </span>
                  </div>
                </div>

                {/* Subscribe Button */}
                <button
                  onClick={handleSubscribeToggle}
                  className={`inline-flex items-center gap-1.5 px-6 py-3 rounded-full text-xs md:text-sm font-extrabold transition-all duration-300 shadow-xs cursor-pointer ${
                    isSubscribed
                      ? "bg-gray-100 border border-gray-200 text-gray-500 hover:bg-gray-200"
                      : "bg-navy hover:bg-primary text-white hover:shadow-md hover:-translate-y-0.5"
                  }`}
                >
                  {isSubscribed ? (
                    <>
                      <Bell className="w-3.5 h-3.5 fill-current" />
                      {settings.subscribed_label || ""}
                    </>
                  ) : (
                    settings.subscribe_label || ""
                  )}
                </button>
              </div>

              {/* Expandable Description Details */}
              <div
                onClick={() => !isDescExpanded && setIsDescExpanded(true)}
                className={`bg-gray-50 border border-gray-100 rounded-2xl p-5 hover:bg-gray-100/50 transition-all duration-200 ${
                  !isDescExpanded ? "cursor-pointer" : ""
                }`}
              >
                <div className="flex items-center justify-between mb-3 text-xs md:text-sm font-bold text-navy">
                  <div className="flex items-center gap-2">
                    <Award className="w-4 h-4 text-primary" />
                    <span>{settings.description_title || ""}</span>
                  </div>
                  {isDescExpanded && (
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        setIsDescExpanded(false);
                      }}
                      className="text-primary hover:underline flex items-center gap-1 cursor-pointer"
                    >
                      {settings.show_less_label || ""}
                      <ChevronUp className="w-4 h-4" />
                    </button>
                  )}
                </div>

                <p
                  className={`text-gray-500 text-xs md:text-sm leading-relaxed ${!isDescExpanded ? "line-clamp-2" : ""}`}
                >
                  {activeVideo.description}
                </p>

                {!isDescExpanded && (
                  <span className="text-primary text-xs font-extrabold mt-3 inline-block hover:underline">
                    {settings.show_more_label || ""}...
                  </span>
                )}

                {isDescExpanded && (
                  <div className="mt-5 pt-4 border-t border-gray-200/50 flex flex-wrap gap-4 text-xs font-semibold text-gray-400">
                    <span className="bg-white border border-gray-100 px-2.5 py-1 rounded-md">
                      {settings.category_label || ""}
                      :{" "}
                      <strong className="text-navy">
                        {labelForCategory(activeVideo.category)}
                      </strong>
                    </span>
                    <span className="bg-white border border-gray-100 px-2.5 py-1 rounded-md">
                      {settings.duration_label || ""}
                      :{" "}
                      <strong className="text-navy">
                        {activeVideo.duration}
                      </strong>
                    </span>
                    <span className="bg-white border border-gray-100 px-2.5 py-1 rounded-md">
                      {settings.platform_label || ""}
                      :{" "}
                      <strong className="text-navy">
                        {activeVideo.isLocal
                          ? settings.local_label || ""
                          : settings.youtube_label || ""}
                      </strong>
                    </span>
                  </div>
                )}
              </div>

              <div className="bg-white border border-gray-100 rounded-2xl p-5">
                <h3 className="text-lg font-extrabold text-navy mb-5 flex items-center gap-2">
                  <MessageSquare className="w-5 h-5 text-primary" />
                  {commentsList.length} {settings.comments_label || ""}
                </h3>

                <div className="flex flex-col gap-4 mb-6">
                  {commentsList.map((comment) => {
                    const parent = commentsList.find(
                      (item) => item.id === comment.parent_id,
                    );
                    return (
                      <div
                        key={comment.id}
                        className={`flex gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100 ${comment.parent_id ? "ml-8 md:ml-12 border-l-4 border-l-primary" : ""}`}
                      >
                        {comment.parent_id && (
                          <CornerDownRight className="w-5 h-5 text-gray-300 shrink-0 mt-2" />
                        )}
                        <div className="w-10 h-10 rounded-full bg-primary-light text-primary flex items-center justify-center shrink-0 border border-white">
                          <User className="w-5 h-5" />
                        </div>
                        <div className="grow">
                          <div className="flex flex-wrap items-baseline justify-between gap-2 mb-1">
                            <div className="flex items-baseline gap-2">
                              <h5 className="font-bold text-navy text-sm">
                                {comment.author}
                              </h5>
                              <span className="text-xs text-gray-400 font-semibold">
                                {formatCommentDate(comment.date)}
                              </span>
                            </div>
                            <button
                              type="button"
                              onClick={() =>
                                handleReplyClick(comment.id, comment.author)
                              }
                              className="text-xs font-bold text-primary hover:text-primary-hover hover:underline cursor-pointer"
                            >
                              {settings.reply_label || ""}
                            </button>
                          </div>
                          <p className="text-gray-500 text-sm leading-relaxed">
                            {parent && (
                              <span className="text-primary font-semibold mr-1">
                                @{parent.author}
                              </span>
                            )}
                            {comment.text}
                          </p>
                        </div>
                      </div>
                    );
                  })}
                </div>

                <div
                  id="video-comment-form"
                  className="bg-gray-50 border border-gray-100 rounded-2xl p-5"
                >
                  {isAuthenticated ? (
                    <form
                      onSubmit={handleCommentSubmit}
                      className="flex flex-col gap-4"
                    >
                      <div className="flex items-center justify-between gap-3">
                        <h4 className="text-sm font-extrabold text-navy">
                          {settings.form_title || ""}
                        </h4>
                        {!authLoading && user?.name && (
                          <span className="text-xs font-bold text-gray-400">
                            {settings.signed_in_as_label || ""} {user.name}
                          </span>
                        )}
                      </div>
                      {replyTarget && (
                        <div className="flex items-center justify-between rounded-xl bg-white border border-gray-100 px-3 py-2 text-xs font-bold text-gray-500">
                          <span>@{replyTarget.author}</span>
                          <button
                            type="button"
                            onClick={() => setReplyTarget(null)}
                            className="text-primary hover:underline"
                          >
                            {settings.show_less_label || ""}
                          </button>
                        </div>
                      )}
                      <textarea
                        value={commentForm.comment}
                        onChange={(event) =>
                          setCommentForm({ comment: event.target.value })
                        }
                        placeholder={settings.form_comment_label || ""}
                        rows={4}
                        className="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-navy focus:outline-none focus:border-primary bg-white"
                        required
                      />
                      <button
                        type="submit"
                        className="self-start bg-primary hover:bg-primary-hover text-white px-5 py-3 rounded-xl text-xs font-extrabold transition-colors cursor-pointer"
                      >
                        {settings.form_submit_label || ""}
                      </button>
                    </form>
                  ) : (
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                      <div>
                        <h4 className="text-sm font-extrabold text-navy">
                          {settings.sign_in_title || ""}
                        </h4>
                        <p className="text-xs font-semibold text-gray-500 mt-1">
                          {settings.sign_in_text || ""}
                        </p>
                      </div>
                      <a
                        href="/login"
                        className="inline-flex items-center justify-center bg-primary hover:bg-primary-hover text-white px-5 py-3 rounded-xl text-xs font-extrabold transition-colors"
                      >
                        {settings.sign_in_action || ""}
                      </a>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* RIGHT COLUMN: Sidebar Video List & Category Filter */}
          <div className="lg:col-span-4 flex flex-col gap-6 text-start">
            {/* Category Pills Slider/Container */}
            <div className="flex flex-wrap items-center gap-2 pb-2">
              {categories.map((cat) => (
                <button
                  key={cat.value}
                  onClick={() => setActiveCategory(cat.value)}
                  className={`px-4 py-2 rounded-full text-xs font-extrabold transition-all cursor-pointer ${
                    activeCategory === cat.value
                      ? "bg-primary text-white shadow-sm"
                      : "bg-gray-50 border border-gray-100 text-navy hover:bg-gray-100"
                  }`}
                >
                  {cat.label}
                </button>
              ))}
            </div>

            {/* Sidebar Play List */}
            <div className="flex flex-col gap-4">
              <h4 className="text-sm font-extrabold uppercase tracking-wider text-navy mb-1 flex items-center gap-1.5">
                <span className="w-2.5 h-2.5 rounded-full bg-primary inline-block"></span>
                {activeCategory === ALL_CATEGORY
                  ? settings.recommended_label || ""
                  : `${labelForCategory(activeCategory)} ${settings.videos_label || ""}`}
              </h4>

              <div className="flex flex-col gap-3 max-h-170 overflow-y-auto pr-1 scrollbar-thin">
                {filteredVideos.map((video) => {
                  const isActive = video.id === activeVideo.id;
                  return (
                    <div
                      key={video.id}
                      onClick={() => handleVideoSelect(video)}
                      className={`flex gap-3 p-2.5 rounded-2xl border transition-all duration-300 cursor-pointer group ${
                        isActive
                          ? "bg-primary-light/50 border-primary shadow-xs"
                          : "bg-white border-gray-100 hover:bg-gray-50 hover:shadow-xs"
                      }`}
                    >
                      {/* Video Thumbnail */}
                      <div className="relative w-28 md:w-32 aspect-video rounded-xl overflow-hidden bg-gray-100 shrink-0 shadow-xs">
                        {video.poster ? (
                          <img
                            src={video.poster}
                            alt={video.title}
                            className="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300"
                            loading="lazy"
                          />
                        ) : (
                          <div className="w-full h-full bg-gray-100" />
                        )}
                        {video.isLocal && (
                          <div className="absolute inset-0 flex items-center justify-center bg-black/10">
                            <span className="text-white text-[8px] font-extrabold bg-primary/85 rounded-full px-2 py-0.5 shadow-sm uppercase tracking-wider">
                              {settings.local_label || ""}
                            </span>
                          </div>
                        )}
                        {/* Duration badge */}
                        <span className="absolute bottom-1 right-1 bg-black/85 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-xs">
                          {video.duration}
                        </span>

                        {/* Active playing indicator overlay */}
                        {isActive && (
                          <div className="absolute inset-0 bg-primary/20 backdrop-blur-xs flex items-center justify-center">
                            <span className="bg-primary text-white text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full shadow-md">
                              {settings.playing_label || ""}
                            </span>
                          </div>
                        )}
                      </div>

                      {/* Video Info metadata */}
                      <div className="flex flex-col justify-between grow py-0.5 min-w-0">
                        <div className="flex flex-col gap-1">
                          <h5
                            className={`font-bold text-[12px] md:text-[13px] leading-tight line-clamp-2 transition-colors duration-200 ${
                              isActive
                                ? "text-primary font-extrabold"
                                : "text-navy group-hover:text-primary"
                            }`}
                          >
                            {video.title}
                          </h5>
                          <span className="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">
                            {labelForCategory(video.category)}
                          </span>
                        </div>

                        <div className="flex items-center gap-2 text-[10px] font-semibold text-gray-400 mt-1">
                          <span className="flex items-center gap-0.5">
                            <Eye className="w-3 h-3" />
                            {formatViews(video.viewsCount)}
                          </span>
                          <span>•</span>
                          <span>{formatDate(video.publishedAt)}</span>
                        </div>
                      </div>
                    </div>
                  );
                })}

                {filteredVideos.length === 0 && (
                  <div className="text-center py-10 bg-gray-50 border border-gray-100 rounded-2xl">
                    <p className="text-xs text-gray-400 font-semibold">
                      {settings.no_videos_label || ""}
                    </p>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Copy Share Link Toast Message */}
      <AnimatePresence>
        {showShareToast && (
          <motion.div
            initial={{ opacity: 0, y: 50, scale: 0.9 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 30, scale: 0.9 }}
            className="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 bg-navy text-white px-6 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 border border-white/10"
          >
            <div className="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-sm shrink-0">
              <Check className="w-3 h-3 stroke-3" />
            </div>
            <span className="text-xs md:text-sm font-extrabold">
              {settings.link_copied_label || ""}
            </span>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
