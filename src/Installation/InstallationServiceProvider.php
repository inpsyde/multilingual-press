<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\ServiceProvider;

/**
 * Service provider for all Installation objects.
 *
 * @package Inpsyde\MultilingualPress\Installation
 * @since   3.0.0
 */
final class InstallationServiceProvider implements ServiceProvider  {

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

		$container->share( 'multilingualpress.network_plugin_deactivator', function () {

			return new MatchingNetworkPluginDeactivator();
		} );
	}
}
