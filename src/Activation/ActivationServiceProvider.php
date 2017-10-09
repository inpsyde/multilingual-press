<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Activation;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\Service\Container;
use Inpsyde\MultilingualPress\Service\IntegrationServiceProvider;

use const Inpsyde\MultilingualPress\ACTION_ACTIVATION;

/**
 * Service provider for all activation objects.
 *
 * @package Inpsyde\MultilingualPress\Activation
 * @since   3.0.0
 */
final class ActivationServiceProvider implements IntegrationServiceProvider {

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

		$container['multilingualpress.activator'] = function () {

			return new NetworkOptionActivator();
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

		$activator = $container['multilingualpress.activator'];

		if ( did_action( ACTION_ACTIVATION ) ) {
			$activator->handle_activation();
		}

		$activator->register_callback( function () use ( $container ) {

			$content_relations = $container['multilingualpress.content_relations'];

			$content_relations->delete_all_relations_for_invalid_sites();
			$content_relations->delete_all_relations_for_invalid_content( ContentRelations::CONTENT_TYPE_POST );
			$content_relations->delete_all_relations_for_invalid_content( ContentRelations::CONTENT_TYPE_TERM );
		} );

		$activator->handle_pending_activation();
	}
}
