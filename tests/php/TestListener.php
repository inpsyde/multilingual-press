<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde;

use PHPUnit\Framework;

/**
 * Test listener implementation taking care of test-suite-specific tasks.
 */
final class TestListener implements Framework\TestListener {

	use Framework\TestListenerDefaultImplementation;

	/**
	 * Performs individual test-suite-specific actions.
	 *
	 * This gets triggered by PHPUnit whenever a new test suite gets run.
	 *
	 * @param Framework\TestSuite $suite
	 */
	public function startTestSuite( Framework\TestSuite $suite ) {

		// @codingStandardsIgnoreLine
		putenv( 'TESTS_FILES_PATH=' . __DIR__ );

		switch ( $suite->getName() ) {
			case 'Unit':
				require_once __DIR__ . '/stubs.php';
				defined( 'HOUR_IN_SECONDS' ) || define( 'HOUR_IN_SECONDS', 3600 );
				break;

			case 'Integration':
				// TODO: Some integration testing preparations...
				break;
		}
	}
}
