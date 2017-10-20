<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Factory;

use Inpsyde\MultilingualPress\Common\Factory\Exception\InvalidClass;

/**
 * Class to be used for class resolution in factories.
 *
 * @package Inpsyde\MultilingualPress\Common\Factory
 * @since   3.0.0
 */
class ClassResolver {

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
	 * @throws \InvalidArgumentException If the given base is not a valid fully qualified class or interface name.
	 */
	public function __construct( string $base, string $default_class = '' ) {

		$this->base = $base;

		$this->base_is_class = class_exists( $base );

		if ( ! $this->base_is_class && ! interface_exists( $base ) ) {
			throw new \InvalidArgumentException(
				__METHOD__ . ' requires a valid fully qualified class or interface name as first argument.'
			);
		}

		if ( $default_class ) {
			$this->default_class = $this->check_class( $default_class );
		} elseif ( $this->base_is_class ) {
			$this->default_class = $base;
		}
	}

	/**
	 * Resolves the class to be used for instantiation, which might be either the given class or the default class.
	 *
	 * @param string $class Optional. Initial class to be used. Defaults to empty string.
	 *
	 * @return string Resolved fully qualified class name.
	 *
	 * @throws \InvalidArgumentException If no class is given and no default class is available.
	 */
	public function resolve( string $class = '' ): string {

		if ( $class && $this->default_class !== $class ) {
			return $this->check_class( $class );
		}

		if ( ! $this->default_class ) {
			throw new \InvalidArgumentException(
				'Cannot resolve class name if no class is given and no default class is available.'
			);
		}

		return $this->default_class;
	}

	/**
	 * Checks if the class with the given name is valid with respect to the defined base.
	 *
	 * @param string $class Fully qualified class name to be checked.
	 *
	 * @return string Fully qualified class name.
	 *
	 * @throws InvalidClass If the class with the given name is invalid with respect to the defined base.
	 */
	private function check_class( string $class ): string {

		if (
			! ( $this->base_is_class && $class === $this->base )
			&& ! is_subclass_of( $class, $this->base, true )
		) {
			throw new InvalidClass(
				"The class '{$class}' is invalid with respect to the defined base '{$this->base}'."
			);
		}

		return $class;
	}
}
