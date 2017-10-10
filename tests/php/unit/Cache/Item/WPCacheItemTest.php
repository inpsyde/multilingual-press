<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Cache\Driver;

use Andrew\StaticProxy;
use Brain\Monkey\Functions;
use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Driver\EphemeralCacheDriver;
use Inpsyde\MultilingualPress\Cache\Item\Value;
use Inpsyde\MultilingualPress\Cache\Item\WPCacheItem;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * Test case for the ephemeral cache driver class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class WPCacheItemTest extends TestCase {

	public function setUp() {

		parent::setUp();

		Functions::when( 'get_current_blog_id' )->justReturn( 1 );
	}

	public function tearDown() {

		$proxy        = new StaticProxy( EphemeralCacheDriver::class );
		$proxy->cache = [];

		parent::tearDown();
	}

	public function test_empty_item() {

		$item = new WPCacheItem( new EphemeralCacheDriver(), 'foo' );

		static::assertFalse( $item->is_hit() );
		static::assertFalse( $item->is_expired() );
		static::assertNull( $item->value() );
	}

	public function test_set_and_value() {

		$item = new WPCacheItem( new EphemeralCacheDriver(), 'foo' );

		$item->set( 'Hi!' );

		static::assertTrue( $item->is_hit() );
		static::assertSame( 'Hi!', $item->value() );
		static::assertFalse( $item->is_expired() );
	}

	public function test_item_state_cycle() {

		$item = new WPCacheItem( new EphemeralCacheDriver(), 'foo' );

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
	}

	public function test_live_for() {

		$item = new WPCacheItem( new EphemeralCacheDriver(), 'foo', 100 );

		$item->set( 'X!' );

		static::assertFalse( $item->is_expired() );
		static::assertSame( 'X!', $item->value() );
		static::assertTrue( $item->is_hit() );

		$item->live_for( - 1000 );

		static::assertTrue( $item->is_hit() );
		static::assertTrue( $item->is_expired() );
		static::assertSame( 'X!', $item->value() );
	}

	public function test_storage_sync() {

		$item = new WPCacheItem( new EphemeralCacheDriver(), 'foo', 100 );
		$item->set( 'Cached!' );

		$item->sync_to_storage();

		$new_item = new WPCacheItem( new EphemeralCacheDriver(), 'foo', 100 );

		static::assertSame( 'Cached!', $item->value() );
		static::assertSame( 'Cached!', $new_item->value() );

		$item->delete();

		static::assertNull( $item->value() );
		static::assertSame( 'Cached!', $new_item->value() );

		$new_item->sync_to_storage();
		$this->storage_sync = false;

		$third_item = new WPCacheItem( new EphemeralCacheDriver(), 'foo', 100 );

		static::assertSame( 'Cached!', $new_item->value() );
		static::assertSame( 'Cached!', $third_item->value() );
	}

	public function test_storage_sync_happen_once_on_destruction() {

		/** @var CacheDriver|\Mockery\MockInterface $driver */
		$driver = \Mockery::mock( CacheDriver::class );
		$driver->shouldReceive('read')->atLeast()->once()->andReturn( new Value() );
		$driver->shouldReceive('write')->once();

		$item = new WPCacheItem( $driver, 'foo' );

		$item->set( 1 );
		$item->set( 2 );
		$item->set( 3 );

		$value = $item->value();

		unset( $item );

		static::assertSame( 3, $value );
	}

}
