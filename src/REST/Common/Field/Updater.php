<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Field;

/**
 * Interface for all field updater implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Field
 * @since   3.0.0
 */
interface Updater {

	/**
	 * Updates the value of the field with the given name of the given object to the given value.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed            $value       New field value.
	 * @param object           $object      Object data.
	 * @param string           $field_name  Field name.
	 * @param \WP_REST_Request $request     Optional. Request object. Defaults to null.
	 * @param string           $object_type Optional. Object type. Defaults to empty string.
	 *
	 * @return bool Whether or not the field was updated successfully.
	 */
	public function update_value(
		$value,
		$object,
		string $field_name,
		\WP_REST_Request $request = null,
		string $object_type = ''
	): bool;
}
