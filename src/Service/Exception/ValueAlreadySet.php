<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service\Exception;

use Exception;

/**
 * Exception to be thrown when a value that has already been set is to be manipulated.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class ValueAlreadySet extends Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the value or factory callback.
	 * @param string $action Optional. Action to be performed. Defaults to 'extend'.
	 *
	 * @return ValueAlreadySet Exception object.
	 */
	public static function for_name( string $name, string $action = 'extend' ): ValueAlreadySet {

		return new static( sprintf(
			'Cannot %2$s "%1$s". There already is a value with this name.',
			$name,
			$action
		) );
	}
}
