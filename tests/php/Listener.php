<?php # -*- coding: utf-8 -*-

use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\TestSuite;

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
