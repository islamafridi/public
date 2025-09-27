<?php
if (!defined('ABSPATH')) exit;

/**
 * 5Plays APK Store Admin Settings
 * Manage API settings and mobile app configuration
 */

// Add admin menu
add_action('admin_menu', 'apkt_add_admin_menu');
add_action('admin_init', 'apkt_admin_init');

function apkt_add_admin_menu() {
    add_options_page(
        '5Plays API Settings',
        '5Plays API',
        'manage_options',
        'apkt-api-settings',
        'apkt_admin_page_callback'
    );
}

function apkt_admin_init() {
    // Register settings
    register_setting('apkt_api_settings', 'apkt_api_base_url');
    register_setting('apkt_api_settings', 'apkt_app_name');
    register_setting('apkt_api_settings', 'apkt_app_description');
    register_setting('apkt_api_settings', 'apkt_app_version');
    register_setting('apkt_api_settings', 'apkt_support_email');
    register_setting('apkt_api_settings', 'apkt_website_url');
    register_setting('apkt_api_settings', 'apkt_enable_cors');
    register_setting('apkt_api_settings', 'apkt_api_rate_limit');

    // Add settings sections
    add_settings_section(
        'apkt_api_section',
        'API Configuration',
        'apkt_api_section_callback',
        'apkt-api-settings'
    );

    add_settings_section(
        'apkt_app_section',
        'Mobile App Settings',
        'apkt_app_section_callback',
        'apkt-api-settings'
    );

    // Add settings fields
    add_settings_field(
        'apkt_api_base_url',
        'API Base URL',
        'apkt_api_base_url_callback',
        'apkt-api-settings',
        'apkt_api_section'
    );

    add_settings_field(
        'apkt_enable_cors',
        'Enable CORS',
        'apkt_enable_cors_callback',
        'apkt-api-settings',
        'apkt_api_section'
    );

    add_settings_field(
        'apkt_api_rate_limit',
        'API Rate Limit (requests/minute)',
        'apkt_api_rate_limit_callback',
        'apkt-api-settings',
        'apkt_api_section'
    );

    add_settings_field(
        'apkt_app_name',
        'App Name',
        'apkt_app_name_callback',
        'apkt-api-settings',
        'apkt_app_section'
    );

    add_settings_field(
        'apkt_app_description',
        'App Description',
        'apkt_app_description_callback',
        'apkt-api-settings',
        'apkt_app_section'
    );

    add_settings_field(
        'apkt_app_version',
        'App Version',
        'apkt_app_version_callback',
        'apkt-api-settings',
        'apkt_app_section'
    );

    add_settings_field(
        'apkt_support_email',
        'Support Email',
        'apkt_support_email_callback',
        'apkt-api-settings',
        'apkt_app_section'
    );

    add_settings_field(
        'apkt_website_url',
        'Website URL',
        'apkt_website_url_callback',
        'apkt-api-settings',
        'apkt_app_section'
    );
}

function apkt_admin_page_callback() {
    ?>
    <div class="wrap">
        <h1>5Plays APK Store - API Settings</h1>

        <div class="notice notice-info">
            <p><strong>API Endpoints:</strong></p>
            <ul>
                <li><code><?php echo esc_url(home_url('/wp-json/apk-templates/v1/apps')); ?></code> - Get all apps</li>
                <li><code><?php echo esc_url(home_url('/wp-json/apk-templates/v1/apps/{id}')); ?></code> - Get single app</li>
                <li><code><?php echo esc_url(home_url('/wp-json/apk-templates/v1/categories')); ?></code> - Get categories</li>
                <li><code><?php echo esc_url(home_url('/wp-json/apk-templates/v1/settings')); ?></code> - Get API settings</li>
            </ul>
        </div>

        <form method="post" action="options.php">
            <?php
            settings_fields('apkt_api_settings');
            do_settings_sections('apkt-api-settings');
            submit_button();
            ?>
        </form>

        <div class="card" style="margin-top: 20px;">
            <h2>Flutter App Configuration</h2>
            <p>To use this API with your Flutter app:</p>
            <ol>
                <li>Update the <code>baseUrl</code> in your Flutter app's <code>lib/services/api_service.dart</code></li>
                <li>Use this URL: <strong><?php echo esc_url(get_option('apkt_api_base_url', home_url()) . '/wp-json/apk-templates/v1'); ?></strong></li>
                <li>Make sure CORS is enabled if accessing from a different domain</li>
            </ol>

            <h3>Test API Connection</h3>
            <button type="button" id="test-api" class="button button-secondary">Test API Endpoints</button>
            <div id="api-test-results" style="margin-top: 10px;"></div>
        </div>
    </div>

    <script>
    document.getElementById('test-api').addEventListener('click', function() {
        const resultsDiv = document.getElementById('api-test-results');
        resultsDiv.innerHTML = '<p>Testing API endpoints...</p>';

        const baseUrl = '<?php echo esc_url(home_url('/wp-json/apk-templates/v1')); ?>';
        const endpoints = [
            { name: 'Settings', url: baseUrl + '/settings' },
            { name: 'Categories', url: baseUrl + '/categories' },
            { name: 'Apps (first page)', url: baseUrl + '/apps?per_page=5' }
        ];

        let results = '<h4>API Test Results:</h4><ul>';
        let completed = 0;

        endpoints.forEach(endpoint => {
            fetch(endpoint.url)
                .then(response => {
                    const status = response.ok ? '✅ OK' : '❌ Error';
                    results += `<li><strong>${endpoint.name}:</strong> ${status} (${response.status})</li>`;
                })
                .catch(error => {
                    results += `<li><strong>${endpoint.name}:</strong> ❌ Failed (${error.message})</li>`;
                })
                .finally(() => {
                    completed++;
                    if (completed === endpoints.length) {
                        results += '</ul>';
                        resultsDiv.innerHTML = results;
                    }
                });
        });
    });
    </script>
    <?php
}

// Section callbacks
function apkt_api_section_callback() {
    echo '<p>Configure API settings for your mobile app integration.</p>';
}

function apkt_app_section_callback() {
    echo '<p>Customize your mobile app information and branding.</p>';
}

// Field callbacks
function apkt_api_base_url_callback() {
    $value = get_option('apkt_api_base_url', home_url());
    echo '<input type="url" name="apkt_api_base_url" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Base URL for your API (e.g., https://5plays.org)</p>';
}

function apkt_enable_cors_callback() {
    $value = get_option('apkt_enable_cors', 0);
    echo '<input type="checkbox" name="apkt_enable_cors" value="1" ' . checked(1, $value, false) . ' />';
    echo '<p class="description">Enable Cross-Origin Resource Sharing for external app access</p>';
}

function apkt_api_rate_limit_callback() {
    $value = get_option('apkt_api_rate_limit', 60);
    echo '<input type="number" name="apkt_api_rate_limit" value="' . esc_attr($value) . '" min="10" max="1000" />';
    echo '<p class="description">Maximum requests per minute per IP address</p>';
}

function apkt_app_name_callback() {
    $value = get_option('apkt_app_name', '5Plays APK Store');
    echo '<input type="text" name="apkt_app_name" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Display name for your mobile app</p>';
}

function apkt_app_description_callback() {
    $value = get_option('apkt_app_description', 'Download and discover amazing Android apps');
    echo '<textarea name="apkt_app_description" rows="3" class="large-text">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">Short description of your app store</p>';
}

function apkt_app_version_callback() {
    $value = get_option('apkt_app_version', '1.0.0');
    echo '<input type="text" name="apkt_app_version" value="' . esc_attr($value) . '" class="small-text" />';
    echo '<p class="description">Current version of your mobile app</p>';
}

function apkt_support_email_callback() {
    $value = get_option('apkt_support_email', get_option('admin_email'));
    echo '<input type="email" name="apkt_support_email" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Support email for app users</p>';
}

function apkt_website_url_callback() {
    $value = get_option('apkt_website_url', home_url());
    echo '<input type="url" name="apkt_website_url" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Your website URL</p>';
}

// Add CORS headers if enabled
add_action('rest_api_init', 'apkt_add_cors_headers');
function apkt_add_cors_headers() {
    if (get_option('apkt_enable_cors', 0)) {
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function($served, $result, $request, $server) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            return $served;
        }, 10, 4);
    }
}
?>