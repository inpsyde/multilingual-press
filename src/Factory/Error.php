<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Factory;

use Exception;
use Inpsyde\MultilingualPress\Common\Factory;
use WP_Error;

/**
 * Factory for WordPress error objects.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
final class Error implements Factory {

	/**
	 * Fully qualified name of the base (class).
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const BASE = '\WP_Error';

	/**
	 * @var Factory
	 */
	private $factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $default_class Optional. Fully qualified name of the default class. Defaults to self::BASE.
	 */
	public function __construct( $default_class = self::BASE ) {

		$this->factory = GenericFactory::with_default_class( self::BASE, (string) $default_class );
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
	 * @throws Exception if caught any and WP_DEBUG is set to true.
	 */
	public function create( array $args = [], $class = '' ) {

		try {
			$object = $this->factory->create( $args, (string) $class );
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				throw $e;
			}

			return $this->factory->create( $args, self::BASE );
		}

		return $object;
	}
}
