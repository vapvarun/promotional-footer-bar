<?php
/**
 * Uninstall Promotional Footer Bar
 *
 * Removes all plugin data when the plugin is deleted.
 *
 * @package Promotional_Footer_Bar
 */

// Exit if accessed directly or not during uninstallation.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin options.
delete_option( 'pfb_notifications' );

// Remove transient cache.
delete_transient( 'pfb_enabled_notifications' );

// For multisite, clean up each site.
if ( is_multisite() ) {
	global $wpdb;

	// Get all blog IDs.
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );

		// Remove options for this site.
		delete_option( 'pfb_notifications' );
		delete_transient( 'pfb_enabled_notifications' );

		restore_current_blog();
	}
}
