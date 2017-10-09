<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\HTTP;

/**
 * @package Inpsyde\MultilingualPress\Common\HTTP
 * @since   3.0.0
 */
final class TrimmingHeaderParser implements HeaderParser {

	/**
	 * Split given header by comma and remove any space around each obtained element.
	 *
	 * @since 3.0.0
	 *
	 * @param string $header Header string.
	 *
	 * @return string[] Parsed header in array form.
	 */
	public function parse( string $header ): array {

		return array_map( 'trim', explode( ',', $header ) );
	}
}
