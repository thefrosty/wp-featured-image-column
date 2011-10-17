<?php
/**
 * Plugin Name: Featured Image Column
 * Plugin URI: http://austinpassy.com/wordpress-plugins/featured-image-column
 * Description: 
 * Version: 0.1.3
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
		const version	= '0.1.3';
		
		/**
		 * Sets up the Featured_Image_Column plugin and loads files at the appropriate time.
		 *
		 * @since 0.1
		 */
		function __construct() {	
			$post_type = ( !empty( $_GET['post_type'] ) ) ? $_GET['post_type'] : 'post';
			
			/* Define constants */
			add_action( 'plugins_loaded', 	array( __CLASS__, 'constants' ) );
			
			add_action( 'admin_init', 		array( __CLASS__, 'localize' ) );
			
			add_action( 'init', 			array( __CLASS__, 'add_theme_support' ) );
			
			/* Print style */
			add_action( 'admin_head', 		array( __CLASS__, 'style' ) );
			
			/* Column manager */
			add_filter( 'manage_pages_columns', 					array( __CLASS__, 'columns' ), 10, 2 );
			add_filter( 'manage_posts_columns', 					array( __CLASS__, 'columns' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_columns",			array( __CLASS__, 'columns' ), 10, 2 );
			add_action( "manage_{$post_type}_posts_custom_column",	array( __CLASS__, 'column_data' ), 10, 2 );
			
			/* Prints pointer javascripts */
			add_action( 'admin_enqueue_scripts',					array( __CLASS__, 'pointer' ) );
		
			do_action( 'featured_image_column_loaded' );
		}
		
		function constants() {		
			/* Set constant path to the plugin directory. */
			define( 'FEATURED_IMAGE_COLUMN_DIR',	plugin_dir_path( __FILE__ ) );
			define( 'FEATURED_IMAGE_COLUMN_ADMIN',	trailingslashit( FEATURED_IMAGE_COLUMN_DIR ) . 'admin/' );
		
			/* Set constant path to the plugin URL. */
			define( 'FEATURED_IMAGE_COLUMN_URL',	plugin_dir_url( __FILE__ ) );
			define( 'FEATURED_IMAGE_COLUMN_IMAGES',	FEATURED_IMAGE_COLUMN_URL . 'images/' );
			define( 'FEATURED_IMAGE_COLUMN_CSS',	FEATURED_IMAGE_COLUMN_URL . 'css/' );
			define( 'FEATURED_IMAGE_COLUMN_JS',		FEATURED_IMAGE_COLUMN_URL . 'js/' );
		}
		
		function localize() {
			load_plugin_textdomain( self::domain, null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		
		/**
		 * WordPress version check
		 *
		 * @since	0.1.1
		 * @since	09/19/2011
		 */
		function is_version( $version = '3.3' ) {
			global $wp_version;
			
			if ( version_compare( $wp_version, $version, '<' ) ) {
				return false;
			}
			return true;
		}

		/**
		 * Add stylesheet
		 * @since 0.1
		 */
		function style() { 
			global $pagenow;
			
			if ( $pagenow == 'edit.php' ) { ?>
				<style type="text/css">
                th#featured-image,
                td.featured-image.column-featured-image {
					max-height: 50px;
					max-width: 50px;
                    width: 50px;
                }
				.featured-image.column-featured-image img {
					max-height: 32px;
					max-width: 36px;
					-ms-interpolation-mode: bicubic;
				}
                </style><?php
			}
		}
		
		/**
		 * Filter the image in before the 'title'
		 *
		 * @todo fix error if no posts exist.
		 * 			For some reason returning before the 'foreach' 
		 *			still triggers the 'foreach', which is causing
		 *			a fatal error when WP_DEBUG is true..
		 */
		function columns( $columns, $post_type = 'post' ) {
			$post_type = get_post_type();
			
			if ( !post_type_supports( $post_type, 'thumbnail' ) )
				return;
				
			if ( empty( $columns ) || !$columns )
				return;
				
			$new = array();
			
			foreach( $columns as $key => $title ) {
				if ( $key == 'title' ) // Put the Thumbnail column before the Title column
					$new['featured-image'] = __( 'Image', self::domain );
				$new[$key] = $title;
			}
			return $new;
		}
		
		function column_data( $column_name, $post_id ) {
			
			$post_id   = ( !empty( $post_id ) ) ? $post_id : get_the_id();
			$post_type = get_post_type();
			
			if ( post_type_supports( $post_type, 'thumbnail' ) && 'featured-image' == $column_name ) {
				echo '<img alt="' . get_the_title() . '" src="' . self::get_the_image( $post_id ) . '" />';
			}
		}
		
		/**
		 * Add theme support for post-thumbnails
		 * on pages if the feature isn't available
		 *
		 * @since	0.1.1 09/20/2011
		 */
		function add_theme_support() {
			if ( !post_type_supports( 'page', 'thumbnail' ) )
				add_theme_support( 'post-thumbnails', array( 'post', 'page' ) );
		}
		
		/**
		 * Add custom image size
		 *
		 * @since	0.1
		 * @deprecated 0.1.1
		 */
		function add_image_size() {
			_deprecated_function( __FUNCTION__, '0.1.1', '' );
				return;
				
			add_image_size( 'featured-column-thumbnail', 32, 32, true ); 
		}
		
		/**
		 * Function to get the image
		 *
		 * @since	0.1
		 * @updated	0.1.3 - Added wp_cache_set()
		 */
		function get_the_image( $post_id ) {
			$post_id = ( !empty( $post_id ) ) ? $post_id : get_the_id();			

			$image = '';
			$image = wp_cache_get( 'featured_column_thumbnail' );				
			
			if ( false == $image ) {
				if ( has_post_thumbnail() ) {	
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), array( 36, 32 ) );
					$image = esc_url( $image[0] );	
				} else {				
					$image = esc_url( plugins_url( 'images/default.png', __FILE__ ) );				
				}
				if ( !is_wp_error( $image ) )
					wp_cache_set( 'featured_column_thumbnail', $image, null, 60*60*24 );
			}
			return apply_filters( 'featured_image_column_default_image', $image ); 
		}
		
		/**
		 * In version 3.3 this will show if the user hasn't 
		 * set page to support 'thumbnails' which is false
		 * by deafult
		 *
		 * @since	0.1.1
		 */
		function pointer() {
			
			if ( self::is_version( '3.3.0' ) && post_type_supports( 'page', 'thumbnail' ) )
				return;
			
			$page = get_user_setting( 'featured_image_page_pointer', 0 );
			if ( !$page ) {
				wp_enqueue_style( 'wp-pointer' ); 
				wp_enqueue_script( array( 'wp-pointer', 'utils' ) );
				add_action( 'admin_print_footer_scripts', array( __CLASS__, 'pointer_scripts' ) );
			}
		}
		
		/**
		 * Returns the pointer content
		 */
		function pointer_scripts() {
			$pointer_content  = __( '<h3>Thanks for installing Featured Image Gallery!</h3>' );
			$pointer_content .= __( '<p>It looks like you haven\'t set your pages to support <code>thumbnail</code>\'s <br />' );
			?>
		<script type="text/javascript"> 
		//<![CDATA[ 
		jQuery(document).ready( function($) {
			$('#menu-pages').pointer({ 
				content: '<?php echo $pointer_content; ?>', 
				position: {
					my: 'right top', 
					at: 'left top', 
					offset: '0 -2'
				},
				arrow: {
					edge: 'left',
					align: 'top',
					offset: 10
				},
				close: function() { 
					setUserSetting( 'featured_image_page_pointer', '1' ); 
				} 
			}).pointer('open'); 
		}); 
		//]]> 
		</script><?php
		}
		
	}
};

$featured_image_column = new Featured_Image_Column; 
			
?>