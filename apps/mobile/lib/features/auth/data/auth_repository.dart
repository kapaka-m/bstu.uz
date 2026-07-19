import '../../../shared/api_helpers.dart';
import '../../../shared/app_state.dart';

class AuthRepository {
  AuthRepository(this.state);

  final AppState state;

  Future<void> login({required String email, required String password}) async {
    final data = asMap(await state.client.post('/auth/login', body: {
      'email': email,
      'password': password,
    }));
    await _saveAuth(data);
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
  }) async {
    final data = asMap(await state.client.post('/auth/register', body: {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': password,
    }));
    await _saveAuth(data);
  }

  Future<void> forgotPassword(String email) {
    return state.client.post('/auth/forgot-password', body: {'email': email});
  }

  Future<void> _saveAuth(Map<String, dynamic> data) async {
    final token = (data['access_token'] ?? data['token']).toString();
    final user = asMap(data['user']);
    await state.setAuth(token: token, userData: user);
  }
}
