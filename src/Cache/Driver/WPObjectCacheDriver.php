<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache\Driver;

/**
 * @package Inpsyde\MultilingualPress\Cache
 * @since   3.0.0
 */
final class WPObjectCacheDriver implements CacheDriver {

	private static $global_namespaces = [];

	/**
	 * @var bool
	 */
	private $is_network;

	/**
	 * Constructor.
	 *
	 * @param int $flags
	 */
	public function __construct( int $flags = 0 ) {

		$this->is_network = (bool) ( $flags & self::FOR_NETWORK );
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

		$this->maybe_global( $namespace );
		$found = false;
		$value = wp_cache_get( $name, $namespace, true, $found );
		if ( $value === false && ! $found ) {
			$value = null;
		}

		return [ $value, $found ];
	}

	/**
	 * Write a value to the cache.
	 *
	 * @param string $namespace
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function write( string $namespace, string $name, $value ): bool {

		$this->maybe_global( $namespace );

		return (bool) wp_cache_set( $name, $value, $namespace, 0 );
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

		$this->maybe_global( $namespace );

		return (bool) wp_cache_delete( $name, $namespace );
	}

	/**
	 * @param string $namespace
	 */
	private function maybe_global( string $namespace ) {

		if ( $this->is_network && ! in_array( $namespace, self::$global_namespaces, true ) ) {
			self::$global_namespaces[] = $namespace;
			wp_cache_add_global_groups( $namespace );
		}
	}
}