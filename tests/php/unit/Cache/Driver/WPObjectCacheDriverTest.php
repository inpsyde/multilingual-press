<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Cache\Driver;

use Andrew\StaticProxy;
use Brain\Monkey\Functions;
use Inpsyde\MultilingualPress\Cache\Driver\WPObjectCacheDriver;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * Test case for the ephemeral cache driver class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class WPObjectCacheDriverTest extends TestCase {

	private $cache = [];

	public function setUp() {

		Functions::when( 'wp_cache_get' )->alias( function ( $name, $namespace = '' ) {

			return array_key_exists( $name . $namespace, $this->cache ) ? $this->cache[ $name . $namespace ] : false;
		} );

		Functions::when( 'wp_cache_set' )->alias( function ( $name, $value, $namespace = '' ) {

			$this->cache[ $name . $namespace ] = $value;

			return true;
		} );

		Functions::when( 'wp_cache_delete' )->alias( function ( $name, $namespace = '' ) {

			$this->cache = array_diff_key( $this->cache, [ $name . $namespace => '' ] );

			return true;
		} );

		parent::setUp();
	}

	public function tearDown() {

		$this->cache = [];

		$proxy                    = new StaticProxy( WPObjectCacheDriver::class );
		$proxy->global_namespaces = [];

		parent::tearDown();
	}

	public function test_driver_can_be_sitewide_or_not() {

		$sitewide    = new WPObjectCacheDriver( WPObjectCacheDriver::FOR_NETWORK );
		$no_sitewide = new WPObjectCacheDriver();

		static::assertTrue( $sitewide->is_network() );
		static::assertFalse( $no_sitewide->is_network() );
	}

	public function test_simple_read_no_value() {

		$driver = new WPObjectCacheDriver();
		$value  = $driver->read( 'foo', 'bar' );

		static::assertNull( $value->value() );
		static::assertFalse( $value->is_hit() );
	}

	public function test_read_and_write() {

		$driver = new WPObjectCacheDriver();
		$driver->write( 'foo', 'bar', 'Hello!' );
		$value = $driver->read( 'foo', 'bar' )->value();

		static::assertSame( 'Hello!', $value );

	}

	public function test_read_and_write_delete() {

		$driver = new WPObjectCacheDriver();
		$driver->write( 'foo', 'bar', 'Bye!' );
		$value_before = $driver->read( 'foo', 'bar' )->value();
		$driver->delete( 'foo', 'bar' );
		$value_after = $driver->read( 'foo', 'bar' )->value();

		static::assertSame( 'Bye!', $value_before );
		static::assertNull( $value_after );
	}

	public function test_global_adds_to_global_groups() {

		Functions::expect( 'wp_cache_add_global_groups' )
			->once()
			->with( 'x' );

		Functions::expect( 'wp_cache_add_global_groups' )
			->once()
			->with( 'y' );

		$driver = new WPObjectCacheDriver( WPObjectCacheDriver::FOR_NETWORK );
		$driver->read( 'x', 'x' );
		$driver->read( 'x', 'y' );
		$driver->read( 'y', 'z' );

	}
}
