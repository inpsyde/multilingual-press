<?php # -*- coding: utf-8 -*-

_deprecated_file(
	'Mlp_Accept_Header_Validator_Interface',
	'3.0.0'
);

/**
 * @deprecated 3.0.0 Deprecated with no alternative available.
 */
class Mlp_Language_Header_Validator {

	/**
	 * @deprecated 3.0.0 Deprecated with no alternative available.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_valid( $value ) {

		_deprecated_function(
			__METHOD__,
			'3.0.0'
		);

		return (bool) preg_match( '~[a-zA-Z_-]~', $value );
	}
}
