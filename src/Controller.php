<?php

namespace WP2StaticNetlify;

class Controller {
    public function run() : void {
        add_filter(
            'wp2static_add_menu_items',
            [ 'WP2StaticNetlify\Controller', 'addSubmenuPage' ]
        );

        add_action(
            'admin_post_wp2static_netlify_save_options',
            [ $this, 'saveOptionsFromUI' ],
            15,
            1
        );

        add_action(
            'wp2static_deploy',
            [ $this, 'deploy' ],
            15,
            2
        );

        add_action(
            'admin_menu',
            [ $this, 'addOptionsPage' ],
            15,
            1
        );

        do_action(
            'wp2static_register_addon',
            'wp2static-addon-netlify',
            'deploy',
            'Netlify Deployment',
            'https://wp2static.com/addons/netlify/',
            'Deploys to Netlify'
        );

        if ( defined( 'WP_CLI' ) ) {
            \WP_CLI::add_command(
                'wp2static netlify',
                [ CLI::class, 'netlify' ]
            );
        }
    }

    /**
     *  Get all add-on options
     *
     *  @return mixed[] All options
     */
    public static function getOptions() : array {
        global $wpdb;
        $options = [];

        $table_name = $wpdb->prefix . 'wp2static_addon_netlify_options';

        $rows = $wpdb->get_results( "SELECT * FROM $table_name" );

        foreach ( $rows as $row ) {
            $options[ $row->name ] = $row;
        }

        return $options;
    }

    /**
     * Seed options
     */
    public static function seedOptions() : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_netlify_options';

        $query_string =
            "INSERT INTO $table_name (name, value, label, description)
            VALUES (%s, %s, %s, %s);";

        $query = $wpdb->prepare(
            $query_string,
            'accessToken',
            '',
            'Personal Access Token',
            ''
        );

        $wpdb->query( $query );

        $query = $wpdb->prepare(
            $query_string,
            'siteID',
            '',
            'Site ID',
            ''
        );

        $wpdb->query( $query );
    }

    /**
     * Save options
     *
     * @param mixed $value value to save
     */
    public static function saveOption( string $name, $value ) : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_netlify_options';

        $query_string = "INSERT INTO $table_name (name, value) VALUES (%s, %s);";
        $query = $wpdb->prepare( $query_string, $name, $value );

        $wpdb->query( $query );
    }

    public static function renderNetlifyPage() : void {
        $view = [];
        $view['nonce_action'] = 'wp2static-netlify-options';
        $view['uploads_path'] = \WP2Static\SiteInfo::getPath( 'uploads' );
        $netlify_path =
            \WP2Static\SiteInfo::getPath( 'uploads' ) . 'wp2static-processed-site.netlify';

        $view['options'] = self::getOptions();

        $view['netlify_url'] =
            is_file( $netlify_path ) ?
                \WP2Static\SiteInfo::getUrl( 'uploads' ) . 'wp2static-processed-site.netlify' : '#';

        require_once __DIR__ . '/../views/netlify-page.php';
    }

    public function deploy( string $processed_site_path, string $enabled_deployer ) : void {
        if ( $enabled_deployer !== 'wp2static-addon-netlify' ) {
            return;
        }

        \WP2Static\WsLog::l( 'Starting Netlify deployment.' );

        $netlify_deployer = new Deployer();
        $netlify_deployer->upload_files( $processed_site_path );
    }

    public static function activate_for_single_site() : void {
        // initialize options DB
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_netlify_options';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            value VARCHAR(255) NOT NULL,
            label VARCHAR(255) NULL,
            description VARCHAR(255) NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // check for seed data
        // if deployment_url option doesn't exist, create:
        $options = self::getOptions();

        if ( ! isset( $options['siteID'] ) ) {
            self::seedOptions();
        }
    }

    public static function deactivate_for_single_site() : void {
    }

    public static function deactivate( bool $network_wide = null ) : void {
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::deactivate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::deactivate_for_single_site();
        }
    }

    public static function activate( bool $network_wide = null ) : void {
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::activate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::activate_for_single_site();
        }
    }

    /**
     * Add submenu to WP2Static sidebar menu
     *
     * @param mixed[] $submenu_pages list of menu items
     * @return mixed[] list of menu items
     */
    public static function addSubmenuPage( array $submenu_pages ) : array {
        $submenu_pages['netlify'] = [ 'WP2StaticNetlify\Controller', 'renderNetlifyPage' ];

        return $submenu_pages;
    }

    public static function saveOptionsFromUI() : void {
        check_admin_referer( 'wp2static-netlify-options' );

        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_netlify_options';

        $personal_access_token =
            $_POST['accessToken'] ?
            \WP2Static\CoreOptions::encrypt_decrypt(
                'encrypt',
                sanitize_text_field( $_POST['accessToken'] )
            ) : '';

        $wpdb->update(
            $table_name,
            [ 'value' => $personal_access_token ],
            [ 'name' => 'accessToken' ]
        );

        $wpdb->update(
            $table_name,
            [ 'value' => sanitize_text_field( $_POST['siteID'] ) ],
            [ 'name' => 'siteID' ]
        );

        wp_safe_redirect(
            admin_url( 'admin.php?page=wp2static-addon-netlify' )
        );

        exit;
    }

    /**
     * Get option value
     *
     * @return string option value
     */
    public static function getValue( string $name ) : string {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_netlify_options';

        $sql = $wpdb->prepare(
            "SELECT value FROM $table_name WHERE" . ' name = %s LIMIT 1',
            $name
        );

        $option_value = $wpdb->get_var( $sql );

        if ( ! is_string( $option_value ) ) {
            return '';
        }

        return $option_value;
    }

    public function addOptionsPage() : void {
        add_submenu_page(
            '',
            'Netlify Deployment Options',
            'Netlify Deployment Options',
            'manage_options',
            'wp2static-addon-netlify',
            [ $this, 'renderNetlifyPage' ]
        );
    }
}

