<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Nonce\Exception;

use Exception;

/**
 * Exception to be thrown when a nonce context value that has not yet been set is to be read from the container.
 *
 * @package Inpsyde\MultilingualPress\Common\Nonce\Exception
 * @since   3.0.0
 */
class ContextValueNotSet extends Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the nonce context value.
	 * @param string $action Optional. Action to be performed. Defaults to 'read'.
	 *
	 * @return static Exception object.
	 */
	public static function for_name( $name, $action = 'read' ) {

		return new static( sprintf(
			'Cannot %2$s "%1$s". There is no nonce context value with this name.',
			$name,
			$action
		) );
	}
}
