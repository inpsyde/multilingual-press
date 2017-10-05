<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Server;

use Inpsyde\MultilingualPress\Cache\CacheFactory;
use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Item\CacheItem;
use Inpsyde\MultilingualPress\Cache\Pool\CachePool;

/**
 * @package Inpsyde\MultilingualPress\Cache
 * @since   3.0.0
 */
class Server {

	const UPDATING_KEYS_TRANSIENT = 'mlp_cache_server_updating_keys';
	const SPAWNING_KEYS_TRANSIENT = 'mlp_cache_server_spawning_keys_';
	const HEADER_KEY = 'X-MLP-Cache-Update-Key';
	const HEADER_TTL = 'X-MLP-Cache-Update-TTL';

	/**
	 * @var CacheFactory
	 */
	private $factory;

	/**
	 * @var CacheDriver
	 */
	private $driver;

	/**
	 * @var CacheDriver
	 */
	private $sitewide_driver;

	/**
	 * @var array[]
	 */
	private $registered = [];

	/**
	 * @var string[]
	 */
	private $spawn_queue = [];

	/**
	 * @var bool
	 */
	private $in_spawn_queue = false;

	/**
	 * Constructor.
	 *
	 * @param CacheFactory $factory
	 * @param CacheDriver  $driver
	 * @param CacheDriver  $sitewide_driver
	 */
	public function __construct( CacheFactory $factory, CacheDriver $driver, CacheDriver $sitewide_driver ) {

		$this->factory         = $factory;
		$this->driver          = $driver;
		$this->sitewide_driver = $sitewide_driver;
	}

	/**
	 * On regular requests it is possible to register a callback to generates same value to cache and associate it
	 * with an unique key in the also given pool.
	 * This should be called early, because the values can only be then "claimed" (which is retrieved for actual use)
	 * after registration.
	 *
	 * The value generated will be valid for the given TTL or for the default one (1 hour).
	 * When the value is expired it will returned anyway, but an updating will be scheduled.
	 * The scheduled updates happens in separate HEAD requests.
	 *
	 * It means that once cached for first time a value will be served always from cache (unless manually flushed) and
	 * updated automatically on expiration without affecting user request time.
	 *
	 * @param string   $namespace
	 * @param string   $key
	 * @param callable $updater
	 * @param int      $time_to_live
	 *
	 * @return Server
	 */
	public function register( string $namespace, string $key, callable $updater, int $time_to_live = 0 ): self {

		if ( $this->update_request_key() || doing_action( 'shutdown' ) ) {
			throw new \BadMethodCallException( __METHOD__ . ' is not available during cache update requests.' );
		}

		if ( $time_to_live < 30 ) {
			$time_to_live = CacheItem::DEFAULT_TIME_TO_LIVE;
		}

		$this->registered[ $namespace . $key ] = [ false, $namespace, $key, $updater, $time_to_live ];

		return $this;
	}

	/**
	 * @param string   $namespace
	 * @param string   $key
	 * @param callable $updater
	 * @param int      $time_to_live
	 *
	 * @return Server
	 */
	public function register_sitewide( string $namespace, string $key, callable $updater, int $time_to_live = 0 ): self {

		if ( $this->update_request_key() || doing_action( 'shutdown' ) ) {
			throw new \BadMethodCallException( __METHOD__ . ' is not available during cache update requests.' );
		}

		if ( $time_to_live < 30 ) {
			$time_to_live = CacheItem::DEFAULT_TIME_TO_LIVE;
		}

		$this->registered[ $namespace . $key ] = [ true, $namespace, $key, $updater, $time_to_live ];

		return $this;
	}

	/**
	 * Check whether the given pair of namespace and key is registered.
	 *
	 * @param string $namespace
	 * @param string $key
	 *
	 * @return bool
	 */
	public function is_registered( string $namespace, string $key ): bool {

		return ! empty( $this->registered[ $namespace . $key ] );

	}

	/**
	 * @param string $namespace
	 * @param string $key
	 *
	 * @return CachePool
	 */
	public function registered_pool( string $namespace, string $key ): CachePool {

		/**
		 * @var bool   $sitewide
		 * @var string $namespace
		 * @var string $key
		 */
		list( $sitewide, $namespace ) = $this->registered[ $namespace . $key ];

		return $sitewide
			? $this->factory->create_sitewide( $namespace, $this->sitewide_driver )
			: $this->factory->create( $namespace, $this->driver );
	}

	/**
	 * On regular requests returns the cached (or just newly generated) value for a registered couple of namespace
	 * and key.
	 * In case the value is expired, it will be returned anyway, but an updating will be scheduled and will happen
	 * in a separate HEAD request and the expired cached value will continue to be served until the value is
	 * successfully updated.
	 *
	 * @param string $namespace
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function claim( string $namespace, string $key ) {

		if ( empty( $this->registered[ $namespace . $key ] ) ) {
			throw new \BadMethodCallException( __CLASS__ . ' can only server registered keys.' );
		}

		/**
		 * @var bool     $sitewide
		 * @var string   $namespace
		 * @var string   $key
		 * @var callable $updater
		 * @var int      $time_to_live
		 */
		list( $sitewide, $namespace, $key, $updater, $time_to_live ) = $this->registered[ $namespace . $key ];

		$pool = $sitewide
			? $this->factory->create_sitewide( $namespace, $this->sitewide_driver )
			: $this->factory->create( $namespace, $this->driver );

		$item = $pool->item( $key );

		if ( $item->is_expired() ) {
			$this->queue_update( $key, $time_to_live, $sitewide );
		}

		if ( $item->is_hit() ) {
			return $item->value();
		}

		$value = $updater( $item );
		$item->expires_after( $time_to_live )->set( $value );

		return $value;
	}

	/**
	 * Once cached for first time values continue to be served from cache (automatically updated on expiration)
	 * unless this method is called to force flush of a specific namespace / key pair or of a whole namespace.
	 *
	 * @param string $namespace
	 * @param string $key
	 *
	 * @return int
	 */
	public function flush( string $namespace, string $key = null ): int {

		if ( $key ) {
			$done = $this->is_registered( $namespace, $key )
				? (int) $this->registered_pool( $namespace, $key )->delete( $key )
				: 0;

			$this->spawn_queue = array_diff( $this->spawn_queue, [ $namespace . $key ] );

			return $done;
		}

		$flushed = 0;
		$dequeue = [];
		foreach ( $this->registered as list( $sitewide, $reg_namespace, $key ) ) {
			if ( $reg_namespace === $namespace ) {
				$flushed   += $this->registered_pool( $namespace, $key )->delete( $key );
				$dequeue[] = $namespace . $key;
			}
		}

		$this->spawn_queue = array_diff( $this->spawn_queue, $dequeue );

		return $flushed;
	}

	/**
	 * When an expired value is requested, it is returned to claiming code, and an HTTP HEAD request is sent
	 * to home page containing headers with information about key and the TTL.
	 * This methods check them and if the request fits criteria update the value using the registered callable.
	 *
	 * @return void
	 */
	public function listen_spawn() {

		$registered_key = $this->update_request_key();

		if ( ! $registered_key ) {
			return;
		}

		/**
		 * @var bool     $sitewide
		 * @var string   $namespace
		 * @var string   $key
		 * @var callable $updater
		 * @var int      $time_to_live
		 */
		list( $sitewide, $namespace, $key, $updater, $time_to_live ) = $this->registered[ $registered_key ];

		if ( $this->is_updating( $key, $sitewide ) ) {
			return;
		}

		$pool = $sitewide
			? $this->factory->create_sitewide( $namespace, $this->sitewide_driver )
			: $this->factory->create( $namespace, $this->driver );

		$this->mark_updating( $key, $sitewide );

		$item = $pool->item( $key );
		$item->expires_after( $time_to_live )->set( $updater( $item ) );

		$this->mark_not_updating( $key, $sitewide );

		add_action( 'multilingualpress.after_server_update_value', function () {

			exit();
		}, 100 );

		do_action( 'multilingualpress.after_server_update_value' );
	}

	/**
	 * Check the HTTP request to see if it is a cache update request and if the eventually requested key is registered.
	 * If so return it, otherwise return an empty string.
	 *
	 * @return string
	 */
	private function update_request_key(): string {

		$method = $_SERVER['REQUEST_METHOD'] ?? '';
		$key    = $_SERVER[ 'HTTP_' . self::HEADER_KEY ] ?? '';

		return $key && ! empty( $this->registered[ $key ] ) && strtoupper( $method ) === 'HEAD' ? $key : '';
	}

	/**
	 * Use transients to mark the given key as currently being updated in a update request, to prevent
	 * multiple concurrent updates.
	 *
	 * @param string $key
	 * @param bool   $sitewide
	 *
	 * @return bool
	 */
	private function mark_updating( string $key, bool $sitewide ): bool {

		$keys = $sitewide
			? get_site_transient( self::UPDATING_KEYS_TRANSIENT )
			: get_transient( self::UPDATING_KEYS_TRANSIENT );

		is_array( $keys ) or $keys = [];

		$keys[] = $key;

		return $sitewide
			? set_site_transient( self::UPDATING_KEYS_TRANSIENT, $keys )
			: set_transient( self::UPDATING_KEYS_TRANSIENT, $keys );
	}

	/**
	 * Remove the given key from transient storage to mark given key again available for updates.
	 *
	 * @param string $key
	 * @param bool   $sitewide
	 *
	 * @return bool
	 */
	private function mark_not_updating( string $key, bool $sitewide ): bool {

		$keys = $sitewide
			? get_site_transient( self::UPDATING_KEYS_TRANSIENT )
			: get_transient( self::UPDATING_KEYS_TRANSIENT );

		return $sitewide
			? set_site_transient( self::UPDATING_KEYS_TRANSIENT, array_diff( (array) $keys, [ $key ] ) )
			: set_transient( self::UPDATING_KEYS_TRANSIENT, array_diff( (array) $keys, [ $key ] ) );
	}

	/**
	 * Use transients to check if the given key is currently being updated in a update request, to prevent
	 * multiple concurrent updates.
	 *
	 * @param string $key
	 * @param bool   $sitewide
	 *
	 * @return bool
	 */
	private function is_updating( $key, bool $sitewide ): bool {

		$keys = $sitewide
			? get_site_transient( self::UPDATING_KEYS_TRANSIENT )
			: get_transient( self::UPDATING_KEYS_TRANSIENT );

		return $keys && in_array( $key, (array) $keys, true );
	}

	/**
	 * Queue given key to be updated in a HTTP request.
	 * The first time it is called adds an action on shutdown that will actually process the queue
	 * and send updating HTTP requests.
	 *
	 * @param string $key
	 * @param int    $time_to_live
	 * @param bool   $sitewide
	 */
	private function queue_update( string $key, int $time_to_live, bool $sitewide ) {

		if ( $this->in_spawn_queue ) {
			return;
		}

		if ( ! $this->spawn_queue && ! did_action( 'shutdown' ) ) {
			add_action( 'shutdown', function () {

				$this->in_spawn_queue = true;
				$this->spawn_queue();
				$this->spawn_queue = [];
			}, 50 );
		}

		$this->spawn_queue[] = [ $key, $time_to_live, $sitewide ];
	}

	/**
	 * Send multiple HTTP request to refresh registered cache items.
	 */
	private function spawn_queue() {

		if ( ! $this->spawn_queue || ! $this->in_spawn_queue ) {
			return;
		}

		$requests      = [];
		$sitewide_keys = $keys = [];

		foreach ( $this->spawn_queue as list( $key, $time_to_live, $sitewide ) ) {

			if ( $this->is_spawning( $key, $sitewide ) ) {
				continue;
			}

			$sitewide ? $sitewide_keys[] = $key : $keys[] = $key;

			$requests[ $key ] = [
				'url'     => home_url(),
				'headers' => [
					self::HEADER_KEY => $key,
					self::HEADER_TTL => $time_to_live,
				],
				'data'    => '',
				'type'    => \Requests::HEAD,
				'cookies' => [],
			];
		}

		$this->mark_spawning( true, ...$sitewide_keys );
		$this->mark_spawning( false, ...$keys );

		\Requests::request_multiple(
			$requests,
			[
				'timeout'          => 0.01,
				'follow_redirects' => false,
				'blocking'         => false,
				'type'             => \Requests::HEAD,
			]
		);
	}

	/**
	 * Use transients to mark the given key as currently being sent via an update request, to prevent
	 * multiple concurrent request.
	 * Transient will no be deleted manually, but are set with a very short expiration so they will expire and vanish
	 * in few seconds when hopefully the updating requests finished.
	 *
	 * @param bool     $sitewide
	 * @param string[] $keys
	 */
	private function mark_spawning( bool $sitewide, string ...$keys ) {

		foreach ( $keys as $key ) {
			$sitewide
				? set_site_transient( self::SPAWNING_KEYS_TRANSIENT . md5( $key ), 1, 10 )
				: set_transient( self::SPAWNING_KEYS_TRANSIENT . md5( $key ), 1, 10 );
		}
	}

	/**
	 * @param string $key
	 * @param bool   $sitewide
	 *
	 * @return bool
	 */
	private function is_spawning( $key, bool $sitewide ): bool {

		return $sitewide
			? (bool) get_site_transient( self::SPAWNING_KEYS_TRANSIENT . md5( $key ) )
			: (bool) get_transient( self::SPAWNING_KEYS_TRANSIENT . md5( $key ) );
	}

}