<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Request;

use Inpsyde\MultilingualPress\REST\Common;
use Inpsyde\MultilingualPress\REST\Core;

/**
 * Simple field processor implementation.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Request
 * @since   3.0.0
 */
final class FieldProcessor implements Common\Request\FieldProcessor {

	/**
	 * @var Common\Field\Access
	 */
	private $field_access;

	/**
	 * @var \WP_Error
	 */
	private $last_error;

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
	public function add_fields_to_object( array $object, \WP_REST_Request $request, string $object_type = '' ): array {

		$fields = $this->field_access->get_fields( $object_type );
		foreach ( $fields as $name => $definition ) {
			if ( empty( $definition['get_callback'] ) ) {
				continue;
			}

			if ( ! is_callable( $definition['get_callback'] ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// @codingStandardsIgnoreLine as this is specifically intended to be used when debugging.
					trigger_error( "Invalid callback. Cannot read {$name} field for {$object_type}." );
				}

				continue;
			}

			$object[ $name ] = $definition['get_callback']( $object, $name, $request, $object_type );
		}

		return $object;
	}

	/**
	 * Returns the last error encountered when updating fields.
	 *
	 * @see   update_fields_for_object
	 * @since 3.0.0
	 *
	 * @return \WP_Error|null WordPress error object, or null.
	 */
	public function get_last_error() {

		return $this->last_error ?? null;
	}

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
	): bool {

		unset( $this->last_error );

		$updated = false;

		$fields = $this->field_access->get_fields( $object_type );
		foreach ( $fields as $name => $definition ) {
			if ( ! isset( $request[ $name ] ) ) {
				continue;
			}

			if ( empty( $definition['update_callback'] ) ) {
				continue;
			}

			if ( ! is_callable( $definition['update_callback'] ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// @codingStandardsIgnoreLine as this is specifically intended to be used when debugging.
					trigger_error( "Invalid callback. Cannot update {$name} field for {$object_type}." );
				}

				continue;
			}

			$result = $definition['update_callback']( $request[ $name ], $object, $name, $request, $object_type );

			if ( is_wp_error( $result ) ) {
				$this->last_error = $result;

				return false;
			}

			$updated = true;
		}

		return $updated;
	}
}
