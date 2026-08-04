import React, { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { Mail, Lock, LogIn, ArrowRight, Eye, EyeOff } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { useAuth } from "../context/AuthContext";
import FormError from "../components/common/FormError";
import { authCmsService } from "../services/authCmsService";

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login, clearSession } = useAuth();
  const { t, hasTranslation, logoSrc, isRtl, language } = useLanguage();
  const [formData, setFormData] = useState({ email: "", password: "" });
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [content, setContent] = useState();

  useEffect(() => {
    let alive = true;
    authCmsService
      .get(language)
      .then((payload) => {
        if (alive) setContent(payload?.pages?.login || null);
      })
      .catch((err) => {
        console.error("Login CMS load error", err);
        if (alive) setContent(null);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  const c = useMemo(
    () => ({
      title: content?.title || t("auth.welcomeBack"),
      subtitle: content?.subtitle || t("auth.loginPrompt"),
      emailLabel: content?.email_label || t("auth.emailLabel"),
      emailPlaceholder: content?.email_placeholder || t("auth.emailPlaceholder"),
      passwordLabel: content?.password_label || t("auth.passwordLabel"),
      passwordPlaceholder: content?.password_placeholder || t("auth.passwordPlaceholder"),
      forgotPassword: content?.forgot_password_label || t("auth.forgotPassword"),
      submitLabel: content?.submit_label || t("auth.logIn"),
      loadingLabel: content?.loading_label || t("auth.loggingIn"),
      secondaryText: content?.secondary_text || t("auth.dontHaveAccount"),
      secondaryActionLabel: content?.secondary_action_label || t("auth.apply"),
      secondaryActionUrl: content?.secondary_action_url || content?.settings?.secondary_action_url || "/apply",
      showPassword: content?.show_password_label || t("auth.showPassword"),
      hidePassword: content?.hide_password_label || t("auth.hidePassword"),
      requiredMessage: content?.validation_required_message || "",
      errorMessage: content?.error_message || t("auth.loginFailed"),
      logoAlt: content?.logo_alt || (hasTranslation("common.logoAlt") ? t("common.logoAlt") : "BSTU logo"),
    }),
    [content, hasTranslation, t],
  );

  if (content === undefined) {
    return null;
  }

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.email || !formData.password) {
      setError(c.requiredMessage);
      return;
    }

    try {
      setLoading(true);
      setError("");
      const user = await login(formData.email, formData.password, "student");
      const roles = user?.roles || [];
      if (!roles.includes("student") || roles.includes("apanel")) {
        clearSession();
        setError(c.errorMessage);
        return;
      }
      navigate(location.state?.from || "/student/dashboard");
    } catch (err) {
      console.error("Student login error", err);
      setError(err?.message || c.errorMessage);
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
            {c.subtitle}
          </p>
        </div>

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
                {c.passwordLabel}
              </label>
              <Link to="/forgot-password" className="text-xs font-semibold text-primary hover:underline">
                {c.forgotPassword}
              </Link>
            </div>
            <div className="relative flex items-center">
              <Lock className="absolute left-4 rtl:left-auto rtl:right-4 w-4 h-4 text-gray-400" />
              <input
                type={showPassword ? "text" : "password"}
                id="password"
                placeholder={c.passwordPlaceholder}
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                className="w-full bg-white border border-gray-100 hover:border-gray-200 focus:border-primary pl-11 pr-12 rtl:pl-12 rtl:pr-11 py-3.5 rounded-xl text-sm focus:outline-none transition-all shadow-sm"
                required
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                aria-label={showPassword ? c.hidePassword : c.showPassword}
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
            {loading ? c.loadingLabel : c.submitLabel}
            {!loading && <LogIn className="w-4 h-4" />}
          </button>
        </form>

        {/* Separator / Redirect */}
        <div className="text-center mt-8 pt-6 border-t border-gray-50 text-sm font-semibold text-gray-500">
          <span>{c.secondaryText} </span>
          <Link to={c.secondaryActionUrl} className="text-primary hover:underline inline-flex items-center gap-0.5 font-bold">
            {c.secondaryActionLabel} 
            <ArrowRight className={`w-3.5 h-3.5 transition-transform ${isRtl ? 'rotate-180' : ''}`} />
          </Link>
        </div>
      </motion.div>
    </div>
  );
}
