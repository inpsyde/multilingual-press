<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache\Driver;

use Inpsyde\MultilingualPress\Cache\Item\Value;

/**
 * @package Inpsyde\MultilingualPress\Cache\Driver
 * @since   3.0.0
 */
final class WPObjectCacheDriver implements CacheDriver {

	/**
	 * @var string[]
	 */
	private static $global_namespaces = [];

	/**
	 * @var bool
	 */
	private $is_network;

	/**
	 * Constructor.
	 *
	 * @param int $flags Object flags.
	 */
	public function __construct( int $flags = 0 ) {

		$this->is_network = (bool) ( $flags & self::FOR_NETWORK );
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

		$this->maybe_global( $namespace );
		$found = false;
		$value = wp_cache_get( $name, $namespace, true, $found );
		if ( false === $value && ! $found ) {
			$value = null;
		}

		return new Value( $value, $found );
	}

	/**
	 * Write a value to the cache.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 * @param mixed  $value     Value to cache.
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
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 *
	 * @return bool
	 */
	public function delete( string $namespace, string $name ): bool {

		$this->maybe_global( $namespace );

		return (bool) wp_cache_delete( $name, $namespace );
	}

	/**
	 * @param string $namespace Cache item namespace.
	 */
	private function maybe_global( string $namespace ) {

		if ( $this->is_network && ! in_array( $namespace, self::$global_namespaces, true ) ) {
			self::$global_namespaces[] = $namespace;
			wp_cache_add_global_groups( $namespace );
		}
	}
}
