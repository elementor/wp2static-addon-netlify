<?php

namespace WP2StaticNetlify;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Aws\Netlify\NetlifyClient;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;

// TODO: pull out of old core GuessMimeType( $local_file )

class Deployer {

    // prepare deploy, if modifies URL structure, should be an action
    // $this->prepareDeploy();

    // options - load from addon's static methods

    public function __construct() {}

    public function upload_files ( $processed_site_path ) : void {
        // check if dir exists
        if ( ! is_dir( $processed_site_path ) ) {
            return;
        }

        $client_options = [
            'profile' => Controller::getValue( 'netlifyProfile' ),
            'version' => 'latest',
            'region' => Controller::getValue( 'netlifyRegion' ),
        ];

        /*
            If no credentials option, SDK attempts to load credentials from your environment in the following order:

                 - environment variables.
                 - a credentials .ini file.
                 - an IAM role.
        */
        if (
            Controller::getValue( 'netlifyAccessKeyID' ) &&
            Controller::getValue( 'netlifySecretAccessKey' )
        ) {
            error_log('using supplied creds');
            $client_options['credentials'] = [
                'key' => Controller::getValue( 'netlifyAccessKeyID' ),
                'secret' => \WP2StaticNetlify\Controller::encrypt_decrypt(
                    'decrypt',
                    Controller::getValue( 'netlifySecretAccessKey' )
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
            Controller::getValue( 'netlifySecretAccessKey' )
        ) {

            $credentials = new Aws\Credentials\Credentials(
                Controller::getValue( 'netlifyAccessKeyID' ),
                \WP2StaticNetlify\Controller::encrypt_decrypt(
                    'decrypt',
                    Controller::getValue( 'netlifySecretAccessKey' )
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

