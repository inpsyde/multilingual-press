<?php
/**
 * Inpsyde_Nonce_Validator_Interface
 *
 * Provide nonces, and handle their validation.
 *
 * @version 2014.02.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Inpsyde_Nonce_Validator_Interface {
	/**
	 * Get nonce field name.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get nonce action.
	 *
	 * @return string
	 */
	public function get_action();

	/**
	 * Verify request.
	 *
	 * @return bool
	 */
	public function is_valid();
}