<?php
/**
 * @link              www.faboba.com
 * @since             1.0
 * @package           Falang
 *
 * @wordpress-plugin
 * Plugin Name:       Falang multilanguage for WordPress
 * Plugin URI:        www.faboba.com/falangw/
 * Description:       Adds multilingual capability to WordPress (Lite version)
 * Version:           1.3.67
 * Author:            Faboba
 * Author URI:        www.faboba.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       falang
 * Domain Path:       /languages
 *
 * Copyright 2020-2024 Faboba
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * This program incorporates work from the plugin Polylang
 * Copyright 2011-2019 Frédéric Demarle
 * Copyright 2021-2022 WP SYNTEX
 *
 * This program incorporates work from the plugin Sublanguage
 * Copyright 2015-2023 Maxime Schoeni
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

/**
 * Currently plugin version.
 */
define( 'FALANG_VERSION', '1.3.67' );
define( 'FALANG_MIN_WP_VERSION', '4.7' );
define( 'FALANG_FILE', __FILE__ ); // this file
define( 'FALANG_BASENAME', plugin_basename( FALANG_FILE ) ); // plugin name as known by WP
define( 'FALANG_DIR', dirname( FALANG_FILE ) ); // our directory
define( 'FALANG_ADMIN', FALANG_DIR . '/admin');
define( 'FALANG_INC', FALANG_DIR . '/includes');
define( 'FALANG_EXT', FALANG_DIR . '/ext');
define ('FALANG_ADMIN_URL', plugins_url('falang/admin'));


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-falang-activator.php
 */
function activate_falang() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-falang-activator.php';
	Falang_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-falang-deactivator.php
 */
function deactivate_falang() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-falang-deactivator.php';
	Falang_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_falang' );
register_deactivation_hook( __FILE__, 'deactivate_falang' );


require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-falang.php';

/**
 *  The api class
 */
require plugin_dir_path( __FILE__ ) . 'includes/api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0
 */

function run_falang() {
	$plugin = new Falang();
	$plugin->run();
}
run_falang();

