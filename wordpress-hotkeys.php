<?php
/**
 * Plugin Name: WordPress Hotkeys
 * Plugin URI:  http://mightyminnow.com
 * Description: Provides hotkeys to navigate the WordPress dashboard.
 * Version:     1.0.0
 * Author:      MIGHTYminnow
 * Author URI:  http://mightyminnow.com
 * License:     GPLv2+
 */

/**
 * TODO
 *
 * Fix comments and other top-level items with no sub-items
 * Fix plugins (2) and better solution for update including number
 * Make sure this only works outside of inputs, etc
 */

// Definitions
define( 'WH_PLUGIN_NAME', 'WordPress Hotkeys' );

// Includes
require_once dirname( __FILE__ ) . '/lib/admin/admin.php';

/**
 * Loads text domain for internationalization
 *
 * @package WordPress Hotkeys
 * @since   1.0.0
 */
function wh_init() {

    // Load plugin text domain
    load_plugin_textdomain( 'wh', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}
add_action( 'plugins_init', 'wh_init' );

/**
 * Enqueues required scripts & styles, and passes PHP variables to jQuery file
 *
 * @package WordPress Hotkeys
 * @since   1.0.0
 */
function wh_admin_scripts() {
    
    // Admin menu items
    global $menu, $submenu;

	// Include WP jQuery hotkeys functionality
	wp_enqueue_script( 'jquery-hotkeys' );

	// Include WH jQuery
    wp_enqueue_script( 'wordpress-hotkeys', plugins_url( '/lib/js/wordpress-hotkeys.js', __FILE__ ), array( 'jquery' ), '1.0.0', false );


    /*----------------------------------------------------------------------------*
     * Generate list of admin menu items
     *----------------------------------------------------------------------------*/
    
    // Setup menu array
	$menu_items = array();

	// Top level menu items
	foreach ( $menu as $item ) {

		$top_name = $item[0];
		$top_url = $item[2];

		// Sub menu items
		foreach ( $submenu as $parent_url => $sub_item ) {
		
			if ( $top_url == $parent_url ) {

				foreach ( $sub_item as $item ) {
					
					$sub_name = $item[0];
					$sub_url = $item[2];

					// Fix for Dashboard > Updates to not include update number in key
					if ( FALSE !== strpos( $top_name, 'Dashboard') && FALSE !== strpos( $sub_name, 'Update') )
						$sub_name = 'Updates';

					$menu_items[ $top_name ][ $sub_name ] = $sub_url;

				}

			}

		}

	}

	/*----------------------------------------------------------------------------*
	 * Hotkey behavior
	 *----------------------------------------------------------------------------*/
	$update_data = wp_get_update_data();

	// Setup hotkey array
	$hotkeys = array(

		// Dashboard
		'd'         => $menu_items['Dashboard']['Home'],
		'u'         => $menu_items['Dashboard']['Updates'],

		// Posts
		'p'         => $menu_items['Posts']['All Posts'],
		'shift+p'   => $menu_items['Posts']['Add New'],

		// Media
		'm'         => $menu_items['Media']['Library'],
		'shift+m'   => $menu_items['Media']['Add New'],

		// Pages
		'g'         => $menu_items['Pages']['All Pages'],
		'shift+g'   => $menu_items['Pages']['Add New'],

		// Comments
		'c'         => $menu_items['Comments']['Library'],
		'shift+m'   => $menu_items['Media']['Add New'],

	);

	// Pass menu items to jQuery
	wp_localize_script( 'wordpress-hotkeys', 'phpHotkeys', $hotkeys );

	// Pass admin URL to jQuery
	wp_localize_script( 'wordpress-hotkeys', 'phpAdminUrl', get_admin_url() );
}
add_action( 'admin_enqueue_scripts', 'wh_admin_scripts' );