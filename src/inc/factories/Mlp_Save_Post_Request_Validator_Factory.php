<?php # -*- coding: utf-8 -*-

/**
 * Static factory for Save Post Request Validator objects.
 */
class Mlp_Save_Post_Request_Validator_Factory {

	/**
	 * Creates a new Save Post Request Validator object.
	 *
	 * @param Inpsyde_Nonce_Validator_Interface $nonce_validator Nonce Validator object.
	 *
	 * @return Mlp_Save_Post_Request_Validator
	 */
	public static function create( Inpsyde_Nonce_Validator_Interface $nonce_validator ) {

		return new Mlp_Save_Post_Request_Validator( $nonce_validator );
	}
}
