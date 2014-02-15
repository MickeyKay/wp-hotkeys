<?php
/**
 * Uninstall functionality
 *
 * @package WP Hotkeys
 * @since   0.9.0
 */

// Exit if called directly
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

$option_name = 'plugin_option_name';

// For Single site
if ( !is_multisite() ) 
{
    delete_option( 'wh-options' );
}

// For Multisite
else {
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();
    
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        delete_site_option( 'wh-options' );  
    }
    
    switch_to_blog( $original_blog_id );
}