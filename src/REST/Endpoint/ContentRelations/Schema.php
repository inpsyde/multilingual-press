<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations;

use Inpsyde\MultilingualPress\REST\Common\Endpoint;
use Inpsyde\MultilingualPress\REST\Common\Endpoint\FieldProcessor;

/**
 * Content relations schema.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\ContentRelations
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
	private $title = 'content-relations';

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
			'site_id'    => [
				'description' => __( 'The site ID of the content element.', 'multilingualpress' ),
				'type'        => 'integer',
				'context'     => [
					'view',
					'edit',
				],
			],
			'content_id' => [
				'description' => __( 'The ID of the content element.', 'multilingualpress' ),
				'type'        => 'integer',
				'context'     => [
					'view',
					'edit',
				],
			],
			'type'       => [
				'description' => __( 'The type of the content element.', 'multilingualpress' ),
				'type'        => 'string',
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
