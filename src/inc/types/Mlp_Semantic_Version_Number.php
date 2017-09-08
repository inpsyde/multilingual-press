<?php
/**
 * version_compare() compatible type.
 *
 * @version 2014.09.12
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Semantic_Version_Number implements Mlp_Version_Number_Interface {

	/**
	 * @type string
	 */
	private $version;

	/**
	 * @param string $version
	 */
	public function __construct( $version ) {

		if ( ! is_scalar( $version ) ) {
			$this->version = self::FALLBACK_VERSION;
		} else {
			$this->version = $this->sanitize( $version );
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {

		return $this->version;
	}

	/**
	 * Remove invalid characters from version string.
	 *
	 * @param  string $version
	 * @return string
	 */
	private function sanitize( $version ) {

		// Special version strings such as alpha and beta are case sensitive.
		$version = strtolower( $version );

		$version = $this->replace_chars( $version );

		if ( '' === $version ) {
			return self::FALLBACK_VERSION;
		}

		return $this->fill_numbers( $version );
	}

	/**
	 * Add missing version numbers.
	 *
	 * Guarantees that `1.beta.2` comes out as `1.0.0.beta.2`
	 * @param $version
	 * @return string
	 */
	private function fill_numbers( $version ) {

		if ( preg_match( '~^\d+\.\d+\.\d+~', $version ) ) {
			return $version;
		}

		$parts  = explode( '.', $version );
		$new    = array();
		$append = array();

		foreach ( $parts as $part ) {
			$this->sort_values( $part, $new, $append );
		}

		$new = $this->pad_with_zero( $new );
		$new = array_merge( $new, $append );

		return join( '.', $new );
	}

	/**
	 * Sets non-numerical characters behind at least three numerical ones.
	 *
	 * @param  string $part
	 * @param  array  $new
	 * @param  array  $append
	 * @return void
	 */
	private function sort_values( $part, array &$new, array &$append ) {

		if ( 3 <= count( $new ) ) {
			$append[] = $part;
		} elseif ( is_numeric( $part ) ) {
			$new[] = $part;
		} else {
			$append[] = $part;
			$new      = $this->pad_with_zero( $new );
		}
	}

	/**
	 * Fills the array of numbers with missing values.
	 *
	 * @param  array $numbers
	 * @return array
	 */
	private function pad_with_zero( array $numbers ) {

		return array_pad( $numbers, 3, 0 );
	}

	/**
	 * Replace invalid characters, and set dots before and after non-numerical characters.
	 *
	 * @param  string $version
	 * @return string
	 */
	private function replace_chars( $version ) {

		// normalize separators
		$version = preg_replace( '~_|\-|\+~',          '.',       $version );
		// remove invalid characters
		$version = preg_replace( '~[^a-z0-9\.]*~',     '',        $version );
		// add dots before and after non-numeric parts: `2beta1` becomes `2.beta.1`
		$version = preg_replace( '~([^a-z])([a-z]+)~', '\\1.\\2', $version );
		$version = preg_replace( '~([a-z]+)([^a-z])~', '\\1.\\2', $version );
		// reduce repeating dots to just one
		$version = preg_replace( '~\.\.+~',            '.',       $version );

		return $version;
	}
}
