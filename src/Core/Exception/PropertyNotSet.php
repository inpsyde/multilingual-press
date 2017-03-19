<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Exception;

/**
 * Exception to be thrown when a property that has not yet been set is to be read.
 *
 * @package Inpsyde\MultilingualPress\Core\Exception
 * @since   3.0.0
 */
class PropertyNotSet extends \Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the property.
	 * @param string $action Optional. Action to be performed. Defaults to 'read'.
	 *
	 * @return PropertyNotSet Exception object.
	 */
	public static function for_name( string $name, string $action = 'read' ): PropertyNotSet {

		return new static( sprintf(
			'Cannot %2$s "%1$s". There is no property with this name.',
			$name,
			$action
		) );
	}
}
