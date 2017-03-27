<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service\Exception;

/**
 * Exception to be thrown when a value that has already been set is to be manipulated.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class InvalidValueWriteAccess extends InvalidValueAccess {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the value or factory callback.
	 * @param string $action Optional. Action to be performed. Defaults to 'extend'.
	 *
	 * @return InvalidValueWriteAccess Exception object.
	 */
	public static function for_immutable_write_attempt(
		string $name,
		string $action = 'extend'
	): InvalidValueWriteAccess {

		return new static( sprintf(
			'Cannot %2$s "%1$s". A service with this name exists and is immutable.',
			$name,
			$action
		) );
	}

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of the value or factory callback.
	 *
	 * @return InvalidValueWriteAccess Exception object.
	 */
	public static function for_immutable_unset_attempt( string $name ): InvalidValueWriteAccess {

		return new static( sprintf(
			'Cannot unset "%1$s". Removing values or factory callbacks is not allowed.',
			$name
		) );
	}
}
