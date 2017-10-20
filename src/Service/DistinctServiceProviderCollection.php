<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Service;

/**
 * Service provider collection implementation that ensures each provider is not present more than once.
 *
 * @package Inpsyde\MultilingualPress\Service
 * @since   3.0.0
 */
final class DistinctServiceProviderCollection implements ServiceProviderCollection {

	/**
	 * @var \SplObjectStorage
	 */
	private $storage;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->storage = new \SplObjectStorage();
	}

	/**
	 * Adds the given service provider to the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param ServiceProvider $provider The provider to be registered.
	 *
	 * @return ServiceProviderCollection The instance that also contains the given provider.
	 */
	public function add_service_provider( ServiceProvider $provider ): ServiceProviderCollection {

		$this->storage->attach( $provider );

		return $this;
	}

	/**
	 * Removes the given service provider from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param ServiceProvider $provider The provider to be registered.
	 *
	 * @return ServiceProviderCollection The instance that does not contain the given provider.
	 */
	public function remove_service_provider( ServiceProvider $provider ): ServiceProviderCollection {

		$this->storage->detach( $provider );

		return $this;
	}

	/**
	 * Calls the method with the given name on all registered providers, and passes on potential further arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string $method_name Name of the method to call on each provider.
	 * @param array  ...$args     Variadic array of arguments that will be passed to provider method.
	 *
	 * @return void
	 */
	public function apply_method( string $method_name, ...$args ) {

		$this->storage->rewind();

		while ( $this->storage->valid() ) {
			/** @var callable $method */
			$method = [ $this->storage->current(), $method_name ];
			$method( ...$args );

			$this->storage->next();
		}
	}

	/**
	 * Executes the given callback for all registered providers, and passes along potential further arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Callback to execute.
	 * @param array    ...$args  Variadic array of arguments that will be passed to provider method.
	 *
	 * @return void
	 */
	public function apply_callback( callable $callback, ...$args ) {

		$this->storage->rewind();

		while ( $this->storage->valid() ) {
			$callback( $this->storage->current(), ...$args );

			$this->storage->next();
		}
	}

	/**
	 * Executes the given callback for all registered providers, and returns the instance that contains the providers
	 * that passed the filtering.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Callback to execute.
	 * @param array    ...$args  Variadic array of arguments that will be passed to provider method.
	 *
	 * @return ServiceProviderCollection The filtered instance.
	 */
	public function filter( callable $callback, ...$args ): ServiceProviderCollection {

		$collection = new static();

		$this->storage->rewind();

		while ( $this->storage->valid() ) {
			/** @var ServiceProvider $provider */
			$provider = $this->storage->current();

			if ( $callback( $provider, ...$args ) ) {
				$collection->add_service_provider( $provider );
			}

			$this->storage->next();
		}

		return $collection;
	}

	/**
	 * Executes the given callback for all registered providers, and returns the instance that contains the providers
	 * obtained.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Callback to execute.
	 * @param array    ...$args  Variadic array of arguments that will be passed to provider method.
	 *
	 * @return ServiceProviderCollection The transformed instance.
	 *
	 * @throws \UnexpectedValueException If a given callback did not return a service provider instance.
	 */
	public function map( callable $callback, ...$args ): ServiceProviderCollection {

		$collection = new static();

		$this->storage->rewind();

		while ( $this->storage->valid() ) {
			$provider = $callback( $this->storage->current(), ...$args );
			if ( ! $provider instanceof ServiceProvider ) {
				throw new \UnexpectedValueException(
					__METHOD__ . ' expects transformation callbacks to return a service provider instance.'
				);
			}

			$collection->add_service_provider( $provider );

			$this->storage->next();
		}

		return $collection;
	}

	/**
	 * Executes the given callback for all registered providers, and passes along the result of previous callback.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Callback to execute.
	 * @param mixed    $initial  Initial value passed as second argument to the callback.
	 *
	 * @return mixed The return value of final callback execution.
	 */
	public function reduce( callable $callback, $initial = null ) {

		$this->storage->rewind();

		$carry = $initial;

		while ( $this->storage->valid() ) {
			$carry = $callback( $carry, $this->storage->current() );

			$this->storage->next();
		}

		return $carry;
	}

	/**
	 * Returns the number of providers in the collection.
	 *
	 * @since 3.0.0
	 *
	 * @return int The number of providers in the collection.
	 */
	public function count(): int {

		return $this->storage->count();
	}
}
