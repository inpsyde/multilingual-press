<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service\Exception;

use Exception;

/**
 * Exception to be thrown when a value or factory callback that has not yet been set is to be read from the container.
 *
 * @package Inpsyde\MultilingualPress\Service\Exception
 * @since   3.0.0
 */
class ValueNotSet extends Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the value or factory callback.
	 * @param string $action Optional. Action to be performed. Defaults to 'read'.
	 *
	 * @return ValueNotSet Exception object.
	 */
	public static function for_name( string $name, string $action = 'read' ): ValueNotSet {

		return new static( sprintf(
			'Cannot %2$s "%1$s". There is no value or factory callback with this name.',
			$name,
			$action
		) );
	}
}
