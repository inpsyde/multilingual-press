<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Cache\Driver;

use Brain\Monkey\Functions;
use Inpsyde\MultilingualPress\Cache\Driver\WPTransientDriver;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * Test case for the ephemeral cache driver class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class WPTransientCacheDriverTest extends TestCase {

	private $cache = [];

	public function setUp() {

		Functions::when( 'get_transient' )->alias( function ( $key ) {

			return array_key_exists( $key, $this->cache ) ? $this->cache[ $key ] : false;
		} );
		Functions::when( 'get_site_transient' )->alias( function ( $key ) {

			return array_key_exists( "_{$key}", $this->cache ) ? $this->cache["_{$key}"] : false;
		} );
		Functions::when( 'set_transient' )->alias( function ( $key, $value ) {

			$this->cache[ $key ] = $value;

			return true;
		} );
		Functions::when( 'set_site_transient' )->alias( function ( $key, $value ) {

			$this->cache["_{$key}"] = $value;

			return true;
		} );
		Functions::when( 'delete_transient' )->alias( function ( $key ) {

			$this->cache = array_diff_key( $this->cache, [ $key => '' ] );

			return true;
		} );
		Functions::when( 'delete_site_transient' )->alias( function ( $key ) {

			$this->cache = array_diff_key( $this->cache, [ "_{$key}" => '' ] );

			return true;
		} );

		parent::setUp();
	}

	public function tearDown() {

		$this->cache = [];

		parent::tearDown();
	}

	public function test_driver_can_be_network_or_not() {

		$network    = new WPTransientDriver( WPTransientDriver::FOR_NETWORK );
		$no_network = new WPTransientDriver();

		static::assertTrue( $network->is_network() );
		static::assertFalse( $no_network->is_network() );
	}

	public function test_simple_read_no_value() {

		Functions::when( 'wp_using_ext_object_cache' )->justReturn( false );

		$driver = new WPTransientDriver();
		$value  = $driver->read( 'foo', 'bar' );

		static::assertNull( $value->value() );
		static::assertFalse( $value->is_hit() );
	}

	public function test_read_uses_object_cache_when_external_cache() {

		Functions::when( 'wp_using_ext_object_cache' )->justReturn( true );

		Functions::expect( 'wp_cache_get' )->once();

		$driver = new WPTransientDriver();
		$driver->read( 'foo', 'bar' );
	}

	public function test_read_and_write() {

		Functions::when( 'wp_using_ext_object_cache' )->justReturn( false );

		$driver = new WPTransientDriver();
		$driver->write( 'foo', 'bar', 'Hello!' );
		$value = $driver->read( 'foo', 'bar' )->value();

		static::assertSame( 'Hello!', $value );

	}

	public function test_read_and_write_delete() {

		Functions::when( 'wp_using_ext_object_cache' )->justReturn( false );

		$driver = new WPTransientDriver();
		$driver->write( 'foo', 'bar', 'Bye!' );
		$value_before = $driver->read( 'foo', 'bar' )->value();
		$driver->delete( 'foo', 'bar' );
		$value_after = $driver->read( 'foo', 'bar' )->value();

		static::assertSame( 'Bye!', $value_before );
		static::assertNull( $value_after );
	}

	public function test_network_read_and_write_delete() {

		Functions::when( 'wp_using_ext_object_cache' )->justReturn( false );

		$driver = new WPTransientDriver( WPTransientDriver::FOR_NETWORK );
		$driver->write( 'foo', 'bar', 'Bye!' );
		$value_before = $driver->read( 'foo', 'bar' )->value();
		$driver->delete( 'foo', 'bar' );
		$value_after = $driver->read( 'foo', 'bar' )->value();

		static::assertSame( 'Bye!', $value_before );
		static::assertNull( $value_after );
	}

	public function test_sidewide_is_separate_cache() {

		Functions::when( 'wp_using_ext_object_cache' )->justReturn( false );

		$network      = new WPTransientDriver( WPTransientDriver::FOR_NETWORK );
		$network_2    = new WPTransientDriver( WPTransientDriver::FOR_NETWORK );
		$no_network   = new WPTransientDriver();
		$no_network_2 = new WPTransientDriver();

		$network->write( 'foo', 'bar', 'All site!' );
		$no_network->write( 'foo', 'bar', '1 blog!' );

		$value_network   = $network->read( 'foo', 'bar' )->value();
		$value_blog      = $no_network->read( 'foo', 'bar' )->value();
		$value_network_2 = $network_2->read( 'foo', 'bar' )->value();
		$value_blog_2    = $no_network_2->read( 'foo', 'bar' )->value();

		static::assertSame( 'All site!', $value_network );
		static::assertSame( 'All site!', $value_network_2 );
		static::assertSame( '1 blog!', $value_blog );
		static::assertSame( '1 blog!', $value_blog_2 );
	}
}
