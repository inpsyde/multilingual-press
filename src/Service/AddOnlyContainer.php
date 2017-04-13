<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Service;

use Inpsyde\MultilingualPress\Service\Exception\InvalidValueWriteAccess;
use Inpsyde\MultilingualPress\Service\Exception\LateAccessToNotSharedService;
use Inpsyde\MultilingualPress\Service\Exception\ValueNotFound;
use Inpsyde\MultilingualPress\Service\Exception\WriteAccessOnLockedContainer;

/**
 * Add-only container implementation to be used for dependency management.
 *
 * @package Inpsyde\MultilingualPress\Service
 * @since   3.0.0
 */
final class AddOnlyContainer implements Container {

	/**
	 * @var callable[]
	 */
	private $factories = [];

	/**
	 * @var bool
	 */
	private $is_bootstrapped = false;

	/**
	 * @var bool
	 */
	private $is_locked = false;

	/**
	 * @var bool[]
	 */
	private $shared = [];

	/**
	 * @var array
	 */
	private $values = [];

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param array $values Initial values or factory callbacks to be stored.
	 */
	public function __construct( array $values = [] ) {

		foreach ( $values as $name => $value ) {
			$this->offsetSet( $name, $value );
		}
	}

	/**
	 * Checks if a value or factory callback with the given name exists.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value or factory callback.
	 *
	 * @return bool Whether or not a value or factory callback with the given name exists.
	 */
	public function offsetExists( $name ) {

		return array_key_exists( $name, $this->values ) || isset( $this->factories[ $name ] );
	}

	/**
	 * Alias offsetExists for PSR-11 compatibility.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value or factory callback.
	 *
	 * @return bool Whether or not a value or factory callback with the given name exists.
	 */
	public function has( $name ) {

		return $this->offsetExists( $name );
	}

	/**
	 * Returns the value or factory callback with the given name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value or factory callback.
	 *
	 * @return mixed The value or factory callback with the given name.
	 *
	 * @throws ValueNotFound                if there is no value or factory callback with the given name.
	 * @throws LateAccessToNotSharedService if a not shared value or factory callback is to be accessed on a
	 *                                      bootstrapped container.
	 */
	public function offsetGet( $name ) {

		if ( ! $this->offsetExists( $name ) ) {
			throw ValueNotFound::for_value( $name, 'read' );
		}

		if ( $this->is_bootstrapped && ! array_key_exists( $name, $this->shared ) ) {
			throw LateAccessToNotSharedService::for_value( $name, 'read' );
		}

		if ( ! array_key_exists( $name, $this->values ) ) {

			$this->values[ $name ] = $this->factories[$name]( $this );

			if ( $this->is_locked ) {
				unset( $this->factories[ $name ] );
			}
		}

		return $this->values[ $name ];
	}

	/**
	 * Alias offsetGet for PSR-11 compatibility.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value or factory callback.
	 *
	 * @return mixed The value or factory callback with the given name.
	 */
	public function get( $name ) {

		return $this->offsetGet( $name );
	}

	/**
	 * Stores the given value or factory callback with the given name.
	 *
	 * Scalar values will get shared automatically.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name  The name of a value or factory callback.
	 * @param mixed  $value The value or factory callback.
	 *
	 * @return void
	 *
	 * @throws WriteAccessOnLockedContainer if the container is locked.
	 * @throws InvalidValueWriteAccess      if there already is a value with the given name.
	 */
	public function offsetSet( $name, $value ) {

		if ( $this->is_locked ) {
			throw WriteAccessOnLockedContainer::for_value( $name, 'set' );
		}

		if ( array_key_exists( $name, $this->values ) ) {
			throw InvalidValueWriteAccess::for_immutable_write_attempt( $name, 'set' );
		}

		if ( is_callable( $value ) ) {
			$this->factories[ $name ] = $value;

			return;
		}

		$this->values[ $name ] = $value;

		if ( is_scalar( $value ) && ! array_key_exists( $name, $this->shared ) ) {
			$this->shared[ $name ] = true;
		}
	}

	/**
	 * Removes the value or factory callback with the given name.
	 *
	 * Removing values or factory callbacks is not allowed.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name The name of a value or factory callback.
	 *
	 * @return void
	 *
	 * @throws InvalidValueWriteAccess
	 */
	public function offsetUnset( $name ) {

		throw InvalidValueWriteAccess::for_immutable_unset_attempt( $name );
	}

	/**
	 * Bootstraps (and locks) the container.
	 *
	 * Only shared values and factory callbacks are accessible from now on.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function bootstrap() {

		$this->lock();

		$this->is_bootstrapped = true;
	}

	/**
	 * Replaces the factory callback with the given name with the given factory callback.
	 *
	 * The new factory callback will receive as first argument the object created by the current factory, and as second
	 * argument the container.
	 *
	 * @since 3.0.0
	 *
	 * @param string   $name        The name of an existing factory callback.
	 * @param callable $new_factory The new factory callback.
	 *
	 * @return Container Container instance.
	 *
	 * @throws WriteAccessOnLockedContainer if the container is locked.
	 * @throws ValueNotFound if there is no factory callback with the given name.
	 * @throws InvalidValueWriteAccess if there already is a value with the given name.
	 */
	public function extend( string $name, callable $new_factory ): Container {

		if ( $this->is_locked ) {
			throw WriteAccessOnLockedContainer::for_value( $name, 'extend' );
		}

		if ( ! array_key_exists( $name, $this->factories ) ) {
			throw ValueNotFound::for_factory( $name, 'extend' );
		}

		if ( array_key_exists( $name, $this->values ) ) {
			throw InvalidValueWriteAccess::for_immutable_write_attempt( $name, 'extend' );
		}

		$current_factory = $this->factories[ $name ];

		$this->factories[ $name ] = function ( Container $container ) use ( $new_factory, $current_factory ) {

			return $new_factory( $current_factory( $container ), $container );
		};

		return $this;
	}

	/**
	 * Locks the container.
	 *
	 * A locked container cannot be manipulated anymore. All stored values and factory callbacks are still accessible.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function lock() {

		$this->is_locked = true;
	}

	/**
	 * Stores the given value or factory callback with the given name, and defines it to be accessible even after the
	 * container has been bootstrapped.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name  The name of a value or factory callback.
	 * @param mixed  $value The value or factory callback.
	 *
	 * @return Container Container instance.
	 */
	public function share( string $name, $value ): Container {

		$this->offsetSet( $name, $value );

		$this->shared[ $name ] = true;

		return $this;
	}
}
