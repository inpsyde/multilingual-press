<?php
/**
 * Plugin Name: Multilingual Press Free
 * Plugin URI:  http://marketpress.com/product/multilingual-press-pro/?piwik_campaign=mlp&piwik_kwd=free
 * Description: Run WordPress Multisite with multiple languages.
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:     2.1.1
 * Text Domain: multilingualpress
 * Domain Path: /languages
 * License:     GPLv3
 * Network:     true
 */

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'Multilingual_Press' ) )
	require plugin_dir_path( __FILE__ ) . 'inc/Multilingual_Press.php';

// Kick-Off
add_action( 'plugins_loaded', 'mlp_init', 0 );


function mlp_init() {

	global $wp_version, $wpdb, $pagenow;

	$path   = plugin_dir_path( __FILE__ );

	if ( ! class_exists( 'Mlp_Load_Controller' ) )
		require $path . 'inc/autoload/Mlp_Load_Controller.php';

	$loader = new Mlp_Load_Controller( $path . 'inc' );
	$data   = new Inpsyde_Property_List;

	$data->loader           = $loader->get_loader();
	$data->plugin_dir_path  = $path;
	$data->plugin_file_path = __FILE__;
	$data->plugin_base_name = plugin_basename( __FILE__ );
	$data->plugin_url       = plugins_url( '/', __FILE__ );
	$data->css_url          = "{$data->plugin_url}css/";
	$data->js_url           = "{$data->plugin_url}js/";
	$data->image_url        = "{$data->plugin_url}images/";
	$data->flag_url         = "{$data->plugin_url}flags/";
	$data->flag_path        = "{$data->plugin_dir_path}flags/";

	$headers = get_file_data(
		__FILE__,
		array (
			'text_domain_path' => 'Domain Path',
			'plugin_uri'       => 'Plugin URI',
			'plugin_name'      => 'Plugin Name',
			'version'          => 'Version'
		)
	);

	foreach ( $headers as $name => $value )
		$data->$name = $value;

	if ( ! mlp_pre_run_test( $pagenow, $data, $wp_version, $wpdb ) )
		return;

	$mlp = new Multilingual_Press( $data, $wpdb );
	$mlp->setup();
}

/**
 * Check current state of the WordPress installation.
 *
 * @param  string                          $pagenow
 * @param  Inpsyde_Property_List_Interface $data
 * @param  string                          $wp_version
 * @param  wpdb                            $wpdb
 * @return bool
 */
function mlp_pre_run_test( $pagenow, Inpsyde_Property_List_Interface $data, $wp_version, wpdb $wpdb ) {

	$self_check         = new Mlp_Self_Check( __FILE__, $pagenow );
	$requirements_check = $self_check->pre_install_check(
		 $data->plugin_name,
		 $data->plugin_base_name,
		 $wp_version
	);

	if ( Mlp_Self_Check::PLUGIN_DEACTIVATED === $requirements_check )
		return FALSE;

	$data->site_relations = new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' );

	if ( Mlp_Self_Check::INSTALLATION_CONTEXT_OK === $requirements_check ) {

		$deactivator = new Mlp_Network_Plugin_Deactivation();

		if ( 'MultilingualPress Pro' === $data->plugin_name ) {
			$deactivator->deactivate( // remove the free version
						array ( 'multilingual-press/multilingual-press.php' )
			);
		}

		$last_version_option = get_site_option( 'mlp_version' );
		$last_version        = new Mlp_Semantic_Version_Number( $last_version_option );
		$current_version     = new Mlp_Semantic_Version_Number( $data->version );
		$upgrade_check       = $self_check->is_current_version( $current_version, $last_version );
		$updater             = new Mlp_Update_Plugin_Data( $data, $wpdb, $current_version, $last_version );

		if ( Mlp_Self_Check::NEEDS_INSTALLATION === $upgrade_check )
			$updater->install_plugin();

		if ( Mlp_Self_Check::NEEDS_UPGRADE === $upgrade_check )
			$updater->update( $deactivator );
	}

	return TRUE;
}


/**
 * Write debug data to the error log.
 *
 * Add the following linge to your `wp-config.php` to enable this function:
 *
 *     const MULTILINGUALPRESS_DEBUG = TRUE;
 *
 * @param  string $message
 * @return void
 */
function mlp_debug( $message ) {

	if ( ! defined( 'MULTILINGUALPRESS_DEBUG' ) || ! MULTILINGUALPRESS_DEBUG )
		return;

	$date = date( 'H:m:s' );

	error_log( "MultilingualPress: $date $message" );
}


if ( defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG )
	add_action( 'mlp_debug', 'mlp_debug' );