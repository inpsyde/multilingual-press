<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Cache\Driver;

use Andrew\StaticProxy;
use Brain\Monkey\Functions;
use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Driver\EphemeralCacheDriver;
use Inpsyde\MultilingualPress\Cache\Item\WPCacheItem;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Test case for the ephemeral cache driver class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class WPCacheItemTest extends TestCase {

	private $driver;
	private $executing = true;
	private $storage_sync = false;

	public function setUp() {

		parent::setUp();

		Functions::when( 'get_current_blog_id' )->justReturn( 1 );

		$this->executing    = true;
		$this->storage_sync = false;

		$real_driver  = new EphemeralCacheDriver();
		$this->driver = \Mockery::mock( CacheDriver::class );

		$this->driver->shouldReceive( 'read' )->andReturnUsing( [ $real_driver, 'read' ] );

		$this->driver->shouldReceive( 'write' )
			->atMost()
			->once()
			->andReturnUsing( function ( ...$args ) use ( $real_driver ) {

				if ( $this->executing ) {
					throw new AssertionFailedError( 'Driver write should happen once on item destruct.' );
				}

				if ( $this->storage_sync ) {
					throw new AssertionFailedError( 'Only one storage sync operation should happen per object.' );
				}

				$this->storage_sync = true;

				return $real_driver->write( ...$args );
			} );

		$this->driver->shouldReceive( 'delete' )
			->atMost()
			->once()
			->andReturnUsing( function ( ...$args ) use ( $real_driver ) {

				if ( $this->executing ) {
					throw new AssertionFailedError( 'Driver delete should happen once on item destruct.' );
				}

				if ( $this->storage_sync ) {
					throw new AssertionFailedError( 'Only one storage sync operation should happen per object.' );
				}

				$this->storage_sync = true;

				return $real_driver->delete( ...$args );
			} );
	}

	public function tearDown() {

		$proxy        = new StaticProxy( EphemeralCacheDriver::class );
		$proxy->cache = [];

		$this->driver = null;

		parent::tearDown();
	}

	public function test_empty_item() {

		$item = new WPCacheItem( $this->driver, 'foo' );

		static::assertFalse( $item->is_hit() );
		static::assertFalse( $item->is_expired() );
		static::assertNull( $item->value() );

		// This will allow the storage driver write/delete to happen on $item object destruction
		$this->executing = false;
	}

	public function test_set_and_value() {

		$item = new WPCacheItem( $this->driver, 'foo' );

		$item->set( 'Hi!' );

		static::assertTrue( $item->is_hit() );
		static::assertSame( 'Hi!', $item->value() );
		static::assertFalse( $item->is_expired() );

		// This will allow the storage driver write/delete to happen on $item object destruction
		$this->executing = false;
	}

	public function test_item_state_cycle() {

		$item = new WPCacheItem( $this->driver, 'foo' );

		static::assertFalse( $item->is_hit() );
		static::assertNull( $item->value() );

		$item->set( 'X!' );

		static::assertTrue( $item->is_hit() );
		static::assertSame( 'X!', $item->value() );

		$item->delete();

		static::assertFalse( $item->is_hit() );
		static::assertNull( $item->value() );

		$item->set( 'Y!' );

		static::assertTrue( $item->is_hit() );
		static::assertSame( 'Y!', $item->value() );

		$item->delete();

		// This will allow the storage driver write/delete to happen on $item object destruction
		$this->executing = false;
	}

	public function test_live_for() {

		$item = new WPCacheItem( $this->driver, 'foo', 100 );

		$item->set( 'X!' );

		static::assertFalse( $item->is_expired() );
		static::assertSame( 'X!', $item->value() );

		$item->live_for( -1000 );

		static::assertTrue( $item->is_expired() );
		static::assertSame( 'X!', $item->value() );

		$this->executing = false;
	}

	public function test_storage_sync() {

		$this->executing = false;

		$item = new WPCacheItem( $this->driver, 'foo', 100 );
		$item->set( 'Cached!' );

		$item->sync_to_storage();
		$this->storage_sync = false;

		$new_item = new WPCacheItem( $this->driver, 'foo', 100 );

		static::assertSame( 'Cached!', $item->value() );
		static::assertSame( 'Cached!', $new_item->value() );

		$item->delete();

		static::assertNull( $item->value() );
		static::assertSame( 'Cached!', $new_item->value() );

		$new_item->sync_to_storage();
		$this->storage_sync = false;

		$third_item = new WPCacheItem( $this->driver, 'foo', 100 );

		static::assertSame( 'Cached!', $new_item->value() );
		static::assertSame( 'Cached!', $third_item->value() );
	}

}
