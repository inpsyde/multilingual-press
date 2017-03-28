<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Factory\ClassResolver;

/**
 * Generic factory.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
class GenericFactory {

	/**
	 * @var ClassResolver
	 */
	private $class_resolver;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $base          Fully qualified name of the base class or interface.
	 * @param string $default_class Optional. Fully qualified name of the default class. Defaults to empty string.
	 */
	public function __construct( string $base, string $default_class = '' ) {

		$this->class_resolver = new ClassResolver( $base, $default_class );
	}

	/**
	 * Returns a new factory, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string $base          Fully qualified name of the base class or interface.
	 * @param string $default_class Fully qualified name of the default class.
	 *
	 * @return static Factory object.
	 */
	public static function with_default_class( string $base, string $default_class ) {

		return new static( $base, $default_class );
	}

	/**
	 * Returns a new object, instantiated with the given arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to empty string.
	 *
	 * @return object Object.
	 */
	public function create( array $args = [], string $class = '' ) {

		$class = $this->class_resolver->resolve_class( $class );

		return new $class( ...$args );
	}
}
