<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: MultilingualPress
 * Plugin URI:  https://wordpress.org/plugins/multilingual-press/
 * Description: The multisite-based free open source plugin for your multilingual WordPress websites.
 * Author:      Inpsyde GmbH
 * Author URI:  https://inpsyde.com
 * Version:     3.0.0-dev
 * Text Domain: multilingual-press
 * License:     MIT
 * Network:     true
 */

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\API\APIServiceProvider;
use Inpsyde\MultilingualPress\API\ContentRelations;
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
use Inpsyde\MultilingualPress\Service\AddOnlyContainer;
use Inpsyde\MultilingualPress\Service\DistinctServiceProviderCollection;
use Inpsyde\MultilingualPress\Service\ServiceProviderCollection;
use Inpsyde\MultilingualPress\SiteDuplication\SiteDuplicationServiceProvider;
use Inpsyde\MultilingualPress\Translation\TranslationServiceProvider;

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

if ( is_readable( __DIR__ . '/src/autoload.php' ) ) {
	/**
	 * MultilingualPress autoload file.
	 */
	require_once __DIR__ . '/src/autoload.php';
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

	$container = AddOnlyContainer::for_mlp();
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
		->add_service_provider( new Widget\WidgetServiceProvider() );

	$multilingualpress = new MultilingualPress( $container, $providers );

	/**
	 * MultilingualPress functions.
	 */
	require_once __DIR__ . '/src/functions.php';

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

	class_exists( 'Mlp_Load_Controller' ) or require __DIR__ . '/src/inc/autoload/Mlp_Load_Controller.php';
	new \Mlp_Load_Controller(
		resolve( 'multilingualpress.properties' )->plugin_dir_path() . '/src/inc'
	);

	// Advanced Translator
	new \Mlp_Advanced_Translator();

	// Translation Meta Box
	new \Mlp_Translation_Metabox();

	if ( is_admin() ) {
		// Term Translator
		add_action( 'wp_loaded', function () {

			$taxonomy = empty( $_REQUEST['taxonomy'] ) ? '' : (string) $_REQUEST['taxonomy'];

			$term_taxonomy_id = empty( $_REQUEST['tag_ID'] ) ? 0 : (int) $_REQUEST['tag_ID'];

			( new \Mlp_Term_Translation_Controller(
				resolve( 'multilingualpress.content_relations', ContentRelations::class ),
				new Common\Nonce\WPNonce( "save_{$taxonomy}_translations_$term_taxonomy_id" )
			) )->setup();
		}, 0 );
	}

	add_action( 'wp_loaded', function () {

		new \Mlp_Language_Manager_Controller(
			new \Mlp_Language_Db_Access( resolve( 'multilingualpress.languages_table', Table::class )->name() ),
			resolve( 'multilingualpress.wpdb', \wpdb::class )
		);
	} );
} );
