<?php # -*- coding: utf-8 -*-

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
	public function __construct( $default_class = ErrorFactory::DEFAULT_CLASS ) {

		$this->factory = GenericFactory::with_default_class( ErrorFactory::BASE, (string) $default_class );
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
	public function create( array $args = [], $class = '' ) {

		try {
			$object = $this->factory->create( $args, (string) $class );
		} catch ( Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				throw $e;
			}

			return $this->factory->create( $args, ErrorFactory::DEFAULT_CLASS );
		}

		return $object;
	}
}
