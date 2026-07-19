import 'package:flutter/material.dart';

import '../../core/routing/app_router.dart';
import '../../core/widgets/mobile_ui.dart';
import '../../shared/app_state.dart';

class AppShell extends StatelessWidget {
  const AppShell(
      {required this.titleKey, required this.body, this.actions, super.key});

  final String titleKey;
  final Widget body;
  final List<Widget>? actions;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return Scaffold(
      appBar: AppBar(
        titleSpacing: 16,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              state.t(titleKey),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            Text(
              state.t('app.name', 'BSTU International'),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                color: BstuColors.muted,
                fontSize: 11,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: state.t('settings.language', 'Language'),
            onPressed: () => Navigator.pushNamed(context, AppRoutes.language),
            icon: const Icon(Icons.language),
          ),
          if (state.isAuthenticated)
            IconButton(
              tooltip: state.t('auth.logout', 'Logout'),
              onPressed: () async {
                await state.logout();
                if (context.mounted) {
                  Navigator.pushNamedAndRemoveUntil(
                      context, AppRoutes.home, (_) => false);
                }
              },
              icon: const Icon(Icons.logout),
            )
          else
            IconButton(
              tooltip: state.t('auth.login', 'Login'),
              onPressed: () => Navigator.pushNamed(context, AppRoutes.login),
              icon: const Icon(Icons.login),
            ),
          ...?actions,
        ],
      ),
      body: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0xfff8fafc), Color(0xffffffff)],
          ),
        ),
        child: SafeArea(top: false, child: body),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex(context),
        onDestinationSelected: (index) {
          final route = switch (index) {
            0 => AppRoutes.home,
            1 => AppRoutes.programs,
            2 => AppRoutes.news,
            _ => AppRoutes.studentDashboard,
          };
          Navigator.pushNamedAndRemoveUntil(context, route, (r) => r.isFirst);
        },
        destinations: [
          NavigationDestination(
              icon: const Icon(Icons.home_outlined),
              label: state.t('nav.home', 'Home')),
          NavigationDestination(
              icon: const Icon(Icons.school_outlined),
              label: state.t('nav.programs', 'Programs')),
          NavigationDestination(
              icon: const Icon(Icons.article_outlined),
              label: state.t('nav.news', 'News')),
          NavigationDestination(
              icon: const Icon(Icons.person_outline),
              label: state.t('student.dashboard.title', 'Student')),
        ],
      ),
    );
  }

  int _selectedIndex(BuildContext context) {
    final route = ModalRoute.of(context)?.settings.name;
    if (route == AppRoutes.programs) return 1;
    if (route == AppRoutes.news) return 2;
    if (route?.startsWith('/student') ?? false) return 3;
    return 0;
  }
}
