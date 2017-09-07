<?php # -*- coding: utf-8 -*-
/**
 * Save site settings for redirect feature.
 *
 * @version 2014.04.26
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 * @package    MultilingualPress
 * @subpackage Redirect
 */
interface Mlp_Redirect_Settings_Data_Interface {

	/**
	 * Name attribute for the view's checkbox.
	 *
	 * @return string
	 */
	public function get_checkbox_name();

	/**
	 * @return int
	 */
	public function get_current_option_value();

	/**
	 * Validate and save user input
	 *
	 * @param  array $data User input
	 * @return bool
	 */
	public function save( array $data );
}
