<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Nonce\Exception;

use Exception;

/**
 * Exception to be thrown when a nonce context value is to be manipulated.
 *
 * @package Inpsyde\MultilingualPress\Common\Nonce\Exception
 * @since   3.0.0
 */
class ContextValueManipulationNotAllowed extends Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the nonce context value.
	 * @param string $action Optional. Action to be performed. Defaults to 'set'.
	 *
	 * @return static Exception object.
	 */
	public static function for_name( $name, $action = 'set' ) {

		return new static( sprintf(
			'Cannot %2$s "%1$s". Manipulating a nonce context value is not allowed.',
			$name,
			$action
		) );
	}
}
