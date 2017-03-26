<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Service;

/**
 * Interface for collections of service providers that allows to perform operation on set of providers interacting
 * with an unique front interface.
 *
 * @package Inpsyde\MultilingualPress\Service
 * @since   3.0.0
 */
interface ServiceProviderCollection extends \Countable {

	/**
	 * @param ServiceProvider $provider The provider to be registered
	 *
	 * @return ServiceProviderCollection The ServiceProviderCollection instance that also contains the given provider.
	 *                                   Implementations might return a different instance.
	 */
	public function add_service_provider( ServiceProvider $provider ): ServiceProviderCollection;

	/**
	 * @param ServiceProvider $provider The provider to be registered
	 *
	 * @return ServiceProviderCollection The ServiceProviderCollection instance that does not contain the given provider.
	 *                                   Implementations might return a different instance.
	 */
	public function remove_service_provider( ServiceProvider $provider ): ServiceProviderCollection;

	/**
	 * Call the given method name on all the registered providers, or to the providers that implement the type given as
	 * second argument.
	 *
	 * @param string $method_name Name of the method to call on each provider
	 * @param array  $args        Variadic array of arguments that will be passed to provider method.
	 *
	 * @return void
	 */
	public function apply_method( string $method_name, ...$args );

	/**
	 * Call the given callback passing as first argument each registered provider. If provider type is given, all
	 * providers not implementing that type are skipped.
	 *
	 * @param callable $callback Callback to call
	 * @param array    $args     Variadic array of arguments tha twill be passed to provider method.
	 *
	 * @return void
	 */
	public function apply_callback( callable $callback, ...$args );

	/**
	 * Call the given callback passing as first argument each registered provider.
	 * Return an instance of ServiceProviderCollection that contains the providers that passed the filter.
	 *
	 * @param callable $callback Callback to call
	 * @param array    $args     Variadic array of arguments tha twill be passed to provider method.
	 *
	 * @return ServiceProviderCollection The filtered ServiceProviderCollection instance.
	 *                                   Implementations might return a different instance.
	 */
	public function filter( callable $callback, ...$args ): ServiceProviderCollection;

	/**
	 * Call the given callback passing as first argument each registered provider.
	 * Return an instance of ServiceProviderCollection that contains the providers obtained calling the callback.
	 *
	 * @param callable $callback Callback to call
	 * @param array    $args     Variadic array of arguments tha twill be passed to provider method.
	 *
	 * @return ServiceProviderCollection The transformed ServiceProviderCollection instance.
	 *                                   Implementations must ensure that returned collection only contains providers
	 *                                   objects and might return a different instance.
	 */
	public function map( callable $callback, ...$args ): ServiceProviderCollection;

	/**
	 * Call the given callback passing as first argument each registered provider and as second argument the result
	 * of previous callback call.
	 * The first provider receives
	 *
	 * @param callable $callback Callback to call
	 * @param array    $initial  Passed as second argument to callback when first argument is the first provider.
	 *
	 * @return mixed The return value of given callback when called with las provider.
	 *               If collection is empty implementation must return given initial value.
	 */
	public function reduce( callable $callback, $initial = null );
}