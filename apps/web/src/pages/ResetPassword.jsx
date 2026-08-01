import React, { useMemo, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { ArrowLeft, Eye, EyeOff, KeyRound, Lock, Mail } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { authService } from "../services/authService";
import FormError from "../components/common/FormError";

const REQUIRED_RESET_PASSWORD_TRANSLATIONS = [
  "auth.resetPassword",
  "auth.emailLabel",
  "auth.emailPlaceholder",
  "auth.passwordLabel",
  "auth.passwordPlaceholder",
  "auth.confirmPasswordLabel",
  "auth.passwordsNotMatch",
  "auth.showPassword",
  "auth.hidePassword",
  "auth.backToLogin",
  "auth.resetSent",
  "auth.loginFailed",
];

export default function ResetPassword() {
  const { t, hasTranslation, translationsReady, logoSrc, isRtl } = useLanguage();
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

  const hasRequiredContent = useMemo(
    () =>
      translationsReady &&
      REQUIRED_RESET_PASSWORD_TRANSLATIONS.every((key) => hasTranslation(key)),
    [hasTranslation, translationsReady],
  );

  const handleSubmit = async (event) => {
    event.preventDefault();

    if (!token || !email) {
      setError(t("auth.loginFailed"));
      return;
    }

    if (password !== passwordConfirmation) {
      setError(t("auth.passwordsNotMatch"));
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
      setError(err?.message || t("auth.loginFailed"));
    } finally {
      setLoading(false);
    }
  };

  if (!hasRequiredContent) {
    return null;
  }

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
          {logoSrc && hasTranslation("common.logoAlt") && (
            <Link to="/" className="inline-flex items-center gap-2 mb-3">
              <img src={logoSrc} alt={t("common.logoAlt")} className="h-10" />
            </Link>
          )}
          <h2 className="text-2xl font-extrabold text-navy">{t("auth.resetPassword")}</h2>
        </div>

        {!submitted ? (
          <>
            {error && <FormError message={error} />}
            <form onSubmit={handleSubmit} className="flex flex-col gap-5 text-start">
              <div className="flex flex-col gap-2">
                <label htmlFor="email" className="text-xs font-bold text-navy uppercase tracking-wider">
                  {t("auth.emailLabel")}
                </label>
                <div className="relative flex items-center">
                  <Mail className="absolute left-4 rtl:left-auto rtl:right-4 w-4 h-4 text-gray-400" />
                  <input
                    type="email"
                    id="email"
                    placeholder={t("auth.emailPlaceholder")}
                    value={email}
                    onChange={(event) => setEmail(event.target.value)}
                    className="w-full bg-white border border-gray-100 hover:border-gray-200 focus:border-primary pl-11 pr-4 rtl:pl-4 rtl:pr-11 py-3.5 rounded-xl text-sm focus:outline-none transition-all shadow-sm"
                    required
                  />
                </div>
              </div>

              <div className="flex flex-col gap-2">
                <label htmlFor="password" className="text-xs font-bold text-navy uppercase tracking-wider">
                  {t("auth.passwordLabel")}
                </label>
                <div className="relative flex items-center">
                  <Lock className="absolute left-4 rtl:left-auto rtl:right-4 w-4 h-4 text-gray-400" />
                  <input
                    type={showPassword ? "text" : "password"}
                    id="password"
                    placeholder={t("auth.passwordPlaceholder")}
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
                    aria-label={showPassword ? t("auth.hidePassword") : t("auth.showPassword")}
                  >
                    {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                </div>
              </div>

              <div className="flex flex-col gap-2">
                <label htmlFor="password_confirmation" className="text-xs font-bold text-navy uppercase tracking-wider">
                  {t("auth.confirmPasswordLabel")}
                </label>
                <div className="relative flex items-center">
                  <Lock className="absolute left-4 rtl:left-auto rtl:right-4 w-4 h-4 text-gray-400" />
                  <input
                    type={showPassword ? "text" : "password"}
                    id="password_confirmation"
                    placeholder={t("auth.confirmPasswordLabel")}
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
                {t("auth.resetPassword")}
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
              {t("auth.resetSent")}
            </p>
          </motion.div>
        )}

        <div className="text-center mt-8 pt-6 border-t border-gray-50 text-sm font-semibold text-gray-500">
          <Link to="/login" className="text-primary hover:underline inline-flex items-center gap-1.5 font-bold">
            <ArrowLeft className={`w-3.5 h-3.5 transition-transform ${isRtl ? "rotate-180" : ""}`} />
            {t("auth.backToLogin")}
          </Link>
        </div>
      </motion.div>
    </div>
  );
}
