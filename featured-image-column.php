<?php

/**
 * Plugin Name: Featured Image Column
 * Plugin URI: https://austin.passy.co/wordpress-plugins/featured-image-column
 * Description: Adds a column to the edit screen with the featured image if it exists.
 * Version: 1.1.0
 * Author: Austin Passy
 * Author URI: httsp://austin.passy.co
 * Requires at least: 6.2
 * Tested up to: 6.7.1
 * Requires PHP: 8.0
 * @copyright 2009 - 2025
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

use TheFrosty\FeatureImageColumn;

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

add_action('_admin_menu', function (): void {
    require_once __DIR__ . '/src/FeatureImageColumn.php';
    (new FeatureImageColumn(__FILE__))->addHooks();
});

register_activation_hook(__FILE__, static function (): void {
    require_once __DIR__ . '/src/FeatureImageColumn.php';
    (new FeatureImageColumn(__FILE__))->activationHook();
});
