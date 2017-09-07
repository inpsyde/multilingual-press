<?php
/**
 * Checks entries in an Accept-Language header.
 *
 * @version 2014.09.25
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Language_Header_Validator
	implements Mlp_Accept_Header_Validator_Interface {

	/**
	 * Validates a value
	 *
	 * @param  string $value
	 * @return bool
	 */
	public function is_valid( $value ) {

		return (bool) preg_match( '~[a-zA-Z_-]~', $value );
	}
}
