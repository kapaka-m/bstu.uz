import React, { useState } from "react";
import { Link } from "react-router-dom";
import { Mail, ArrowLeft, KeyRound, CheckCircle } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { authService } from "../services/authService";
import FormError from "../components/common/FormError";

const REQUIRED_FORGOT_PASSWORD_TRANSLATIONS = [
  "auth.resetPassword",
  "auth.forgotPrompt",
  "auth.emailLabel",
  "auth.emailPlaceholder",
  "auth.sendReset",
  "auth.sendingInstructions",
  "auth.backToLogin",
  "auth.resetSent",
  "auth.emailMustApplyFirst",
  "auth.checkEmailTitle",
  "auth.checkEmailDesc",
];

export default function ForgotPassword() {
  const { t, hasTranslation, translationsReady, logoSrc, isRtl } = useLanguage();
  const [email, setEmail] = useState("");
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

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
          : err?.message || "",
      );
    } finally {
      setLoading(false);
    }
  };

  const hasRequiredContent =
    translationsReady &&
    REQUIRED_FORGOT_PASSWORD_TRANSLATIONS.every((key) => hasTranslation(key));

  if (!hasRequiredContent) {
    return null;
  }

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
          {logoSrc && hasTranslation("common.logoAlt") && (
            <Link to="/" className="inline-flex items-center gap-2 mb-3">
              <img src={logoSrc} alt={t("common.logoAlt")} className="h-10" />
            </Link>
          )}
          <h2 className="text-2xl font-extrabold text-navy">{t("auth.resetPassword")}</h2>
          <p className="text-gray-400 text-xs md:text-sm font-semibold mt-1">
            {submitted
              ? t("auth.resetSent")
              : t("auth.forgotPrompt")}
          </p>
        </div>

        {!submitted ? (
          <>
            {error && <FormError message={error} />}
            <form onSubmit={handleSubmit} className="flex flex-col gap-5 text-start">
              {/* Email */}
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
                {loading ? t("auth.sendingInstructions") : t("auth.sendReset")}
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
            <h4 className="text-lg font-bold text-navy mb-2">{t("auth.checkEmailTitle")}</h4>
            <p className="text-gray-500 text-sm leading-relaxed mb-6">
              {t("auth.checkEmailDesc").replace("{email}", email)}
            </p>
          </motion.div>
        )}

        {/* Back to Login */}
        <div className="text-center mt-8 pt-6 border-t border-gray-50 text-sm font-semibold text-gray-500">
          <Link to="/login" className="text-primary hover:underline inline-flex items-center gap-1.5 font-bold">
            <ArrowLeft className={`w-3.5 h-3.5 transition-transform ${isRtl ? 'rotate-180' : ''}`} /> 
            {t("auth.backToLogin")}
          </Link>
        </div>
      </motion.div>
    </div>
  );
}
