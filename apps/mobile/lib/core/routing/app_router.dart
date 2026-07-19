import 'package:flutter/material.dart';

import '../../features/auth/presentation/auth_screens.dart';
import '../../features/home/presentation/home_screen.dart';
import '../../features/language/presentation/language_screen.dart';
import '../../features/public_content/presentation/public_content_screens.dart';
import '../../features/splash/presentation/splash_screen.dart';
import '../../features/student/presentation/student_screens.dart';
import '../../shared/app_state.dart';

class AppRoutes {
  const AppRoutes._();

  static const splash = '/';
  static const language = '/language';
  static const home = '/home';
  static const login = '/login';
  static const register = '/register';
  static const forgotPassword = '/forgot-password';
  static const faculties = '/faculties';
  static const facultyDetails = '/faculty';
  static const departments = '/departments';
  static const departmentDetails = '/department';
  static const programs = '/programs';
  static const programDetails = '/program';
  static const news = '/news';
  static const newsDetails = '/news/details';
  static const announcements = '/announcements';
  static const announcementDetails = '/announcement';
  static const services = '/services';
  static const contact = '/contact';
  static const studentDashboard = '/student/dashboard';
  static const studentProfile = '/student/profile';
  static const studentApplication = '/student/application';
  static const studentApplicationStatus = '/student/application/status';
  static const studentDocuments = '/student/documents';
  static const studentNotifications = '/student/notifications';
  static const studentContracts = '/student/contracts';
  static const studentPayments = '/student/payments';
  static const studentSupport = '/student/support';
  static const settings = '/settings';
}

class AppRouter {
  const AppRouter._();

  static Route<dynamic> onGenerateRoute(
      RouteSettings settings, AppState state) {
    final routeName = settings.name ?? AppRoutes.home;
    if (_protectedRoutes.contains(routeName) && !state.isAuthenticated) {
      return _page(const LoginScreen(), name: AppRoutes.login);
    }

    switch (routeName) {
      case AppRoutes.splash:
        return _page(const SplashScreen(), name: routeName);
      case AppRoutes.language:
        return _page(const LanguageScreen(), name: routeName);
      case AppRoutes.home:
        return _page(const HomeScreen(), name: routeName);
      case AppRoutes.login:
        return _page(const LoginScreen(), name: routeName);
      case AppRoutes.register:
        return _page(const RegisterScreen(), name: routeName);
      case AppRoutes.forgotPassword:
        return _page(const ForgotPasswordScreen(), name: routeName);
      case AppRoutes.faculties:
        return _page(const PublicListScreen(kind: PublicContentKind.faculties),
            name: routeName);
      case AppRoutes.departments:
        return _page(
            const PublicListScreen(kind: PublicContentKind.departments),
            name: routeName);
      case AppRoutes.programs:
        return _page(const PublicListScreen(kind: PublicContentKind.programs),
            name: routeName);
      case AppRoutes.news:
        return _page(const PublicListScreen(kind: PublicContentKind.news),
            name: routeName);
      case AppRoutes.announcements:
        return _page(
            const PublicListScreen(kind: PublicContentKind.announcements),
            name: routeName);
      case AppRoutes.services:
        return _page(const PublicListScreen(kind: PublicContentKind.services),
            name: routeName);
      case AppRoutes.contact:
        return _page(const ContactScreen(), name: routeName);
      case AppRoutes.facultyDetails:
      case AppRoutes.departmentDetails:
      case AppRoutes.programDetails:
      case AppRoutes.newsDetails:
      case AppRoutes.announcementDetails:
        return _page(PublicDetailScreen(arguments: settings.arguments),
            name: routeName);
      case AppRoutes.studentDashboard:
        return _page(const StudentDashboardScreen(), name: routeName);
      case AppRoutes.studentProfile:
        return _page(const StudentProfileScreen(), name: routeName);
      case AppRoutes.studentApplication:
        return _page(const StudentApplicationScreen(), name: routeName);
      case AppRoutes.studentApplicationStatus:
        return _page(const StudentApplicationStatusScreen(), name: routeName);
      case AppRoutes.studentDocuments:
        return _page(const StudentDocumentsScreen(), name: routeName);
      case AppRoutes.studentNotifications:
        return _page(const StudentNotificationsScreen(), name: routeName);
      case AppRoutes.studentContracts:
        return _page(const StudentContractsScreen(), name: routeName);
      case AppRoutes.studentPayments:
        return _page(const StudentPaymentsScreen(), name: routeName);
      case AppRoutes.studentSupport:
        return _page(const StudentSupportScreen(), name: routeName);
      case AppRoutes.settings:
        return _page(const LanguageScreen(), name: routeName);
      default:
        return _page(const HomeScreen(), name: AppRoutes.home);
    }
  }

  static final _protectedRoutes = {
    AppRoutes.studentDashboard,
    AppRoutes.studentProfile,
    AppRoutes.studentApplication,
    AppRoutes.studentApplicationStatus,
    AppRoutes.studentDocuments,
    AppRoutes.studentNotifications,
    AppRoutes.studentContracts,
    AppRoutes.studentPayments,
    AppRoutes.studentSupport,
  };

  static MaterialPageRoute<dynamic> _page(Widget child,
      {required String name}) {
    return MaterialPageRoute(
        builder: (_) => child, settings: RouteSettings(name: name));
  }
}
