=== Featured Image Column ===
Contributors: austyfrosty, DH-Shredder, MartyThornley, chrisjean,
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XQRHECLPQ46TE
Tags: featured image, admin, column
Requires at least: 3.0
Tested up to: 3.3
Stable tag: trunk

Adds a column to the edit screen with the featured image if it exists.

== Description ==

This plugin has no options. It simply adds a column before the title (far left) the show's the posts featured image if it's supported and/or exists.

Add a defualt image simply by filtering you own image in. Use `featured_image_column_default_image` or filter your own post_type by using `featured_image_column_post_types`.

**Add support for a custom default image**

`
function my_custom_featured_image_column_image( $image ) {
	if ( !has_post_thumbnail() )
		return trailingslashit( get_stylesheet_directory_uri() ) . 'images/no-featured-image';
}
add_filter( 'featured_image_column_default_image', 'my_custom_featured_image_column_image' );
`

**Add support for a certain `post_type`.**

`
function my_custom_featured_image_column_type( $post_types ) {
	return 'gallery'; //$post_type name
}
add_filter( 'featured_image_column_post_types', 'my_custom_featured_image_column_type' );
`

For question please visit my blog @ [http://austinpassy.com](http://austinpassy.com/wordpress-plugins/featured-image-column/)

== Installation ==

Follow the steps below to install the plugin.

1. Upload the `featured-image-column` directory to the /wp-content/plugins/ directory. OR click add new plugin in your WordPress admin.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Nothing yet =

== Screenshots ==

1. Post edit.php screen.

== Changelog ==

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

= 0.1.6 =

* Cleaned up code thanks to Chris @ iThemes. All errors should be suppressed and clear.

= 0.1.2 =

* Code cleanup, attempt at fixing a fatal error when no posts exist (still in progress).

= 0.1.1 =

* Adds support for public $post_type's that support 'post-thumbnails'.