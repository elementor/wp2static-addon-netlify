<?php

/**
 * Plugin Name:       WP2Static Add-on: Netlify
 * Plugin URI:        https://wp2static.com
 * Description:       AWS Netlify as a deployment option for WP2Static.
 * Version:           0.1
 * Author:            Leon Stafford
 * Author URI:        https://ljs.dev
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-netlify
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WP2STATIC_NETLIFY_PATH', plugin_dir_path( __FILE__ ) );

require WP2STATIC_NETLIFY_PATH . 'vendor/autoload.php';

define( 'PLUGIN_NAME_VERSION', '0.1' );

function run_wp2static_addon_netlify() {

	$plugin = new WP2Static\NetlifyAddon();
	$plugin->run();

}

run_wp2static_addon_netlify();

