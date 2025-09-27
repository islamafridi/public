# 5Plays APK Store - Flutter App

A Flutter mobile application that connects to your WordPress 5Plays website to display and manage Android apps.

## Features

- **App Listing**: Browse all available apps with pagination
- **Search**: Search apps by name or content
- **Category Filtering**: Filter apps by categories
- **App Details**: View detailed app information including:
  - App screenshots carousel
  - Download links with file sizes
  - MOD features and information
  - Ratings and reviews
  - Technical specifications
- **Download Management**: Direct APK download via external browser
- **Responsive Design**: Material Design 3 with light/dark theme support

## Architecture

### WordPress Integration
- **REST API**: Custom endpoints in `/wp-content/themes/9mod/inc/inits.php`
- **Endpoints**:
  - `GET /wp-json/apk-templates/v1/apps` - List apps with pagination and filtering
  - `GET /wp-json/apk-templates/v1/apps/{id}` - Get single app details
  - `GET /wp-json/apk-templates/v1/categories` - Get app categories

### Flutter Structure
```
lib/
├── models/          # Data models with JSON serialization
├── services/        # API service and state management
├── screens/         # App screens (Home, Detail)
├── widgets/         # Reusable UI components
└── main.dart       # App entry point
```

## Setup Instructions

### Prerequisites
- Flutter SDK 3.9.2+
- WordPress site running locally at `https://5plays.local`
- WordPress theme with custom meta boxes for app data

### Installation

1. **Clone/Navigate to the project**:
   ```bash
   cd "C:\Users\mafri\Local Sites\5plays\app\public\5plays_app"
   ```

2. **Install dependencies**:
   ```bash
   flutter pub get
   ```

3. **Generate JSON serialization**:
   ```bash
   flutter packages pub run build_runner build
   ```

4. **Run the app**:
   ```bash
   flutter run
   ```

### Configuration

- **API Base URL**: Update `baseUrl` in `lib/services/api_service.dart` if your WordPress site URL changes
- **WordPress Site**: Ensure your WordPress site is accessible at `https://5plays.local`

## API Endpoints

### Apps List
```http
GET /wp-json/apk-templates/v1/apps?page=1&per_page=10&search=game&category=action
```

Response:
```json
{
  "apps": [...],
  "pagination": {
    "total": 100,
    "pages": 10,
    "current_page": 1,
    "per_page": 10
  }
}
```

### Single App
```http
GET /wp-json/apk-templates/v1/apps/123
```

### Categories
```http
GET /wp-json/apk-templates/v1/categories
```

## WordPress Integration

The WordPress theme includes:
- Custom meta boxes for app information
- Download links management
- Screenshot management
- Rating system
- MOD features tracking

## Dependencies

- **http**: API communication
- **provider**: State management
- **cached_network_image**: Image loading and caching
- **carousel_slider**: Image carousels
- **url_launcher**: External URL handling
- **shimmer**: Loading animations
- **json_annotation**: JSON serialization

## Development

### Adding New Features
1. Update models in `lib/models/`
2. Add API endpoints in WordPress
3. Update `ApiService` for new endpoints
4. Create/update UI components
5. Run `flutter packages pub run build_runner build` after model changes

### Testing
```bash
flutter test
```

### Building
```bash
# Android APK
flutter build apk

# Android Bundle
flutter build appbundle
```

## Network Configuration

For local development, ensure your WordPress site is accessible from your mobile device or emulator. You may need to:
- Use your computer's IP address instead of `localhost`
- Configure SSL certificates for HTTPS
- Update network security configurations for Android

## Troubleshooting

1. **API Connection Issues**: Verify WordPress site is running and accessible
2. **JSON Serialization Errors**: Run `flutter packages pub run build_runner build --delete-conflicting-outputs`
3. **Build Issues**: Run `flutter clean && flutter pub get`
