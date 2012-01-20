<?php
/**
 * Plugin Name: Featured Image Column
 * Plugin URI: http://austinpassy.com/wordpress-plugins/featured-image-column
 * Description: Adds a column to the edit screen with the featured image if it exists.
 * Version: 0.1.7
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
		
		const domain  = 'featured-image-column';
		const version = '0.1.7';
		
		/**
		 * Ensures that the rest of the code only runs on edit.php pages
		 *
		 * @since 0.1
		 */
		function __construct() {
			add_action( 'load-edit.php',	array( $this, 'load' ) );
		}
		
		/**
		 * Since the load-edit.php hook is too early for checking the post type, hook the rest
		 * of the code to the wp action to allow the query to be run first
		 *
		 * @since 0.1.6
		 */
		function load() {
			add_action( 'wp',				array( $this, 'init' ) );
		}
		
		/**
		 * Sets up the Featured_Image_Column plugin and loads files at the appropriate time.
		 *
		 * @since 0.1.6
		 */
		function init() {
			$post_type = get_post_type();
			
			if ( !post_type_supports( $post_type, 'thumbnail' ) )
				return;			
			
			/* Print style */
			add_action( 'admin_enqueue_scripts',					array( $this, 'style' ), 0 );
			
			/* Column manager */
			add_filter( "manage_{$post_type}_posts_columns",		array( $this, 'columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column",	array( $this, 'column_data' ), 10, 2 );			
			
			do_action( 'featured_image_column_loaded' );
		}
		
		/**
		 * Enqueue stylesheaet
		 * @since 0.1
		 */
		function style() {
			wp_register_style( 'featured-image-column', plugin_dir_url( __FILE__ ) . 'css/column.css', null, self::version );
			wp_enqueue_style( 'featured-image-column' );
		}
		
		/**
		 * Filter the image in before the 'title'
		 */
		function columns( $columns ) {
			if ( !is_array( $columns ) )
				$columns = array();
			
			$new = array();
			
			foreach( $columns as $key => $title ) {
				if ( $key == 'title' ) // Put the Thumbnail column before the Title column
					$new['featured-image'] = __( 'Image', self::domain );
				
				$new[$key] = $title;
			}
			
			return $new;
		}
		
		/**
		 * Output the image
		 */
		function column_data( $column_name, $post_id ) {
			global $post;
			
			if ( 'featured-image' != $column_name )
				return;			
			
			$image_src = self::get_the_image( $post->ID );
			
			if ( empty( $image_src ) ) {
				echo "&nbsp;"; // This helps prevent issues with empty cells
				return;
			}
			
			echo '<img alt="' . esc_attr( get_the_title() ) . '" src="' . esc_url( $image_src ) . '" />';
		}
		
		/**
		 * Function to get the image
		 *
		 * @since	0.1
		 * @updated	0.1.3 - Added wp_cache_set()
		 */
		function get_the_image( $post_id ) {			
			$image = wp_cache_get( 'featured_column_thumbnail', 'post' );
			
			if ( empty( $image) || !is_string( $image ) ) {
				$image = '';
					
				if ( has_post_thumbnail( $post_id ) ) {
					$image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), array( 36, 32 ) );
					
					if ( is_array( $image_array ) && is_string( $image_array[0] ) )
						$image = $image_array[0];
				}
				
				if ( empty( $image ) ) {
					$image = plugins_url( 'images/default.png', __FILE__ );
					$image = apply_filters( 'featured_image_column_default_image', $image );
				}
				
				$image = esc_url( $image );
				
				wp_cache_set( 'featured_column_thumbnail', $image, 'post', 60 * 60 * 24 /* 24 hours */ );
			}
			
			return $image;
		}
	}	
	$featured_image_column = &new Featured_Image_Column();
};

?>