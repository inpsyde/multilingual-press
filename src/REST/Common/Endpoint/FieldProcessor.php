<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Endpoint;

/**
 * Interface for all field processor implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Endpoint
 * @since   3.0.0
 */
interface FieldProcessor {

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
	public function add_fields_to_properties( array $properties, string $object_type ): array;
}
