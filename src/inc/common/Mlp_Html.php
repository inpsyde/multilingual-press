<?php # -*- coding: utf-8 -*-
/**
 * HTML helper
 *
 * @author     toscho
 * @since      2013.08.26
 * @version    2013.08.26
 * @package    MultilingualPress
 * @subpackage Helpers
 */
class Mlp_Html implements Mlp_Html_Interface {

	/**
	 * Converts an array into HTML attributes.
	 *
	 * @since  2013.08.26
	 * @param  array $attrs
	 * @param  bool  $xml
	 * @return string
	 */
	public function array_to_attrs( array $attrs, $xml = false ) {
		$str = '';

		foreach ( $attrs as $key => $value ) {
			if ( true === $value ) {
				$value = $xml ? "='$key'" : '';
			}

			$str .= " $key='" . esc_attr( $value ) . "'";
		}

		return $str;
	}
}
