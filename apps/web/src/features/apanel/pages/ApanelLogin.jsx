import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../../../context/AuthContext";
import { useLanguage } from "../../../context/LanguageContext";
import { Loader2, ShieldCheck, Lock, Mail } from "lucide-react";
import FormError from "../../../components/common/FormError";

export default function ApanelLogin() {
  const navigate = useNavigate();
  const { login, checkAdminRole, clearSession } = useAuth();
  const { t, settings = {}, logoSrc } = useLanguage();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!email || !password) return;

    try {
      setSubmitting(true);
      setError("");
      clearSession();

      // 1. Call normal login
      await login(email, password);

      // 2. Perform verification of role in backend user response
      const hasAccess = await checkAdminRole();
      if (!hasAccess) {
        setError(t("apanel.login.accessDenied"));
        setSubmitting(false);
        return;
      }

      // 3. Redirect to apanel home
      navigate("/apanel");
    } catch (err) {
      console.error("Admin login error", err);
      setError(err?.message || t("apanel.login.failed"));
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-navy flex items-center justify-center p-4">
      <div className="bg-white border border-gray-100 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        {/* Header branding */}
        <div className="flex flex-col items-center justify-center text-center space-y-3">
          {logoSrc ? (
            <img
              src={logoSrc}
              alt={settings.site_name || t("app.name")}
              className="w-12 h-12 rounded-2xl object-contain"
            />
          ) : (
            <div className="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-black text-2xl">
              {t("apanel.initials")}
            </div>
          )}
          <div className="space-y-1">
            <h2 className="font-extrabold text-navy text-xl uppercase tracking-wider">
              {t("apanel.login.title")}
            </h2>
            <p className="text-gray-400 text-xs font-semibold">
              {t("apanel.login.subtitle")}
            </p>
          </div>
        </div>

        {error && <FormError message={error} />}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-1.5">
            <label className="block text-[10px] font-extrabold text-navy uppercase tracking-wider">
              {t("form.email")}
            </label>
            <div className="relative">
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder={t("apanel.login.emailPlaceholder")}
                required
                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
              />
              <Mail className="absolute left-3.5 top-3.5 w-3.5 h-3.5 text-gray-400" />
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="block text-[10px] font-extrabold text-navy uppercase tracking-wider">
              {t("form.password")}
            </label>
            <div className="relative">
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
                required
                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
              />
              <Lock className="absolute left-3.5 top-3.5 w-3.5 h-3.5 text-gray-400" />
            </div>
          </div>

          <button
            type="submit"
            disabled={submitting}
            className="w-full bg-primary hover:bg-primary-hover text-white py-3 rounded-xl text-xs font-extrabold shadow-sm hover:shadow-md cursor-pointer transition-all flex items-center justify-center gap-1.5 mt-2"
          >
            {submitting ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <ShieldCheck className="w-4 h-4" />
            )}
            {t("apanel.login.verifyEnter")}
          </button>
        </form>
      </div>
    </div>
  );
}
