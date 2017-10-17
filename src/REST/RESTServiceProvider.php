<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST;

use Inpsyde\MultilingualPress\Factory\PermissionCallbackFactory;
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

		$container->share( 'multilingualpress.rest_field_access', function () {

			return new Core\Field\Access();
		} );

		$container->share( 'multilingualpress.rest_field_collection', function () {

			return new Core\Field\Collection();
		} );

		$container->share( 'multilingualpress.rest_field_registry', function () {

			return new Core\Field\Registry();
		} );

		$container->share( 'multilingualpress.rest_request_field_processor', function ( Container $container ) {

			return new Core\Request\FieldProcessor(
				$container['multilingualpress.rest_field_access']
			);
		} );

		$container->share( 'multilingualpress.rest_response_data_access', function () {

			return new Core\Response\LinkAwareDataAccess();
		} );

		$container->share( 'multilingualpress.rest_route_collection', function () {

			return new Core\Route\Collection();
		} );

		$container->share( 'multilingualpress.rest_route_registry', function () {

			return new Core\Route\Registry( REST_API_NAMESPACE );
		} );

		$container->share( 'multilingualpress.rest_schema_field_processor', function ( Container $container ) {

			return new Core\Endpoint\FieldProcessor(
				$container['multilingualpress.rest_field_access']
			);
		} );

		$this->register_content_relations( $container );

		$this->register_site_relations( $container );
	}

	/**
	 * Registers the content relations services on the given container.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_content_relations( Container $container ) {

		$container->share( 'multilingualpress.rest.content_relations_create_arguments', function (
			Container $container
		) {

			return new Endpoint\ContentRelations\Create\EndpointArguments(
				$container['multilingualpress.error_factory']
			);
		} );

		$container->share( 'multilingualpress.rest.content_relations_create_handler', function (
			Container $container
		) {

			return new Endpoint\ContentRelations\Create\RequestHandler(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.rest.content_relations_formatter'],
				$container['multilingualpress.rest.content_relations_schema'],
				$container['multilingualpress.rest_request_field_processor'],
				$container['multilingualpress.rest_response_factory']
			);
		} );

		$container->share( 'multilingualpress.rest.content_relations_data_filter', function ( Container $container ) {

			return new Core\Response\SchemaAwareDataFilter(
				$container['multilingualpress.rest.content_relations_schema']
			);
		} );

		$container->share( 'multilingualpress.rest.content_relations_field', function ( Container $container ) {

			$field = new Core\Field\Field( 'mlp:content_relations' );
			$field->set_get_callback( $container['multilingualpress.rest.content_relations_field_reader'] );
			$field->set_schema( $container['multilingualpress.rest.content_relations_field_schema'] );

			return $field;
		} );

		$container->share( 'multilingualpress.rest.content_relations_field_reader', function (
			Container $container
		) {

			return new Field\ContentRelations\Reader(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.active_post_types'],
				$container['multilingualpress.active_taxonomies']
			);
		} );

		$container->share( 'multilingualpress.rest.content_relations_field_schema', function () {

			return new Field\ContentRelations\Schema();
		} );

		$container->share( 'multilingualpress.rest.content_relations_formatter', function ( Container $container ) {

			return new Endpoint\ContentRelations\Formatter(
				$container['multilingualpress.rest.content_relations_data_filter'],
				$container['multilingualpress.rest.content_relations_schema'],
				$container['multilingualpress.rest_response_factory'],
				$container['multilingualpress.rest_response_data_access']
			);
		} );

		$container->share( 'multilingualpress.rest.content_relations_read_arguments', function () {

			return new Endpoint\ContentRelations\Read\EndpointArguments();
		} );

		$container->share( 'multilingualpress.rest.content_relations_read_handler', function ( Container $container ) {

			return new Endpoint\ContentRelations\Read\RequestHandler(
				$container['multilingualpress.content_relations'],
				$container['multilingualpress.rest.content_relations_formatter'],
				$container['multilingualpress.rest.content_relations_schema'],
				$container['multilingualpress.rest_request_field_processor'],
				$container['multilingualpress.rest_response_factory']
			);
		} );

		$container->share( 'multilingualpress.rest.content_relations_schema', function ( Container $container ) {

			return new Endpoint\ContentRelations\Schema(
				$container['multilingualpress.rest_schema_field_processor']
			);
		} );
	}

	/**
	 * Registers the site relations services on the given container.
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	private function register_site_relations( Container $container ) {

		$container->share( 'multilingualpress.rest.site_relations_data_filter', function ( Container $container ) {

			return new Core\Response\SchemaAwareDataFilter(
				$container['multilingualpress.rest.site_relations_schema']
			);
		} );

		$container->share( 'multilingualpress.rest.site_relations_formatter', function ( Container $container ) {

			return new Endpoint\SiteRelations\Formatter(
				$container['multilingualpress.rest.site_relations_data_filter'],
				$container['multilingualpress.rest.site_relations_schema'],
				$container['multilingualpress.rest_response_factory'],
				$container['multilingualpress.rest_response_data_access']
			);
		} );

		$container->share( 'multilingualpress.rest.site_relations_read_arguments', function () {

			return new Endpoint\SiteRelations\Read\EndpointArguments();
		} );

		$container->share( 'multilingualpress.rest.site_relations_read_handler', function ( Container $container ) {

			return new Endpoint\SiteRelations\Read\RequestHandler(
				$container['multilingualpress.site_relations'],
				$container['multilingualpress.rest.site_relations_formatter'],
				$container['multilingualpress.rest.site_relations_schema'],
				$container['multilingualpress.rest_request_field_processor'],
				$container['multilingualpress.rest_response_factory']
			);
		} );

		$container->share( 'multilingualpress.rest.site_relations_schema', function ( Container $container ) {

			return new Endpoint\SiteRelations\Schema(
				$container['multilingualpress.rest_schema_field_processor']
			);
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

		add_action( 'rest_api_init', function () use ( $container ) {

			// Bootstrap new REST routes.
			$route_collection = $container['multilingualpress.rest_route_collection'];

			$this->add_content_relations_routes( $route_collection, $container );
			$this->add_site_relations_routes( $route_collection, $container );

			$container['multilingualpress.rest_route_registry']->register_routes( $route_collection );

			// Bootstrap new REST fields.
			$field_collection = $container['multilingualpress.rest_field_collection'];

			$this->add_content_relations_fields( $field_collection, $container );

			$container['multilingualpress.rest_field_registry']->register_fields( $field_collection );
		} );
	}

	/**
	 * Adds the content relations routes.
	 *
	 * @param Core\Route\Collection $route_collection Route collection object.
	 * @param Container             $container        Container object.
	 *
	 * @return void
	 */
	private function add_content_relations_routes( Core\Route\Collection $route_collection, Container $container ) {

		$schema = $container['multilingualpress.rest.content_relations_schema'];

		$base = $schema->title();

		$route_collection->add( new Core\Route\Route(
			$base,
			Core\Route\Options::from_arguments(
				$container['multilingualpress.rest.content_relations_create_handler'],
				$container['multilingualpress.rest.content_relations_create_arguments'],
				\WP_REST_Server::CREATABLE,
				[
					'permission_callback' => PermissionCallbackFactory::current_user_can( 'edit_posts' ),
				]
			)->set_schema( $schema )
		) );

		$route_collection->add( new Core\Route\Route(
			$base . '/(?P<site_id>\d+)/(?P<content_id>\d+)(?:/(?P<type>[^/]+))?',
			Core\Route\Options::from_arguments(
				$container['multilingualpress.rest.content_relations_read_handler'],
				$container['multilingualpress.rest.content_relations_read_arguments']
			)->set_schema( $schema )
		) );

		$route_collection->add( new Core\Route\Route(
			$base . '/schema',
			Core\Route\Options::with_callback( [ $schema, 'definition' ] )
		) );
	}

	/**
	 * Adds the site relations routes.
	 *
	 * @param Core\Route\Collection $route_collection Route collection object.
	 * @param Container             $container        Container object.
	 *
	 * @return void
	 */
	private function add_site_relations_routes( Core\Route\Collection $route_collection, Container $container ) {

		$schema = $container['multilingualpress.rest.site_relations_schema'];

		$base = $schema->title();

		$route_collection->add( new Core\Route\Route(
			$base . '/(?P<site_id>\d+)',
			Core\Route\Options::from_arguments(
				$container['multilingualpress.rest.site_relations_read_handler'],
				$container['multilingualpress.rest.site_relations_read_arguments']
			)->set_schema( $schema )
		) );

		$route_collection->add( new Core\Route\Route(
			$base . '/schema',
			Core\Route\Options::with_callback( [ $schema, 'definition' ] )
		) );
	}

	/**
	 * Adds the content relations fields.
	 *
	 * @param Core\Field\Collection $field_collection Field collection object.
	 * @param Container             $container        Container object.
	 *
	 * @return void
	 */
	private function add_content_relations_fields( Core\Field\Collection $field_collection, Container $container ) {

		$object_types = array_merge(
			$container['multilingualpress.active_post_types']->names(),
			$container['multilingualpress.active_taxonomies']->names()
		);
		if ( ! $object_types ) {
			return;
		}

		$field = $container['multilingualpress.rest.content_relations_field'];

		foreach ( $object_types as $object_type ) {
			$field_collection->add( $object_type, $field );
		}
	}
}
