import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { studentService } from "../../../services/studentService";
import { applicationService } from "../../../services/applicationService";
import { useLanguage } from "../../../context/LanguageContext";
import {
  ClipboardList,
  FileCheck,
  CreditCard,
  Bell,
  ArrowRight,
  ShieldAlert,
  CheckCircle,
  Clock,
} from "lucide-react";
import StatusBadge from "../../apanel/components/StatusBadge";
import LoadingState from "../../../components/common/LoadingState";

export default function StudentDashboard() {
  const { t } = useLanguage();
  const [profile, setProfile] = useState(null);
  const [applications, setApplications] = useState([]);
  const [contracts, setContracts] = useState([]);
  const [payments, setPayments] = useState([]);
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadDashboardData = async () => {
      try {
        setLoading(true);
        const [profData, appsData, contractsData, paymentsData] =
          await Promise.all([
            studentService.getProfile().catch(() => null),
            applicationService.getApplications().catch(() => []),
            studentService.getContracts().catch(() => []),
            studentService.getPayments().catch(() => []),
          ]);

        setProfile(profData);
        setApplications(appsData);
        setContracts(contractsData);
        setPayments(paymentsData);

        // Fetch notifications
        const notifRes = await studentService
          .getNotifications()
          .catch(() => []);
        setNotifications(notifRes.slice(0, 5)); // show latest 5
      } catch (err) {
        console.error("Dashboard load failed", err);
      } finally {
        setLoading(false);
      }
    };
    loadDashboardData();
  }, []);

  if (loading) {
    return <LoadingState message="Loading dashboard statistics..." />;
  }

  const activeApp =
    applications.find(
      (app) => app.status !== "graduated" && app.status !== "rejected",
    ) || applications[0];

  // Count missing documents
  const requiredDocTypes = [
    "passport",
    "photo",
    "education_certificate",
    "transcript",
  ];
  const uploadedTypes =
    activeApp?.documents?.map((d) => d.document_type || d.document_name) || [];
  const missingDocs = requiredDocTypes.filter(
    (type) => !uploadedTypes.includes(type),
  );
  const latestPayment = payments[0];
  const translateMaybe = (value) => {
    if (!value || !String(value).includes(".")) return value;
    return t(value, value);
  };

  return (
    <div className="space-y-8 animate-in fade-in duration-200">
      {/* Welcome Banner */}
      <div className="bg-linear-to-r from-navy to-navy-dark rounded-3xl p-8 text-white relative overflow-hidden shadow-xl">
        <div className="absolute right-0 bottom-0 top-0 opacity-10 flex items-center justify-center">
          <ClipboardList className="w-64 h-64 rotate-12 translate-x-12 translate-y-12" />
        </div>
        <div className="relative z-10 space-y-2">
          <h1 className="text-2xl md:text-3xl font-extrabold uppercase tracking-wide">
            {t("student.welcome")}
          </h1>
          <p className="text-gray-300 text-xs font-semibold max-w-xl">
            {t(
              "student.welcomeSubtitle",
              "Complete your profile, submit dynamic documents, and track your admission status in real-time.",
            )}
          </p>
        </div>
      </div>

      {/* Grid Stats Summary */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Application Card */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs flex flex-col justify-between space-y-4">
          <div className="flex justify-between items-start">
            <div className="space-y-1">
              <span className="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">
                Active Application
              </span>
              <h3 className="text-base font-bold text-navy">
                {activeApp?.program?.translations?.[0]?.name ||
                  "No active application"}
              </h3>
            </div>
            <div className="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
              <ClipboardList className="w-5 h-5" />
            </div>
          </div>
          {activeApp ? (
            <div className="flex items-center justify-between pt-2 border-t border-gray-50">
              <StatusBadge status={activeApp.status} />
              <Link
                to="/student/application"
                className="text-xs font-extrabold text-primary hover:underline flex items-center gap-1"
              >
                <span>View Details</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </Link>
            </div>
          ) : (
            <div className="pt-2 border-t border-gray-50">
              <Link
                to="/student/application"
                className="text-xs font-extrabold text-primary hover:underline flex items-center gap-1"
              >
                <span>Start Application</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </Link>
            </div>
          )}
        </div>

        {/* Profile Completion Card */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs flex flex-col justify-between space-y-4">
          <div className="flex justify-between items-start">
            <div className="space-y-1">
              <span className="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">
                Profile Status
              </span>
              <h3 className="text-base font-bold text-navy">
                {profile ? "Profile Completed" : "Profile Incomplete"}
              </h3>
            </div>
            <div
              className={`w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 ${profile ? "bg-emerald-50 text-emerald-600" : "bg-rose-50 text-rose-600"}`}
            >
              {profile ? (
                <CheckCircle className="w-5 h-5" />
              ) : (
                <ShieldAlert className="w-5 h-5" />
              )}
            </div>
          </div>
          <div className="flex items-center justify-between pt-2 border-t border-gray-50">
            <span className="text-xs font-bold text-gray-400">
              {profile ? "Details fully updated" : "Required to apply"}
            </span>
            <Link
              to="/student/profile"
              className="text-xs font-extrabold text-primary hover:underline flex items-center gap-1"
            >
              <span>{profile ? "Edit Profile" : "Complete Profile"}</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>

        {/* Documents Status Card */}
        <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs flex flex-col justify-between space-y-4">
          <div className="flex justify-between items-start">
            <div className="space-y-1">
              <span className="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest">
                Verification Documents
              </span>
              <h3 className="text-base font-bold text-navy">
                {missingDocs.length > 0
                  ? `${missingDocs.length} Missing File(s)`
                  : "All Files Uploaded"}
              </h3>
            </div>
            <div
              className={`w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 ${missingDocs.length === 0 ? "bg-emerald-50 text-emerald-600" : "bg-amber-50 text-amber-600"}`}
            >
              <FileCheck className="w-5 h-5" />
            </div>
          </div>
          <div className="flex items-center justify-between pt-2 border-t border-gray-50">
            <span className="text-xs font-bold text-gray-400">
              {activeApp
                ? `${activeApp.documents?.length || 0} file(s) attached`
                : "Application draft needed"}
            </span>
            <Link
              to="/student/documents"
              className="text-xs font-extrabold text-primary hover:underline flex items-center gap-1"
            >
              <span>Upload Files</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left column: Notifications & Inquiries */}
        <div className="lg:col-span-2 space-y-8">
          {/* Notifications feed */}
          <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
            <div className="flex justify-between items-center pb-2 border-b border-gray-50">
              <h3 className="text-base font-extrabold text-navy uppercase tracking-wider">
                {t("student.notifications")}
              </h3>
              <Link
                to="/student/notifications"
                className="text-xs font-extrabold text-primary hover:underline"
              >
                View All
              </Link>
            </div>
            {notifications.length > 0 ? (
              <div className="space-y-3">
                {notifications.map((notif) => (
                  <div
                    key={notif.id}
                    className="flex gap-3.5 p-3 rounded-2xl border border-gray-50 hover:bg-gray-50/50 transition-all"
                  >
                    <div className="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                      <Bell className="w-4 h-4" />
                    </div>
                    <div className="space-y-1">
                      <p className="text-xs font-bold text-navy">
                        {translateMaybe(notif.title)}
                      </p>
                      <p className="text-[11px] text-gray-400 font-semibold">
                        {translateMaybe(notif.message)}
                      </p>
                      <span className="block text-[9px] text-gray-450 font-bold">
                        {new Date(notif.created_at).toLocaleDateString()}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-xs text-gray-400 font-bold text-center py-6">
                No recent notifications found.
              </p>
            )}
          </div>
        </div>

        {/* Right column: Contracts & Tuition billing */}
        <div className="space-y-8">
          <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-xs space-y-4">
            <h3 className="text-base font-extrabold text-navy uppercase tracking-wider pb-2 border-b border-gray-50">
              Contracts & Billing
            </h3>
            <div className="p-4 rounded-2xl border border-gray-50 bg-gray-50/50 flex items-center justify-between gap-3">
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                  <CreditCard className="w-4 h-4" />
                </div>
                <div>
                  <p className="text-[10px] text-gray-400 font-black uppercase tracking-wider">
                    Latest Payment
                  </p>
                  <p className="text-xs font-extrabold text-navy">
                    {latestPayment
                      ? latestPayment.status.replace("_", " ").toUpperCase()
                      : "NO PAYMENT REQUEST"}
                  </p>
                </div>
              </div>
              <Link
                to="/student/payments"
                className="text-xs font-extrabold text-primary hover:underline"
              >
                View
              </Link>
            </div>
            {contracts.length > 0 ? (
              <div className="space-y-3">
                {contracts.map((c) => (
                  <div
                    key={c.id}
                    className="p-4 rounded-2xl border border-gray-50 space-y-2"
                  >
                    <div className="flex justify-between items-center">
                      <span className="text-[10px] font-extrabold text-gray-400 uppercase">
                        Contract #{c.contract_number}
                      </span>
                      <span
                        className={`text-[10px] font-extrabold px-2 py-0.5 rounded-full ${c.status === "signed" || c.status === "active" ? "bg-emerald-50 text-emerald-600" : "bg-amber-50 text-amber-600"}`}
                      >
                        {(c.status || "pending").toUpperCase()}
                      </span>
                    </div>
                    <div className="flex justify-between items-end">
                      <div className="space-y-0.5">
                        <p className="text-[10px] text-gray-400 font-bold">
                          Total Amount
                        </p>
                        <p className="text-xs font-black text-navy">
                          ${Number(c.amount).toLocaleString()}
                        </p>
                      </div>
                      <Link
                        to="/student/contracts"
                        className="text-xs font-extrabold text-primary hover:underline"
                      >
                        Details
                      </Link>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8 text-gray-400 font-bold text-xs space-y-2">
                <Clock className="w-8 h-8 text-gray-300 mx-auto" />
                <p>No contract details generated yet.</p>
                <p className="text-[10px] font-semibold">
                  Your contract slip will appear here once accepted.
                </p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
