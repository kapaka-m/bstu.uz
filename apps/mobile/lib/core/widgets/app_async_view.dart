import 'package:flutter/material.dart';

import '../../shared/app_state.dart';
import 'mobile_ui.dart';

class AppAsyncView extends StatelessWidget {
  const AppAsyncView({
    required this.loading,
    required this.error,
    required this.onRetry,
    required this.child,
    super.key,
  });

  final bool loading;
  final Object? error;
  final VoidCallback onRetry;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    if (loading) {
      return const Center(
        child: SizedBox(
          width: 34,
          height: 34,
          child: CircularProgressIndicator(strokeWidth: 3),
        ),
      );
    }
    if (error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: BstuCard(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.wifi_off, color: BstuColors.primary, size: 42),
                const SizedBox(height: 12),
                Text(
                  state.t('common.error', 'Something went wrong'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontWeight: FontWeight.w900,
                    color: BstuColors.navy,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  error.toString(),
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: BstuColors.muted),
                ),
                const SizedBox(height: 16),
                FilledButton(
                    onPressed: onRetry,
                    child: Text(state.t('common.retry', 'Retry'))),
              ],
            ),
          ),
        ),
      );
    }
    return child;
  }
}

class EmptyState extends StatelessWidget {
  const EmptyState({required this.messageKey, super.key});

  final String messageKey;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: BstuCard(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.inbox_outlined,
                  color: BstuColors.primary, size: 40),
              const SizedBox(height: 10),
              Text(
                state.t(messageKey),
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: BstuColors.muted,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
