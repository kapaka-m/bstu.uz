import 'package:flutter/material.dart';

import '../../../core/routing/app_router.dart';
import '../../../core/widgets/app_shell.dart';
import '../../../core/widgets/mobile_ui.dart';
import '../../../shared/app_state.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    const items = [
      _HomeItem('nav.faculties', Icons.account_balance, AppRoutes.faculties),
      _HomeItem('nav.departments', Icons.business, AppRoutes.departments),
      _HomeItem('nav.programs', Icons.school, AppRoutes.programs),
      _HomeItem('nav.news', Icons.article, AppRoutes.news),
      _HomeItem('nav.announcements', Icons.campaign, AppRoutes.announcements),
      _HomeItem('nav.services', Icons.support_agent, AppRoutes.services),
      _HomeItem('contact.title', Icons.mail, AppRoutes.contact),
      _HomeItem(
          'student.dashboard.title', Icons.person, AppRoutes.studentDashboard),
    ];

    return AppShell(
      titleKey: 'app.name',
      body: MobilePage(
        children: [
          Container(
            padding: const EdgeInsets.all(22),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(28),
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [BstuColors.primary, Color(0xff2563eb)],
              ),
              boxShadow: [
                BoxShadow(
                  color: BstuColors.primary.withValues(alpha: 0.22),
                  blurRadius: 24,
                  offset: const Offset(0, 14),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.14),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.white24),
                  ),
                  child:
                      const Icon(Icons.school, color: Colors.white, size: 34),
                ),
                const SizedBox(height: 16),
                Text(
                  state.t('home.hero.title', 'BSTU International'),
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        height: 1.1,
                      ),
                ),
                const SizedBox(height: 10),
                Text(
                  state.t('home.hero.subtitle', 'Study in Uzbekistan'),
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: Colors.white.withValues(alpha: 0.86),
                        height: 1.45,
                      ),
                  maxLines: 4,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 18),
                const Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    InfoPill(label: '15,000+', icon: Icons.groups_outlined),
                    InfoPill(label: 'International', icon: Icons.public),
                    InfoPill(label: 'Programs', icon: Icons.school_outlined),
                  ],
                ),
                const SizedBox(height: 18),
                FilledButton.icon(
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: BstuColors.primaryDark,
                    minimumSize: const Size.fromHeight(48),
                  ),
                  onPressed: () =>
                      Navigator.pushNamed(context, AppRoutes.programs),
                  icon: const Icon(Icons.arrow_forward),
                  label: Text(state.t('nav.programs', 'Programs')),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          SectionHeader(
            title: state.t('nav.home', 'Home'),
            subtitle: state.t(
              'home.mobile.quickAccess',
              'Quick access to public information and the student portal.',
            ),
            icon: Icons.dashboard_outlined,
          ),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: items.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 14,
              crossAxisSpacing: 14,
              childAspectRatio: 1.08,
            ),
            itemBuilder: (context, index) {
              final item = items[index];
              return BstuCard(
                onTap: () => Navigator.pushNamed(context, item.route),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: BstuColors.primary.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(15),
                      ),
                      child: Icon(item.icon, color: BstuColors.primary),
                    ),
                    const Spacer(),
                    Text(
                      state.t(item.key),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        color: BstuColors.navy,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Icon(Icons.arrow_forward,
                        size: 16, color: BstuColors.muted),
                  ],
                ),
              );
            },
          ),
          const SizedBox(height: 18),
          BstuCard(
            color: const Color(0xfff1f5f9),
            onTap: () => Navigator.pushNamed(context, AppRoutes.contact),
            child: Row(
              children: [
                const Icon(Icons.support_agent, color: BstuColors.primary),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    state.t('contact.title', 'Contact'),
                    style: const TextStyle(fontWeight: FontWeight.w900),
                  ),
                ),
                const Icon(Icons.chevron_right, color: BstuColors.muted),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HomeItem {
  const _HomeItem(this.key, this.icon, this.route);
  final String key;
  final IconData icon;
  final String route;
}
