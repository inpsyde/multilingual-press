<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Route;

use Inpsyde\MultilingualPress\REST\Common;

/**
 * Route implementation using the route options data type.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Route
 * @since   3.0.0
 */
final class Route implements Common\Route\Route {

	/**
	 * @var Common\Arguments
	 */
	private $options;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string           $url     Base URL of the route.
	 * @param Common\Arguments $options Route options object.
	 */
	public function __construct( string $url, Common\Arguments $options ) {

		$this->url = trim( $url, '/' );

		$this->options = $options;
	}

	/**
	 * Returns an array of options for the route, or an array of arrays for multiple HTTP request methods.
	 *
	 * @see   register_rest_route()
	 * @since 3.0.0
	 *
	 * @return array Route options.
	 */
	public function options(): array {

		return $this->options->to_array();
	}

	/**
	 * Returns the base URL of the route.
	 *
	 * @see   register_rest_route()
	 * @since 3.0.0
	 *
	 * @return string Base URL of the route.
	 */
	public function url(): string {

		return $this->url;
	}
}
