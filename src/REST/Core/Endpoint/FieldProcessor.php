<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Endpoint;

use Inpsyde\MultilingualPress\REST\Common;
use Inpsyde\MultilingualPress\REST\Core;

/**
 * Simple field processor implementation.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Endpoint
 * @since   3.0.0
 */
final class FieldProcessor implements Common\Endpoint\FieldProcessor {

	/**
	 * @var Common\Field\Access
	 */
	private $field_access;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param Common\Field\Access $field_access Optional. Field access object. Defaults to null.
	 */
	public function __construct( Common\Field\Access $field_access = null ) {

		$this->field_access = $field_access ?? new Core\Field\Access();
	}

	/**
	 * Returns the given properties with added data of all schema-aware fields registered for the given object type.
	 *
	 * @see   \WP_REST_Controller::add_additional_fields_schema
	 * @since 3.0.0
	 *
	 * @param array  $properties  Schema properties definition.
	 * @param string $object_type Object type.
	 *
	 * @return array Properties with added data of all schema-aware fields registered for the given object type.
	 */
	public function add_fields_to_properties( array $properties, string $object_type ): array {

		$fields = $this->field_access->get_fields( $object_type );
		foreach ( $fields as $name => $definition ) {
			if ( empty( $definition['schema'] ) ) {
				continue;
			}

			$properties['properties'][ $name ] = $definition['schema'];
		}

		return $properties;
	}
}
