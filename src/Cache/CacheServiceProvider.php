<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache;

use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

/**
 * Service provider for all cache objects.
 *
 * @package Inpsyde\MultilingualPress\Asset
 * @since   3.0.0
 */
final class CacheServiceProvider implements ServiceProvider {

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

		$container->share( 'multilingualpress.cache_factory', function ( Container $container ) {

			return new CacheFactory( sprintf( 'mlp_%s_', $container['multilingualpress.properties']->version() ) );
		} );
	}
}
