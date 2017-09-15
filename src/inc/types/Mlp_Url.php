<?php # -*- coding: utf-8 -*-
/**
 * Escaped URL data type
 *
 * @version 2014.09.22
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Url implements Mlp_Url_Interface {

	/**
	 * @type string
	 */
	private $url;

	/**
	 * Constructor
	 *
	 * @param string $url
	 */
	public function __construct( $url ) {

		if ( is_scalar( $url ) ) {
			$this->url = esc_url( (string) $url );
		} // method_exists() and is_callable() are not reliable
		// see the comments for http://php.net/manual/en/function.method-exists.php
		elseif ( is_object( $url ) && in_array( '__toString', get_class_methods( $url ), true ) ) {
			$this->url = esc_url( (string) $url );
		} // Might be a WP_Error.
		else {
			$this->url = '';
		}
	}

	/**
	 * Returns an URL
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->url;
	}
}
