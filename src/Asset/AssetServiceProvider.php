<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Asset;

use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Service provider for all assets objects.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
final class AssetServiceProvider implements BootstrappableServiceProvider {

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

		$container->share( 'multilingualpress.asset_factory', function ( Container $container ) {

			return new AssetFactory(
				$container['multilingualpress.internal_locations']
			);
		} );

		$container->share( 'multilingualpress.asset_manager', function () {

			return new AssetManager();
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

		$asset_factory = $container['multilingualpress.asset_factory'];

		$container['multilingualpress.asset_manager']
			->register_script(
				$asset_factory->create_internal_script(
					'multilingualpress',
					'frontend.js'
				)
			)
			->register_script(
				$asset_factory->create_internal_script(
					'multilingualpress-admin',
					'admin.js',
					[
						'backbone',
					]
				)->add_data(
					'mlpSettings',
					[
						'urlRoot' => esc_url( wp_parse_url( admin_url(), PHP_URL_PATH ) ),
					]
				)
			)
			->register_style(
				$asset_factory->create_internal_style(
					'multilingualpress',
					'frontend.css'
				)
			)
			->register_style(
				$asset_factory->create_internal_style(
					'multilingualpress-admin',
					'admin.css'
				)
			);
	}
}
