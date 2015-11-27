<?php # -*- coding: utf-8 -*-

/**
 * Test case for the install requirements class.
 */
class Mlp_Install_RequirementsTest extends PHPUnit_Framework_TestCase {

	public function test_multisite_required() {

		$testee = new Mlp_Install_Requirements();

		$this->assertSame( TRUE, $testee->multisite_required() );
	}

}
