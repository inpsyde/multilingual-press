<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Item;

/**
 * @package Inpsyde\MultilingualPress\Cache\Item
 * @since   3.0.0
 */
interface CacheItem {

	const LIFETIME_IN_SECONDS = HOUR_IN_SECONDS;

	/**
	 * Cache item key
	 *
	 * @return string
	 */
	public function key(): string;

	/**
	 * Cache item value.
	 *
	 * @return mixed Should be null when no value is stored in cache.
	 */
	public function value();

	/**
	 * Check if the cache item was a hit. Necessary to disguise null values stored in cache.
	 *
	 * @return bool
	 */
	public function is_hit(): bool;

	/**
	 * Check if the cache item is expired.
	 *
	 * @return bool
	 */
	public function is_expired(): bool;

	/**
	 * Sets the value for the cache item.
	 *
	 * @param mixed $value Value to cache.
	 *
	 * @return bool
	 */
	public function set( $value ): bool;

	/**
	 * Delete the cache item from its storage and ensure that next value() call return null.
	 *
	 * @return bool
	 */
	public function delete(): bool;

	/**
	 * Sets a specific time to live for the item.
	 *
	 * @param int $ttl How much time in seconds the cached value should be considered valid.
	 *
	 * @return CacheItem
	 */
	public function live_for( int $ttl ): CacheItem;

	/**
	 * Push values to storage driver.
	 *
	 * @return bool
	 */
	public function sync_to_storage(): bool;

	/**
	 * Load values from storage driver.
	 *
	 * @return bool
	 */
	public function sync_from_storage(): bool;

}
