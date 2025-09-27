<?php
class AT_Create_GP_Post
{
    private $is_advanced_options = false;

    private $apk_description;
    private $apk_whats_new;
    private $apk_name;
    private $apk_developer;
    private $apk_size;
    private $apk_version;
    private $apk_id;
    private $apk_rating;
    private $apk_votes;
    private $apk_screenshots;
    private $apk_thumbnail_url;
    private $apk_banner_url;
    private $apk_category;
    private $apk_sub_category;
    private $apk_content;
    private $apk_content_rating;
	private $apk_required;
	private $apk_price;
    private $apt_id = "";

    private $post_id;
    private $parent_cat;
    private $child_cat;

    private $post_status = 'draft';
    private $post_start_title = '';
    private $post_end_title = '';
    private $post_mod_feature = '';
    private $post_thumbnail_format = 'png';
    private $post_thumbnail_quality = 'raw';
    private $import_screenshots = false;
    private $post_screenshots_format = 'jpg';

    public function __construct($post_meta)
    {
        $default_meta = [
            'apk_description' => '',
            'apk_whats_new' => '',
            'apk_name' => '',
            'apk_developer' => '',
            'apk_size' => '',
            'apk_version' => '',
            'apk_id' => '',
            'apk_rating_text' => '',
            'apk_total_votes' => '',
            'apk_screenshots' => [],
            'apk_thumbnail' => '',
            'apk_banner' => '',
            'apk_category' => '',
            'apk_sub_category' => '',
            'apk_content' => '',
			'apk_content_rating' => '',
			'apk_required_version_text' => '',
			'apk_price' => '',
            'apt_id' => '',
        ];

        $data = array_merge($default_meta, $post_meta['data']);

        $this->apk_description = $data['apk_description'];
        $this->apk_whats_new = $data['apk_whats_new'];
        $this->apk_name = $data['apk_name'];
        $this->apk_developer = $data['apk_developer'];
        $this->apk_size = $data['apk_size'];
        $this->apk_version = $data['apk_version'];
        $this->apk_id = $data['apk_id'];
        $this->apk_rating = $data['apk_rating_text'];
        $this->apk_votes = $data['apk_total_votes'];
        $this->apk_screenshots = $data['apk_screenshots'];
        $this->apk_thumbnail_url = $data['apk_thumbnail'];
        $this->apk_banner_url = $data['apk_banner'];
        $this->apk_category = $data['apk_category'];
        $this->apk_sub_category = $data['apk_sub_category'];
        $this->apk_content = $data['apk_content'];
		$this->apk_content_rating = $data['apk_content_rating'];
		$this->apk_required = $data['apk_required_version_text'];
		$this->apk_price = $data['apk_price'];
        $this->apt_id = $data['apt_id'];

        $this->is_advanced_options = at_options('is_advanced_options', false);

        if ($this->is_advanced_options) {
            $post_status = at_options('post_status');
            $post_start_title = at_options('post_title_start');
            $post_end_title = at_options('post_title_end');
            $post_mod_feature = at_options('mod_feature');
            $post_thumbnail_format = at_options('post_thumbnail_format');
            $post_thumbnail_quality = at_options('post_thumbnail_quality');
            $import_screenshots = at_options('import_screenshots', false);
            $post_screenshots_format = at_options('post_screenshots_format');

            $this->post_status = !empty($post_status) ? $post_status : '';
            $this->post_start_title = !empty($post_start_title) ? $post_start_title : '';
            $this->post_end_title = !empty($post_end_title) ? $post_end_title : '';
            $this->post_mod_feature = !empty($post_mod_feature) ? $post_mod_feature : '';
            $this->post_thumbnail_format = !empty($post_thumbnail_format) ? $post_thumbnail_format : '';
            $this->post_thumbnail_quality = !empty($post_thumbnail_quality) ? $post_thumbnail_quality : '';
            $this->import_screenshots = $import_screenshots ? $import_screenshots : false;
            $this->post_screenshots_format = $post_screenshots_format ? $post_screenshots_format : 'jpg';
        }
    }

    private function generate_post_title()
    {

        $start_title = "";
        $end_title = "";
        $mod_feature_title = "";
        $is_mod_title = at_options('is_mod_title', true);
        $is_title_version = at_options('is_title_version', true);
        $is_mod_feature_title = at_options('is_mod_feature_title', false);

        if ($this->is_advanced_options) {
            $start_title = (!empty($this->post_start_title)) ? $this->post_start_title . ' ' : '';
            $end_title = (!empty($this->post_end_title)) ? $this->post_end_title : '';
            $mod_feature_title = ($is_mod_feature_title && !empty($this->post_mod_feature)) ? '(' . $this->post_mod_feature . ') ' : '';
        }

        $main_title = $this->apk_name . ' ';

        $mod_title = ($is_mod_title) ? 'MOD APK' . ' ' : '';

        $title_version = $this->apk_version;
        $version_title = ($is_title_version && !empty($title_version)) ? 'v' . $title_version . ' ' : '';

        $post_title = $start_title . $main_title . $mod_title . $mod_feature_title . $version_title . $end_title;

        return $post_title;
    }

    private function get_category()
    {
        $categories = [
            'parent_id' => null,
            'child_id' => null,
        ];

        $parent_cat_name = get_parsed_category($this->apk_category);
        $child_cat_name = $this->apk_sub_category;

        $parent_cat_slug = sanitize_title_with_dashes(apktemplates_clean($parent_cat_name));
        $child_cat_slug = sanitize_title_with_dashes(apktemplates_clean($child_cat_name));

        $parent_category = get_term_by("slug", $parent_cat_slug, "category");

        if ($parent_category) {
            $categories['parent_id'] = $parent_category->term_id;
        } else {
            $parent_id = wp_insert_term($parent_cat_name, "category", ['slug' => $parent_cat_slug]);

            if (!is_wp_error($parent_id)) {
                $categories['parent_id'] = $parent_id["term_id"];
            }
        }

        $child_category = get_term_by("slug", $child_cat_slug, "category");

        if ($child_category) {
            $categories['child_id'] = $child_category->term_id;
        } else {
            $child_id = wp_insert_term($child_cat_name, "category", ["parent" => $categories['parent_id'], "slug" => $child_cat_slug]);

            if (!is_wp_error($child_id)) {
                $categories['child_id'] = $child_id["term_id"];
            }
        }

        return $categories;
    }

    private function upload_image_to_wp($image_url, $format = 'png', $name = 'thumbnail', )
    {
        if (!empty($image_url)) {
            $upload_dir = wp_upload_dir();

            $image_name = sanitize_title_with_dashes(apktemplates_clean($this->apk_name));

            $image_full_name = "{$image_name}-{$name}." . $format;
            $image_path = $upload_dir["path"] . "/" . $image_full_name;

            $scraper = new Scraper();
            $fetch_image = $scraper->scrape($image_url);

            $existing_attachment_id = attachment_url_to_postid($upload_dir["url"] . "/" . $image_full_name);

            if ($existing_attachment_id) {
                return $existing_attachment_id;
            } else {
                file_put_contents($image_path, $fetch_image);

                $file_type = wp_check_filetype(basename($image_full_name), null);

                $image_attachment = [
                    "post_mime_type" => $file_type['type'],
                    "post_title" => $image_name,
                    "post_content" => "",
                    "post_status" => "inherit",
                ];

                $image_id = wp_insert_attachment(
                    $image_attachment,
                    $image_path
                );

                require_once ABSPATH . "wp-admin/includes/image.php";

                $image_data = wp_generate_attachment_metadata(
                    $image_id,
                    $image_path
                );

                wp_update_attachment_metadata($image_id, $image_data);

                return $image_id;
            }
        }
    }

    private function add_meta_data()
    {
        add_post_meta($this->post_id, "wp_title_GP", $this->apk_name);
        add_post_meta($this->post_id, "wp_GP_ID", $this->apk_id);
        add_post_meta($this->post_id, "wp_developers_GP", $this->apk_developer);
        add_post_meta($this->post_id, "wp_version_GP", $this->apk_version);
        add_post_meta($this->post_id, "wp_sizes_GP", $this->apk_size);
        add_post_meta($this->post_id, "wp_contentrated_GP", $this->apk_content_rating);
        add_post_meta($this->post_id, "wp_requires_GP", $this->apk_required);
        add_post_meta($this->post_id, "price", $this->apk_price);
		
        if ($this->is_advanced_options && !empty($this->post_mod_feature)) {
            add_post_meta($this->post_id, "wp_mods", $this->post_mod_feature);
        }

        add_post_meta($this->post_id, "avg_rating", floatval($this->apk_rating));
        add_post_meta($this->post_id, "total_votes", intval($this->apk_votes));
        add_post_meta($this->post_id, "apt_id", $this->apt_id);
    }

    private function upload_thumbnail()
    {
        $image_size = '';
        $image_quality = '';

        if ($this->is_advanced_options) {
            if ($this->post_thumbnail_format === 'webp') {
                $image_quality = ($this->post_thumbnail_quality === 'raw') ? '=rw' : '-rw';
            }

            switch ($this->post_thumbnail_quality) {
                case '512':
                    $image_size = '=s512';
                    break;
                case '256':
                    $image_size = '=s256';
                    break;
                case '128':
                    $image_size = '=s128';
                    break;
            };
        }

        $thumbnail_id = $this->upload_image_to_wp($this->apk_thumbnail_url . $image_size . $image_quality, $this->post_thumbnail_format);

        set_post_thumbnail($this->post_id, $thumbnail_id);

    }

    private function upload_banner_image()
    {
        if (!empty($this->apk_banner_url)) {
            $banner_id = $this->upload_image_to_wp($this->apk_banner_url . '=w1024-h500', 'jpg', 'banner');
            $banner_image = wp_get_attachment_url($banner_id);

            add_post_meta($this->post_id, "wp_poster_GP", $banner_image);
        }
    }

    private function add_screenshots_meta()
    {
		$import_ss_limit = (int) at_options('import_ss_limit', '7');
        $screenshots = array();
        $get_screenshots = $this->apk_screenshots;
        $image_quality = '';

        if ($this->post_screenshots_format === 'webp') {
            $image_quality = '=rw';
        }

        if ($this->is_advanced_options && $this->import_screenshots) {
            for ($i = 0; $i < $import_ss_limit; $i++) {
                if (!empty($get_screenshots[$i])) {
                    $ss_name = 'screenshot-' . ($i + 1);
                    $ss_id = $this->upload_image_to_wp(esc_url($get_screenshots[$i]) . $image_quality, $this->post_screenshots_format, $ss_name);
                    $screenshots[$i]["ss_url"] = esc_url(wp_get_attachment_url($ss_id));
                }
            }
        } else {
            for ($i = 0; $i < $import_ss_limit; $i++) {
                if (!empty($get_screenshots[$i])) {
                    $screenshots[$i]["ss_url"] = esc_url($get_screenshots[$i] . $image_quality);
                }
            }
        }

        add_post_meta($this->post_id, "ss_images", $screenshots);

    }

    private function add_download_meta()
    {
        $download_info = [];
        $mod_feature = '';

        if ($this->is_advanced_options && !empty($this->post_mod_feature)) {
            $mod_feature = $this->post_mod_feature;
        }

        $download_info_data = [
            'download_name' => strtoupper($this->apk_name),
            'download_version' => $this->apk_version,
            'download_mod_info' => $mod_feature,
            'download_size' => $this->apk_size,
            'download_url' => 'https://play.google.com/store/apps/details?id=' . $this->apk_id,
        ];

        array_push($download_info, $download_info_data);

        update_post_meta($this->post_id, "repeatable_download_link", $download_info);
    }

    public function create_post()
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
        
        $post_permalink = sanitize_title_with_dashes(apktemplates_clean($this->apk_name));

        $category = $this->get_category();
        $post_content = wp_encode_emoji($this->apk_content);

        $new_post = [
            "post_title" => $this->generate_post_title(),
            "post_name" => $post_permalink,
            "post_content" => $post_content,
            "post_status" => $this->post_status,
            "post_category" => [$category['parent_id'], $category['child_id']],
            "post_type" => "post",
        ];

        $post_id = wp_insert_post($new_post);

        $this->post_id = $post_id;

        $this->add_meta_data();

        $this->upload_thumbnail();

        $this->upload_banner_image();

        $this->add_screenshots_meta();

        $this->add_download_meta();

        $response = [
            'status' => 'success',
            'data' => [
                'message' => '<strong><a href="' . get_edit_post_link($this->post_id) . '" target="_blank">' . $this->apk_name . '</a></strong> post uploaded.',
            ],
        ];

        return $response;
    }

}

class AT_Create_APT_Post
{
    private $apt_is_advanced_options = false;

    private $apk_description;
    private $apk_whats_new;
    private $apk_name;
    private $apk_developer;
    private $apk_size;
    private $apk_version;
    private $apk_id;
    private $apk_rating;
    private $apk_votes;
    private $apk_screenshots;
    private $apk_thumbnail_url;
    private $apk_banner_url;
    private $apk_category;
    private $apk_sub_category;
    private $apk_content;
	private $apk_content_rating;
	private $apk_required;
	private $apk_price;
    private $apk_file_url = "";
    private $apk_file_version = "";
    private $apk_file_size = "";
    private $apt_id = "";

    private $post_id;
    private $parent_cat;
    private $child_cat;

    private $apt_post_status = 'draft';
    private $apt_post_title_start = '';
    private $apt_post_title_end = '';
    private $apt_mod_feature = '';
    private $apt_post_thumbnail_quality = 'raw';
    private $apt_import_screenshots = false;
    private $apt_import_apk_file = false;

    public function __construct($post_meta)
    {
        $default_meta = [
            'apk_description' => '',
            'apk_whats_new' => '',
            'apk_name' => '',
            'apk_developer' => '',
            'apk_size' => '',
            'apk_version' => '',
            'apk_id' => '',
            'apk_rating' => '',
            'apk_total_votes' => '',
            'apk_screenshots' => [],
            'apk_thumbnail' => '',
            'apk_banner' => '',
            'apk_category' => '',
            'apk_sub_category' => '',
            'apk_content' => '',
			'apk_required_version' => '',
			'apk_content_rating' => '',
			'apk_price' => '',
            'apk_file_url' => '',
            'apk_file_version' => '',
            'apk_file_size' => '',
            'apt_id' => '',
        ];

        $data = array_merge($default_meta, $post_meta['data']);

        $this->apk_description = $data['apk_description'];
        $this->apk_whats_new = $data['apk_whats_new'];
        $this->apk_name = $data['apk_name'];
        $this->apk_developer = $data['apk_developer'];
        $this->apk_size = $data['apk_size'];
        $this->apk_version = $data['apk_version'];
        $this->apk_id = $data['apk_id'];
        $this->apk_rating = $data['apk_rating'];
        $this->apk_votes = $data['apk_total_votes'];
        $this->apk_screenshots = $data['apk_screenshots'];
        $this->apk_thumbnail_url = $data['apk_thumbnail'];
        $this->apk_banner_url = $data['apk_banner'];
        $this->apk_category = $data['apk_category'];
        $this->apk_sub_category = $data['apk_sub_category'];
        $this->apk_content = $data['apk_content'];
        $this->apk_content_rating = $data['apk_content_rating'];
        $this->apk_required = $data['apk_required_version'];
        $this->apk_price = $data['apk_price'];
        $this->apk_file_url = $data['apk_file_url'];
        $this->apk_file_version = $data['apk_file_version'];
        $this->apk_file_size = $data['apk_file_size'];
        $this->apt_id = $data['apt_id'];

        $this->apt_is_advanced_options = at_options('apt_is_advanced_options', false);

        if ($this->apt_is_advanced_options) {
            $apt_post_status = at_options('apt_post_status');
            $apt_post_title_start = at_options('apt_post_title_start');
            $apt_post_title_end = at_options('apt_post_title_end');
            $apt_mod_feature = at_options('apt_mod_feature');
            $apt_post_thumbnail_quality = at_options('apt_post_thumbnail_quality');
            $apt_import_screenshots = at_options('apt_import_screenshots', false);
            $is_get_apk = at_options('is_get_apk', false);

            $this->apt_post_status = !empty($apt_post_status) ? $apt_post_status : '';
            $this->apt_post_title_start = !empty($apt_post_title_start) ? $apt_post_title_start : '';
            $this->apt_post_title_end = !empty($apt_post_title_end) ? $apt_post_title_end : '';
            $this->apt_mod_feature = !empty($apt_mod_feature) ? $apt_mod_feature : '';
            $this->apt_post_thumbnail_quality = !empty($apt_post_thumbnail_quality) ? $apt_post_thumbnail_quality : '';
            $this->apt_import_screenshots = $apt_import_screenshots ? $apt_import_screenshots : false;
            $this->apt_import_apk_file = $is_get_apk ? $is_get_apk : false;
        }
    }

    private function generate_post_title()
    {
        $start_title = "";
        $end_title = "";
        $mod_feature_title = "";
        $is_mod_title = at_options('is_mod_title', true);
        $is_title_version = at_options('is_title_version', true);
        $is_mod_feature_title = at_options('is_mod_feature_title', false);

        if ($this->apt_is_advanced_options) {
            $start_title = (!empty($this->apt_post_title_start)) ? $this->apt_post_title_start . ' ' : '';
            $end_title = (!empty($this->apt_post_title_end)) ? $this->apt_post_title_end : '';
            $mod_feature_title = ($is_mod_feature_title && !empty($this->apt_mod_feature)) ? '(' . $this->apt_mod_feature . ') ' : '';
        }

        $main_title = $this->apk_name . ' ';

        $mod_title = ($is_mod_title) ? 'MOD APK' . ' ' : '';

        $title_version = $this->apk_version;
        $version_title = ($is_title_version && !empty($title_version)) ? 'v' . $title_version . ' ' : '';

        $post_title = $start_title . $main_title . $mod_title . $mod_feature_title . $version_title . $end_title;

        return $post_title;
    }

    private function get_category()
    {
        $categories = [
            'parent_id' => null,
            'child_id' => null,
        ];

        $parent_cat_name = $this->apk_category;
        $child_cat_name = $this->apk_sub_category;

        $parent_cat_slug = sanitize_title_with_dashes(apktemplates_clean($parent_cat_name));
        $child_cat_slug = sanitize_title_with_dashes(apktemplates_clean($child_cat_name));

        $parent_category = get_term_by("slug", $parent_cat_slug, "category");

        if ($parent_category) {
            $categories['parent_id'] = $parent_category->term_id;
        } else {
            $parent_id = wp_insert_term($parent_cat_name, "category", ['slug' => $parent_cat_slug]);

            if (!is_wp_error($parent_id)) {
                $categories['parent_id'] = $parent_id["term_id"];
            }
        }

        $child_category = get_term_by("slug", $child_cat_slug, "category");

        if ($child_category) {
            $categories['child_id'] = $child_category->term_id;
        } else {
            $child_id = wp_insert_term($child_cat_name, "category", ["parent" => $categories['parent_id'], "slug" => $child_cat_slug]);

            if (!is_wp_error($child_id)) {
                $categories['child_id'] = $child_id["term_id"];
            }
        }

        return $categories;
    }

    private function upload_image_to_wp($image_url, $format = 'png', $name = 'thumbnail', )
    {
        if (!empty($image_url)) {
            $upload_dir = wp_upload_dir();

            $image_name = sanitize_title_with_dashes(apktemplates_clean($this->apk_name));

            $image_full_name = "{$image_name}-{$name}." . $format;
            $image_path = $upload_dir["path"] . "/" . $image_full_name;

            $scraper = new Scraper();
            $fetch_image = $scraper->scrape($image_url);

            $existing_attachment_id = attachment_url_to_postid($upload_dir["url"] . "/" . $image_full_name);

            if ($existing_attachment_id) {
                return $existing_attachment_id;
            } else {
                file_put_contents($image_path, $fetch_image);

                $file_type = wp_check_filetype(basename($image_full_name), null);

                $image_attachment = [
                    "post_mime_type" => $file_type['type'],
                    "post_title" => $image_name,
                    "post_content" => "",
                    "post_status" => "inherit",
                ];

                $image_id = wp_insert_attachment(
                    $image_attachment,
                    $image_path
                );

                require_once ABSPATH . "wp-admin/includes/image.php";

                $image_data = wp_generate_attachment_metadata(
                    $image_id,
                    $image_path
                );

                wp_update_attachment_metadata($image_id, $image_data);

                return $image_id;
            }
        }
    }

    private function add_meta_data()
    {
        add_post_meta($this->post_id, "wp_title_GP", $this->apk_name);
        add_post_meta($this->post_id, "wp_GP_ID", $this->apk_id);
        add_post_meta($this->post_id, "wp_developers_GP", $this->apk_developer);
        add_post_meta($this->post_id, "wp_version_GP", $this->apk_version);
        add_post_meta($this->post_id, "wp_sizes_GP", apkt_format_bytes($this->apk_size));
        add_post_meta($this->post_id, "wp_contentrated_GP", $this->apk_content_rating);
        add_post_meta($this->post_id, "wp_requires_GP", $this->apk_required);
        add_post_meta($this->post_id, "price", $this->apk_price);

        if ($this->apt_is_advanced_options && !empty($this->apt_mod_feature)) {
            add_post_meta($this->post_id, "wp_mods", $this->apt_mod_feature);
        }

        add_post_meta($this->post_id, "avg_rating", floatval($this->apk_rating));
        add_post_meta($this->post_id, "total_votes", intval($this->apk_votes));
        add_post_meta($this->post_id, "apt_id", $this->apt_id);
    }

    private function upload_thumbnail()
    {
        $image_quality = '';

        if ($this->apt_is_advanced_options) {

            if ($this->apt_post_thumbnail_quality) {
                $image_quality = '';
            }

            switch ($this->apt_post_thumbnail_quality) {
                case '512':
                    $image_quality = '?w=512';
                    break;
                case '256':
                    $image_quality = '?w=256';
                    break;
                case '128':
                    $image_quality = '?w=128';
                    break;
            };
        }

        $thumbnail_id = $this->upload_image_to_wp($this->apk_thumbnail_url . $image_quality, 'png');

        set_post_thumbnail($this->post_id, $thumbnail_id);

    }

    private function upload_banner_image()
    {
        if (!empty($this->apk_banner_url)) {
            $banner_id = $this->upload_image_to_wp($this->apk_banner_url, 'jpg', 'banner');
            $banner_image = wp_get_attachment_url($banner_id);

            add_post_meta($this->post_id, "wp_poster_GP", $banner_image);
        }
    }

    private function add_screenshots_meta()
    {
		$import_ss_limit = (int) at_options('import_ss_limit', '7');
        $screenshots = array();
        $get_screenshots = $this->apk_screenshots;

        if ($this->apt_is_advanced_options && $this->apt_import_screenshots) {
            for ($i = 0; $i < $import_ss_limit; $i++) {
                if (!empty($get_screenshots[$i])) {
                    $ss_name = 'screenshot-' . ($i + 1);
                    $ss_id = $this->upload_image_to_wp(esc_url($get_screenshots[$i]), 'png', $ss_name);
                    $screenshots[$i]["ss_url"] = esc_url(wp_get_attachment_url($ss_id));
                }
            }
        } else {
            for ($i = 0; $i < $import_ss_limit; $i++) {
                if (!empty($get_screenshots[$i])) {
                    $screenshots[$i]["ss_url"] = esc_url($get_screenshots[$i]);
                }
            }
        }

        add_post_meta($this->post_id, "ss_images", $screenshots);

    }

    private function is_apk_file_exist($apk_url, $apk_version)
    {
        $upload_dir = wp_upload_dir();
        $file_base_name = basename($apk_url);

        $pattern = '/^(.*?)-\d+/';

        preg_match($pattern, $file_base_name, $matches);

        if (!empty($matches[1])) {
            $file_name = $matches[1] . '-' . str_replace('.', '-', $apk_version);
        } else {
            $file_name = pathinfo($file_base_name, PATHINFO_FILENAME);
        }

        $file_extension = pathinfo($apk_url, PATHINFO_EXTENSION);
        $file_full_name = $file_name . '.' . $file_extension;
        $file_url = $upload_dir["apk_path_url"] . '/' . $file_full_name;
        $existing_attachment_id = attachment_url_to_postid($file_url);
        return $existing_attachment_id;
        if ($existing_attachment_id) {
            return true;
        } else {
            return $existing_attachment_id;
        }

    }

    public function create_post()
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

        $post_permalink = sanitize_title_with_dashes(apktemplates_clean($this->apk_name));

        $category = $this->get_category();
        $post_content = wp_encode_emoji($this->apk_content);

        $new_post = [
            "post_title" => $this->generate_post_title(),
            "post_name" => $post_permalink,
            "post_content" => $post_content,
            "post_status" => $this->apt_post_status,
            "post_category" => [$category['parent_id'], $category['child_id']],
            "post_type" => "post",
        ];

        $post_id = wp_insert_post($new_post);

        $this->post_id = $post_id;

        $this->add_meta_data();

        $this->upload_thumbnail();

        $this->upload_banner_image();

        $this->add_screenshots_meta();

        $response = [
            'status' => 'success',
            'data' => [
                'message' => '<strong><a href="' . get_edit_post_link($this->post_id) . '" target="_blank">' . $this->apk_name . '</a></strong> post uploaded.',
            ],
        ];

        if ($this->apt_is_advanced_options && $this->apt_import_apk_file) {
            $response['data']['get_apk'] = true;
            $response['data']['is_apk_exist'] = false;
            $response['data']['file_check'] = $this->is_apk_file_exist($this->apk_file_url, $this->apk_file_version);
            if (isset($this->apk_file_url)) {
                $response['data']['has_apk_file'] = true;
                $response['data']['apk_file_url'] = $this->apk_file_url;
                $response['data']['apk_file_version'] = $this->apk_file_version;
                $response['data']['apk_file_size'] = $this->apk_file_size;
                $response['data']['post_id'] = $this->post_id;
            } else {
                $response['data']['has_apk_file'] = false;
            }
        }

        return $response;
    }
}