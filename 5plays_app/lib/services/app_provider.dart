import 'package:flutter/foundation.dart';
import '../models/app_model.dart';
import 'api_service.dart';

class AppProvider extends ChangeNotifier {
  List<AppModel> _apps = [];
  List<AppCategory> _categories = [];
  bool _isLoading = false;
  bool _hasError = false;
  String _errorMessage = '';
  Pagination? _pagination;
  int _currentPage = 1;
  String _searchQuery = '';
  String? _selectedCategory;

  List<AppModel> get apps => _apps;
  List<AppCategory> get categories => _categories;
  bool get isLoading => _isLoading;
  bool get hasError => _hasError;
  String get errorMessage => _errorMessage;
  Pagination? get pagination => _pagination;
  int get currentPage => _currentPage;
  String get searchQuery => _searchQuery;
  String? get selectedCategory => _selectedCategory;

  bool get hasMorePages =>
      _pagination != null && _currentPage < _pagination!.pages;

  Future<void> loadApps({
    bool refresh = false,
    String? search,
    String? category,
  }) async {
    if (refresh) {
      _currentPage = 1;
      _apps.clear();
      _searchQuery = search ?? '';
      _selectedCategory = category;
    }

    _setLoading(true);
    _clearError();

    try {
      final response = await ApiService.getApps(
        page: _currentPage,
        search: _searchQuery.isEmpty ? null : _searchQuery,
        category: _selectedCategory,
      );

      if (refresh) {
        _apps = response.apps;
      } else {
        _apps.addAll(response.apps);
      }

      _pagination = response.pagination;
      _currentPage++;

      notifyListeners();
    } catch (e) {
      _setError(e.toString());
    } finally {
      _setLoading(false);
    }
  }

  Future<void> loadMoreApps() async {
    if (!hasMorePages || _isLoading) return;
    await loadApps();
  }

  Future<void> searchApps(String query) async {
    _searchQuery = query;
    await loadApps(refresh: true, search: query);
  }

  Future<void> filterByCategory(String? category) async {
    _selectedCategory = category;
    await loadApps(refresh: true, category: category);
  }

  Future<void> loadCategories() async {
    try {
      _categories = await ApiService.getCategories();
      notifyListeners();
    } catch (e) {
      if (kDebugMode) {
        print('Error loading categories: $e');
      }
    }
  }

  Future<AppModel?> getAppById(int id) async {
    try {
      return await ApiService.getApp(id);
    } catch (e) {
      _setError(e.toString());
      return null;
    }
  }

  void _setLoading(bool loading) {
    _isLoading = loading;
    notifyListeners();
  }

  void _setError(String message) {
    _hasError = true;
    _errorMessage = message;
    notifyListeners();
  }

  void _clearError() {
    _hasError = false;
    _errorMessage = '';
  }

  void clearSearch() {
    _searchQuery = '';
    _selectedCategory = null;
    loadApps(refresh: true);
  }
}