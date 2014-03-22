<?php
/**
 * Validate requests. Yes, really.
 *
 * @author  Inpsyde GmbH, toscho
 * @version 2014.03.09
 * @license GPL
 */
interface Mlp_Request_Validator_Interface {
	/**
	 * Is this a valid request?
	 *
	 * @param  mixed $context
	 * @return bool
	 */
	public function is_valid( $context = NULL );
}