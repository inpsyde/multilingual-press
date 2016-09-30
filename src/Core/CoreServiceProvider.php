<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Module;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

// TODO: As soon as necessary, make this class implement BootstrappableServiceProvider instead of ServiceProvider.

/**
 * Service provider for all Core objects.
 *
 * @package Inpsyde\MultilingualPress\Core
 * @since   3.0.0
 */
final class CoreServiceProvider implements ServiceProvider {

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

		$container['multilingualpress.module_manager'] = function () {

			// TODO: Maybe store the option name somewhere? But then again, who else really needs to know it?
			return new Module\NetworkOptionModuleManager( 'multilingualpress_modules' );
		};
	}
}
