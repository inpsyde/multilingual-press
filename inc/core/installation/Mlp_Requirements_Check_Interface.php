<?php # -*- coding: utf-8 -*-
/**
 * Check the current system if it matches the minimum requirements.
 *
 * @version 2014.08.29
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
interface Mlp_Requirements_Check_Interface {

	/**
	 * Check all given requirements.
	 *
	 * @return bool
	 */
	public function is_compliant();

	/**
	 * @return array
	 */
	public function get_error_messages();
}