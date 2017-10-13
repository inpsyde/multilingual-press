<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Route;

use Inpsyde\MultilingualPress\REST\Common;

/**
 * Registry implementation for routes in a common namespace.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Route
 * @since   3.0.0
 */
final class Registry implements Common\Route\Registry {

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $namespace Namespace.
	 */
	public function __construct( string $namespace ) {

		$this->namespace = $namespace;
	}

	/**
	 * Registers the given routes.
	 *
	 * @since 3.0.0
	 *
	 * @param Common\Route\Collection $routes Route collection object.
	 *
	 * @return void
	 */
	public function register_routes( Common\Route\Collection $routes ) {

		/**
		 * Fires right before the routes are registered.
		 *
		 * @since 3.0.0
		 *
		 * @param Common\Route\Collection $routes    Route collection object.
		 * @param string                  $namespace Namespace.
		 */
		do_action( Common\Route\Registry::ACTION_REGISTER, $routes, $this->namespace );

		/** @var Common\Route\Route $route */
		foreach ( $routes as $route ) {
			register_rest_route( $this->namespace, $route->url(), $route->options() );
		}
	}
}
