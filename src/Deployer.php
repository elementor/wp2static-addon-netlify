<?php

namespace WP2StaticNetlify;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use GuzzleHttp\Client;

class Deployer {

    public function __construct() {}

    /**
     * Post file hashes to Netlify and get list of required hashes
     *
     * @param mixed[] $hashes "filename" => "hash" list
     * @return mixed[] array of hashes needing to be PUT
     */
    public function postHashes ( array $hashes ) : array {
        $site_id = Controller::getValue( 'siteID' );
        $access_token = \WP2StaticNetlify\Controller::encrypt_decrypt(
            'decrypt',
            Controller::getValue( 'accessToken' )
        );

        error_log( print_r( $hashes, true ) );die();

        // NOTE: formats to JSON as per
        // https://docs.netlify.com/api/get-started/#file-digest-method
        $file_hashes = [
            'files' => $hashes
        ];

        // ie $file_hashes = [
        //     'files' => [
        //         "/index.html" => "aba4cedf9f9d47ac4905040f66b3a50767aeddc2",
        //         "/style.css" => "ee31f7fd72ad321582487cc20f4514ef1eb19d1c",
        //     ]
        // ];

        $client = new Client(
            ['base_uri' => 'https://api.netlify.com/']
        );

        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Accept'        => 'application/json',
        ];

        $res = $client->request(
            'POST',
            "/api/v1/sites/$site_id/deploys",
            [
                'headers' => $headers,
                'json' => $file_hashes,
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
        error_log( $response->id );
        error_log( $response->state );
        error_log( print_r($response->required) );

        return $response->required;

    }

    public function upload_files ( $processed_site_path ) : void {
        $file_hashes = [];

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

                // TODO: may skip DeployCache for Netlify when using file digest
                // if ( $result['@metadata']['statusCode'] === 200 ) {
                //     \WP2Static\DeployCache::addFile( $filename );
                // }
            }
        }

        $this->postHashes( $file_hashes );

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

