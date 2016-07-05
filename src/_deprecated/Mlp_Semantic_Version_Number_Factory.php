<?php # -*- coding: utf-8 -*-

use Inpsyde\MultilingualPress\Common\Type\SemanticVersionNumber;

_deprecated_file(
	'Mlp_Semantic_Version_Number_Factory',
	'3.0.0',
	'Inpsyde\MultilingualPress\Common\Type\SemanticVersionNumber'
);

/**
 * @deprecated 3.0.0 Deprecated in favor of {@see SemanticVersionNumber}.
 */
class Mlp_Semantic_Version_Number_Factory {

	/**
	 * @deprecated 3.0.0 Deprecated in favor of {@see SemanticVersionNumber::create}.
	 *
	 * @param mixed $version Version source.
	 *
	 * @return SemanticVersionNumber Version number object.
	 */
	public static function create( $version ) {

		_deprecated_function(
			__METHOD__,
			'3.0.0',
			'Inpsyde\MultilingualPress\Common\Type\SemanticVersionNumber::create'
		);

		return SemanticVersionNumber::create( $version );
	}
}
