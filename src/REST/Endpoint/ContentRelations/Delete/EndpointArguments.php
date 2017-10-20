<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Delete;

use Inpsyde\MultilingualPress\API\ContentRelations;
use Inpsyde\MultilingualPress\REST\Common\Arguments;
use Inpsyde\MultilingualPress\Factory\SanitizationCallbackFactory as Sanitizer;

/**
 * Endpoint arguments for deleting content relations.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Delete
 * @since   3.0.0
 */
final class EndpointArguments implements Arguments {

	/**
	 * Returns the arguments in array form.
	 *
	 * @since 3.0.0
	 *
	 * @return array[] Arguments array.
	 */
	public function to_array(): array {

		return [
			'site_id'    => [
				'description'       => __( 'A site ID.', 'multilingualpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'required'          => true,
				'sanitize_callback' => Sanitizer::sanitize_numeric_id(),
			],
			'content_id' => [
				'description'       => __( 'A content element ID.', 'multilingualpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'required'          => true,
				'sanitize_callback' => Sanitizer::sanitize_numeric_id(),
			],
			'type'       => [
				'description'       => __( 'A content element type.', 'multilingualpress' ),
				'type'              => 'string',
				'default'           => ContentRelations::CONTENT_TYPE_POST,
				'sanitize_callback' => Sanitizer::sanitize_string(),
			],
		];
	}
}
