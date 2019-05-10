<?php

namespace WP2Static;

class Netlify extends SitePublisher {

    public function __construct() {
        // TODO: race condition in getting filtered options back
        // use static method to access options, checking for an instance
        // ie Option::getOption('netlifySiteID');
        $plugin = Controller::getInstance();

        error_log($plugin->options->getOption( 'netlifySiteID' ));
        die();

        $this->base_url = 'https://api.netlify.com';

        $this->detectSiteID();
    }

    public function detectSiteID() {
        $this->site_id = $this->settings['netlifySiteID'];

        if ( strpos( $this->site_id, 'netlify.com' ) !== false ) {
            return;
        } elseif ( strpos( $this->site_id, '.' ) !== false ) {
            return;
        } elseif ( strlen( $this->site_id ) === 37 ) {
            return;
        } else {
            $this->site_id .= '.netlify.com';
        }
    }

    public function deploy() {
        $this->zip_archive_path = SiteInfo::getPath('uploads') .
            'wp2static-exported-site.zip';

        $zip_deploy_endpoint = $this->base_url . '/api/v1/sites/' .
            $this->site_id . '/deploys';

        try {
            $headers = array(
                'Authorization: Bearer ' .
                    $this->settings['netlifyPersonalAccessToken'],
                'Content-Type: application/zip',
            );

            $this->client = new Request();

            $this->client->postWithFileStreamAndHeaders(
                $zip_deploy_endpoint,
                $this->zip_archive_path,
                $headers
            );

            $this->checkForValidResponses(
                $this->client->status_code,
                array( '100', '200', '201', '301', '302', '304' )
            );

            $this->finalizeDeployment();
        } catch ( Exception $e ) {
            $this->handleException( $e );
        }
    }

    public function test_netlify() {
        $site_info_endpoint = $this->base_url . '/api/v1/sites/' .
            $this->site_id;

        error_log($site_info_endpoint);

        try {

            $headers = array(
                'Authorization: Bearer ' .
                    $this->settings['netlifyPersonalAccessToken'],
            );

            $this->client = new Request();

            $status_code = $this->client->getWithCustomHeaders(
                $site_info_endpoint,
                $headers
            );

            // NOTE: check for certain header, as response is always 200
            if ( isset( $this->client->headers['x-ratelimit-limit'] ) ) {
                if ( ! defined( 'WP_CLI' ) ) {
                    echo 'SUCCESS';
                }
            } else {
                WsLog::l(
                    'BAD RESPONSE CODE FROM API (' . $status_code . ')'
                );

                http_response_code( $code );

                echo 'Netlify test error';
                wp_die();
            }
        } catch ( Exception $e ) {
            $this->handleException( $e );
        }
    }
}

$netlify = new Netlify();
