<?php
/**
 * @package   WP_Advanced_Gallery
 * @author    WPMount
 * @license   GPL-2.0+
 * @link      http://wpmount.com
 * @copyright 2016 WPMount
 *
 * @wordpress-plugin
 * Plugin Name:       WP Advanced Gallery
 * Plugin URI:        http://wpmount.com/plugin/wp-advanced-gallery
 * Description:       Enhanced gallery for WordPress with the following: Magnific Popup, Isotope / Masonry, Owl-Carousel
 * Version:           1.0.0
 * Author:            WPMount
 * Author URI:        http://wpmount.com
 * Text Domain:       wp-advanced-gallery
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/*----------------------------------------------------------------------------*
 * * * ATTENTION! * * *
 * FOR DEVELOPMENT ONLY
 * SHOULD BE DISABLED ON PRODUCTION
 *----------------------------------------------------------------------------*/
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
/*----------------------------------------------------------------------------*/



/*----------------------------------------------------------------------------*
 * Main Class
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'class-wp-advanced-gallery.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'WP_Advanced_Gallery', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Advanced_Gallery', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WP_Advanced_Gallery', 'get_instance' ) );



