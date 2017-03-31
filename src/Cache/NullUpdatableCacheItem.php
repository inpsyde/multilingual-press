<?php # -*- coding: utf-8 -*-

declare( strict_types=1 );

namespace Inpsyde\MultilingualPress\Cache;

use Inpsyde\MultilingualPress\Common\Event\Event;

/**
 * Fully equipped null cache object item.
 *
 * @package Inpsyde\MultilingualPress\Cache
 * @since   3.0.0
 */
final class NullUpdatableCacheItem implements UpdatableCacheItem, TaggableCacheItem, ExpirableCacheItem {

	/**
	 * Cache item key
	 *
	 * @return string
	 */
	public function key(): string {
		return '';
	}

	/**
	 * Cache item value.
	 *
	 * @return mixed Should be null when no value is stored in cache.
	 */
	public function value() {
		return null;
	}

	/**
	 * Check if the cache item was a hit. Necessary to disguise null values stored in cache.
	 *
	 * @return bool
	 */
	public function is_hit(): bool {
		return false;
	}

	/**
	 * Sets the value for the cache item.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function set( $value ): bool {
		return false;
	}

	/**
	 * Delete the cache item from its storage and ensure that next value() call return null.
	 *
	 * @return bool
	 */
	public function delete(): bool {
		return false;
	}

	/**
	 * Sets a specific date of expiration of the item.
	 *
	 * @param \DateTimeInterface $expire_date
	 *
	 * @return ExpirableCacheItem
	 */
	public function expires_on( \DateTimeInterface $expire_date ): ExpirableCacheItem {
		return $this;
	}

	/**
	 * Expiration the item after a given number of seconds.
	 *
	 * @param int $time_to_live
	 *
	 * @return ExpirableCacheItem
	 */
	public function expires_after( int $time_to_live ): ExpirableCacheItem {
		return $this;
	}

	/**
	 * Return current item tags.
	 *
	 * @return string[]
	 */
	public function tags(): array {
		return [];
	}

	/**
	 * Check if current cache item have one (or more) tags.
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function has_tag( string ...$tags ): bool {
		return false;
	}

	/**
	 * Add one or more tags ot cache item.
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function add_tags( string ...$tags ): bool {
		return false;
	}

	/**
	 * Add one or more tags ot cache item.
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function remove_tags( string ...$tags ): bool {
		return false;
	}

	/**
	 * Overwrite tags withe given tag(s).
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function use_tags( string ...$tags ): bool {
		return false;
	}

	/**
	 * Subscribe given event with given callback.
	 * Implementations should pass to callback the current cache item as first argument.
	 *
	 * @param Event    $event
	 * @param callable $callback
	 *
	 * @return UpdatableCacheItem
	 */
	public function listen( Event $event, callable $callback ): UpdatableCacheItem {
		return $this;
	}

	/**
	 * Subscribe given event to delete the cache item value when the event is fired.
	 *
	 * @param Event $event
	 *
	 * @return UpdatableCacheItem
	 */
	public function listen_and_delete( Event $event ): UpdatableCacheItem {
		return $this;
	}
}