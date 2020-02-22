<?php

namespace WP2StaticNetlify;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use GuzzleHttp\Client;

class Deployer {

    public function __construct() {}

    public function upload_files ( $processed_site_path ) : void {
        $file_hashes = [];
        $filename_path_hashes = [];

        // check if dir exists
        if ( ! is_dir( $processed_site_path ) ) {
            return;
        }

        // iterate each file in ProcessedSite
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $processed_site_path,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        // 1st iteration to get hashes
        foreach ( $iterator as $filename => $file_object ) {
            $base_name = basename( $filename );
            if ( $base_name != '.' && $base_name != '..' ) {
                $real_filepath = realpath( $filename );

                // TODO: do filepaths differ when running from WP-CLI (non-chroot)?
                // TODO: check if in DeployCache (avoid hashing all files)
                // if ( \WP2Static\DeployCache::fileisCached( $filename ) ) {
                //     continue;
                // }

                if ( ! $real_filepath ) {
                    $err = 'Trying to deploy unknown file: ' . $filename;
                    WsLog::l( $err );
                    continue;
                }

                // Standardise all paths to use / (Windows support)
                $filename = str_replace( '\\', '/', $filename );

                if ( ! is_string( $filename ) ) {
                    continue;
                }

                $remote_path = str_replace( $processed_site_path, '', $filename );

                $hash = sha1( $filename );

                $file_hashes[ $remote_path ] =  $hash;
                $filename_path_hashes[ $hash ] =  [ $filename, $remote_path ];

                // TODO: may skip DeployCache for Netlify when using file digest
                // if ( $result['@metadata']['statusCode'] === 200 ) {
                //     \WP2Static\DeployCache::addFile( $filename );
                // }
            }
        }

        // send file hash to Netlify to check which are required
        $site_id = Controller::getValue( 'siteID' );
        $access_token = \WP2StaticNetlify\Controller::encrypt_decrypt(
            'decrypt',
            Controller::getValue( 'accessToken' )
        );

        // NOTE: formats to JSON as per
        // https://docs.netlify.com/api/get-started/#file-digest-method
        $payload = [ 'files' => $file_hashes ];

        $client = new Client( ['base_uri' => 'https://api.netlify.com/'] );

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

        // error_log( $res->getStatusCode() );
        // error_log( $res->getHeader('content-type')[0] );
        // error_log( json_encode( json_decode( $res->getBody() ), JSON_PRETTY_PRINT ) );

        /*
            response contains:
              - id: 5e51098987987ffa63ae
              - state: uploading
              - required: array of hashes required to PUT
         */
        $response = json_decode( $res->getBody() );

        $deploy_id = $response->id;
        $state =  $response->state;
        $required_hashes = $response->required;

        // error_log( print_r( $required_hashes, true ) );

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
                    'Content-Type' => 'application/octet-stream'
                ];

                error_log('sending required hash: ' . $hash );
                error_log('filename: ' . $filename );
                error_log('remote path: ' . $remote_path );
                error_log('curent hash: ' . sha1( $filename ) );

                // put file
                $res = $client->request(
                    'PUT',
                    "/api/v1/deploys/$deploy_id/files/index.html",
                    [
                        'headers' => $headers,
                        // 'body' => file_get_contents($filename),
                        'body' => fopen($filename, 'r'),
                    ]
                );

                error_log( json_encode( json_decode( $res->getBody() ), JSON_PRETTY_PRINT ) );


            } else {
                error_log('Not required (cached):');
                error_log( print_r( $file_info, true ) );
            }

        }

        error_log('finished PUTing to Netlify');


        // TODO: optimization, build file_hashes once with filenames as extra column
        // map array to just get paths + hashes for Netlify, then filter same original array to get all required hashes only, saving an extra walk 


    }


    public function cloudfront_invalidate_all_items() {
        if ( ! Controller::getValue( 'cfDistributionID' ) ) {
            return;
        }

        \WsLog::l( 'Invalidating all CloudFront items' );

        $client_options = [
            'profile' => 'default',
            'version' => 'latest',
            'region' => Controller::getValue( 'cfRegion' ),
        ];

        /*
            If no credentials option, SDK attempts to load credentials from your environment in the following order:

                 - environment variables.
                 - a credentials .ini file.
                 - an IAM role.
        */
        if (
            Controller::getValue( 'netlifyAccessKeyID' ) &&
            Controller::getValue( 'accessToken' )
        ) {

            $credentials = new Aws\Credentials\Credentials(
                Controller::getValue( 'netlifyAccessKeyID' ),
                \WP2StaticNetlify\Controller::encrypt_decrypt(
                    'decrypt',
                    Controller::getValue( 'accessToken' )
                )
            );

            $client_options['credentials'] = $credentials;
        }

        $client = new Aws\CloudFront\CloudFrontClient( $client );

        try {
            $result = $client->createInvalidation([
                'DistributionId' => Controller::getValue( 'cfDistributionID' ),
                'InvalidationBatch' => [
                    'CallerReference' => 'WP2Static Netlify Add-on',
                    'Paths' => [
                        'Items' => ['/*'],
                        'Quantity' => 1,
                    ],
                ]
            ]);

            error_log(print_r($result, true));

        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
    }
}

