<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\EscapedURL;

_deprecated_file(
	'Mlp_Url_Factory',
	'3.0.0',
	'Inpsyde\MultilingualPress\Common\Type\EscapedURL'
);

/**
 * @deprecated 3.0.0 Deprecated in favor of {@see EscapedURL}.
 */
class Mlp_Url_Factory {

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see EscapedURL::create}.
	 *
	 * @param mixed $url URL source.
	 *
	 * @return EscapedURL URL object.
	 */
	public static function create( $url ) {

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\EscapedURL::create'
		);

		return EscapedURL::create( $url );
	}
}
