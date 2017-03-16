<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Factory;
use Inpsyde\MultilingualPress\Factory\Exception\InvalidClass;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * Generic factory to be used by other factories.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
final class GenericFactory implements Factory {

	/**
	 * @var string
	 */
	private $base;

	/**
	 * @var bool
	 */
	private $base_is_class;

	/**
	 * @var string
	 */
	private $default_class;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $base          Fully qualified name of the base class or interface.
	 * @param string $default_class Optional. Fully qualified name of the default class. Defaults to empty string.
	 *
	 * @throws InvalidArgumentException if the given base is not a valid fully qualified class or interface name.
	 * @throws BadMethodCallException   if no default class is given and the base is an interface.
	 * @throws InvalidClass             if default class is provided but it is invalid
	 */
	public function __construct( string $base, string $default_class = '' ) {

		$this->base_is_class = class_exists( $base );

		if ( ! ( $this->base_is_class || interface_exists( $base ) ) ) {
			throw new InvalidArgumentException( sprintf(
				'"%s"" requires a valid fully qualified class or interface name as first argument.',
				__METHOD__
			) );
		}

		$this->base = (string) $base;

		if ( $default_class ) {
			$this->check_class( $default_class );
			$this->default_class = (string) $default_class;

			return;
		}

		if ( $this->base_is_class ) {
			$this->default_class = (string) $base;

			return;
		}

		throw new BadMethodCallException( sprintf(
			'"%s"" requires a fully qualified class name as first or second argument.',
			__METHOD__
		) );
	}

	/**
	 * Returns a new factory object, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string $base          Fully qualified name of the base class or interface.
	 * @param string $default_class Fully qualified name of the default class.
	 *
	 * @return static Factory object.
	 *
	 * @throws InvalidArgumentException if the given base is not a valid fully qualified class or interface name.
	 * @throws BadMethodCallException   if no default class is given and the base is an interface.
	 * @throws InvalidClass             if default class is provided but it is invalid
	 */
	public static function with_default_class( string $base, string $default_class ) {

		return new static( $base, $default_class );
	}

	/**
	 * Returns a new object of the given (or default) class, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to empty string.
	 *
	 * @return object Object of the given (or default) class, instantiated with the given arguments.
	 *
	 * @throws InvalidClass if class is provided but it is invalid
	 */
	public function create( array $args = [], string $class = '' ) {

		if ( $class ) {
			$this->check_class( $class );
		} else {
			$class = $this->default_class;
		}

		return new $class( ...$args );
	}

	/**
	 * Checks if the class with the given name is valid with respect to the defined base.
	 *
	 * @param string $class FQN of the class to be checked.
	 *
	 * @return void
	 *
	 * @throws InvalidClass if the class with the given name is invalid with respect to the defined base.
	 */
	private function check_class( string $class ) {

		if (
			( ! $this->base_is_class || $class !== $this->base )
			&& is_subclass_of( $class, $this->base, true )
		) {
			throw InvalidClass::for_base( $class, $this->base );
		}
	}
}
