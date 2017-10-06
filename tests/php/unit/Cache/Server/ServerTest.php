<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Cache\Server;

use Andrew\StaticProxy;
use Brain\Monkey\Functions;
use Brain\Monkey\WP\Actions;
use Inpsyde\MultilingualPress\Cache\CacheFactory;
use Inpsyde\MultilingualPress\Cache\Driver\EphemeralCacheDriver;
use Inpsyde\MultilingualPress\Cache\Server\ItemLogic;
use Inpsyde\MultilingualPress\Cache\Server\Server;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * Test case for the WP cache pool class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class ServerTest extends TestCase {

	private $server_data_backup;

	public function setUp() {

		parent::setUp();
		$this->server_data_backup = $_SERVER;
	}

	public function tearDown() {

		$_SERVER = $this->server_data_backup;

		$proxy        = new StaticProxy( EphemeralCacheDriver::class );
		$proxy->cache = [];

		parent::tearDown();
	}

	public function test_register_fails_during_update_requests() {

		$_SERVER['REQUEST_METHOD']               = 'HEAD';
		$_SERVER[ 'HTTP_' . Server::HEADER_KEY ] = 'xyz';

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );

		/** @var CacheFactory $factory */
		$factory = \Mockery::mock( CacheFactory::class );

		$server = new Server( $factory, $driver, $driver );

		$this->expectException( \BadMethodCallException::class );

		$server->register( new ItemLogic( 'foo', 'bar' ) );
	}

	public function test_register_fails_during_shutdown() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( true );

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );

		/** @var CacheFactory $factory */
		$factory = \Mockery::mock( CacheFactory::class );

		$server = new Server( $factory, $driver, $driver );

		$this->expectException( \BadMethodCallException::class );

		$server->register( new ItemLogic( 'foo', 'bar' ) );
	}

	public function test_register_and_register_for_network_use_different_drivers() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );

		$driver     = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );
		$net_driver = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP | EphemeralCacheDriver::FOR_NETWORK );

		$server = new Server( new CacheFactory( 'tests_' ), $driver, $net_driver );

		$server
			->register( new ItemLogic( 'test', 'site' ) )
			->register_for_network( new ItemLogic( 'test', 'network' ) );

		$pool     = $server->registered_pool( 'test', 'site' );
		$net_pool = $server->registered_pool( 'test', 'network' );

		static::assertFalse( $pool->is_for_network() );
		static::assertTrue( $net_pool->is_for_network() );

	}

	public function test_register_adds_flushing_actions() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );
		$server = new Server( new CacheFactory( 'tests_' ), $driver, $driver );

		$logic = new ItemLogic( 'test', 'foo' );

		Actions::expectAdded( 'foo' )->once();
		Actions::expectAdded( 'bar' )->once();

		$server->register( $logic->delete_on( 'foo', 'bar' ) );
	}

	public function test_is_registered() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );
		$server = new Server( new CacheFactory( 'tests_' ), $driver, $driver );

		$server->register( new ItemLogic( 'test', 'foo' ) );

		static::assertTrue( $server->is_registered( 'test', 'foo' ) );
		static::assertFalse( $server->is_registered( 'test', 'bar' ) );

	}

	public function test_registered_pool_fails_if_not_registered() {

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );
		$server = new Server( new CacheFactory( 'tests_' ), $driver, $driver );

		static::expectException( \OutOfRangeException::class );

		$server->registered_pool( 'foo', 'bar' );
	}

	public function test_claim_fails_if_not_registered() {

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );
		$server = new Server( new CacheFactory( 'tests_' ), $driver, $driver );

		static::expectException( \OutOfRangeException::class );

		$server->claim( 'foo', 'bar' );
	}

	public function test_claim_returns_value_for_cache_miss() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );

		$server = new Server( new CacheFactory( 'tests_' ), $driver, $driver );

		$item_logic = new ItemLogic( 'foo', 'bar' );
		$item_logic->update_with(
			function ( $null, $arg ) {

				static::assertNull( $null );
				static::assertSame( 'updater argument', $arg );

				return 'Cached!';
			},
			'updater argument'
		);

		$server->register( $item_logic );

		static::assertSame( 'Cached!', $server->claim( 'foo', 'bar' ) );

	}

	public function test_claim_returns_value_for_expired_cache_and_schedule_update() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );
		Functions::expect( 'get_transient' )->andReturn( false );
		Functions::expect( 'set_transient' )->andReturn( true );
		Functions::expect( 'get_current_blog_id' )->andReturn( 1 );

		Actions::expectAdded( 'shutdown' )->once();

		$driver  = new EphemeralCacheDriver();
		$factory = new CacheFactory( 'tests_' );

		$item = $factory->create( 'foo', $driver )->set( 'bar', 'Expired value!', - 1000 );
		$item->sync_to_storage();

		$server = new Server( $factory, $driver, $driver );

		$item_logic = new ItemLogic( 'foo', 'bar' );
		$item_logic
			->live_for( 3600 )
			->update_with( static function ( $value ) {

				static::assertSame( 'Expired value!', $value );

				return 'Updated value!';
			} );

		$server->register( $item_logic );

		static::assertTrue( $item->is_expired() );
		static::assertSame( 'Expired value!', $server->claim( 'foo', 'bar' ) );
		static::assertTrue( $server->is_queued_for_update( 'foo', 'bar' ) );

		$_SERVER['REQUEST_METHOD']               = 'HEAD';
		$_SERVER[ 'HTTP_' . Server::HEADER_KEY ] = 'tests_foobar';

		$server->listen_spawn();

		$item->sync_from_storage();

		static::assertFalse( $item->is_expired() );
		static::assertSame( 'Updated value!', $item->value() );
	}

	public function test_claim_returns_value_for_valid_cache_and_not_schedule_update() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );
		Functions::expect( 'get_transient' )->andReturn( false );
		Functions::expect( 'set_transient' )->andReturn( true );
		Functions::expect( 'get_current_blog_id' )->andReturn( 1 );

		Actions::expectAdded( 'shutdown' )->never();

		$driver  = new EphemeralCacheDriver();
		$factory = new CacheFactory( 'tests_' );

		$item = $factory->create( 'foo', $driver )->set( 'bar', 'Valid value!', 3600 );
		$item->sync_to_storage();

		$server = new Server( $factory, $driver, $driver );

		$item_logic = new ItemLogic( 'foo', 'bar' );
		$item_logic
			->live_for( 3600 )
			->update_with( static function () {

				/** @noinspection PhpUndefinedMethodInspection */
				\Mockery::mock()->shouldReceive( 'dont_run_this' )->never()->getMock()->dont_run_this();
			} );

		$server->register( $item_logic );

		static::assertFalse( $item->is_expired() );
		static::assertSame( 'Valid value!', $server->claim( 'foo', 'bar' ) );
		static::assertFalse( $server->is_queued_for_update( 'foo', 'bar' ) );
	}

	public function test_flush_single_key() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );
		Functions::expect( 'get_current_blog_id' )->andReturn( 1 );
		Functions::when( '__return_null' )->justReturn();

		$driver  = new EphemeralCacheDriver();
		$factory = new CacheFactory( 'tests_' );

		$bar = $factory->create( 'foo', $driver )->set( 'bar', 'Bar!', 3600 );
		$bar->sync_to_storage();

		$baz = $factory->create( 'foo', $driver )->set( 'baz', 'Baz!', 3600 );
		$baz->sync_to_storage();

		$server = new Server( $factory, $driver, $driver );

		$server
			->register( new ItemLogic( 'foo', 'bar' ) )
			->register( new ItemLogic( 'foo', 'baz' ) );

		static::assertSame( 'Bar!', $server->claim( 'foo', 'bar' ) );
		static::assertSame( 'Baz!', $server->claim( 'foo', 'baz' ) );

		$server->flush( 'foo', 'bar' );

		static::assertNull( $server->claim( 'foo', 'bar' ) );
		static::assertSame( 'Baz!', $server->claim( 'foo', 'baz' ) );
	}

	public function test_flush_namespace() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );
		Functions::expect( 'get_current_blog_id' )->andReturn( 1 );
		Functions::when( '__return_null' )->justReturn();

		$driver  = new EphemeralCacheDriver();
		$factory = new CacheFactory( 'tests_' );

		$bar = $factory->create( 'foo', $driver )->set( 'bar', 'Bar!', 3600 );
		$bar->sync_to_storage();

		$baz = $factory->create( 'foo', $driver )->set( 'baz', 'Baz!', 3600 );
		$baz->sync_to_storage();

		$baz = $factory->create( 'hello', $driver )->set( 'bar', 'Hello Bar!', 3600 );
		$baz->sync_to_storage();

		$server = new Server( $factory, $driver, $driver );

		$server
			->register( new ItemLogic( 'foo', 'bar' ) )
			->register( new ItemLogic( 'foo', 'baz' ) )
			->register( new ItemLogic( 'hello', 'bar' ) );

		static::assertSame( 'Bar!', $server->claim( 'foo', 'bar' ) );
		static::assertSame( 'Baz!', $server->claim( 'foo', 'baz' ) );
		static::assertSame( 'Hello Bar!', $server->claim( 'hello', 'bar' ) );

		$server->flush( 'foo' );

		static::assertNull( $server->claim( 'foo', 'bar' ) );
		static::assertNull( $server->claim( 'foo', 'baz' ) );
		static::assertSame( 'Hello Bar!', $server->claim( 'hello', 'bar' ) );
	}

	public function test_spawn_requests() {

		Functions::expect( 'doing_action' )->with( 'shutdown' )->andReturn( false );
		Functions::expect( 'get_current_blog_id' )->andReturn( 1 );
		Functions::expect( 'get_transient' )->andReturn( false );
		Functions::expect( 'set_transient' )->andReturn( true );
		Functions::expect( 'home_url' )->andReturn( 'https://example.com' );

		/** @var callable $spawn */
		$spawn = null;
		Actions::expectAdded( 'shutdown' )->once()->whenHappen( function ( callable $callable ) use ( &$spawn ) {

			$spawn = $callable;
		} );

		$driver  = new EphemeralCacheDriver();
		$factory = new CacheFactory( 'tests_' );

		$item = $factory->create( 'foo', $driver )->set( 'bar', 'Expired value!', - 1000 );
		$item->sync_to_storage();

		$server = new Server( $factory, $driver, $driver );

		$item_logic = new ItemLogic( 'foo', 'bar' );

		$server->register( $item_logic->live_for( 123456 ) );
		$server->claim( 'foo', 'bar' );

		// Simulate what happen on shutdown
		$spawn();

		// @see /tests/stubs.php for the Requests class definition in
		$calls               = \Requests::$static_calls;
		$proxy               = new StaticProxy( \Requests::class );
		$proxy->static_calls = [];

		$request_multiple      = $calls['request_multiple'] ?? [];
		$request_multiple_args = reset( $request_multiple ) ?: [ [], [] ];
		list( $requests_args, $options ) = $request_multiple_args;

		static::assertArrayHasKey( 'tests_foobar', $requests_args );
		static::assertArrayHasKey( 'headers', $requests_args['tests_foobar'] );
		static::assertArrayHasKey( Server::HEADER_KEY, $requests_args['tests_foobar']['headers'] );
		static::assertArrayHasKey( Server::HEADER_TTL, $requests_args['tests_foobar']['headers'] );
		static::assertSame( 'tests_foobar', $requests_args['tests_foobar']['headers'][ Server::HEADER_KEY ] );
		static::assertSame( 123456, $requests_args['tests_foobar']['headers'][ Server::HEADER_TTL ] );

		static::assertArrayHasKey( 'type', $options );
		static::assertSame( 'HEAD', $options['type'] );
	}
}
