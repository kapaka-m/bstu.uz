import React, { useMemo, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { ArrowLeft, Eye, EyeOff, KeyRound, Lock, Mail, ShieldCheck } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { authService } from "../services/authService";
import FormError from "../components/common/FormError";
import { authCmsService } from "../services/authCmsService";

export default function ResetPassword() {
  const { logoSrc, isRtl, language } = useLanguage();
  const [searchParams] = useSearchParams();
  const token = searchParams.get("token") || "";
  const initialEmail = searchParams.get("email") || searchParams.get("amp;email") || "";
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
      title: content?.title || "",
      subtitle: content?.subtitle || "",
      emailLabel: content?.email_label || "",
      emailPlaceholder: content?.email_placeholder || "",
      passwordLabel: content?.password_label || "",
      passwordPlaceholder: content?.password_placeholder || "",
      confirmPasswordLabel: content?.confirm_password_label || "",
      confirmPasswordPlaceholder: content?.confirm_password_placeholder || "",
      submitLabel: content?.submit_label || "",
      loadingLabel: content?.loading_label || "",
      successMessage: content?.success_message || "",
      successTitle: content?.success_title || "",
      backLabel: content?.back_label || "",
      showPassword: content?.show_password_label || "",
      hidePassword: content?.hide_password_label || "",
      requiredMessage: content?.validation_required_message || "",
      mismatchMessage: content?.validation_mismatch_message || "",
      errorMessage: content?.error_message || "",
      logoAlt: content?.logo_alt || "",
    }),
    [content],
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

  const missingLinkData = !token;

  return (
    <div className="relative flex min-h-screen items-center justify-center overflow-hidden bg-gray-50 px-4 pb-20 pt-28">
      <div className="absolute inset-x-0 top-0 h-72 bg-linear-to-b from-primary-light to-transparent" />
      <div className="absolute inset-x-0 bottom-0 h-px bg-linear-to-r from-transparent via-primary/20 to-transparent" />
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6 }}
        className="relative z-10 w-full max-w-md overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-xl"
      >
        <div className="border-b border-gray-100 bg-white px-6 py-7 text-center sm:px-8">
          <Link to="/" className="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white shadow-xs">
            {logoSrc ? (
              <img src={logoSrc} alt={c.logoAlt} className="h-10 w-10 object-contain" />
            ) : (
              <ShieldCheck className="h-7 w-7 text-primary" />
            )}
          </Link>
          <h2 className="text-2xl font-extrabold text-navy">{c.title}</h2>
          {c.subtitle && (
            <p className="mx-auto mt-2 max-w-sm text-xs font-semibold leading-relaxed text-gray-500">
              {c.subtitle}
            </p>
          )}
        </div>

        <div className="p-6 sm:p-8">
        {!submitted ? (
          <>
            {error && <FormError message={error} />}
            {missingLinkData && (
              <div className="mb-5 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-xs font-bold leading-relaxed text-amber-700">
                {c.errorMessage}
              </div>
            )}
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
                    className="w-full bg-white border border-gray-200 hover:border-gray-300 focus:border-primary pl-11 pr-4 rtl:pl-4 rtl:pr-11 py-3.5 rounded-xl text-sm font-semibold text-navy focus:outline-none transition-all shadow-sm"
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
                    className="w-full bg-white border border-gray-200 hover:border-gray-300 focus:border-primary pl-11 pr-12 rtl:pl-12 rtl:pr-11 py-3.5 rounded-xl text-sm font-semibold text-navy focus:outline-none transition-all shadow-sm"
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
                    className="w-full bg-white border border-gray-200 hover:border-gray-300 focus:border-primary pl-11 pr-4 rtl:pl-4 rtl:pr-11 py-3.5 rounded-xl text-sm font-semibold text-navy focus:outline-none transition-all shadow-sm"
                    required
                    minLength={8}
                  />
                </div>
              </div>

              <button
                type="submit"
                disabled={loading || missingLinkData}
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
            <h3 className="mb-2 text-lg font-extrabold text-navy">{c.successTitle}</h3>
            <p className="text-gray-500 text-sm leading-relaxed mb-6">
              {c.successMessage}
            </p>
          </motion.div>
        )}
        </div>

        <div className="border-t border-gray-100 bg-gray-50/70 px-6 py-5 text-center text-sm font-semibold text-gray-500">
          <Link to="/login" className="text-primary hover:underline inline-flex items-center gap-1.5 font-bold">
            <ArrowLeft className={`w-3.5 h-3.5 transition-transform ${isRtl ? "rotate-180" : ""}`} />
            {c.backLabel}
          </Link>
        </div>
      </motion.div>
    </div>
  );
}
