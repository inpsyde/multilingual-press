<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Service;

/**
 * Interface for all service provider collection implementations.
 *
 * @package Inpsyde\MultilingualPress\Service
 * @since   3.0.0
 */
interface ServiceProviderCollection extends \Countable {

	/**
	 * Adds the given service provider to the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param ServiceProvider $provider The provider to be registered
	 *
	 * @return ServiceProviderCollection The instance that also contains the given provider.
	 */
	public function add_service_provider( ServiceProvider $provider ): ServiceProviderCollection;

	/**
	 * Removes the given service provider from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param ServiceProvider $provider The provider to be registered
	 *
	 * @return ServiceProviderCollection The instance that does not contain the given provider.
	 */
	public function remove_service_provider( ServiceProvider $provider ): ServiceProviderCollection;

	/**
	 * Calls the method with the given name on all registered providers, and passes on potential further arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string $method_name Name of the method to call on each provider.
	 * @param array  $args        Variadic array of arguments that will be passed to provider method.
	 *
	 * @return void
	 */
	public function apply_method( string $method_name, ...$args );

	/**
	 * Executes the given callback for all registered providers, and passes along potential further arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Callback to execute.
	 * @param array    $args     Variadic array of arguments that will be passed to provider method.
	 *
	 * @return void
	 */
	public function apply_callback( callable $callback, ...$args );

	/**
	 * Executes the given callback for all registered providers, and returns the instance that contains the providers
	 * that passed the filtering.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Callback to execute.
	 * @param array    $args     Variadic array of arguments that will be passed to provider method.
	 *
	 * @return ServiceProviderCollection The filtered instance.
	 */
	public function filter( callable $callback, ...$args ): ServiceProviderCollection;

	/**
	 * Executes the given callback for all registered providers, and returns the instance that contains the providers
	 * obtained.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Callback to execute.
	 * @param array    $args     Variadic array of arguments that will be passed to provider method.
	 *
	 * @return ServiceProviderCollection The transformed instance.
	 */
	public function map( callable $callback, ...$args ): ServiceProviderCollection;

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
	public function reduce( callable $callback, $initial = null );
}
