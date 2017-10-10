<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Cache\Server;

use Inpsyde\MultilingualPress\Cache\CacheFactory;
use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Exception;
use Inpsyde\MultilingualPress\Cache\Pool\CachePool;

use function Inpsyde\MultilingualPress\call_exit;

/**
 * @package Inpsyde\MultilingualPress\Cache\Server
 * @since   3.0.0
 */
class Server {

	const UPDATING_KEYS_TRANSIENT = 'mlp_cache_server_updating_keys';
	const SPAWNING_KEYS_TRANSIENT = 'mlp_cache_server_spawning_keys_';
	const HEADER_KEY = 'MLP-Cache-Update-Key';
	const HEADER_TTL = 'MLP-Cache-Update-TTL';

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
	private $network_driver;

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
	 * @param CacheFactory $factory        Server cache factory.
	 * @param CacheDriver  $driver         Server cache driver.
	 * @param CacheDriver  $network_driver Server cache driver for network values.
	 */
	public function __construct( CacheFactory $factory, CacheDriver $driver, CacheDriver $network_driver ) {

		$this->factory        = $factory;
		$this->driver         = $driver;
		$this->network_driver = $network_driver;
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
	 * @param ItemLogic $item_logic Item logic to register.
	 *
	 * @return Server
	 */
	public function register( ItemLogic $item_logic ): self {

		return $this->do_register( $item_logic, false );
	}

	/**
	 * @param ItemLogic $item_logic Item logic to register.
	 *
	 * @return Server
	 */
	public function register_for_network( ItemLogic $item_logic ): self {

		return $this->do_register( $item_logic, true );
	}

	/**
	 * Check whether the given pair of namespace and key is registered.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $key       Cache item key.
	 *
	 * @return bool
	 */
	public function is_registered( string $namespace, string $key ): bool {

		return array_key_exists( $this->full_key( $namespace, $key ), $this->registered );
	}

	/**
	 * @param string $namespace Cache item namespace.
	 * @param string $key       Cache item key.
	 *
	 * @return CachePool
	 */
	public function registered_pool( string $namespace, string $key ): CachePool {

		$this->bail_if_not_registered( $namespace, $key );

		/**
		 * @var bool $is_network
		 */
		list( $is_network ) = $this->registered[ $this->full_key( $namespace, $key ) ];

		return $is_network
			? $this->factory->create_for_network( $namespace, $this->network_driver )
			: $this->factory->create( $namespace, $this->driver );
	}

	/**
	 * On regular requests returns the cached (or just newly generated) value for a registered couple of namespace
	 * and key.
	 * In case the value is expired, it will be returned anyway, but an updating will be scheduled and will happen
	 * in a separate HEAD request and the expired cached value will continue to be served until the value is
	 * successfully updated.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $key       Cache item key.
	 *
	 * @return mixed
	 */
	public function claim( string $namespace, string $key ) {

		$this->bail_if_not_registered( $namespace, $key );

		$registered_key = $this->full_key( $namespace, $key );

		/**
		 * @var bool      $is_network
		 * @var ItemLogic $logic
		 */
		list( $is_network, $logic ) = $this->registered[ $registered_key ];

		$pool = $this->registered_pool( $namespace, $key );

		$item = $pool->item( $key );

		if ( $item->is_expired() && ! $this->is_queued_for_update( $namespace, $key ) ) {
			$this->queue_update( $registered_key, $logic->time_to_live(), $is_network );
		}

		if ( $item->is_hit() ) {
			return $item->value();
		}

		list( $new_value, $success ) = $this->fetch_updated_value( $logic->updater(), $logic->updater_args() );
		if ( $success ) {
			$item->live_for( $logic->time_to_live() )->set( $new_value );
		}

		return $new_value;
	}

	/**
	 * Once cached for first time values continue to be served from cache (automatically updated on expiration)
	 * unless this method is called to force flush of a specific namespace / key pair or of a whole namespace.
	 *
	 * @param string $namespace Cache item namespace.
	 * @param string $key       Cache item key.
	 *
	 * @return int
	 */
	public function flush( string $namespace, string $key = null ): int {

		if ( $key && $this->is_registered( $namespace, $key ) ) {
			$pool = $this->registered_pool( $namespace, $key );

			$deleted = $pool->delete( $key );
			if ( $deleted ) {
				$pool->item( $key )->sync_to_storage();
			}

			$this->spawn_queue = array_diff( $this->spawn_queue, [ $namespace . $key ] );

			return $deleted;

		} elseif ( null !== $key ) {

			return 0;
		}

		$flushed = 0;

		/** @var ItemLogic $logic */
		foreach ( $this->registered as list( , $logic ) ) {
			if ( $logic->namespace() === $namespace ) {
				$flushed += $this->flush( $namespace, $logic->key() );
			}
		}

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

		if ( ! $registered_key || empty( $this->registered[ $registered_key ] ) ) {
			return;
		}

		$updating_key = md5( $registered_key );

		/**
		 * @var bool      $is_network
		 * @var ItemLogic $logic
		 */
		list( $is_network, $logic ) = $this->registered[ $registered_key ];

		if ( $this->is_updating( $updating_key, $is_network ) ) {

			call_exit();

			return;
		}

		$this->mark_updating( $updating_key, $is_network );

		$item_key                  = $logic->key();
		$item_namespace            = $logic->namespace();
		$item_updater              = $logic->updater();
		$item_updater_args         = $logic->updater_args();
		$item_time_to_live         = $logic->time_to_live();
		$item_extension_on_failure = $logic->extension_on_failure();

		$pool = $this->registered_pool( $item_namespace, $item_key );

		$item = $pool->item( $item_key );
		$item->sync_from_storage();

		$current_value = $item->value();

		$item = $item->live_for( $item_time_to_live );

		list( $new_value, $success ) = $this->fetch_updated_value( $item_updater, $item_updater_args, $current_value );

		if ( ! $success && $item_extension_on_failure && $item->is_hit() ) {
			$item->live_for( $item_extension_on_failure )->set( $current_value );
		} elseif ( ! $success ) {
			$item->delete();
		}

		if ( $success ) {
			$item->set( $new_value );
		}

		$item->sync_to_storage();

		$this->mark_not_updating( $updating_key, $is_network );

		call_exit();
	}

	/**
	 * @param string $namespace Cache item namespace.
	 * @param string $key       Cache item key.
	 *
	 * @return bool
	 */
	public function is_queued_for_update( string $namespace, string $key ): bool {

		return array_key_exists( $this->full_key( $namespace, $key ), $this->spawn_queue );
	}

	/**
	 * @param string $namespace Cache item namespace.
	 * @param string $key       Cache item key.
	 *
	 * @return string
	 */
	private function full_key( string $namespace, string $key ) {

		return $this->factory->prefix() . $namespace . $key;
	}

	/**
	 * Adds the actions that will cause item flushing.
	 *
	 * @param ItemLogic $logic       Cache item logic to register.
	 * @param bool      $for_network True if item is for network.
	 *
	 * @return Server
	 *
	 * @throws Exception\BadCacheItemRegistration If called during shutdown on in a update request.
	 */
	private function do_register( ItemLogic $logic, bool $for_network ) {

		if ( $this->update_request_key() || doing_action( 'shutdown' ) ) {
			throw new Exception\BadCacheItemRegistration();
		}

		$namespace = $logic->namespace();
		$key       = $logic->key();

		$this->registered[ $this->full_key( $namespace, $key ) ] = [ $for_network, $logic ];

		$actions = $logic->deleting_actions();

		foreach ( $actions as $action ) {
			add_action( $action, function () use ( $logic ) {

				$this->flush( $logic->namespace(), $logic->key() );
			} );
		}

		return $this;
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

		return $key && strtoupper( $method ) === 'HEAD' ? $key : '';
	}

	/**
	 * Use transients to mark the given key as currently being updated in a update request, to prevent
	 * multiple concurrent updates.
	 *
	 * @param string $key        Cache item key.
	 * @param bool   $is_network True if key refers to a network value.
	 *
	 * @return bool
	 */
	private function mark_updating( string $key, bool $is_network ): bool {

		$keys = $is_network
			? get_site_transient( self::UPDATING_KEYS_TRANSIENT )
			: get_transient( self::UPDATING_KEYS_TRANSIENT );

		if ( ! is_array( $keys ) ) {
			$keys = [];
		}

		$keys[] = $key;

		return $is_network
			? set_site_transient( self::UPDATING_KEYS_TRANSIENT, $keys )
			: set_transient( self::UPDATING_KEYS_TRANSIENT, $keys );
	}

	/**
	 * Remove the given key from transient storage to mark given key again available for updates.
	 *
	 * @param string $key        Cache item key.
	 * @param bool   $is_network True if key refers to a network value.
	 *
	 * @return bool
	 */
	private function mark_not_updating( string $key, bool $is_network ): bool {

		$keys = $is_network
			? get_site_transient( self::UPDATING_KEYS_TRANSIENT )
			: get_transient( self::UPDATING_KEYS_TRANSIENT );

		if ( ! is_array( $keys ) ) {
			$keys = [];
		}

		unset( $keys[ $key ] );

		return $is_network
			? set_site_transient( self::UPDATING_KEYS_TRANSIENT, $keys )
			: set_transient( self::UPDATING_KEYS_TRANSIENT, $keys );
	}

	/**
	 * Use transients to check if the given key is currently being updated in a update request, to prevent
	 * multiple concurrent updates.
	 *
	 * @param string $key        Cache item key.
	 * @param bool   $is_network True if key refers to a network value.
	 *
	 * @return bool
	 */
	private function is_updating( $key, bool $is_network ): bool {

		$keys = $is_network
			? get_site_transient( self::UPDATING_KEYS_TRANSIENT )
			: get_transient( self::UPDATING_KEYS_TRANSIENT );

		return $keys && in_array( $key, (array) $keys, true );
	}

	/**
	 * Queue given key to be updated in a HTTP request.
	 * The first time it is called adds an action on shutdown that will actually process the queue
	 * and send updating HTTP requests.
	 *
	 * @param string $key          Cache item key.
	 * @param int    $time_to_live Time to live for the item.
	 * @param bool   $is_network   True if key refers to a network value.
	 */
	private function queue_update( string $key, int $time_to_live, bool $is_network ) {

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

		$this->spawn_queue[ $key ] = [ $key, $time_to_live, $is_network ];
	}

	/**
	 * Send multiple HTTP request to refresh registered cache items.
	 */
	private function spawn_queue(): array {

		if ( ! $this->spawn_queue || ! $this->in_spawn_queue ) {
			return [];
		}

		$requests = [];

		$keys = [
			'site'    => [],
			'network' => [],
		];

		foreach ( $this->spawn_queue as list( $key, $time_to_live, $is_network ) ) {

			if ( $this->is_spawning( $key, $is_network ) ) {
				continue;
			}

			$keys[ $is_network ? 'network' : 'site' ][] = $key;

			$requests[ $key ] = [
				'url'     => home_url(),
				'headers' => [
					self::HEADER_KEY => $key,
					self::HEADER_TTL => $time_to_live,
				],
				'data'    => '',
				'cookies' => [],
			];
		}

		$this->mark_spawning( true, ...$keys['network'] );
		$this->mark_spawning( false, ...$keys['site'] );

		\Requests::request_multiple( $requests, [
			'timeout'          => 0.01,
			'follow_redirects' => false,
			'blocking'         => false,
			'type'             => \Requests::HEAD,
		] );

		return $requests;
	}

	/**
	 * Use transients to mark the given key as currently being sent via an update request, to prevent
	 * multiple concurrent request.
	 * Transients will not be deleted manually, but are set with a very short expiration so they will expire and vanish
	 * in few seconds when (hopefully) all the parallel-executing updating requests finished.
	 *
	 * @param bool     $is_network True if keys refers to a network value.
	 * @param string[] ...$keys    Cache item keys.
	 */
	private function mark_spawning( bool $is_network, string ...$keys ) {

		$callback = $is_network ? 'set_site_transient' : 'set_transient';

		array_walk( $keys, function ( string $key, $i, callable $callback ) {

			$callback( self::SPAWNING_KEYS_TRANSIENT . md5( $key ), 1, 10 );

		}, $callback );
	}

	/**
	 * @param string $key        Cache item key.
	 * @param bool   $is_network True if keys refers to a network value.
	 *
	 * @return bool
	 */
	private function is_spawning( $key, bool $is_network ): bool {

		return $is_network
			? (bool) get_site_transient( self::SPAWNING_KEYS_TRANSIENT . md5( $key ) )
			: (bool) get_transient( self::SPAWNING_KEYS_TRANSIENT . md5( $key ) );
	}

	/**
	 * @param callable $updater       Cache item updater callback.
	 * @param array    $args          Cache item updater args.
	 * @param mixed    $current_value Currently cached item value.
	 *
	 * @return array
	 */
	private function fetch_updated_value( callable $updater, array $args, $current_value = null ) {

		try {

			array_unshift( $args, $current_value );
			$value   = $updater( ...$args );
			$success = true;

		} catch ( \Throwable $throwable ) {

			$value   = $current_value;
			$success = false;
		}

		return [ $value, $success ];
	}

	/**
	 * @param string $namespace Cache item namespace.
	 * @param string $key       Cache item key.
	 *
	 * @throws Exception\NotRegisteredCacheItem When required item is not registered.
	 */
	private function bail_if_not_registered( string $namespace, string $key ) {

		if ( ! $this->is_registered( $namespace, $key ) ) {
			throw Exception\NotRegisteredCacheItem::for_namespace_and_key( $namespace, $key );
		}
	}
}
