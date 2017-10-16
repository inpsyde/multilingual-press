<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Read;

use Inpsyde\MultilingualPress\REST\Common\Arguments;
use Inpsyde\MultilingualPress\Factory\SanitizationCallbackFactory as Sanitizer;

/**
 * Endpoint arguments for reading content relations.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations\Read
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
				'default'           => 'post',
				'sanitize_callback' => Sanitizer::sanitize_string(),
			],
		];
	}
}
