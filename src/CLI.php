<?php

namespace WP2StaticNetlify;

use WP_CLI;

/**
 * WP2StaticNetlify WP-CLI commands
 *
 * Registers WP-CLI commands for WP2StaticNetlify under main wp2static cmd
 *
 * Usage: wp wp2static options set siteID mysiteid
 */
class CLI {

    /**
     * Netlify add-on commands
     *
     * @param string[] $args CLI args
     * @param string[] $assoc_args CLI args
     */
    public static function netlify(
        array $args,
        array $assoc_args
    ) : void {
        $action = isset( $args[0] ) ? $args[0] : null;
        $arg = isset( $args[1] ) ? $args[1] : null;

        if ( empty( $action ) ) {
            WP_CLI::error( 'Missing required argument: <options>' );
        }

        if ( $action === 'options' ) {
            if ( empty( $arg ) ) {
                WP_CLI::error( 'Missing required argument: <get|set|list>' );
            }

            $option_name = isset( $args[2] ) ? $args[2] : null;

            if ( $arg === 'get' ) {
                if ( empty( $option_name ) ) {
                    WP_CLI::error( 'Missing required argument: <option-name>' );
                    return;
                }

                // decrypt accessToken
                if ( $option_name === 'accessToken' ) {
                    $option_value = \WP2Static\CoreOptions::encrypt_decrypt(
                        'decrypt',
                        Controller::getValue( $option_name )
                    );
                } else {
                    $option_value = Controller::getValue( $option_name );
                }

                WP_CLI::line( $option_value );
            }

            if ( $arg === 'set' ) {
                if ( empty( $option_name ) ) {
                    WP_CLI::error( 'Missing required argument: <option-name>' );
                    return;
                }

                $option_value = isset( $args[3] ) ? $args[3] : null;

                if ( empty( $option_value ) ) {
                    $option_value = '';
                }

                // decrypt accessToken
                if ( $option_name === 'accessToken' ) {
                    $option_value = \WP2Static\CoreOptions::encrypt_decrypt(
                        'encrypt',
                        $option_value
                    );
                }

                Controller::saveOption( $option_name, $option_value );
            }

            if ( $arg === 'list' ) {
                $options = Controller::getOptions();

                // decrypt accessToken
                $options['accessToken']->value = \WP2Static\CoreOptions::encrypt_decrypt(
                    'decrypt',
                    $options['accessToken']->value
                );

                WP_CLI\Utils\format_items(
                    'table',
                    $options,
                    [ 'name', 'value' ]
                );
            }
        }
    }
}

