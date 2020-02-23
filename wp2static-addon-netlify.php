<?php

/**
 * Plugin Name:       WP2Static Add-on: Netlify Deployment
 * Plugin URI:        https://wp2static.com
 * Description:       Netlify deployment add-on for WP2Static.
 * Version:           0.1
 * Author:            Leon Stafford
 * Author URI:        https://ljs.dev
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-netlify
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP2STATIC_NETLIFY_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP2STATIC_NETLIFY_VERSION', '0.1' );

require WP2STATIC_NETLIFY_PATH . 'vendor/autoload.php';

function run_wp2static_addon_netlify() {
    $controller = new WP2StaticNetlify\Controller();
    $controller->run();
}

register_activation_hook(
    __FILE__,
    [ 'WP2StaticNetlify\Controller', 'activate' ]
);

register_deactivation_hook(
    __FILE__,
    [ 'WP2StaticNetlify\Controller', 'deactivate' ]
);

run_wp2static_addon_netlify();

