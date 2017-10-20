<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache\Driver;

use Inpsyde\MultilingualPress\Cache\Item\Value;

/**
 * Cache driver implementation that vanish with request.
 * Useful in tests or to share things that should never survive a single request without polluting classes with
 * many static variables.
 *
 * @package Inpsyde\MultilingualPress\Cache\Driver
 * @since   3.0.0
 */
final class EphemeralCacheDriver implements CacheDriver {

	const NOOP = 8192;

	/**
	 * @var array
	 */
	private static $cache = [];

	/**
	 * @var bool
	 */
	private $is_network;

	/**
	 * @var bool
	 */
	private $noop;

	/**
	 * Constructor. Sets properties.
	 *
	 * @param int $flags Class flags.
	 */
	public function __construct( int $flags = 0 ) {

		$this->is_network = (bool) ( $flags & self::FOR_NETWORK );

		$this->noop = (bool) ( $flags & self::NOOP );
	}

	/**
	 * @return bool
	 */
	public function is_network(): bool {

		return $this->is_network;
	}

	/**
	 * Reads a value from the cache.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 *
	 * @return Value
	 */
	public function read( string $namespace, string $name ): Value {

		if ( $this->noop ) {
			return new Value();
		}

		$key = $this->build_key( $namespace, $name );

		return new Value( self::$cache[ $key ] ?? null, array_key_exists( $key, self::$cache ) );
	}

	/**
	 * Write a value to the cache.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 * @param mixed  $value     Cached value.
	 *
	 * @return bool
	 */
	public function write( string $namespace, string $name, $value ): bool {

		if ( $this->noop ) {
			return false;
		}

		$key = $this->build_key( $namespace, $name );

		self::$cache[ $key ] = $value;

		return true;
	}

	/**
	 * Delete a value from the cache.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 *
	 * @return bool
	 */
	public function delete( string $namespace, string $name ): bool {

		if ( $this->noop ) {
			return false;
		}

		$key = $this->build_key( $namespace, $name );
		unset( self::$cache[ $key ] );

		return true;
	}

	/**
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 *
	 * @return string
	 */
	private function build_key( string $namespace, string $name ) {

		$key = $namespace . $name;

		return $this->is_network ? "N_{$key}_" : 'S_' . get_current_blog_id() . "_{$key}_";
	}
}
