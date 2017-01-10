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
use Inpsyde\MultilingualPress\Module;
use Inpsyde\MultilingualPress\NavMenu\NavMenuServiceProvider;
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
		->register_service_provider( new SiteDuplicationServiceProvider() )
		->register_service_provider( new TranslationServiceProvider() )
		->register_service_provider( new Widget\WidgetServiceProvider() );

	/**
	 * MultilingualPress functions.
	 */
	require_once __DIR__ . '/src/functions.php';

	return $multilingualpress->bootstrap();
}
