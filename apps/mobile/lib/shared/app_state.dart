import 'package:flutter/material.dart';

import '../core/constants/app_constants.dart';
import '../core/network/api_client.dart';
import '../core/storage/secure_store.dart';

class AppState extends ChangeNotifier {
  AppState({required this.client, required this.store});

  final ApiClient client;
  final SecureStore store;

  String locale = 'en';
  Map<String, dynamic> translations = {};
  Map<String, dynamic>? user;
  bool bootstrapped = false;

  bool get isRtl => AppConstants.rtlLocales.contains(locale);
  bool get isAuthenticated => user != null;

  Future<void> bootstrap() async {
    locale = await store.getLocale() ?? 'en';
    user = await store.getUser();
    await loadTranslations(locale);
    bootstrapped = true;
    notifyListeners();
  }

  Future<void> setLocale(String value) async {
    if (!AppConstants.supportedLocales.contains(value)) return;
    locale = value;
    await store.saveLocale(value);
    await loadTranslations(value);
    notifyListeners();
  }

  Future<void> loadTranslations(String value) async {
    try {
      final data = await client.get('/translations', query: {'locale': value});
      translations = data is Map<String, dynamic> ? data : {};
    } catch (_) {
      translations = {};
    }
  }

  String t(String key, [String? fallback]) {
    dynamic current = translations;
    for (final part in key.split('.')) {
      if (current is Map && current.containsKey(part)) {
        current = current[part];
      } else {
        return fallback ?? key;
      }
    }
    return current?.toString() ?? fallback ?? key;
  }

  Future<void> setAuth(
      {required String token, required Map<String, dynamic> userData}) async {
    await store.saveToken(token);
    await store.saveUser(userData);
    user = userData;
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await client.post('/auth/logout');
    } catch (_) {
      // Local logout should still happen if the token is already expired.
    }
    await store.clearAuth();
    user = null;
    notifyListeners();
  }
}

class AppScope extends InheritedWidget {
  const AppScope({required this.state, required super.child, super.key});

  final AppState state;

  static AppState of(BuildContext context) {
    final scope = context
        .getElementForInheritedWidgetOfExactType<AppScope>()
        ?.widget as AppScope?;
    assert(scope != null, 'AppScope not found');
    return scope!.state;
  }

  @override
  bool updateShouldNotify(AppScope oldWidget) => true;
}
