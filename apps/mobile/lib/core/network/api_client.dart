import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../errors/api_exception.dart';
import '../storage/secure_store.dart';

class ApiClient {
  ApiClient(
      {required this.baseUrl, required this.store, http.Client? httpClient})
      : _http = httpClient ?? http.Client();

  final String baseUrl;
  final SecureStore store;
  final http.Client _http;

  Future<dynamic> get(String path, {Map<String, String>? query}) {
    return _send('GET', path, query: query);
  }

  Future<dynamic> post(String path,
      {Map<String, dynamic>? body, Map<String, String>? query}) {
    return _send('POST', path, body: body, query: query);
  }

  Future<dynamic> put(String path,
      {Map<String, dynamic>? body, Map<String, String>? query}) {
    return _send('PUT', path, body: body, query: query);
  }

  Future<dynamic> patch(String path,
      {Map<String, dynamic>? body, Map<String, String>? query}) {
    return _send('PATCH', path, body: body, query: query);
  }

  Future<dynamic> delete(String path, {Map<String, String>? query}) {
    return _send('DELETE', path, query: query);
  }

  Future<dynamic> upload(
    String path, {
    required File file,
    required String fileField,
    required Map<String, String> fields,
    Map<String, String>? query,
  }) async {
    final request = http.MultipartRequest('POST', _uri(path, query));
    request.headers.addAll(await _headers(json: false));
    request.fields.addAll(fields);
    request.files.add(await http.MultipartFile.fromPath(fileField, file.path));
    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);
    return _decode(response);
  }

  Future<dynamic> _send(
    String method,
    String path, {
    Map<String, dynamic>? body,
    Map<String, String>? query,
  }) async {
    final uri = _uri(path, query);
    final headers = await _headers();
    late final http.Response response;

    switch (method) {
      case 'POST':
        response = await _http.post(uri,
            headers: headers, body: jsonEncode(body ?? {}));
      case 'PUT':
        response = await _http.put(uri,
            headers: headers, body: jsonEncode(body ?? {}));
      case 'PATCH':
        response = await _http.patch(uri,
            headers: headers, body: jsonEncode(body ?? {}));
      case 'DELETE':
        response = await _http.delete(uri, headers: headers);
      default:
        response = await _http.get(uri, headers: headers);
    }

    return _decode(response);
  }

  Uri _uri(String path, Map<String, String>? query) {
    final cleanBase = baseUrl.endsWith('/')
        ? baseUrl.substring(0, baseUrl.length - 1)
        : baseUrl;
    final cleanPath = path.startsWith('/') ? path : '/$path';
    final uri = Uri.parse('$cleanBase$cleanPath');
    if (query == null || query.isEmpty) return uri;
    return uri.replace(queryParameters: {...uri.queryParameters, ...query});
  }

  Future<Map<String, String>> _headers({bool json = true}) async {
    final token = await store.getToken();
    return {
      'Accept': 'application/json',
      if (json) 'Content-Type': 'application/json',
      if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
    };
  }

  dynamic _decode(http.Response response) {
    final status = response.statusCode;
    final rawBody = utf8.decode(response.bodyBytes);
    final body = rawBody.isEmpty ? <String, dynamic>{} : jsonDecode(rawBody);

    if (status >= 200 && status < 300) {
      if (body is Map<String, dynamic> && body.containsKey('data')) {
        return body['data'];
      }
      return body;
    }

    if (body is Map<String, dynamic>) {
      throw ApiException(
        (body['message'] ?? 'Request failed').toString(),
        statusCode: status,
        errors: body['errors'] is Map<String, dynamic>
            ? body['errors'] as Map<String, dynamic>
            : null,
      );
    }

    throw ApiException('Request failed', statusCode: status);
  }
}
