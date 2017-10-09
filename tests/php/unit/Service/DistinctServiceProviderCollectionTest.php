<?php # -*- coding: utf-8 -*-

declare( strict_types=1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Service;

use Andrew\Proxy;
use Inpsyde\MultilingualPress\Service\DistinctServiceProviderCollection;
use Inpsyde\MultilingualPress\Service\ServiceProvider;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * @package Inpsyde\MultilingualPress\Tests\Unit\Service
 */
class DistinctServiceProviderCollectionTest extends TestCase {

	public function test_add_service_provider_add_same_instance_once() {

		$collection = new DistinctServiceProviderCollection();

		/** @var ServiceProvider $provider_a */
		$provider_a = \Mockery::mock( ServiceProvider::class );
		$provider_b = clone $provider_a;

		$collection
			->add_service_provider( $provider_a )
			->add_service_provider( $provider_b )
			->add_service_provider( $provider_a )
			->add_service_provider( $provider_b );

		self::assertCount( 2, $collection );

	}

	public function test_remove_service_provider() {

		$collection = new DistinctServiceProviderCollection();

		/** @var ServiceProvider $provider_a */
		$provider_a = \Mockery::mock( ServiceProvider::class );
		$provider_b = clone $provider_a;

		$collection
			->add_service_provider( $provider_a )
			->add_service_provider( $provider_b )
			->remove_service_provider( $provider_a );

		self::assertCount( 1, $collection );
	}

	public function test_apply_method() {

		$collection = new DistinctServiceProviderCollection();

		/** @var ServiceProvider|\Mockery\MockInterface $provider_a */
		$provider_a = \Mockery::mock( ServiceProvider::class );
		$provider_b = clone $provider_a;

		$provider_a
			->shouldReceive( 'run' )
			->once()
			->andReturnUsing( function ( string $a, string $b ) {

				self::assertSame( 'param 1', $a );
				self::assertSame( 'param 2', $b );
				echo 'It ';
			} );

		$provider_b
			->shouldReceive( 'run' )
			->once()
			->andReturnUsing( function ( string $a, string $b ) {

				self::assertSame( 'param 1', $a );
				self::assertSame( 'param 2', $b );
				echo 'works!';
			} );

		$collection
			->add_service_provider( $provider_a )
			->add_service_provider( $provider_b );

		$this->expectOutputString( 'It works!' );

		$collection->apply_method( 'run', 'param 1', 'param 2' );

	}

	public function test_apply_callback() {

		$collection = new DistinctServiceProviderCollection();

		/** @var ServiceProvider|\Mockery\MockInterface $provider_a */
		$provider_a = \Mockery::mock( ServiceProvider::class );
		$provider_b = clone $provider_a;

		$provider_a
			->shouldReceive( 'run' )
			->once()
			->andReturn( 'It ' );

		$provider_b
			->shouldReceive( 'run' )
			->once()
			->andReturn( 'works!' );

		$collection
			->add_service_provider( $provider_a )
			->add_service_provider( $provider_b );

		$callback = function ( $provider, string $a, string $b ) {

			self::assertSame( 'param 1', $a );
			self::assertSame( 'param 2', $b );
			/** @noinspection PhpUndefinedMethodInspection */
			echo $provider->run();
		};

		$this->expectOutputString( 'It works!' );

		$collection->apply_callback( $callback, 'param 1', 'param 2' );

	}

	public function test_filter() {

		$collection = new DistinctServiceProviderCollection();

		/** @var ServiceProvider|\Mockery\MockInterface $provider_a */
		$provider_a = \Mockery::mock( ServiceProvider::class );
		$provider_b = clone $provider_a;
		$provider_c = clone $provider_a;
		$provider_d = clone $provider_a;

		$provider_a
			->shouldReceive( 'run' )
			->once()
			->andReturn( 1 );

		$provider_b
			->shouldReceive( 'run' )
			->once()
			->andReturn( 2 );

		$provider_c
			->shouldReceive( 'run' )
			->once()
			->andReturn( 3 );

		$provider_d
			->shouldReceive( 'run' )
			->once()
			->andReturn( 4 );

		$collection
			->add_service_provider( $provider_a )
			->add_service_provider( $provider_b )
			->add_service_provider( $provider_c )
			->add_service_provider( $provider_d );

		$new_collection = $collection->filter( function ( $provider, int $divider ) {

			/** @noinspection PhpUndefinedMethodInspection */
			return $provider->run() % $divider === 0;

		}, 3 );

		// Allow access to private properties of $new_collection
		$proxy = new Proxy( $new_collection );
		$proxy->storage->rewind();

		self::assertNotSame( $collection, $new_collection );
		self::assertCount( 1, $new_collection );
		self::assertCount( 4, $collection );
		// $provider_c is the only object whose (mocked) run() method return a value divisible by 3
		self::assertSame( $provider_c, $proxy->storage->current() );

	}

	public function test_map() {

		$collection = new DistinctServiceProviderCollection();

		/** @var ServiceProvider|\Mockery\MockInterface $provider_a */
		$provider_a = \Mockery::mock( ServiceProvider::class );
		$provider_b = clone $provider_a;
		$provider_c = clone $provider_a;
		$provider_d = clone $provider_a;

		$count    = 0;
		$callback = function ( $provider, int $add ) use ( &$count ) {

			$count             += $add;
			$provider->counted = $count;

			return $provider;
		};

		$collection
			->add_service_provider( $provider_a )
			->add_service_provider( $provider_b )
			->add_service_provider( $provider_c )
			->add_service_provider( $provider_d );

		$new_collection = $collection->map( $callback, 2 );

		self::assertNotSame( $collection, $new_collection );
		self::assertCount( 4, $new_collection );
		self::assertCount( 4, $collection );

		// Allow access to private properties of $new_collection
		$proxy = new Proxy( $new_collection );

		$proxy->storage->rewind();

		self::assertSame( $provider_a, $proxy->storage->current() );
		self::assertSame( 2, $proxy->storage->current()->counted );

		$proxy->storage->next();

		self::assertSame( $provider_b, $proxy->storage->current() );
		self::assertSame( 4, $proxy->storage->current()->counted );

		$proxy->storage->next();

		self::assertSame( $provider_c, $proxy->storage->current() );
		self::assertSame( 6, $proxy->storage->current()->counted );

		$proxy->storage->next();

		self::assertSame( $provider_d, $proxy->storage->current() );
		self::assertSame( 8, $proxy->storage->current()->counted );

	}

	public function test_reduce() {

		$collection = new DistinctServiceProviderCollection();

		/** @var ServiceProvider|\Mockery\MockInterface $provider_a */
		$provider_a = \Mockery::mock( ServiceProvider::class );
		$provider_a
			->shouldReceive( 'run' )
			->andReturn( 2 );

		$provider_b = clone $provider_a;
		$provider_c = clone $provider_a;
		$provider_d = clone $provider_a;

		$callback = function ( int $count, $provider ) {

			/** @noinspection PhpUndefinedMethodInspection */
			return $count + $provider->run();
		};

		$collection
			->add_service_provider( $provider_a )
			->add_service_provider( $provider_b )
			->add_service_provider( $provider_c )
			->add_service_provider( $provider_d );

		$result = $collection->reduce( $callback, 2 );

		self::assertSame( 10, $result );
		self::assertCount( 4, $collection );

	}
}