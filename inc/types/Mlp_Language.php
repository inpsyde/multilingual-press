<?php # -*- coding: utf-8 -*-
/**
 * Language object
 *
 * @version 2014.09.22
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language implements Mlp_Language_Interface {

	/**
	 * @type int
	 */
	private $priority = 1;

	/**
	 * @type bool
	 */
	private $is_rtl = FALSE;

	/**
	 * @type array
	 */
	private $names = array();

	/**
	 * @param array $raw_data
	 */
	public function __construct( Array $raw_data ) {

		$data = $this->prepare_raw_data( $raw_data );

		$this->priority = (int) $data[ 'priority' ];
		unset ( $data[ 'priority' ] );

		$this->is_rtl = empty ( $data[ 'is_rtl' ] );
		unset ( $data[ 'is_rtl' ] );

		$this->names  = $data;
	}

	/**
	 * @return bool
	 */
	public function is_rtl() {

		return $this->is_rtl;
	}

	/**
	 * Get different possible language names
	 *
	 * @param  string $name Possible values:
	 *                      - 'native' (default) ex: Deutsch for German
	 *                      - 'english' English name of the language
	 *                      - 'http' ex: 'de-AT'
	 *                      - 'language_long' alias for 'http'.
	 *                      - 'language_short' first part of 'http', ex: 'de' in 'de-AT'
	 *                      - 'lang' alias for 'language_short'
	 *                      - 'wp_locale' Identifier for translation files used by WordPress
	 *                      - 'custom' Language name set in the site preferences
	 *                      - 'text' alias for 'custom'
	 *                      - 'none' no text output (eg. for displaying just the flag icon)
	 * @return string
	 */
	public function get_name( $name = '' ) {

		if ( isset ( $this->names[ $name ] ) )
			return $this->names[ $name ];

		if ( isset ( $this->names[ $name . '_name' ] ) )
			return $this->names[ $name . '_name' ];

		if ( in_array( $name, array ( 'language_short', 'lang' ) ) )
			return strtok( $this->names[ 'http_name'], '-' );

		if ( $name === 'language_long' )
			return $this->names[ 'http_name' ];

		if ( $name === 'custom_name' || $name === 'custom' ) {

			if ( ! empty ( $this->names[ 'text' ] ) )
				return $this->names[ 'text' ];

			return $this->names[ 'custom_name' ];
		}

		if ( $name === 'none' ) {
			return '';
		}

		// $name is empty or invalid, so ...
		foreach ( array ( 'native_name', 'english_name' ) as $match ) {
			if ( ! empty ( $this->names[ $match ] ) )
				return $this->names[ $match ];
		}

		return '';
	}

	/**
	 * @return int
	 */
	public function get_priority() {

		return $this->priority;
	}

	/**
	 * Prepares data passed by a db query for example.
	 *
	 * @param  array $raw_data
	 * @return array
	 */
	private function prepare_raw_data( Array $raw_data ) {

		$default = array (
			'english_name' => '',
			'native_name'  => '',
			'custom_name'  => '',
			'is_rtl'       => FALSE,
			'http_name'    => '',
			'priority'     => 1,
			'wp_locale'    => '',
			'text'         => ''
		);

		if ( isset ( $raw_data[ 'text' ] ) )
			$default[ 'custom_name' ] = $raw_data[ 'text' ];

		return wp_parse_args( $raw_data, $default );
	}
}