# Download File Renamer Plugin

A WordPress plugin that renames download files and provides secure proxy download functionality.

## Installation

1. Create the plugin directory structure:
```
wp-content/plugins/download-file-renamer/
├── download-file-renamer.php (Main plugin file)
├── includes/
│   ├── settings.php
│   ├── file-handler.php
│   └── token-manager.php
└── README.md
```

2. Upload all files to the respective directories
3. Go to WordPress Admin → Plugins → Activate "Download File Renamer"

## Configuration

1. Go to **Settings → Download Renamer**
2. Configure:
   - **Enable Download Proxy**: ✅ Check this
   - **Allowed Domains**: 
     ```
     disk.9mod.cc
     cloud.9mod.com
     cdn.9mod.cc
     files.9mod.com
     ```
   - **Old Brand Names**:
     ```
     9mod.cc
     9mod.com
     ```
   - **New Brand Name**: `5play.io`
   - **Enable Secure Links**: ✅ Check for short URLs with expiry
   - **Link Expiry Time**: `360` minutes (6 hours) - minimum 5 minutes

## Usage in Theme Files

### Basic Usage:
```php
<?php
$download_url = dfr_get_proxy_download_url($original_url);
?>
<a href="<?php echo esc_url($download_url); ?>">Download</a>
```

### With Custom Expiry:
```php
<?php
// Expires in 30 minutes
$download_url = dfr_get_proxy_download_url($original_url, 30);
?>
<a href="<?php echo esc_url($download_url); ?>">Download</a>
```

## URL Formats

### Basic Mode (Secure Links Disabled):
```
/?download_proxy=1&file=base64encodedurl&name=renamed-file-5play.io.apk
```

### Secure Mode (Secure Links Enabled):
```
/?download_proxy=1&token=abc123def456:202509111640/renamed-file-5play.io.apk
```

## Features

✅ **File Renaming**: Automatically replaces brand names in filenames  
✅ **Domain Filtering**: Only allows downloads from trusted domains  
✅ **Secure URLs**: Short, encrypted tokens with expiration  
✅ **Expiry Control**: Set custom expiry times (5 minutes to 30 days)  
✅ **Auto Cleanup**: Expired tokens are automatically removed  
✅ **Easy Integration**: Simple functions for theme integration  

## Security

- HMAC SHA-256 token verification
- Domain whitelist protection
- Automatic token cleanup
- Configurable expiry times
- No IP binding (removed for flexibility)

## Common Expiry Times

- **5 minutes**: `5`
- **30 minutes**: `30`  
- **1 hour**: `60`
- **6 hours**: `360` (default)
- **12 hours**: `720`
- **24 hours**: `1440`