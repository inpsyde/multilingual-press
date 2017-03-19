<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Factory\Exception;

/**
 * Exception to be thrown when trying to create an object of an invalid class.
 *
 * @package Inpsyde\MultilingualPress\Factory\Exception
 * @since   3.0.0
 */
class InvalidClass extends \Exception {

	/**
	 * Returns a new exception object.
	 *
	 * @since 3.0.0
	 *
	 * @param string $class Fully qualified name of the class.
	 * @param string $base  Fully qualified name of the base class or interface.
	 *
	 * @return InvalidClass
	 */
	public static function for_base( string $class, string $base ): InvalidClass {

		return new static( sprintf(
			'The class "%1$s" is invalid with respect to the defined base "%2$s".',
			$class,
			$base
		) );
	}
}
