import React, { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { Mail, Lock, LogIn, ArrowRight, Eye, EyeOff } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { useAuth } from "../context/AuthContext";
import FormError from "../components/common/FormError";
import { authCmsService } from "../services/authCmsService";

function LoginSkeleton({ logoSrc, logoAlt }) {
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
        <div className="space-y-2">
          <div className="h-3 w-24 rounded bg-gray-100" />
          <div className="h-12 rounded-xl bg-gray-100" />
        </div>
        <div className="h-12 rounded-xl bg-primary/10" />
      </div>
    </div>
  );
}

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login, clearSession } = useAuth();
  const { logoSrc, isRtl, language } = useLanguage();
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
      title: content?.title || "",
      subtitle: content?.subtitle || "",
      emailLabel: content?.email_label || "",
      emailPlaceholder: content?.email_placeholder || "",
      passwordLabel: content?.password_label || "",
      passwordPlaceholder: content?.password_placeholder || "",
      forgotPassword: content?.forgot_password_label || "",
      submitLabel: content?.submit_label || "",
      loadingLabel: content?.loading_label || "",
      secondaryText: content?.secondary_text || "",
      secondaryActionLabel: content?.secondary_action_label || "",
      secondaryActionUrl: content?.secondary_action_url || content?.settings?.secondary_action_url || "",
      showPassword: content?.show_password_label || "",
      hidePassword: content?.hide_password_label || "",
      requiredMessage: content?.validation_required_message || "",
      errorMessage: content?.error_message || "",
      logoAlt: content?.logo_alt || "",
    }),
    [content],
  );

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
    <div className="relative flex min-h-screen items-center justify-center overflow-hidden bg-linear-to-br from-primary-light via-white to-[#eef4ff] px-4 py-24 sm:py-28">
      <div className="absolute start-0 top-24 h-64 w-64 -translate-x-1/2 rounded-full bg-primary/5 blur-3xl rtl:translate-x-1/2" />
      <div className="absolute bottom-8 end-0 h-72 w-72 translate-x-1/2 rounded-full bg-blue-400/5 blur-3xl rtl:-translate-x-1/2" />

      {content === undefined ? (
        <LoginSkeleton logoSrc={logoSrc} logoAlt={c.logoAlt} />
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
              {c.subtitle}
            </p>
          </div>

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
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full min-w-0 rounded-xl border border-gray-100 bg-white py-3.5 pl-11 pr-4 text-sm shadow-sm transition-all hover:border-gray-200 focus:border-primary focus:outline-none rtl:pl-4 rtl:pr-11"
                  required
                />
              </div>
            </div>

            <div className="flex flex-col gap-2">
              <div className="flex flex-wrap items-center justify-between gap-2">
                <label htmlFor="password" className="text-xs font-bold uppercase tracking-wider text-navy">
                  {c.passwordLabel}
                </label>
                <Link to="/forgot-password" className="text-xs font-semibold text-primary hover:underline">
                  {c.forgotPassword}
                </Link>
              </div>
              <div className="relative flex items-center">
                <Lock className="absolute left-4 h-4 w-4 text-gray-400 rtl:left-auto rtl:right-4" />
                <input
                  type={showPassword ? "text" : "password"}
                  id="password"
                  placeholder={c.passwordPlaceholder}
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  className="w-full min-w-0 rounded-xl border border-gray-100 bg-white py-3.5 pl-11 pr-12 text-sm shadow-sm transition-all hover:border-gray-200 focus:border-primary focus:outline-none rtl:pl-12 rtl:pr-11"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  aria-label={showPassword ? c.hidePassword : c.showPassword}
                  className="absolute right-4 cursor-pointer text-gray-400 hover:text-gray-600 focus:outline-none rtl:right-auto rtl:left-4"
                >
                  {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3.5 text-sm font-semibold text-white shadow-md shadow-primary/20 transition-all duration-300 hover:bg-primary-hover hover:shadow-primary/30 disabled:bg-primary/50"
            >
              <span className="min-w-0 break-words">{loading ? c.loadingLabel : c.submitLabel}</span>
              {!loading && <LogIn className="h-4 w-4" />}
            </button>
          </form>

          <div className="mt-8 border-t border-gray-50 pt-6 text-center text-sm font-semibold text-gray-500">
            <span>{c.secondaryText} </span>
            <Link to={c.secondaryActionUrl} className="inline-flex items-center gap-0.5 font-bold text-primary hover:underline">
              {c.secondaryActionLabel}
              <ArrowRight className={`h-3.5 w-3.5 transition-transform ${isRtl ? "rotate-180" : ""}`} />
            </Link>
          </div>
        </motion.div>
      )}
    </div>
  );
}
