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
	 * Formats the given number according to the Semantic Versioning specification.
	 *
	 * @see http://semver.org/#semantic-versioning-specification-semver
	 *
	 * @param string $version Raw version number string.
	 *
	 * @return string Semantic version number string.
	 */
	private function get_semantic_version_number( string $version ): string {

		list( $numeric, $pre_release, $metadata ) = $this->match_semver_pattern( $version );

		if ( ! $numeric ) {
			return VersionNumber::FALLBACK_VERSION;
		}

		$version = $numeric;

		if ( $pre_release ) {
			$version .= "-{$pre_release}";
		}

		if ( $metadata ) {
			$version .= "+{$metadata}";
		}

		return $version;
	}

	/**
	 * @param string $version
	 *
	 * @return string[] A 3 items array with the 3 parts of SemVer specs, in order:
	 *                  - The numeric part of SemVer specs
	 *                  - The pre-release part of SemVer specs, could be empty
	 *                  - The meta part of SemVer specs, could be empty
	 */
	private function match_semver_pattern( string $version ): array {

		$pattern = '~^(?P<numbers>(?:[0-9]+)+(?:[0-9\.]+)?)+(?P<anything>.*?)?$~';

		$matched = preg_match( $pattern, $version, $matches );

		if ( ! $matched ) {
			return [ '', '', '' ];
		}

		$numbers = explode( '.', trim( $matches['numbers'], '.' ) );

		// if less than 3 numbers, we ensure at least 3 numbers are there, filling with zeroes.
		$numeric = implode( '.', array_replace( [ '0', '0', '0' ], array_slice( $numbers, 0, 3 ) ) );

		// if more than 3 numbers are there, we store additional numbers as build.
		$build = implode( '.', array_slice( $numbers, 3 ) );

		// if there's no anything else, we already know what to return.
		if ( ! $matches['anything'] ) {
			return [ $numeric, $build, '' ];
		}

		$pre  = ltrim( $matches['anything'], '-' );
		$meta = '';

		// seems we have some metadata.
		if ( substr_count( $matches['anything'], '+' ) > 0 ) {
			$parts = explode( '+', $pre );
			// pre is what's before the first +, which could actually be empty when version has meta but not pre-release.
			$pre = array_shift( $parts );
			// everything comes after first + is meta. If there were more +, we replace them with dots.
			$meta = $this->sanitize_identifier( trim( implode( '.', $parts ), '-' ) );
		}

		if ( $build ) {
			$pre = "$build.$pre";
		}

		return [ $numeric, $this->sanitize_identifier( $pre ), $meta ];
	}

	/**
	 * Sanitizes given identifier according to SemVer specs. Allow for underscores, replacing them with hyphens.
	 *
	 * @param string $identifier The identifier to be sanitized.
	 *
	 * @return string The sanitized identifier.
	 */
	private function sanitize_identifier( string $identifier ): string {

		// the condition will be false for both "" and "0", which are both valid so don't need any replace.
		if ( $identifier ) {
			$identifier = preg_replace( '~[^a-zA-Z0-9\-\.]~', '', str_replace( '_', '-', $identifier ) );
		}

		return $identifier;
	}
}
