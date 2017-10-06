<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde;

use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\TestSuite;

/**
 * Test listener implementation taking care of test-suite-specific tasks.
 */
final class TestListener extends BaseTestListener {

	/**
	 * Performs individual test-suite-specific actions.
	 *
	 * This gets triggered by PHPUnit whenever a new test suite gets run.
	 *
	 * @param TestSuite $suite
	 */
	public function startTestSuite( TestSuite $suite ) {

		putenv( 'TESTS_FILES_PATH=' . __DIR__ );

		switch ( $suite->getName() ) {
			case 'Unit':
				require_once __DIR__ . '/stubs.php';
				break;

			case 'Integration':
				// TODO: Some integration testing preparations...
				break;
		}
	}
}
