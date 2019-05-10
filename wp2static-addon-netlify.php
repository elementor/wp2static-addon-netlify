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

// @codingStandardsIgnoreStart
$ajax_action = isset( $_POST['ajax_action'] ) ? $_POST['ajax_action'] : '';
// @codingStandardsIgnoreEnd

if ( $ajax_action == 'test_netlify' ) {
    $netlify = new WP2Static\Netlify();

    $netlify->test_netlify();

    wp_die();
    return null;
} elseif ( $ajax_action == 'netlify_do_export' ) {
    $netlify = new WP2Static\Netlify();

    $netlify->bootstrap();
    $netlify->deploy();

    wp_die();
    return null;
}

define( 'PLUGIN_NAME_VERSION', '0.1' );

function run_wp2static_addon_netlify() {
	$plugin = new WP2Static\NetlifyAddon();
	$plugin->run();

}

run_wp2static_addon_netlify();

