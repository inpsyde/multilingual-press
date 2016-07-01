<?php # -*- coding: utf-8 -*-

/**
 * Test listener implementation taking care test-suite-specific tasks.
 *
 * @since 1.0.0
 */
class Listener extends PHPUnit_Framework_BaseTestListener {

	/**
	 * Performs individual test-suite-specific actions.
	 *
	 * This gets triggered by PHPUnit when a new test suite gets run.
	 *
	 * @since 1.0.0
	 *
	 * @param PHPUnit_Framework_TestSuite $suite Test suite object.
	 */
	public function startTestSuite( PHPUnit_Framework_TestSuite $suite ) {

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
