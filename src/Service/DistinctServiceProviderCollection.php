<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Service;

/**
 * Service providers collection implementation that ensure same instance of provider is not present more than once
 * in the collection.
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
	 * Constructor. Initialize the properties.
	 */
	public function __construct() {

		$this->storage = new \SplObjectStorage();
	}

	/**
	 * @param ServiceProvider $provider The provider to be registered
	 *
	 * @return ServiceProviderCollection Itself.
	 */
	public function add_service_provider( ServiceProvider $provider ): ServiceProviderCollection {

		$this->storage->attach( $provider );

		return $this;
	}

	/**
	 * @param ServiceProvider $provider The provider to be registered
	 *
	 * @return ServiceProviderCollection Itself.
	 */
	public function remove_service_provider( ServiceProvider $provider ): ServiceProviderCollection {

		$this->storage->detach( $provider );

		return $this;
	}

	/**
	 * Call the given method name on all the contained providers.
	 *
	 * @param string $method_name Name of the method to call on each provider
	 * @param array  $args        Variadic array of arguments that will be passed to provider method.
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
	 * Call the given callback passing as first argument each contained provider.
	 *
	 * @param callable $callback Callback to call
	 * @param array    $args     Variadic array of arguments that will be passed to provider method.
	 *
	 * @return void
	 */
	public function apply_callback( callable $callback, ...$args ) {

		$this->storage->rewind();

		// adds null as a placeholder that will be replaced by each provider in the loop
		array_unshift( $args, null );

		while ( $this->storage->valid() ) {
			$args[0] = $this->storage->current();
			$callback( ...$args );
			$this->storage->next();
		}
	}

	/**
	 * Call the given callback passing as first argument each registered provider.
	 * Return an instance of ServiceProviderCollection that contains the providers that passed the filter.
	 *
	 * @param callable $callback Callback to call
	 * @param array    $args     Variadic array of arguments tha twill be passed to provider method.
	 *
	 * @return ServiceProviderCollection A new filtered ServiceProviderCollection instance.
	 */
	public function filter( callable $callback, ...$args ): ServiceProviderCollection {

		$collection = new static();
		$this->storage->rewind();

		array_unshift( $args, null ); // adds null as a placeholder that will be replaced by each provider in the loop

		while ( $this->storage->valid() ) {
			/** @var ServiceProvider $provider */
			$provider = $this->storage->current();
			$args[0]  = $provider;
			if ( $callback( ...$args ) ) {
				$collection->add_service_provider( $provider );
			}
			$this->storage->next();
		}

		return $collection;
	}

	/**
	 * Call the given callback passing as first argument each registered provider.
	 * Return an instance of ServiceProviderCollection that contains the providers obtained calling the callback.
	 *
	 * @param callable $callback Callback to call
	 * @param array    $args     Variadic array of arguments tha twill be passed to provider method.
	 *
	 * @return ServiceProviderCollection A new transformed ServiceProviderCollection instance.
	 */
	public function map( callable $callback, ...$args ): ServiceProviderCollection {

		$collection = new static();
		$this->storage->rewind();

		array_unshift( $args, null ); // adds null as a placeholder that will be replaced by each provider in the loop

		while ( $this->storage->valid() ) {
			/** @var ServiceProvider $provider */
			$provider = $this->storage->current();
			$args[0]  = $provider;
			$provider = $callback( ...$args );
			if ( ! $provider instanceof ServiceProvider ) {
				throw new \UnexpectedValueException(
					sprintf(
						'Transformation callbacks in %s must return and instance of ServiceProvider.',
						__METHOD__
					)
				);
			}

			$collection->add_service_provider( $provider );
			$this->storage->next();
		}

		return $collection;
	}

	/**
	 * Call the given callback passing as second argument each registered provider and as first argument the return
	 * value of previous callback call.
	 *
	 * @param callable $callback Callback to call
	 * @param array    $initial  Passed as first argument to callback when its second argument is the first provider.
	 *
	 * @return mixed The return value of given callback when called with last provider.
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
	 * Return the number of providers contained in the collection.
	 */
	public function count(): int {

		return $this->storage->count();
	}
}