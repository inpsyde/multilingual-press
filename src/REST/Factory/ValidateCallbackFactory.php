<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\REST\Common\Factory;

/**
 * Static factory for diverse validation callbacks.
 *
 * @package Inpsyde\MultilingualPress\REST\Common\Factory
 * @since   3.0.0
 */
class ValidateCallbackFactory {

	/**
	 * Returns a callback that validates and returns the given value.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $min            Minimum array elements.
	 * @param mixed $return_invalid Optional. Return value in case of invalid value. Defaults to false.
	 * @param mixed $return_valid   Optional. Return value in case of valid value. Defaults to true.
	 *
	 * @return \Closure Validation callback.
	 */
	public static function validate_array_min_elements( int $min, $return_invalid = false, $return_valid = true ) {

		/**
		 * Validates and returns the given value.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed $value Value to be validated.
		 *
		 * @return mixed The default or passed return value according to the validation.
		 */
		return function ( $value ) use ( $min, $return_invalid, $return_valid ) {

			return ( is_array( $value ) && $min <= count( $value ) )
				? $return_valid
				: $return_invalid;
		};
	}
}
