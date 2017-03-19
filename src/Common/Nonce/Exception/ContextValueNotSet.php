<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Nonce\Exception;

/**
 * Exception to be thrown when a nonce context value that has not yet been set is to be read from the container.
 *
 * @package Inpsyde\MultilingualPress\Common\Nonce\Exception
 * @since   3.0.0
 */
class ContextValueNotSet extends \Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the nonce context value.
	 * @param string $action Optional. Action to be performed. Defaults to 'read'.
	 *
	 * @return ContextValueNotSet Exception object.
	 */
	public static function for_name( string $name, string $action = 'read' ): ContextValueNotSet {

		return new static( sprintf(
			'Cannot %2$s "%1$s". There is no nonce context value with this name.',
			$name,
			$action
		) );
	}
}
