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

	public function test_driver_can_be_sitewide_or_not() {

		$sitewide    = new EphemeralCacheDriver( EphemeralCacheDriver::FOR_NETWORK );
		$no_sitewide = new EphemeralCacheDriver();

		static::assertTrue( $sitewide->is_sidewide() );
		static::assertFalse( $no_sitewide->is_sidewide() );
	}

	public function test_simple_read_no_value() {

		$driver = new EphemeralCacheDriver();
		list( $value, $found ) = $driver->read( 'foo', 'bar' );

		static::assertNull( $value );
		static::assertFalse( $found );
	}

	public function test_read_and_write() {

		$driver = new EphemeralCacheDriver();
		$driver->write( 'foo', 'bar', 'Yes!' );

		list( $value, $found ) = $driver->read( 'foo', 'bar' );

		static::assertSame( 'Yes!', $value );
		static::assertTrue( $found );
	}

	public function test_read_and_write_delete() {

		$driver = new EphemeralCacheDriver();
		$driver->write( 'foo', 'bar', 'Yes! Yes!' );

		list( $value_before, $found_before ) = $driver->read( 'foo', 'bar' );

		$driver->delete( 'foo', 'bar' );

		list( $value_after, $found_after ) = $driver->read( 'foo', 'bar' );

		static::assertSame( 'Yes! Yes!', $value_before );
		static::assertTrue( $found_before );
		static::assertNull( $value_after );
		static::assertFalse( $found_after );
	}

	public function test_read_and_write_noop() {

		$driver  = new EphemeralCacheDriver( EphemeralCacheDriver::NOOP );
		$written = $driver->write( 'foo', 'bar', 'Yes!' );

		list( $value, $found ) = $driver->read( 'foo', 'bar' );

		static::assertFalse( $written );
		static::assertNull( $value );
		static::assertFalse( $found );
	}

	public function test_sidewide_is_separate_cache() {

		$sitewide      = new EphemeralCacheDriver( EphemeralCacheDriver::FOR_NETWORK );
		$sitewide_2    = new EphemeralCacheDriver( EphemeralCacheDriver::FOR_NETWORK );
		$no_sitewide   = new EphemeralCacheDriver();
		$no_sitewide_2 = new EphemeralCacheDriver();

		$sitewide->write( 'foo', 'bar', 'All site!' );
		$no_sitewide->write( 'foo', 'bar', '1 blog!' );

		list( $value_sitewide ) = $sitewide->read( 'foo', 'bar' );
		list( $value_blog ) = $no_sitewide->read( 'foo', 'bar' );
		list( $value_sitewide_2 ) = $sitewide_2->read( 'foo', 'bar' );
		list( $value_blog_2 ) = $no_sitewide_2->read( 'foo', 'bar' );

		static::assertSame( 'All site!', $value_sitewide );
		static::assertSame( 'All site!', $value_sitewide_2 );
		static::assertSame( '1 blog!', $value_blog );
		static::assertSame( '1 blog!', $value_blog_2 );
	}

	public function test_noop_driver() {

		$driver = new EphemeralCacheDriver( EphemeralCacheDriver::FOR_NETWORK | EphemeralCacheDriver::NOOP );

		static::assertFalse( $driver->write( 'foo', 'bar', 'Noop!' ) );
		static::assertNull( $driver->read( 'foo', 'bar' )[0] );
		static::assertFalse( $driver->delete( 'foo', 'bar' ) );
	}
}
