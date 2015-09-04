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
interface Mlp_Html_Interface {

	/**
	 * Converts an array into HTML attributes.
	 *
	 * @since  2013.08.26
	 * @param  array $attrs
	 * @param  bool  $xml
	 * @return string
	 */
	public function array_to_attrs( array $attrs, $xml = FALSE );
}