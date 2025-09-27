import 'package:json_annotation/json_annotation.dart';

part 'app_model.g.dart';

@JsonSerializable()
class AppModel {
  final int id;
  final String title;
  final String content;
  final String excerpt;
  @JsonKey(name: 'featured_image')
  final String? featuredImage;
  @JsonKey(name: 'date_published')
  final String datePublished;
  @JsonKey(name: 'app_info')
  final AppInfo appInfo;
  final Rating rating;
  final List<String> categories;
  @JsonKey(name: 'download_links')
  final List<DownloadLink>? downloadLinks;
  final List<String>? screenshots;

  AppModel({
    required this.id,
    required this.title,
    required this.content,
    required this.excerpt,
    this.featuredImage,
    required this.datePublished,
    required this.appInfo,
    required this.rating,
    required this.categories,
    this.downloadLinks,
    this.screenshots,
  });

  factory AppModel.fromJson(Map<String, dynamic> json) =>
      _$AppModelFromJson(json);

  Map<String, dynamic> toJson() => _$AppModelToJson(this);
}

@JsonSerializable()
class AppInfo {
  final String? name;
  final String? version;
  final String? size;
  @JsonKey(name: 'mod_features')
  final String? modFeatures;
  @JsonKey(name: 'package_id')
  final String? packageId;
  final String? developer;
  @JsonKey(name: 'content_rating')
  final String? contentRating;
  @JsonKey(name: 'required_os')
  final String? requiredOs;
  final String? price;
  @JsonKey(name: 'is_mod')
  final bool isMod;
  @JsonKey(name: 'mod_info')
  final String? modInfo;
  @JsonKey(name: 'download_info')
  final String? downloadInfo;

  AppInfo({
    this.name,
    this.version,
    this.size,
    this.modFeatures,
    this.packageId,
    this.developer,
    this.contentRating,
    this.requiredOs,
    this.price,
    required this.isMod,
    this.modInfo,
    this.downloadInfo,
  });

  factory AppInfo.fromJson(Map<String, dynamic> json) =>
      _$AppInfoFromJson(json);

  Map<String, dynamic> toJson() => _$AppInfoToJson(this);
}

@JsonSerializable()
class Rating {
  final double average;
  final int votes;
  final int total;

  Rating({
    required this.average,
    required this.votes,
    required this.total,
  });

  factory Rating.fromJson(Map<String, dynamic> json) => _$RatingFromJson(json);

  Map<String, dynamic> toJson() => _$RatingToJson(this);
}

@JsonSerializable()
class DownloadLink {
  final String? name;
  final String? url;
  final String? size;
  @JsonKey(name: 'mod_info')
  final String? modInfo;
  @JsonKey(name: 'mod_note')
  final String? modNote;
  final String? note;
  final String? group;

  DownloadLink({
    this.name,
    this.url,
    this.size,
    this.modInfo,
    this.modNote,
    this.note,
    this.group,
  });

  factory DownloadLink.fromJson(Map<String, dynamic> json) =>
      _$DownloadLinkFromJson(json);

  Map<String, dynamic> toJson() => _$DownloadLinkToJson(this);
}

@JsonSerializable()
class AppCategory {
  final int id;
  final String name;
  final String slug;
  final int count;

  AppCategory({
    required this.id,
    required this.name,
    required this.slug,
    required this.count,
  });

  factory AppCategory.fromJson(Map<String, dynamic> json) =>
      _$AppCategoryFromJson(json);

  Map<String, dynamic> toJson() => _$AppCategoryToJson(this);
}

@JsonSerializable()
class AppsResponse {
  final List<AppModel> apps;
  final Pagination pagination;

  AppsResponse({
    required this.apps,
    required this.pagination,
  });

  factory AppsResponse.fromJson(Map<String, dynamic> json) =>
      _$AppsResponseFromJson(json);

  Map<String, dynamic> toJson() => _$AppsResponseToJson(this);
}

@JsonSerializable()
class Pagination {
  final int total;
  final int pages;
  @JsonKey(name: 'current_page')
  final int currentPage;
  @JsonKey(name: 'per_page')
  final int perPage;

  Pagination({
    required this.total,
    required this.pages,
    required this.currentPage,
    required this.perPage,
  });

  factory Pagination.fromJson(Map<String, dynamic> json) =>
      _$PaginationFromJson(json);

  Map<String, dynamic> toJson() => _$PaginationToJson(this);
}