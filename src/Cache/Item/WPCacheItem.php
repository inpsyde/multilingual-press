<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Cache\Item;

use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;

/**
 * A complete multi-driver cache item.
 *
 * @package Inpsyde\MultilingualPress\Cache\Item
 * @since   3.0.0
 */
final class WPCacheItem implements CacheItem {

	const DIRTY = 'dirty';
	const DIRTY_SHALLOW = 'shallow';
	const DELETED = 'deleted';
	const CLEAN = '';

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
	 * @var string
	 */
	private $dirty_status = self::CLEAN;

	/**
	 * @var bool
	 */
	private $is_expired = null;

	/**
	 * @var int
	 */
	private $time_to_live = null;

	/**
	 * @var \DateTimeImmutable
	 */
	private $last_save = null;

	/**
	 * @var bool
	 */
	private $shallow_update = false;

	/**
	 * Constructor, sets the key.
	 *
	 * @param CacheDriver $driver       Cache item driver.
	 * @param string      $key          Cache item key.
	 * @param int|null    $time_to_live Cache item time to live.
	 */
	public function __construct( CacheDriver $driver, string $key, int $time_to_live = null ) {

		$this->driver       = $driver;
		$this->key          = $key;
		$this->time_to_live = $time_to_live;

		$this->calculate_status();
	}

	/**
	 * Before the object vanishes its storage its updated if needs to.
	 */
	public function __destruct() {

		$this->sync_to_storage();
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
	 * @param mixed $value Value to store in cache.
	 *
	 * @return bool
	 */
	public function set( $value ): bool {

		$this->is_hit       = true;
		$this->value        = $value;
		$this->is_expired   = null;
		$this->dirty_status = self::DIRTY;
		$this->last_save    = $this->now();

		return true;
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
	 * Check if the cache item is expired.
	 *
	 * @return bool
	 */
	public function is_expired(): bool {

		if ( ! $this->is_hit() ) {
			return false;
		}

		if ( isset( $this->is_expired ) ) {
			return $this->is_expired;
		}

		// If we have a last save and a time to live, calculate an expired timestamp based on that.
		$expiry_time = $this->last_save && is_int( $this->time_to_live )
			? $this->last_save->getTimestamp() + $this->time_to_live
			: null;

		// If we don't have and expiration date, nor we were able to calculate a expiration by TTL, let's just return.
		if ( null === $expiry_time ) {
			return false;
		}

		$this->is_expired = $expiry_time < (int) $this->now( 'U' );

		return $this->is_expired;
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

		if ( self::DELETED === $this->dirty_status ) {
			return null;
		}

		$this->calculate_status();

		return $this->value;
	}

	/**
	 * Delete the cache value and ensure that next value() call returns null and is_hit() returns false.
	 *
	 * @return bool
	 */
	public function delete(): bool {

		$this->value        = null;
		$this->time_to_live = null;
		$this->last_save    = null;
		$this->is_expired   = null;
		$this->is_hit       = false;
		$this->dirty_status = self::DELETED;

		return true;
	}

	/**
	 * Sets a specific time to live for the item.
	 *
	 * @param int $ttl How much time in seconds the cached value should be considered valid.
	 *
	 * @return CacheItem
	 */
	public function live_for( int $ttl ): CacheItem {

		$this->time_to_live = $ttl;
		$this->is_expired   = null;

		if ( self::CLEAN === $this->dirty_status ) {
			$this->dirty_status = self::DIRTY_SHALLOW;
		}

		return $this;

	}

	/**
	 * Ensure synchronization with storage driver.
	 *
	 * @return bool
	 */
	public function sync_to_storage(): bool {

		if ( self::CLEAN === $this->dirty_status ) {
			return true;
		}

		// Shallow update means no change will be done on "last save" property, so we don't prolong the TTL.
		$this->shallow_update = self::DIRTY_SHALLOW === $this->dirty_status;
		$updated              = $this->update();
		$this->shallow_update = false;

		// If the update is successful, the value is in sync with storage, so status is clean again.
		if ( $updated ) {
			$this->dirty_status = self::CLEAN;

			return true;
		}

		// If update failed, the status of the item need to be re-calculated.
		$this->calculate_status();

		return false;
	}

	/**
	 * Ensure synchronization with storage driver.
	 *
	 * @return bool
	 */
	public function sync_from_storage(): bool {

		$this->delete();
		$this->dirty_status = self::CLEAN;
		$this->calculate_status();

		return true;
	}

	/**
	 * Initialize (or update) the internal status of the item.
	 */
	private function calculate_status() {

		// First, load cached value from storage and mark item as "hit" if the value was actually stored.
		$value_object = $this->driver->read( $this->group, $this->key );

		$cached_value = $value_object->value();
		$this->is_hit = $value_object->is_hit() && is_array( $cached_value ) && $cached_value;

		// Then initialize properties and override them with values from storage, only if those exists and validates.
		$value     = null;
		$ttl       = null;
		$last_save = null;
		if ( $this->is_hit ) {
			list( $value, $ttl, $last_save ) = $this->prepare_value( $cached_value );
		}

		// Always override "last save" value from storage if there is something there.
		if ( $this->is_hit ) {
			$this->last_save = $last_save;
		}

		// If no value was already set on the item (via `set()`) use value from storage (or null, if no hit).
		if ( null === $this->value ) {
			$this->value = $value;
		}

		// If no TTL was already set on the item (via `live_for()`) use value from storage (or default, if no hit).
		if ( null === $this->time_to_live ) {
			$this->time_to_live = is_int( $ttl ) ? $ttl : self::LIFETIME_IN_SECONDS;
		}

		$current_ttl = is_null( $ttl ) ? self::LIFETIME_IN_SECONDS : $ttl;

		/**
		 * Calculate status:
		 * - if object properties match storage, the status is "clean" (no update needed)
		 * - if object "value" differs from storage, the status is "dirty" (full update needed)
		 * - if object "value" matches storage, but TTL differs, the status is "dirty shallow" (partial update needed)
		 */
		$this->dirty_status = self::CLEAN;
		if ( $this->value !== $value ) {
			$this->dirty_status = self::DIRTY;
		} elseif ( ( $current_ttl !== $this->time_to_live ) ) {
			$this->dirty_status = self::DIRTY_SHALLOW;
		}

	}

	/**
	 * @return bool
	 */
	private function update(): bool {

		if ( $this->is_hit ) {

			$this->driver->write( $this->group, $this->key, $this->prepare_value() );
			$this->is_expired = null;

			return true;
		}

		return $this->driver->delete( $this->group, $this->key );
	}

	/**
	 * @param string|null $format Datetime format string.
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
	 * @param array $compact_value Value to be unserialized, or null to serialize status.
	 *
	 * @return array
	 */
	private function prepare_value( array $compact_value = null ): array {

		if ( null === $compact_value ) {

			// When doing a shallow update, we don't update last save time, unless value was never saved before.
			$last_save = ( ! $this->shallow_update || ! $this->last_save ) ? $this->now() : $this->last_save;

			return [
				'V' => $this->value,
				'T' => (int) $this->time_to_live ?: self::LIFETIME_IN_SECONDS,
				'S' => $this->serialize_date( $last_save ),
			];
		}

		$value     = $compact_value['V'] ?? null;
		$ttl       = isset( $compact_value['T'] ) ? (int) $compact_value['T'] : null;
		$last_save = isset( $compact_value['S'] ) ? $this->unserialize_date( (string) $compact_value['S'] ) : null;

		return [
			$value,
			$ttl,
			$last_save,
		];
	}

	/**
	 * @param \DateTimeInterface $date Date to serialize.
	 *
	 * @return string
	 */
	private function serialize_date( \DateTimeInterface $date ): string {

		return $date->format( 'c' );
	}

	/**
	 * @param string $date Serialized date string.
	 *
	 * @return \DateTimeImmutable|null
	 */
	private function unserialize_date( string $date ) {

		if ( ! $date ) {
			return null;
		}

		$date = \DateTimeImmutable::createFromFormat( 'U', (string) strtotime( $date ) );

		return $date ?: null;
	}
}
