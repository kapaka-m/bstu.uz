import 'package:flutter/material.dart';

import '../../../core/routing/app_router.dart';
import '../../../core/widgets/app_async_view.dart';
import '../../../core/widgets/app_shell.dart';
import '../../../core/widgets/mobile_ui.dart';
import '../../../features/home/data/public_repository.dart';
import '../../../shared/api_helpers.dart';
import '../../../shared/app_state.dart';

enum PublicContentKind {
  faculties,
  departments,
  programs,
  news,
  announcements,
  services
}

class PublicListScreen extends StatefulWidget {
  const PublicListScreen({required this.kind, super.key});

  final PublicContentKind kind;

  @override
  State<PublicListScreen> createState() => _PublicListScreenState();
}

class _PublicListScreenState extends State<PublicListScreen> {
  bool loading = true;
  Object? error;
  List<Map<String, dynamic>> items = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final repo = PublicRepository(AppScope.of(context));
      items = switch (widget.kind) {
        PublicContentKind.faculties => await repo.faculties(),
        PublicContentKind.departments => await repo.departments(),
        PublicContentKind.programs => await repo.programs(),
        PublicContentKind.news => await repo.news(),
        PublicContentKind.announcements => await repo.announcements(),
        PublicContentKind.services => await repo.services(),
      };
    } catch (e) {
      error = e;
    }
    if (mounted) {
      setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return AppShell(
      titleKey: _titleKey(widget.kind),
      body: AppAsyncView(
        loading: loading,
        error: error,
        onRetry: _load,
        child: items.isEmpty
            ? const EmptyState(messageKey: 'common.empty')
            : RefreshIndicator(
                onRefresh: _load,
                child: ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                  itemCount: items.length + 1,
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    if (index == 0) {
                      return SectionHeader(
                        title: state.t(_titleKey(widget.kind)),
                        subtitle: state.t(
                          'common.browse',
                          'Browse the latest information from BSTU.',
                        ),
                        icon: _kindIcon(widget.kind),
                      );
                    }

                    final item = items[index - 1];
                    final title =
                        translatedField(item, 'title', state.locale).isNotEmpty
                            ? translatedField(item, 'title', state.locale)
                            : translatedField(item, 'name', state.locale);
                    final subtitle = translatedField(
                                item, 'short_description', state.locale)
                            .isNotEmpty
                        ? translatedField(
                            item, 'short_description', state.locale)
                        : translatedField(item, 'description', state.locale);

                    return _ContentCard(
                      title: title,
                      subtitle: subtitle,
                      icon: _kindIcon(widget.kind),
                      meta: _metaFor(widget.kind, item),
                      onTap: widget.kind == PublicContentKind.services
                          ? null
                          : () => Navigator.pushNamed(
                                context,
                                _detailRoute(widget.kind),
                                arguments: {
                                  'kind': widget.kind.name,
                                  'item': item,
                                },
                              ),
                    );
                  },
                ),
              ),
      ),
    );
  }
}

class _ContentCard extends StatelessWidget {
  const _ContentCard({
    required this.title,
    required this.icon,
    this.subtitle = '',
    this.meta = '',
    this.onTap,
  });

  final String title;
  final String subtitle;
  final String meta;
  final IconData icon;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return BstuCard(
      onTap: onTap,
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 46,
            height: 46,
            decoration: BoxDecoration(
              color: BstuColors.primary.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Icon(icon, color: BstuColors.primary),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (meta.isNotEmpty) ...[
                  Text(
                    meta,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: BstuColors.primary,
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 4),
                ],
                Text(
                  title.isEmpty ? 'BSTU' : title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: BstuColors.navy,
                    fontWeight: FontWeight.w900,
                    height: 1.2,
                  ),
                ),
                if (subtitle.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Text(
                    subtitle,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: BstuColors.muted,
                      fontSize: 12,
                      height: 1.35,
                    ),
                  ),
                ],
              ],
            ),
          ),
          if (onTap != null) ...[
            const SizedBox(width: 8),
            const Icon(Icons.chevron_right, color: BstuColors.muted),
          ],
        ],
      ),
    );
  }
}

class PublicDetailScreen extends StatelessWidget {
  const PublicDetailScreen({required this.arguments, super.key});

  final Object? arguments;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final args = arguments is Map
        ? Map<String, dynamic>.from(arguments as Map)
        : <String, dynamic>{};
    final item = asMap(args['item']);
    final title = translatedField(item, 'title', state.locale).isNotEmpty
        ? translatedField(item, 'title', state.locale)
        : translatedField(item, 'name', state.locale);
    final description =
        translatedField(item, 'description', state.locale).isNotEmpty
            ? translatedField(item, 'description', state.locale)
            : translatedField(item, 'short_description', state.locale);

    return Scaffold(
      appBar: AppBar(
          title: Text(
              title.isEmpty ? state.t('common.details', 'Details') : title)),
      body: MobilePage(
        children: [
          Container(
            padding: const EdgeInsets.all(22),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(28),
              gradient: const LinearGradient(
                colors: [BstuColors.primary, Color(0xff2563eb)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.school, color: Colors.white, size: 36),
                const SizedBox(height: 16),
                Text(
                  title.isEmpty ? state.t('common.details', 'Details') : title,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        height: 1.12,
                      ),
                ),
                if ((item['slug'] ?? '').toString().isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Text(
                    item['slug'].toString(),
                    style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.78),
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 16),
          if ((item['image'] ?? '').toString().isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(22),
              child: Image.network(
                item['image'].toString(),
                height: 190,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => const SizedBox.shrink(),
              ),
            ),
          BstuCard(
            child: Text(
              description.isEmpty
                  ? state.t('common.noDescription', 'No description available.')
                  : description,
              style: const TextStyle(
                color: BstuColors.muted,
                height: 1.55,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (item['degree'] != null ||
              item['study_mode'] != null ||
              item['language_of_study'] != null ||
              item['tuition_fee'] != null)
            BstuCard(
              child: Column(
                children: [
                  if (item['degree'] != null)
                    _InfoRow(
                        labelKey: 'application.degreeLevel',
                        value: item['degree'].toString()),
                  if (item['study_mode'] != null)
                    _InfoRow(
                        labelKey: 'application.studyMode',
                        value: item['study_mode'].toString()),
                  if (item['language_of_study'] != null)
                    _InfoRow(
                        labelKey: 'application.languageOfStudy',
                        value: item['language_of_study'].toString()),
                  if (item['tuition_fee'] != null)
                    _InfoRow(
                        labelKey: 'payment.amount',
                        value:
                            '${item['tuition_fee']} ${item['currency'] ?? ''}'),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

class ContactScreen extends StatefulWidget {
  const ContactScreen({super.key});

  @override
  State<ContactScreen> createState() => _ContactScreenState();
}

class _ContactScreenState extends State<ContactScreen> {
  final formKey = GlobalKey<FormState>();
  final name = TextEditingController();
  final email = TextEditingController();
  final subject = TextEditingController();
  final message = TextEditingController();
  bool saving = false;

  @override
  void dispose() {
    name.dispose();
    email.dispose();
    subject.dispose();
    message.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return AppShell(
      titleKey: 'contact.title',
      body: Form(
        key: formKey,
        child: MobilePage(
          children: [
            SectionHeader(
              title: state.t('contact.title', 'Contact'),
              subtitle: state.t(
                'contact.subtitle',
                'Send a message to the international office.',
              ),
              icon: Icons.mail_outline,
            ),
            BstuCard(
              child: Column(
                children: [
                  _TextField(controller: name, labelKey: 'form.name'),
                  _TextField(
                      controller: email,
                      labelKey: 'form.email',
                      keyboardType: TextInputType.emailAddress),
                  _TextField(controller: subject, labelKey: 'form.subject'),
                  _TextField(
                      controller: message,
                      labelKey: 'form.message',
                      maxLines: 5),
                ],
              ),
            ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: saving ? null : _submit,
              child: saving
                  ? const CircularProgressIndicator()
                  : Text(state.t('button.submit', 'Submit')),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!formKey.currentState!.validate()) return;
    setState(() => saving = true);
    try {
      await PublicRepository(AppScope.of(context)).inquiry({
        'name': name.text,
        'email': email.text,
        'subject': subject.text,
        'message': message.text,
      });
      if (mounted) {
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.toString())));
      }
    }
    if (mounted) {
      setState(() => saving = false);
    }
  }
}

class _TextField extends StatelessWidget {
  const _TextField(
      {required this.controller,
      required this.labelKey,
      this.keyboardType,
      this.maxLines = 1});

  final TextEditingController controller;
  final String labelKey;
  final TextInputType? keyboardType;
  final int maxLines;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        controller: controller,
        keyboardType: keyboardType,
        maxLines: maxLines,
        decoration: InputDecoration(labelText: state.t(labelKey)),
        validator: (value) => value == null || value.trim().isEmpty
            ? state.t('validation.required', 'Required')
            : null,
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.labelKey, required this.value});

  final String labelKey;
  final String value;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
              child: Text(state.t(labelKey),
                  style: const TextStyle(
                      fontWeight: FontWeight.w800, color: BstuColors.navy))),
          Expanded(
              child:
                  Text(value, style: const TextStyle(color: BstuColors.muted))),
        ],
      ),
    );
  }
}

IconData _kindIcon(PublicContentKind kind) => switch (kind) {
      PublicContentKind.faculties => Icons.account_balance,
      PublicContentKind.departments => Icons.business,
      PublicContentKind.programs => Icons.school,
      PublicContentKind.news => Icons.article,
      PublicContentKind.announcements => Icons.campaign,
      PublicContentKind.services => Icons.support_agent,
    };

String _metaFor(PublicContentKind kind, Map<String, dynamic> item) {
  if (kind == PublicContentKind.programs) {
    return [
      item['display_code'] ?? item['official_code'] ?? item['code'],
      item['degree'],
    ]
        .where((value) => value != null && value.toString().isNotEmpty)
        .join(' / ');
  }
  if (kind == PublicContentKind.news ||
      kind == PublicContentKind.announcements) {
    return (item['published_at'] ?? item['category'] ?? '').toString();
  }
  return (item['code'] ?? item['slug'] ?? '').toString();
}

String _titleKey(PublicContentKind kind) => switch (kind) {
      PublicContentKind.faculties => 'nav.faculties',
      PublicContentKind.departments => 'nav.departments',
      PublicContentKind.programs => 'nav.programs',
      PublicContentKind.news => 'nav.news',
      PublicContentKind.announcements => 'nav.announcements',
      PublicContentKind.services => 'nav.services',
    };

String _detailRoute(PublicContentKind kind) => switch (kind) {
      PublicContentKind.faculties => AppRoutes.facultyDetails,
      PublicContentKind.departments => AppRoutes.departmentDetails,
      PublicContentKind.programs => AppRoutes.programDetails,
      PublicContentKind.news => AppRoutes.newsDetails,
      PublicContentKind.announcements => AppRoutes.announcementDetails,
      PublicContentKind.services => AppRoutes.services,
    };
