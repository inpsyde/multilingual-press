<?php
/**
 * Read an accept header and sort its values by priority.
 *
 * @version    2014.09.26
 * @author     Inpsyde GmbH, toscho
 * @license    GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */


class Mlp_Accept_Header_Parser implements Mlp_Accept_Header_Parser_Interface {

	/**
	 * @type Mlp_Accept_Header_Validator_Interface
	 */
	private $validator;

	/**
	 * @param Mlp_Accept_Header_Validator_Interface $validator
	 */
	public function __construct( Mlp_Accept_Header_Validator_Interface $validator = null ) {

		$this->validator = $validator;
	}

	/**
	 * @param  string $accept_header
	 * @return array
	 */
	public function parse( $accept_header ) {

		$accept_header = $this->remove_comment( $accept_header );

		if ( '' === $accept_header ) {
			return array();
		}

		$out   = array();
		$parts = $this->separate_values( $accept_header );

		foreach ( $parts as $part ) {

			$separated = $this->separate_priority( $part );

			if ( empty( $separated ) ) {
				continue;
			}

			list ( $key, $priority ) = $separated;

			$out[ $key ] = $priority;
		}

		return $out;
	}

	/**
	 * @param  string $part
	 * @return array
	 */
	private function separate_priority( $part ) {

		if ( false === strpos( $part, ';' ) ) {

			if ( ! $this->validator->is_valid( $part ) ) {
				return array();
			}

			return array( $part, 1 );
		}

		// string with quality value like 'en;q=0.8'
		$key = strtok( $part, ';' );

		if ( ! $this->validator->is_valid( $key ) ) {
			return array();
		}

		strtok( '=' );

		$priority = strtok( ';' );
		$priority = $this->sanitize_priority( $priority );

		return array( $key, $priority );
	}

	/**
	 * Guarantees a float value between 0 and 1
	 *
	 * @param  string $priority
	 * @return float
	 */
	private function sanitize_priority( $priority ) {

		$priority = (float) $priority;
		$priority = min( 1, $priority );
		$priority = max( 0, $priority );

		return $priority;
	}

	/**
	 * @param  string $header
	 * @return array
	 */
	private function separate_values( $header ) {

		$parts = explode( ',', $header );
		$parts = array_map( 'trim', $parts );

		return $parts;
	}

	/**
	 * Removes comments from header string.
	 *
	 * A comment starts with a `(` and ends with the first `)`.
	 * HTTP has a strange syntax.
	 *
	 * @param  string $header
	 * @return string
	 */
	private function remove_comment( $header ) {

		$unescape_delimiter = false;

		if ( false !== strpos( $header, '~' ) ) {

			$header      = str_replace( '~', '\~', $header );
			$unescape_delimiter = true;
		}

		$no_comment = preg_replace( '~\([^)]*\)~', '', $header );

		if ( $unescape_delimiter ) {
			$no_comment = str_replace( '\~', '~', $no_comment );
		}

		return trim( $no_comment );
	}
}
