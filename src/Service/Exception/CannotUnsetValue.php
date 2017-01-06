<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Service\Exception;

use Exception;

/**
 * Exception to be thrown when a value or factory callback is to be unset from the container.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class CannotUnsetValue extends Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of the value or factory callback.
	 *
	 * @return static Exception object.
	 */
	public static function for_name( $name ) {

		return new static( sprintf(
			'Cannot unset "%1$s". Removing values or factory callbacks is not allowed.',
			$name
		) );
	}
}
