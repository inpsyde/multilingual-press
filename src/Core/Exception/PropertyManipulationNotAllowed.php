<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Core\Exception;

/**
 * Exception to be thrown when a property is to be manipulated.
 *
 * @package Inpsyde\MultilingualPress\Core\Exception
 * @since   3.0.0
 */
class PropertyManipulationNotAllowed extends \Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name   The name of the property.
	 * @param string $action Optional. Action to be performed. Defaults to 'set'.
	 *
	 * @return PropertyManipulationNotAllowed Exception object.
	 */
	public static function for_name( string $name, string $action = 'set' ): PropertyManipulationNotAllowed {

		return new static( sprintf(
			'Cannot %2$s "%1$s". Manipulating a property is not allowed.',
			$name,
			$action
		) );
	}
}
