import React, { Suspense } from "react";
import {
  BrowserRouter as Router,
  Routes,
  Route,
  Navigate,
  useLocation,
} from "react-router-dom";
import Header from "./components/Header";
import Footer from "./components/Footer";
import ScrollToTop from "./components/ScrollToTop";

import { LocaleProvider } from "./context/LocaleContext";
import { AuthProvider, useAuth } from "./context/AuthContext";
import { AppDataProvider } from "./context/AppDataContext";
import LoadingState from "./components/common/LoadingState";

// Lazy loaded page routes
const Home = React.lazy(() => import("./pages/Home"));
const Blog = React.lazy(() => import("./pages/Blog"));
const BlogDetails = React.lazy(() => import("./pages/BlogDetails"));
const AboutPage = React.lazy(() => import("./pages/AboutPage"));
const ServicesPage = React.lazy(() => import("./pages/ServicesPage"));
const ContactPage = React.lazy(() => import("./pages/ContactPage"));
const LoginPage = React.lazy(() => import("./pages/LoginPage"));
const RegisterPage = React.lazy(() => import("./pages/RegisterPage"));
const FacultyDetails = React.lazy(() => import("./pages/FacultyDetails"));
const DepartmentPage = React.lazy(() => import("./pages/DepartmentPage"));
const ForgotPassword = React.lazy(() => import("./pages/ForgotPassword"));
const AdministrationDetails = React.lazy(
  () => import("./pages/AdministrationDetails"),
);
const CenterDetails = React.lazy(() => import("./pages/CenterDetails"));
const ProgramsPage = React.lazy(() => import("./pages/ProgramsPage"));
const ProgramDetails = React.lazy(() => import("./pages/ProgramDetails"));
const AnnouncementsPage = React.lazy(() => import("./pages/AnnouncementsPage"));
const AnnouncementDetails = React.lazy(
  () => import("./pages/AnnouncementDetails"),
);
const VideoBDTU = React.lazy(() => import("./pages/VideoBDTU"));
const NewsPage = React.lazy(() => import("./pages/NewsPage"));
const NewsDetails = React.lazy(() => import("./pages/NewsDetails"));
const ApplyPage = React.lazy(() => import("./pages/ApplyPage"));
const GreenCampusPage = React.lazy(() => import("./pages/GreenCampusPage"));
const GreenCampusDetails = React.lazy(
  () => import("./pages/GreenCampusDetails"),
);
const ProfileDetails = React.lazy(() => import("./pages/ProfileDetails"));

// Lazy loaded student portal routes
const StudentLayout = React.lazy(
  () => import("./features/student/layouts/StudentLayout"),
);
const StudentLogin = React.lazy(
  () => import("./features/student/pages/StudentLogin"),
);
const StudentRegister = React.lazy(
  () => import("./features/student/pages/StudentRegister"),
);
const StudentDashboard = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentProfile = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentApplication = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentApplicationStatus = React.lazy(
  () => import("./features/student/pages/StudentApplicationStatus"),
);
const StudentDocuments = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentNotifications = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentContracts = React.lazy(
  () => import("./features/student/pages/StudentContracts"),
);
const StudentPayments = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentSupport = React.lazy(
  () => import("./features/student/pages/StudentSupport"),
);
const StudentAcademicInformation = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentEquivalency = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentAdmission = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);
const StudentEnrollment = React.lazy(
  () => import("./features/student/pages/StudentPortalPhaseTwo"),
);

// Lazy loaded admin apanel routes
const ApanelLogin = React.lazy(
  () => import("./features/apanel/pages/ApanelLogin"),
);
const ApanelDashboard = React.lazy(
  () => import("./features/apanel/pages/ApanelDashboard"),
);
const ApanelMedia = React.lazy(
  () => import("./features/apanel/pages/ApanelMedia"),
);
const ApanelLocales = React.lazy(
  () => import("./features/apanel/pages/ApanelLocales"),
);
const ApanelCenters = React.lazy(
  () => import("./features/apanel/pages/ApanelCenters"),
);
const ApanelApplicationsWorkflow = React.lazy(
  () => import("./features/apanel/pages/ApanelApplicationsWorkflow"),
);
const ApanelTranslations = React.lazy(
  () => import("./features/apanel/pages/ApanelTranslations"),
);
const ApanelFooterWeb = React.lazy(
  () => import("./features/apanel/pages/ApanelFooterWeb"),
);
const ApanelAboutPage = React.lazy(
  () => import("./features/apanel/pages/ApanelAboutPage"),
);
const ApanelContactPage = React.lazy(
  () => import("./features/apanel/pages/ApanelContactPage"),
);
const ApanelHeaderNavbar = React.lazy(
  () => import("./features/apanel/pages/ApanelHeaderNavbar"),
);
const ApanelNewsEvents = React.lazy(
  () => import("./features/apanel/pages/ApanelNewsEvents"),
);
const ApanelAnnouncements = React.lazy(
  () => import("./features/apanel/pages/ApanelAnnouncements"),
);
const ApanelBlog = React.lazy(
  () => import("./features/apanel/pages/ApanelBlog"),
);
const ApanelVideoBdtu = React.lazy(
  () => import("./features/apanel/pages/ApanelVideoBdtu"),
);
const ApanelGreenCampus = React.lazy(
  () => import("./features/apanel/pages/ApanelGreenCampus"),
);
const ApanelAdministration = React.lazy(
  () => import("./features/apanel/pages/ApanelAdministration"),
);
const ApanelInteractiveServices = React.lazy(
  () => import("./features/apanel/pages/ApanelInteractiveServices"),
);
const ApanelNewsletterSubscriptions = React.lazy(
  () => import("./features/apanel/pages/ApanelNewsletterSubscriptions"),
);
const ApanelContactManagement = React.lazy(
  () => import("./features/apanel/pages/ApanelContactManagement"),
);
const ApanelCrud = React.lazy(
  () => import("./features/apanel/pages/ApanelCrud"),
);
const ApanelLayout = React.lazy(
  () => import("./features/apanel/layouts/ApanelLayout"),
);

// Student Protected Route wrapper — redirects to /student/login if not authenticated
function StudentRoute({ children }) {
  const { isAuthenticated, loading, user } = useAuth();

  if (loading) {
    return <LoadingState message="Checking session..." height="h-screen" />;
  }

  if (!isAuthenticated) {
    return <Navigate to="/student/login" replace />;
  }

  const roles = user?.roles || [];
  if (roles.includes("apanel")) {
    return <Navigate to="/apanel/dashboard" replace />;
  }

  if (roles.length > 0 && !roles.includes("student")) {
    return <Navigate to="/student/login" replace />;
  }

  return children;
}

// Admin Protected Route wrapper
function AdminRoute({ children }) {
  const { isAuthenticated, loading, checkAdminRole, clearSession } = useAuth();
  const [checkingRole, setCheckingRole] = React.useState(true);
  const [isAdmin, setIsAdmin] = React.useState(false);

  React.useEffect(() => {
    const verify = async () => {
      if (isAuthenticated) {
        const hasRole = await checkAdminRole();
        setIsAdmin(hasRole);
      }
      setCheckingRole(false);
    };
    if (!loading) {
      verify();
    }
  }, [isAuthenticated, loading, checkAdminRole]);

  React.useEffect(() => {
    if (!loading && !checkingRole && isAuthenticated && !isAdmin) {
      clearSession();
    }
  }, [loading, checkingRole, isAuthenticated, isAdmin, clearSession]);

  if (loading || checkingRole) {
    return (
      <LoadingState
        message="Verifying admin credentials..."
        height="h-screen"
      />
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/apanel/login" replace />;
  }

  if (!isAdmin) {
    return <Navigate to="/apanel/login" replace />;
  }

  return children;
}

// Separate component to consume React Router hooks (useLocation) safely
function AppContent() {
  const location = useLocation();
  const isApanel = location.pathname.startsWith("/apanel");
  const isStudent = location.pathname.startsWith("/student");

  return (
    <div className="flex flex-col min-h-screen">
      {!isApanel && !isStudent && <Header />}
      <main className="grow">
        <Suspense
          fallback={
            <LoadingState
              message="Loading page contents..."
              height="h-screen"
            />
          }
        >
          <Routes>
            <Route path="/" element={<Home />} />

            {/* Public Pages */}
            <Route path="/about" element={<AboutPage />} />
            <Route path="/services" element={<ServicesPage />} />
            <Route path="/contact" element={<ContactPage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/forgot-password" element={<ForgotPassword />} />
            <Route path="/faculty/:id" element={<FacultyDetails />} />
            <Route path="/department/:id" element={<DepartmentPage />} />
            <Route
              path="/administration/:id"
              element={<AdministrationDetails />}
            />
            <Route path="/center/:id" element={<CenterDetails />} />
            <Route path="/programs" element={<ProgramsPage />} />
            <Route path="/programs/:id" element={<ProgramDetails />} />
            <Route path="/program/:id" element={<ProgramDetails />} />
            <Route path="/announcements" element={<AnnouncementsPage />} />
            <Route
              path="/announcements/:id"
              element={<AnnouncementDetails />}
            />
            <Route path="/video-bdtu" element={<VideoBDTU />} />
            <Route path="/news" element={<NewsPage />} />
            <Route path="/news/:id" element={<NewsDetails />} />
            <Route path="/green-campus" element={<GreenCampusPage />} />
            <Route path="/green-campus/:id" element={<GreenCampusDetails />} />

            <Route path="/apply" element={<ApplyPage />} />
            <Route path="/profile/:id" element={<ProfileDetails />} />

            <Route path="/blog" element={<Blog />} />
            <Route path="/blog/:id" element={<BlogDetails />} />
            <Route path="/blog-details" element={<BlogDetails />} />
            <Route path="/blog-details.html" element={<BlogDetails />} />

            {/* ── Student Portal Routes ── */}
            <Route path="/student/login" element={<StudentLogin />} />
            <Route path="/student/register" element={<StudentRegister />} />
            <Route
              path="/student"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentDashboard />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/dashboard"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentDashboard />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/profile"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentProfile />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/application"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentApplication />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/application/status"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentApplicationStatus />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/documents"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentDocuments />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/academic-information"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentAcademicInformation />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/equivalency"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentEquivalency />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/notifications"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentNotifications />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/contracts"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentContracts />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/payments"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentPayments />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/admission"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentAdmission />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/enrollment"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentEnrollment />
                  </StudentLayout>
                </StudentRoute>
              }
            />
            <Route
              path="/student/support"
              element={
                <StudentRoute>
                  <StudentLayout>
                    <StudentSupport />
                  </StudentLayout>
                </StudentRoute>
              }
            />

            {/* Apanel Administration Routes */}
            <Route path="/apanel/login" element={<ApanelLogin />} />
            <Route
              path="/apanel"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelDashboard />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/dashboard"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelDashboard />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/media"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelMedia />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/locales"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelLocales />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/locales"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelLocales />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/centres-and-departments"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelCenters />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/translations"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelTranslations />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/news"
              element={<Navigate to="/apanel/cms/news-events" replace />}
            />
            <Route
              path="/apanel/menus"
              element={<Navigate to="/apanel/cms/header-navbar" replace />}
            />
            <Route
              path="/apanel/menu-items"
              element={<Navigate to="/apanel/cms/header-navbar" replace />}
            />
            <Route
              path="/apanel/pages"
              element={<Navigate to="/apanel/dashboard" replace />}
            />
            <Route
              path="/apanel/page-blocks"
              element={<Navigate to="/apanel/dashboard" replace />}
            />
            <Route
              path="/apanel/services"
              element={<Navigate to="/apanel/cms/interactive-services" replace />}
            />
            <Route
              path="/apanel/videos"
              element={<Navigate to="/apanel/cms/video-bdtu" replace />}
            />
            <Route
              path="/apanel/announcements"
              element={<Navigate to="/apanel/cms/announcements" replace />}
            />
            <Route
              path="/apanel/Facultys"
              element={<Navigate to="/apanel/faculties" replace />}
            />
            <Route
              path="/apanel/dr-staf"
              element={<Navigate to="/apanel/staff" replace />}
            />
            <Route
              path="/apanel/den-faculties"
              element={<Navigate to="/apanel/staff" replace />}
            />
            <Route
              path="/apanel/man-department"
              element={<Navigate to="/apanel/staff" replace />}
            />
            <Route
              path="/apanel/cms/footer-web"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelFooterWeb />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/about-page"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelAboutPage />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/contact-page"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelContactPage />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/header-navbar"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelHeaderNavbar />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/news-events"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelNewsEvents />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/announcements"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelAnnouncements />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/blog"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelBlog />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/video-bdtu"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelVideoBdtu />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/green-campus"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelGreenCampus />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/administration"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelAdministration />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/cms/interactive-services"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelInteractiveServices />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/newsletter/subscriptions"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelNewsletterSubscriptions />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/inquiries"
              element={<Navigate to="/apanel/management/contact" replace />}
            />
            <Route
              path="/apanel/management/contact"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelContactManagement />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/applications"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelApplicationsWorkflow />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/applications/:id"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelApplicationsWorkflow />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/applications/:id/documents"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelApplicationsWorkflow />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/applications/:id/equivalency"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelApplicationsWorkflow />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/applications/:id/payments"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelApplicationsWorkflow />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/applications/:id/final-review"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelApplicationsWorkflow />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/applications/:id/admission"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelApplicationsWorkflow />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/applications/:id/enrollment"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelApplicationsWorkflow />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
            <Route
              path="/apanel/:resource"
              element={
                <AdminRoute>
                  <ApanelLayout>
                    <ApanelCrud />
                  </ApanelLayout>
                </AdminRoute>
              }
            />
          </Routes>
        </Suspense>
      </main>
      {!isApanel && !isStudent && <Footer />}
      {!isApanel && !isStudent && <ScrollToTop />}
    </div>
  );
}

export default function App() {
  return (
    <LocaleProvider>
      <AuthProvider>
        <AppDataProvider>
          <Router>
            <AppContent />
          </Router>
        </AppDataProvider>
      </AuthProvider>
    </LocaleProvider>
  );
}
