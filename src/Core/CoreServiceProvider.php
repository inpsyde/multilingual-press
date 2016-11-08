<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Module;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;

/**
 * Service provider for all Core objects.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
final class CoreServiceProvider implements BootstrappableServiceProvider {

	/**
	 * Registers the provided services on the given container.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function register( Container $container ) {

		$container['multilingualpress.base_path_adapter'] = function () {

			return new CachingBasePathAdapter();
		};

		// TODO: Make this a regular not shared service as soon as everything else has been adapted.
		$container->share( 'multilingualpress.internal_locations', function () {

			return new InternalLocations();
		} );

		// TODO: Make a regular not shared service as soon as everything else has been adapted. Or remove from here?
		$container->share( 'multilingualpress.module_manager', function () {

			// TODO: Maybe store the option name somewhere? But then again, who else really needs to know it?
			// TODO: Migration: The old option name was "state_modules", and it stored "on" and "off" values, no bools.
			return new Module\NetworkOptionModuleManager( 'multilingualpress_modules' );
		} );
	}

	/**
	 * Bootstraps the registered services.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function bootstrap( Container $container ) {

		$properties = $container['multilingualpress.properties'];

		$plugin_dir_path = $properties->plugin_dir_path();

		$plugin_dir_url = $properties->plugin_dir_url();

		$container['multilingualpress.internal_locations']
			->add(
				'plugin',
				$plugin_dir_path,
				$plugin_dir_url
			)
			->add(
				'css',
				"$plugin_dir_path/assets/css",
				"$plugin_dir_url/assets/css"
			)
			->add(
				'js',
				"$plugin_dir_path/assets/js",
				"$plugin_dir_url/assets/js"
			)
			->add(
				'images',
				"$plugin_dir_path/assets/images",
				"$plugin_dir_url/assets/images"
			)
			->add(
				'flags',
				"$plugin_dir_path/assets/images/flags",
				"$plugin_dir_url/assets/images/flags"
			);

		add_action( 'widgets_init', function () {

			/* TODO: With WordPress 4.6 + 2, do the following (via Container?):
			register_widget( new \Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher\Widget(
				new \Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher\WidgetView(),
				$container->get( 'multilingualpress.asset_manager' )
			) );
			*/

			register_widget( '\Inpsyde\MultilingualPress\Widget\Sidebar\LanguageSwitcher\Widget' );
		} );
	}
}
