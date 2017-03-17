<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Factory;
use Throwable;
use WP_Error;

/**
 * Factory for WordPress error objects performing a fallback to WP_Error.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
final class FallbackErrorFactory implements ErrorFactory {

	/**
	 * @var Factory
	 */
	private $factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $default_class Optional. Fully qualified name of the default class. Defaults to
	 *                              ErrorFactory::DEFAULT_CLASS.
	 */
	public function __construct( string $default_class = ErrorFactory::DEFAULT_CLASS ) {

		$this->factory = GenericFactory::with_default_class( ErrorFactory::BASE, $default_class );
	}

	/**
	 * Returns a new WordPress error object, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to empty string.
	 *
	 * @return WP_Error WordPress error object.
	 *
	 * @throws Throwable if caught any and WP_DEBUG is set to true.
	 */
	public function create( array $args = [], string $class = '' ): WP_Error {

		try {
			$object = $this->factory->create( $args, $class );
		} catch ( Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				throw $e;
			}

			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->factory->create( $args, ErrorFactory::DEFAULT_CLASS );
		}

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $object;
	}
}
