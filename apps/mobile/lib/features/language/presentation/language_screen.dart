import 'package:flutter/material.dart';

import '../../../core/constants/app_constants.dart';
import '../../../shared/app_state.dart';

class LanguageScreen extends StatelessWidget {
  const LanguageScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    return Scaffold(
      appBar: AppBar(title: Text(state.t('settings.language', 'Language'))),
      body: RadioGroup<String>(
        groupValue: state.locale,
        onChanged: (value) async {
          if (value == null) return;
          await state.setLocale(value);
          if (context.mounted) Navigator.pop(context);
        },
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            for (final locale in AppConstants.supportedLocales)
              Card(
                child: RadioListTile<String>(
                  value: locale,
                  selected: locale == state.locale,
                  title: Text(state.t('locale.$locale', locale)),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
