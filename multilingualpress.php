<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: MultilingualPress
 * Plugin URI:  https://wordpress.org/plugins/multilingualpress/
 * Description: The multisite-based free open source plugin for your multilingual WordPress websites.
 * Author:      Inpsyde GmbH
 * Author URI:  https://inpsyde.com
 * Version:     3.0.0-dev
 * Text Domain: multilingualpress
 * License:     MIT
 * Network:     true
 */

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\API\APIServiceProvider;
use Inpsyde\MultilingualPress\Asset\AssetServiceProvider;
use Inpsyde\MultilingualPress\Core\CoreServiceProvider;
use Inpsyde\MultilingualPress\Core\ImmutablePluginProperties;
use Inpsyde\MultilingualPress\Database\DatabaseServiceProvider;
use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Factory\FactoryProvider;
use Inpsyde\MultilingualPress\Installation\InstallationServiceProvider;
use Inpsyde\MultilingualPress\Integration\IntegrationProvider;
use Inpsyde\MultilingualPress\Module;
use Inpsyde\MultilingualPress\NavMenu\NavMenuServiceProvider;
use Inpsyde\MultilingualPress\Relations\RelationsServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\DistinctServiceProviderCollection;
use Inpsyde\MultilingualPress\Service\ServiceProviderCollection;
use Inpsyde\MultilingualPress\SiteDuplication\SiteDuplicationServiceProvider;
use Inpsyde\MultilingualPress\Translation\TranslationServiceProvider;
use Inpsyde\MultilingualPress\Widget\WidgetServiceProvider;

defined( 'ABSPATH' ) or die();

/**
 * Action name.
 *
 * @since 3.0.0
 *
 * @var string
 */
const ACTION_ACTIVATION = 'multilingualpress.activation';

/**
 * Action name.
 *
 * @since 3.0.0
 *
 * @var string
 */
const ACTION_ADD_SERVICE_PROVIDERS = 'multilingualpress.add_service_providers';

if ( ! class_exists( MultilingualPress::class ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	/**
	 * MultilingualPress autoload file.
	 */
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Bootstraps MultilingualPress.
 *
 * @since   3.0.0
 * @wp-hook plugins_loaded
 *
 * @return bool Whether or not MultilingualPress was bootstrapped successfully.
 */
function bootstrap(): bool {

	/** @var Container $container */
	$container = resolve( null );
	$container->share( 'multilingualpress.properties', new ImmutablePluginProperties( __FILE__ ) );

	$providers = new DistinctServiceProviderCollection();
	$providers
		->add_service_provider( new CoreServiceProvider() )
		->add_service_provider( new APIServiceProvider() )
		->add_service_provider( new AssetServiceProvider() )
		->add_service_provider( new DatabaseServiceProvider() )
		->add_service_provider( new FactoryProvider() )
		->add_service_provider( new InstallationServiceProvider() )
		->add_service_provider( new IntegrationProvider() )
		->add_service_provider( new Module\AlternativeLanguageTitleInAdminBar\ServiceProvider() )
		->add_service_provider( new Module\CustomPostTypeSupport\ServiceProvider() )
		->add_service_provider( new Module\Quicklinks\ServiceProvider() )
		->add_service_provider( new Module\Redirect\ServiceProvider() )
		->add_service_provider( new Module\Trasher\ServiceProvider() )
		->add_service_provider( new NavMenuServiceProvider() )
		->add_service_provider( new RelationsServiceProvider() )
		->add_service_provider( new SiteDuplicationServiceProvider() )
		->add_service_provider( new TranslationServiceProvider() )
		->add_service_provider( new WidgetServiceProvider() );

	$multilingualpress = new MultilingualPress( $container, $providers );

	/**
	 * Fires right before MultilingualPress gets bootstrapped.
	 *
	 * Hook here to add custom service providers via `ServiceProviderCollection::add_service_provider()`.
	 *
	 * @since 3.0.0
	 *
	 * @param ServiceProviderCollection $providers Service provider collection instance.
	 */
	do_action( ACTION_ADD_SERVICE_PROVIDERS, $providers );

	$bootstrapped = $multilingualpress->bootstrap();

	unset( $providers );

	return $bootstrapped;
}

/**
 * Triggers a plugin-specific activation action third parties can listen to.
 *
 * @since   3.0.0
 * @wp-hook activate_{$plugin}
 *
 * @return void
 */
function activate() {

	/**
	 * Fires when MultilingualPress is about to be activated.
	 *
	 * @since 3.0.0
	 */
	do_action( ACTION_ACTIVATION );

	add_action( 'activated_plugin', function ( $plugin ) {

		if ( plugin_basename( __FILE__ ) === $plugin ) {
			// Bootstrap MultilingualPress right now to take care of installation or upgrade routines.
			bootstrap();
		}
	}, 0 );
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap', 0 );

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );

// TODO: Eventually remove/refactor according to new architecture as soon as the old controller got replaced.
add_action( MultilingualPress::ACTION_BOOTSTRAPPED, function () {

	add_action( 'wp_loaded', function () {

		new \Mlp_Language_Manager_Controller(
			new \Mlp_Language_Db_Access( resolve( 'multilingualpress.languages_table', Table::class )->name() ),
			resolve( 'multilingualpress.wpdb', \wpdb::class )
		);
	} );
} );
