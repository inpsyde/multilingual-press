<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache\Pool;

use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Item\CacheItem;
use Inpsyde\MultilingualPress\Cache\Item\WPUpdatableCacheItem;

/**
 * @package Inpsyde\MultilingualPress\Cache
 * @since   3.0.0
 */
final class WPCachePool implements DeferredCachePool {

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var CacheDriver
	 */
	private $driver;

	/**
	 * @var array
	 */
	private $queue = [];

	/**
	 * @param string      $namespace
	 * @param CacheDriver $driver
	 */
	public function __construct( string $namespace, CacheDriver $driver ) {

		$this->driver    = $driver;
		$this->namespace = $namespace;
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string $key  The unique key of item in the cache.
	 * @param mixed  $tags Optional. Tags the further identify a cache item. Defaults to empty array.
	 *
	 * @return mixed The cache item identified by key and tags
	 */
	public function item( string $key, array $tags = [] ): CacheItem {

		return new WPUpdatableCacheItem( $this->driver, $this->namespace . $key, $tags );
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string     $key     The unique key of item in the cache.
	 * @param mixed      $tags    Optional. Tags the further identify a cache item. Defaults to empty array.
	 * @param mixed|null $default Default value to return if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get( string $key, array $tags = [], $default = null ) {

		$item = $this->item( $key, $tags );
		if ( ! $item->is_hit() ) {
			return $default;
		}

		return $item->value();
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string[]   $keys    The unique keys of item in the cache.
	 * @param mixed      $tags    Optional. Tags the further identify a cache items. Defaults to empty array.
	 * @param mixed|null $default Default value to assign to each item if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get_many( array $keys, array $tags = [], $default = null ): array {

		$values = [];
		foreach ( $keys as $key ) {
			$values[] = $this->get( $key, $tags, $default );
		}

		return $values;
	}

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string   $key   The key of the item to store.
	 * @param mixed    $value Optional. The value of the item to store, must be serializable. Defaults to null.
	 * @param array    $tags  Optional. Tags the further identify a cache item.
	 * @param null|int $ttl   Optional. The TTL value of item.
	 *
	 * @return CacheItem The cache item just wrote to
	 */
	public function set( string $key, $value = null, array $tags = [], int $ttl = null ): CacheItem {

		$item = new WPUpdatableCacheItem( $this->driver, $this->namespace . $key, $tags, $ttl );
		$item->set( $value );

		return $item;
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param string $key  The unique cache key of the item to delete.
	 * @param array  $tags Optional. Tags the further identify a cache item. Defaults to empty array.
	 *
	 * @return bool True if the item was successfully removed. False if there was an error.
	 */
	public function delete( string $key, array $tags = [] ): bool {

		return $this->item( $key, $tags )->delete();
	}

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * @param string $key The cache item key.
	 *
	 * @param array  $tags
	 *
	 * @return bool
	 */
	public function has( string $key, array $tags = [] ): bool {

		return $this->item( $key, $tags )->is_hit();
	}

	/**
	 * Queue data to be stored in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string    $key   The key of the item to store.
	 * @param array     $tags  Tags to further identify the cache item
	 * @param mixed     $value The value of the item to store, must be serializable.
	 * @param null|int| $ttl   Optional. The TTL value of item.
	 *
	 * @return DeferredCachePool
	 */
	public function queue_for_set( string $key, array $tags = [], $value = null, int $ttl = null ): DeferredCachePool {

		$this->ensure_commit_scheduled();

		$this->queue[ $this->queue_key( $key, $tags ) ] = function () use ( $key, $tags, $value, $ttl ) {

			$this->set( $key, $value, $tags, $ttl );
		};

		return $this;

	}

	/**
	 * Queue data uniquely referenced by given key to be deleted from cache.
	 *
	 * @param string $key The key of the item to store.
	 *
	 * @param array  $tags
	 *
	 * @return DeferredCachePool
	 */
	public function queue_for_delete( string $key, array $tags = [] ): DeferredCachePool {

		$this->ensure_commit_scheduled();

		$this->queue[ $this->queue_key( $key, $tags ) ] = function () use ( $key, $tags ) {

			$this->delete( $key, $tags );
		};

		return $this;
	}

	/**
	 * Remove an item from queue.
	 *
	 * @param string $key  The key of the item to store.
	 * @param array  $tags Tags to further identify the cache item
	 *
	 * @return bool True if item removed from queue
	 */
	public function dequeue( string $key, array $tags = [] ): bool {

		$index = $this->queue_key( $key, $tags );

		if ( array_key_exists( $index, $this->queue ) ) {
			unset( $this->queue[ $index ] );

			return true;
		}

		return false;
	}

	/**
	 * Perform all queued operations.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function commit(): bool {

		array_walk( $this->queue, function ( callable $callable ) {

			$callable();
		} );

		$this->queue = [];

		return true;
	}

	/**
	 * Add commit method to shutdown action if not already added.
	 */
	private function ensure_commit_scheduled() {

		if ( ! has_action( 'shutdown', [ $this, 'commit' ] ) ) {
			add_action( 'shutdown', [ $this, 'commit' ] );
		}
	}

	/**
	 * @param string $key
	 * @param array  $tags
	 *
	 * @return string
	 */
	private function queue_key( string $key, array $tags = [] ): string {

		array_unshift( $tags, $key );

		return md5( serialize( $tags ) );
	}
}