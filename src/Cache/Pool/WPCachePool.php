<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache\Pool;

use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Item\CacheItem;
use Inpsyde\MultilingualPress\Cache\Item\WPCacheItem;

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
	 * @param string $key The unique key of item in the cache.
	 *
	 * @return CacheItem The cache item identified by given key.
	 */
	public function item( string $key ): CacheItem {

		return new WPCacheItem( $this->driver, $this->namespace . $key );
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string     $key     The unique key of item in the cache.
	 * @param mixed|null $default Default value to return if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get( string $key, $default = null ) {

		$item = $this->item( $key );
		if ( ! $item->is_hit() ) {
			return $default;
		}

		return $item->value();
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param string[]   $keys    The unique keys of item in the cache.
	 * @param mixed|null $default Default value to assign to each item if the key does not exist.
	 *
	 * @return array The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get_many( array $keys, $default = null ): array {

		$values = [];
		foreach ( $keys as $key ) {
			$values[] = $this->get( $key, $default );
		}

		return $values;
	}

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string   $key   The key of the item to store.
	 * @param mixed    $value Optional. The value of the item to store, must be serializable. Defaults to null.
	 * @param null|int $ttl   Optional. The TTL value of item.
	 *
	 * @return CacheItem The cache item just wrote to
	 */
	public function set( string $key, $value = null, int $ttl = null ): CacheItem {

		$item = new WPCacheItem( $this->driver, $this->namespace . $key, $ttl );
		$item->set( $value );

		return $item;
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param string $key The unique cache key of the item to delete.
	 *
	 * @return bool True if the item was successfully removed. False if there was an error.
	 */
	public function delete( string $key ): bool {

		return $this->item( $key )->delete();
	}

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * @param string $key The cache item key.
	 *
	 * @return bool
	 */
	public function has( string $key ): bool {

		return $this->item( $key )->is_hit();
	}

	/**
	 * Queue data to be stored in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string    $key   The key of the item to store.
	 * @param mixed     $value The value of the item to store, must be serializable.
	 * @param null|int| $ttl   Optional. The TTL value of item.
	 *
	 * @return DeferredCachePool
	 */
	public function queue_for_set( string $key, $value = null, int $ttl = null ): DeferredCachePool {

		$this->ensure_commit_scheduled();

		$this->queue[ $key ] = function () use ( $key, $value, $ttl ) {

			$this->set( $key, $value, $ttl );
		};

		return $this;

	}

	/**
	 * Queue data uniquely referenced by given key to be deleted from cache.
	 *
	 * @param string $key The key of the item to store.
	 *
	 * @return DeferredCachePool
	 */
	public function queue_for_delete( string $key ): DeferredCachePool {

		$this->ensure_commit_scheduled();

		$this->queue[ $key ] = function () use ( $key ) {

			$this->delete( $key );
		};

		return $this;
	}

	/**
	 * Remove an item from queue.
	 *
	 * @param string $key The key of the item to store.
	 *
	 * @return bool True if item removed from queue
	 */
	public function dequeue( string $key ): bool {

		if ( array_key_exists( $key, $this->queue ) ) {
			$this->queue = array_diff_key( $this->queue, [ $key => '' ] );

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
}