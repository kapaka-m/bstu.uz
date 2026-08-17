import React, { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { Mail, ArrowLeft, KeyRound, CheckCircle } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { authService } from "../services/authService";
import FormError from "../components/common/FormError";
import { authCmsService } from "../services/authCmsService";

function ForgotPasswordSkeleton({ logoSrc, logoAlt }) {
  return (
    <div className="relative z-10 mx-4 w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-xl sm:p-8 md:p-10">
      <div className="mb-8 flex flex-col items-center gap-3">
        {logoSrc && <img src={logoSrc} alt={logoAlt} className="h-10 w-auto object-contain" />}
        <div className="h-7 w-44 rounded-lg bg-gray-100" />
        <div className="h-4 w-64 max-w-full rounded-lg bg-gray-100" />
      </div>
      <div className="space-y-5">
        <div className="space-y-2">
          <div className="h-3 w-24 rounded bg-gray-100" />
          <div className="h-12 rounded-xl bg-gray-100" />
        </div>
        <div className="h-12 rounded-xl bg-primary/10" />
      </div>
    </div>
  );
}

export default function ForgotPassword() {
  const { logoSrc, isRtl, language } = useLanguage();
  const [email, setEmail] = useState("");
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [content, setContent] = useState();

  useEffect(() => {
    let alive = true;
    authCmsService
      .get(language)
      .then((payload) => {
        if (alive) setContent(payload?.pages?.forgot_password || null);
      })
      .catch((err) => {
        console.error("Forgot password CMS load error", err);
        if (alive) setContent(null);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  const c = useMemo(
    () => ({
      title: content?.title || "",
      subtitle: content?.subtitle || "",
      emailLabel: content?.email_label || "",
      emailPlaceholder: content?.email_placeholder || "",
      submitLabel: content?.submit_label || "",
      loadingLabel: content?.loading_label || "",
      successTitle: content?.success_title || "",
      successMessage: content?.success_message || "",
      backLabel: content?.back_label || "",
      errorMessage: content?.error_message || "",
      logoAlt: content?.logo_alt || "",
    }),
    [content],
  );

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!email) return;

    try {
      setLoading(true);
      setError("");
      await authService.forgotPassword(email);
      setSubmitted(true);
    } catch (err) {
      console.error("Student password reset request error", err);
      setError(err?.message || c.errorMessage);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="relative flex min-h-screen items-center justify-center overflow-hidden bg-linear-to-br from-primary-light via-white to-[#eef4ff] px-4 py-24 sm:py-28">
      <div className="absolute start-0 top-24 h-64 w-64 -translate-x-1/2 rounded-full bg-primary/5 blur-3xl rtl:translate-x-1/2" />
      <div className="absolute bottom-8 end-0 h-72 w-72 translate-x-1/2 rounded-full bg-blue-400/5 blur-3xl rtl:-translate-x-1/2" />

      {content === undefined ? (
        <ForgotPasswordSkeleton logoSrc={logoSrc} logoAlt={c.logoAlt} />
      ) : (
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          className="relative z-10 w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-xl transition-shadow hover:shadow-2xl sm:p-8 md:p-10"
        >
          <div className="mb-8 text-center">
            {logoSrc && (
              <Link to="/" className="mb-3 inline-flex items-center gap-2">
                <img src={logoSrc} alt={c.logoAlt} className="h-10 w-auto object-contain" />
              </Link>
            )}
            <h2 className="break-words text-2xl font-extrabold text-navy">{c.title}</h2>
            <p className="mt-1 break-words text-xs font-semibold leading-relaxed text-gray-400 md:text-sm">
              {submitted ? c.successTitle : c.subtitle}
            </p>
          </div>

          {!submitted ? (
            <>
              {error && <FormError message={error} />}
              <form onSubmit={handleSubmit} className="flex min-w-0 flex-col gap-5 text-start">
                <div className="flex flex-col gap-2">
                  <label htmlFor="email" className="text-xs font-bold uppercase tracking-wider text-navy">
                    {c.emailLabel}
                  </label>
                  <div className="relative flex items-center">
                    <Mail className="absolute left-4 h-4 w-4 text-gray-400 rtl:left-auto rtl:right-4" />
                    <input
                      type="email"
                      id="email"
                      placeholder={c.emailPlaceholder}
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      className="w-full min-w-0 rounded-xl border border-gray-100 bg-white py-3.5 pl-11 pr-4 text-sm shadow-sm transition-all hover:border-gray-200 focus:border-primary focus:outline-none rtl:pl-4 rtl:pr-11"
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  disabled={loading}
                  className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3.5 text-sm font-semibold text-white shadow-md shadow-primary/20 transition-all duration-300 hover:bg-primary-hover hover:shadow-primary/30 disabled:bg-primary/50"
                >
                  <span className="min-w-0 break-words">{loading ? c.loadingLabel : c.submitLabel}</span>
                  {!loading && <KeyRound className="h-4 w-4" />}
                </button>
              </form>
            </>
          ) : (
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              className="flex flex-col items-center py-4 text-center"
            >
              <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-50 text-green-500">
                <CheckCircle className="h-10 w-10" />
              </div>
              <h4 className="mb-2 break-words text-lg font-bold text-navy">{c.successTitle}</h4>
              <p className="mb-6 break-words text-sm leading-relaxed text-gray-500">
                {c.successMessage.replace("{email}", email)}
              </p>
            </motion.div>
          )}

          <div className="mt-8 border-t border-gray-50 pt-6 text-center text-sm font-semibold text-gray-500">
            <Link to="/login" className="inline-flex items-center gap-1.5 font-bold text-primary hover:underline">
              <ArrowLeft className={`h-3.5 w-3.5 transition-transform ${isRtl ? "rotate-180" : ""}`} />
              <span className="min-w-0 break-words">{c.backLabel}</span>
            </Link>
          </div>
        </motion.div>
      )}
    </div>
  );
}
