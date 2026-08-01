import React, { useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { Mail, Lock, LogIn, ArrowRight, Eye, EyeOff } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { useAuth } from "../context/AuthContext";
import FormError from "../components/common/FormError";

const REQUIRED_LOGIN_TRANSLATIONS = [
  "auth.welcomeBack",
  "auth.loginPrompt",
  "auth.emailLabel",
  "auth.emailPlaceholder",
  "auth.passwordLabel",
  "auth.passwordPlaceholder",
  "auth.forgotPassword",
  "auth.logIn",
  "auth.dontHaveAccount",
  "auth.apply",
  "auth.showPassword",
  "auth.hidePassword",
  "auth.loginFailed",
];

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login, clearSession } = useAuth();
  const { t, hasTranslation, translationsReady, logoSrc, isRtl } = useLanguage();
  const [formData, setFormData] = useState({ email: "", password: "" });
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.email || !formData.password) return;

    try {
      setLoading(true);
      setError("");
      const user = await login(formData.email, formData.password, "student");
      const roles = user?.roles || [];
      if (!roles.includes("student") || roles.includes("apanel")) {
        clearSession();
        setError(t("auth.loginFailed"));
        return;
      }
      navigate(location.state?.from || "/student/dashboard");
    } catch (err) {
      console.error("Student login error", err);
      setError(err?.message || t("auth.loginFailed"));
    } finally {
      setLoading(false);
    }
  };

  const hasRequiredContent =
    translationsReady &&
    REQUIRED_LOGIN_TRANSLATIONS.every((key) => hasTranslation(key));

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
          <h2 className="text-2xl font-extrabold text-navy">{t("auth.welcomeBack")}</h2>
          <p className="text-gray-400 text-xs md:text-sm font-semibold mt-1">
            {t("auth.loginPrompt")}
          </p>
        </div>

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
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                className="w-full bg-white border border-gray-100 hover:border-gray-200 focus:border-primary pl-11 pr-4 rtl:pl-4 rtl:pr-11 py-3.5 rounded-xl text-sm focus:outline-none transition-all shadow-sm"
                required
              />
            </div>
          </div>

          {/* Password */}
          <div className="flex flex-col gap-2">
            <div className="flex justify-between items-center">
              <label htmlFor="password" className="text-xs font-bold text-navy uppercase tracking-wider">
                {t("auth.passwordLabel")}
              </label>
              <Link to="/forgot-password" className="text-xs font-semibold text-primary hover:underline">
                {t("auth.forgotPassword")}
              </Link>
            </div>
            <div className="relative flex items-center">
              <Lock className="absolute left-4 rtl:left-auto rtl:right-4 w-4 h-4 text-gray-400" />
              <input
                type={showPassword ? "text" : "password"}
                id="password"
                placeholder={t("auth.passwordPlaceholder")}
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                className="w-full bg-white border border-gray-100 hover:border-gray-200 focus:border-primary pl-11 pr-12 rtl:pl-12 rtl:pr-11 py-3.5 rounded-xl text-sm focus:outline-none transition-all shadow-sm"
                required
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                aria-label={showPassword ? t("auth.hidePassword") : t("auth.showPassword")}
                className="absolute right-4 rtl:right-auto rtl:left-4 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
              >
                {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
          </div>

          {/* Submit */}
          <button
            type="submit"
            disabled={loading}
            className="w-full mt-2 inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white py-3.5 rounded-xl text-sm font-semibold transition-all duration-300 shadow-md shadow-primary/20 hover:shadow-primary/30 cursor-pointer"
          >
            {loading ? t("auth.loggingIn") : t("auth.logIn")}
            {!loading && <LogIn className="w-4 h-4" />}
          </button>
        </form>

        {/* Separator / Redirect */}
        <div className="text-center mt-8 pt-6 border-t border-gray-50 text-sm font-semibold text-gray-500">
          <span>{t("auth.dontHaveAccount")} </span>
          <Link to="/apply" className="text-primary hover:underline inline-flex items-center gap-0.5 font-bold">
            {t("auth.apply")} 
            <ArrowRight className={`w-3.5 h-3.5 transition-transform ${isRtl ? 'rotate-180' : ''}`} />
          </Link>
        </div>
      </motion.div>
    </div>
  );
}
