<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Factory;

use WP_Error;

/**
 * Static factory for error objects.
 *
 * @package Inpsyde\MultilingualPress\Common\Factory
 * @since   3.0.0
 */
class Error {

	/**
	 * Creates a new error object for the given arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param string|int $code    Optional. Error code. Defaults to ''.
	 * @param string     $message Optional. Error message. Defaults to ''.
	 * @param mixed      $data    Optional. Error data. Defaults to ''.
	 *
	 * @return WP_Error Error object.
	 */
	public static function create( $code = '', $message = '', $data = '' ) {

		return new WP_Error( $code, $message, $data );
	}
}
