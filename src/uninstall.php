<?php
/**
 * Uninstall routines. This file is called automatically when the plugin
 * is deleted per user interface.
 *
 * See http://codex.wordpress.org/Function_Reference/register_uninstall_hook
 */

// Prevent direct access.
defined( 'WP_UNINSTALL_PLUGIN' ) || die();

// We don't do anything on single sites anyway.
if ( ! is_multisite() )
	return;


// check if the "pro"-version is available and activated
if ( function_exists( 'mlp_pro_init' ) ) {
	return;
}

// check if the "free"-version is available and activated
if ( function_exists( 'mlp_init' ) ) {
	return;
}


// getting all available plugins
$plugins = get_plugins();
$check   = '';

if ( WP_UNINSTALL_PLUGIN === 'multilingual-press/multilingual-press.php' ) {
	// checking if the pro is available (not active) when the free is uninstalled
	if ( array_key_exists( 'multilingual-press-pro/multilingual-press.php', $plugins ) )
		return;
}
else if ( WP_UNINSTALL_PLUGIN === 'multilingual-press-pro/multilingual-press.php' ) {
	// checking if the free is available (not active) when the pro is uninstalled
	if ( array_key_exists( 'multilingual-press/multilingual-press.php', $plugins ) )
		return;
}


// ------ Tables ------
/**
 * @var wpdb
 */
global $wpdb;

foreach ( array ( 'mlp_languages', 'multilingual_linked', 'mlp_site_relations' ) as $table )
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->base_prefix . $table );


// ------ Site options ------

delete_site_option( 'inpsyde_multilingual' );
delete_site_option( 'inpsyde_multilingual_cpt' );
delete_site_option( 'inpsyde_multilingual_quicklink_options' );
delete_site_option( 'state_modules' );
delete_site_option( 'mlp_version' );
delete_site_option( 'multilingual_press_check_db' );


// ------ Blog options ------

// TODO: With WordPress 4.6 + 2, just use `get_sites()` and `$site->id`.
// Get the unaltered WordPress version.
require ABSPATH . WPINC . '/version.php';
/** @var string $wp_version */
$is_pre_4_6 = version_compare( $wp_version, '4.6-RC1', '<' );

$sites = $is_pre_4_6 ? wp_get_sites() : get_sites();
foreach ( $sites as $site ) {
	switch_to_blog( $is_pre_4_6 ? $site['blog_id'] : $site->id );

	delete_option( 'inpsyde_multilingual_blog_relationship' );
	delete_option( 'inpsyde_multilingual_redirect' );
	delete_option( 'inpsyde_multilingual_flag_url' );
	delete_option( 'inpsyde_multilingual_default_actions' );
	delete_option( 'inpsyde_license_status_MultilingualPress Pro' );

	restore_current_blog();
}
