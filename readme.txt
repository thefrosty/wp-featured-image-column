=== Featured Image Column ===
Contributors: austyfrosty, dh-shredder, martythornley, chrisjean,
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XQRHECLPQ46TE
Tags: featured image, admin, column
Requires at least: 6.2
Tested up to: 7.1.0
Stable tag: trunk
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a column to any post type edit screen with the featured image if it exists.

== Description ==

As of version 0.2.2 you can select which post types you'd like to have the image column.
It simply adds a column before the title (far left) the show's the posts featured image if it's supported and exists.

Want to change the default image? Simply filter you own image by using `featured_image_column_default_image`
or filter your own CSS by using the `featured_image_column_css` filter hook.

= Example actions/filters =

**Change thumbnail size**
`
function my_custom_featured_image_size(mixed $size, int $post_id): mixed {
	if (in_array($post_id, [1, 5, 23], true)) {
		return [65. 65];
	}

	return $size;
}
add_filter('featured_image_post_thumbnail_size', 'my_custom_featured_image_size', 10, 2);
`

**Add support for a custom default image**
`
function my_custom_featured_image_column_image(string $image, int $post_id): string {
	if (in_array($post_id, [1, 5, 23], true)) {
		return trailingslashit( get_stylesheet_directory_uri() ) . 'images/featured-image.png';
	}

	return $image;
}
add_filter('featured_image_column_default_image', 'my_custom_featured_image_column_image', 10, 2);
`

**Remove support for post types** *Use the `featured_image_column_init` action hook for your filter.*
`
function frosty_featured_image_column_init_func() {
	add_filter('featured_image_column_post_types', 'frosty_featured_image_column_remove_post_types', 11); // Remove
}
add_action('featured_image_column_init', 'frosty_featured_image_column_init_func');

function frosty_featured_image_column_remove_post_types($post_types) {
	foreach($post_types as $key => $post_type) {
		if ($post_type === 'post-type') // Post type you'd like removed. Ex: 'post' or 'page'
			unset($post_types[$key]);
	}
	return $post_types;
}
`

For more question please visit [https://austin.passy.co](https://austin.passy.co/wordpress-plugins/featured-image-column/)

== Installation ==

Follow the steps below to install the plugin.

1. Upload the `featured-image-column` directory to the /wp-content/plugins/ directory. OR click add new plugin in your WordPress admin.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. Post edit.php screen.

== Changelog ==

= Version 1.3.0 (2026/08/14) =

* New compressed default image: (before) 4.2 K → (after) 235 B
* Add: filter to allow modification of post_thumbnail size `featured_image_post_thumbnail_size` (defaults to [50,50]).
* Add: pass (int) post_id to default image filter as second param.
* Qualifier(s) can be replaced with an import.
* Fix: The (readme) Contributors field should only contain WordPress.org usernames.
* Fix: The (readme) License field is missing. A GPLv2 or later compatible license should be specified.

= Version 1.2.0 (2026/07/24) =

* Tested upto WordPress 7.1.0.
* Added width/height value to `get_the_post_thumbnail`.

= Version 1.1.0 (2024/11/29) =

* Tested upto WordPress 6.7.1.
* Added GitHub Release -> WordPress.org Action.
* Resolve 1.0.0 Release call_user_func errors and incorrect file path(s).

= Version 1.0.0 (2023/11/16) =

* Update code for PHP >= 8.0.
* Update for WordPress >= 6.2.

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

== Upgrade Notice ==

= 1.1.1 =
Required PHP >= 8.0
