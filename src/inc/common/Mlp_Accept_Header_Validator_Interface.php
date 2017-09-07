<?php
/**
 * Validates single values in an accept header.
 *
 * @version 2014.09.25
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Accept_Header_Validator_Interface {

	/**
	 * Validates a value
	 *
	 * @param  mixed $value
	 * @return bool
	 */
	public function is_valid( $value );
}
