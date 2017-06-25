<?php

/**
 * Plugin Name: Featured Image Column
 * Plugin URI: http://austin.passy.co/wordpress-plugins/featured-image-column
 * Description: Adds a column to the edit screen with the featured image if it exists.
 * Version: 0.3.2
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

    const ID = 'featured-image';

    /**
     * Ensures that the rest of the code only runs on edit.php pages
     *
     * @since 0.3
     */
    public function add_hooks() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'style' ) );
    }

    /**
     * Activation hook, to add our default settings.
     */
    public function activation_hook() {
        $post_types = array();
        foreach ( $this->get_post_types() as $key => $post_type ) {
            if ( post_type_supports( $post_type, 'thumbnail' ) ) {
                $post_types[ $post_type ] = $post_type;
            }
        }
        update_option( 'featured_image_column', $post_types );
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
            'featured-image-column',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Since the load-edit.php hook is too early for checking the post type, hook the rest
     * of the code to the wp action to allow the query to be run first
     *
     * @since 0.1.6
     * @update 0.3
     */
    public function admin_init() {
        global $pagenow;

        $this->register_settings();

        // Only continue if we're on the 'edit.php' page(s)
        if ( ( empty( $pagenow ) || $pagenow !== 'edit.php' ) &&
             ( defined( 'DOING_AJAX' ) && ! DOING_AJAX )
        ) {
            return;
        }

        // Make sure we've got some post_types saved via our settings.
        $post_types = get_option( 'featured_image_column', array() );
        if ( empty( $post_types ) ) {
            return;
        }

        // Add out custom column and column data
        foreach ( $post_types as $post_type ) {
            if ( ! post_type_supports( $post_type, 'thumbnail' ) ) {
                continue;
            }
            add_filter( "manage_{$post_type}_posts_columns", array( $this, 'columns' ) );
            add_action( "manage_{$post_type}_posts_custom_column",
                array( $this, 'column_data' ), 10, 2 );
        }
    }

    /**
     * Output our settings.
     *
     * @since 0.2.2
     */
    public function settings_page() {
        $post_types = get_option( 'featured_image_column', false );
        if ( $post_types === false ) {
            $post_types = array();
            foreach ( $this->get_post_types() as $key => $post_type ) {
                if ( post_type_supports( $post_type, 'thumbnail' ) ) {
                    $post_types[ $post_type ] = $post_type;
                }
            }
            update_option( 'featured_image_column', $post_types );
        }

        include __DIR__ . '/views/settings.php';
    }

    /**
     * Sanitize our setting.
     *
     * @since 0.2.2
     *
     * @param mixed $input
     *
     * @return array
     */
    public function sanitize_callback( $input ) {
        if ( is_array( $input ) ) {
            return array_map( 'sanitize_key', $input );
        }

        return sanitize_key( $input );
    }

    /**
     * Enqueue our stylesheet on the edit.php page.
     *
     * @since 0.1
     *
     * @param string $hook
     */
    public function style( $hook ) {
        wp_register_style(
            'featured-image-column',
            apply_filters( 'featured_image_column_css', plugin_dir_url( __FILE__ ) . 'css/column.css' ),
            array(),
            '20170625'
        );

        if ( $hook === 'edit.php' ) {
            wp_enqueue_style( 'featured-image-column' );
        }
    }

    /**
     * Filter the image in before the 'title'
     *
     * @param array $columns
     *
     * @return array
     */
    public function columns( array $columns ) {
        if ( ! is_array( $columns ) ) {
            $columns = array();
        }

        $new_columns = array();
        foreach ( $columns as $key => $title ) {
            // Put the Thumbnail column before the Title column
            if ( $key == 'title' ) {
                $new_columns[ self::ID ] = esc_html__( 'Image', 'featured-image-column' );
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

        $featured_image = $this->get_the_image( $post_id );

        if ( ! empty( $featured_image ) ) {
            echo $featured_image;

            return;
        }

        echo "&nbsp;"; // This helps prevent issues with empty cells
    }

    /**
     * Register the plugins settings.
     */
    protected function register_settings() {
        register_setting(
            'featured_image_column_post_types',
            'featured_image_column',
            array( $this, 'sanitize_callback' )
        );
    }

    /**
     * Function to get the image
     *
     * @since 0.1
     * @updated 0.1.3 - Added wp_cache_set()
     * @updated 0.1.9 - fixed persistent cache per post_id
     * @updated 0.3 - Removed wp_cache_*
     *
     * @param int $post_id
     *
     * @return string
     */
    protected function get_the_image( $post_id ) {
        if ( has_post_thumbnail( $post_id ) ) {
            return get_the_post_thumbnail( $post_id );
        } else {
            $default = plugins_url( 'images/default.png', __FILE__ );
            $image   = apply_filters( 'featured_image_column_default_image', $default );

            return '<img alt="' . esc_attr( get_the_title( $post_id ) ) . '"
                src="' . esc_url( $image ) . '">';
        }
    }

    /**
     * Get our settings from the DB with the defaults set to an array.
     *
     * @return array
     */
    protected function get_settings() {
        return get_option( 'featured_image_column', array() );
    }

    /**
     * Helper function to return all public post ty`pes
     *
     * @since 0.2.2
     *
     * @return array
     */
    protected function get_post_types() {
        return get_post_types( array( 'public' => true ) );
    }
}

$featured_image_column = new Featured_Image_Column();
$featured_image_column->add_hooks();
register_activation_hook( __FILE__, array( $featured_image_column, 'activation_hook' ) );
