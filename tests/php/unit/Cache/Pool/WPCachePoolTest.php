<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Cache\Driver;

use Inpsyde\MultilingualPress\Cache\Driver\EphemeralCacheDriver as TestDriver;
use Inpsyde\MultilingualPress\Cache\Driver\EphemeralCacheDriver;
use Inpsyde\MultilingualPress\Cache\Pool\WPCachePool;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * Test case for the WP cache pool class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class WPCachePoolTest extends TestCase {

	public function test_side_wide_depends_on_driver() {

		$sw_pool = new WPCachePool( 'foo', new TestDriver( TestDriver::NOOP | TestDriver::FOR_NETWORK ) );
		$pool    = new WPCachePool( 'foo', new TestDriver( TestDriver::NOOP ) );

		static::assertTrue( $sw_pool->is_for_network() );
		static::assertFalse( $pool->is_for_network() );
	}

	public function test_created_item_respect_namespace() {

		$pool = new WPCachePool( 'hello', new TestDriver( TestDriver::NOOP ) );

		static::assertStringStartsWith( 'hello', $pool->item( 'world' )->key() );
	}

	public function test_get_returns_default_if_item_miss() {

		$pool = new WPCachePool( 'hello', new TestDriver( TestDriver::NOOP ) );

		$random = uniqid();

		static::assertSame( $random, $pool->get( 'bar', $random ) );
	}

	public function test_get_returns_cached_if_item_valid() {

		$pool = new WPCachePool( 'foo', new EphemeralCacheDriver( EphemeralCacheDriver::NOOP ) );
		$pool->set( 'bar', 'Cached!' );

		static::assertSame( 'Cached!', $pool->get( 'bar', 'Default!' ) );
	}

	public function test_get_many() {

		$pool = new WPCachePool( 'foo', new EphemeralCacheDriver( EphemeralCacheDriver::NOOP ) );

		$keys   = range( 'a', 'c' );
		$values = range( 'A', 'C' );
		$to_set = array_combine( $keys, $values );

		array_walk( $to_set, function ( string $value, string $key, WPCachePool $pool ) {

			$pool->set( $key, $value );
		}, $pool );

		$keys[] = 'd';

		$data = $pool->get_many( $keys, 'Default!' );

		static::assertSame( 'A', $data['a'] );
		static::assertSame( 'B', $data['b'] );
		static::assertSame( 'C', $data['c'] );
		static::assertSame( 'Default!', $data['d'] );
	}

	public function test_delete_many() {

		$pool = new WPCachePool( 'foo', new EphemeralCacheDriver( EphemeralCacheDriver::NOOP ) );

		$pool->set( 'foo', 'Cached!' );
		$pool->set( 'bar', 'Cached!' );

		static::assertTrue( $pool->has( 'foo' ) );
		static::assertTrue( $pool->has( 'bar' ) );
		static::assertFalse( $pool->has( 'meh!' ) );

		$pool->delete( 'foo' );

		static::assertFalse( $pool->has( 'foo' ) );
		static::assertTrue( $pool->has( 'bar' ) );
	}
}
