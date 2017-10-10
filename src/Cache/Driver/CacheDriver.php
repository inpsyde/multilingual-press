<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Driver;

use Inpsyde\MultilingualPress\Cache\Item\Value;

/**
 * @package Inpsyde\MultilingualPress\Cache\Driver
 * @since   3.0.0
 */
interface CacheDriver {

	const FOR_NETWORK = 32;

	/**
	 * @return bool
	 */
	public function is_network(): bool;

	/**
	 * Reads a value from the cache.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 *
	 * @return Value
	 */
	public function read( string $namespace, string $name ): Value;

	/**
	 * Write a value to the cache.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 * @param mixed  $value     Cached value.
	 *
	 * @return bool
	 */
	public function write( string $namespace, string $name, $value ): bool;

	/**
	 * Delete a value from the cache.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $name      Cache item name.
	 *
	 * @return bool
	 */
	public function delete( string $namespace, string $name ): bool;

}
