<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Tests\Unit\Common\Type;

use Inpsyde\MultilingualPress\Common\Type\SemanticVersionNumber;
use Inpsyde\MultilingualPress\Tests\Unit\TestCase;

/**
 * @package Inpsyde\MultilingualPress\Tests\Unit\Common\Type
 */
class SemanticVersionNumberTest extends TestCase {

	/**
	 * @dataProvider versions_provider
	 *
	 * @param string $version
	 * @param string $expected
	 */
	public function test_semver_resolution( string $version, string $expected ) {
		
		self::assertSame( $expected, (string) new SemanticVersionNumber( $version ) );
	}

	public function versions_provider() {

		return [
			[ '4', '4.0.0' ],
			[ '4.1', '4.1.0' ],
			[ '4.1.12', '4.1.12' ],
			[ '4.1.12.25', '4.1.12-25' ],
			[ '4.1-dev', '4.1.0-dev' ],
			[ '4-dev', '4.0.0-dev' ],
			[ '4+meta.23', '4.0.0+meta.23' ],
			[ '4.2-dev.2+meta.23', '4.2.0-dev.2+meta.23' ],
			[ '4.1.12.25-dev', '4.1.12-25.dev' ],
			[ 'meh', '0.0.0' ],
			[ '-meh', '0.0.0' ],
			[ '-1', '0.0.0' ],
			[ '1-1', '1.0.0-1' ],
			[ '1me_meh?', '1.0.0-me-meh' ],
			[ '1xyz.32', '1.0.0-xyz.32' ],
			[ '1!x!y!z!.!3!2!+!a!.!b!', '1.0.0-xyz.32+a.b' ],
			[ '1.2.3.4.5-a.b', '1.2.3-4.5.a.b' ],
			[ '1.2.3.4.5-a-12+b+c+d', '1.2.3-4.5.a-12+b.c.d' ],
			[ '4-alpha-40306', '4.0.0-alpha-40306' ],
			[ '4.8-alpha-40306', '4.8.0-alpha-40306' ],
			[ '4.8.9-alpha-40306', '4.8.9-alpha-40306' ],
			[ '5.3.6-13ubuntu3.2', '5.3.6-13ubuntu3.2' ],
		];
	}

}