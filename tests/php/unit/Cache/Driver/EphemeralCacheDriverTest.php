<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Cache\Driver;

use Andrew\StaticProxy;
use Brain\Monkey\Functions;
use Inpsyde\MultilingualPress\Cache\Driver\EphemeralCacheDriver;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * Test case for the ephemeral cache driver class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class EphemeralCacheDriverTest extends TestCase {

	public function setUp() {

		Functions::when( 'get_current_blog_id' )->justReturn( 1 );

		parent::setUp();
	}

	public function tearDown() {

		$proxy        = new StaticProxy( EphemeralCacheDriver::class );
		$proxy->cache = [];

		parent::tearDown();
	}

	public function test_driver_can_be_network_or_not() {

		$network    = new EphemeralCacheDriver( EphemeralCacheDriver::FOR_NETWORK );
		$no_network = new EphemeralCacheDriver();

		static::assertTrue( $network->is_network() );
		static::assertFalse( $no_network->is_network() );
	}

	public function test_simple_read_no_value() {

		$driver = new EphemeralCacheDriver();
		$value  = $driver->read( 'foo', 'bar' );

		static::assertNull( $value->value() );
		static::assertFalse( $value->is_hit() );
	}

	public function test_read_and_write() {

		$driver = new EphemeralCacheDriver();
		$driver->write( 'foo', 'bar', 'Yes!' );

		$value = $driver->read( 'foo', 'bar' );

		static::assertSame( 'Yes!', $value->value() );
		static::assertTrue( $value->is_hit() );
	}

	public function test_read_and_write_delete() {

		$driver = new EphemeralCacheDriver();
		$driver->write( 'foo', 'bar', 'Yes! Yes!' );

		$value_before = $driver->read( 'foo', 'bar' );

		$driver->delete( 'foo', 'bar' );

		$value_after = $driver->read( 'foo', 'bar' );

		static::assertSame( 'Yes! Yes!', $value_before->value() );
		static::assertTrue( $value_before->is_hit() );
		static::assertNull( $value_after->value() );
		static::assertFalse( $value_after->is_hit() );
	}

	public function test_read_and_write_noop() {

		$driver  = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );
		$written = $driver->write( 'foo', 'bar', 'Yes!' );

		$value = $driver->read( 'foo', 'bar' );

		static::assertFalse( $written );
		static::assertNull( $value->value() );
		static::assertFalse( $value->is_hit() );
	}

	public function test_sidewide_is_separate_cache() {

		$network      = new EphemeralCacheDriver( EphemeralCacheDriver::FOR_NETWORK );
		$network_2    = new EphemeralCacheDriver( EphemeralCacheDriver::FOR_NETWORK );
		$no_network   = new EphemeralCacheDriver();
		$no_network_2 = new EphemeralCacheDriver();

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

	public function test_noop_driver() {

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::FOR_NETWORK | EphemeralCacheDriver::NOOP );

		static::assertFalse( $driver->write( 'foo', 'bar', 'Noop!' ) );
		static::assertNull( $driver->read( 'foo', 'bar' )->value() );
		static::assertFalse( $driver->delete( 'foo', 'bar' ) );
	}
}
