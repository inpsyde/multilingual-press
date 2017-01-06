<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Exception;

use Exception;

/**
 * Exception to be thrown when a value or factory callback is to be resolved with no container instance available.
 *
 * @package Inpsyde\MultilingualPress\Core\Exception
 * @since   3.0.0
 */
class CannotResolveName extends Exception {

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
			'Cannot resolve "%s". MultilingualPress has not yet been initialised.',
			$name
		) );
	}
}
