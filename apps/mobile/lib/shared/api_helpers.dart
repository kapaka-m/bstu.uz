List<Map<String, dynamic>> asList(dynamic value) {
  if (value is List) {
    return value
        .whereType<Map>()
        .map((item) => Map<String, dynamic>.from(item))
        .toList();
  }
  if (value is Map && value['data'] is List) {
    return asList(value['data']);
  }
  return [];
}

Map<String, dynamic> asMap(dynamic value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) return Map<String, dynamic>.from(value);
  return {};
}

String translatedField(Map<String, dynamic> item, String field, String locale) {
  final translations = item['translations'];
  if (translations is List) {
    final exact = translations
        .whereType<Map>()
        .cast<Map>()
        .where((entry) => entry['locale'] == locale);
    final fallback = translations.whereType<Map>().cast<Map>();
    final source = exact.isNotEmpty
        ? exact.first
        : (fallback.isNotEmpty ? fallback.first : null);
    final value = source?[field];
    if (value != null && value.toString().isNotEmpty) return value.toString();
  }
  final direct = item[field] ??
      item['name'] ??
      item['title'] ??
      item['slug'] ??
      item['id'];
  return direct?.toString() ?? '';
}
