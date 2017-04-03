<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Http;

/**
 * Interface for all header parser implementations.
 *
 * @package Inpsyde\MultilingualPress\Common\AcceptHeader
 * @since   3.0.0
 */
interface HeaderParser {

	/**
	 * Parses the given Accept header and returns the according data in array form.
	 *
	 * @since 3.0.0
	 *
	 * @param string $header Accept header string.
	 *
	 * @return array Parsed Accept header in array form.
	 */
	public function parse( string $header ): array;
}
