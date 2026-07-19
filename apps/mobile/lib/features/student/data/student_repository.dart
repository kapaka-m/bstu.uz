import 'dart:io';

import '../../../shared/api_helpers.dart';
import '../../../shared/app_state.dart';

class StudentRepository {
  StudentRepository(this.state);

  final AppState state;

  Future<Map<String, dynamic>> profile() async =>
      asMap(await state.client.get('/student/profile'));
  Future<Map<String, dynamic>> saveProfile(Map<String, dynamic> body) async =>
      asMap(await state.client.put('/student/profile', body: body));

  Future<List<Map<String, dynamic>>> applications() async =>
      asList(await state.client.get('/applications'));
  Future<Map<String, dynamic>> createApplication(
          Map<String, dynamic> body) async =>
      asMap(await state.client.post('/applications', body: body));
  Future<Map<String, dynamic>> updateApplication(
          int id, Map<String, dynamic> body) async =>
      asMap(await state.client.put('/applications/$id', body: body));
  Future<Map<String, dynamic>> submitApplication(int id) async =>
      asMap(await state.client.post('/applications/$id/submit'));

  Future<Map<String, dynamic>> uploadDocument({
    required int applicationId,
    required String documentType,
    required File file,
  }) async {
    return asMap(await state.client.upload(
      '/applications/$applicationId/documents',
      file: file,
      fileField: 'file',
      fields: {
        'document_type': documentType,
        'document_name': documentType,
      },
    ));
  }

  Future<void> deleteDocument(
      {required int applicationId, required int documentId}) {
    return state.client
        .delete('/applications/$applicationId/documents/$documentId');
  }

  Future<List<Map<String, dynamic>>> notifications() async =>
      asList(await state.client.get('/student/notifications'));
  Future<void> markRead(int id) =>
      state.client.patch('/student/notifications/$id/read');
  Future<void> markAllRead() =>
      state.client.post('/student/notifications/read-all');

  Future<List<Map<String, dynamic>>> contracts() async =>
      asList(await state.client.get('/student/contracts'));
  Future<List<Map<String, dynamic>>> payments() async =>
      asList(await state.client.get('/student/payments'));
  Future<List<Map<String, dynamic>>> supportTickets() async =>
      asList(await state.client.get('/student/support-tickets'));
  Future<void> createSupportTicket(Map<String, dynamic> body) =>
      state.client.post('/student/support-tickets', body: body);
}
