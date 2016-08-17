<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\AcceptHeader\Parser;

_deprecated_file(
	'Mlp_Accept_Header_Parser_Interface',
	'3.0.0',
	'Inpsyde\MultilingualPress\Common\AcceptHeader\Parser'
);

/**
 * @deprecated 3.0.0 Deprecated in favor of {@see Parser}.
 */
interface Mlp_Accept_Header_Parser_Interface extends Parser {

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Parser::parse_header}.
	 *
	 * @param string $header
	 *
	 * @return array
	 */
	public function parse( $header );
}
