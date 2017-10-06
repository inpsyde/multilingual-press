<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Server;

use Inpsyde\MultilingualPress\Cache\Item\CacheItem;

/**
 * @package MultilingualPress
 * @license http://opensource.org/licenses/MIT MIT
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
	 * @var array
	 */
	private $delete_on = [];

	/**
	 * Constructor.
	 *
	 * @param string $namespace
	 * @param string $key
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
	 * @return array
	 */
	public function deleting_actions(): array {

		return $this->delete_on;
	}

	/**
	 * @return int
	 */
	public function time_to_live(): int {

		return $this->time_to_live > 1 ? $this->time_to_live : CacheItem::DEFAULT_TIME_TO_LIVE;
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
	 * @param callable $callback
	 * @param array    $args
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
	 * @param string[] ...$actions
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
	 * @param int $time_to_live
	 *
	 * @return ItemLogic
	 */
	public function live_for( int $time_to_live ): ItemLogic {

		$this->time_to_live = $time_to_live;

		return $this;
	}

	/**
	 * @param int $extension
	 *
	 * @return ItemLogic
	 */
	public function on_failure_extend_by( int $extension ): ItemLogic {

		$this->extension_on_failure = $extension;

		return $this;
	}

}