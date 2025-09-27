<?php

class AT_Google_Play
{
    public $base_url = 'https://play.google.com/';
    private $is_advanced_options;
    private $post_language = 'en-US';

    function __construct()
    {
        $this->is_advanced_options = at_options('is_advanced_options', false);

        if ($this->is_advanced_options) {
            $this->post_language = at_options('post_language', 'en-US');
        }
    }

    private function validate_gplay_url($gplay_url)
    {
        if (empty($gplay_url) || !strpos($gplay_url, 'play.google.com')) {
            return false;
        }
        return true;
    }

    private function validate_gplay_id($gplay_url)
    {
        if (preg_match("/\bid=([^\&]+)/", $gplay_url, $matches)) {
            return true;
        }
        return false;
    }

    private function validate_url_response_code($gplay_url)
    {
        return get_http_response_code($gplay_url) !== false;
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

    private function multiple_map_data($script_data, $mapping_values)
    {
        $mapped_data = [];
        foreach ($script_data as $item) {
            $mapped_item = [];
            foreach ($mapping_values as $key => $mapping) {
                if (is_array($mapping)) {
                    if (isset($mapping['fun'])) {
                        $value = $this->extract_path_value($item, $mapping['path']);
                        $mapped_item[$key] = $mapping['fun']($value);
                    } else {
                        $mapped_item[$key] = $this->extract_path_value($item, $mapping);
                    }
                }
            }
            $mapped_data[] = $mapped_item;
        }
        return $mapped_data;
    }

    public function search_single_map_data($script_data, $mapping_values)
    {
        $mapped_data = [];
        foreach ($script_data as $item) {
            $mapped_item = [];
            foreach ($mapping_values as $key => $mapping) {
                if (is_array($mapping)) {
                    if (isset($mapping['fun'])) {
                        $value = $this->extract_path_value($item, $mapping['path']);
                        $mapped_item[$key] = $mapping['fun']($value);
                    } else {
                        $mapped_item[$key] = $this->extract_path_value($item, $mapping);
                    }
                }
            }
            $mapped_data[] = $mapped_item;
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

	private function get_description_text($description)
	{
		$decoded_description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$html = '<div>' . str_replace('<br>', "\r\n", $decoded_description) . '</div>';
		$dom = new DOMDocument();
		$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
		$div_content = $dom->getElementsByTagName('div')[0]->textContent;

		return $div_content;
	}

    private function description_html_localized($search_array)
    {
        $description_translation = isset($search_array[12][0][0][1]) ? $search_array[12][0][0][1] : null;
        $description_original = $search_array[72][0][1];

        return $description_translation ?? $description_original;
    }

    private function generate_histogram_rating($container)
    {
        if (!$container) {
            return array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
        }

        return array(
            1 => $container[1][1],
            2 => $container[2][1],
            3 => $container[3][1],
            4 => $container[4][1],
            5 => $container[5][1],
        );
    }

    private function get_android_version($android_version_text)
    {
        if (!$android_version_text) {
            return '9.0';
        }

        $parts = explode(' ', $android_version_text);
        $number = $parts[0];

        if (is_numeric($number)) {
            return $number;
        }

        return '9.0';
    }

    private function extract_categories($search_array, $categories = [])
    {
        if ($search_array === null || count($search_array) === 0) {
            return $categories;
        }

        if (count($search_array) >= 4 && is_string($search_array[0])) {
            $categories[] = [
                'name' => $search_array[0],
                'id' => $search_array[2],
            ];
        } else {
            foreach ($search_array as $sub) {
                $categories = $this->extract_categories($sub, $categories);
            }
        }

        return $categories;
    }

    private function apk_mappings()
    {
        $apk_mappings = [
            'apk_name' => [1, 2, 0, 0],
            'apk_id' => [1, 11, 0, 0],
            'apk_id_alt' => [1, 2, 77, 0],
            'apk_content' => [
                'path' => [1, 2],
                'fun' => function ($value) {
                    return $this->get_description_text($this->description_html_localized($value));
                },
            ],
            'apk_content_html' => [
                'path' => [1, 2],
                'fun' => function ($value) {
                    return $this->description_html_localized($value);
                },
            ],
            'apk_description' => [1, 2, 73, 0, 1],
            'apk_installs' => [1, 2, 13, 0],
            'apk_min_installs' => [1, 2, 13, 1],
            'apk_max_installs' => [1, 2, 13, 2],
            'apk_rating' => [1, 2, 51, 0, 1],
            'apk_rating_text' => [1, 2, 51, 0, 0],
            'apk_total_rating' => [1, 2, 51, 2, 1],
            'apk_total_votes' => [1, 2, 51, 3, 1],
            'apk_histogram' => [
                'path' => [1, 2, 51, 1],
                'fun' => function ($value) {
                    return $this->generate_histogram_rating($value);
                },
            ],
            'apk_price' => [
                'path' => [1, 2, 57, 0, 0, 0, 0, 1, 0, 0],
                'fun' => function ($price) {
                    if (!isset($price) || $price === '') {
                        return 0;
                    } else {
                        return $price / 1000000;
                    }
                },
            ],
            'apk_original_price' => [
                'path' => [1, 2, 57, 0, 0, 0, 0, 1, 1, 0],
                'fun' => function ($price) {
                    if (!isset($price) || $price === '') {
                        return 0;
                    } else {
                        return $price / 1000000;
                    }
                },
            ],
            'apk_discount_end_date' => [1, 2, 57, 0, 0, 0, 0, 14, 1],
            'apk_is_free' => [
                'path' => [1, 2, 57, 0, 0, 0, 0, 1, 0, 0],
                'fun' => function ($value) {
                    return $value === 0;
                },
            ],
            'apk_currency' => [1, 2, 57, 0, 0, 0, 0, 1, 0, 1],
            'apk_price_text' => [
                'path' => [1, 2, 57, 0, 0, 0, 0, 1, 0, 2],
                'fun' => function ($value) {
                    return $value ?: '0';
                },
            ],
            'apk_is_available' => [
                'path' => [1, 2, 18, 0],
                'fun' => function ($value) {
                    return (bool) $value;
                },
            ],
            'apk_offers_IAP' => [
                'path' => [1, 2, 19, 0],
                'fun' => function ($value) {
                    return (bool) $value;
                },
            ],
            'apk_IAP_range' => [1, 2, 19, 0],
            'apk_required_version' => [
                'path' => [1, 2, 140, 1, 1, 0, 0, 1],
                'fun' => function ($version) {
                    return $this->get_android_version($version);
                },
            ],
            'apk_required_version_text' => [
                'path' => [1, 2, 140, 1, 1, 0, 0, 1],
                'fun' => function ($version) {
                    return $version ?: '9.0';
                },
            ],
            'apk_required_max_version' => [
                'path' => [1, 2, 140, 1, 1, 0, 1, 1],
                'fun' => function ($version) {
                    return $this->get_android_version($version);
                },
            ],
            'apk_developer' => [1, 2, 68, 0],
            'apk_developer_id' => [
                'path' => [1, 2, 68, 1, 4, 2],
                'fun' => function ($developer_url) {
                    $parts = explode('id=', $developer_url);
                    return isset($parts[1]) ? $parts[1] : null;
                },
            ],
            'apk_developer_email' => [1, 2, 69, 1, 0],
            'apk_developer_website' => [1, 2, 69, 0, 5, 2],
            'apk_developer_address' => [1, 2, 69, 2, 0],
            'apk_privacy_policy' => [1, 2, 99, 0, 5, 2],
            'apk_developer_internal_id' => [
                'path' => [1, 2, 68, 1, 4, 2],
                'fun' => function ($developer_url) {
                    $parts = explode('id=', $developer_url);
                    return isset($parts[1]) ? $parts[1] : null;
                },
            ],
            'apk_category' => [1, 2, 79, 0, 0, 2], // It to parse the category
            'apk_sub_category' => [1, 2, 79, 0, 0, 0],
            'categories' => [
                'path' => [1, 2],
                'fun' => function ($array_data) {
                    $categories = $this->extract_categories($array_data[118]);

                    if (count($categories) === 0) {
                        $categories[] = [
                            'name' => $array_data[79][0][0][0],
                            'id' => $array_data[79][0][0][2],
                        ];
                    }
                    return $categories;
                }
            ],
            'apk_thumbnail' => [1, 2, 95, 0, 3, 2],
            'apk_banner' => [1, 2, 96, 0, 3, 2],
            'apk_screenshots' => [
                'path' => [1, 2, 78, 0],
                'fun' => function ($screenshots) {
                    if ($screenshots === null) {
                        return [];
                    }

                    $result = [];
                    foreach ($screenshots as $screenshot) {
                        if (isset($screenshot[3][2])) {
                            $result[] = $screenshot[3][2];
                        }
                    }
                    return $result;
                }
            ],
            'apk_yt_video' => [1, 2, 100, 0, 0, 3, 2],
            'apk_video_image' => [1, 2, 100, 1, 0, 3, 2],
            'apk_preview_video' => [1, 2, 100, 1, 2, 0, 2],
            'apk_content_rating' => [1, 2, 9, 0],
            'apk_content_rating_description' => [1, 2, 9, 2, 1],
            'apk_ad_supported' => [
                'path' => [1, 2, 48],
                'fun' => function ($val) {
                    return (bool) $val;
                },
            ],
            'apk_released' => [1, 2, 10, 0],
            'apk_updated' => [
                'path' => [1, 2, 145, 0, 1, 0],
                'fun' => function ($timestamp) {
                    return date("Y-m-d", $timestamp);
                },
            ],
            'apk_version' => [
                'path' => [1, 2, 140, 0, 0, 0],
                'fun' => function ($value) {
                    return $value ?: '';
                },
            ],
            'apk_whats_new' => [1, 2, 144, 1, 1],
            'apk_pre_register' => [
                'path' => [1, 2, 18, 0],
                'fun' => function ($value) {
                    return $value === 1;
                },
            ],
            'apk_is_early_access_enabled' => [
                'path' => [1, 2, 18, 2],
                'fun' => function ($value) {
                    return is_string($value);
                },
            ],
            'apk_is_available_in_playpass' => [
                'path' => [1, 2, 62],
                'fun' => function ($field) {
                    return !!$field;
                },
            ],
            'apt_id' => '',
        ];

        return $apk_mappings;
    }

    private function search_mappings($index)
    {
        if ($index === 23) {

            $search_mappings = [
                'apk_name' => [0, 3],
                'apk_id' => [0, 0, 0],
                'apk_url' => [
                    'path' => [0, 10, 4, 2],
                    'fun' => function ($path) {
                        $url = rtrim($this->base_url, '/') . '/' . ltrim($path, '/');
                        return $url;
                    },
                ],
                'apk_thumbnail' => [
                    'path' => [0, 1, 3, 2],
                    'fun' => function ($thumbnail) {
                        return $thumbnail . '=w96-rw';
                    },
                ],
                'apk_banner' => [0, 101, 1, 0, 3, 2],
                'apk_developer' => [0, 14],
                'apk_currency' => [0, 8, 1, 0, 1],
                'apk_price' => [
                    'path' => [0, 8, 1, 0, 0],
                    'fun' => function ($price) {
                        return $price / 1000000;
                    },
                ],
                'apk_is_free' => [
                    'path' => [0, 8, 1, 0, 0],
                    'fun' => function ($price) {
                        return $price === 0;
                    },
                ],
                'apk_description' => [0, 13, 1],
                'apk_downloads' => [0, 15],
                'apk_rating_text' => [0, 4, 0],
                'apk_rating' => [0, 4, 1],
            ];

            return $search_mappings;

        } elseif ($index === 24) {

            $search_mappings_1 = [
                'apk_name' => [16, 2, 0, 0],
                'apk_id' => [16, 11, 0, 0],
                'apk_url' => [
                    'path' => [17, 0, 0, 4, 2],
                    'fun' => function ($path) {
                        $url = rtrim('https://play.google.com/', '/') . '/' . ltrim($path, '/');
                        return $url;
                    },
                ],
                'apk_thumbnail' => [
                    'path' => [16, 2, 95, 0, 3, 2],
                    'fun' => function ($thumbnail) {
                        return $thumbnail . '=w96-rw';
                    },
                ],
                'apk_banner' => [16, 2, 96, 0, 3, 2],
                'apk_developer' => [16, 2, 68, 0],
                'apk_currency' => [17, 0, 2, 0, 1, 0, 1],
                'apk_price' => [
                    'path' => [17, 0, 2, 0, 1, 0, 0],
                    'fun' => function ($price) {
                        return $price / 1000000;
                    },
                ],
                'apk_is_free' => [
                    'path' => [17, 0, 2, 0, 1, 0, 0],
                    'fun' => function ($price) {
                        return $price === 0;
                    },
                ],
                'apk_description' => [16, 2, 73, 0, 1],
                'apk_downloads' => [16, 2, 13, 0],
                'apk_rating_text' => [16, 2, 51, 0, 0],
                'apk_rating' => [16, 2, 51, 0, 1],
            ];

            $search_mappings_2 = [
                'apk_name' => [0, 3],
                'apk_id' => [0, 0, 0],
                'apk_url' => [
                    'path' => [0, 10, 4, 2],
                    'fun' => function ($path) {
                        $url = rtrim('https://play.google.com/', '/') . '/' . ltrim($path, '/');
                        return $url;
                    },
                ],
                'apk_thumbnail' => [
                    'path' => [0, 1, 3, 2],
                    'fun' => function ($thumbnail) {
                        return $thumbnail . '=w96-rw';
                    },
                ],
                'apk_banner' => [0, 22, 3, 2],
                'apk_developer' => [0, 14],
                'apk_currency' => [0, 8, 1, 0, 1],
                'apk_price' => [
                    'path' => [0, 8, 1, 0, 0],
                    'fun' => function ($price) {
                        return $price / 1000000;
                    },
                ],
                'apk_is_free' => [
                    'path' => [0, 8, 1, 0, 0],
                    'fun' => function ($price) {
                        return $price === 0;
                    },
                ],
                'apk_description' => [0, 13, 1],
                'apk_downloads' => [0, 15],
                'apk_rating_text' => [0, 4, 0],
                'apk_rating' => [0, 4, 1],
            ];

            $search_mappings[] = $search_mappings_1;
            $search_mappings[] = $search_mappings_2;

            return $search_mappings;

        } elseif ($index === 26) {

            $search_mappings = [
                'no_results' => [0, 0, 1],
                'no_results_description' => [1, 0, 1],
            ];

            return $search_mappings;

        }
    }

    private function get_apk_data($gp_url)
    {
        $response = [
            'status' => '',
            'data' => '',
        ];

        $scraper = new Scraper();
        $gp_html = $scraper->scrape($gp_url);

        $html = new simple_html_dom();
        $html->load($gp_html);

        $script_data = $html->find('script[type="application/ld+json"]', 0);

        $get_scripts = $html->find('script');

        $apk_icon_url = null;

        if ($script_data) {

            $script_data = $script_data->innertext;
            $script_data = json_decode($script_data);
            $apk_icon_url = isset($script_data->image) ? $script_data->image : null;

        } else {
            $response['status'] = 'error';
            $response['data'] = [
                'message' => 'Main script not found!',
            ];

            return $response;
        }

        $scripts_count = 0;

        if ($get_scripts) {

            foreach ($get_scripts as $script) {

				if (strpos($script, '[[[[]]],[null,null,[[')) {

					$scripts_count++;

					$data_pattern = "/'ds:([\d]+)'\s*,\s*hash:\s*'(\d+)'\s*,\s*data:\[([^\[\]]*(?:\[(?:[^\[\]]*|(?3))*\][^\[\]]*)*)\]/";

					if (preg_match($data_pattern, $script, $matches)) {
						//$ds = $matches[1];
						////$hash = $matches[2];
						$data = '[' . $matches[3] . ']';
						$data = substr($data, 1, -1);
						$data = json_decode("[$data]", true);

						$response['status'] = 'success';
						$response['data'] = $this->single_map_data($data, $this->apk_mappings());
					} else {
						$response['status'] = 'error';
						$response['data'] = [
							'message' => 'Pattern not matched to script data!',
						];
					}

                }
            }

        } else {

            $response['status'] = 'error';
            $response['data'] = [
                'message' => 'No one script files not available in play store!',
            ];

        }

        return $response;
    }

    private function get_search_data($url)
    {
        $response = [
            'status' => '',
            'data' => '',
        ];

        $scraper = new Scraper();
        $gp_html = $scraper->scrape($url);

        $html = new simple_html_dom();
        $html->load($gp_html);

        $scripts = $html->find('script');

        if ($scripts) {

            foreach ($scripts as $script) {

                if (strpos($script->innertext, "key: 'ds:4'") !== false) {

                    $search_script = $script->innertext;

                    $start_pos = strpos($search_script, "data:[[");

                    if ($start_pos !== false) {

                        $start_pos += strlen("data:");

                        $end_pos = strpos($search_script, ", sideChannel: {}}", $start_pos);

                        if ($end_pos !== false) {
                            $length = $end_pos - $start_pos;

                            $search_data = substr($search_script, $start_pos, $length);

                            $search_data = substr($search_data, 1, -1);

                            $search_data = json_decode("[$search_data]", true);

                            $search_data_length = count($search_data[0][1][0]);

                            if ($search_data_length === 23) {

                                $search_data = $this->search_single_map_data($search_data[0][1][0][22][0], $this->search_mappings(23));

                                $response['status'] = 'success';
                                $response['data'] = $search_data;

                            } elseif ($search_data_length === 24) {
                                $search_mappings = $this->search_mappings(24);

                                $searched_data_1 = [$this->single_map_data($search_data[0][1][0][23], $search_mappings[0])];
                                $searched_data_2 = $this->multiple_map_data($search_data[0][1][1][22][0], $search_mappings[1]);

                                $search_data = array_merge($searched_data_1, $searched_data_2);

                                $response['status'] = 'success';
                                $response['data'] = $search_data;

                            } elseif ($search_data_length === 26) {

                                $search_data = $this->single_map_data($search_data[0][1][0][25][0], $this->search_mappings(26));

                                $response['status'] = 'error';
                                $response['data'] = [
                                    'message' => 'No results found :(',
                                ];

                            } else {

                                $response['status'] = 'error';
                                $response['data'] = [
                                    'message' => 'Requested data not found in script data.',
                                ];

                            }

                        } else {

                            $response['status'] = 'error';
                            $response['data'] = [
                                'message' => 'Data found but sideChannel not found in script.',
                            ];

                        }

                    } else {

                        $response['status'] = 'error';
                        $response['data'] = [
                            'message' => 'Script not found in play store.',
                        ];

                    }

                }

            }

        } else {

            $response['status'] = 'error';
            $response['data'] = [
                'message' => 'No one script files not available in play store!',
            ];

        }

        return $response;
    }

    public function extract($gp_url, $type = '')
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
        
        $gplay_url = $gp_url . '&gl=US';

        if (!empty($this->post_language) && $this->post_language != '0') {
            $gplay_url = $gp_url . '&hl=' . $this->post_language . '&gl=US';
        }

        $gp_url_validation = $this->validate_gplay_url($gplay_url);

        if ($gp_url_validation === false) {
            $response = [
                'status' => 'error',
                'data' => [
                    'message' => 'Please enter a valid Google Play Store URL!',
                ],
            ];

            return $response;
        }

        $gp_url_response = $this->validate_url_response_code($gplay_url);

        if (!$gp_url_response) {
            $response = [
                'status' => 'error',
                'data' => [
                    'message' => 'URL not available on Play Store.',
                ],
            ];

            return $response;
        }

        if (!empty($type) && $type === 'apk') {
            $gp_id_validation = $this->validate_gplay_id($gplay_url);

            if (!$gp_id_validation) {
                $response = [
                    'status' => 'error',
                    'data' => [
                        'message' => 'APK Package ID not found in URL!',
                    ],
                ];

                return $response;
            }

            $response = $this->get_apk_data($gplay_url);

            if ($response['status'] === 'success') {
                $apk_post_creator = new AT_Create_GP_Post($response);
                $response = $apk_post_creator->create_post();

                return $response;
            } else {
                return $response;
            }

        } elseif (!empty($type) && $type === 'search') {
            return $this->get_search_data($gplay_url);
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