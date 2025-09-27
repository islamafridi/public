<?php
require_once "googleplay.class.php";

class AT_License
{
    public $plugin_file;
    public $responseObj;
    public $licenseMessage;
    private $show_message = false;
    private $main_lic_key = 'AN1byAPKTEMPLATES_lic_Key';
    private $email_meta_key = 'AN1byAPKTEMPLATES_lic_email';

function __construct()
{
    if (is_admin()) {
        add_action('wp_ajax_apkt_activate_license', [$this, 'activate_license']);
        add_action('wp_ajax_apkt_deactivate_license', [$this, 'deactivate_license']);

        $this->plugin_file = get_stylesheet_directory() . '/style.css';

        if(!$this->is_license_active()) {
            add_action('admin_notices', [$this, 'apkt_license_notify']);
        }
    }
}


    public function is_license_active()
    {
        if (A_N_1_Base::check_wp_plugin($this->get_license_key(), $this->get_license_email(), $this->licenseMessage, $this->responseObj, $this->plugin_file)) {
            return true;
        }
        return false;
    }

    public function get_license_key($encrypted = false)
    {
        $license_key_name = A_N_1_Base::get_lic_key_param($this->main_lic_key);
        $license_key = get_option($license_key_name, '');

        if (empty($license_key)) {
            $license_key = get_option($this->main_lic_key, '');
            if (!empty($license_key)) {
                update_option($license_key_name, $license_key);
                update_option($this->main_lic_key, '');
            }
        }

        if ($encrypted) {
            return substr($license_key, 0, 4) . '-xxxx-xxxx';
        }
        return $license_key;
    }

    public function get_license_email()
    {
        return get_option($this->email_meta_key, get_bloginfo('admin_email'));
    }

    public function get_info()
    {
        if ($this->is_license_active()) {
            return [
                'is_valid' => $this->responseObj->is_valid,
                'license_name' => ucfirst($this->responseObj->license_title),
                'license_end_date' => $this->responseObj->expire_date,
                'license_renewal_url' => !empty($this->responseObj->expire_renew_link) ? $this->responseObj->expire_renew_link : '',
                'support_end_date' => $this->responseObj->support_end,
                'support_renewal_url' => !empty($this->responseObj->support_renew_link) ? $this->responseObj->support_renew_link : '',
                'message' => strpos($this->responseObj->msg, '-') !== false ? strstr($this->responseObj->msg, '-', true) : $this->responseObj->msg,
            ];
        }
        return [
            'is_valid' => '',
            'license_name' => '',
            'license_end_date' => '',
            'license_renewal_url' => '',
            'support_end_date' => '',
            'support_renewal_url' => '',
            'message' => '',
        ];
    }

    public function activate_license()
    {
        $nonce = sanitize_text_field($_POST['nonce']);
        $client_license_key = isset($_POST['apkt_license_key']) ? sanitize_text_field($_POST['apkt_license_key']) : '';
        $client_email = isset($_POST['apkt_license_email']) ? sanitize_email($_POST['apkt_license_email']) : '';

        if (!wp_verify_nonce($nonce, 'panel_nonce')) {
            $response = [
                'status' => 'error',
                'data' => ['message' => 'Khatam! Tata! Good Bye!'],
            ];
        } elseif (empty($client_license_key)) {
            $response = [
                'status' => 'error',
                'data' => ['message' => esc_html__('Invalid license key.', 'apktemplates')],
            ];
        } elseif (empty($client_email) || !is_email($client_email)) {
            $response = [
                'status' => 'error',
                'data' => ['message' => esc_html__('Invalid email address.', 'apktemplates')],
            ];
        } else {
            if (current_user_can('manage_options')) {
                update_option($this->main_lic_key, $client_license_key);
                update_option($this->email_meta_key, $client_email);
                update_option('_site_transient_update_themes', '');
            }

            if (A_N_1_Base::check_wp_plugin($client_license_key, $client_email, $this->licenseMessage, $this->responseObj, $this->plugin_file)) {
                $response = [
                    'status' => 'success',
                    'data' => ['message' => esc_html__('License activated :)', 'apktemplates')],
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'data' => ['message' => $this->licenseMessage ?: 'License activation failed'],
                ];
            }
        }

        echo wp_json_encode($response);
        exit;
    }

    public function deactivate_license()
    {
        $nonce = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce($nonce, 'panel_nonce')) {
            $response = [
                'status' => 'error',
                'data' => ['message' => 'Khatam! Tata! Good Bye!'],
            ];
        } else {
            $message = '';
            if (current_user_can('manage_options') && A_N_1_Base::remove_license_key($this->plugin_file, $message)) {
                $license_key_name = A_N_1_Base::get_lic_key_param($this->main_lic_key);
                update_option($license_key_name, '');
                update_option('_site_transient_update_themes', '');
            }

            $response = [
                'status' => 'success',
                'data' => ['message' => esc_html('License deactivated!', 'apktemplates')],
            ];
        }

        echo wp_json_encode($response);
        exit;
    }

    public function is_valid_license()
    {
        return true;
    }

    public function apkt_license_notify()
    {
        return;
    }
}