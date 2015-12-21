<?php # -*- coding: utf-8 -*-

/**
 * Static factory for WordPress Error objects.
 */
class Mlp_WP_Error_Factory {

	/**
	 * Creates a new WordPress Error object.
	 *
	 * @param string|int $code    Optional. Error code. Defaults to ''.
	 * @param string     $message Optional. Error message. Defaults to ''.
	 * @param mixed      $data    Optional. Error data. Defaults to ''.
	 *
	 * @return WP_Error
	 */
	public static function create( $code = '', $message = '', $data = '' ) {

		return new WP_Error( $code, $message, $data );
	}
}
