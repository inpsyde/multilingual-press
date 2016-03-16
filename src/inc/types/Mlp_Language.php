<?php # -*- coding: utf-8 -*-

/**
 * Language object
 *
 * @version 2015.06.26
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language implements Mlp_Language_Interface {

	/**
	 * @var int
	 */
	private $priority = 1;

	/**
	 * @var bool
	 */
	private $is_rtl = FALSE;

	/**
	 * @var array
	 */
	private $names = array();

	/**
	 * Constructor. Set up the properies.
	 *
	 * @param array $raw_data
	 */
	public function __construct( Array $raw_data ) {

		$data = $this->prepare_raw_data( $raw_data );

		$this->priority = (int) $data[ 'priority' ];
		unset ( $data[ 'priority' ] );

		$this->is_rtl = empty ( $data[ 'is_rtl' ] );
		unset ( $data[ 'is_rtl' ] );

		$this->names = $data;
	}

	/**
	 * Check if the language left-to-right.
	 *
	 * @return bool
	 */
	public function is_rtl() {

		return $this->is_rtl;
	}

	/**
	 * Get language name for given type.
	 *
	 * @param string $name Possible values:
	 *                     - 'native': Native name of the language (default, e.g., Deutsch for German).
	 *                     - 'english': English name of the language.
	 *                     - 'http': HTTP language code (e.g., 'de-AT').
	 *                     - 'language_long': Alias for 'http'.
	 *                     - 'language_short' First part of 'http' (e.g., 'de' for 'de-AT').
	 *                     - 'lang': Alias for 'language_short'.
	 *                     - 'wp_locale': Identifier for translation files used by WordPress.
	 *                     - 'custom': Language name set in the site preferences.
	 *                     - 'text': Alias for 'custom'.
	 *                     - 'none': No text output (e.g,. for displaying the flag icon only).
	 *
	 * @return string
	 */
	public function get_name( $name = '' ) {

		if ( ! empty( $this->names[ $name ] ) ) {
			return $this->names[ $name ];
		}

		if ( ! empty( $this->names[ $name . '_name' ] ) ) {
			return $this->names[ $name . '_name' ];
		}

		if ( in_array( $name, array( 'language_short', 'lang' ), true ) ) {
			return strtok( $this->names['http_name'], '-' );
		}

		if ( $name === 'language_long' ) {
			return $this->names[ 'http_name' ];
		}

		if ( $name === 'none' ) {
			return '';
		}

		// $name is empty or invalid, so ...
		foreach ( array( 'native_name', 'english_name' ) as $match ) {
			if ( ! empty( $this->names[ $match ] ) ) {
				return $this->names[ $match ];
			}
		}

		return '';
	}

	/**
	 * Get the priority.
	 *
	 * @return int
	 */
	public function get_priority() {

		return $this->priority;
	}

	/**
	 * Prepares data passed by a DB query for example.
	 *
	 * @param array $raw_data Raw data array.
	 *
	 * @return array
	 */
	private function prepare_raw_data( array $raw_data ) {

		$default = array(
			'english_name' => '',
			'native_name'  => '',
			'custom_name'  => '',
			'is_rtl'       => FALSE,
			'http_name'    => '',
			'priority'     => 1,
			'wp_locale'    => '',
			'text'         => '',
		);

		if ( isset( $raw_data[ 'text' ] ) ) {
			$default[ 'custom_name' ] = $raw_data[ 'text' ];
		}

		return wp_parse_args( $raw_data, $default );
	}

}
