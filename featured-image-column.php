<?php
/**
 * Plugin Name: Featured Image Column
 * Plugin URI: http://austinpassy.com/wordpress-plugins/featured-image-column
 * Description: 
 * Version: 0.1
 * Author: Austin Passy
 * Author URI: http://austinpassy.com
 *
 * @copyright 2009 - 2011
 * @author Austin Passy
 * @link http://frostywebdesigns.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package Featured_Image_Column
 */

if ( !class_exists( 'Featured_Image_Column' ) ) {
	class Featured_Image_Column {
		
		const domain	= 'featured-image-column';
		const version	= '0.1';
		
		function Featured_Image_Column() {
			$this->__construct();
		}
		
		/**
		 * Sets up the Featured_Image_Column plugin and loads files at the appropriate time.
		 *
		 * @since 0.1
		 */
		function __construct() {			
			/* Define constants */
			add_action( 'plugins_loaded', 				array( __CLASS__, 'constants' ) );
			
			add_action( 'admin_init', 					array( __CLASS__, 'localize' ) );
			
			add_action( 'init', 						array( __CLASS__, 'add_image_size' ) );
			
			/* Print style */
			add_action( 'admin_head', 					array( __CLASS__, 'style' ) );
			
			/* Column manager */
			add_filter( 'manage_posts_columns', 		array( __CLASS__, 'columns' ), 10, 2 );
			add_action( 'manage_posts_custom_column', 	array( __CLASS__, 'column_data' ), 10, 2 );
		
			do_action( 'featured_image_column_loaded' );
		}
		
		function constants() {		
			/* Set constant path to the plugin directory. */
			define( 'FEATURED_IMAGE_COLUMN_DIR', plugin_dir_path( __FILE__ ) );
			define( 'FEATURED_IMAGE_COLUMN_ADMIN', trailingslashit( FEATURED_IMAGE_COLUMN_DIR ) . 'admin/' );
		
			/* Set constant path to the plugin URL. */
			define( 'FEATURED_IMAGE_COLUMN_URL', plugin_dir_url( __FILE__ ) );
			define( 'FEATURED_IMAGE_COLUMN_IMAGES', FEATURED_IMAGE_COLUMN_URL . 'images/' );
			define( 'FEATURED_IMAGE_COLUMN_CSS', FEATURED_IMAGE_COLUMN_URL . 'css/' );
			define( 'FEATURED_IMAGE_COLUMN_JS', FEATURED_IMAGE_COLUMN_URL . 'js/' );
		}
		
		function localize() {
			load_plugin_textdomain( self::domain, null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add stylesheet
		 * @since 0.01
		 */
		function style() { 
			global $pagenow;
			
			if ( $pagenow == 'edit.php' ) { ?>
				<style type="text/css">
                th#featured-image,
                td.featured-image.column-featured-image {
                    width: 50px;
                }
                </style><?php
			}
		}
		
		function columns( $columns, $post_type ) {	
			$post_types = get_post_types( array( 'public' => true, 'supports' => 'thumbnail'  ), 'objects' );			
			
			if ( !$post_types ) {
				$new = array();
				
				foreach( $columns as $key => $title ) {
					if ( $key == 'title' ) // Put the Thumbnail column before the Author column
						$new['featured-image'] = __( 'Image', self::domain );
					$new[$key] = $title;
				}
			}
			return $new;
		}
		
		function column_data( $column_name, $post_id ) {
			
			$post_type = get_post_type();
			
			if ( post_type_supports( $post_type, 'thumbnail' ) && 'featured-image' == $column_name ) {
				echo '<img alt="' . get_the_title() . '" src="' . self::get_the_image( $post_id ) . '" />';
			}
		}
		
		function add_image_size() {
			add_image_size( 'featured-column-thumbnail', 32, 32, true ); 
		}
		
		function get_the_image( $id ) {
			global $post;
				
			$image = '';
			
			if ( has_post_thumbnail() ) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'featured-column-thumbnail' );
				$image = esc_url( $image[0] );
			} else {
				$image = esc_url( plugins_url( 'images/default.png', __FILE__ ) );
			}
			return apply_filters( 'featured_image_column_default_image', $image );
		}
		
	}
};

$featured_image_column = new Featured_Image_Column; 
			
?>