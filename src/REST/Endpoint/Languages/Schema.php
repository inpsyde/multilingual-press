<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\Languages;

use Inpsyde\MultilingualPress\REST\Common\Endpoint;
use Inpsyde\MultilingualPress\REST\Common\Endpoint\FieldProcessor;

/**
 * Languages schema.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\Languages
 * @since   3.0.0
 */
final class Schema implements Endpoint\Schema {

	/**
	 * @var FieldProcessor
	 */
	private $field_processor;

	/**
	 * @var string
	 */
	private $title = 'languages';

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param FieldProcessor $field_processor Field processor object.
	 */
	public function __construct( FieldProcessor $field_processor ) {

		$this->field_processor = $field_processor;
	}

	/**
	 * Returns the schema definition.
	 *
	 * @since 3.0.0
	 *
	 * @return array Schema definition.
	 */
	public function definition(): array {

		return [
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => $this->title(),
			'type'       => 'object',
			'properties' => $this->properties(),
		];
	}

	/**
	 * Returns the properties of the schema.
	 *
	 * @since 3.0.0
	 *
	 * @return array Properties definition.
	 */
	public function properties(): array {

		return $this->field_processor->add_fields_to_properties( [
			'id'           => [
				'description' => __( 'The ID of the language.', 'multilingualpress' ),
				'type'        => 'integer',
				'context'     => [
					'view',
					'edit',
				],
				'readonly'    => true,
			],
			'english_name' => [
				'description' => __( 'The English name of the language.', 'multilingualpress' ),
				'type'        => 'string',
				'context'     => [
					'view',
					'edit',
				],
			],
			'native_name'  => [
				'description' => __( 'The native name of the language.', 'multilingualpress' ),
				'type'        => 'string',
				'context'     => [
					'view',
					'edit',
				],
			],
			'custom_name'  => [
				'description' => __( 'The custom name of the language.', 'multilingualpress' ),
				'type'        => 'string',
				'context'     => [
					'view',
					'edit',
				],
			],
			'rtl'          => [
				'description' => __( 'Whether or not the language is right-to-left (RTL).', 'multilingualpress' ),
				'type'        => 'boolean',
				'context'     => [
					'view',
					'edit',
				],
			],
			'iso_639_1'    => [
				'description' => __( 'The ISO-639-1 code of the language.', 'multilingualpress' ),
				'type'        => 'string',
				'context'     => [
					'view',
					'edit',
				],
			],
			'iso_639_2'    => [
				'description' => __( 'The ISO-639-2 code of the language.', 'multilingualpress' ),
				'type'        => 'string',
				'context'     => [
					'view',
					'edit',
				],
			],
			'locale'       => [
				'description' => __( 'The WordPress locale of the language.', 'multilingualpress' ),
				'type'        => 'string',
				'context'     => [
					'view',
					'edit',
				],
			],
			'http_code'    => [
				'description' => __( 'The HTTP code of the language.', 'multilingualpress' ),
				'type'        => 'string',
				'context'     => [
					'view',
					'edit',
				],
			],
			'priority'     => [
				'description' => __( 'The priority of the language.', 'multilingualpress' ),
				'type'        => 'integer',
				'context'     => [
					'view',
					'edit',
				],
			],
		], $this->title );
	}

	/**
	 * Returns the title of the schema.
	 *
	 * @since 3.0.0
	 *
	 * @return string Title.
	 */
	public function title(): string {

		return $this->title;
	}
}
