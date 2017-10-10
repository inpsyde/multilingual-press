<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Exception;

use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;

/**
 * @package MultilingualPress\Cache\Exception
 * @since   3.0.0
 */
class InvalidCacheDriver extends Exception {

	const SITE_DRIVER_AS_NETWORK = 1;

	/**
	 * @param CacheDriver $driver Cache driver.
	 *
	 * @return InvalidCacheDriver
	 */
	public static function for_site_driver_as_network( CacheDriver $driver ): InvalidCacheDriver {

		$type = get_class( $driver );

		return new static(
			"Cannot create a network-wide cache with driver of type \"{$type}\" which is not for network.",
			self::SITE_DRIVER_AS_NETWORK
		);
	}

}
