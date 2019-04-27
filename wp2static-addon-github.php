<?php

/**
 * Plugin Name:       WP2Static Add-on: GitHub
 * Plugin URI:        https://wp2static.com
 * Description:       AWS GitHub as a deployment option for WP2Static.
 * Version:           0.1
 * Author:            Leon Stafford
 * Author URI:        https://leonstafford.github.io
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-github
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// @codingStandardsIgnoreStart
$ajax_action = isset( $_POST['ajax_action'] ) ? $_POST['ajax_action'] : '';
// @codingStandardsIgnoreEnd

$wp2static_core_dir =
    dirname( __FILE__ ) . '/../static-html-output-plugin';

$add_on_dir = dirname( __FILE__ );

if ( $ajax_action == 'test_github' ) {
    require_once $wp2static_core_dir .
        '/plugin/WP2Static/SitePublisher.php';
    require_once $add_on_dir . '/GitHub.php';

    wp_die();
    return null;
} elseif ( $ajax_action == 'github_prepare_export' ) {
    require_once $wp2static_core_dir .
        '/plugin/WP2Static/SitePublisher.php';
    require_once $add_on_dir . '/GitHub.php';

    wp_die();
    return null;
} elseif ( $ajax_action == 'github_upload_files' ) {
    require_once $wp2static_core_dir .
        '/plugin/WP2Static/SitePublisher.php';
    require_once $add_on_dir . '/GitHub.php';

    wp_die();
    return null;
}

define( 'PLUGIN_NAME_VERSION', '0.1' );

require plugin_dir_path( __FILE__ ) . 'includes/class-wp2static-addon-github.php';

function run_wp2static_addon_github() {

	$plugin = new Wp2static_Addon_GitHub();
	$plugin->run();

}

run_wp2static_addon_github();

