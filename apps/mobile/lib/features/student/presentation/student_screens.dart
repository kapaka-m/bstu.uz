import 'dart:io';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/routing/app_router.dart';
import '../../../core/widgets/app_async_view.dart';
import '../../../core/widgets/app_shell.dart';
import '../../../core/widgets/mobile_ui.dart';
import '../../../features/home/data/public_repository.dart';
import '../../../features/student/data/student_repository.dart';
import '../../../shared/api_helpers.dart';
import '../../../shared/app_state.dart';

class StudentDashboardScreen extends StatefulWidget {
  const StudentDashboardScreen({super.key});

  @override
  State<StudentDashboardScreen> createState() => _StudentDashboardScreenState();
}

class _StudentDashboardScreenState extends State<StudentDashboardScreen> {
  bool loading = true;
  Object? error;
  List<Map<String, dynamic>> apps = [];
  List<Map<String, dynamic>> notifications = [];
  List<Map<String, dynamic>> contracts = [];
  List<Map<String, dynamic>> payments = [];

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
      final repo = StudentRepository(AppScope.of(context));
      apps = await repo.applications();
      notifications = await repo.notifications();
      contracts = await repo.contracts();
      payments = await repo.payments();
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
    final app = apps.isNotEmpty ? apps.first : <String, dynamic>{};
    return AppShell(
      titleKey: 'student.dashboard.title',
      body: AppAsyncView(
        loading: loading,
        error: error,
        onRetry: _load,
        child: RefreshIndicator(
          onRefresh: _load,
          child: MobilePage(
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
                    const Icon(Icons.person, color: Colors.white, size: 38),
                    const SizedBox(height: 14),
                    Text(
                      state.t('student.dashboard.title', 'Student dashboard'),
                      style:
                          Theme.of(context).textTheme.headlineSmall?.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.w900,
                              ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _statusLabel(state, app['status']),
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.84),
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              GridView.count(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                crossAxisCount: 2,
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.25,
                children: [
                  _SummaryCard(
                      titleKey: 'application.status',
                      value: _statusLabel(state, app['status'])),
                  _SummaryCard(
                      titleKey: 'application.selectedProgram',
                      value: _programTitle(state, app)),
                  _SummaryCard(
                      titleKey: 'contract.status',
                      value: contracts.isEmpty
                          ? state.t('common.empty')
                          : _statusLabel(state, contracts.first['status'])),
                  _SummaryCard(
                      titleKey: 'payment.status',
                      value: payments.isEmpty
                          ? state.t('common.empty')
                          : _statusLabel(state, payments.first['status'])),
                ],
              ),
              const SizedBox(height: 8),
              const Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _ActionChip(
                      'student.profile.title', AppRoutes.studentProfile),
                  _ActionChip(
                      'application.title', AppRoutes.studentApplication),
                  _ActionChip('document.title', AppRoutes.studentDocuments),
                  _ActionChip(
                      'application.status', AppRoutes.studentApplicationStatus),
                  _ActionChip('support.title', AppRoutes.studentSupport),
                ],
              ),
              const SizedBox(height: 16),
              Text(state.t('notification.latest'),
                  style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              if (notifications.isEmpty)
                Text(state.t('common.empty'))
              else
                for (final item in notifications.take(3))
                  BstuCard(
                    child: ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        state.t(item['title']?.toString() ?? '',
                            item['title']?.toString()),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      subtitle: Text(
                        state.t(item['message']?.toString() ?? '',
                            item['message']?.toString()),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ),
            ],
          ),
        ),
      ),
    );
  }
}

class StudentProfileScreen extends StatefulWidget {
  const StudentProfileScreen({super.key});

  @override
  State<StudentProfileScreen> createState() => _StudentProfileScreenState();
}

class _StudentProfileScreenState extends State<StudentProfileScreen> {
  final fields = <String, TextEditingController>{
    'phone': TextEditingController(),
    'gender': TextEditingController(),
    'birth_date': TextEditingController(),
    'passport_number': TextEditingController(),
    'passport_expiry_date': TextEditingController(),
    'nationality': TextEditingController(),
    'address': TextEditingController(),
    'guardian_name': TextEditingController(),
    'guardian_relation': TextEditingController(),
    'guardian_phone': TextEditingController(),
    'guardian_email': TextEditingController(),
    'education_institution_name': TextEditingController(),
    'education_degree_obtained': TextEditingController(),
    'education_gpa': TextEditingController(),
    'education_graduation_year': TextEditingController(),
  };
  bool saving = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    for (final controller in fields.values) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final profile = await StudentRepository(AppScope.of(context)).profile();
      for (final entry in fields.entries) {
        entry.value.text = profile[entry.key]?.toString() ?? '';
      }
      final guardians = profile['guardians'];
      if (guardians is List && guardians.isNotEmpty) {
        final guardian = asMap(guardians.first);
        fields['guardian_name']!.text = guardian['name']?.toString() ?? '';
        fields['guardian_relation']!.text =
            guardian['relation']?.toString() ?? '';
        fields['guardian_phone']!.text = guardian['phone']?.toString() ?? '';
        fields['guardian_email']!.text = guardian['email']?.toString() ?? '';
      }
      final education = profile['education_backgrounds'];
      if (education is List && education.isNotEmpty) {
        final item = asMap(education.first);
        fields['education_institution_name']!.text =
            item['institution_name']?.toString() ?? '';
        fields['education_degree_obtained']!.text =
            item['degree_obtained']?.toString() ?? '';
        fields['education_gpa']!.text = item['gpa']?.toString() ?? '';
        fields['education_graduation_year']!.text =
            item['graduation_year']?.toString() ?? '';
      }
      if (mounted) {
        setState(() {});
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return AppShell(
      titleKey: 'student.profile.title',
      body: MobilePage(
        children: [
          SectionHeader(
            title: state.t('student.profile.title', 'Profile'),
            subtitle: state.t(
              'student.profile.subtitle',
              'Keep your personal and education details up to date.',
            ),
            icon: Icons.person_outline,
          ),
          BstuCard(
            child: Column(
              children: [
                for (final key in fields.keys)
                  _ProfileField(
                      controller: fields[key]!,
                      labelKey: 'student.profile.$key'),
              ],
            ),
          ),
          const SizedBox(height: 16),
          FilledButton(
              onPressed: saving ? null : _save,
              child: Text(state.t('button.save', 'Save'))),
        ],
      ),
    );
  }

  Future<void> _save() async {
    setState(() => saving = true);
    try {
      await StudentRepository(AppScope.of(context)).saveProfile(
          {for (final entry in fields.entries) entry.key: entry.value.text});
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(AppScope.of(context).t('common.saved', 'Saved'))));
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

class StudentApplicationScreen extends StatefulWidget {
  const StudentApplicationScreen({super.key});

  @override
  State<StudentApplicationScreen> createState() =>
      _StudentApplicationScreenState();
}

class _StudentApplicationScreenState extends State<StudentApplicationScreen> {
  List<Map<String, dynamic>> programs = [];
  Map<String, dynamic>? selectedProgram;
  bool loading = true;
  bool saving = false;
  Object? error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      programs = await PublicRepository(AppScope.of(context)).programs();
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
      titleKey: 'application.title',
      body: AppAsyncView(
        loading: loading,
        error: error,
        onRetry: _load,
        child: MobilePage(
          children: [
            SectionHeader(
              title: state.t('application.title', 'Application'),
              subtitle: state.t(
                'application.subtitle',
                'Choose a study program and start your admission process.',
              ),
              icon: Icons.assignment_outlined,
            ),
            BstuCard(
              child: DropdownButtonFormField<Map<String, dynamic>>(
                initialValue: selectedProgram,
                isExpanded: true,
                decoration:
                    InputDecoration(labelText: state.t('application.program')),
                items: [
                  for (final program in programs)
                    DropdownMenuItem(
                      value: program,
                      child: Text(
                        translatedField(program, 'title', state.locale).isEmpty
                            ? program['slug'].toString()
                            : translatedField(program, 'title', state.locale),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                ],
                onChanged: (value) => setState(() => selectedProgram = value),
              ),
            ),
            const SizedBox(height: 16),
            if (selectedProgram != null) ...[
              _SummaryCard(
                  titleKey: 'application.degreeLevel',
                  value: selectedProgram!['degree']?.toString() ?? ''),
              const SizedBox(height: 10),
              _SummaryCard(
                  titleKey: 'application.languageOfStudy',
                  value:
                      selectedProgram!['language_of_study']?.toString() ?? ''),
              const SizedBox(height: 10),
              _SummaryCard(
                  titleKey: 'application.studyMode',
                  value: selectedProgram!['study_mode']?.toString() ?? ''),
            ],
            const SizedBox(height: 16),
            FilledButton(
                onPressed: saving || selectedProgram == null ? null : _create,
                child:
                    Text(state.t('application.create', 'Create application'))),
          ],
        ),
      ),
    );
  }

  Future<void> _create() async {
    final program = selectedProgram!;
    setState(() => saving = true);
    try {
      await StudentRepository(AppScope.of(context)).createApplication({
        'program_id': program['id'],
        'faculty_id': program['faculty_id'],
        'department_id': program['department_id'],
        'degree_level': program['degree'],
        'language_of_study': program['language_of_study'],
        'study_mode': program['study_mode'],
      });
      if (mounted) {
        Navigator.pushNamed(context, AppRoutes.studentDocuments);
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

class StudentApplicationStatusScreen extends StatelessWidget {
  const StudentApplicationStatusScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return _StudentListScreen(
      titleKey: 'application.status',
      loader: () => StudentRepository(AppScope.of(context)).applications(),
      itemBuilder: (state, item) => ListTile(
        title: Text(_programTitle(state, item)),
        subtitle: Text(_statusLabel(state, item['status'])),
      ),
    );
  }
}

class StudentDocumentsScreen extends StatefulWidget {
  const StudentDocumentsScreen({super.key});

  @override
  State<StudentDocumentsScreen> createState() => _StudentDocumentsScreenState();
}

class _StudentDocumentsScreenState extends State<StudentDocumentsScreen> {
  List<Map<String, dynamic>> apps = [];
  bool loading = true;
  Object? error;

  Map<String, dynamic>? get app => apps.isEmpty ? null : apps.first;

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
      apps = await StudentRepository(AppScope.of(context)).applications();
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
      titleKey: 'document.title',
      body: AppAsyncView(
        loading: loading,
        error: error,
        onRetry: _load,
        child: app == null
            ? const EmptyState(messageKey: 'application.empty')
            : ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  for (final type in AppConstants.documentTypes)
                    Card(
                      child: ListTile(
                        title: Text(state.t('document.type.$type', type)),
                        subtitle: Text(_docStatus(state, type)),
                        trailing: IconButton(
                          icon: const Icon(Icons.upload_file),
                          onPressed: () => _upload(type),
                        ),
                      ),
                    ),
                  const SizedBox(height: 12),
                  FilledButton(
                      onPressed: _submit,
                      child: Text(
                          state.t('application.submit', 'Submit application'))),
                ],
              ),
      ),
    );
  }

  String _docStatus(AppState state, String type) {
    final docs = app?['documents'];
    if (docs is! List) {
      return state.t('status.missing', 'Missing');
    }
    final match =
        docs.whereType<Map>().where((doc) => doc['document_type'] == type);
    if (match.isEmpty) {
      return state.t('status.missing', 'Missing');
    }
    return _statusLabel(state, match.first['status']);
  }

  Future<void> _upload(String type) async {
    final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png', 'webp']);
    final path = result?.files.single.path;
    if (path == null || app == null || !mounted) {
      return;
    }
    final applicationId = app!['id'] as int;
    try {
      await StudentRepository(AppScope.of(context)).uploadDocument(
          applicationId: applicationId, documentType: type, file: File(path));
      await _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.toString())));
      }
    }
  }

  Future<void> _submit() async {
    if (app == null) {
      return;
    }
    try {
      await StudentRepository(AppScope.of(context))
          .submitApplication(app!['id'] as int);
      if (mounted) {
        Navigator.pushNamed(context, AppRoutes.studentApplicationStatus);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.toString())));
      }
    }
  }
}

class StudentNotificationsScreen extends StatelessWidget {
  const StudentNotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return _StudentListScreen(
      titleKey: 'notification.title',
      loader: () => StudentRepository(AppScope.of(context)).notifications(),
      itemBuilder: (state, item) => ListTile(
        title: Text(state.t(
            item['title']?.toString() ?? '', item['title']?.toString())),
        subtitle: Text(state.t(
            item['message']?.toString() ?? '', item['message']?.toString())),
        trailing: item['is_read'] == true
            ? const Icon(Icons.check)
            : const Icon(Icons.circle),
      ),
    );
  }
}

class StudentContractsScreen extends StatelessWidget {
  const StudentContractsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return _StudentListScreen(
      titleKey: 'contract.title',
      loader: () => StudentRepository(AppScope.of(context)).contracts(),
      itemBuilder: (state, item) => ListTile(
        title: Text(
            item['contract_number']?.toString() ?? state.t('contract.title')),
        subtitle: Text(
            '${state.t('payment.amount')}: ${item['amount'] ?? ''} - ${_statusLabel(state, item['status'])}'),
      ),
    );
  }
}

class StudentPaymentsScreen extends StatelessWidget {
  const StudentPaymentsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return _StudentListScreen(
      titleKey: 'payment.title',
      loader: () => StudentRepository(AppScope.of(context)).payments(),
      itemBuilder: (state, item) => ListTile(
        title: Text(
            item['payment_number']?.toString() ?? state.t('payment.title')),
        subtitle: Text(
            '${item['amount'] ?? ''} - ${_statusLabel(state, item['status'])}'),
      ),
    );
  }
}

class StudentSupportScreen extends StatefulWidget {
  const StudentSupportScreen({super.key});

  @override
  State<StudentSupportScreen> createState() => _StudentSupportScreenState();
}

class _StudentSupportScreenState extends State<StudentSupportScreen> {
  final subject = TextEditingController();
  final message = TextEditingController();

  @override
  void dispose() {
    subject.dispose();
    message.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return AppShell(
      titleKey: 'support.title',
      body: MobilePage(
        children: [
          SectionHeader(
            title: state.t('support.title', 'Support'),
            subtitle: state.t(
              'support.subtitle',
              'Create a support ticket and track responses.',
            ),
            icon: Icons.support_agent,
          ),
          BstuCard(
            child: Column(
              children: [
                TextField(
                    controller: subject,
                    decoration:
                        InputDecoration(labelText: state.t('form.subject'))),
                const SizedBox(height: 12),
                TextField(
                    controller: message,
                    decoration:
                        InputDecoration(labelText: state.t('form.message')),
                    maxLines: 5),
              ],
            ),
          ),
          const SizedBox(height: 12),
          FilledButton(
              onPressed: _create,
              child: Text(state.t('support.createTicket', 'Create ticket'))),
          const SizedBox(height: 24),
          _StudentListInline(
              loader: () => StudentRepository(state).supportTickets()),
        ],
      ),
    );
  }

  Future<void> _create() async {
    try {
      await StudentRepository(AppScope.of(context)).createSupportTicket({
        'subject': subject.text,
        'message': message.text,
        'priority': 'normal',
      });
      subject.clear();
      message.clear();
      if (mounted) {
        setState(() {});
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.toString())));
      }
    }
  }
}

class _StudentListScreen extends StatefulWidget {
  const _StudentListScreen(
      {required this.titleKey,
      required this.loader,
      required this.itemBuilder});

  final String titleKey;
  final Future<List<Map<String, dynamic>>> Function() loader;
  final Widget Function(AppState state, Map<String, dynamic> item) itemBuilder;

  @override
  State<_StudentListScreen> createState() => _StudentListScreenState();
}

class _StudentListScreenState extends State<_StudentListScreen> {
  bool loading = true;
  Object? error;
  List<Map<String, dynamic>> items = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      items = await widget.loader();
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
      titleKey: widget.titleKey,
      body: AppAsyncView(
        loading: loading,
        error: error,
        onRetry: _load,
        child: items.isEmpty
            ? const EmptyState(messageKey: 'common.empty')
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: items.length,
                itemBuilder: (context, index) =>
                    Card(child: widget.itemBuilder(state, items[index])),
              ),
      ),
    );
  }
}

class _StudentListInline extends StatefulWidget {
  const _StudentListInline({required this.loader});

  final Future<List<Map<String, dynamic>>> Function() loader;

  @override
  State<_StudentListInline> createState() => _StudentListInlineState();
}

class _StudentListInlineState extends State<_StudentListInline> {
  List<Map<String, dynamic>> items = [];

  @override
  void initState() {
    super.initState();
    widget.loader().then((value) {
      if (mounted) {
        setState(() => items = value);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    if (items.isEmpty) {
      return Text(state.t('common.empty'));
    }
    return Column(
      children: [
        for (final item in items)
          Card(
            child: ListTile(
              title: Text(item['subject']?.toString() ?? ''),
              subtitle: Text(_statusLabel(state, item['status'])),
            ),
          ),
      ],
    );
  }
}

class _ActionChip extends StatelessWidget {
  const _ActionChip(this.labelKey, this.route);

  final String labelKey;
  final String route;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return ActionChip(
      avatar: const Icon(Icons.arrow_forward, size: 16),
      label: Text(state.t(labelKey)),
      backgroundColor: BstuColors.soft,
      side: BorderSide(color: BstuColors.primary.withValues(alpha: 0.12)),
      labelStyle: const TextStyle(
        color: BstuColors.primaryDark,
        fontWeight: FontWeight.w800,
      ),
      onPressed: () => Navigator.pushNamed(context, route),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({required this.titleKey, required this.value});

  final String titleKey;
  final String value;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return BstuCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            state.t(titleKey),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: BstuColors.muted,
              fontSize: 12,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            value.isEmpty ? state.t('common.empty') : value,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: BstuColors.navy,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}

class _ProfileField extends StatelessWidget {
  const _ProfileField({required this.controller, required this.labelKey});

  final TextEditingController controller;
  final String labelKey;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextField(
          controller: controller,
          decoration: InputDecoration(
              labelText: state.t(labelKey, labelKey.split('.').last))),
    );
  }
}

String _statusLabel(AppState state, Object? status) {
  final value = status?.toString() ?? '';
  return value.isEmpty
      ? state.t('common.empty')
      : state.t('status.$value', value);
}

String _programTitle(AppState state, Map<String, dynamic> app) {
  final program = asMap(app['program']);
  if (program.isEmpty) {
    return state.t('common.empty');
  }
  final translated = translatedField(program, 'title', state.locale);
  return translated.isEmpty
      ? program['slug']?.toString() ?? state.t('common.empty')
      : translated;
}
