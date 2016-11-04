<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Nonce;

use Inpsyde\MultilingualPress\Common\Nonce\Exception\ContextValueManipulationNotAllowedException;
use Inpsyde\MultilingualPress\Common\Nonce\Exception\ContextValueNotSetException;

/**
 * Array-based nonce context implementation.
 *
 * @package Inpsyde\MultilingualPress\Common\Nonce
 * @since   3.0.0
 */
final class ArrayContext implements Context {

	/**
	 * @var array
	 */
	private $data;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Context data.
	 */
	public function __construct( array $data ) {

		if ( ! isset( $this->data ) ) {
			$this->data = $data;
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

		return array_key_exists( $name, $this->data );
	}

	/**
	 * Returns the value with the given name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value.
	 *
	 * @return mixed The value with the given name.
	 *
	 * @throws ContextValueNotSetException if there is no value with the given name.
	 */
	public function offsetGet( $name ) {

		if ( $this->offsetExists( $name ) ) {
			return $this->data[ $name ];
		}

		throw ContextValueNotSetException::for_name( $name, 'read' );
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
	 *
	 * @throws ContextValueManipulationNotAllowedException
	 */
	public function offsetSet( $name, $value ) {

		throw ContextValueManipulationNotAllowedException::for_name( $name, 'set' );
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
	 *
	 * @throws ContextValueManipulationNotAllowedException
	 */
	public function offsetUnset( $name ) {

		throw ContextValueManipulationNotAllowedException::for_name( $name, 'unset' );
	}
}
