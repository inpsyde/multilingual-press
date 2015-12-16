<?php # -*- coding: utf-8 -*-

/**
 * Static factory for URL objects.
 */
class Mlp_Url_Factory {

	/**
	 * Creates a new URL object.
	 *
	 * @param string $url URL.
	 *
	 * @return Mlp_Url
	 */
	public static function create( $url ) {

		return new Mlp_Url( $url );
	}
}
