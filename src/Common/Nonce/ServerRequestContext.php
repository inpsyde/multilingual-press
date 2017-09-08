<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Nonce;

use Inpsyde\MultilingualPress\Common\HTTP\PHPServerRequest;
use Inpsyde\MultilingualPress\Common\HTTP\ServerRequest;
use Inpsyde\MultilingualPress\Common\Nonce\Exception\ContextValueManipulationNotAllowed;
use Inpsyde\MultilingualPress\Common\Nonce\Exception\ContextValueNotSet;

/**
 * Nonce context implementation wrapping around the server request.
 *
 * @package Inpsyde\MultilingualPress\Common\Nonce
 * @since   3.0.0
 */
final class ServerRequestContext implements Context {

	/**
	 * @var ServerRequest
	 */
	private $request;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param ServerRequest $request Optional. Server request object. Defaults to null.
	 */
	public function __construct( ServerRequest $request = null ) {

		$this->request = $request ?? new PHPServerRequest();
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

		return $this->request->body_value( $name ) !== null;
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
	 * @throws ContextValueNotSet If there is no value with the given name.
	 */
	public function offsetGet( $name ) {

		if ( $this->offsetExists( $name ) ) {
			return $this->request->body_value( $name );
		}

		throw ContextValueNotSet::for_name( $name, 'read' );
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
	 * @throws ContextValueManipulationNotAllowed
	 */
	public function offsetSet( $name, $value ) {

		throw ContextValueManipulationNotAllowed::for_name( $name, 'set' );
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
	 * @throws ContextValueManipulationNotAllowed
	 */
	public function offsetUnset( $name ) {

		throw ContextValueManipulationNotAllowed::for_name( $name, 'unset' );
	}
}
