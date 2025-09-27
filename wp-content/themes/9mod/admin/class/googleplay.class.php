<?php
if (!class_exists("A_N_1_Base")) {
    class A_N_1_Base {
        public $key = "65B60DEFBE2022A8";
        private $product_id = "3";
        private $product_base = "an1";
        private $server_host = "https://lic.9mod.cc/wp-json/licensor/";
        private $has_check_update = true;
        private $plugin_file;
        private $theme_dir_name = '';
        private static $selfobj = null;
        private $version = "";
        private $is_theme = false;
        private $email_address = "";
        private static $_on_delete_license = [];

        function __construct($plugin_base_file = '') {
            if (empty($plugin_base_file)) {
                $dir = str_replace('\\', '/', dirname(__FILE__));
            } else {
                $dir = str_replace('\\', '/', dirname($plugin_base_file));
            }
            if (strpos($dir, 'wp-content/themes') !== FALSE) {
                $this->is_theme = true;
                $this->theme_dir_name = self::get_this_theme_path_name();
                $theme_data = wp_get_theme($this->theme_dir_name);
                $version = $theme_data->get('Version');
                if (!empty($version)) {
                    $this->version = $version;
                }
            }
            $this->plugin_file = $plugin_base_file;
            if (empty($this->plugin_file) && $this->is_theme) {
                $this->plugin_file = self::get_this_theme_path();
            }
            if (empty($this->version)) {
                $this->version = $this->get_current_version();
            }

            if ($this->has_check_update) {
                if (function_exists("add_action")) {
                    add_action('admin_post_an1_fupc', function () {
                        update_option('_site_transient_update_plugins', '');
                        update_option('_site_transient_update_themes', '');
                        set_site_transient('update_themes', null);
                        delete_transient($this->product_base . "_up");
                        wp_redirect(admin_url('plugins.php'));
                        exit;
                    });
                    add_action('init', [$this, "init_action_handler"]);
                }
                if (function_exists("add_filter")) {
                    if ($this->is_theme) {
                        add_filter('pre_set_site_transient_update_themes', [$this, "plugin_update"]);
                        add_filter('themes_api', [$this, 'check_update_info'], 10, 3);
                        add_action('admin_menu', function () {
                            add_theme_page('Update Check', 'Update Check', 'edit_theme_options', 'update_check', [$this, "theme_force_update"]);
                        }, 999);
                    } else {
                        add_filter('pre_set_site_transient_update_plugins', [$this, "plugin_update"]);
                        add_filter('plugins_api', [$this, 'check_update_info'], 10, 3);
                        add_filter('plugin_row_meta', function ($links, $plugin_file) {
                            if (plugin_basename($this->plugin_file) == $plugin_file) {
                                $links[] = " <a class='edit coption' href='" . esc_url(admin_url('admin-post.php') . '?action=an1_fupc') . "'>Update Check</a>";
                            }
                            return $links;
                        }, 10, 2);
                        add_action("in_plugin_update_message-" . plugin_basename($this->plugin_file), [$this, 'update_message_cb'], 20, 2);
                    }

                    add_action('upgrader_process_complete', function ($upgrader_object, $options) {
                        update_option('_site_transient_update_plugins', '');
                        update_option('_site_transient_update_themes', '');
                        set_site_transient('update_themes', null);
                    }, 10, 2);
                }
            }
            
            $this->create_fake_license();
        }

        private function create_fake_license() {
            $response_obj = new stdClass();
            $response_obj->is_valid = true;
            $response_obj->next_request = time() + (30 * DAY_IN_SECONDS);
            $response_obj->expire_date = "No Expiry";
            $response_obj->support_end = "Unlimited";
            $response_obj->license_title = "Educational License";
            $response_obj->license_key = "EDU-BYPASS-" . rand(1000, 9999);
            $response_obj->msg = "License bypassed for educational purposes";
            $this->save_wp_response($response_obj);
        }

        // Re-added init_action_handler
        function init_action_handler() {
            $handler = hash("crc32b", $this->product_id . $this->key . $this->get_domain()) . "_handle";
            if (isset($_GET['action']) && $_GET['action'] == $handler) {
                $this->handle_server_request();
                exit;
            }
        }

        public static function check_wp_plugin($purchase_key, $email, &$error = "", &$response_obj = null, $plugin_base_file = "") {
            $obj = self::get_instance($plugin_base_file);
            $obj->set_email_address($email);
            
            $response_obj = new stdClass();
            $response_obj->is_valid = true;
            $response_obj->next_request = time() + (30 * DAY_IN_SECONDS);
            $response_obj->expire_date = "No Expiry";
            $response_obj->support_end = "Unlimited";
            $response_obj->license_title = "Educational License";
            $response_obj->license_key = $purchase_key ?: "EDU-BYPASS-" . rand(1000, 9999);
            $response_obj->msg = "License bypassed for educational purposes";
            $response_obj->renew_link = "";
            $response_obj->expire_renew_link = "";
            $response_obj->support_renew_link = "";
            
            $obj->save_wp_response($response_obj);
            delete_transient($obj->product_base . "_up");
            return true;
        }

        final function _check_wp_plugin($purchase_key, &$error = "", &$response_obj = null) {
            return self::check_wp_plugin($purchase_key, $this->email_address, $error, $response_obj, $this->plugin_file);
        }

        public static function remove_license_key($plugin_base_file, &$message = "") {
            $obj = self::get_instance($plugin_base_file);
            $obj->remove_old_wp_response();
            $obj->create_fake_license();
            $message = "License reset for educational purposes";
            return true;
        }

        final function _remove_wp_plugin_license(&$message = '') {
            return self::remove_license_key($this->plugin_file, $message);
        }

        function handle_server_request() {
            $type = isset($_GET['type']) ? strtolower(sanitize_text_field(wp_unslash($_GET['type']))) : '';
            $obj = new stdClass();
            $obj->product = $this->product_id;
            $obj->status = true;
            
            switch ($type) {
                case "rl":
                case "rc":
                    $this->remove_old_wp_response();
                    $this->create_fake_license();
                    call_user_func('printf', '%s', $this->encrypt_obj($obj));
                    break;
                case "dl":
                    if ($this->is_theme) {
                        $res = delete_theme($this->plugin_file);
                        if (!is_wp_error($res)) {
                            $obj->status = true;
                        }
                    } else {
                        deactivate_plugins([plugin_basename($this->plugin_file)]);
                        $res = delete_plugins([plugin_basename($this->plugin_file)]);
                        if (!is_wp_error($res)) {
                            $obj->status = true;
                        }
                    }
                    call_user_func('printf', '%s', $this->encrypt_obj($obj));
                    break;
                default:
                    $obj->status = true;
                    call_user_func('printf', '%s', $this->encrypt_obj($obj));
            }
            exit;
        }

        private function _request($relative_url, $data, &$error = '') {
            $response = new stdClass();
            $response->status = true;
            $response->msg = "Educational bypass active";
            $response->is_request_error = false;
            $response->data = new stdClass();
            $response->data->is_valid = true;
            return $response;
        }

        public function theme_force_update() {
            $this->clean_update_info();
            $url = admin_url('themes.php');
            echo wp_kses_post('<h1>' . __("Update Checking..", "an1") . '</h1>');
            call_user_func('printf', '%s', "<script>location.href = '" . $url . "'</script>");
        }

        public function set_email_address($email_address) {
            $this->email_address = $email_address;
        }

        function get_current_version() {
            if (!function_exists('get_plugin_data')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            $data = get_plugin_data($this->plugin_file);
            return isset($data['Version']) ? $data['Version'] : 0;
        }

        public function clean_update_info() {
            update_option('_site_transient_update_plugins', '');
            update_option('_site_transient_update_themes', '');
            delete_transient($this->product_base . "_up");
        }

        function plugin_update($transient) {
            if (empty($transient)) {
                $transient = new stdClass();
                $transient->response = [];
            }
            return $transient;
        }

        function check_update_info($false, $action, $arg) {
            return $false;
        }

        function update_message_cb($data, $response) {
            // Empty implementation since we're bypassing
        }

        private function encrypt($plain_text, $password = '') {
            if (empty($password)) {
                $password = $this->key;
            }
            $plain_text = rand(10, 99) . $plain_text . rand(10, 99);
            $method = 'aes-256-cbc';
            $key = substr(hash('sha256', $password, true), 0, 32);
            $iv = substr(strtoupper(md5($password)), 0, 16);
            return $this->b64_en(openssl_encrypt($plain_text, $method, $key, OPENSSL_RAW_DATA, $iv));
        }

        private function decrypt($encrypted, $password = '') {
            if (empty($password)) {
                $password = $this->key;
            }
            $method = 'aes-256-cbc';
            $key = substr(hash('sha256', $password, true), 0, 32);
            $iv = substr(strtoupper(md5($password)), 0, 16);
            $plaintext = openssl_decrypt($this->b64_dc($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);
            return substr($plaintext, 2, -2);
        }

        function b64_dc($encrypted) {
            return base64_decode($encrypted);
        }

        function b64_en($str) {
            return base64_encode($str);
        }

        function encrypt_obj($obj) {
            $text = serialize($obj);
            return $this->encrypt($text);
        }

        private function get_domain() {
            return self::get_raw_domain();
        }

        private static function get_raw_domain() {
            if (function_exists("site_url")) {
                return site_url();
            }
            if (defined("WPINC") && function_exists("home_url")) {
                return esc_url(home_url());
            }
            return 'http://localhost';
        }

        private function get_key_name() {
            return hash('crc32b', $this->get_domain() . $this->product_id . "EDU");
        }

        private function save_wp_response($response) {
            $key = $this->get_key_name();
            $data = $this->encrypt(serialize($response), $this->get_domain());
            update_option($key, $data) OR add_option($key, $data);
        }

        private function get_old_wp_response() {
            $key = $this->get_key_name();
            $response = get_option($key, NULL);
            if (empty($response)) {
                $this->create_fake_license();
                return $this->get_old_wp_response();
            }
            return unserialize($this->decrypt($response, $this->get_domain()));
        }

        private function remove_old_wp_response() {
            $key = $this->get_key_name();
            return delete_option($key);
        }

        public static function get_instance($plugin_base_file = null) {
            if (empty(self::$selfobj)) {
                if (!empty($plugin_base_file)) {
                    self::$selfobj = new self($plugin_base_file);
                }
            }
            return self::$selfobj;
        }

        public static function get_register_info() {
            if (!empty(self::$selfobj)) {
                return self::$selfobj->get_old_wp_response();
            }
            return null;
        }

        public static function get_lic_key_param($key) {
            $raw_url = self::get_raw_domain();
            return $key . "_s" . hash('crc32b', $raw_url);
        }

        public static function get_this_theme_path_name() {
            $wp_theme_dir = str_replace('\\', '/', WP_CONTENT_DIR) . '/themes/';
            $wp_file_dir = str_replace('\\', '/', dirname(__FILE__));
            $themename = str_replace($wp_theme_dir, "", $wp_file_dir);
            $pos = strpos($themename, '/');
            if ($pos !== false) {
                $themename = substr($themename, 0, $pos);
            }
            return $themename;
        }

        public static function get_this_theme_path() {
            $wp_theme_dir = str_replace('\\', '/', WP_CONTENT_DIR) . '/themes/';
            $themename = self::get_this_theme_path_name();
            $style_css_path = $wp_theme_dir . $themename . '/' . "style.css";
            if (file_exists($style_css_path)) {
                return $style_css_path;
            }
            return get_stylesheet_directory();
        }
    }
}