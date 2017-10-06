<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Cache;

use Inpsyde\MultilingualPress\Cache\CacheFactory;
use Inpsyde\MultilingualPress\Cache\Driver\CacheDriver;
use Inpsyde\MultilingualPress\Cache\Pool\CachePool;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * Test case for the cache factory class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class CacheFactoryTest extends TestCase {

	public function test_create_creates_a_pool() {

		$factory = new CacheFactory( 'x' );

		static::assertInstanceOf( CachePool::class, $factory->create( 'y' ) );
	}

	public function test_created_driver_class_can_be_chosen() {

		$driver = \Mockery::mock( CacheDriver::class );

		$factory = new CacheFactory( 'x' );

		static::assertInstanceOf( CachePool::class, $factory->create( 'y', $driver ) );
	}

	public function test_create_sitewide_requires_sitewide_driver() {

		$driver = \Mockery::mock( CacheDriver::class );
		$driver->shouldReceive( 'is_sidewide' )->andReturn( false );

		$factory = new CacheFactory( 'x' );

		static::expectException( \InvalidArgumentException::class );
		static::expectExceptionMessageRegExp( '/network-wide/i' );

		$factory->create_for_network( 'y', $driver );
	}

	public function test_created_sitewide_driver_class_can_be_chosen() {

		$driver = \Mockery::mock( CacheDriver::class );
		$driver->shouldReceive( 'is_sidewide' )->andReturn( true );

		$factory = new CacheFactory( 'x' );

		static::assertInstanceOf( CachePool::class, $factory->create_for_network( 'y', $driver ) );
	}

}
