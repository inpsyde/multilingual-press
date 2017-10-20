<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Field;

/**
 * Interface for all implementations of readable fields.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Field
 * @since   3.0.0
 */
interface ReadableField extends Field {

	/**
	 * Sets the callback for reading the field value to the according callback on the given field reader object.
	 *
	 * @since 3.0.0
	 *
	 * @param Reader $reader Optional. Field reader object. Defaults to null.
	 *
	 * @return ReadableField Field object.
	 */
	public function set_get_callback( Reader $reader = null ): ReadableField;
}
