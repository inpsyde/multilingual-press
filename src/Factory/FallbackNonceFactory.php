<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Factory;

use Inpsyde\MultilingualPress\Common\Factory;
use Inpsyde\MultilingualPress\Common\Nonce\Nonce;
use Throwable;

/**
 * Factory for nonce objects performing a fallback to WPNonce.
 *
 * @package Inpsyde\MultilingualPress\Factory
 * @since   3.0.0
 */
final class FallbackNonceFactory implements NonceFactory {

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
	 *                              NonceFactory::DEFAULT_CLASS.
	 */
	public function __construct( $default_class = NonceFactory::DEFAULT_CLASS ) {

		$this->factory = GenericFactory::with_default_class( NonceFactory::BASE, (string) $default_class );
	}

	/**
	 * Returns a new nonce object, instantiated with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args  Optional. Constructor arguments. Defaults to empty array.
	 * @param string $class Optional. Fully qualified class name. Defaults to empty string.
	 *
	 * @return Nonce Nonce object.
	 *
	 * @throws Throwable if caught any and WP_DEBUG is set to true.
	 */
	public function create( array $args = [], string $class = '' ): Nonce {

		try {
			$object = $this->factory->create( $args, (string) $class );
		} catch ( Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				throw $e;
			}

			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->factory->create( $args, NonceFactory::DEFAULT_CLASS );
		}

		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $object;
	}
}
