import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStore {
  SecureStore() : _storage = const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  static const _tokenKey = 'auth_token';
  static const _localeKey = 'locale';
  static const _userKey = 'user';

  Future<String?> getToken() => _storage.read(key: _tokenKey);
  Future<void> saveToken(String token) =>
      _storage.write(key: _tokenKey, value: token);
  Future<void> clearToken() => _storage.delete(key: _tokenKey);

  Future<String?> getLocale() => _storage.read(key: _localeKey);
  Future<void> saveLocale(String locale) =>
      _storage.write(key: _localeKey, value: locale);

  Future<Map<String, dynamic>?> getUser() async {
    final raw = await _storage.read(key: _userKey);
    if (raw == null || raw.isEmpty) return null;
    return jsonDecode(raw) as Map<String, dynamic>;
  }

  Future<void> saveUser(Map<String, dynamic> user) {
    return _storage.write(key: _userKey, value: jsonEncode(user));
  }

  Future<void> clearUser() => _storage.delete(key: _userKey);

  Future<void> clearAuth() async {
    await clearToken();
    await clearUser();
  }
}
