<?php # -*- coding: utf-8 -*-
/**
 * Language object
 *
 * @version 2014.09.22
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Language_Interface {

	/**
	 * @return int
	 */
	public function get_priority();

	/**
	 * @param  string $name
	 * @return string
	 */
	public function get_name( $name = '' );

	/**
	 * @return bool
	 */
	public function is_rtl();
}
