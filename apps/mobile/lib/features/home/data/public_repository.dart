import '../../../shared/api_helpers.dart';
import '../../../shared/app_state.dart';

class PublicRepository {
  PublicRepository(this.state);

  final AppState state;

  Future<List<Map<String, dynamic>>> faculties() async =>
      asList(await state.client.get('/faculties', query: _locale));
  Future<Map<String, dynamic>> faculty(String slug) async =>
      asMap(await state.client.get('/faculties/$slug', query: _locale));

  Future<List<Map<String, dynamic>>> departments() async =>
      asList(await state.client.get('/departments', query: _locale));
  Future<Map<String, dynamic>> department(String slug) async =>
      asMap(await state.client.get('/departments/$slug', query: _locale));

  Future<List<Map<String, dynamic>>> programs() async =>
      asList(await state.client.get('/programs', query: _locale));
  Future<Map<String, dynamic>> program(String slug) async =>
      asMap(await state.client.get('/programs/$slug', query: _locale));

  Future<List<Map<String, dynamic>>> news() async =>
      asList(await state.client.get('/news', query: _locale));
  Future<Map<String, dynamic>> newsItem(String slug) async =>
      asMap(await state.client.get('/news/$slug', query: _locale));

  Future<List<Map<String, dynamic>>> announcements() async =>
      asList(await state.client.get('/announcements', query: _locale));
  Future<Map<String, dynamic>> announcement(String slug) async =>
      asMap(await state.client.get('/announcements/$slug', query: _locale));

  Future<List<Map<String, dynamic>>> services() async =>
      asList(await state.client.get('/services', query: _locale));

  Future<void> inquiry(Map<String, dynamic> body) =>
      state.client.post('/inquiries', body: body);

  Map<String, String> get _locale => {'locale': state.locale};
}
