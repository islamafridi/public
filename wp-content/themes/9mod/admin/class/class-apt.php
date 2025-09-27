<?php
class AT_Aptoide
{
    private $post_id;
    private $apt_url;

    function __construct()
    {
        
    }

    private function validate_apt_url($apt_url)
    {
        if (empty($apt_url) || !strpos($apt_url, 'aptoide.com')) {
            return false;
        }

        return true;
    }

    private function validate_apt_id($apt_url)
    {
        if (preg_match('/https:\/\/([^\.]+)\.[a-z]+\.aptoide\.com\/app/', $apt_url, $matches)) {
            return true;
        }

        return false;
    }

    private function validate_url_status_code($apt_url)
    {
        return get_http_response_code($apt_url) !== false;
    }

    private function single_map_data($script_data, $mapping_values)
    {
        $mapped_data = [];
        foreach ($mapping_values as $key => $mapping) {
            if (is_array($mapping)) {
                if (isset($mapping['fun'])) {
                    $value = $this->extract_path_value($script_data, $mapping['path']);
                    $mapped_data[$key] = $mapping['fun']($value);
                } else {
                    $mapped_data[$key] = $this->extract_path_value($script_data, $mapping);
                }
            }
        }
        return $mapped_data;
    }

    private function extract_path_value($script_data, $mapping_path)
    {
        $current_data = $script_data;
        foreach ($mapping_path as $key) {
            if (isset($current_data[$key])) {
                $current_data = $current_data[$key];
            } else {
                return null;
            }
        }
        return $current_data;
    }

    private function get_apk_data($apt_url)
    {
        $response = [
            'status' => '',
            'data' => '',
        ];

        $scraper = new Scraper();
        $apt_html = $scraper->scrape($apt_url);

        $html = new simple_html_dom();
        $html->load($apt_html);

        $script_data = $html->find('script[id="__NEXT_DATA__"]', 0);

        if ($script_data) {
            $script_data = $script_data->innertext;
            $script_data = json_decode($script_data, true);
        } else {
            $response['status'] = 'error';
            $response['data'] = [
                'message' => 'Main script not found!',
            ];

            return $response;
        }

        $apk_mappings = [
            'apk_name' => ['app', 'name'],
            'apk_id' => ['app', 'package'],
            'apk_thumbnail' => ['app', 'icon'],
            'apk_banner' => ['app', 'graphic'],
            'apk_version' => ['app', 'file', 'vername'],
            'apk_category' => [
                'path' => ['groups', '0', 'parent', 'title'],
                'fun' => function ($category) {
                    if ($category === 'Applications') {
                        return 'Apps';
                    } else {
                        return $category;
                    }
                },
            ],
            'apk_sub_category' => ['groups', '0', 'title'],
            'apk_required_version' => [
                'path' => ['app', 'file', 'hardware', 'version', 'number'],
                'fun' => function ($version) {
                    $pattern = '/^(\d+(?:\.\d+)?)/';

                    preg_match($pattern, $version, $matches);

                    if (!empty($matches[1])) {
                        $required_version = $matches[1];
                    } else {
                        $required_version = $version;
                    }

                    return $required_version;
                },
            ],
            'apk_size' => ['app', 'size'],
			'apk_content_rating' => ['app', 'age', 'title'],
            'apk_price' => '0',
            'apk_developer' => ['app', 'developer', 'name'],
            'apk_rating' => ['app', 'stats', 'prating', 'avg'],
            'apk_total_votes' => ['app', 'stats', 'prating', 'total'],
            'apk_whats_new' => ['app', 'media', 'news'],
            'apk_screenshots' => [
                'path' => ['app', 'media', 'screenshots'],
                'fun' => function ($screenshots) {
                    if ($screenshots === null) {
                        return [];
                    }

                    $result = [];
                    foreach ($screenshots as $screenshot) {
                        if (isset($screenshot['url'])) {
                            $result[] = $screenshot['url'];
                        }
                    }
                    return $result;
                }
            ],
            'apk_content' => ['app', 'media', 'description'],
            'apk_description' => ['app', 'media', 'summary'],
            'apk_file_url' => ['app', 'file', 'path'],
            'apk_file_version' => ['app', 'file', 'vername'],
            'apk_file_size' => ['app', 'file', 'filesize'],
            'apt_id' => ['app', 'uname'],
        ];

        $response['status'] = 'success';
        $response['data'] = $this->single_map_data($script_data['props'], $apk_mappings);

        return $response;
    }

    private function get_search_data($apt_search_url)
    {
        $scraper = new Scraper();
        $apt_json = $scraper->scrape($apt_search_url);

        if ($apt_json) {
            $apt_data_list = json_decode($apt_json, true);
            $apt_data_list = $apt_data_list['datalist']['list'];
        } else {
            $response['status'] = 'error';
            $response['data'] = [
                'message' => 'JSON not found!',
            ];

            return $response;
        }

        $apt_search_data_list = [];

        foreach ($apt_data_list as $apt_data) {
            $apt_search_data = [
                'apk_name' => $apt_data['name'],
                'apk_id' => $apt_data['package'],
                'apk_url' => $apt_data['uname'] . '.' . 'en.aptoide.com/app',
                'apk_thumbnail' => $apt_data['icon'] . '?w=92',
                'apk_banner' => $apt_data['graphic'],
                'apk_developer' => $apt_data['developer']['name'],
                'apk_downloads' => $apt_data['stats']['pdownloads'],
                'apk_rating' => $apt_data['stats']['prating']['avg'],
            ];

            $apt_search_data_list[] = $apt_search_data;
        }

        $response['status'] = 'success';
        $response['data'] = $apt_search_data_list;

        return $response;
    }

    public function extract($apt_url, $type = '')
    {
        $lic = new AT_License();

        if (!$lic->is_valid_license()) {
            $response = [
                'status' => 'error',
                'data' => [
                    'message' => __('License Key is invalid or expired.', 'apktemplates'),
                ],
            ];

            return $response;
        }

        $atp_url_validation = $this->validate_apt_url($apt_url);

        if ($atp_url_validation === false) {
            $response = [
                'status' => 'error',
                'data' => [
                    'message' => 'Please enter a valid Aptoide URL!',
                ],
            ];

            return $response;
        }

        $atp_url_response = $this->validate_url_status_code($apt_url);

        if (!$atp_url_response) {
            $response = [
                'status' => 'error',
                'data' => [
                    'message' => 'URL not available on Aptoide.',
                ],
            ];

            return $response;
        }

        if (!empty($type) && $type === 'apk') {
            $apt_id_validation = $this->validate_apt_id($apt_url);

            if (!$apt_id_validation) {
                $response = [
                    'status' => 'error',
                    'data' => [
                        'message' => 'Aptoide APK ID not found in URL!',
                    ],
                ];

                return $response;
            }

            $response = $this->get_apk_data($apt_url);

            if ($response['status'] === 'success') {
                $apk_post_creator = new AT_Create_APT_Post($response);
                $response = $apk_post_creator->create_post();

                return $response;
            } else {
                return $response;
            }

        } elseif (!empty($type) && $type === 'search') {
            return $this->get_search_data($apt_url);
        }

        $response = [
            'status' => 'error',
            'data' => [
                'message' => 'Something went wrong!',
            ],
        ];

        return $response;
    }
}
