<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Integration;

use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\IntegrationServiceProvider;

/**
 * Service provider for all third-party integrations.
 *
 * @package Inpsyde\MultilingualPress\Integration
 * @since   3.0.0
 */
final class IntegrationProvider implements IntegrationServiceProvider {

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

		$container['multilingualpress.integration.wp_cli'] = function () {

			return new WPCLI();
		};
	}

	/**
	 * Integrates the registered services with MultilingualPress.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function integrate( Container $container ) {

		$container['multilingualpress.integration.wp_cli']->integrate();
	}
}
