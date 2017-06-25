=== Featured Image Column ===
Contributors: austyfrosty, DH-Shredder, MartyThornley, chrisjean,
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XQRHECLPQ46TE
Tags: featured image, admin, column
Requires at least: 4.4
Tested up to: 4.8
Stable tag: trunk

Adds a column to any post type edit screen with the featured image if it exists.

== Description ==

As of version 0.2.2 you can select which post types you'd like to have the image column.
It simply adds a column before the title (far left) the show's the posts featured image if it's supported and exists.

Want to change the default image? Simply filter you own image by using `featured_image_column_default_image`
or filter your own CSS by using the `featured_image_column_css` filter hook.

= Example actions/filters =

**Add support for a custom default image**
`
function my_custom_featured_image_column_image( $image ) {
	if ( !has_post_thumbnail() ) {
		return trailingslashit( get_stylesheet_directory_uri() ) . 'images/featured-image.png';
	}

	return $image;
}
add_filter( 'featured_image_column_default_image', 'my_custom_featured_image_column_image' );
`

**Remove support for post types** *Use the `featured_image_column_init` action hook for your filter.*
`
function frosty_featured_image_column_init_func() {
	add_filter( 'featured_image_column_post_types', 'frosty_featured_image_column_remove_post_types', 11 ); // Remove
}
add_action( 'featured_image_column_init', 'frosty_featured_image_column_init_func' );

function frosty_featured_image_column_remove_post_types( $post_types ) {
	foreach( $post_types as $key => $post_type ) {
		if ( 'post-type' === $post_type ) // Post type you'd like removed. Ex: 'post' or 'page'
			unset( $post_types[$key] );
	}
	return $post_types;
}
`

**Add your own CSS to change the size of the image.**
`
/**
 * @use '.featured-image.column-featured-image img {}'
 */
function my_custom_featured_image_css() {
	return trailingslashit( get_stylesheet_directory_uri() ) . 'css/featured-image.css'; //URL to your css
}
add_filter( 'featured_image_column_css', 'my_custom_featured_image_css' );
`

For more question please visit [http://austin.passy.co](http://austin.passy.co/wordpress-plugins/featured-image-column/)

== Installation ==

Follow the steps below to install the plugin.

1. Upload the `featured-image-column` directory to the /wp-content/plugins/ directory. OR click add new plugin in your WordPress admin.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. Post edit.php screen.

== Changelog ==

= Version 0.3.2 (06/26/17) =

* Missed short-array syntax updates for PHP < 5.4 compatibility.

= Version 0.3.1 (06/26/17) =

* Fix for PHP versions < 5.4.
* Please update your PHP versions!

= Version 0.3 (06/25/17) =

* Code cleanup.
* Tested with WordPress 4.8 new minimum version requirement set to 4.4.
* Image columns work correctly when using Quick Edit now (no more collapsing)!
* Removed use of additional wp_cache.s
* Toggling setting controls for post types works again (turn on/off featured image column per post type).

= Version 0.2.3 (04/4/15) =

* Make sure get_the_image() returns the cached image. /ht Djules

= Version 0.2.2 (12/3/14) =

* Wow. Exactly one year to the dau since the last update!
* Added settings to turn on/off featured image column per post type.
* Added pre load hook `featured_image_column_init`.
* Better custom post type column manager filter.

= Version 0.2.1 (12/3/13) =

* Fixed custom post type support.

= Version 0.2.0 (12/2/13) =

* Updated version to WordPress 3.8 compatable and PHP 5.3+
* Added new filter `featured_image_column_post_types` for post type support (add/remove).
* Removed closing PHP.

= Version 0.1.10 (9/5/13) =

* Fixed `PHP Deprecated:  Assigning the return value of new by reference is deprecated in /featured-image-column/featured-image-column.php on line 157`.

= Version 0.1.9 (3/11/12) =

* Fixed repeat images per posts.
* Added filter to style sheet, (use your own CSS to make the thumbnail bigger).

= Version 0.1.8 (2/16/12) =

* Updated `wp_cache_set/get`

= Version 0.1.7 (1/18/12) =

* Tried to update some code to fix repeated images.

= Version 0.1.6 (11/21/11) =

* Code edits by Chris Jean of ithemes.com.

= Version 0.1.5 (10/18/11) =

* Fixed latest post image showing up across all posts.
* Reset the query check.

= Version 0.1.4 (10/17/11) =

* Added filter for `post_type`'s, thanks to [Bill Erickson](http://wordpress.org/support/topic/plugin-featured-image-column-filter-for-post-types?replies=1)
* Fixed error when zero posts exists (very rare).

= Version 0.1.3 (10/17/11) =

* Added a light caching script for the images.
* Updated a contributors .org profile name.

= Version 0.1.2 (9/30/11) =

* Removed PHP4 constructor.
* TODO: Fix error when no posts exists.

= Version 0.1.1 (9/20/11) =

* Add support for public `$post_type`'s that support `'post-thumbnails'`.

= Version 0.1 (9/14/11) =

* Initial release.

== Upgrade Notice ==

= 0.3 =
Code cleanup, compatibility updates for WordPress 4.8, quick edit no longer collapses the columns and the settings actually toggle post_types on/off!

= 0.2.2 =
Happy holidays! If you like the updates please consider donating. PayPal: austin@thefrosty.com. WP 4.1 and post type settings.

= 0.2.1 =
Happy holidays! If you like the updates please consider donating. PayPal: austin@thefrosty.com. WP 3.8 and PHP 5.3+ compat.

= 0.1.9 =
Happy 311 day! Finally fixed duplicate image output. Yay for cache array().

= 0.1.7 =
Working on fixed repeating images.

= 0.1.6 =
Cleaned up code thanks to Chris @ iThemes. All errors should be suppressed and clear.

= 0.1.2 =
Code cleanup, attempt at fixing a fatal error when no posts exist (still in progress).

= 0.1.1 =
Adds support for public $post_type's that support 'post-thumbnails'.