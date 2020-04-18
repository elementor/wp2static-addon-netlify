<?php

namespace WP2StaticNetlify;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use GuzzleHttp\Client;

/**
 * Netlify Deployer
 *
 * - uses Netlify's digest method, doesn't need WP2Static's DeployCache
 */
class Deployer {

    public function upload_files( string $processed_site_path ) : void {
        $deployed = 0;
        $cache_skipped = 0;
        $file_hashes = [];
        $filename_path_hashes = [];

        if ( ! is_dir( $processed_site_path ) ) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $processed_site_path,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        // Get all deployable file hashes to send to Netlify
        foreach ( $iterator as $filename => $file_object ) {
            $base_name = basename( $filename );

            if ( $base_name != '.' && $base_name != '..' ) {
                $real_filepath = realpath( $filename );

                if ( ! $real_filepath ) {
                    $err = 'Trying to deploy unknown file: ' . $filename;
                    \WP2Static\WsLog::l( $err );
                    continue;
                }

                // Standardise all paths to use / (Windows support)
                $filename = str_replace( '\\', '/', $filename );

                if ( ! is_string( $filename ) ) {
                    continue;
                }

                $remote_path = str_replace( $processed_site_path, '', $filename );
                $hash = sha1_file( $filename );
                $file_hashes[ $remote_path ] = $hash;
                $filename_path_hashes[ $hash ] = [ $filename, $remote_path ];
            }
        }

        // Send digest to Netlify to confirm which files have changed
        $site_id = Controller::getValue( 'siteID' );
        $access_token = \WP2Static\CoreOptions::encrypt_decrypt(
            'decrypt',
            Controller::getValue( 'accessToken' )
        );

        $payload = [ 'files' => $file_hashes ];

        $client = new Client( [ 'base_uri' => 'https://api.netlify.com/' ] );

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Accept'        => 'application/json',
        ];

        $res = $client->request(
            'POST',
            "/api/v1/sites/$site_id/deploys",
            [
                'headers' => $headers,
                'json' => $payload,
            ]
        );

        $response = json_decode( $res->getBody() );
        $deploy_id = $response->id;
        $state = $response->state;
        $required_hashes = $response->required;

        // TODO: rm duplicate hashes - Netlify only wants one if identical
        // TODO: easy optimizations by filtering lists
        foreach ( $filename_path_hashes as $hash => $file_info ) {
            $filename = $file_info[0];
            $remote_path = $file_info[1];

            // if hash is in required_hashes
            if ( in_array( $hash, $required_hashes ) ) {
                $headers = [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Accept'        => 'application/json',
                    'Content-Type' => 'application/octet-stream',
                ];

                $remote_path = urlencode( $remote_path );

                $res = $client->request(
                    'PUT',
                    "/api/v1/deploys/$deploy_id/files/$remote_path",
                    [
                        'headers' => $headers,
                        // 'body' => file_get_contents($filename),
                        'body' => fopen( $filename, 'r' ),
                    ]
                );

                $deployed++;
            } else {
                $cache_skipped++;
            }
        }

        \WP2Static\WsLog::l(
            "Netlify deploy complete. $deployed deployed, $cache_skipped unchanged."
        );
    }
}

