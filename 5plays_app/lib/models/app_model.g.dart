// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

AppModel _$AppModelFromJson(Map<String, dynamic> json) => AppModel(
  id: (json['id'] as num).toInt(),
  title: json['title'] as String,
  content: json['content'] as String,
  excerpt: json['excerpt'] as String,
  featuredImage: json['featured_image'] as String?,
  datePublished: json['date_published'] as String,
  appInfo: AppInfo.fromJson(json['app_info'] as Map<String, dynamic>),
  rating: Rating.fromJson(json['rating'] as Map<String, dynamic>),
  categories: (json['categories'] as List<dynamic>)
      .map((e) => e as String)
      .toList(),
  downloadLinks: (json['download_links'] as List<dynamic>?)
      ?.map((e) => DownloadLink.fromJson(e as Map<String, dynamic>))
      .toList(),
  screenshots: (json['screenshots'] as List<dynamic>?)
      ?.map((e) => e as String)
      .toList(),
);

Map<String, dynamic> _$AppModelToJson(AppModel instance) => <String, dynamic>{
  'id': instance.id,
  'title': instance.title,
  'content': instance.content,
  'excerpt': instance.excerpt,
  'featured_image': instance.featuredImage,
  'date_published': instance.datePublished,
  'app_info': instance.appInfo,
  'rating': instance.rating,
  'categories': instance.categories,
  'download_links': instance.downloadLinks,
  'screenshots': instance.screenshots,
};

AppInfo _$AppInfoFromJson(Map<String, dynamic> json) => AppInfo(
  name: json['name'] as String?,
  version: json['version'] as String?,
  size: json['size'] as String?,
  modFeatures: json['mod_features'] as String?,
  packageId: json['package_id'] as String?,
  developer: json['developer'] as String?,
  contentRating: json['content_rating'] as String?,
  requiredOs: json['required_os'] as String?,
  price: json['price'] as String?,
  isMod: json['is_mod'] as bool,
  modInfo: json['mod_info'] as String?,
  downloadInfo: json['download_info'] as String?,
);

Map<String, dynamic> _$AppInfoToJson(AppInfo instance) => <String, dynamic>{
  'name': instance.name,
  'version': instance.version,
  'size': instance.size,
  'mod_features': instance.modFeatures,
  'package_id': instance.packageId,
  'developer': instance.developer,
  'content_rating': instance.contentRating,
  'required_os': instance.requiredOs,
  'price': instance.price,
  'is_mod': instance.isMod,
  'mod_info': instance.modInfo,
  'download_info': instance.downloadInfo,
};

Rating _$RatingFromJson(Map<String, dynamic> json) => Rating(
  average: (json['average'] as num).toDouble(),
  votes: (json['votes'] as num).toInt(),
  total: (json['total'] as num).toInt(),
);

Map<String, dynamic> _$RatingToJson(Rating instance) => <String, dynamic>{
  'average': instance.average,
  'votes': instance.votes,
  'total': instance.total,
};

DownloadLink _$DownloadLinkFromJson(Map<String, dynamic> json) => DownloadLink(
  name: json['name'] as String?,
  url: json['url'] as String?,
  size: json['size'] as String?,
  modInfo: json['mod_info'] as String?,
  modNote: json['mod_note'] as String?,
  note: json['note'] as String?,
  group: json['group'] as String?,
);

Map<String, dynamic> _$DownloadLinkToJson(DownloadLink instance) =>
    <String, dynamic>{
      'name': instance.name,
      'url': instance.url,
      'size': instance.size,
      'mod_info': instance.modInfo,
      'mod_note': instance.modNote,
      'note': instance.note,
      'group': instance.group,
    };

AppCategory _$AppCategoryFromJson(Map<String, dynamic> json) => AppCategory(
  id: (json['id'] as num).toInt(),
  name: json['name'] as String,
  slug: json['slug'] as String,
  count: (json['count'] as num).toInt(),
);

Map<String, dynamic> _$AppCategoryToJson(AppCategory instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'slug': instance.slug,
      'count': instance.count,
    };

AppsResponse _$AppsResponseFromJson(Map<String, dynamic> json) => AppsResponse(
  apps: (json['apps'] as List<dynamic>)
      .map((e) => AppModel.fromJson(e as Map<String, dynamic>))
      .toList(),
  pagination: Pagination.fromJson(json['pagination'] as Map<String, dynamic>),
);

Map<String, dynamic> _$AppsResponseToJson(AppsResponse instance) =>
    <String, dynamic>{'apps': instance.apps, 'pagination': instance.pagination};

Pagination _$PaginationFromJson(Map<String, dynamic> json) => Pagination(
  total: (json['total'] as num).toInt(),
  pages: (json['pages'] as num).toInt(),
  currentPage: (json['current_page'] as num).toInt(),
  perPage: (json['per_page'] as num).toInt(),
);

Map<String, dynamic> _$PaginationToJson(Pagination instance) =>
    <String, dynamic>{
      'total': instance.total,
      'pages': instance.pages,
      'current_page': instance.currentPage,
      'per_page': instance.perPage,
    };
