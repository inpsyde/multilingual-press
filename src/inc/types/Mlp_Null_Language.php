<?php # -*- coding: utf-8 -*-

/**
 * Null language implementation.
 */
class Mlp_Null_Language implements Mlp_Language_Interface {

	/**
	 * @return int
	 */
	public function get_priority() {

		return 0;
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public function get_name( $name = '' ) {

		return '';
	}

	/**
	 * @return bool
	 */
	public function is_rtl() {

		return false;
	}
}
