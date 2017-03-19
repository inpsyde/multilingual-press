<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service\Exception;

/**
 * Exception to be thrown when a value or factory callback is to be unset from the container.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class CannotUnsetValue extends \Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of the value or factory callback.
	 *
	 * @return CannotUnsetValue Exception object.
	 */
	public static function for_name( string $name ): CannotUnsetValue {

		return new static( sprintf(
			'Cannot unset "%1$s". Removing values or factory callbacks is not allowed.',
			$name
		) );
	}
}
