<?php

/**
 * Plugin Name: Featured Image Column
 * Plugin URI: http://austin.passy.co/wordpress-plugins/featured-image-column
 * Description: Adds a column to the edit screen with the featured image if it exists.
 * Version: 0.3
 * Author: Austin Passy
 * Author URI: http://austin.passy.co
 *
 * @copyright 2009 - 2017
 * @author Austin Passy
 * @link http://frosty.media/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package TheFrosty\Featured_Image_Column
 */

namespace TheFrosty;

/**
 * Class Featured_Image_Column
 *
 * @package TheFrosty
 */
class Featured_Image_Column {

    const DOMAIN = 'featured-image-column';
    const ID = 'featured-image';
    const VERSION = '0.3';

    /**
     * Ensures that the rest of the code only runs on edit.php pages
     *
     * @since 0.1
     */
    public function add_hooks() {
        add_action( 'load-edit.php', [ $this, 'load' ] );
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_setting' ] );
    }

    /**
     * Since the load-edit.php hook is too early for checking the post type, hook the rest
     * of the code to the wp action to allow the query to be run first
     *
     * @since 0.1.6
     */
    public function load() {
        add_action( 'wp', [ $this, 'init' ] );
    }

    /**
     * Register our settings page.
     *
     * @since 0.2.2
     */
    public function admin_menu() {
        add_options_page(
            'Featured Image Column Settings',
            'Featured Image Col',
            'manage_options',
            self::DOMAIN,
            [ $this, 'settings_page' ]
        );
    }

    /**
     * Output our settings.
     *
     * @since 0.2.2
     */
    public function settings_page() {
        if ( false === ( $post_types = get_option( 'featured_image_column', false ) ) ) {
            $post_types = [];
            foreach ( $this->get_post_types() as $key => $label ) {
                if ( post_type_supports( $label, 'thumbnail' ) ) {
                    $post_types[ $label ] = $label;
                }
            }
            update_option( 'featured_image_column', $post_types );
        }

        include __DIR__ . '/views/settings.php';
    }

    /**
     * Register our setting.
     *
     * @since 0.2.2
     */
    public function register_setting() {
        register_setting(
            'featured_image_column_post_types',
            'featured_image_column',
            [ $this, 'sanitize_callback' ]
        );
    }

    /**
     * Sanitize our setting.
     *
     * @since 0.2.2
     *
     * @param array $input
     *
     * @return array
     */
    public function sanitize_callback( $input ) {
        $input = array_map( 'sanitize_key', $input );

        return $input;
    }

    /**
     * Sets up the Featured_Image_Column plugin and loads files at the appropriate time.
     *
     * @since 0.1.6
     */
    public function init() {
        do_action( 'featured_image_column_init' );

        /**
         * Sample filter to remove post type
         * add_filter( 'featured_image_column_post_types', array( $this, 'remove_post_type' ), 99 );
         */
        add_filter( 'featured_image_column_post_types', [ $this, 'add_setting_post_types' ] );

        $post_type = get_post_type();

        /* Bail early if $post_type isn't supported */
        if ( ! $this->included_post_types( $post_type ) ) {
            return;
        }

        /* Print style */
        add_action( 'admin_enqueue_scripts', [ $this, 'style' ], 0 );

        /* Column manager */
        add_filter( "manage_edit-{$post_type}_columns", [ $this, 'columns' ] );
        add_action( "manage_{$post_type}_posts_custom_column", [ $this, 'column_data' ], 10, 2 );

        do_action( 'featured_image_column_loaded' );
    }

    /**
     * Filter our settings into our $post_type array and add our new Post Types.
     *
     * @since 0.2.2
     *
     * @param array $post_types
     *
     * @return array
     */
    public function add_setting_post_types( $post_types ) {
        $setting_post_types = get_option( 'featured_image_column', [] );
        $post_types         = array_merge( $post_types, array_keys( $setting_post_types ) );

        return $post_types;
    }

    /**
     * Enqueue our stylesheet.
     *
     * @since 0.1
     */
    public function style() {
        wp_register_style(
            'featured-image-column',
            apply_filters( 'featured_image_column_css', plugin_dir_url( __FILE__ ) . 'css/column.css' ),
            null,
            self::VERSION
        );
        wp_enqueue_style( 'featured-image-column' );
    }

    /**
     * Filter the image in before the 'title'
     *
     * @param array $columns
     *
     * @return array
     */
    public function columns( $columns ) {
        if ( ! is_array( $columns ) ) {
            $columns = [];
        }

        $new_columns = [];
        foreach ( $columns as $key => $title ) {
            // Put the Thumbnail column before the Title column
            if ( $key == 'title' ) {
                $new_columns[ self::ID ] = __( 'Image', self::DOMAIN );
            }

            $new_columns[ $key ] = $title;
        }

        return $new_columns;
    }

    /**
     * Output the image
     *
     * @param string $column_name
     * @param int $post_id
     */
    public function column_data( $column_name, $post_id ) {
        if ( self::ID != $column_name ) {
            return;
        }

        $image_src = $this->get_the_image( $post_id );

        if ( empty( $image_src ) ) {
            echo "&nbsp;"; // This helps prevent issues with empty cells

            return;
        }

        echo '<img alt="' . esc_attr( get_the_title() ) . '" src="' . esc_url( $image_src ) . '">';
    }

    /**
     * Function to get the image
     *
     * @since 0.1
     * @updated 0.1.3 - Added wp_cache_set()
     * @updated 0.1.9 - fixed persistent cache per post_id
     * @link http://www.ethitter.com/slides/wcmia-caching-scaling-2012-02-18/#slide-11
     *
     * @param int $post_id
     *
     * @return string
     */
    public function get_the_image( $post_id ) {
        $cache_key = "featured_image_post_id-{$post_id}-_thumbnail";
        $cache     = wp_cache_get( $cache_key, null );

        if ( ! is_array( $cache ) ) {
            $cache = [];
        }

        if ( ! array_key_exists( $cache_key, $cache ) ) {
            if ( empty( $cache ) || ! is_string( $cache ) ) {
                $output = '';

                if ( has_post_thumbnail( $post_id ) ) {
                    $image_array = wp_get_attachment_image_src(
                        get_post_thumbnail_id( $post_id ),
                        [ 36, 32, ]
                    );

                    if ( is_array( $image_array ) && is_string( $image_array[0] ) ) {
                        $output = $image_array[0];
                    }
                }

                if ( empty( $output ) ) {
                    $output = plugins_url( 'images/default.png', __FILE__ );
                    $output = apply_filters( 'featured_image_column_default_image', $output );
                }

                $output              = esc_url( $output );
                $cache[ $cache_key ] = $output;

                wp_cache_set( $cache_key, $cache, null, DAY_IN_SECONDS );

                return $cache[ $cache_key ];
            }
        }

        /**
         * Make sure we're returning the cached image
         * HT: https://wordpress.org/support/topic/do-not-display-image-from-cache?replies=1#post-6773703
         */
        return isset( $cache[ $cache_key ] ) ? $cache[ $cache_key ] :
            isset( $output ) ? $output : '';
    }

    /**
     * Allowed post types
     *
     * @since 0.2
     * @link http://wordpress.org/support/topic/plugin-featured-image-column-filter-for-post-types?replies=5
     *
     * @param string $post_type
     *
     * @return bool
     */
    private function included_post_types( $post_type ) {
        $post_types = [];
        foreach ( $this->get_post_types() as $type ) {
            if ( post_type_supports( $type, 'thumbnail' ) ) {
                $post_types[] = $type;
            }
        }

        $post_types = apply_filters( 'featured_image_column_post_types', $post_types );

        return in_array( $post_type, $post_types );
    }

    /**
     * Helper function to return all public post ty`pes
     *
     * @since 0.2.2
     *
     * @return array
     */
    private function get_post_types() {
        return get_post_types( [ 'public' => true ] );
    }

    /**
     * Helper function to test post_type against its support
     *
     * @since 0.2.2
     *
     * @param string $supports
     *
     * @return array
     */
    private function post_type_supports( $supports = 'thumbnail' ) {
        $post_types = [];

        foreach ( $this->get_post_types() as $key => $label ) {
            if ( post_type_supports( $label, $supports ) ) {
                $post_types[] = $label;
            }
        }

        return $post_types;
    }
}

( new Featured_Image_Column() )->add_hooks();
