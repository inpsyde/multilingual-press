<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Module\Redirect\LanguageNegotiation;

use Inpsyde\MultilingualPress\Common\AcceptHeader\AcceptHeaderParser;

/**
 * Parser for Accept-Language headers, sorting by priority.
 *
 * @package Inpsyde\MultilingualPress\Module\Redirect\LanguageNegotiation
 * @since   3.0.0
 */
final class AcceptLanguageParser implements AcceptHeaderParser {

	/**
	 * Parses the given Accept header and returns the according data in array form.
	 *
	 * @since 3.0.0
	 *
	 * @param string $header Accept header string.
	 *
	 * @return array Parsed Accept header in array form.
	 */
	public function parse( $header ) {

		$header = $this->remove_comment( $header );
		if ( '' === $header ) {
			return [];
		}

		return array_reduce( $this->get_values( $header ), function ( array $values, $value ) {

			$split_values = $this->split_value( $value );
			if ( $split_values ) {
				list( $language, $priority ) = $split_values;

				$values[ $language ] = $priority;
			}

			return $values;
		}, [] );
	}

	/**
	 * Returns the given Accept header without comment.
	 *
	 * A comment starts with a `(` and ends with the first `)`.
	 *
	 * @param string $header Accept header string.
	 *
	 * @return string Accept header without comment.
	 */
	private function remove_comment( $header ) {

		$delimiter = '~';

		$delimiter_found = false !== strpos( $header, $delimiter );
		if ( $delimiter_found ) {
			$header = str_replace( $delimiter, "\\$delimiter", $header );
		}

		$header = preg_replace( '~\([^)]*\)~', '', $header );

		if ( $delimiter_found ) {
			$header = str_replace( "\\$delimiter", $delimiter, $header );
		}

		return trim( $header );
	}

	/**
	 * Returns the array with the individual values of the given Accept header.
	 *
	 * @param string $header Accept header string.
	 *
	 * @return string[] Array of values.
	 */
	private function get_values( $header ) {

		$values = explode( ',', $header );
		$values = array_map( 'trim', $values );

		return $values;
	}

	/**
	 * Returns the array with the language and priority of the given value, and an empty array for an invalid language.
	 *
	 * @param string $value Accept-Language header value.
	 *
	 * @return array Array with language and priority, or empty array in case of invalid language.
	 */
	private function split_value( $value ) {

		$language = strtok( $value, ';' );
		if ( ! preg_match( '~[a-zA-Z_-]~', $language ) ) {
			return [];
		}

		if ( $language === $value ) {
			return [ $language, 1 ];
		}

		strtok( '=' );

		$priority = strtok( ';' );
		$priority = $this->normalize_priority( $priority );

		return [ $language, $priority ];
	}

	/**
	 * Returns a normalized float value between 0 and 1 for the given numeric string.
	 *
	 * @param string $priority Numeric priority string.
	 *
	 * @return float Normalized priority.
	 */
	private function normalize_priority( $priority ) {

		$priority = (float) $priority;
		$priority = max( 0, $priority );
		$priority = min( 1, $priority );

		return $priority;
	}
}
