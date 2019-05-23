<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: MultilingualPress
 * Plugin URI:  https://wordpress.org/plugins/multilingual-press/
 * Description: Create a fast translation network on WordPress multisite. Run each language in a separate site, and connect the content in a lightweight user interface. Use a customizable widget to link to all sites.
 * Author:      Inpsyde GmbH
 * Author URI:  https://inpsyde.com
 * Version:     2.11.3
 * Text Domain: multilingual-press
 * Domain Path: /languages
 * License:     GPLv3
 * Network:     true
 */

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Multilingual_Press' ) ) {
	require plugin_dir_path( __FILE__ ) . 'inc/Multilingual_Press.php';
}

// Kick-Off
add_action( 'plugins_loaded', 'mlp_init', 0 );

/**
 * Initialize the plugin.
 *
 * @wp-hook plugins_loaded
 *
 * @return void
 */
function mlp_init() {

	global $pagenow, $wp_version, $wpdb;

	$plugin_path = plugin_dir_path( __FILE__ );
	$plugin_url = plugins_url( '/', __FILE__ );

	$assets_base = 'assets';

	if ( ! class_exists( 'Mlp_Load_Controller' ) ) {
		require $plugin_path . 'inc/autoload/Mlp_Load_Controller.php';
	}

	$loader = new Mlp_Load_Controller( $plugin_path . 'inc' );

	$data = new Mlp_Plugin_Properties();

	$data->set( 'loader',$loader->get_loader() );

	$locations = new Mlp_Internal_Locations();
	$locations->add_dir( $plugin_path, $plugin_url, 'plugin' );
	$assets_locations = array(
		'css'    => 'css',
		'js'     => 'js',
		'images' => 'images',
		'flags'  => 'images/flags',
	);
	foreach ( $assets_locations as $type => $dir ) {
		$locations->add_dir(
			$plugin_path . $assets_base . '/' . $dir,
			$plugin_url . $assets_base . '/' . $dir,
			$type
		);
	}
	$data->set( 'locations',$locations );

	$data->set( 'plugin_file_path', __FILE__ );
	$data->set( 'plugin_base_name', plugin_basename( __FILE__ ) );

	$headers = get_file_data(
		__FILE__,
		array(
			'text_domain_path' => 'Domain Path',
			'plugin_uri'       => 'Plugin URI',
			'plugin_name'      => 'Plugin Name',
			'version'          => 'Version',
		)
	);
	foreach ( $headers as $name => $value ) {
		$data->set( $name, $value );
	}

	if ( ! mlp_pre_run_test( $pagenow, $data, $wp_version, $wpdb ) ) {
		return;
	}

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
 *
 * @return bool
 */
function mlp_pre_run_test( $pagenow, Inpsyde_Property_List_Interface $data, $wp_version, wpdb $wpdb ) {

	$self_check = new Mlp_Self_Check( __FILE__, $pagenow );
	$requirements_check = $self_check->pre_install_check(
		$data->get( 'plugin_name' ),
		$data->get( 'plugin_base_name' ),
		$wp_version
	);

	if ( Mlp_Self_Check::PLUGIN_DEACTIVATED === $requirements_check ) {
		return false;
	}

	$data->set( 'site_relations', new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' ) );

	if ( Mlp_Self_Check::INSTALLATION_CONTEXT_OK === $requirements_check ) {

		$deactivator = new Mlp_Network_Plugin_Deactivation();

		$last_version_option = get_site_option( 'mlp_version' );
		$last_version = Mlp_Semantic_Version_Number_Factory::create( $last_version_option );
		$current_version = Mlp_Semantic_Version_Number_Factory::create( $data->get( 'version' ) );
		$upgrade_check = $self_check->is_current_version( $current_version, $last_version );
		$updater = new Mlp_Update_Plugin_Data( $data, $wpdb, $current_version, $last_version );

		if ( Mlp_Self_Check::NEEDS_INSTALLATION === $upgrade_check ) {
			$updater->install_plugin();
		}

		if ( Mlp_Self_Check::NEEDS_UPGRADE === $upgrade_check ) {
			$updater->update( $deactivator );
		}
	}

	return true;
}

/**
 * Write debug data to the error log.
 *
 * Add the following linge to your `wp-config.php` to enable this function:
 *
 *     const MULTILINGUALPRESS_DEBUG = true;
 *
 * @param string $message
 *
 * @return void
 */
function mlp_debug( $message ) {

	if ( ! defined( 'MULTILINGUALPRESS_DEBUG' ) || ! MULTILINGUALPRESS_DEBUG ) {
		return;
	}

	$date = date( 'H:m:s' );

	// @codingStandardsIgnoreLine as this is a function specifically used when debugging.
	error_log( "MultilingualPress: $date $message" );
}

if ( defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG ) {
	add_action( 'mlp_debug', 'mlp_debug' );
}

/**
 * Triggers the plugin initialization routine on activation.
 *
 * @since   2.9.0
 * @wp-hook activated_plugin
 *
 * @param string $plugin Plugin file path.
 *
 * @return void
 */
function mlp_init_on_activation( $plugin ) {

	$mlp_plugin_file = defined( 'MLP_PLUGIN_FILE' ) ? MLP_PLUGIN_FILE : __FILE__;
	if ( plugin_basename( $mlp_plugin_file ) === $plugin ) {
		add_filter( 'multilingualpress.force_system_check', '__return_true' );

		// Bootstrap MultilingualPress right now to take care of installation or upgrade routines.
		mlp_init();
	}
}

add_action( 'activated_plugin', 'mlp_init_on_activation', 0 );
