<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Factory\ClassResolver;

/**
 * Factory for WordPress error objects.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
class ErrorFactory {

	/**
	 * @var ClassResolver
	 */
	private $class_resolver;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $default_class Optional. Fully qualified name of the default class. Defaults to empty string.
	 */
	public function __construct( string $default_class = '' ) {

		$this->class_resolver = new ClassResolver( \WP_Error::class, $default_class );
	}

	/**
	 * Returns a new WordPress error object, instantiated with the given arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to empty string.
	 *
	 * @return \WP_Error WordPress error object.
	 */
	public function create( array $args = [], string $class = '' ): \WP_Error {

		$class = $this->class_resolver->resolve_class( $class );

		return new $class( ...$args );
	}
}
