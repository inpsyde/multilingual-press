<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: MultilingualPress
 * Plugin URI:  https://wordpress.org/plugins/multilingual-press/
 * Description: Simply <strong>the</strong> multisite-based free open source plugin for your multilingual websites.
 * Author:      Inpsyde GmbH
 * Author URI:  https://inpsyde.com
 * Version:     3.0.0-dev
 * Text Domain: multilingual-press
 * License:     MIT
 * Network:     true
 */

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\API\APIServiceProvider;
use Inpsyde\MultilingualPress\Asset\AssetServiceProvider;
use Inpsyde\MultilingualPress\Core\CoreServiceProvider;
use Inpsyde\MultilingualPress\Core\ImmutablePluginProperties;
use Inpsyde\MultilingualPress\Database\DatabaseServiceProvider;
use Inpsyde\MultilingualPress\Factory\FactoryProvider;
use Inpsyde\MultilingualPress\Installation\InstallationServiceProvider;
use Inpsyde\MultilingualPress\Integration\IntegrationProvider;
use Inpsyde\MultilingualPress\Module;
use Inpsyde\MultilingualPress\NavMenu\NavMenuServiceProvider;
use Inpsyde\MultilingualPress\Relations\RelationsServiceProvider;
use Inpsyde\MultilingualPress\Service\AddOnlyContainer;
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
		->register_service_provider( new CoreServiceProvider() )
		->register_service_provider( new APIServiceProvider() )
		->register_service_provider( new AssetServiceProvider() )
		->register_service_provider( new DatabaseServiceProvider() )
		->register_service_provider( new FactoryProvider() )
		->register_service_provider( new InstallationServiceProvider() )
		->register_service_provider( new IntegrationProvider() )
		->register_service_provider( new Module\AlternativeLanguageTitleInAdminBar\ServiceProvider() )
		->register_service_provider( new Module\CustomPostTypeSupport\ServiceProvider() )
		->register_service_provider( new Module\Quicklinks\ServiceProvider() )
		->register_service_provider( new Module\Redirect\ServiceProvider() )
		->register_service_provider( new Module\Trasher\ServiceProvider() )
		->register_service_provider( new Module\UserAdminLanguage\ServiceProvider() )
		->register_service_provider( new NavMenuServiceProvider() )
		->register_service_provider( new RelationsServiceProvider() )
		->register_service_provider( new SiteDuplicationServiceProvider() )
		->register_service_provider( new TranslationServiceProvider() )
		->register_service_provider( new Widget\WidgetServiceProvider() );

	/**
	 * MultilingualPress functions.
	 */
	require_once __DIR__ . '/src/functions.php';

	return $multilingualpress->bootstrap();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );

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

// TODO: Eventually remove/refactor according to new architecure as soon as the old controller got replaced.
add_action( MultilingualPress::ACTION_BOOTSTRAPPED, function () {

	class_exists( 'Mlp_Load_Controller' ) or require __DIR__ . '/src/inc/autoload/Mlp_Load_Controller.php';
	new \Mlp_Load_Controller(
		MultilingualPress::resolve( 'multilingualpress.properties' )->plugin_dir_path() . '/src/inc'
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
				MultilingualPress::resolve( 'multilingualpress.content_relations' ),
				new Common\Nonce\WPNonce( "save_{$taxonomy}_translations_$term_taxonomy_id" )
			) )->setup();
		}, 0 );
	}

	add_action( 'wp_loaded', function () {

		new \Mlp_Language_Manager_Controller(
			new \Mlp_Language_Db_Access( MultilingualPress::resolve( 'multilingualpress.languages_table' )->name() ),
			MultilingualPress::resolve( 'multilingualpress.wpdb' )
		);
	} );
} );
