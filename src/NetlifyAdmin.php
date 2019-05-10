<?php

namespace WP2Static;

class NetlifyAdmin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function enqueue_scripts() {
		wp_enqueue_script( WP2STATIC_NETLIFY_PATH . 'js/wp2static-addon-netlify-admin.js', array( 'jquery' ), $this->version, false );
	}
}
