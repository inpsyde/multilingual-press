<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Field;

use Inpsyde\MultilingualPress\REST\Common;

/**
 * Registry implementation for fields.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Field
 * @since   3.0.0
 */
final class Registry implements Common\Field\Registry {

	/**
	 * Registers the given fields.
	 *
	 * @since 3.0.0
	 *
	 * @param Common\Field\Collection $fields Field collection object.
	 *
	 * @return void
	 */
	public function register_fields( Common\Field\Collection $fields ) {

		/**
		 * Fires right before the fields are registered.
		 *
		 * @since 3.0.0
		 *
		 * @param Common\Field\Collection $fields Field collection object.
		 */
		do_action( Common\Field\Registry::ACTION_REGISTER, $fields );

		foreach ( $fields as $resource => $resource_fields ) {
			/** @var Common\Field\Field $field */
			foreach ( $resource_fields as $field_name => $field ) {
				register_rest_field( $resource, $field_name, $field->definition() );
			}
		}
	}
}
