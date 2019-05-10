<?php

namespace WP2Static;

class NetlifyAddon {

	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '0.1';
		}

		$this->plugin_name = 'wp2static-addon-netlify';
	}

    public function add_deployment_option_to_ui( $deploy_options ) {
        $deploy_options['netlify'] = array('Netlify');

        return $deploy_options;
    }

    public function load_deployment_option_template( $templates ) {
        $templates[] =  __DIR__ . '/../views/netlify_settings_block.phtml';

        return $templates;
    }

    public function add_deployment_option_keys( $keys ) {
        $new_keys = array(
            'baseUrl-netlify',
            'netlifyHeaders',
            'netlifyPersonalAccessToken',
            'netlifyRedirects',
            'netlifySiteID',
        );

        $keys = array_merge(
            $keys,
            $new_keys
        );

        return $keys;
    }

    public function whitelist_deployment_option_keys( $keys ) {
        $whitelist_keys = array(
            'baseUrl-netlify',
            'netlifyHeaders',
            'netlifyRedirects',
            'netlifySiteID',
        );

        $keys = array_merge(
            $keys,
            $whitelist_keys
        );

        return $keys;
    }

    public function add_post_and_db_keys( $keys ) {
        $keys['netlify'] = array(
            'baseUrl-netlify',
            'netlifyHeaders',
            'netlifyPersonalAccessToken',
            'netlifyRedirects',
            'netlifySiteID',
        );

        return $keys;
    }

	public function run() {
        add_filter(
            'wp2static_add_deployment_method_option_to_ui',
            [$this, 'add_deployment_option_to_ui']
        );

        add_filter(
            'wp2static_load_deploy_option_template',
            [$this, 'load_deployment_option_template']
        );

        add_filter(
            'wp2static_add_option_keys',
            [$this, 'add_deployment_option_keys']
        );

        add_filter(
            'wp2static_whitelist_option_keys',
            [$this, 'whitelist_deployment_option_keys']
        );

        add_filter(
            'wp2static_add_post_and_db_keys',
            [$this, 'add_post_and_db_keys']
        );
	}
}
