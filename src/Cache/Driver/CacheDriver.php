<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Driver;

/**
 * @package Inpsyde\MultilingualPress\Cache
 * @since   3.0.0
 */
interface CacheDriver {

	const FOR_NETWORK = 32;

	/**
	 * @return bool
	 */
	public function is_sidewide(): bool;

	/**
	 * Reads a value from the cache.
	 *
	 * @param string $namespace
	 * @param string $name
	 *
	 * @return array Two item array where first item is the read value and the second is a boolean telling if the read
	 *               was a cache it (to disguise cache null)
	 */
	public function read( string $namespace, string $name ): array;

	/**
	 * Write a value to the cache.
	 *
	 * @param string $namespace
	 * @param string $name
	 * @param        $value
	 *
	 * @return bool
	 */
	public function write( string $namespace, string $name, $value ): bool;

	/**
	 * Delete a value from the cache.
	 *
	 * @param string $namespace
	 * @param string $name
	 *
	 * @return bool
	 */
	public function delete( string $namespace, string $name ): bool;

}