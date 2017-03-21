<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests;

use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Exception;

/**
 * Test listener implementation taking care of test-suite-specific tasks.
 */
final class Listener extends BaseTestListener {

	/**
	 * Performs individual test-suite-specific actions.
	 *
	 * This gets triggered by PHPUnit whenever a new test suite gets run.
	 *
	 * @param TestSuite $suite
	 */
	public function startTestSuite( TestSuite $suite ) {

		if ( ! class_exists( TestCase::class ) ) {
			throw new Exception( 'Please install plugin via Composer before running tests.' );
		}

		putenv( 'TESTS_FILES_PATH=' . __DIR__ );

		switch ( $suite->getName() ) {
			case 'Unit':
				// TODO: Some unit testing preparations...
				break;

			case 'Integration':
				// TODO: Some integration testing preparations...
				break;
		}
	}
}
