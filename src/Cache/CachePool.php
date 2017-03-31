<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache;

/**
 * Interface for all cache implementations.
 */
interface CachePool {

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string $key  The unique key of this item in the cache.
	 * @param mixed  $tags Optional. Tags the further identify a cache item. Defaults to empty array.
	 *
	 * @return mixed The cache item identified by key and tags
	 */
	public function item( string $key, array $tags = [] ): CacheItem;

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string     $key     The unique key of this item in the cache.
	 * @param mixed      $tags    Optional. Tags the further identify a cache item. Defaults to empty array.
	 * @param mixed|null $default Default value to return if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get( string $key, array $tags = [], $default = null );

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string[]   $keys    The unique keys of item in the cache.
	 * @param mixed      $tags    Optional. Tags the further identify a cache items. Defaults to empty array.
	 * @param mixed|null $default Default value to assign to each item if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get_many( array $keys, array $tags = [], $default = null ): array;

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string   $key   The key of the item to store.
	 * @param mixed    $value Optional. The value of the item to store, must be serializable. Defaults to null.
	 * @param array    $tags  Optional. Tags the further identify a cache item.
	 * @param null|int $ttl   Optional. The TTL value of this item.
	 *
	 * @return CacheItem The cache item just wrote to
	 */
	public function set( string $key, $value = null, array $tags = [], int $ttl = null ): CacheItem;

	/**
	 * Get an item from the pool, or create and empty item if not exist.
	 *
	 * @param string   $key  The key of the item to store.
	 * @param array    $tags Optional. Tags the further identify a cache item.
	 * @param null|int $ttl  Optional. The TTL value of item.
	 *
	 * @return CacheItem The cache item just wrote to
	 */
	public function exist_or_create( string $key, array $tags = [], int $ttl = null ): CacheItem;

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param string $key  The unique cache key of the item to delete.
	 * @param array  $tags Optional. Tags the further identify a cache item. Defaults to empty array.
	 *
	 * @return bool True if the item was successfully removed. False if there was an error.
	 */
	public function delete( string $key, array $tags = [] ): bool;

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * @param string $key The cache item key.
	 *
	 * @return bool
	 */
	public function has( string $key ): bool;

}