<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\HTTP;

/**
 * Interface for all header parser implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\AcceptHeader
 * @since   3.0.0
 */
interface HeaderParser {

	/**
	 * Parses the given header and returns the according data in array form.
	 *
	 * @since 3.0.0
	 *
	 * @param string $header Header string.
	 *
	 * @return array Parsed header in array form.
	 */
	public function parse( string $header ): array;
}
