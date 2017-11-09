<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\SiteRelations\Update;

use Inpsyde\MultilingualPress\Factory\ErrorFactory;
use Inpsyde\MultilingualPress\REST\Common\Arguments;
use Inpsyde\MultilingualPress\Factory\SanitizationCallbackFactory as Sanitizer;
use Inpsyde\MultilingualPress\Factory\ValidationCallbackFactory as Validator;

/**
 * Endpoint arguments for updating site relations.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\SiteRelations\Update
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
			'site_id'       => [
				'description'       => __( 'A site ID.', 'multilingualpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'required'          => true,
				'sanitize_callback' => Sanitizer::sanitize_numeric_id(),
			],
			'related_sites' => [
				'description'       => __( 'An array with the IDs of all related sites.', 'multilingualpress' ),
				'type'              => 'array',
				'required'          => true,
				'sanitize_callback' => Sanitizer::sanitize_array(),
				'validate_callback' => Validator::validate_array_min_elements( 1, function () {

					return $this->error_factory->create( [
						'not_enough_sites',
						__( 'A relationship needs two or more sites.', 'multilingualpress' ),
						[
							'status' => 400,
						],
					] );
				} ),
			],
		];
	}
}
