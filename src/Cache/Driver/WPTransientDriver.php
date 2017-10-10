<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache\Driver;

use Inpsyde\MultilingualPress\Cache\Item\Value;

/**
 * A driver implementation that uses WordPress transient functions.
 *
 * Two gotchas:
 *   1) when using external object cache, is better to use object cache driver, WordPress will use object cache anyway
 *   2) Avoid to use store `false` when using this driver because it can't be disguised from a cache miss.
 *
 * @package Inpsyde\MultilingualPress\Cache\Driver
 * @since   3.0.0
 */
final class WPTransientDriver implements CacheDriver {

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
	 * @param string $name      Cache item.
	 *
	 * @return Value
	 */
	public function read( string $namespace, string $name ): Value {

		$key = $this->build_key( $namespace, $name );

		/*
		 * When using external object cache, WP uses it for transient anyway.
		 * Using cache methods directly helps to disguise the $found value.
		 */
		if ( wp_using_ext_object_cache() ) {

			$group = $this->is_network ? 'site-transient' : 'transient';
			$found = false;
			$value = wp_cache_get( $key, $group, true, $found );

			return new Value( $value, $found );
		}

		$value = $this->is_network ? get_site_transient( $key ) : get_transient( $key );

		/*
		 * Transient do not allow to lookup for "hit" or "miss", so there's not way to know if a `false` was returned
		 * because cached or because value not found.
		 * For consistence with other drivers we return null as value when transient functions returns false.
		 * It means that a cached `null` value can be disguised, but not a cached `false`.
		 * Avoid to use store `false` in cache when using this driver.
		 */
		$found = false !== $value;

		return new Value( $found ? $value : null, $found );
	}

	/**
	 * Write a value to the cache.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item namespace.
	 * @param mixed  $value     Value to cache.
	 *
	 * @return bool
	 */
	public function write( string $namespace, string $name, $value ): bool {

		return $this->is_network
			? set_site_transient( $this->build_key( $namespace, $name ), $value, 0 )
			: set_transient( $this->build_key( $namespace, $name ), $value, 0 );
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

		return $this->is_network
			? delete_site_transient( $this->build_key( $namespace, $name ) )
			: delete_transient( $this->build_key( $namespace, $name ) );
	}

	/**
	 * Site transients limits key to 40 characters or less.
	 * This method builds a key that is unique per namespace and key and is 39 characters or less.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 *
	 * @return string
	 */
	private function build_key( string $namespace, string $name ): string {

		$full   = $namespace . $name;
		$prefix = substr( $full, 0, 6 );

		return "{$prefix}_" . md5( $full );
	}
}
