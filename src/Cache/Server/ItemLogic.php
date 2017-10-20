<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Server;

use Inpsyde\MultilingualPress\Cache\Item\CacheItem;

/**
 * @package Inpsyde\MultilingualPress\Cache\Server
 * @since   3.0.0
 */
class ItemLogic {

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var callable|null
	 */
	private $updater;

	/**
	 * @var array
	 */
	private $updater_args = [];

	/**
	 * @var int
	 */
	private $time_to_live = 0;

	/**
	 * @var int
	 */
	private $extension_on_failure = 0;

	/**
	 * @var string[]
	 */
	private $delete_on = [];

	/**
	 * Constructor.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $key       Cache item key.
	 */
	public function __construct( string $namespace, string $key ) {

		$this->namespace = $namespace;
		$this->key       = $key;
	}

	/**
	 * @return string
	 */
	public function namespace(): string {

		return $this->namespace;
	}

	/**
	 * @return string
	 */
	public function key(): string {

		return $this->key;
	}

	/**
	 * @return callable
	 */
	public function updater(): callable {

		return $this->updater ?: '__return_null';
	}

	/**
	 * @return array
	 */
	public function updater_args(): array {

		return $this->updater_args;
	}

	/**
	 * @return string[]
	 */
	public function deleting_actions(): array {

		return $this->delete_on;
	}

	/**
	 * @return int
	 */
	public function time_to_live(): int {

		return $this->time_to_live > 1 ? $this->time_to_live : CacheItem::LIFETIME_IN_SECONDS;
	}

	/**
	 * @return int
	 */
	public function extension_on_failure(): int {

		return max( $this->extension_on_failure, 0 );
	}

	/**
	 * Set the callback and the optional arguments that will be used to bot generate and update the cache item value.
	 *
	 * The callback should throw an exception when the generation of the value fails.
	 *
	 * @param callable $callback Callback that will generate the cached value.
	 * @param array    ...$args  Optional, additional args to pass to updater callback, 1st arg is always current value.
	 *
	 * @return ItemLogic
	 */
	public function update_with( callable $callback, ...$args ): ItemLogic {

		$this->updater      = $callback;
		$this->updater_args = $args;

		return $this;
	}

	/**
	 * Set the action hook that will trigger the deletion of cached value.
	 *
	 * @param string[] ...$actions WordPress hooks that will cause an item cache flush.
	 *
	 * @return ItemLogic
	 */
	public function delete_on( string ...$actions ): ItemLogic {

		$this->delete_on = $actions;

		return $this;
	}

	/**
	 * Set the time to live for the cached value.
	 *
	 * @param int $time_to_live Time to live in seconds for the cache item.
	 *
	 * @return ItemLogic
	 */
	public function live_for( int $time_to_live ): ItemLogic {

		$this->time_to_live = $time_to_live;

		return $this;
	}

	/**
	 * @param int $extension Duration in seconds to be set as time to live for an expired item when its update fails.
	 *
	 * @return ItemLogic
	 */
	public function on_failure_extend_by( int $extension ): ItemLogic {

		$this->extension_on_failure = $extension;

		return $this;
	}

}
