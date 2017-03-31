<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache;

interface ExpirableCacheItem extends CacheItem {

	/**
	 * Sets a specific date of expiration of the item.
	 *
	 * @param \DateTimeInterface $expire_date
	 *
	 * @return ExpirableCacheItem
	 */
	public function expires_on( \DateTimeInterface $expire_date ): ExpirableCacheItem;

	/**
	 * Expiration the item after a given number of seconds.
	 *
	 * @param int $time_to_live
	 *
	 * @return ExpirableCacheItem
	 */
	public function expires_after( int $time_to_live ): ExpirableCacheItem;

}