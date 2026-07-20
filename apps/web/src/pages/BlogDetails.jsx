import React, { useState, useEffect, useMemo } from "react";
import { useParams, Link, useNavigate } from "react-router-dom";
import {
  Calendar,
  User,
  MessageSquare,
  Search,
  Folder,
  Tag,
  CornerDownRight,
  Twitter,
  Facebook,
  Instagram,
} from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { useAuth } from "../context/AuthContext";
import { blogService } from "../services/blogService";

const asText = (value, fallback = "") => {
  if (typeof value === "string" || typeof value === "number") {
    return String(value);
  }
  if (Array.isArray(value)) {
    return value.filter(Boolean).join(" ");
  }
  if (value && typeof value === "object") {
    return Object.values(value).filter(Boolean).join(" ");
  }
  return fallback;
};

const labelText = (value, fallback) => asText(value, asText(fallback));

export default function BlogDetails() {
  const navigate = useNavigate();
  const { id } = useParams();
  const { language } = useLanguage();
  const { user, isAuthenticated, loading: authLoading } = useAuth();
  const [post, setPost] = useState(null);
  const [settings, setSettings] = useState({});
  const [recentPosts, setRecentPosts] = useState([]);
  const [commentsList, setCommentsList] = useState([]);
  const [commentForm, setCommentForm] = useState({
    name: "",
    email: "",
    comment: "",
  });
  const [replyTarget, setReplyTarget] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  useEffect(() => {
    let alive = true;

    const loadDetails = async () => {
      try {
        setLoading(true);
        const nextSettings = await blogService.getSettings();
        const [nextPost, recentResponse] = await Promise.all([
          blogService.getBlogItem(id),
          blogService.getBlog({
            per_page: Number(nextSettings.recent_limit || 5),
            page: 1,
          }),
        ]);
        const nextComments = await blogService.getComments(nextPost.slug || id);

        if (alive) {
          setSettings(nextSettings);
          setPost(nextPost);
          setRecentPosts(recentResponse.items || []);
          setCommentsList(nextComments || []);
        }
      } catch (err) {
        console.error("Failed to load blog details", err);
        if (alive) {
          setPost(null);
        }
      } finally {
        if (alive) setLoading(false);
      }
    };

    loadDetails();

    return () => {
      alive = false;
    };
  }, [id, language]);

  const categories = useMemo(() => {
    const labelByValue = recentPosts.reduce((labels, item) => {
      const value = asText(item.category).toLowerCase();
      if (value && !labels[value]) {
        labels[value] = asText(item.categoryLabel, asText(item.category));
      }
      return labels;
    }, {});

    return Object.entries(labelByValue).map(([value, label]) => {
      return {
        name: label,
        count: recentPosts.filter(
          (item) => asText(item.category).toLowerCase() === value,
        ).length,
        value,
      };
    });
  }, [recentPosts]);

  const tags =
    Array.isArray(settings.tags) && settings.tags.length > 0
      ? settings.tags
          .map((tag) => {
            if (tag && typeof tag === "object") {
              return {
                value: asText(tag.value || tag.label).toLowerCase(),
                label: asText(tag.label || tag.value),
              };
            }
            const value = asText(tag);
            return { value: value.toLowerCase(), label: value };
          })
          .filter((tag) => tag.value && tag.label)
      : categories.map((cat) => ({ value: cat.value, label: cat.name }));

  const handleCommentChange = (e) => {
    setCommentForm((prev) => ({
      ...prev,
      [e.target.name]: e.target.value,
    }));
  };

  const handleCommentSubmit = async (e) => {
    e.preventDefault();
    if (!post || !isAuthenticated || !commentForm.comment) return;

    try {
      const created = await blogService.postComment(post.slug, {
        content: commentForm.comment,
        parent_id: replyTarget?.id || null,
      });
      setCommentsList((prev) => [...prev, created]);
      setCommentForm({ name: "", email: "", comment: "" });
      setReplyTarget(null);
    } catch (err) {
      console.error("Failed to post blog comment", err);
    }
  };

  const handleReplyClick = (commentId, authorName) => {
    if (!isAuthenticated) {
      const formElement = document.getElementById("comment-form");
      if (formElement) {
        formElement.scrollIntoView({ behavior: "smooth" });
      }
      return;
    }

    setReplyTarget({ id: commentId, author: authorName });
    const formElement = document.getElementById("comment-form");
    if (formElement) {
      formElement.scrollIntoView({ behavior: "smooth" });
    }
  };

  if (loading) {
    return (
      <div className="pt-24 bg-white">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-20">
          <div className="text-center py-20 bg-gray-50 rounded-3xl border border-gray-100">
            <p className="text-gray-500 font-semibold">
              {settings.loading_label || ""}
            </p>
          </div>
        </div>
      </div>
    );
  }

  if (!post) {
    return (
      <div className="pt-24 bg-white">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl py-20">
          <Link to="/blog" className="text-primary font-bold hover:underline">
            {labelText(settings.back_to_blog_label, "")}
          </Link>
        </div>
      </div>
    );
  }

  const commentsLabel = labelText(settings.comments_label, "");
  const currentUrl = typeof window !== "undefined" ? window.location.href : "";
  const categoryLabel = asText(post.categoryLabel, asText(post.category));
  const handleNativeShare = async () => {
    if (!currentUrl) return;

    const shareData = {
      title: asText(post.title),
      text: asText(post.excerpt, asText(post.title)),
      url: currentUrl,
    };

    try {
      if (navigator.share) {
        await navigator.share(shareData);
        return;
      }

      if (navigator.clipboard) {
        await navigator.clipboard.writeText(currentUrl);
      }
    } catch {
      // Sharing can be cancelled by the user; no UI error is needed here.
    }
  };
  const commentAuthCopy = {
    title: labelText(settings.comment_login_title, ""),
    text: labelText(settings.comment_login_text, ""),
    action: labelText(settings.comment_login_action, ""),
    signedInAs: labelText(settings.signed_in_as_label, ""),
  };

  return (
    <div className="pt-24 bg-white">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl py-12 md:py-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
          {/* Left: Article Details & Comments */}
          <div className="lg:col-span-8 flex flex-col gap-10">
            <article className="flex flex-col gap-6">
              {/* Image */}
              <div className="rounded-3xl overflow-hidden shadow-lg aspect-video bg-gray-50 border border-gray-100">
                <img
                  src={post.image}
                  alt={post.title}
                  className="w-full h-full object-cover"
                />
              </div>

              {/* Title */}
              <h1 className="text-2xl md:text-4xl font-extrabold text-navy leading-snug">
                {post.title}
              </h1>

              {/* Meta */}
              <div className="flex items-center gap-4 text-xs md:text-sm font-semibold text-gray-400 border-b border-gray-200/50 pb-4">
                <span className="flex items-center gap-1">
                  <User className="w-4 h-4 text-primary" />
                  <span>{post.author}</span>
                </span>
                <span className="flex items-center gap-1">
                  <Calendar className="w-4 h-4 text-primary" />
                  <span>{post.date}</span>
                </span>
                <span className="flex items-center gap-1">
                  <MessageSquare className="w-4 h-4 text-primary" />
                  <span>
                    {commentsList.length} {commentsLabel}
                  </span>
                </span>
              </div>

              {/* Content */}
              <div className="text-gray-500 text-sm md:text-base leading-relaxed flex flex-col gap-6">
                {post.paragraphs.length > 0 ? (
                  post.paragraphs.map((paragraph, idx) => (
                    <p key={idx} className="leading-relaxed">
                      {paragraph}
                    </p>
                  ))
                ) : (
                  <p className="leading-relaxed">{post.excerpt}</p>
                )}
              </div>

              {/* Meta Bottom */}
              <div className="flex flex-wrap items-center gap-6 border-t border-gray-200/50 pt-4 text-xs md:text-sm text-gray-400 font-semibold">
                <span className="flex items-center gap-1.5">
                  <Folder className="w-4 h-4 text-primary" />
                  <span className="text-navy">{categoryLabel}</span>
                </span>
                <span className="flex items-center gap-1.5">
                  <Tag className="w-4 h-4 text-primary" />
                  <div className="flex gap-2">
                    <Link
                      to="/blog"
                      state={{ category: asText(post.category).toLowerCase() }}
                      className="bg-gray-50 hover:bg-primary/5 hover:text-primary transition-colors border border-gray-100 px-2.5 py-1 rounded-lg text-gray-500 font-bold"
                    >
                      {categoryLabel}
                    </Link>
                  </div>
                </span>
              </div>
            </article>

            {/* Author Section */}
            <div
              dir={language === "ar" ? "ltr" : undefined}
              className={`bg-primary-light border border-gray-100 p-8 rounded-3xl flex gap-6 ${language === "ar" ? "flex-row items-center justify-end text-right" : "flex-col sm:flex-row items-center sm:items-start"}`}
            >
              {post.authorImage ? (
                <img
                  src={post.authorImage}
                  alt={post.author}
                  className={`w-20 h-20 rounded-full object-cover border-2 border-white shadow-md shrink-0 ${language === "ar" ? "order-2" : ""}`}
                />
              ) : (
                <div
                  className={`w-20 h-20 rounded-full border-2 border-white shadow-md shrink-0 bg-white text-primary flex items-center justify-center ${language === "ar" ? "order-2" : ""}`}
                  aria-label={post.author}
                >
                  <User className="w-8 h-8" />
                </div>
              )}
              <div
                dir={language === "ar" ? "rtl" : undefined}
                className={`${language === "ar" ? "order-1 text-right items-end shrink-0" : "text-center sm:text-left items-center sm:items-start grow w-full"} flex flex-col`}
              >
                <h4 className="text-lg font-extrabold text-navy mb-1">
                  {post.author}
                </h4>
                <div
                  className={`flex items-center ${language === "ar" ? "justify-end" : "justify-center sm:justify-start"} gap-2.5 mb-3`}
                >
                  <a
                    href={`https://twitter.com/intent/tweet?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(post.title)}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-gray-400 hover:text-primary"
                  >
                    <Twitter className="w-4 h-4" />
                  </a>
                  <a
                    href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-gray-400 hover:text-primary"
                  >
                    <Facebook className="w-4 h-4" />
                  </a>
                  <button
                    type="button"
                    onClick={handleNativeShare}
                    aria-label={asText(post.title)}
                    className="text-gray-400 hover:text-primary"
                  >
                    <Instagram className="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            {/* Comments List */}
            <div>
              <h3 className="text-xl font-bold text-navy mb-6">
                {commentsList.length} {commentsLabel}
              </h3>
              <div className="flex flex-col gap-6">
                {commentsList.map((comment) => {
                  const parent = commentsList.find(
                    (item) => item.id === comment.parent_id,
                  );
                  return (
                    <div
                      key={comment.id}
                      className={`flex gap-4 p-5 rounded-3xl bg-white border border-gray-50 shadow-sm ${comment.parent_id ? "ml-8 md:ml-12 border-l-4 border-l-primary" : ""}`}
                    >
                      {comment.parent_id && (
                        <CornerDownRight className="w-5 h-5 text-gray-300 shrink-0 mt-2" />
                      )}
                      {comment.avatar ? (
                        <img
                          src={comment.avatar}
                          alt={comment.author}
                          className="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm shrink-0"
                        />
                      ) : (
                        <div
                          className="w-12 h-12 rounded-full border-2 border-white shadow-sm shrink-0 bg-primary-light text-primary flex items-center justify-center"
                          aria-label={comment.author}
                        >
                          <User className="w-5 h-5" />
                        </div>
                      )}
                      <div className="grow">
                        <div className="flex flex-wrap items-baseline justify-between gap-2 mb-2">
                          <div className="flex items-baseline gap-2">
                            <h5 className="font-bold text-navy text-sm md:text-base">
                              {comment.author}
                            </h5>
                            <span className="text-xs text-gray-400 font-semibold">
                              {comment.date
                                ? new Date(comment.date).toLocaleDateString(
                                    language === "ar" ? "ar" : language,
                                  )
                                : ""}
                            </span>
                          </div>
                          <button
                            onClick={() =>
                              handleReplyClick(comment.id, comment.author)
                            }
                            className="text-xs font-bold text-primary hover:text-primary-hover hover:underline cursor-pointer"
                          >
                            {labelText(settings.reply_label, "")}
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
            </div>

            {/* Comment Form */}
            <div
              id="comment-form"
              className="bg-primary-light border border-gray-100 p-8 rounded-3xl"
            >
              <h3 className="text-xl font-bold text-navy mb-2">
                {labelText(settings.form_title, "")}
              </h3>
              {!authLoading && !isAuthenticated ? (
                <div className="mt-4 bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                  <h4 className="text-base font-extrabold text-navy mb-2">
                    {commentAuthCopy.title}
                  </h4>
                  <p className="text-sm text-gray-500 leading-relaxed mb-4">
                    {commentAuthCopy.text}
                  </p>
                  <Link
                    to="/student/login"
                    state={{ from: `/blog/${post.slug}` }}
                    className="inline-flex items-center justify-center bg-primary hover:bg-primary-hover text-white px-5 py-3 rounded-xl text-sm font-semibold transition-all duration-300 shadow-md shadow-primary/20 hover:shadow-primary/30"
                  >
                    {commentAuthCopy.action}
                  </Link>
                </div>
              ) : (
                <>
                  {replyTarget && (
                    <div className="flex items-center justify-between bg-primary/5 text-primary text-xs font-bold px-4 py-2 rounded-lg mb-4">
                      <span>
                        {labelText(settings.form_replying_to_label, "")}{" "}
                        @{replyTarget.author}
                      </span>
                      <button
                        onClick={() => setReplyTarget(null)}
                        className="text-gray-400 hover:text-primary cursor-pointer"
                      >
                        {labelText(settings.form_cancel_label, "")}
                      </button>
                    </div>
                  )}
                  <div className="mt-4 mb-4 bg-white border border-gray-100 rounded-xl px-4 py-3 text-xs font-bold text-gray-500">
                    {commentAuthCopy.signedInAs}:{" "}
                    <span className="text-navy">
                      {user?.name || user?.email}
                    </span>
                  </div>
                  <form
                    onSubmit={handleCommentSubmit}
                    className="flex flex-col gap-6"
                  >
                    <div className="flex flex-col gap-2">
                      <label
                        htmlFor="comment"
                        className="text-xs font-bold text-navy uppercase tracking-wider"
                      >
                        {labelText(settings.form_comment_label, "")}{" "}
                        *
                      </label>
                      <textarea
                        id="comment"
                        name="comment"
                        rows={4}
                        value={commentForm.comment}
                        onChange={handleCommentChange}
                        className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm resize-none"
                        placeholder={
                          replyTarget
                            ? `${labelText(settings.form_reply_placeholder, "")} @${replyTarget.author}...`
                            : labelText(
                                settings.form_comment_placeholder,
                                "",
                              )
                        }
                        required
                      />
                    </div>

                    <div>
                      <button
                        type="submit"
                        className="bg-primary hover:bg-primary-hover text-white px-6 py-3.5 rounded-xl text-sm font-semibold transition-all duration-300 shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 cursor-pointer"
                      >
                        {labelText(settings.form_submit_label, "")}
                      </button>
                    </div>
                  </form>
                </>
              )}
            </div>
          </div>

          {/* Right: Sidebar */}
          <div className="lg:col-span-4 flex flex-col gap-8">
            {/* Search Box */}
            <div className="bg-primary-light border border-gray-100 p-6 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-4">
                {labelText(settings.search_title, "")}
              </h4>
              <div className="flex bg-white border border-gray-200/50 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <input
                  id="blog-details-search-input"
                  name="blog_search"
                  type="text"
                  autoComplete="off"
                  placeholder={labelText(
                    settings.search_placeholder,
                    "",
                  )}
                  className="grow px-4 py-3 text-sm focus:outline-none"
                  onKeyDown={(e) => {
                    if (e.key === "Enter") {
                      navigate("/blog", { state: { search: e.target.value } });
                    }
                  }}
                />
                <div className="px-4 py-3 text-gray-400 flex items-center justify-center border-s border-gray-100 bg-gray-50">
                  <Search className="w-4 h-4" />
                </div>
              </div>
            </div>

            {/* Categories */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {labelText(
                  settings.categories_title,
                  "",
                )}
              </h4>
              <ul className="flex flex-col gap-3 font-semibold text-sm">
                {categories.map((cat) => (
                  <li key={cat.value}>
                    <Link
                      to="/blog"
                      state={{ category: cat.value }}
                      className="w-full flex items-center justify-between py-2 text-gray-500 hover:text-primary transition-all"
                    >
                      <span>{cat.name}</span>
                      <span className="bg-white px-2.5 py-1 rounded-lg border border-gray-100 text-xs text-gray-400">
                        ({cat.count})
                      </span>
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            {/* Recent Posts */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {labelText(
                  settings.recent_title,
                  "",
                )}
              </h4>
              <div className="flex flex-col gap-5">
                {recentPosts.map((item) => (
                  <div key={item.slug || item.id} className="flex gap-4">
                    <img
                      src={item.image}
                      alt={item.title}
                      className="w-16 h-16 rounded-xl object-cover shrink-0 bg-gray-100 border border-white shadow-sm"
                    />
                    <div className="flex flex-col justify-center">
                      <h5 className="font-bold text-sm text-navy hover:text-primary transition-colors line-clamp-2 leading-tight">
                        <Link to={`/blog/${item.slug}`}>{item.title}</Link>
                      </h5>
                      <span className="text-xs text-gray-400 mt-1 font-semibold">
                        {item.date}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Tags */}
            <div className="bg-primary-light border border-gray-100 p-8 rounded-3xl">
              <h4 className="text-base font-extrabold text-navy mb-5 border-b border-gray-200/50 pb-3">
                {labelText(settings.tags_title, "")}
              </h4>
              <div className="flex flex-wrap gap-2">
                {tags.map((tag) => (
                  <Link
                    key={tag.value}
                    to="/blog"
                    state={{ category: tag.value }}
                    className="px-3 py-1.5 bg-white hover:bg-primary hover:text-white border border-gray-200/30 rounded-lg text-xs font-bold text-gray-400 transition-colors shadow-sm"
                  >
                    {tag.label}
                  </Link>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
