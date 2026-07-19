import 'package:flutter/material.dart';

import '../../../core/routing/app_router.dart';
import '../../../core/widgets/mobile_ui.dart';
import '../../../features/auth/data/auth_repository.dart';
import '../../../shared/app_state.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final email = TextEditingController();
  final password = TextEditingController();
  bool saving = false;

  @override
  void dispose() {
    email.dispose();
    password.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return _AuthScaffold(
      titleKey: 'auth.login',
      children: [
        _AuthField(
            controller: email,
            labelKey: 'form.email',
            keyboardType: TextInputType.emailAddress),
        _AuthField(
            controller: password, labelKey: 'form.password', obscureText: true),
        FilledButton(
            onPressed: saving ? null : _login,
            child: Text(state.t('auth.login', 'Login'))),
        TextButton(
          onPressed: () =>
              Navigator.pushReplacementNamed(context, AppRoutes.register),
          child: Text(state.t('auth.register', 'Register')),
        ),
        TextButton(
          onPressed: () =>
              Navigator.pushNamed(context, AppRoutes.forgotPassword),
          child: Text(state.t('auth.forgotPassword', 'Forgot password')),
        ),
      ],
    );
  }

  Future<void> _login() async {
    setState(() => saving = true);
    try {
      await AuthRepository(AppScope.of(context))
          .login(email: email.text.trim(), password: password.text);
      if (mounted) {
        Navigator.pushNamedAndRemoveUntil(
            context, AppRoutes.studentDashboard, (_) => false);
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

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final name = TextEditingController();
  final email = TextEditingController();
  final password = TextEditingController();
  bool saving = false;

  @override
  void dispose() {
    name.dispose();
    email.dispose();
    password.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return _AuthScaffold(
      titleKey: 'auth.register',
      children: [
        _AuthField(controller: name, labelKey: 'form.name'),
        _AuthField(
            controller: email,
            labelKey: 'form.email',
            keyboardType: TextInputType.emailAddress),
        _AuthField(
            controller: password, labelKey: 'form.password', obscureText: true),
        FilledButton(
            onPressed: saving ? null : _register,
            child: Text(state.t('auth.register', 'Register'))),
        TextButton(
          onPressed: () =>
              Navigator.pushReplacementNamed(context, AppRoutes.login),
          child: Text(state.t('auth.login', 'Login')),
        ),
      ],
    );
  }

  Future<void> _register() async {
    setState(() => saving = true);
    try {
      await AuthRepository(AppScope.of(context)).register(
          name: name.text.trim(),
          email: email.text.trim(),
          password: password.text);
      if (mounted) {
        Navigator.pushNamedAndRemoveUntil(
            context, AppRoutes.studentDashboard, (_) => false);
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

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final email = TextEditingController();
  bool saving = false;

  @override
  void dispose() {
    email.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return _AuthScaffold(
      titleKey: 'auth.forgotPassword',
      children: [
        _AuthField(
            controller: email,
            labelKey: 'form.email',
            keyboardType: TextInputType.emailAddress),
        FilledButton(
            onPressed: saving ? null : _send,
            child: Text(state.t('button.submit', 'Submit'))),
      ],
    );
  }

  Future<void> _send() async {
    setState(() => saving = true);
    try {
      await AuthRepository(AppScope.of(context))
          .forgotPassword(email.text.trim());
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

class _AuthScaffold extends StatelessWidget {
  const _AuthScaffold({required this.titleKey, required this.children});

  final String titleKey;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return Scaffold(
      appBar: AppBar(title: Text(state.t(titleKey))),
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
                const Icon(Icons.school, size: 46, color: Colors.white),
                const SizedBox(height: 14),
                Text(
                  state.t(titleKey),
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                      ),
                ),
                const SizedBox(height: 6),
                Text(
                  state.t('app.name', 'BSTU International'),
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.82),
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          BstuCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: children,
            ),
          ),
        ],
      ),
    );
  }
}

class _AuthField extends StatelessWidget {
  const _AuthField({
    required this.controller,
    required this.labelKey,
    this.keyboardType,
    this.obscureText = false,
  });

  final TextEditingController controller;
  final String labelKey;
  final TextInputType? keyboardType;
  final bool obscureText;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextField(
        controller: controller,
        keyboardType: keyboardType,
        obscureText: obscureText,
        decoration: InputDecoration(labelText: state.t(labelKey)),
      ),
    );
  }
}
