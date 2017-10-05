<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Pool;

use Inpsyde\MultilingualPress\Cache\Item\CacheItem;

/**
 * Interface for all cache implementations.
 */
interface CachePool {

	/**
	 * Return poll namespace.
	 *
	 * @return string
	 */
	public function namespace(): string;

	/**
	 * Check if the cache pool is sitewide.
	 *
	 * @return bool
	 */
	public function is_sitewide(): bool;

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string $key The unique key of this item in the cache.
	 *
	 * @return CacheItem The cache item identified by given key
	 */
	public function item( string $key ): CacheItem;

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string     $key     The unique key of this item in the cache.
	 * @param mixed|null $default Default value to return if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get( string $key, $default = null );

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string[]   $keys    The unique keys of item in the cache.
	 * @param mixed|null $default Default value to assign to each item if the key does not exist.
	 *
	 * @return array The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get_many( array $keys, $default = null ): array;

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string   $key   The key of the item to store.
	 * @param mixed    $value Optional. The value of the item to store, must be serializable. Defaults to null.
	 * @param null|int $ttl   Optional. The TTL value of this item.
	 *
	 * @return CacheItem The cache item just wrote to
	 */
	public function set( string $key, $value = null, int $ttl = null ): CacheItem;

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param string $key The unique cache key of the item to delete.
	 *
	 * @return bool True if the item was successfully removed. False if there was an error.
	 */
	public function delete( string $key ): bool;

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * @param string $key The cache item key.
	 *
	 * @return bool
	 */
	public function has( string $key ): bool;

}