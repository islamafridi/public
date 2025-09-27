import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/app_model.dart';

class ApiService {
  // Default URLs for different environments
  static const String _localUrl = 'http://10.0.2.2/wp-json/apk-templates/v1'; // For emulator
  static const String _defaultUrl = 'https://5plays.local/wp-json/apk-templates/v1'; // For physical device

  // Dynamic base URL - can be changed at runtime
  static String _baseUrl = 'https://5plays.org/wp-json/apk-templates/v1';

  // Getter for current base URL
  static String get baseUrl => _baseUrl;

  // Method to update base URL
  static void setBaseUrl(String url) {
    _baseUrl = url.endsWith('/wp-json/apk-templates/v1')
        ? url
        : '$url/wp-json/apk-templates/v1';
  }

  // Method to use local emulator URL
  static void useLocalUrl() {
    _baseUrl = _localUrl;
  }

  // Method to use production URL
  static void useProductionUrl(String domain) {
    _baseUrl = '$domain/wp-json/apk-templates/v1';
  }

  static Future<AppsResponse> getApps({
    int page = 1,
    int perPage = 10,
    String? search,
    String? category,
  }) async {
    final Map<String, String> queryParams = {
      'page': page.toString(),
      'per_page': perPage.toString(),
    };

    if (search != null && search.isNotEmpty) {
      queryParams['search'] = search;
    }

    if (category != null && category.isNotEmpty) {
      queryParams['category'] = category;
    }

    final uri = Uri.parse('$baseUrl/apps').replace(queryParameters: queryParams);

    try {
      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        return AppsResponse.fromJson(data);
      } else {
        throw ApiException('Failed to load apps: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: $e');
    }
  }

  static Future<AppModel> getApp(int id) async {
    final uri = Uri.parse('$baseUrl/apps/$id');

    try {
      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        return AppModel.fromJson(data);
      } else if (response.statusCode == 404) {
        throw ApiException('App not found');
      } else {
        throw ApiException('Failed to load app: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: $e');
    }
  }

  static Future<List<AppCategory>> getCategories() async {
    final uri = Uri.parse('$baseUrl/categories');

    try {
      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((json) => AppCategory.fromJson(json)).toList();
      } else {
        throw ApiException('Failed to load categories: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: $e');
    }
  }

  static Future<Map<String, dynamic>> getApiSettings() async {
    final uri = Uri.parse('$baseUrl/settings');

    try {
      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw ApiException('Failed to load API settings: ${response.statusCode}');
      }
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('Network error: $e');
    }
  }
}

class ApiException implements Exception {
  final String message;

  ApiException(this.message);

  @override
  String toString() => 'ApiException: $message';
}