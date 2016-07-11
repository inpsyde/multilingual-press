<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Factory\Error;

_deprecated_file(
	'Mlp_WP_Error_Factory',
	'3.0.0',
	'Inpsyde\MultilingualPress\Common\Factory\Error'
);

/**
 * @deprecated 3.0.0 Deprecated in favor of {@see Error}.
 */
class Mlp_WP_Error_Factory {

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see Error::create}.
	 *
	 * @param string|int $code    Optional. Error code. Defaults to ''.
	 * @param string     $message Optional. Error message. Defaults to ''.
	 * @param mixed      $data    Optional. Error data. Defaults to ''.
	 *
	 * @return Error Error object.
	 */
	public static function create( $code = '', $message = '', $data = '' ) {

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\Error::create'
		);

		return Error::create( $code, $message, $data );
	}
}
