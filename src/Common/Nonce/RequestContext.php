<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Nonce;

/**
 * Nonce context implementation wrapping around the $_GET and $_POST request superglobals.
 *
 * @package Inpsyde\MultilingualPress\Common\Nonce
 * @since   3.0.0
 */
final class RequestContext implements Context {

	/**
	 * @var ArrayContext
	 */
	private $context;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		if ( ! isset( $this->context ) ) {
			$this->context = new ArrayContext(
				( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) )
					? array_merge( $_GET, $_POST )
					: $_GET
			);
		}
	}

	/**
	 * Checks if a value with the given name exists.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $name The name of a value.
	 *
	 * @return bool Whether or not a value with the given name exists.
	 */
	public function offsetExists( $name ) {

		return $this->context->offsetExists( $name );
	}

	/**
	 * Returns the value with the given name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value.
	 *
	 * @return mixed The value with the given name.
	 */
	public function offsetGet( $name ) {

		return $this->context->offsetGet( $name );
	}

	/**
	 * Stores the given value with the given name.
	 *
	 * Manipulating values is not allowed.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name  The name of a value.
	 * @param mixed  $value The value.
	 *
	 * @return void
	 */
	public function offsetSet( $name, $value ) {

		$this->context->offsetSet( $name, $value );
	}

	/**
	 * Removes the value with the given name.
	 *
	 * Manipulating values is not allowed.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value.
	 *
	 * @return void
	 */
	public function offsetUnset( $name ) {

		$this->context->offsetUnset( $name );
	}
}
