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

use Inpsyde\MultilingualPress\Core\ImmutablePluginProperties;
use Inpsyde\MultilingualPress\Factory\FactoryProvider;
use Inpsyde\MultilingualPress\Service\AddOnlyContainer;

defined( 'ABSPATH' ) or die();

if ( is_readable( __DIR__ . '/src/autoload.php' ) ) {
	/**
	 * MultilingualPress autoload file.
	 */
	require_once __DIR__ . '/src/autoload.php';
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap', 0 );

/**
 * Bootstraps MultilingualPress.
 *
 * @since   3.0.0
 * @wp-hook plugins_loaded
 *
 * @return bool Whether or not MultilingualPress was bootstrapped successfully.
 */
function bootstrap() {

	$container = new AddOnlyContainer();
	$container->share( 'multilingualpress.properties', new ImmutablePluginProperties( __FILE__ ) );

	$multilingualpress = new MultilingualPress( $container );
	$multilingualpress
		->register_service_provider( new FactoryProvider() );

	/**
	 * MultilingualPress functions.
	 */
	require_once __DIR__ . '/src/functions.php';

	return $multilingualpress->bootstrap();
}
