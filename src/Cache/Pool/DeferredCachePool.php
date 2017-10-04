<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Pool;

/**
 * Interface for all cache implementations.
 */
interface DeferredCachePool extends CachePool {

	/**
	 * Queue data to be stored in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string    $key   The key of the item to store.
	 * @param mixed     $value The value of the item to store, must be serializable.
	 * @param null|int| $ttl   Optional. The TTL value of this item.
	 *
	 * @return DeferredCachePool
	 */
	public function queue_for_set( string $key, $value = null, int $ttl = null ): DeferredCachePool;

	/**
	 * Queue data uniquely referenced by given key to be deleted from cache.
	 *
	 * @param string $key The key of the item to store.
	 *
	 * @return DeferredCachePool
	 */
	public function queue_for_delete( string $key ): DeferredCachePool;

	/**
	 * Remove an item from queue.
	 *
	 * @param string $key The key of the item to store.
	 *
	 * @return bool True if item removed from queue
	 */
	public function dequeue( string $key ): bool;

	/**
	 * Perform all queued operations.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function commit(): bool;

}