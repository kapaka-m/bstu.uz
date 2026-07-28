import React, { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { useAuth } from "../../../context/AuthContext";
import { useLanguage } from "../../../context/LanguageContext";
import {
  Loader2,
  GraduationCap,
  Lock,
  Mail,
  User,
  ArrowRight,
} from "lucide-react";
import FormError from "../../../components/common/FormError";

export default function StudentRegister() {
  const navigate = useNavigate();
  const { register } = useAuth();
  const { t } = useLanguage();

  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!name || !email || !password || !passwordConfirmation) return;

    if (password !== passwordConfirmation) {
      setError(t("validation.passwordMismatch"));
      return;
    }

    try {
      setSubmitting(true);
      setError("");
      await register(name, email, password, passwordConfirmation);
      navigate("/student/dashboard");
    } catch (err) {
      console.error("Student registration error", err);
      setError(
        err?.message || "Registration failed. Please check your credentials.",
      );
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-navy flex items-center justify-center p-4">
      <div className="bg-white border border-gray-150 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <div className="flex flex-col items-center justify-center text-center space-y-3">
          <div className="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
            <GraduationCap className="w-6 h-6" />
          </div>
          <div className="space-y-1">
            <h2 className="font-extrabold text-navy text-xl uppercase tracking-wider">
              {t("auth.registerTitle")}
            </h2>
            <p className="text-gray-400 text-xs font-semibold">
              {t(
                "auth.registerSubtitle",
                "Start your international application today",
              )}
            </p>
          </div>
        </div>

        {error && <FormError message={error} />}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-1.5">
            <label className="block text-[10px] font-extrabold text-navy uppercase tracking-wider">
              {t("form.fullName")}
            </label>
            <div className="relative">
              <input
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                placeholder="John Doe"
                required
                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
              />
              <User className="absolute left-3.5 top-3.5 w-3.5 h-3.5 text-gray-400" />
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="block text-[10px] font-extrabold text-navy uppercase tracking-wider">
              {t("form.email")}
            </label>
            <div className="relative">
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="student@example.com"
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

          <div className="space-y-1.5">
            <label className="block text-[10px] font-extrabold text-navy uppercase tracking-wider">
              {t("form.confirmPassword")}
            </label>
            <div className="relative">
              <input
                type="password"
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
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
              <>
                <span>{t("button.register")}</span>
                <ArrowRight className="w-4 h-4" />
              </>
            )}
          </button>
        </form>

        <div className="text-center text-xs font-bold text-gray-400 pt-2">
          {t("auth.alreadyHaveAccount")}{" "}
          <Link
            to="/student/login"
            className="text-primary hover:underline ml-1"
          >
            {t("auth.loginNow")}
          </Link>
        </div>
      </div>
    </div>
  );
}
