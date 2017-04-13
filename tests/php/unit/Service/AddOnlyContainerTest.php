<?php # -*- coding: utf-8 -*-

declare( strict_types=1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Service;

use Inpsyde\MultilingualPress\Service\AddOnlyContainer;
use Inpsyde\MultilingualPress\Service\Exception;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * @package Inpsyde\MultilingualPress\Tests\Unit\Service
 */
class AddOnlyContainerTest extends TestCase {

	public function test_offsetExists_with_constructor_values() {

		$container = new AddOnlyContainer( [ 'foo' => 'bar' ] );

		self::assertTrue( $container->offsetExists( 'foo' ) );
		self::assertTrue( isset( $container['foo'] ) );
		self::assertFalse( empty( $container['foo'] ) );
		self::assertFalse( $container->offsetExists( 'bar' ) );
	}

	public function test_offsetExists_with_not_existent_key() {

		$this->expectException( Exception\ValueNotFound::class );

		$container = new AddOnlyContainer( [ 'foo' => 'bar' ] );
		$container['bar'];
	}

	public function test_offsetGet_fails_accessing_not_existent_values() {

		$container = new AddOnlyContainer();

		$this->expectException( Exception\ValueNotFound::class );

		$container['foo'];
	}

	public function test_offsetGet_fails_accessing_not_shared_values_after_bootstrap() {

		$container = new AddOnlyContainer();

		$container['foo'] = new \stdClass();

		self::assertEquals( new \stdClass(), $container['foo'] );

		$container->bootstrap();

		$this->expectException( Exception\LateAccessToNotSharedService::class );

		$container['foo'];
	}

	public function test_offsetSet_stores_callback_and_OffsetGet_return_values() {

		$container        = new AddOnlyContainer();
		$container['foo'] = function () {

			return 'Hi!';
		};

		self::assertSame( 'Hi!', $container['foo'] );
	}

	public function test_offsetSet_fails_if_locked() {

		$this->expectException( Exception\WriteAccessOnLockedContainer::class );

		$container = new AddOnlyContainer();
		$container->lock();

		$container['foo'] = 'bar';
	}

	public function test_offsetSet_fails_at_overwrite_attempt() {

		$this->expectException( Exception\InvalidValueWriteAccess::class );

		$container        = new AddOnlyContainer( [ 'foo' => 'bar' ] );
		$container['foo'] = 'bar';
	}

	public function test_offsetSet_makes_scalar_accessible_after_bootstrap() {

		$container        = new AddOnlyContainer();
		$container['foo'] = 'bar';
		$container->bootstrap();

		self::assertSame( 'bar', $container['foo'] );
	}

	public function test_offsetUnset_is_disallowed() {

		$container = new AddOnlyContainer( [ 'foo' => 'bar' ] );
		$this->expectException( Exception\InvalidValueWriteAccess::class );
		unset( $container['foo'] );

	}

	public function test_simple_extend() {

		$container = new AddOnlyContainer( [ 'foo' => 'bar' ] );

		$container['stdClass'] = function ( AddOnlyContainer $container ) {

			return (object) [ $container['foo'] => 'Bar!' ];
		};

		$container->extend( 'stdClass', function ( \stdClass $stdClass, AddOnlyContainer $container ) {

			self::assertSame( 'Bar!', $stdClass->bar );

			return (object) [ 'bar' => 'Bar! Bar!' ];
		} );

		$stdClass = $container['stdClass'];

		self::assertInstanceOf( \stdClass::class, $stdClass );
		self::assertSame( 'Bar! Bar!', $stdClass->bar );

	}

	public function test_extend_multiple_levels() {

		$container = new AddOnlyContainer( [ 'counter' => new \ArrayObject( [ 'count' => 0 ] ) ] );

		$container['foo'] = function () {

			return 'Foo';
		};

		$container->extend( 'foo', function ( string $foo, AddOnlyContainer $container ) {

			$container['counter']['count'] ++;
			self::assertSame( 'Foo', $foo );

			return "{$foo}!";
		} );

		$container->extend( 'foo', function ( string $foo, AddOnlyContainer $container ) {

			$container['counter']['count'] ++;
			self::assertSame( 'Foo!', $foo );

			return "{$foo}!!";
		} );

		self::assertSame( 'Foo!!!', $container['foo'] );
		self::assertSame( 2, $container['counter']['count'] = 2 );
	}

	public function test_extend_fails_if_locked() {

		$container = new AddOnlyContainer();

		$container['foo'] = function () {

			return 'Foo';
		};

		$container->lock();

		$this->expectException( Exception\WriteAccessOnLockedContainer::class );

		$container->extend( 'foo', function ( string $foo ) {

			return "{$foo}!";
		} );

	}

	public function test_extend_fails_if_original_not_found() {

		$container = new AddOnlyContainer( [ 'foo' => 'bar' ] );

		$this->expectException( Exception\ValueNotFound::class );

		$container->extend( 'foo', function () {

			return null;
		} );
	}

	public function test_extend_fails_for_already_resolved_objects() {

		$container = new AddOnlyContainer();

		$container['foo'] = function () {

			return 'Hello';
		};

		self::assertSame( 'Hello', $container['foo'] );

		$this->expectException( Exception\InvalidValueWriteAccess::class );

		$container->extend( 'foo', function () {

			return 'Goodbye';
		} );
	}

	public function test_share_allow_for_late_access() {

		$container = new AddOnlyContainer();

		$container['N'] = function () {

			return new \ArrayObject( [ 'shared' => 'No!' ] );
		};

		$container->share( 'Y', function () {

			return new \ArrayObject( [ 'shared' => 'Yes!' ] );
		} );

		self::assertSame( 'No!', $container['N']['shared'] );
		self::assertSame( 'Yes!', $container['Y']['shared'] );

		$container->bootstrap();

		self::assertSame( 'Yes!', $container['Y']['shared'] );

		$this->expectException( Exception\LateAccessToNotSharedService::class );

		self::assertSame( 'No!', $container['N']['shared'] );

	}

}
