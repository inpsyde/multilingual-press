<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\Languages\Create;

use Inpsyde\MultilingualPress\REST\Common\Arguments;
use Inpsyde\MultilingualPress\Factory\SanitizationCallbackFactory as Sanitizer;

/**
 * Endpoint arguments for creating languages.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\Languages\Create
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
			'english_name' => [
				'description' => __( 'The English name of the language.', 'multilingualpress' ),
				'type'        => 'string',
			],
			'native_name'  => [
				'description' => __( 'The native name of the language.', 'multilingualpress' ),
				'type'        => 'string',
			],
			'custom_name'  => [
				'description' => __( 'The custom name of the language.', 'multilingualpress' ),
				'type'        => 'string',
			],
			'rtl'          => [
				'description'       => __( 'Whether or not the language is right-to-left (RTL).', 'multilingualpress' ),
				'type'              => 'boolean',
				'sanitize_callback' => Sanitizer::sanitize_bool(),
			],
			'iso_639_1'    => [
				'description' => __( 'The ISO-639-1 code of the language.', 'multilingualpress' ),
				'type'        => 'string',
			],
			'iso_639_2'    => [
				'description' => __( 'The ISO-639-2 code of the language.', 'multilingualpress' ),
				'type'        => 'string',
			],
			'locale'       => [
				'description' => __( 'The WordPress locale of the language.', 'multilingualpress' ),
				'type'        => 'string',
			],
			'http_code'    => [
				'description' => __( 'The HTTP code of the language.', 'multilingualpress' ),
				'type'        => 'string',
			],
			'priority'     => [
				'description' => __( 'The priority of the language.', 'multilingualpress' ),
				'type'        => 'integer',
			],
		];
	}
}
