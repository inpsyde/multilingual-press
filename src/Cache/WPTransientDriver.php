<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache;

/**
 * A driver implementation that uses WordPress transient functions.
 *
 * Two gotchas:
 *   1) when using external object cache, is better to use object cache driver, WordPress will use object cache anyway
 *   2) Avoid to use store `false` when using this driver because it can't be disguised from a cache miss.
 *
 * @package Inpsyde\MultilingualPress\Cache
 * @since   3.0.0
 */
final class WPTransientDriver implements WPCacheDriver {

	/**
	 * @var bool
	 */
	private $sitewide;

	/**
	 * Constructor.
	 *
	 * @param int $flags
	 */
	public function __construct( int $flags = 0 ) {

		$this->sitewide = (bool) ( $flags & self::SITEWIDE );
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

		$key = $this->build_key( $namespace, $name );

		/*
		 * When using external object cache, WP uses it for transient anyway.
		 * Using cache methods directly helps to disguise the $found value.
		 */
		if ( wp_using_ext_object_cache() ) {

			$group = $this->sitewide ? 'site-transient' : 'transient';
			$found = false;
			$value = wp_cache_get( $key, $group, true, $found );

			return [ $value, $found ];
		}

		$value = $this->sitewide ? get_site_transient( $key ) : get_transient( $key );

		/*
		 * Transient do not allow to lookup for "hit" or "miss", so there's not way to know if a `false` was returned
		 * because cached or because value not found.
		 * For consistence with other drivers we return null as value when transient functions returns false.
		 * It means that a cached `null` value can be disguised, but not a cached `false`.
		 * Avoid to use store `false` in cache when using this driver.
		 */
		$found = $value !== false;

		return [ $found, $found ? $value : null ];
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

		return $this->sitewide
			? set_site_transient( $this->build_key( $namespace, $name ), $value, 0 )
			: set_transient( $this->build_key( $namespace, $name ), $value, 0 );
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

		return $this->sitewide
			? delete_site_transient( $this->build_key( $namespace, $name ) )
			: delete_transient( $this->build_key( $namespace, $name ) );
	}

	/**
	 * Site transients limits key to 40 characters or less.
	 * This method builds a key that is unique per namespace and key and is 39 characters or less.
	 *
	 * @param string $namespace
	 * @param string $name
	 *
	 * @return string
	 */
	private function build_key( string $namespace, string $name ): string {

		$full   = $namespace . $name;
		$prefix = substr( $full, 0, 6 );

		return "{$prefix}_" . md5( $full );
	}
}