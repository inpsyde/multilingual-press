<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;

/**
 * Abstract base class for all test case implementations.
 *
 * @package Inpsyde\MultilingualPress\Tests
 */
abstract class UnitTestCase extends PHPUnitTestCase {

	/**
	 * Prepares the test environment before each test.
	 *
	 * @return void
	 */
	protected function setUp() {

		parent::setUp();
		Monkey::setUpWP();
	}

	/**
	 * Cleans up the test environment after each test.
	 *
	 * @return void
	 */
	protected function tearDown() {

		Monkey::tearDownWP();
		\Mockery::close();
		parent::tearDown();
	}
}