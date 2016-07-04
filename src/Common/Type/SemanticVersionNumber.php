<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Type;

/**
 * Version number data type implementation according to the Semantic Versioning specification.
 *
 * @package Inpsyde\MultilingualPress\Common\Type
 * @see     http://semver.org/#semantic-versioning-specification-semver
 * @since   3.0.0
 */
class SemanticVersionNumber implements VersionNumber {

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
	 * @return string
	 */
	public function __toString() {

		return $this->version;
	}

	/**
	 * Returns a sanitized semantic version number string for the given version.
	 *
	 * @param string $version Raw version number string.
	 *
	 * @return string Sanitized semantic version number string.
	 */
	private function get_semantic_version_number( $version ) {

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
	private function sanitize_version( $version ) {

		$version = strtolower( $version );

		// Normalize separators.
		$version = str_replace( [ '_', '-', '+' ], '.', $version );

		// Remove invalid characters.
		$version = preg_replace( '~[^a-z0-9\.]*~', '', $version );
		if ( '' === $version ) {
			return '';
		}

		// Reduce repeating dots to one dot only.
		$version = preg_replace( '~\.\.+~', '.', $version );

		// Insert a dot between a numeric character followed by a non-numeric one (i.e., "2beta1" becomes "2.beta1").
		$version = preg_replace( '~([0-9])([a-z])~', '$1.$2', $version );

		// Insert a dot between a non-numeric character followed by a numeric one (i.e., "2beta1" becomes "2beta.1").
		$version = preg_replace( '~([a-z])([0-9])~', '$1.$2', $version );

		return $version;
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
	private function format_version( $version ) {

		if ( preg_match( '~^\d+\.\d+\.\d+~', $version ) ) {
			return $version;
		}

		// Semantic Versioning at least requires the format X.Y.Z. with X, Y, and Z being non-negative integers.
		$parts = [ 0, 0, 0 ];

		foreach ( explode( '.', $version ) as $index => $level ) {
			if ( $index < 3 && is_numeric( $level ) ) {
				$parts[ $index ] = (int) $level;

				continue;
			}

			$parts[] = $level;
		}

		return join( '.', $parts );
	}
}
