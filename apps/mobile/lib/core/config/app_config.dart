class AppConfig {
  const AppConfig._();

  static const androidEmulatorApiBaseUrl = 'http://10.0.2.2:8000/api/v1';
  static const localWebApiBaseUrl = 'http://127.0.0.1:8000/api/v1';
  static const realDeviceTemplate = 'http://YOUR_PC_LOCAL_IP:8000/api/v1';

  static const defaultApiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: androidEmulatorApiBaseUrl,
  );
}
