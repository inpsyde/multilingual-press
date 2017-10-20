<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Exception;

/**
 * @package MultilingualPress\Cache\Exception
 * @since   3.0.0
 */
class NotRegisteredCacheItem extends Exception {

	/**
	 * @param string $namespace Item namespace.
	 * @param string $key       Item key.
	 *
	 * @return NotRegisteredCacheItem
	 */
	public static function for_namespace_and_key( string $namespace, string $key ): NotRegisteredCacheItem {

		return new static(
			sprintf(
				'The namespace/key pair "%s"/"%s" does not belong to any registered cache logic in cache server.',
				$namespace,
				$key
			)
		);
	}

}

