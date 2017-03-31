<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service\Exception;

/**
 * Exception to be thrown when a factory callback could not be found in the container.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class FactoryNotFound extends ValueNotFound {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the value or factory callback.
	 * @param string $action Optional. Action to be performed. Defaults to 'extend'.
	 *
	 * @return ValueNotFound|FactoryNotFound Exception object.
	 */
	public static function for_name( string $name, string $action = 'extend' ): ValueNotFound {

		return new static( sprintf(
			'Cannot %2$s "%1$s". There is no factory callback with this name.',
			$name,
			$action
		) );
	}
}
