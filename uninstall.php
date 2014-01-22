<?php
/**
 * Uninstall routines. This file is called automatically when the plugin
 * is deleted per user interface.
 *
 * See http://codex.wordpress.org/Function_Reference/register_uninstall_hook
 */

// Prevent direct access.
defined( 'WP_UNINSTALL_PLUGIN' ) || die();


// ------ Tables ------

/**
 * @var wpdb
 */
global $wpdb;

foreach ( array ( 'mlp_languages', 'multilingual_linked' ) as $table )
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->base_prefix . $table );


// ------ Site options ------

delete_site_option( 'inpsyde_multilingual' );
delete_site_option( 'inpsyde_multilingual_cpt' );
delete_site_option( 'inpsyde_multilingual_quicklink_options' );
delete_site_option( 'mlp_version' );
delete_site_option( 'multilingual_press_check_db' );


// ------ Blog options ------

$sites = wp_get_sites();

if ( empty ( $sites ) )
	return;

foreach ( $sites as $site ) {

	switch_to_blog( $site['blog_id'] );

	delete_option( 'inpsyde_multilingual_blog_relationship' );
	delete_option( 'inpsyde_multilingual_redirect' );
	delete_option( 'inpsyde_multilingual_flag_url' );
	delete_option( 'inpsyde_multilingual_default_actions' );
	delete_option( 'inpsyde_companyname' );
	delete_option( 'inpsyde_license_status_Multilingual Press Pro' );

	restore_current_blog();
}