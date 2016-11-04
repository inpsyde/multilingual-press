<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Nonce\Nonce;

/**
 * Static factory for Save Post Request Validator objects.
 */
class Mlp_Save_Post_Request_Validator_Factory {

	/**
	 * Creates a new Save Post Request Validator object.
	 *
	 * @param Nonce $nonce Nonce object.
	 *
	 * @return Mlp_Save_Post_Request_Validator
	 */
	public static function create( Nonce $nonce ) {

		return new Mlp_Save_Post_Request_Validator( $nonce );
	}
}
