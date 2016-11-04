<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Core\Exception;

use Exception;

/**
 * Exception to be thrown when a property that has not yet been set is to be read.
 *
 * @package Inpsyde\MultilingualPress\Core\Exception
 * @since   3.0.0
 */
class PropertyNotSetException extends Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the property.
	 * @param string $action Optional. Action to be performed. Defaults to 'read'.
	 *
	 * @return static Exception object.
	 */
	public static function for_name( $name, $action = 'read' ) {

		return new static( sprintf(
			'Cannot %2$s "%1$s". There is no property with this name.',
			$name,
			$action
		) );
	}
}
