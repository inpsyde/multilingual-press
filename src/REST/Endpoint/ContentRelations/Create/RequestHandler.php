<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Create;

use Inpsyde\MultilingualPress\API\ContentRelations as API;
use Inpsyde\MultilingualPress\Factory\RESTResponseFactory;
use Inpsyde\MultilingualPress\REST\Common\Endpoint;
use Inpsyde\MultilingualPress\REST\Common\Request\FieldProcessor;
use Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Formatter;
use Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Schema;

/**
 * Request handler for creating content relations.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Create
 * @since   3.0.0
 */
final class RequestHandler implements Endpoint\RequestHandler {

	/**
	 * @var API
	 */
	private $api;

	/**
	 * @var FieldProcessor
	 */
	private $field_processor;

	/**
	 * @var Formatter
	 */
	private $formatter;

	/**
	 * @var string
	 */
	private $object_type;

	/**
	 * @var RESTResponseFactory
	 */
	private $response_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param API                 $api              Content relations API object.
	 * @param Formatter           $formatter        Response data formatter object.
	 * @param Schema              $schema           Endpoint schema object.
	 * @param FieldProcessor      $field_processor  Request data field processor object.
	 * @param RESTResponseFactory $response_factory REST response factory object.
	 */
	public function __construct(
		API $api,
		Formatter $formatter,
		Schema $schema,
		FieldProcessor $field_processor,
		RESTResponseFactory $response_factory
	) {

		$this->api = $api;

		$this->formatter = $formatter;

		$this->object_type = $schema->title();

		$this->field_processor = $field_processor;

		$this->response_factory = $response_factory;
	}

	/**
	 * Handles the given request object and returns the according response object.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function handle_request( \WP_REST_Request $request ): \WP_REST_Response {

		$type = (string) $request['type'];

		$relationship_id = $this->api->create_relationship( $request['content_ids'], $type );
		if ( ! $relationship_id ) {
			return $this->create_error_response( $request );
		}

		$data = $this->formatter->format(
			$this->api->get_content_ids( $relationship_id ),
			$type,
			(string) ( $request['context'] ?? 'view' )
		);

		$this->field_processor->update_fields_for_object( $data, $request, $this->object_type );

		$data = $this->field_processor->add_fields_to_object( $data, $request, $this->object_type );

		return $this->response_factory->create( [ $data ] );
	}

	/**
	 * Creates an error response.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	private function create_error_response( \WP_REST_Request $request ): \WP_REST_Response {

		return $this->response_factory->create( [
			[
				'code'    => 'could_not_create',
				'message' => __( 'The relationship could not be created.', 'multilingualpress' ),
				'data'    => $request->get_params(),
			],
			400,
		] );
	}
}
