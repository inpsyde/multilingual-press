<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: MultilingualPress
 * Plugin URI:  https://wordpress.org/plugins/multilingual-press/
 * Description: Simply <strong>the</strong> multisite-based free open source plugin for your multilingual websites.
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:     3.0.0-dev
 * Text Domain: multilingual-press
 * Domain Path: languages
 * License:     MIT
 * Network:     true
 */

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Factory\TypeFactory;

// TODO: Adapt the following imports:
use \Mlp_Internal_Locations;
use \Mlp_Load_Controller;
use \Mlp_Network_Plugin_Deactivation;
use \Mlp_Plugin_Properties;
use \Mlp_Self_Check;
use \Mlp_Site_Relations;
use \Mlp_Update_Plugin_Data;
use \Multilingual_Press;

defined( 'ABSPATH' ) or die();

if ( is_readable( __DIR__ . '/src/autoload.php' ) ) {
	/**
	 * MultilingualPress autoload file.
	 */
	require_once __DIR__ . '/src/autoload.php';
}

// TODO: Remove as soon as the front controller has been replaced.
if ( ! class_exists( 'Multilingual_Press' ) ) {
	/** @noinspection PhpIncludeInspection */
	require plugin_dir_path( __FILE__ ) . 'src/inc/Multilingual_Press.php';
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap', 0 );

/**
 * Bootstraps the plugin.
 *
 * @since   3.0.0
 * @wp-hook plugins_loaded
 */
function bootstrap() {

	global $pagenow, $wp_version, $wpdb;

	// TODO: Eventually remove the following block.
	if ( ! class_exists( 'Mlp_Load_Controller' ) ) {
		/** @noinspection PhpIncludeInspection */
		require __DIR__ . '/src/inc/autoload/Mlp_Load_Controller.php';
	}

	$plugin_path = plugin_dir_path( __FILE__ );

	$loader = new Mlp_Load_Controller( $plugin_path . 'src/inc' );

	$data = new Mlp_Plugin_Properties();
	$data->set( 'loader', $loader->get_loader() );

	$plugin_url = plugins_url( '/', __FILE__ );

	$locations = new Mlp_Internal_Locations();
	$locations->add_dir( $plugin_path, $plugin_url, 'plugin' );

	$assets_base = 'assets';

	$assets_locations = [
		'css'    => 'css',
		'js'     => 'js',
		'images' => 'images',
		'flags'  => 'images/flags',
	];
	foreach ( $assets_locations as $type => $dir ) {
		$locations->add_dir(
			$plugin_path . $assets_base . '/' . $dir,
			$plugin_url . $assets_base . '/' . $dir,
			$type
		);
	}

	$data->set( 'locations', $locations );
	$data->set( 'plugin_file_path', __FILE__ );
	$data->set( 'plugin_base_name', plugin_basename( __FILE__ ) );

	$headers = get_file_data( __FILE__, [
		'text_domain_path' => 'Domain Path',
		'plugin_uri'       => 'Plugin URI',
		'plugin_name'      => 'Plugin Name',
		'version'          => 'Version',
	] );
	foreach ( $headers as $name => $value ) {
		$data->set( $name, $value );
	}

	// --- PRE_RUN TEST - START
	$type_factory = new TypeFactory();

	$self_check = new Mlp_Self_Check( __FILE__, $pagenow, $type_factory );

	$requirements_check = $self_check->pre_install_check(
		$data->get( 'plugin_name' ),
		$data->get( 'plugin_base_name' ),
		$wp_version
	);

	if ( Mlp_Self_Check::PLUGIN_DEACTIVATED === $requirements_check ) {
		return;
	}

	$data->set( 'type_factory', $type_factory );
	$data->set( 'site_relations', new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' ) );

	if ( Mlp_Self_Check::INSTALLATION_CONTEXT_OK === $requirements_check ) {
		$last_version = $type_factory->create_version_number( [
			get_site_option( 'mlp_version' ),
		] );

		$current_version = $type_factory->create_version_number( [
			$data->get( 'version' ),
		] );

		switch ( $self_check->is_current_version( $current_version, $last_version ) ) {
			case Mlp_Self_Check::NEEDS_INSTALLATION:
				( new Mlp_Update_Plugin_Data( $data, $wpdb, $current_version, $last_version ) )->install_plugin();
				break;

			case Mlp_Self_Check::NEEDS_UPGRADE:
				( new Mlp_Update_Plugin_Data( $data, $wpdb, $current_version, $last_version ) )->update(
					new Mlp_Network_Plugin_Deactivation()
				);
				break;
		}
	}
	// --- PRE_RUN TEST - END

	$mlp = new Multilingual_Press( $data, $wpdb );
	$mlp->setup();
}

/**TODO: Move to functions.php file.
 * Writes debug data to the error log.
 *
 * To enable this function, add the following line to your wp-config.php file:
 *
 *     define( 'MULTILINGUALPRESS_DEBUG', true );
 *
 * @since 3.0.0
 *
 * @param string $message Message to be logged.
 */
function debug( $message ) {

	if ( defined( 'MULTILINGUALPRESS_DEBUG' ) && MULTILINGUALPRESS_DEBUG ) {
		do_action( 'multilingualpress.debug', $message );

		error_log( sprintf( 'MultilingualPress: %s %s', date( 'H:m:s' ), $message ) );
	}
}
