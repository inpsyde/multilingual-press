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
use Inpsyde\MultilingualPress\Common\WordPressServiceProvider;
use Inpsyde\MultilingualPress\Core\CoreServiceProvider;
use Inpsyde\MultilingualPress\Core\ImmutablePluginProperties;
use Inpsyde\MultilingualPress\Database\DatabaseServiceProvider;
use Inpsyde\MultilingualPress\Factory\FactoryProvider;
use Inpsyde\MultilingualPress\Installation\InstallationServiceProvider;
use Inpsyde\MultilingualPress\Module;
use Inpsyde\MultilingualPress\NavMenu\NavMenuServiceProvider;
use Inpsyde\MultilingualPress\Relations\RelationsServiceProvider;
use Inpsyde\MultilingualPress\Service\AddOnlyContainer;
use Inpsyde\MultilingualPress\SiteDuplication\SiteDuplicationServiceProvider;
use Inpsyde\MultilingualPress\Translation\TranslationServiceProvider;

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
		->register_service_provider( new CoreServiceProvider() )
		->register_service_provider( new WordPressServiceProvider() )
		->register_service_provider( new APIServiceProvider() )
		->register_service_provider( new AssetServiceProvider() )
		->register_service_provider( new DatabaseServiceProvider() )
		->register_service_provider( new FactoryProvider() )
		->register_service_provider( new InstallationServiceProvider() )
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

// TODO: Eventually remove/refactor according to new architecure as soon as the old controller got replaced.
add_action( MultilingualPress::ACTION_BOOTSTRAPPED, function () {

	class_exists( 'Mlp_Load_Controller' ) or require __DIR__ . '/src/inc/autoload/Mlp_Load_Controller.php';
	new \Mlp_Load_Controller(
		MultilingualPress::resolve( 'multilingualpress.properties' )->plugin_dir_path() . '/src/inc'
	);

	class_exists( 'Multilingual_Press' ) or require __DIR__ . '/src/inc/Multilingual_Press.php';
	$old_controller = new \Multilingual_Press();
	$old_controller->setup();
	add_action( 'wp_loaded', [ $old_controller, 'prepare_plugin_data' ] );
} );
