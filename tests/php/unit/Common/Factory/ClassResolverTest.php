<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Common\Factory;

use Inpsyde\MultilingualPress\Common\Factory\ClassResolver as Testee;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * Test case for the class resolver class.
 *
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Factory
 * @since   3.0.0
 */
class ClassResolverTest extends TestCase {

	/**
	 * Tests construction with an invalid base fails.
	 *
	 * @since 3.0.0
	 *
	 * @expectedException \InvalidArgumentException
	 *
	 * @return void
	 */
	public function test_construction_with_invalid_base_fails() {

		new Testee( '\InvalidFQN' );
	}

	/**
	 * Tests construction with an invalid default class fails.
	 *
	 * @since 3.0.0
	 *
	 * @expectedException \Inpsyde\MultilingualPress\Common\Factory\Exception\InvalidClass
	 *
	 * @return void
	 */
	public function test_construction_with_invalid_default_class_fails() {

		new Testee( '\Iterator', '\InvalidFQN' );
	}

	/**
	 * Tests resolution with the base class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function test_resolution_with_base_class() {

		$base_class = '\IteratorIterator';

		self::assertSame( $base_class, ( new Testee( $base_class ) )->resolve() );
	}

	/**
	 * Tests resolution with the default class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function test_resolution_with_default_class() {

		$base_interface = '\Iterator';

		$class = '\IteratorIterator';

		self::assertSame( $class, ( new Testee( $base_interface, $class ) )->resolve() );
	}

	/**
	 * Tests resolution with the default class overrides the base class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function test_resolution_with_default_class_overrides_base_class() {

		$base_class = '\IteratorIterator';

		$default_class = '\CachingIterator';

		self::assertSame( $default_class, ( new Testee( $base_class, $default_class ) )->resolve() );
	}

	/**
	 * Tests resolution with a class overriding the default class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function test_resolution_with_class_overrides_default_class() {

		$base_interface = '\Iterator';

		$default_class = '\CachingIterator';

		$class = '\IteratorIterator';

		self::assertSame( $class, ( new Testee( $base_interface, $default_class ) )->resolve( $class ) );
	}

	/**
	 * Tests resolution with a class overriding both the base and default class.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function test_resolution_with_class_overrides_base_and_default_class() {

		$base_class = '\IteratorIterator';

		$default_class = '\CachingIterator';

		$class = '\InfiniteIterator';

		self::assertSame( $class, ( new Testee( $base_class, $default_class ) )->resolve( $class ) );
	}

	/**
	 * Tests resolution with an invalid class fails.
	 *
	 * @since 3.0.0
	 *
	 * @expectedException \Inpsyde\MultilingualPress\Common\Factory\Exception\InvalidClass
	 *
	 * @return void
	 */
	public function test_resolution_with_invalid_class_fails() {

		( new Testee( '\IteratorIterator' ) )->resolve( '\InvalidFQN' );
	}

	/**
	 * Tests resolution with no class fails.
	 *
	 * @since 3.0.0
	 *
	 * @expectedException \InvalidArgumentException
	 *
	 * @return void
	 */
	public function test_resolution_with_no_class_fails() {

		( new Testee( '\Iterator' ) )->resolve();
	}
}
