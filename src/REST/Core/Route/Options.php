<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Core\Route;

use Inpsyde\MultilingualPress\REST\Common\Arguments;
use Inpsyde\MultilingualPress\REST\Common\Endpoint\RequestHandler;
use Inpsyde\MultilingualPress\REST\Common\Endpoint\Schema;
use Inpsyde\MultilingualPress\REST\Common\Route\ExtensibleOptions;
use Inpsyde\MultilingualPress\REST\Common\Route\SchemaAwareOptions;

/**
 * Implementation of extensible and schema-aware route options.
 *
 * @package Inpsyde\MultilingualPress\REST\Core\Route
 * @since   3.0.0
 */
final class Options implements ExtensibleOptions, SchemaAwareOptions {

	/**
	 * Default comma-separated HTTP verbs.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const DEFAULT_METHODS = \WP_REST_Server::READABLE;

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param array $options Optional. Route options. Defaults to empty array.
	 */
	public function __construct( array $options = [] ) {

		if ( $options ) {
			$this->options[] = $options;
		}
	}

	/**
	 * Returns a new route options object, instantiated with an entry according to the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param RequestHandler $handler Optional. Request handler object. Defaults to null.
	 * @param Arguments      $args    Optional. Endpoint arguments object. Defaults to null.
	 * @param string         $methods Optional. Comma-separated HTTP verbs. Defaults to self::DEFAULT_METHODS.
	 * @param array          $options Optional. Additional options array. Defaults to empty array.
	 *
	 * @return Options Route options object.
	 */
	public static function from_arguments(
		RequestHandler $handler = null,
		Arguments $args = null,
		string $methods = self::DEFAULT_METHODS,
		array $options = []
	): Options {

		if ( $handler ) {
			$options['callback'] = [ $handler, 'handle_request' ];
		}

		if ( $args ) {
			$options['args'] = $args->to_array();
		}

		$options['methods'] = $methods;

		return new self( $options );
	}

	/**
	 * Returns a new route options object with the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback Endpoint callback.
	 * @param array    $args     Optional. Endpoint arguments. Defaults to empty array.
	 * @param string   $methods  Optional. Comma-separated HTTP verbs. Defaults to self::DEFAULT_METHODS.
	 * @param array    $options  Optional. Route options. Defaults to empty array.
	 *
	 * @return Options Route options object.
	 */
	public static function with_callback(
		callable $callback,
		array $args = [],
		string $methods = self::DEFAULT_METHODS,
		array $options = []
	): Options {

		return new self( compact( 'methods', 'callback', 'args' ) + $options );
	}

	/**
	 * Returns a new route options object with a schema callback on the given object.
	 *
	 * @since 3.0.0
	 *
	 * @param Schema $schema  Schema object.
	 * @param array  $options Optional. Route options. Defaults to empty array.
	 *
	 * @return SchemaAwareOptions Route options object.
	 */
	public static function with_schema( Schema $schema, array $options = [] ): SchemaAwareOptions {

		return ( new self( $options ) )->set_schema( $schema );
	}

	/**
	 * Adds the given route options as new entry to the internal options.
	 *
	 * @since 3.0.0
	 *
	 * @param array $options Route options.
	 *
	 * @return ExtensibleOptions Options object.
	 */
	public function add( array $options ): ExtensibleOptions {

		$this->options = array_merge( $this->options, [ $options ] );

		return $this;
	}

	/**
	 * Sets the schema callback in the options to the according callback on the given schema object.
	 *
	 * @since 3.0.0
	 *
	 * @param Schema $schema Schema object.
	 *
	 * @return SchemaAwareOptions Options object.
	 */
	public function set_schema( Schema $schema ): SchemaAwareOptions {

		$this->options['schema'] = [ $schema, 'definition' ];

		return $this;
	}

	/**
	 * Returns the route options in array form.
	 *
	 * @since 3.0.0
	 *
	 * @return array Route options.
	 */
	public function to_array(): array {

		return $this->options;
	}
}
