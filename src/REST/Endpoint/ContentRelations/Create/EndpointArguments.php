<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Create;

use Inpsyde\MultilingualPress\Factory\ErrorFactory;
use Inpsyde\MultilingualPress\Factory\SanitizationCallbackFactory as Sanitizer;
use Inpsyde\MultilingualPress\Factory\ValidationCallbackFactory as Validator;
use Inpsyde\MultilingualPress\REST\Common\Arguments;

/**
 * Endpoint arguments for creating content relations.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Create
 * @since   3.0.0
 */
final class EndpointArguments implements Arguments {

	/**
	 * @var ErrorFactory
	 */
	private $error_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ErrorFactory $error_factory Error factory object.
	 */
	public function __construct( ErrorFactory $error_factory ) {

		$this->error_factory = $error_factory;
	}

	/**
	 * Returns the arguments in array form.
	 *
	 * @since 3.0.0
	 *
	 * @return array[] Arguments array.
	 */
	public function to_array(): array {

		return [
			'content_ids' => [
				'description'       => __(
					'An array with site IDs as keys and content IDs as values.',
					'multilingualpress'
				),
				'type'              => 'array',
				'required'          => true,
				'sanitize_callback' => Sanitizer::sanitize_array(),
				'validate_callback' => Validator::validate_array_min_elements( 2, function () {

					return $this->error_factory->create( [
						'not_enough_content_elements',
						__( 'A relationship needs two or more content elements.', 'multilingualpress' ),
						[
							'status' => 400,
						],
					] );
				} ),
			],
			'type'        => [
				'description'       => __( 'The type of the related content elements.', 'multilingualpress' ),
				'type'              => 'string',
				'default'           => 'post',
				'sanitize_callback' => Sanitizer::sanitize_string(),
			],
		];
	}
}
