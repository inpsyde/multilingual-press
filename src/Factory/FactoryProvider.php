<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Nonce\WPNonce;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

/**
 * Service provider for all factories.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
final class FactoryProvider implements ServiceProvider {

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

		$container->share( 'multilingualpress.error_factory', function () {

			return new ErrorFactory();
		} );

		$container->share( 'multilingualpress.nonce_factory', function () {

			return new NonceFactory( WPNonce::class );
		} );

		$container->share( 'multilingualpress.permission_callback_factory', function () {

			return new PermissionCallbackFactory();
		} );

		$container->share( 'multilingualpress.type_factory', function () {

			return new TypeFactory();
		} );
	}
}
