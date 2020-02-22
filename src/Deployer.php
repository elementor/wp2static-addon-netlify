<?php

namespace WP2StaticNetlify;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use GuzzleHttp\Client;

class Deployer {

    public function __construct() {}

    public function upload_files ( $processed_site_path ) : void {
        $site_id = Controller::getValue( 'siteID' );
        $access_token = \WP2StaticNetlify\Controller::encrypt_decrypt(
            'decrypt',
            Controller::getValue( 'accessToken' )
        );

        error_log($site_id);
        error_log($access_token);
        /*
         ie [
                "/index.html": "aba4cedf9f9d47ac4905040f66b3a50767aeddc2",
                "/style.css": "ee31f7fd72ad321582487cc20f4514ef1eb19d1c",
            ]
         */
        $file_hashes = [
            'files' => [
                "/index.html" => "aba4cedf9f9d47ac4905040f66b3a50767aeddc2",
                "/style.css" => "ee31f7fd72ad321582487cc20f4514ef1eb19d1c",
            ]
        ];

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

        // TODO: quick abort after testing
        return;

        // check if dir exists
        if ( ! is_dir( $processed_site_path ) ) {
            return;
        }

        $client_options = [
            'profile' => Controller::getValue( 'netlifyProfile' ),
            'version' => 'latest',
            'region' => Controller::getValue( 'siteID' ),
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
            error_log('using supplied creds');
            $client_options['credentials'] = [
                'key' => Controller::getValue( 'netlifyAccessKeyID' ),
                'secret' => \WP2StaticNetlify\Controller::encrypt_decrypt(
                    'decrypt',
                    Controller::getValue( 'accessToken' )
                )
            ];
            unset( $client_options['profile'] );
        }

        error_log(print_r($client_options, true));

        // instantiate Netlify client
        $netlify = new \Aws\Netlify\NetlifyClient( $client_options );


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

                // TODO: check if in DeployCache
                if ( \WP2Static\DeployCache::fileisCached( $filename ) ) {
                    continue;
                }

                if ( ! $real_filepath ) {
                    $err = 'Trying to add unknown file to Zip: ' . $filename;
                    WsLog::l( $err );
                    continue;
                }

                // Standardise all paths to use / (Windows support)
                $filename = str_replace( '\\', '/', $filename );

                if ( ! is_string( $filename ) ) {
                    continue;
                }

                $key =
                    Controller::getValue( 'netlifyRemotePath' ) ?
                    Controller::getValue( 'netlifyRemotePath' ) . '/' . ltrim( str_replace( $processed_site_path, '', $filename ), '/' ) :
                    ltrim( str_replace( $processed_site_path, '', $filename ),  '/' );

                $result = $netlify->putObject([
                    'Bucket' => Controller::getValue( 'netlifyBucket' ),
                    'Key' => $key,
                    'Body' => file_get_contents( $filename ),
                    'ACL'    => 'public-read',
                    'ContentType' => mime_content_type( $filename ),
                ]);

                if ( $result['@metadata']['statusCode'] === 200 ) {
                    \WP2Static\DeployCache::addFile( $filename );
                }
            }
        }
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

