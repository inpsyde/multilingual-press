<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache\Driver;

/**
 * Cache driver implementation that vanish with request.
 * Useful in tests or to share things that should never survive a single request without polluting classes with
 * many static variables.
 *
 * @package Inpsyde\MultilingualPress\Cache
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
	 * @param int $flags
	 */
	public function __construct( int $flags = 0 ) {

		$this->is_network = (bool) ( $flags & self::FOR_NETWORK );
		$this->noop       = (bool) ( $flags & self::NOOP );
	}

	/**
	 * @return bool
	 */
	public function is_sidewide(): bool {

		return $this->is_network;
	}

	/**
	 * Reads a value from the cache.
	 *
	 * @param string $namespace
	 * @param string $name
	 *
	 * @return array Two item array where first item is the read value and the second is a boolean telling if the read
	 *               was a cache it (to disguise cache null)
	 */
	public function read( string $namespace, string $name ): array {

		if ( $this->noop ) {
			return [ null, false ];
		}

		$key   = $this->build_key( $namespace, $name );
		$found = array_key_exists( $key, self::$cache );

		return [ $found ? self::$cache[ $key ] : null, $found ];
	}

	/**
	 * Write a value to the cache.
	 *
	 * @param string $namespace
	 * @param string $name
	 * @param        $value
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
	 * @param string $namespace
	 * @param string $name
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
	 * @param string $namespace
	 * @param string $name
	 *
	 * @return string
	 */
	private function build_key( string $namespace, string $name ) {

		$key = $namespace . $name;

		return $this->is_network ? "W_{$key}_" : 'S_' . get_current_blog_id() . "_{$key}_";
	}
}