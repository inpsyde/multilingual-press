<?php # -*- coding: utf-8 -*-

/**
 * Static factory for Nonce Validator objects.
 */
class Mlp_Nonce_Validator_Factory {

	/**
	 * Creates a new Nonce Validator object.
	 *
	 * @param string $base    Base name for both nonce name and action.
	 * @param int    $site_id Optional. Nonce site ID. Defaults to 0.
	 *
	 * @return Inpsyde_Nonce_Validator
	 */
	public static function create( $base, $site_id = 0 ) {

		return new Inpsyde_Nonce_Validator( $base, $site_id );
	}
}
