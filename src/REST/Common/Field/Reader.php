<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Field;

/**
 * Interface for all field reader implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Field
 * @since   3.0.0
 */
interface Reader {

	/**
	 * Returns the value of the field with the given name of the given object.
	 *
	 * @since 3.0.0
	 *
	 * @param array            $object      Object data in array form.
	 * @param string           $field_name  Field name.
	 * @param \WP_REST_Request $request     Request object.
	 * @param string           $object_type Optional. Object type. Defaults to empty string.
	 *
	 * @return mixed Field value.
	 */
	public function get_value(
		array $object,
		string $field_name,
		\WP_REST_Request $request,
		string $object_type = ''
	);
}
