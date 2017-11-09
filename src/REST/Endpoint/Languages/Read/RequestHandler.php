<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Endpoint\Languages\Read;

use Inpsyde\MultilingualPress\API\Languages as API;
use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\NullLanguage;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;
use Inpsyde\MultilingualPress\Factory\RESTResponseFactory;
use Inpsyde\MultilingualPress\REST\Common\Endpoint;
use Inpsyde\MultilingualPress\REST\Common\Request\FieldProcessor;
use Inpsyde\MultilingualPress\REST\Endpoint\Languages\Formatter;
use Inpsyde\MultilingualPress\REST\Endpoint\Languages\Schema;

/**
 * Request handler for reading languages.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\Languages\Read
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

		$data = $this->formatter->format(
			$this->get_languages( $request ),
			(string) ( $request['context'] ?? 'view' )
		);

		$data = $this->field_processor->add_fields_to_object( $data, $request, $this->object_type );

		return $this->response_factory->create( [ $data ] );
	}

	/**
	 * Returns an array of all language objects, or the one with the ID included in the request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return Language[] An array of language objects..
	 */
	private function get_languages( \WP_REST_Request $request ): array {

		if ( ! isset( $request['id'] ) ) {
			return $this->api->get_languages( [
				'order_by' => [
					[
						'field' => LanguagesTable::COLUMN_ID,
						'order' => 'ASC',
					],
				],
			] );
		}

		$language = $this->api->get_language_by( LanguagesTable::COLUMN_ID, (int) $request['id'] );
		if ( $language instanceof NullLanguage ) {
			return [];
		}

		return [
			$language,
		];
	}
}
