<?php # -*- coding: utf-8 -*-

declare( strict_types=1 );

namespace Inpsyde\MultilingualPress\Cache\Item;

use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Common\Event\Event;

/**
 * A complete multi-driver cache item.
 *
 * @package Inpsyde\MultilingualPress\Cache
 * @since   3.0.0
 */
final class WPUpdatableCacheItem implements UpdatableCacheItem, TaggableCacheItem, ExpirableCacheItem {

	const DEFAULT_TIME_TO_LIVE = 3600;

	/**
	 * @var CacheDriver
	 */
	private $driver;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var string
	 */
	private $group = '';

	/**
	 * @var mixed
	 */
	private $value = null;

	/**
	 * @var bool
	 */
	private $is_hit = false;

	/**
	 * @var int
	 */
	private $time_to_live = null;

	/**
	 * @var \DateTimeImmutable
	 */
	private $expire_date = null;

	/**
	 * @var \DateTimeImmutable
	 */
	private $last_save = null;

	/**
	 * @var bool
	 */
	private $locked = false;

	/**
	 * @var bool
	 */
	private $shallow_update = false;

	/**
	 * @var array
	 */
	private $tags;

	/**
	 * Constructor, sets the key.
	 *
	 * @param CacheDriver $driver
	 * @param string      $key
	 * @param array       $tags
	 * @param int|null    $time_to_live
	 */
	public function __construct( CacheDriver $driver, string $key, array $tags = [], int $time_to_live = null ) {

		$this->driver       = $driver;
		$this->key          = $key;
		$this->time_to_live = $time_to_live;
		$this->tags         = $tags;

		$this->value();
	}

	/**
	 * Cache item key
	 *
	 * @return string
	 */
	public function key(): string {

		return $this->key;
	}

	/**
	 * Sets the value for the cache item.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function set( $value ): bool {

		$this->is_hit = true;
		$this->value  = $value;

		return $this->update();
	}

	/**
	 * Check if the cache item was a hit. Necessary to disguise null values stored in cache.
	 *
	 * @return bool
	 */
	public function is_hit(): bool {

		return $this->is_hit;
	}

	/**
	 * Cache item value.
	 *
	 * @return mixed Should be null when no value is stored in cache.
	 */
	public function value() {

		if ( $this->is_hit ) {
			return $this->value;
		}

		list( $cached, $found ) = $this->driver->read( $this->group, $this->key );

		$this->is_hit = $found && is_array( $cached ) && $cached;

		$value = $ttl = $expire_date = $last_save = null;
		$tags  = [];

		if ( $this->is_hit ) {
			list( $value, $ttl, $expire_date, $last_save, $tags ) = $this->prepare_value( $cached );
		}

		$this->value       = $value;
		$this->last_save   = $last_save;
		$this->expire_date = $expire_date;
		$this->tags        = array_unique( array_merge( $tags, $this->tags ) );

		if ( $this->time_to_live === null ) {
			$this->time_to_live = is_int( $ttl ) ? $ttl : self::DEFAULT_TIME_TO_LIVE;
		}

		// If value is expired we are going to return it anyway, but we delete it from cache
		$deleted = $this->is_hit ? $this->maybe_delete() : true;

		if ( $deleted ) {
			return $this->value;
		}

		// If something changed we need to update the storage
		if ( ( $ttl && $ttl !== $this->time_to_live ) || ( $this->tags !== $tags ) ) {
			// Shallow update means no change will be done on "last save" property, so we don't prolong the TTL
			$this->shallow_update = true;
			$this->update();
			$this->shallow_update = false;
		}

		return $this->value;
	}

	/**
	 * Delete the cache item from its storage and ensure that next value() call return null
	 * (unless added again to storage).
	 *
	 * @return bool
	 */
	public function delete(): bool {

		unset( $this->value, $this->time_to_live, $this->last_save );

		$this->is_hit = false;

		return $this->driver->delete( $this->group, $this->key );
	}

	/**
	 * Return current item tags.

	 * @return string[]
	 */
	public function tags(): array  {

		return $this->tags;
	}

	/**
	 * Check if current cache item have one (or more) tags.
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function has_tag( string ...$tags ): bool {

		return (bool) ( array_intersect( $tags, $this->tags ) == $tags );
	}

	/**
	 * Add one or more tags ot cache item.
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function add_tags( string ...$tags ): bool {

		$tags = array_unique( array_merge( $tags, $this->tags ) );

		if ( $tags !== $this->tags ) {
			$this->tags           = $tags;
			$this->shallow_update = true;
			$this->update();
			$this->shallow_update = true;

			return true;
		}

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

		$tags = array_unique( array_diff( $this->tags, $tags ) );

		if ( $tags !== $this->tags ) {
			$this->tags           = $tags;
			$this->shallow_update = true;
			$this->update();
			$this->shallow_update = true;

			return true;
		}

		return false;
	}

	/**
	 * Overwrite tags with the given tag(s).
	 *
	 * @param string[] ...$tags
	 *
	 * @return bool
	 */
	public function use_tags( string ...$tags ): bool {

		if ( $tags !== $this->tags ) {
			$this->tags           = $tags;
			$this->shallow_update = true;
			$this->update();
			$this->shallow_update = true;

			return true;
		}

		return false;
	}

	/**
	 * Subscribe given event with given callback.
	 * Pass to callback the current cache item as first argument.
	 *
	 * @param Event    $event
	 * @param callable $callback
	 *
	 * @return WPUpdatableCacheItem|UpdatableCacheItem
	 */
	public function listen( Event $event, callable $callback ): UpdatableCacheItem {

		$this->assert_not_locked();

		$this->locked = true;
		$event->listen( $callback, $this );
		$this->locked = false;

		return $this;
	}

	/**
	 * Subscribe given event to delete the cache item value when the event is fired.
	 *
	 * @param Event $event
	 *
	 * @return WPUpdatableCacheItem|UpdatableCacheItem
	 */
	public function listen_and_delete( Event $event ): UpdatableCacheItem {

		$this->assert_not_locked();

		$delete_callback = function () {

			$this->is_hit = false;
			$this->driver->delete( $this->group, $this->key );
		};

		$this->locked = true;
		$event->listen( $delete_callback, $this );
		$this->locked = false;

		return $this;
	}

	/**
	 * Sets a specific date of expiration of the item.
	 *
	 * @param \DateTimeInterface $expire_date
	 *
	 * @return WPUpdatableCacheItem|ExpirableCacheItem
	 */
	public function expires_on( \DateTimeInterface $expire_date ): ExpirableCacheItem {

		// Let's ensure expire_date is immutable and it is in the GMT timezone
		$exp_offset  = $expire_date->getOffset();
		$timestamp   = $exp_offset === 0 ? $expire_date->getTimestamp() : $expire_date->getTimestamp() + $exp_offset;
		$expire_date = \DateTimeImmutable::createFromFormat( 'U', $timestamp, new \DateTimeZone( 'GMT' ) );

		$this->expire_date = $expire_date;

		// Shallow update means no change will be done on "last save" property
		$this->shallow_update = true;
		$this->update();
		$this->shallow_update = false;

		$this->is_hit = false;

		return $this;
	}

	/**
	 * Expiration the item after a given number of seconds.
	 *
	 * @param int $time_to_live
	 *
	 * @return WPUpdatableCacheItem|ExpirableCacheItem
	 */
	public function expires_after( int $time_to_live ): ExpirableCacheItem {

		$now = $this->now();

		return $this->expires_on( $now->setTimestamp( $now->getTimestamp() + $time_to_live ) );
	}

	/**
	 * @return bool
	 */
	private function update(): bool {

		if ( $this->is_hit ) {

			$this->driver->write( $this->group, $this->key, $this->prepare_value() );

			return true;
		}

		$this->delete();

		return true;
	}

	/**
	 * @throws \BadMethodCallException If currently locked
	 */
	private function assert_not_locked() {

		if ( $this->locked ) {

			$message = 'Error updating cache for key %s. ';
			$message .= '%s can\'t be called from a update/delete callbacks. ';
			$message .= 'Use delete() to prevent the cache to return any value and also flush cache storage, ';
			$message .= 'or use expires_on() with no arguments to flush cache storage ';
			$message .= 'but leaving cached value for current request';

			throw new \BadMethodCallException( sprintf( $message, $this->key, __METHOD__ ) );
		}
	}

	/**
	 * @return bool
	 */
	private function maybe_delete(): bool {

		// If the value has a fixed expire date, let's keep it as expire timestamp
		$expiry_time_by_date = $this->expire_date ? $this->expire_date->getTimestamp() : null;

		// If we have a last save and a time to live, calculate an expired timestamp based on that
		$expiry_time_by_ttl = $this->last_save && is_int( $this->time_to_live )
			? $this->last_save->getTimestamp() + $this->time_to_live
			: null;

		// If we don't have and expiration date, nor we were able to calculate a expiration by TTL, let's just return
		if ( $expiry_time_by_date === null && $expiry_time_by_ttl === null ) {
			return false;
		}

		// Expire time is which occur first between expire date end expiration calculated via TTL
		switch ( true ) {
			case ( $expiry_time_by_date === null ) :
				$expiry = $expiry_time_by_ttl;
				break;
			case ( $expiry_time_by_ttl === null ) :
				$expiry = $expiry_time_by_date;
				break;
			default :
				$expiry = min( $expiry_time_by_date, $expiry_time_by_ttl );
				break;
		}

		// If not expired, we have nothing to do
		if ( $expiry < (int) $this->now( 'U' ) ) {
			return false;
		}

		// If here, value is expired. Setting is_hit to false, on next value() access, value will be updated from cache
		$this->is_hit = false;
		// and by unsetting it, we ensure the value returned will be null (unless updated again)
		$this->driver->delete( $this->group, $this->key );

		return true;
	}

	/**
	 * @param string|null $format
	 *
	 * @return \DateTimeImmutable|string
	 */
	private function now( string $format = null ) {

		$now = new \DateTimeImmutable( 'now', new \DateTimeZone( 'GMT' ) );

		return $format ? $now->format( $format ) : $now;
	}

	/**
	 * Compact to and explode from storage a value
	 *
	 * @param array $compact_value
	 *
	 * @return array
	 */
	private function prepare_value( array $compact_value = null ): array {

		if ( $compact_value === null ) {

			// When doing a shallow update, we don't update last save time, unless value was never saved before
			$last_save = ! $this->shallow_update || ! $this->last_save ? $this->now() : $this->last_save;

			return [
				'V' => $this->value,
				'T' => (int) $this->time_to_live ?: self::DEFAULT_TIME_TO_LIVE,
				'E' => $this->expire_date ? $this->serialize_date( $this->expire_date ) : '',
				'S' => $this->serialize_date( $last_save ),
				'A' => array_filter( $this->tags, 'is_string' ),
			];
		}

		$value       = $compact_value['V'] ?? null;
		$ttl         = $compact_value['T'] ?? null;
		$expire_date = ( $compact_value['E'] ?? null );
		$last_save   = ( $compact_value['S'] ?? null );
		$tags        = ( $compact_value['A'] ?? [] );

		return [
			$value,
			$ttl === null ? null : (int) $ttl,
			$expire_date === null ? null : $this->unserialize_date( (string) $expire_date ),
			$last_save === null ? null : $this->unserialize_date( (string) $last_save ),
			(array) $tags,
		];
	}

	/**
	 * @param \DateTimeInterface $date
	 *
	 * @return string
	 */
	private function serialize_date( \DateTimeInterface $date ): string {

		return $date->format( 'c' );
	}

	/**
	 * @param string $date
	 *
	 * @return \DateTimeImmutable|null
	 */
	private function unserialize_date( string $date ) {

		if ( ! $date || ! is_string( $date ) ) {
			return null;
		}

		$date = \DateTimeImmutable::createFromFormat( 'c', $date );

		return $date ?: null;
	}

}