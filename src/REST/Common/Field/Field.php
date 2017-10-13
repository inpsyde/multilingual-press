<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Field;

/**
 * Interface for all field implementations.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Field
 * @since   3.0.0
 */
interface Field {

	/**
	 * Returns the field definition (i.e., callbacks and schema).
	 *
	 * @see   register_rest_field()
	 * @since 3.0.0
	 *
	 * @return array Field definition.
	 */
	public function definition(): array;

	/**
	 * Returns the name of the field.
	 *
	 * @see   register_rest_field()
	 * @since 3.0.0
	 *
	 * @return string Field name.
	 */
	public function name(): string;
}
