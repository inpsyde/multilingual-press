<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache;

use Inpsyde\MultilingualPress\Common\Event\Event;

interface UpdatableCacheItem extends CacheItem {

	/**
	 * Subscribe given event with given callback.
	 * Implementations should pass to callback the current cache item as first argument.
	 *
	 * @param Event $event
	 * @param callable   $callback
	 *
	 * @return UpdatableCacheItem
	 */
	public function listen( Event $event, callable $callback ): UpdatableCacheItem;

	/**
	 * Subscribe given event to delete the cache item value when the event is fired.
	 *
	 * @param Event $event
	 *
	 * @return UpdatableCacheItem
	 */
	public function listen_and_delete( Event $event ): UpdatableCacheItem;

}