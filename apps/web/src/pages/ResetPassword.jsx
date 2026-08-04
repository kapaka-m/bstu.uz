import React, { useMemo, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { ArrowLeft, Eye, EyeOff, KeyRound, Lock, Mail } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { authService } from "../services/authService";
import FormError from "../components/common/FormError";
import { authCmsService } from "../services/authCmsService";

export default function ResetPassword() {
  const { t, hasTranslation, logoSrc, isRtl, language } = useLanguage();
  const [searchParams] = useSearchParams();
  const token = searchParams.get("token") || "";
  const initialEmail = searchParams.get("email") || "";
  const [email, setEmail] = useState(initialEmail);
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [content, setContent] = useState();

  React.useEffect(() => {
    let alive = true;
    authCmsService
      .get(language)
      .then((payload) => {
        if (alive) setContent(payload?.pages?.reset_password || null);
      })
      .catch((err) => {
        console.error("Reset password CMS load error", err);
        if (alive) setContent(null);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  const c = useMemo(
    () => ({
      title: content?.title || t("auth.resetPassword"),
      emailLabel: content?.email_label || t("auth.emailLabel"),
      emailPlaceholder: content?.email_placeholder || t("auth.emailPlaceholder"),
      passwordLabel: content?.password_label || t("auth.passwordLabel"),
      passwordPlaceholder: content?.password_placeholder || t("auth.passwordPlaceholder"),
      confirmPasswordLabel: content?.confirm_password_label || t("auth.confirmPasswordLabel"),
      confirmPasswordPlaceholder: content?.confirm_password_placeholder || t("auth.confirmPasswordLabel"),
      submitLabel: content?.submit_label || t("auth.resetPassword"),
      loadingLabel: content?.loading_label || t("auth.resetPassword"),
      successMessage: content?.success_message || t("auth.resetSent"),
      backLabel: content?.back_label || t("auth.backToLogin"),
      showPassword: content?.show_password_label || t("auth.showPassword"),
      hidePassword: content?.hide_password_label || t("auth.hidePassword"),
      requiredMessage: content?.validation_required_message || t("auth.loginFailed"),
      mismatchMessage: content?.validation_mismatch_message || t("auth.passwordsNotMatch"),
      errorMessage: content?.error_message || t("auth.loginFailed"),
      logoAlt: content?.logo_alt || (hasTranslation("common.logoAlt") ? t("common.logoAlt") : "BSTU logo"),
    }),
    [content, hasTranslation, t],
  );

  if (content === undefined) {
    return null;
  }

  const handleSubmit = async (event) => {
    event.preventDefault();

    if (!token || !email) {
      setError(c.requiredMessage);
      return;
    }

    if (password !== passwordConfirmation) {
      setError(c.mismatchMessage);
      return;
    }

    try {
      setLoading(true);
      setError("");
      await authService.resetPassword({
        email,
        token,
        password,
        password_confirmation: passwordConfirmation,
      });
      setSubmitted(true);
    } catch (err) {
      console.error("Student password reset error", err);
      setError(err?.message || c.errorMessage);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen pt-28 pb-20 flex items-center justify-center bg-linear-to-br from-primary-light via-white to-[#eef4ff] relative overflow-hidden">
      <div className="absolute top-20 left-10 w-72 h-72 bg-primary/5 rounded-full blur-3xl" />
      <div className="absolute bottom-10 right-10 w-96 h-96 bg-blue-400/5 rounded-full blur-3xl" />

      <motion.div
        initial={{ opacity: 0, y: 30 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6 }}
        className="w-full max-w-md bg-white border border-gray-100 p-8 md:p-10 rounded-3xl shadow-xl hover:shadow-2xl transition-shadow relative z-10 mx-4"
      >
        <div className="text-center mb-8">
          {logoSrc && (
            <Link to="/" className="inline-flex items-center gap-2 mb-3">
              <img src={logoSrc} alt={c.logoAlt} className="h-10" />
            </Link>
          )}
          <h2 className="text-2xl font-extrabold text-navy">{c.title}</h2>
        </div>

        {!submitted ? (
          <>
            {error && <FormError message={error} />}
            <form onSubmit={handleSubmit} className="flex flex-col gap-5 text-start">
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
                    onChange={(event) => setEmail(event.target.value)}
                    className="w-full bg-white border border-gray-100 hover:border-gray-200 focus:border-primary pl-11 pr-4 rtl:pl-4 rtl:pr-11 py-3.5 rounded-xl text-sm focus:outline-none transition-all shadow-sm"
                    required
                  />
                </div>
              </div>

              <div className="flex flex-col gap-2">
                <label htmlFor="password" className="text-xs font-bold text-navy uppercase tracking-wider">
                  {c.passwordLabel}
                </label>
                <div className="relative flex items-center">
                  <Lock className="absolute left-4 rtl:left-auto rtl:right-4 w-4 h-4 text-gray-400" />
                  <input
                    type={showPassword ? "text" : "password"}
                    id="password"
                    placeholder={c.passwordPlaceholder}
                    value={password}
                    onChange={(event) => setPassword(event.target.value)}
                    className="w-full bg-white border border-gray-100 hover:border-gray-200 focus:border-primary pl-11 pr-12 rtl:pl-12 rtl:pr-11 py-3.5 rounded-xl text-sm focus:outline-none transition-all shadow-sm"
                    required
                    minLength={8}
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword((value) => !value)}
                    className="absolute right-4 rtl:right-auto rtl:left-4 text-gray-400 hover:text-primary transition-colors"
                    aria-label={showPassword ? c.hidePassword : c.showPassword}
                  >
                    {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                </div>
              </div>

              <div className="flex flex-col gap-2">
                <label htmlFor="password_confirmation" className="text-xs font-bold text-navy uppercase tracking-wider">
                  {c.confirmPasswordLabel}
                </label>
                <div className="relative flex items-center">
                  <Lock className="absolute left-4 rtl:left-auto rtl:right-4 w-4 h-4 text-gray-400" />
                  <input
                    type={showPassword ? "text" : "password"}
                    id="password_confirmation"
                    placeholder={c.confirmPasswordPlaceholder}
                    value={passwordConfirmation}
                    onChange={(event) => setPasswordConfirmation(event.target.value)}
                    className="w-full bg-white border border-gray-100 hover:border-gray-200 focus:border-primary pl-11 pr-4 rtl:pl-4 rtl:pr-11 py-3.5 rounded-xl text-sm focus:outline-none transition-all shadow-sm"
                    required
                    minLength={8}
                  />
                </div>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="w-full mt-2 inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white py-3.5 rounded-xl text-sm font-semibold transition-all duration-300 shadow-md shadow-primary/20 hover:shadow-primary/30 cursor-pointer"
              >
                {loading ? c.loadingLabel : c.submitLabel}
                <KeyRound className="w-4 h-4" />
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
              <KeyRound className="w-10 h-10" />
            </div>
            <p className="text-gray-500 text-sm leading-relaxed mb-6">
              {c.successMessage}
            </p>
          </motion.div>
        )}

        <div className="text-center mt-8 pt-6 border-t border-gray-50 text-sm font-semibold text-gray-500">
          <Link to="/login" className="text-primary hover:underline inline-flex items-center gap-1.5 font-bold">
            <ArrowLeft className={`w-3.5 h-3.5 transition-transform ${isRtl ? "rotate-180" : ""}`} />
            {c.backLabel}
          </Link>
        </div>
      </motion.div>
    </div>
  );
}
