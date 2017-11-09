<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\Languages\Read;

use Inpsyde\MultilingualPress\REST\Common\Arguments;
use Inpsyde\MultilingualPress\Factory\SanitizationCallbackFactory as Sanitizer;

/**
 * Endpoint arguments for reading languages.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\Languages\Read
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
			'id' => [
				'description'       => __( 'A language ID.', 'multilingualpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'sanitize_callback' => Sanitizer::sanitize_numeric_id(),
			],
		];
	}
}
