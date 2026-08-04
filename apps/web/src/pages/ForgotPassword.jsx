import React, { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { Mail, ArrowLeft, KeyRound, CheckCircle } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { authService } from "../services/authService";
import FormError from "../components/common/FormError";
import { authCmsService } from "../services/authCmsService";

export default function ForgotPassword() {
  const { t, hasTranslation, logoSrc, isRtl, language } = useLanguage();
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
      title: content?.title || t("auth.resetPassword"),
      subtitle: content?.subtitle || t("auth.forgotPrompt"),
      emailLabel: content?.email_label || t("auth.emailLabel"),
      emailPlaceholder: content?.email_placeholder || t("auth.emailPlaceholder"),
      submitLabel: content?.submit_label || t("auth.sendReset"),
      loadingLabel: content?.loading_label || t("auth.sendingInstructions"),
      successTitle: content?.success_title || t("auth.checkEmailTitle"),
      successMessage: content?.success_message || t("auth.checkEmailDesc"),
      backLabel: content?.back_label || t("auth.backToLogin"),
      errorMessage: content?.error_message || "",
      logoAlt: content?.logo_alt || (hasTranslation("common.logoAlt") ? t("common.logoAlt") : "BSTU logo"),
    }),
    [content, hasTranslation, t],
  );

  if (content === undefined) {
    return null;
  }

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
      setError(
        err?.data?.message === "auth.emailMustApplyFirst"
            ? t("auth.emailMustApplyFirst")
          : err?.message || c.errorMessage,
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen pt-28 pb-20 flex items-center justify-center bg-linear-to-br from-primary-light via-white to-[#eef4ff] relative overflow-hidden">
      {/* Decorative Circles */}
      <div className="absolute top-20 left-10 w-72 h-72 bg-primary/5 rounded-full blur-3xl" />
      <div className="absolute bottom-10 right-10 w-96 h-96 bg-blue-400/5 rounded-full blur-3xl" />

      <motion.div
        initial={{ opacity: 0, y: 30 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6 }}
        className="w-full max-w-md bg-white border border-gray-100 p-8 md:p-10 rounded-3xl shadow-xl hover:shadow-2xl transition-shadow relative z-10 mx-4"
      >
        {/* Brand logo header */}
        <div className="text-center mb-8">
          {logoSrc && (
            <Link to="/" className="inline-flex items-center gap-2 mb-3">
              <img src={logoSrc} alt={c.logoAlt} className="h-10" />
            </Link>
          )}
          <h2 className="text-2xl font-extrabold text-navy">{c.title}</h2>
          <p className="text-gray-400 text-xs md:text-sm font-semibold mt-1">
            {submitted ? c.successTitle : c.subtitle}
          </p>
        </div>

        {!submitted ? (
          <>
            {error && <FormError message={error} />}
            <form onSubmit={handleSubmit} className="flex flex-col gap-5 text-start">
              {/* Email */}
              <div className="flex flex-col gap-2">
                <label htmlFor="email" className="text-xs font-bold text-navy uppercase tracking-wider">
                  {c.emailLabel}
                </label>
                <div className="relative flex items-center">
                  <Mail className="absolute left-4 rtl:left-auto rtl:right-4 w-4 h-4 text-gray-400" />
                  <input
                    type="email"
                    id="email"
                    placeholder={c.emailPlaceholder}
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full bg-white border border-gray-100 hover:border-gray-200 focus:border-primary pl-11 pr-4 rtl:pl-4 rtl:pr-11 py-3.5 rounded-xl text-sm focus:outline-none transition-all shadow-sm"
                    required
                  />
                </div>
              </div>

              {/* Submit */}
              <button
                type="submit"
                disabled={loading}
                className="w-full mt-2 inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white py-3.5 rounded-xl text-sm font-semibold transition-all duration-300 shadow-md shadow-primary/20 hover:shadow-primary/30 cursor-pointer"
              >
                {loading ? c.loadingLabel : c.submitLabel}
                {!loading && <KeyRound className="w-4 h-4" />}
              </button>
            </form>
          </>
        ) : (
          <motion.div
            initial={{ scale: 0.9, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            className="flex flex-col items-center text-center py-4"
          >
            <div className="w-16 h-16 rounded-full bg-green-50 text-green-500 flex items-center justify-center mb-4">
              <CheckCircle className="w-10 h-10" />
            </div>
            <h4 className="text-lg font-bold text-navy mb-2">{c.successTitle}</h4>
            <p className="text-gray-500 text-sm leading-relaxed mb-6">
              {c.successMessage.replace("{email}", email)}
            </p>
          </motion.div>
        )}

        {/* Back to Login */}
        <div className="text-center mt-8 pt-6 border-t border-gray-50 text-sm font-semibold text-gray-500">
          <Link to="/login" className="text-primary hover:underline inline-flex items-center gap-1.5 font-bold">
            <ArrowLeft className={`w-3.5 h-3.5 transition-transform ${isRtl ? 'rotate-180' : ''}`} /> 
            {c.backLabel}
          </Link>
        </div>
      </motion.div>
    </div>
  );
}
