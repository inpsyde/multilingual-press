<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Request;

/**
 * Interface for all field processor implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Request
 * @since   3.0.0
 */
interface FieldProcessor {

	/**
	 * Returns the given object with added data of all registered readable fields.
	 *
	 * @see   \WP_REST_Controller::add_additional_fields_to_object
	 * @since 3.0.0
	 *
	 * @param array            $object      Object data in array form.
	 * @param \WP_REST_Request $request     Request object.
	 * @param string           $object_type Optional. Object type. Defaults to empty string.
	 *
	 * @return array Object with added data of all registered readable fields.
	 */
	public function add_fields_to_object( array $object, \WP_REST_Request $request, string $object_type = '' ): array;

	/**
	 * Returns the last error encountered when updating fields.
	 *
	 * @see   update_fields_for_object
	 * @since 3.0.0
	 *
	 * @return \WP_Error|null WordPress error object, or null.
	 */
	public function get_last_error();

	/**
	 * Updates all registered updatable fields of the given object.
	 *
	 * @see   \WP_REST_Controller::update_additional_fields_for_object
	 * @since 3.0.0
	 *
	 * @param array            $object      Object data in array form.
	 * @param \WP_REST_Request $request     Request object.
	 * @param string           $object_type Optional. Object type. Defaults to empty string.
	 *
	 * @return bool Whether or not all fields were updated successfully.
	 */
	public function update_fields_for_object(
		array $object,
			\WP_REST_Request $request,
			string $object_type = ''
	): bool;
}
