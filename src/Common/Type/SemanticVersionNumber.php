<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Version number data type implementation according to the Semantic Versioning specification.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @see     http://semver.org/#semantic-versioning-specification-semver
 * @since   3.0.0
 */
final class SemanticVersionNumber implements VersionNumber {

	/**
	 * @var string
	 */
	private $version = VersionNumber::FALLBACK_VERSION;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $version Version source.
	 */
	public function __construct( $version ) {

		if (
			is_scalar( $version )
			|| ( is_object( $version ) && method_exists( $version, '__toString' ) )
		) {
			$this->version = $this->get_semantic_version_number( (string) $version );
		}
	}

	/**
	 * Returns the version string.
	 *
	 * @since 3.0.0
	 *
	 * @return string Version string.
	 */
	public function __toString(): string {

		return $this->version;
	}

	/**
	 * Returns a sanitized semantic version number string for the given version.
	 *
	 * @param string $version Raw version number string.
	 *
	 * @return string Sanitized semantic version number string.
	 */
	private function get_semantic_version_number( string $version ): string {

		$version = $this->sanitize_version( $version );
		if ( '' === $version ) {
			return VersionNumber::FALLBACK_VERSION;
		}

		$version = $this->format_version( $version );

		return $version;
	}

	/**
	 * Removes invalid characters, and inserts dots between numeric and non-numeric characters.
	 *
	 * @param string $version Raw version number string.
	 *
	 * @return string Sanitized version number string.
	 */
	private function sanitize_version( string $version ): string {

		return preg_replace(
			[ '~[_\.\-\+]+~', '~([0-9])([a-z])~', '~([a-z])([0-9])~', '~[^a-z0-9\.\-\+]*~' ],
			[ '.', '$1.$2', '$1.$2', '' ],
			strtolower( $version )
		);
	}

	/**
	 * Formats the given number according to the Semantic Versioning specification.
	 *
	 * @see http://semver.org/#semantic-versioning-specification-semver
	 *
	 * @param string $version Version number string.
	 *
	 * @return string Semantic version number string.
	 */
	private function format_version( string $version ): string {

		// filter because `$version` coming from sanitization could be just `'.'` and `explode('.', '.')` is `['', '']`
		$all_parts = array_filter( explode( '.', $version ) );

		$digit_parts = array_filter( $all_parts, 'ctype_digit' );

		$count_digit_parts = count( $digit_parts );

		$additional_parts = array_slice( $all_parts, $count_digit_parts );

		// Ensure at least 3 digit parts, filling with 0 if some is missing
		$digit_string = $count_digit_parts < 3
			? implode( '.', array_replace( [ 0, 0, 0 ], $digit_parts ) )
			: implode( '.', $digit_parts );

		return $additional_parts ? "$digit_string." . implode( '.', $additional_parts ) : $digit_string;
	}
}
