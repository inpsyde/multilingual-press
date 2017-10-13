<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST;

use Inpsyde\MultilingualPress\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Service\Container;

use const Inpsyde\MultilingualPress\REST_API_NAMESPACE;

/**
 * Service provider for all RETST API objects.
 *
 * @package Inpsyde\MultilingualPress\REST
 * @since   3.0.0
 */
final class RESTServiceProvider implements BootstrappableServiceProvider {

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

		$container['multilingualpress.rest_field_access'] = function () {

			return new Core\Field\Access();
		};

		$container['multilingualpress.rest_request_field_processor'] = function ( Container $container ) {

			return new Core\Request\FieldProcessor(
				$container['multilingualpress.rest_field_access']
			);
		};

		$container['multilingualpress.rest_response_data_access'] = function () {

			return new Core\Response\LinkAwareDataAccess();
		};

		$container['multilingualpress.rest_response_factory'] = function () {

			return new Factory\ResponseFactory();
		};

		$container['multilingualpress.rest_route_collection'] = function () {

			return new Core\Route\Collection();
		};

		$container['multilingualpress.rest_route_registry'] = function () {

			return new Core\Route\Registry( REST_API_NAMESPACE );
		};

		$container['multilingualpress.rest_schema_field_processor'] = function ( Container $container ) {

			return new Core\Endpoint\FieldProcessor(
				$container['multilingualpress.rest_field_access']
			);
		};
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

		add_action( 'rest_api_init', function () use ( $container ) {

			$routes = $container['multilingualpress.rest_route_collection'];

			// TODO: Set up the endpoints, and add the individual routes...

			$container['multilingualpress.rest_route_registry']->register_routes( $routes );
		} );
	}
}
