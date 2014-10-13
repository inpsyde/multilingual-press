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
	 * @param  string $name
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

		// $name is empty or invalid, so ...
		foreach ( array ( 'custom_name', 'native_name', 'english_name' ) as $match ) {
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
			'wp_locale'    => ''
		);

		return wp_parse_args( $raw_data, $default );
	}
}