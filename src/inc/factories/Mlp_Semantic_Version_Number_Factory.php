<?php # -*- coding: utf-8 -*-

/**
 * Static factory for Semantic Version Number objects.
 */
class Mlp_Semantic_Version_Number_Factory {

	/**
	 * Creates a new Semantic Version Number object.
	 *
	 * @param string $version Version.
	 *
	 * @return Mlp_Semantic_Version_Number
	 */
	public static function create( $version ) {

		return new Mlp_Semantic_Version_Number( $version );
	}
}
