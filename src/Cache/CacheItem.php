<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache;

interface CacheItem {

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
	 * Sets the value for the cache item.
	 *
	 * @param mixed $value
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

}