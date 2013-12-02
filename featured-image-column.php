<?php
/**
 * Plugin Name: Featured Image Column
 * Plugin URI: http://austinpassy.com/wordpress-plugins/featured-image-column
 * Description: Adds a column to the edit screen with the featured image if it exists.
 * Version: 0.2
 * Author: Austin Passy
 * Author URI: http://austinpassy.com
 *
 * @copyright 2009 - 2013
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
		const version = '0.2.0';
		
		/**
		 * Ensures that the rest of the code only runs on edit.php pages
		 *
		 * @since 	0.1
		 */
		function __construct() {
			add_action( 'load-edit.php', array( $this, 'load' ) );
		}
		
		/**
		 * Since the load-edit.php hook is too early for checking the post type, hook the rest
		 * of the code to the wp action to allow the query to be run first
		 *
		 * @since 	0.1.6
		 */
		function load() {
			add_action( 'wp', array( $this, 'init' ) );
		}
		
		/**
		 * Sets up the Featured_Image_Column plugin and loads files at the appropriate time.
		 *
		 * @since 	0.1.6
		 */
		function init() {
			$post_type = get_post_type();
			
			if ( !self::included_post_types( $post_type ) )
				return;
			
			/* Print style */
			add_action( 'admin_enqueue_scripts',					array( $this, 'style' ), 0 );
			
			/* Column manager */
			add_filter( "manage_{$post_type}_posts_columns",		array( $this, 'columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column",	array( $this, 'column_data' ), 10, 2 );	
			
			/**
			 * Sample filter to remove post type
			add_filter( 'featured_image_column_post_types',			array( $this, 'remove_post_type' ), 99 );
			 */
			
			do_action( 'featured_image_column_loaded' );
		}
		
		/**
		 * Sample function to remove featured image from specific post type
		 *
		 * @since 	0.2
		 */
		function remove_post_type( $post_types ) {						
			foreach( $post_types as $key => $post_type ) {
				if ( 'page' === $post_type )
					unset( $post_types[$key] );
			}
			return $post_types;
		}
		
		/**
		 * Enqueue stylesheaet
		 * @since 	0.1
		 */
		function style() {
			wp_register_style( 'featured-image-column', apply_filters( 'featured_image_column_css', plugin_dir_url( __FILE__ ) . 'css/column.css' ), null, self::version );
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
			
			$image_src = self::get_the_image( $post_id );
						
			if ( empty( $image_src ) ) {
				echo "&nbsp;"; // This helps prevent issues with empty cells
				return;
			}
			
			echo '<img alt="' . esc_attr( get_the_title() ) . '" src="' . esc_url( $image_src ) . '" />';
		}
		
		/**
		 * Allowed post types
		 *
		 * @since 	0.2
		 * @ref		http://wordpress.org/support/topic/plugin-featured-image-column-filter-for-post-types?replies=5
		 */
		function included_post_types( $post_type ) {
			$post_types = array();
			
			if ( post_type_supports( 'post', 'thumbnail' ) ) $post_types[] = 'post';
			if ( post_type_supports( 'page', 'thumbnail' ) ) $post_types[] = 'page';
			
			$post_types = apply_filters( 'featured_image_column_post_types', $post_types );
			
			if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
				print_r( $post_types );
			}
			
			if ( in_array( $post_type, $post_types ) )
				return true;
			else
				return false;
		}
		
		/**
		 * Function to get the image
		 *
		 * @since	0.1
		 * @updated	0.1.3 - Added wp_cache_set()
		 * @updated 0.1.9 - fixed persistent cache per post_id
		 *		@ref	http://www.ethitter.com/slides/wcmia-caching-scaling-2012-02-18/#slide-11
		 */
		function get_the_image( $post_id = false ) {			
			$post_id	= (int)$post_id;
			$cache_key	= "featured_image_post_id-{$post_id}-_thumbnail";
			$cache		= wp_cache_get( $cache_key, null );
			
			if ( !is_array( $cache ) )
				$cache = array();
		
			if ( !array_key_exists( $cache_key, $cache ) ) {
				if ( empty( $cache) || !is_string( $cache ) ) {
					$output = '';
						
					if ( has_post_thumbnail( $post_id ) ) {
						$image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), array( 36, 32 ) );
						
						if ( is_array( $image_array ) && is_string( $image_array[0] ) )
							$output = $image_array[0];
					}
					
					if ( empty( $output ) ) {
						$output = plugins_url( 'images/default.png', __FILE__ );
						$output = apply_filters( 'featured_image_column_default_image', $output );
					}
					
					$output = esc_url( $output );
					$cache[$cache_key] = 
					$output;
					
					wp_cache_set( $cache_key, $cache, null, 60 * 60 * 24 /* 24 hours */ );
				}
			}

			return $output;
		}
	}	
	$featured_image_column = new Featured_Image_Column();
};